<?php

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
                    // Lock this device for 30 seconds to avoid scheduling too frequently when the device is offline
                    $lock = Cache::lock('device:poll:' . $device['device_id'], 30);

                    if ($lock->get()) {
                        Log::debug('Submitted work for device ID ' . $device['device_id'] . ' to queue poller-' . $device['poller_group']);
                        PollDevice::dispatch($device['device_id'], $this->module_overrides, $this->verbosity)->onQueue('poller-' . $device['poller_group']);
                    } else {
                        Log::warning('Device ID ' . $device['device_id'] . ' needs to wait more time before it can be queued again');
                    }
                }

                Log::debug('Submitted work for ' . $devices->count() . ' devices');
            }
        }
    }
}
