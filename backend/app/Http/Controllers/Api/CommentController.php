<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Comment\CreateRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $validated = $request->validated();

        $comment = Comment::create([
            'content'    => $validated['content'],
            'post_id'    => $validated['post_id'],
            'parent_id'  => $validated['parent_id'] ?? null,
            'user_id'    => auth()->id(), 
        ]);

        $comment->load('user');
        // event(new CommentCreated($comment));    

        return response()->json([
            'message' => 'Комментарий создан',
            'data' => $comment,
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

    public function postComments(Post $post)
    {
        $comments = $post->comments()
            ->with(['user.profile', 'children.user.profile', 'children.children.user.profile'])
            ->latest()
            ->get();

        return response()->json($comments);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
