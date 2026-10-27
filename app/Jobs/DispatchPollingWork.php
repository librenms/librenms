<?php

namespace App\Jobs;

use App\Facades\LibrenmsConfig;
use App\Horizon\QueueStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        $this->onQueue('scheduler'); // kept separate so other work cannot delay dispatching
        $this->poll ??= LibrenmsConfig::get('schedule_type.poller') == 'scheduler';
        $this->discover ??= LibrenmsConfig::get('schedule_type.discovery') == 'scheduler';

        $this->poll_interval = self::pollInterval();
        $this->discovery_interval = self::discoveryInterval();
    }

    /**
     * Tags shown in Horizon, failed runs are found by tag
     *
     * @return string[]
     */
    public function tags(): array
    {
        return ['dispatcher'];
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
        // new devices are not polled until discovered, a rediscover request (last_discovered null) keeps polling
        $new_device = '`last_discovered` IS NULL AND `last_polled` IS NULL';

        // based on the python dispatcher (LibreNMS/service.py)
        $devices = DB::table('devices')
            ->select(['device_id', 'poller_group'])
            ->selectRaw("IF($new_device, 0, COALESCE($poll_wait <= ?, 1)) AS `poll`", [$this->poll_interval, self::LOOKAHEAD])
            // polling retries down devices, only new devices rely on discovery for that
            ->selectRaw("IF(`status` = 0, $new_device, COALESCE($discovery_wait <= ?, 1)) AS `discover`", [$this->discovery_interval, self::LOOKAHEAD])
            ->selectRaw("$poll_wait AS `poll_wait`", [$this->poll_interval])
            ->selectRaw("$discovery_wait AS `discovery_wait`", [$this->discovery_interval])
            ->where('disabled', 0)
            ->havingRaw('`poll` OR `discover`')
            ->orderByRaw('`last_discovered` IS NULL DESC')
            ->orderBy('last_polled_timetaken', 'desc')
            ->get();

        $modules = ModuleList::fromUserOverrides([]);
        $worked = $this->workedQueues();
        $discovered = [];
        $polled = [];
        $unworked = [];

        foreach ($devices as $device) {
            if ($this->discover && $device->discover) {
                $queue = DiscoverDevice::queueFor($device->poller_group);
                if ($worked === null || in_array($queue, $worked)) {
                    DiscoverDevice::dispatch($device->device_id, $modules)
                        ->onQueue($queue)
                        ->delay($this->delay($device->discovery_wait));
                    $discovered[] = $device->device_id;
                } else {
                    $unworked[$queue][] = $device->device_id;
                }
            }

            if ($this->poll && $device->poll) {
                $queue = PollDevice::queueFor($device->poller_group);
                if ($worked === null || in_array($queue, $worked)) {
                    PollDevice::dispatch($device->device_id, $modules)
                        ->onQueue($queue)
                        ->delay($this->delay($device->poll_wait));
                    $polled[] = $device->device_id;
                } else {
                    $unworked[$queue][] = $device->device_id;
                }
            }
        }

        Log::debug('Due for discovery: ' . implode(',', $discovered));
        Log::debug('Due for polling: ' . implode(',', $polled));

        foreach ($unworked as $queue => $device_ids) {
            // warn hourly, devices stay due and are queued as soon as a node works the queue
            if (Cache::add("dispatch-unworked-queue:$queue", true, 3600)) {
                Log::warning("No workers for queue $queue, not queueing devices: " . implode(',', $device_ids));
            }
        }
    }

    /**
     * Queues worked by running Horizon supervisors.
     * A job queued for a poller group no node works would wait there, with another queued each time its unique lock expires.
     * Null when unknown (Horizon is not in use or not running), everything is queued.
     *
     * @return string[]|null
     */
    private function workedQueues(): ?array
    {
        if (! QueueStatus::available()) {
            return null;
        }

        return app(QueueStatus::class)->workedQueues() ?: null;
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
     * Retry delays for a down device, using 90% of the poll interval.
     * The rest is left for the attempts themselves and the next dispatch.
     *
     * @return array<int, int>
     */
    public static function downRetryBackoff(): array
    {
        $interval = self::pollInterval();

        return array_map(fn (float $fraction): int => (int) round($interval * $fraction), [0.1, 0.3, 0.5]);
    }

    /**
     * Hold work that is not due yet until its due time, so start times are not rounded up to the next dispatcher run
     */
    private function delay(mixed $wait): int
    {
        return max(0, (int) ceil((float) $wait));
    }
}
