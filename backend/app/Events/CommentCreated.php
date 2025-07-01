<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment->load('user');
    }

    public function broadcastOn()
    {
        return new Channel('post.' . $this->comment->post_id);
    }

    public function broadcastAs()
    {
        return 'comment.created';
    }

    public function broadcastWith()
    {
        return [
            'comment' => $this->comment->toArray()
        ];
    }
}
