<?php

namespace App\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Events\DeviceDiscovered;
use App\Events\DiscoveringDevice;
use App\Events\DiscoveringModule;
use App\Events\ModuleDiscovered;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SupportsSubmodules;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use Throwable;

class DiscoverDevice implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $deviceArray;
    private ?Device $device = null;
    public int $tries = 1;
    public int $uniqueFor = 7200; // > $timeout
    public int $timeout = 3600; // < queue retry_after

    public function __construct(
        public int $device_id,
        public ModuleList $moduleList,
    ) {
    }

    public function displayName(): string
    {
        return "DiscoverDevice:$this->device_id";
    }

    public function uniqueId(): int
    {
        return $this->device_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->initDevice();
            App::forgetInstance('sensor-discovery');
            DiscoveringDevice::dispatch($this->device);
            $measurement = Measurement::start('discover');
            $measurement->manager()->checkpoint(); // don't count previous stats

            $this->discoverModules();

            $measurement->end();

            Log::info(sprintf("\n>>> Discovered %s (%s) in %0.3f seconds <<<",
                $this->device->displayName(),
                $this->device->device_id,
                $measurement->getDuration()));

            Log::alert(sprintf('INFO: device:discover %s (%s) discovered in %0.3fs',
                $this->device->hostname,
                $this->device->device_id,
                $measurement->getDuration()));

            $this->device->last_discovered = Carbon::now();
            $this->device->last_discovered_timetaken = $measurement->getDuration();
            $this->device->save();

            DeviceDiscovered::dispatch($this->device);
        } finally {
            $this->device?->save(); // make sure device changes are saved even if discovery fails
        }
    }

    private function initDevice(): void
    {
        Cache::driver('device')->flush();
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

    private function discoverModules(): void
    {
        // import legacy stuff
        include_once base_path('includes/functions.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/discovery/functions.inc.php');
        include_once base_path('includes/snmp.inc.php');

        // update availability status
        app(CheckDeviceAvailability::class)->execute($this->device);
        $connectivity = new ConnectivityHelper($this->device);
        $this->deviceArray['status'] = $this->device->status;
        $this->deviceArray['status_reason'] = $this->device->status_reason;
        $os = OS::make($this->deviceArray);

        foreach ($this->moduleList->modulesWithStatus(ProcessType::Discovery, $this->device) as $module => $module_status) {
            $should_discover = false;
            $start_memory = memory_get_usage();
            $module_start = microtime(true);

            try {
                $instance = Module::fromName($module);
                $should_discover = $instance->shouldDiscover($os, $module_status, $connectivity);

                if ($should_discover) {
                    DiscoveringModule::dispatch($this->device, $module);
                    Log::info("#### Load discovery module $module ####\n");
                    Log::debug($module_status);

                    if ($instance instanceof SupportsSubmodules) {
                        $instance->setSubmodules($module_status->submodules);
                    }

                    $instance->discover($os);

                    // a module (core) may have detected a new os, reload os specific code
                    if ($this->device->os !== $os->getName()) {
                        $os = $this->handleOsChange($os);
                    }
                }
            } catch (Throwable $e) {
                // Re-throw exception if we're in running tests
                if (defined('PHPUNIT_RUNNING')) {
                    throw $e;
                }

                // isolate module exceptions so they don't disrupt the discovery process
                Eventlog::log("Error discovering $module module: " . class_basename($e) . '. Check log file for more details.', $this->device, 'discovery', Severity::Error);
                report($e);
            }

            if ($should_discover) {
                Log::info('');
                app(MeasurementManager::class)->printChangedStats();
                Module::savePerformance($module, ProcessType::Discovery, $module_start, $start_memory);
                Log::info("#### Unload discovery module $module ####\n");
                ModuleDiscovered::dispatch($this->device, $module);
            }
        }
    }

    private function handleOsChange(OS $os): OS
    {
        Eventlog::log('Device OS changed: ' . $os->getName() . ' -> ' . $this->device->os, $this->device, 'system', Severity::Notice);
        $this->deviceArray['os'] = $this->device->os;
        $this->deviceArray['os_group'] = LibrenmsConfig::getOsSetting($this->device->os, 'group');
        $os = OS::make($this->deviceArray);

        Log::info('OS Changed ');
        Log::notice('OS: ' . LibrenmsConfig::getOsSetting($this->device->os, 'text') . " ({$this->device->os})\n");

        return $os;
    }
}
