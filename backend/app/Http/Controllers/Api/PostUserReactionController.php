<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\PostUserReaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class PostUserReactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['like', 'dislike'])],
        ]);

        $post = Post::findOrFail($request->post_id);
        $user = $request->user();

        $existing = PostUserReaction::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->type === $request->type) {
                $existing->delete();
            } else {
                $existing->update(['type' => $request->type]);
            }
        } else {
            PostUserReaction::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'type' => $request->type,
            ]);
        }

        $likeCount = PostUserReaction::where('post_id', $post->id)->where('type', 'like')->count();
        $dislikeCount = PostUserReaction::where('post_id', $post->id)->where('type', 'dislike')->count();

        $post->update([
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount,
        ]);

        return response()->json([
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount,
            'user_reaction' => PostUserReaction::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->value('type'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }

    public function myReaction($id)
    {
        $post = Post::findOrFail($id);

        $reaction = PostUserReaction::where('post_id', $post->id)
            ->where('user_id', auth()->id())
            ->value('type');

        return response()->json(['reaction' => $reaction]);
    }
}
