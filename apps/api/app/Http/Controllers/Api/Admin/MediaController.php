<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderMediaRequest;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = Media::query()
            ->when($request->string('type')->toString() ?: null, fn ($q, $type) => $q->where('type', $type))
            ->when($request->string('category')->toString() ?: null, fn ($q, $category) => $q->where('category', $category))
            ->when($request->string('subcategory')->toString() ?: null, fn ($q, $sub) => $q->where('subcategory', $sub))
            ->when($request->has('is_published'), function ($q) use ($request) {
                $q->where('is_published', filter_var($request->input('is_published'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 24));

        return MediaResource::collection($items);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = $this->media->upload(
            $request->file('file'),
            $request->string('type', 'image')->toString(),
            $request->user()?->id,
            null,
            $request->safe()->only(['category', 'subcategory', 'is_published']),
        );

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMediaRequest $request, Media $medium): MediaResource
    {
        return new MediaResource($this->media->update($medium, $request->validated()));
    }

    public function reorder(ReorderMediaRequest $request): JsonResponse
    {
        $this->media->reorder($request->validated('ids'));

        return response()->json([
            'data' => ['message' => 'Orden actualizado.'],
        ]);
    }

    public function destroy(Media $medium): JsonResponse
    {
        $this->media->destroy($medium);

        return response()->json([
            'data' => ['message' => 'Archivo eliminado.'],
        ]);
    }
}
