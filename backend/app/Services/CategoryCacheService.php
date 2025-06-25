<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryCacheService
{
   public static function getAll()
   {
      return Cache::remember('categories.all', 86400, function () {
         Log::info('Кеш категорий не найден — идёт запрос в БД');
         return Category::select('id', 'name', 'slug', 'parent_id')
            ->orderByDesc('id') 
            ->get();
      });
   }

   public static function clear()
   {
      Cache::forget('categories.all');
      Log::info('Кеш категорий очищен');
   }
}