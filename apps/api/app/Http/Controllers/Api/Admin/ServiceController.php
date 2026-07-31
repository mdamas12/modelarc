<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()->with('image')->orderBy('sort_order')->get();

        return ServiceResource::collection($services);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $service = Service::query()->create($data)->load('image');

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service->load('image'));
    }

    public function update(UpdateServiceRequest $request, Service $service): ServiceResource
    {
        $data = $request->validated();
        // El slug se genera automáticamente al crear; en edición no se fuerza desde el admin.
        unset($data['slug']);

        $service->update($data);

        return new ServiceResource($service->fresh('image'));
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'data' => ['message' => 'Servicio eliminado.'],
        ]);
    }
}
