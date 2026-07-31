<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreLeadRequest;
use App\Http\Resources\LeadResource;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(private readonly LeadService $leads) {}

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->leads->create($request->validated());

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }
}
