<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateHeroRequest;
use App\Http\Resources\HeroGalleryResource;
use App\Http\Resources\HeroResource;
use App\Models\Hero;
use App\Models\HeroGallery;
use Illuminate\Http\JsonResponse;

class HeroController extends Controller
{
    public function show(): JsonResponse
    {
        $hero = Hero::singleton();
        $galleries = HeroGallery::query()
            ->where('hero_id', $hero->id)
            ->ordered()
            ->get();

        return response()->json([
            'data' => [
                'hero' => (new HeroResource($hero))->resolve(),
                'galleries' => HeroGalleryResource::collection($galleries)->resolve(),
            ],
        ]);
    }

    public function update(UpdateHeroRequest $request): HeroResource
    {
        $hero = Hero::singleton();
        $hero->update($request->validated());

        return new HeroResource($hero->fresh());
    }
}
