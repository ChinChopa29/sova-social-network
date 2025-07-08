<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');

        if (strlen($q) < 2) {
            return response()->json([
                'posts' => [],
                'users' => [],
                'categories' => [],
                'tags' => [],
                'groups' => [],
            ]);
        }

        $posts = Post::select('id', 'title', 'slug')
            ->where('title', 'ilike', "%$q%")
            ->limit(5)
            ->get();

        $users = User::with(['profile' => function ($query) {
            $query->select('id', 'user_id', 'slug', 'avatar');
        }])
            ->select('id', 'name')
            ->where('name', 'ilike', "%$q%")
            ->limit(5)
            ->get();

        $categories = Category::select('id', 'name', 'slug')
            ->where('name', 'ilike', "%$q%")
            ->limit(5)
            ->get();

        $tags = Tag::select('id', 'name', 'slug')
            ->where('name', 'ilike', "%$q%")
            ->limit(5)
            ->get();

        // $groups = Group::select('id', 'name', 'slug')
        //     ->where('name', 'ilike', "%$q%")
        //     ->limit(5)
        //     ->get();

        return response()->json([
            'posts' => $posts,
            'users' => $users,
            'categories' => $categories,
            'tags' => $tags,
            // 'groups' => $groups,
        ]);
    }
}
