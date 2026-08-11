<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderHeroGalleryRequest;
use App\Http\Requests\Admin\StoreHeroGalleryRequest;
use App\Http\Requests\Admin\UpdateHeroGalleryRequest;
use App\Http\Resources\HeroGalleryResource;
use App\Models\Hero;
use App\Models\HeroGallery;
use App\Services\HeroImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class HeroGalleryController extends Controller
{
    public function __construct(
        private readonly HeroImageService $images,
    ) {}

    public function store(StoreHeroGalleryRequest $request): JsonResponse
    {
        $hero = Hero::singleton();
        $data = $request->validated();
        $published = array_key_exists('published', $data) ? (bool) $data['published'] : true;

        if ($published && $this->images->publishedCount($hero->id) >= HeroImageService::MAX_PUBLISHED) {
            throw ValidationException::withMessages([
                'published' => ['Solo puedes publicar un máximo de '.HeroImageService::MAX_PUBLISHED.' imágenes en el hero.'],
            ]);
        }

        $path = $this->images->store($request->file('image'));

        $gallery = HeroGallery::query()->create([
            'hero_id' => $hero->id,
            'path' => $path,
            'order' => $data['order'] ?? $this->images->nextOrder($hero->id),
            'published' => $published,
        ]);

        return (new HeroGalleryResource($gallery))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateHeroGalleryRequest $request, HeroGallery $heroGallery): HeroGalleryResource
    {
        $data = $request->validated();

        if (array_key_exists('published', $data) && $data['published'] && ! $heroGallery->published) {
            if ($this->images->publishedCount($heroGallery->hero_id, $heroGallery->id) >= HeroImageService::MAX_PUBLISHED) {
                throw ValidationException::withMessages([
                    'published' => ['Solo puedes publicar un máximo de '.HeroImageService::MAX_PUBLISHED.' imágenes en el hero.'],
                ]);
            }
        }

        if ($request->hasFile('image')) {
            $data['path'] = $this->images->replace($heroGallery->path, $request->file('image'));
        }

        unset($data['image']);

        $heroGallery->update($data);

        return new HeroGalleryResource($heroGallery->fresh());
    }

    public function reorder(ReorderHeroGalleryRequest $request): JsonResponse
    {
        foreach ($request->validated('ids') as $index => $id) {
            HeroGallery::query()->whereKey($id)->update(['order' => $index + 1]);
        }

        return response()->json([
            'data' => ['message' => 'Orden actualizado.'],
        ]);
    }

    public function destroy(HeroGallery $heroGallery): JsonResponse
    {
        $this->images->delete($heroGallery->path);
        $heroGallery->delete();

        return response()->json([
            'data' => ['message' => 'Imagen del hero eliminada.'],
        ]);
    }
}
