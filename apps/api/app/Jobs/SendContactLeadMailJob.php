<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\ContactMailNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendContactLeadMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(public int $leadId) {}

    public function handle(ContactMailNotifier $notifier): void
    {
        $lead = Lead::query()->find($this->leadId);

        if (! $lead) {
            return;
        }

        $notifier->send($lead);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('SendContactLeadMailJob failed', [
            'lead_id' => $this->leadId,
            'error' => $e?->getMessage(),
        ]);
    }
}
