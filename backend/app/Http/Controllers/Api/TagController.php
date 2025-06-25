<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\CreateRequest;
use App\Models\Tag;
use App\Services\SearchService;
use App\Services\TagCacheService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function index() 
    {
        return response()->json([
            'data' => TagCacheService::getAll(),
        ]);
    }

    public function store(CreateRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $slug = SlugGenerator::generate($data['name'], Tag::class);

        $data['slug'] = $slug;

        Tag::create($data);
        TagCacheService::clear();

        return response()->json(['message' => 'Тэг создан']);
    }

    public function update(CreateRequest $request, Tag $tag): JsonResponse
    {
        $data = $this->prepareData($request->validated());

        $tag->update($data);
        TagCacheService::clear();

        return response()->json(['message' => 'Тег обновлен']);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();
        TagCacheService::clear();

        return response()->json(['message' => 'Тег удален']);
    }

    private function prepareData(array $validated): array
    {
        $validated['slug'] = SlugGenerator::generate($validated['name'], Tag::class);
        return $validated;
    }

    public function search(Request $request, SearchService $searchService)
    {
        $query = $request->input('query');
        $results = $searchService->search(Tag::class, $query);

        return response()->json([
            'data' => $results,
        ]);
    }

}
