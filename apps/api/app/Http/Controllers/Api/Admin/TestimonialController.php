<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestimonialController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = Testimonial::query()
            ->with(['clientPhoto', 'project:id,title,slug'])
            ->orderBy('sort_order')
            ->get();

        return TestimonialResource::collection($items);
    }

    public function store(StoreTestimonialRequest $request): JsonResponse
    {
        $testimonial = Testimonial::query()->create($request->validated())
            ->load(['clientPhoto', 'project']);

        return (new TestimonialResource($testimonial))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Testimonial $testimonial): TestimonialResource
    {
        return new TestimonialResource($testimonial->load(['clientPhoto', 'project']));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): TestimonialResource
    {
        $testimonial->update($request->validated());

        return new TestimonialResource($testimonial->fresh(['clientPhoto', 'project']));
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $testimonial->delete();

        return response()->json([
            'data' => ['message' => 'Testimonio eliminado.'],
        ]);
    }
}
