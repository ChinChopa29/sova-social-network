<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\CreateRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\Comment;
use App\Models\Profile;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Report::query()
            ->with('user', 'targetUser');

        if ($request->has('after')) {
            $query->where('id', '>', $request->after);
        }

        $reports = $query->latest()->get();

        $grouped = $reports->groupBy('target_type');

        $loaded = [];

        foreach ($grouped as $type => $items) {
            $ids = $items->pluck('target_id')->unique();

            switch ($type) {
                case Post::class:
                    $loaded[$type] = Post::whereIn('id', $ids)->get(['id', 'slug', 'title'])->keyBy('id');
                    break;
                case Comment::class:
                    $loaded[$type] = Comment::whereIn('id', $ids)->get(['id', 'post_id'])->keyBy('id');
                    break;
                case User::class:
                    $loaded[$type] = User::whereIn('id', $ids)->with('profile')->get(['id', 'name'])->keyBy('id');
                    break;
                    // case Group::class:
                    //     $loaded[$type] = Group::whereIn('id', $ids)->get(['id', 'slug', 'name'])->keyBy('id');
                    //     break;
                default:
                    break;
            }
        }

        $reports->each(function ($report) use ($loaded) {
            $type = $report->target_type;
            $id = $report->target_id;

            if (isset($loaded[$type][$id])) {
                $report->setRelation('target', $loaded[$type][$id]);
            }
        });

        return response()->json([
            'data' => $reports,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $data['target_type'] = match ($data['type']) {
            'post' => Post::class,
            'comment' => Comment::class,
            'profile' => User::class,
            // 'group' => Group::class,
            default => null,
        };

        if (!$data['target_type']) {
            return response()->json([
                'message' => 'Неверный тип объекта жалобы.',
            ], 422);
        }

        unset($data['type']);

        try {
            Report::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'SQL ошибка',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['message' => 'Жалоба отправлена'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $report = Report::with([
            'user.profile',
            'targetUser.profile',
            'target' => function ($morphTo) {
                $morphTo->morphWith([
                    Post::class => ['user.profile', 'images', 'tags', 'category'],
                    Comment::class => ['user.profile'],
                    // Group::class => [],
                    Profile::class => ['user'],
                ]);
            }
        ])->findOrFail($id);

        return response()->json($report);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $report->delete();

        return response()->json(['message' => 'Жалоба отклонена']);
    }
}
