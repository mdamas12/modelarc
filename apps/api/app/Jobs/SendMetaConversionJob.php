<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\MetaConversionsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendMetaConversionJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public int $timeout = 30;

    /**
     * @param  array{
     *     event_id: string,
     *     event_source_url?: ?string,
     *     client_ip_address?: ?string,
     *     client_user_agent?: ?string,
     *     fbp?: ?string,
     *     fbc?: ?string
     * }  $context
     */
    public function __construct(
        public int $leadId,
        public array $context,
    ) {}

    public function handle(MetaConversionsService $meta): void
    {
        if (! $meta->isEnabled()) {
            return;
        }

        $lead = Lead::query()->find($this->leadId);
        if (! $lead) {
            return;
        }

        $meta->sendLead($lead, $this->context);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('SendMetaConversionJob failed', [
            'lead_id' => $this->leadId,
            'event_id' => $this->context['event_id'] ?? null,
            'error' => $e?->getMessage(),
        ]);
    }
}
