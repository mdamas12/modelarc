<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\SubmitTestimonialInvitationRequest;
use App\Http\Resources\TestimonialInvitationResource;
use App\Http\Resources\TestimonialResource;
use App\Services\TestimonialInvitationService;
use Illuminate\Http\JsonResponse;

class TestimonialInvitationController extends Controller
{
    public function __construct(private readonly TestimonialInvitationService $invitations) {}

    public function show(string $token): TestimonialInvitationResource|JsonResponse
    {
        $invitation = $this->invitations->findPublicByToken($token);

        if (! $invitation->isPending()) {
            return response()->json([
                'data' => [
                    'status' => $invitation->status,
                    'message' => 'Este enlace ya fue utilizado. Gracias por tu participación.',
                    'client_name' => $invitation->client_name,
                    'project' => $invitation->project ? [
                        'id' => $invitation->project->id,
                        'title' => $invitation->project->title,
                    ] : null,
                ],
            ], 410);
        }

        return new TestimonialInvitationResource($invitation);
    }

    public function submit(SubmitTestimonialInvitationRequest $request, string $token): JsonResponse
    {
        $invitation = $this->invitations->findPublicByToken($token);
        $testimonial = $this->invitations->submit($invitation, $request->validated());

        return (new TestimonialResource($testimonial))
            ->response()
            ->setStatusCode(201);
    }
}
