<?php

namespace App\Services;

class SearchService
{
    public function search(string $modelClass, string $query, array $fields = ['name', 'slug'], int $limit = 10)
    {
        $queryBuilder = $modelClass::query();

        foreach ($fields as $field) {
            $queryBuilder->orWhere($field, 'LIKE', "%{$query}%");
        }

        return $queryBuilder->limit($limit)->get();
    }
}
