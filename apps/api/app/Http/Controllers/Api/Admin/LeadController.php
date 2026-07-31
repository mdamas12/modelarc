<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leads) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->leads->list($request->only(['status', 'search']), (int) $request->integer('per_page', 20));

        return LeadResource::collection($paginator);
    }

    public function show(Lead $lead): LeadResource
    {
        return new LeadResource($lead->load('project'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        return new LeadResource($this->leads->update($lead, $request->validated()));
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->leads->delete($lead);

        return response()->json([
            'data' => ['message' => 'Lead eliminado.'],
        ]);
    }
}
