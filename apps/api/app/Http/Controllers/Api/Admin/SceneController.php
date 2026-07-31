<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSceneRequest;
use App\Http\Requests\Admin\UpdateSceneRequest;
use App\Http\Resources\TourSceneResource;
use App\Models\TourScene;
use App\Models\VirtualTour;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;

class SceneController extends Controller
{
    public function __construct(private readonly TourService $tours) {}

    public function store(StoreSceneRequest $request, VirtualTour $tour): JsonResponse
    {
        $scene = $this->tours->createScene($tour, $request->validated());

        return (new TourSceneResource($scene))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSceneRequest $request, TourScene $scene): TourSceneResource
    {
        return new TourSceneResource($this->tours->updateScene($scene, $request->validated()));
    }

    public function destroy(TourScene $scene): JsonResponse
    {
        $this->tours->deleteScene($scene);

        return response()->json([
            'data' => ['message' => 'Escena eliminada.'],
        ]);
    }
}
