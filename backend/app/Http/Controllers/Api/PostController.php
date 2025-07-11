<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CreateRequest;
use App\Models\Post;
use App\Models\Tag;
use App\Services\PostImageService;
use App\Services\TagService;
use App\Support\SlugGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Post::with(['user', 'category'])->latest()->get(),
        ]);
    }

    public function show(Post $post)
    {
        if (!Gate::allows('view', $post)) {
            abort(403); 
        }
        $post->load(['user.profile', 'category', 'tags', 'images']);
        return response()->json($post);
    }

    public function store(CreateRequest $request)
    {   
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $slug = SlugGenerator::generate($data['title'], Post::class);

        $data['slug'] = $slug;

        $images = $data['images'] ?? [];
        unset($data['images']);

        $post = Post::create($data);

        PostImageService::storeImages($images, $post);

        TagService::attachTagsToPost($data['tags'] ?? [], $post, $request->user()->id);

        return response()->json(['message' => 'Пост создан']);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Пост удален']);
    }
}
