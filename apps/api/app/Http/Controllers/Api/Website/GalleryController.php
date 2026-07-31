<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GalleryController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $items = Media::query()
            ->where('is_published', true)
            ->whereIn('type', ['image', 'panorama'])
            ->when($request->string('category')->toString() ?: null, fn ($q, $category) => $q->where('category', $category))
            ->when($request->string('subcategory')->toString() ?: null, fn ($q, $sub) => $q->where('subcategory', $sub))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 24));

        return MediaResource::collection($items);
    }
}
