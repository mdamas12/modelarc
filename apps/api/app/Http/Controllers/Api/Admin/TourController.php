<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\VirtualTourResource;
use App\Models\VirtualTour;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TourController extends Controller
{
    public function __construct(private readonly TourService $tours) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tours = VirtualTour::query()
            ->with([
                'project:id,title,slug,cover_media_id',
                'project.coverMedia',
                'initialScene.thumbnailMedia',
                'initialScene.panoramaMedia',
                'scenes' => fn ($q) => $q->orderBy('sort_order')->with(['thumbnailMedia', 'panoramaMedia']),
            ])
            ->when($request->integer('project_id') ?: null, fn ($q, $id) => $q->where('project_id', $id))
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return VirtualTourResource::collection($tours);
    }

    public function store(StoreTourRequest $request): JsonResponse
    {
        $tour = $this->tours->create($request->validated());

        return (new VirtualTourResource($tour))
            ->response()
            ->setStatusCode(201);
    }

    public function show(VirtualTour $tour): VirtualTourResource
    {
        $tour->load([
            'project',
            'initialScene',
            'scenes.panoramaMedia',
            'scenes.thumbnailMedia',
            'scenes.hotspots.media',
            'scenes.hotspots.targetScene',
        ]);

        return new VirtualTourResource($tour);
    }

    public function update(UpdateTourRequest $request, VirtualTour $tour): VirtualTourResource
    {
        return new VirtualTourResource($this->tours->update($tour, $request->validated()));
    }

    public function destroy(VirtualTour $tour): JsonResponse
    {
        $this->tours->delete($tour);

        return response()->json([
            'data' => ['message' => 'Tour eliminado.'],
        ]);
    }

    public function publish(VirtualTour $tour): VirtualTourResource
    {
        return new VirtualTourResource($this->tours->publish($tour));
    }
}
