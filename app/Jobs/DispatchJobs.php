<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use LibreNMS\Config;

class DispatchJobs implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function uniqueId(): int
    {
        // Only allow one dispatch job to be queued
        return 1;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Make sure we have configured job queueing
        if (getenv('QUEUE_CONNECTION')) {
            // Queue poller jobs if not configured for service worker
            if (! Config::get('service_poller_enabled')) {
                Artisan::call('device:poll', ['device spec' => 'needs_polling', '-q' => true, '-d' => true]);
            }
        }
    }
}
