<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHotspotRequest;
use App\Http\Requests\Admin\UpdateHotspotRequest;
use App\Http\Resources\TourHotspotResource;
use App\Models\TourHotspot;
use App\Models\TourScene;
use App\Services\HotspotService;
use Illuminate\Http\JsonResponse;

class HotspotController extends Controller
{
    public function __construct(private readonly HotspotService $hotspots) {}

    public function store(StoreHotspotRequest $request, TourScene $scene): JsonResponse
    {
        $hotspot = $this->hotspots->create($scene, $request->validated());

        return (new TourHotspotResource($hotspot))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateHotspotRequest $request, TourHotspot $hotspot): TourHotspotResource
    {
        return new TourHotspotResource($this->hotspots->update($hotspot, $request->validated()));
    }

    public function destroy(TourHotspot $hotspot): JsonResponse
    {
        $this->hotspots->delete($hotspot);

        return response()->json([
            'data' => ['message' => 'Hotspot eliminado.'],
        ]);
    }
}
