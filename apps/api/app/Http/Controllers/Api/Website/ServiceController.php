<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->active()
            ->with('image')
            ->orderBy('sort_order')
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(string $slug): ServiceResource
    {
        $service = Service::query()
            ->active()
            ->with('image')
            ->where('slug', $slug)
            ->firstOrFail();

        return new ServiceResource($service);
    }
}
