<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubcategoryRequest;
use App\Http\Requests\Admin\UpdateSubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;

class SubcategoryController extends Controller
{
    public function store(StoreSubcategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        $data['category_id'] = $category->id;
        $data['order'] = $data['order'] ?? ((int) $category->subcategories()->max('order') + 1);
        $data['published'] = array_key_exists('published', $data) ? (bool) $data['published'] : true;

        $subcategory = Subcategory::query()->create($data);

        return (new SubcategoryResource($subcategory))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSubcategoryRequest $request, Subcategory $subcategory): SubcategoryResource
    {
        $subcategory->update($request->validated());

        return new SubcategoryResource($subcategory->fresh());
    }

    public function destroy(Subcategory $subcategory): JsonResponse
    {
        $subcategory->delete();

        return response()->json([
            'data' => ['message' => 'Subcategoría eliminada.'],
        ]);
    }
}
