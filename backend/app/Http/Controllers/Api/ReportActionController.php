<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportAction\CreateRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\ReportAction;
use App\Models\User;
use Dom\Comment;
use Illuminate\Support\Facades\DB;

class ReportActionController extends Controller
{
    public function store(CreateRequest $request)
    {
        $data = $request->validated();
        $data['moderator_id'] = auth()->id();

        $modelMap = [
            'post'    => Post::class,
            'comment' => Comment::class,
            'user'    => User::class,
            // 'group'   => \App\Models\Group::class,
        ];

        if (!isset($modelMap[$data['target_type']])) {
            return response()->json([
                'message' => "Класс модели {$data['target_type']} не найден",
            ], 400);
        }

        $modelClass = $modelMap[$data['target_type']];
        $data['target_type'] = $modelClass;

        DB::beginTransaction();

        try {
            switch ($data['action_type']) {
                case 'ban':
                    $user = User::findOrFail($data['target_user_id']);
                    $user->is_banned = true;
                    $user->save();
                    break;

                case 'delete':
                    $target = $modelClass::findOrFail($data['target_id']);
                    if (method_exists($target, 'delete')) {
                        $target->delete();
                    } else {
                        throw new \Exception("Удаление не поддерживается моделью {$modelClass}");
                    }
                    break;

                case 'warning':
                    break;
            }

            ReportAction::create([
                'moderator_id'   => $data['moderator_id'],
                'report_id'      => $data['report_id'],
                'target_type'    => $data['target_type'],
                'target_id'      => $data['target_id'],
                'target_user_id' => $data['target_user_id'],
                'action_type'    => $data['action_type'],
                'comment'        => $data['comment'] ?? null,
            ]);

            Report::findOrFail($data['report_id'])->delete();

            DB::commit();

            return response()->json(['message' => 'Действие выполнено успешно'], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ошибка при выполнении действия',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
