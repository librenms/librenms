<?php

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;

class DispatchPollJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  string  $device_spec
     * @param  int  $verbosity
     * @param  array<string, bool|string[]>  $module_overrides
     */
    public function __construct(
        public string $device_spec,
        public int $verbosity = -1,
        public array $module_overrides = [],
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Make sure we have configured job queueing
        if (\config('queue.default') != 'sync') {
            // Queue poller jobs if not configured for service worker
            if (! Config::get('service_poller_enabled')) {
                $devices = Device::whereDeviceSpec($this->device_spec)->select('device_id', 'poller_group')->get();

                foreach ($devices as $device) {
                    Log::debug('Submitted work for device ID ' . $device['device_id'] . ' to queue poller-' . $device['poller_group']);
                    PollDevice::dispatch($device['device_id'], $this->module_overrides, $this->verbosity)->onQueue('poller-' . $device['poller_group']);
                }

                Log::debug('Submitted work for ' . $devices->count() . ' devices');
            }
        }
    }
}
