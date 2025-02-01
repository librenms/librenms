<?php

namespace App\Jobs;

use App\Events\DevicePolled;
use App\Events\PollingDevice;
use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Enum\Severity;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Module;
use Throwable;

class PollDevice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?\App\Models\Device $device = null;
    private ?array $deviceArray = null;
    /**
     * @var \LibreNMS\OS|\LibreNMS\OS\Generic
     */
    private $os;

    /**
     * @param  int  $device_id
     * @param  array<string, bool|string[]>  $module_overrides
     */
    public function __construct(
        public int $device_id,
        public array $module_overrides = [],
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->initDevice();
        PollingDevice::dispatch($this->device);
        $this->os = OS::make($this->deviceArray);

        $measurement = Measurement::start('poll');
        $measurement->manager()->checkpoint(); // don't count previous stats

        $helper = new ConnectivityHelper($this->device);
        $helper->saveMetrics();
        $helper->isUp(); // check and save status

        $this->pollModules();

        $measurement->end();

        // if modules are not overridden, record performance
        if (empty($this->modules)) {
            if ($this->device->status) {
                $this->recordPerformance($measurement);
            }

            if ($helper->canPing()) {
                $this->os->enableGraph('ping_perf');
            }

            $this->os->persistGraphs($this->device->status); // save graphs but don't delete any if device is down
            Log::info(sprintf("Enabled graphs (%s): %s\n\n",
                $this->device->graphs->count(),
                $this->device->graphs->pluck('graph')->implode(' ')
            ));
        }

        // finalize the device poll
        $this->device->save();

        Log::info(sprintf("\n>>> Polled %s (%s) in %0.3f seconds <<<",
            $this->device->displayName(),
            $this->device->device_id,
            $measurement->getDuration()));

        // add log file line, this is used by the simple python dispatcher watchdog
        Log::channel('single')->alert(sprintf('INFO: device:poll %s (%s) polled in %0.3fs',
            $this->device->hostname,
            $this->device->device_id,
            $measurement->getDuration()));

        // check if the poll took too long and log an event
        if ($measurement->getDuration() > Config::get('rrd.step')) {
            Eventlog::log('Polling took longer than ' . round(Config::get('rrd.step') / 60, 2) .
                ' minutes!  This will cause gaps in graphs.', $this->device, 'system', Severity::Error);
        }

        DevicePolled::dispatch($this->device);
    }

    private function pollModules(): void
    {
        // update $device array status
        $this->deviceArray['status'] = $this->device->status;
        $this->deviceArray['status_reason'] = $this->device->status_reason;

        // import legacy garbage
        include_once base_path('includes/functions.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/polling/functions.inc.php');
        include_once base_path('includes/snmp.inc.php');

        $datastore = app('Datastore');

        foreach ($this->getModules() as $module => $status) {
            $module_status = Module::pollingStatus($module, $this->device, $this->isModuleManuallyEnabled($module));
            $should_poll = false;
            $start_memory = memory_get_usage();
            $module_start = microtime(true);

            try {
                $instance = Module::fromName($module);
                $should_poll = $instance->shouldPoll($this->os, $module_status);

                if ($should_poll) {
                    Log::info("#### Load poller module $module ####\n");
                    Log::debug($module_status);

                    if (is_array($status)) {
                        Config::set('poller_submodules.' . $module, $status);
                    }

                    $instance->poll($this->os, $datastore);
                }
            } catch (Throwable $e) {
                // isolate module exceptions so they don't disrupt the polling process
                Log::error("%rError polling $module module for {$this->device->hostname}.%n $e", ['color' => true]);
                Eventlog::log("Error polling $module module. Check log file for more details.", $this->device, 'poller', Severity::Error);
                report($e);
            }

            if ($should_poll) {
                Log::info('');
                app(MeasurementManager::class)->printChangedStats();
                $this->saveModulePerformance($module, $module_start, $start_memory);
                Log::info("#### Unload poller module $module ####\n");
            }
        }
    }

    private function saveModulePerformance(string $module, float $start_time, int $start_memory): void
    {
        $module_time = microtime(true) - $start_time;
        $module_mem = (memory_get_usage() - $start_memory);

        Log::info(sprintf(">> Runtime for poller module '%s': %.4f seconds with %s bytes", $module, $module_time, $module_mem));

        app('Datastore')->put($this->deviceArray, 'poller-perf', [
            'module' => $module,
            'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
            'rrd_name' => ['poller-perf', $module],
        ], [
            'poller' => $module_time,
        ]);
        $this->os->enableGraph('poller_modules_perf');
    }

    private function initDevice(): void
    {
        \DeviceCache::setPrimary($this->device_id);
        $this->device = \DeviceCache::getPrimary();
        $this->device->ip = $this->device->overwrite_ip ?: Dns::lookupIp($this->device) ?: $this->device->ip;

        $this->deviceArray = $this->device->toArray();
        if ($os_group = Config::get("os.{$this->device->os}.group")) {
            $this->deviceArray['os_group'] = $os_group;
        }

        $this->printDeviceInfo($os_group);
        $this->initRrdDirectory();
    }

    private function initRrdDirectory(): void
    {
        $host_rrd = \Rrd::name($this->device->hostname, '', '');
        if (Config::get('rrd.enable', true) && ! is_dir($host_rrd)) {
            try {
                mkdir($host_rrd);
                Log::info("Created directory : $host_rrd");
            } catch (\ErrorException $e) {
                Eventlog::log("Failed to create rrd directory: $host_rrd", $this->device);
                Log::error($e);
            }
        }
    }

    private function printDeviceInfo(?string $group): void
    {
        Log::info(sprintf(<<<'EOH'
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s

EOH, $this->device->hostname, $group ? " ($group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));
    }

    private function recordPerformance(Measurement $measurement): void
    {
        $measurement->manager()->record('device', $measurement);
        $this->device->last_polled = Carbon::now();
        $this->device->last_polled_timetaken = $measurement->getDuration();

        app('Datastore')->put($this->deviceArray, 'poller-perf', [
            'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
            'module' => 'ALL',
        ], [
            'poller' => $this->device->last_polled_timetaken,
        ]);

        $this->os->enableGraph('poller_perf');
    }

    private function getModules(): array
    {
        $default_modules = LibrenmsConfig::get('poller_modules', []);

        if (empty($this->module_overrides)) {
            return $default_modules;
        }

        // ensure order of modules, preserve submodules
        $ordered_modules = [];
        foreach ($default_modules as $module => $enabled) {
            if (isset($this->module_overrides[$module])) {
                $ordered_modules[$module] = $this->module_overrides[$module];
            }
        }

        return $ordered_modules;
    }

    private function isModuleManuallyEnabled(string $module): ?bool
    {
        if (empty($this->module_overrides)) {
            return null;
        }

        return isset($this->module_overrides[$module]);
    }
}
