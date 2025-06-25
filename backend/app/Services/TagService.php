<?php

namespace App\Services;

use App\Models\Tag;
use App\Support\SlugGenerator;

class TagService 
{
   public static function attachTagsToPost(array $tagNames, $post, $userId): void
    {
        $tagIds = collect($tagNames)->map(function ($tagName) use ($userId) {
            return Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => SlugGenerator::generate($tagName, Tag::class),
                    'user_id' => $userId,
                ]
            )->id;
        });

        $post->tags()->sync($tagIds);
    }
}