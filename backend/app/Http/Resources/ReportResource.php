<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'target_id' => $this->target_id,
            'target_user_id' => $this->target_user_id,
            'comment' => $this->comment,
            'category' => $this->category,
            'user' => new UserResource($this->user),
            'target_user' => new UserResource($this->targetUser),
            'target' => $this->transformTarget(),
        ];
    }

    protected function transformTarget(): ?array
    {
        if (!$this->target) return null;

        return match ($this->type) {
            'post' => [
                'slug' => $this->target->slug ?? null,
                'title' => $this->target->title ?? null,
            ],
            'comment' => [
                'id' => $this->target->id ?? $this->target_id,
            ],
            'profile' => [
                'slug' => $this->target->slug ?? null,
                'name' => $this->target->name ?? null,
            ],
            'group' => [
                'slug' => $this->target->slug ?? null,
                'name' => $this->target->name ?? null,
            ],
            default => null,
        };
    }
}
