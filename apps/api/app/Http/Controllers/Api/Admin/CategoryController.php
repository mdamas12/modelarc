<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('order')->orderBy('name')])
            ->ordered()
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['order'] = $data['order'] ?? ((int) Category::query()->max('order') + 1);
        $data['published'] = array_key_exists('published', $data) ? (bool) $data['published'] : true;

        $category = Category::query()->create($data);

        return (new CategoryResource($category->load('subcategories')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load('subcategories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category->fresh('subcategories'));
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'data' => ['message' => 'Categoría eliminada.'],
        ]);
    }
}
