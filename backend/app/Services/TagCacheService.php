<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TagCacheService
{
   public static function getAll()
   {
      return Cache::remember('tags.all', 86400, function () {
        Log::info('Кеш тегов не найден — идёт запрос в БД');
        return Tag::with('user')
            ->select('id', 'name', 'slug', 'user_id')
            ->orderByDesc('id')
            ->get();
    });
   }

   public static function clear()
   {
      Cache::forget('tags.all');
      Log::info('Кеш тегов очищен');
   }
}