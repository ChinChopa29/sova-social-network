<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CreateRequest;
use App\Models\Category;
use App\Services\CategoryCacheService;
use App\Services\SearchService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{

    public function index(): JsonResponse
    {
        $categories = CategoryCacheService::getAll();
        return response()->json(['data' => $categories]);
    }
    
    public function paginated(): JsonResponse
    {
        $categories = Category::orderByDesc('created_at')->paginate(12);
        return response()->json($categories); 
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $data = $this->prepareData($request->validated());

        Category::create($data);
        CategoryCacheService::clear();

        return response()->json(['message' => 'Категория создана']);
    }

    public function update(CreateRequest $request, Category $category): JsonResponse
    {
        $data = $this->prepareData($request->validated());

        $category->update($data);
        CategoryCacheService::clear();

        return response()->json(['message' => 'Категория обновлена']);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        CategoryCacheService::clear();

        return response()->json(['message' => 'Категория удалена']);
    }

    private function prepareData(array $validated): array
    {
        $validated['slug'] = SlugGenerator::generate($validated['name'], Category::class);
        return $validated;
    }
    
    public function search(Request $request, SearchService $searchService)
    {
        $query = $request->input('query');
        $results = $searchService->search(Category::class, $query);

        return response()->json([
            'data' => $results,
        ]);
    }
}
