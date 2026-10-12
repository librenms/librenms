<?php

namespace App\Jobs;

use App\Facades\LibrenmsConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\ModuleList;

class DispatchPollingWork implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** seconds between dispatcher runs, keep in sync with the schedule in routes/console.php */
    public const INTERVAL = 10;
    /** work due before the next dispatcher run (plus slack for a late run) is dispatched now with a delay */
    private const LOOKAHEAD = self::INTERVAL + 5;
    /**
     * Start each run a few seconds early so worker pickup latency (queue:work --sleep defaults to 3s)
     * cannot stretch the interval past the frequency and skip a window. Start times slowly drift earlier,
     * which spreads the load around the window.
     */
    private const EARLY_START = 3;

    public int $uniqueFor = 60; // only one waiting in the queue
    private string $pollingQueueConnection;
    private int $poll_interval;
    private int $discovery_interval;

    /**
     * @param  bool|null  $poll  force polling dispatch on or off, null uses schedule_type.poller
     * @param  bool|null  $discover  force discovery dispatch on or off, null uses schedule_type.discovery
     */
    public function __construct(
        private ?bool $poll = null,
        private ?bool $discover = null,
    ) {
        $this->poll ??= LibrenmsConfig::get('schedule_type.poller') == 'scheduler';
        $this->discover ??= LibrenmsConfig::get('schedule_type.discovery') == 'scheduler';

        $this->poll_interval = self::pollInterval();
        $this->discovery_interval = self::discoveryInterval();

        $default = \config('queue.default');
        // database minimum driver, redis recommended
        $this->pollingQueueConnection = $default == 'sync' ? 'database' : $default;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->poll && ! $this->discover) {
            return;
        }

        // seconds until due, measured from the start of the previous run (last_* is recorded at the end of the run)
        $poll_wait = 'UNIX_TIMESTAMP(`last_polled`) - COALESCE(`last_polled_timetaken`, 0) + ? - UNIX_TIMESTAMP()';
        $discovery_wait = 'UNIX_TIMESTAMP(`last_discovered`) - COALESCE(`last_discovered_timetaken`, 0) + ? - UNIX_TIMESTAMP()';
        // seconds until a discovery attempt that did not complete may be tried again
        $deferred_wait = 'UNIX_TIMESTAMP(`discovery_deferred_until`) - UNIX_TIMESTAMP()';
        // discovery checks if the device is up until the first discovery completes, polling does after
        $awaiting_discovery = '`last_discovered` IS NULL AND `last_polled` IS NULL';

        // based on the python dispatcher (LibreNMS/service.py)
        $devices = DB::table('devices')
            ->select(['device_id', 'poller_group'])
            ->selectRaw("IF($awaiting_discovery, 0, COALESCE($poll_wait <= ?, 1)) AS `poll`", [$this->poll_interval, self::LOOKAHEAD])
            ->selectRaw("IF(`status` = 0 AND NOT ($awaiting_discovery), 0, COALESCE($discovery_wait <= ?, 1) AND COALESCE($deferred_wait <= ?, 1)) AS `discover`", [$this->discovery_interval, self::LOOKAHEAD, self::LOOKAHEAD])
            ->selectRaw("$poll_wait AS `poll_wait`", [$this->poll_interval])
            ->selectRaw("$discovery_wait AS `discovery_wait`", [$this->discovery_interval])
            ->selectRaw("$deferred_wait AS `deferred_wait`")
            ->where('disabled', 0)
            ->havingRaw('`poll` OR `discover`')
            ->orderByRaw('`last_discovered` IS NULL DESC')
            ->orderBy('last_polled_timetaken', 'desc')
            ->get();

        $modules = ModuleList::fromUserOverrides([]);
        $discovered = [];
        $polled = [];

        foreach ($devices as $device) {
            if ($this->discover && $device->discover) {
                DiscoverDevice::dispatch($device->device_id, $modules)
                    ->onConnection($this->pollingQueueConnection)
                    ->onQueue($device->poller_group ? "discovery-$device->poller_group" : 'discovery')
                    ->delay($this->delay($device->discovery_wait, $device->deferred_wait));
                $discovered[] = $device->device_id;
            }

            if ($this->poll && $device->poll) {
                PollDevice::dispatch($device->device_id, $modules)
                    ->onConnection($this->pollingQueueConnection)
                    ->onQueue($device->poller_group ? "poll-$device->poller_group" : 'poll')
                    ->delay($this->delay($device->poll_wait));
                $polled[] = $device->device_id;
            }
        }

        Log::debug('Due for discovery: ' . implode(',', $discovered));
        Log::debug('Due for polling: ' . implode(',', $polled));
    }

    /**
     * Seconds from the start of one poll to the start of the next
     */
    public static function pollInterval(): int
    {
        return LibrenmsConfig::get('service_poller_frequency', LibrenmsConfig::get('rrd.step', 300)) - self::EARLY_START;
    }

    /**
     * Seconds from the start of one discovery to the start of the next
     */
    public static function discoveryInterval(): int
    {
        return LibrenmsConfig::get('service_discovery_frequency', 21600) - self::EARLY_START;
    }

    /**
     * Hold work that is not due yet until its due time, so start times are not rounded up to the next dispatcher run
     */
    private function delay(mixed ...$waits): int
    {
        return max(0, (int) ceil(max(array_map(floatval(...), $waits))));
    }
}
