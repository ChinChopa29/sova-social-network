<?php

namespace App\Support;

use Illuminate\Support\Str;

class SlugGenerator
{
    public static function generate(string $name, string $modelClass, string $column = 'slug'): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $i = 1;

        while ($modelClass::where($column, $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        return $slug;
    }
}
