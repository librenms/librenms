<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;

class DispatchPollingWork implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private string $pollingQueueConnection;
    private int $find_time;
    private bool $enabled;

    public function __construct(
    ) {
        $this->find_time = Config::get('service_poller_frequency', Config::get('rrd.step', 300)) - 1;
        $this->enabled = Config::get('scheduler.poll.enabled', false);
        $default = \config('queue.default');
        // database minimum driver, redis recommended
        $this->pollingQueueConnection = $default == 'sync' ? 'database' : $default;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->enabled) {
            return;
        }

        $devices = DB::table('devices')
            ->select(['device_id', 'poller_group'])
            ->where('disabled', 0)
            ->where(function (Builder $query) {
                $query->whereNull('last_polled')
                    ->orWhereRaw('`last_polled` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -? SECOND), INTERVAL COALESCE(`last_polled_timetaken`, 0) SECOND)', [$this->find_time]);
            })
            ->orderBy('last_polled_timetaken', 'desc')
            ->get();

        Log::info('Due to for polling: ' . $devices->pluck('device_id')->implode(','));

        foreach ($devices as $device) {
            PollDevice::dispatch($device->device_id)
                ->onConnection($this->pollingQueueConnection)
                ->onQueue($device->poller_group ? "poll-$device->poller_group" : 'poll');
        }
    }
}
