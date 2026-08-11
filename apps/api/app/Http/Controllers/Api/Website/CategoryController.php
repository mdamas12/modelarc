<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->published()
            ->with(['subcategories' => fn ($q) => $q->published()->ordered()])
            ->ordered()
            ->get();

        return CategoryResource::collection($categories);
    }
}
