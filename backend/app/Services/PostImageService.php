<?php

namespace App\Services;

class PostImageService 
{
   public static function storeImages(array $images, $post) : void 
   {
      foreach ($images as $image) {
         $path = $image->store("posts/{$post->id}", 'public');
         $post->images()->create(['path' => $path]);
      }
   }
}