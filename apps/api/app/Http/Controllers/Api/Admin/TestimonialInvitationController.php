<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialInvitationRequest;
use App\Http\Resources\TestimonialInvitationResource;
use App\Models\TestimonialInvitation;
use App\Services\TestimonialInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestimonialInvitationController extends Controller
{
    public function __construct(private readonly TestimonialInvitationService $invitations) {}

    public function index(): AnonymousResourceCollection
    {
        $items = TestimonialInvitation::query()
            ->with(['project:id,title,slug', 'creator:id,name'])
            ->latest()
            ->paginate(20);

        return TestimonialInvitationResource::collection($items);
    }

    public function store(StoreTestimonialInvitationRequest $request): JsonResponse
    {
        $result = $this->invitations->createAndSend(
            $request->validated(),
            $request->user()?->id,
        );

        return (new TestimonialInvitationResource($result['invitation']))
            ->additional([
                'meta' => [
                    'mail_sent' => $result['mail_sent'],
                    'mail_error' => $result['mail_error'],
                    'public_url' => $result['invitation']->publicUrl(),
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function resend(TestimonialInvitation $invitation): JsonResponse
    {
        $result = $this->invitations->resend($invitation);

        return (new TestimonialInvitationResource($result['invitation']))
            ->additional([
                'meta' => [
                    'mail_sent' => $result['mail_sent'],
                    'mail_error' => $result['mail_error'],
                    'public_url' => $result['invitation']->publicUrl(),
                ],
            ])
            ->response();
    }

    public function destroy(TestimonialInvitation $invitation): JsonResponse
    {
        $invitation->delete();

        return response()->json([
            'data' => ['message' => 'Invitación eliminada.'],
        ]);
    }
}
