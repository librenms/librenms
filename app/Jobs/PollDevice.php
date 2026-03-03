<?php

namespace App\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Events\DevicePolled;
use App\Events\PollingDevice;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
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
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\Severity;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use Throwable;

class PollDevice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?Device $device = null;
    private ?array $deviceArray = null;
    /**
     * @var OS|OS\Generic
     */
    private $os;

    /**
     * @param  int  $device_id
     * @param  ModuleList  $moduleList
     */
    public function __construct(
        public int $device_id,
        public ModuleList $moduleList,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initDevice();
        $this->initRrdDirectory();
        PollingDevice::dispatch($this->device);
        $this->os = OS::make($this->deviceArray);

        $measurement = Measurement::start('poll');
        $measurement->manager()->checkpoint(); // don't count previous stats

        // check and save status
        app(CheckDeviceAvailability::class)->execute($this->device, true);

        $this->pollModules();

        $measurement->end();

        // if modules are not overridden, record performance
        if (! $this->moduleList->hasOverride()) {
            if ($this->device->status) {
                $this->recordPerformance($measurement);
            }

            if (ConnectivityHelper::pingIsAllowed($this->device)) {
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
        Log::alert(sprintf('INFO: device:poll %s (%s) polled in %0.3fs',
            $this->device->hostname,
            $this->device->device_id,
            $measurement->getDuration()));

        // check if the poll took too long and log an event
        if ($measurement->getDuration() > LibrenmsConfig::get('rrd.step')) {
            Eventlog::log('Polling took longer than ' . round(LibrenmsConfig::get('rrd.step') / 60, 2) .
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

        foreach ($this->moduleList->modulesWithStatus(ProcessType::Poller, $this->device) as $module => $module_status) {
            $should_poll = false;
            $start_memory = memory_get_usage();
            $module_start = microtime(true);

            try {
                $instance = Module::fromName($module);
                $should_poll = $instance->shouldPoll($this->os, $module_status);

                if ($should_poll) {
                    Log::info("#### Load poller module $module ####\n");
                    Log::debug($module_status);

                    if ($module_status->hasSubModules()) {
                        LibrenmsConfig::set('poller_submodules.' . $module, $module_status->submodules);
                    }

                    $instance->poll($this->os, $datastore);
                }
            } catch (Throwable $e) {
                // Re-throw exception if we're in running tests
                if (defined('PHPUNIT_RUNNING')) {
                    throw $e;
                }

                // isolate module exceptions so they don't disrupt the polling process
                Eventlog::log("Error polling $module module. Check log file for more details.", $this->device, 'poller', Severity::Error);
                report($e);
            }

            if ($should_poll) {
                Log::info('');
                app(MeasurementManager::class)->printChangedStats();
                Module::savePerformance($module, ProcessType::Poller, $module_start, $start_memory);
                $this->os->enableGraph('poller_modules_perf');
                Log::info("#### Unload poller module $module ####\n");
            }
        }
    }

    private function initDevice(): void
    {
        \DeviceCache::setPrimary($this->device_id);
        $this->device = \DeviceCache::getPrimary();
        $this->device->ip = Dns::lookupIp($this->device) ?? $this->device->ip;

        $this->deviceArray = $this->device->toArray();
        if ($os_group = LibrenmsConfig::get("os.{$this->device->os}.group")) {
            $this->deviceArray['os_group'] = $os_group;
        }

        Log::info(sprintf(<<<'EOH'
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s

EOH, $this->device->hostname, $os_group ? " ($os_group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));
    }

    private function initRrdDirectory(): void
    {
        $host_rrd = \Rrd::name($this->device->hostname, '', '');
        if (LibrenmsConfig::get('rrd.enable', true) && ! is_dir($host_rrd)) {
            try {
                mkdir($host_rrd);
                Log::info("Created directory : $host_rrd");
            } catch (\ErrorException $e) {
                Eventlog::log("Failed to create rrd directory: $host_rrd", $this->device);
                Log::error($e);
            }
        }
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
    }
}
