<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\VirtualTourResource;
use App\Services\TourService;

class TourController extends Controller
{
    public function __construct(private readonly TourService $tours) {}

    public function show(string $slug): VirtualTourResource
    {
        return new VirtualTourResource($this->tours->findPublicBySlug($slug));
    }
}
