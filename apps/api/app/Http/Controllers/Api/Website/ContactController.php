<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreLeadRequest;
use App\Http\Resources\LeadResource;
use App\Jobs\SendMetaConversionJob;
use App\Services\LeadService;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactController extends Controller
{
    public function __construct(
        private readonly LeadService $leads,
        private readonly MetaConversionsService $meta,
    ) {}

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $metaEventId = isset($validated['meta_event_id'])
            ? (string) $validated['meta_event_id']
            : null;
        $metaFbp = isset($validated['meta_fbp']) ? (string) $validated['meta_fbp'] : null;
        $metaFbc = isset($validated['meta_fbc']) ? (string) $validated['meta_fbc'] : null;
        $eventSourceUrl = isset($validated['event_source_url'])
            ? (string) $validated['event_source_url']
            : null;

        unset(
            $validated['meta_event_id'],
            $validated['meta_fbp'],
            $validated['meta_fbc'],
            $validated['event_source_url'],
        );

        $lead = $this->leads->create($validated);

        if (
            $metaEventId !== null
            && $metaEventId !== ''
            && $this->meta->isEnabled()
        ) {
            try {
                SendMetaConversionJob::dispatch($lead->id, [
                    'event_id' => $metaEventId,
                    'event_source_url' => $eventSourceUrl,
                    'client_ip_address' => $request->ip(),
                    'client_user_agent' => $request->userAgent(),
                    'fbp' => $metaFbp,
                    'fbc' => $metaFbc,
                ]);
            } catch (Throwable $e) {
                Log::error('No se pudo encolar Meta CAPI Lead', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }
}
