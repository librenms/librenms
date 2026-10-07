<?php

namespace App\Jobs;

use App\Facades\LibrenmsConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\ModuleList;

class DispatchPollingWork implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private string $pollingQueueConnection;
    private int $find_time;
    private int $discovery_find_time;
    private bool $enabled;
    private bool $discovery_enabled;

    public function __construct(
    ) {
        $this->find_time = LibrenmsConfig::get('service_poller_frequency', LibrenmsConfig::get('rrd.step', 300)) - 1;
        $this->discovery_find_time = LibrenmsConfig::get('service_discovery_frequency', 21600) - 1;
        $this->enabled = LibrenmsConfig::get('scheduler.poll.enabled', false);
        $this->discovery_enabled = LibrenmsConfig::get('scheduler.discovery.enabled', false);
        $default = \config('queue.default');
        // database minimum driver, redis recommended
        $this->pollingQueueConnection = $default == 'sync' ? 'database' : $default;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->enabled && ! $this->discovery_enabled) {
            return;
        }

        $poll_due = 'DATE_ADD(DATE_ADD(NOW(), INTERVAL -? SECOND), INTERVAL COALESCE(`last_polled_timetaken`, 0) SECOND)';
        $discovery_due = 'DATE_ADD(DATE_ADD(NOW(), INTERVAL -? SECOND), INTERVAL COALESCE(`last_discovered_timetaken`, 0) SECOND)';

        // same selection as the python dispatcher (LibreNMS/service.py)
        $devices = DB::table('devices')
            ->select(['device_id', 'poller_group'])
            // never polled and never discovered devices must be discovered first
            ->selectRaw("IF(`last_discovered` IS NULL AND `last_polled` IS NULL, 0, COALESCE(`last_polled` <= $poll_due, 1)) AS `poll`", [$this->find_time])
            // down devices are only discovered if they have never been discovered
            ->selectRaw("IF(`status` = 0, IF(`last_discovered` IS NULL, 1, 0), COALESCE(`last_discovered` <= $discovery_due, 1)) AS `discover`", [$this->discovery_find_time])
            ->where('disabled', 0)
            ->where(function (Builder $query) use ($poll_due, $discovery_due): void {
                $query->whereNull('last_polled')
                    ->orWhereNull('last_discovered')
                    ->orWhereRaw("`last_polled` <= $poll_due", [$this->find_time])
                    ->orWhereRaw("`last_discovered` <= $discovery_due", [$this->discovery_find_time]);
            })
            ->orderByRaw('`last_discovered` IS NULL DESC')
            ->orderBy('last_polled_timetaken', 'desc')
            ->get();

        $modules = ModuleList::fromUserOverrides([]);
        $discovered = [];
        $polled = [];

        foreach ($devices as $device) {
            if ($this->discovery_enabled && $device->discover) {
                DiscoverDevice::dispatch($device->device_id, $modules)
                    ->onConnection($this->pollingQueueConnection)
                    ->onQueue($device->poller_group ? "discovery-$device->poller_group" : 'discovery');
                $discovered[] = $device->device_id;
            }

            if ($this->enabled && $device->poll) {
                PollDevice::dispatch($device->device_id, $modules)
                    ->onConnection($this->pollingQueueConnection)
                    ->onQueue($device->poller_group ? "poll-$device->poller_group" : 'poll');
                $polled[] = $device->device_id;
            }
        }

        Log::debug('Due for discovery: ' . implode(',', $discovered));
        Log::debug('Due for polling: ' . implode(',', $polled));
    }
}
