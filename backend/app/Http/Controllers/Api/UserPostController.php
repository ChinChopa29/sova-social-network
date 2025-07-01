<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;  

class UserPostController extends Controller
{
    public function index(User $user)
    {
        $perPage = 4;
        $page = request()->get('page', 1);

        $filtered = $user->posts()
            ->latest()
            ->with(['user.profile', 'category', 'tags', 'images'])
            ->withCount('comments')
            ->get()
            ->filter(fn ($post) => Gate::allows('view', $post))
            ->values(); 

        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(), 
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginated;
    }
}
