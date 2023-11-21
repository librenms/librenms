<?php
/**
 * Poller.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Events\DevicePolled;
use App\Events\PollingDevice;
use App\Models\Device;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use Carbon\Carbon;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\Result;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Module;
use LibreNMS\Util\Version;
use Psr\Log\LoggerInterface;
use Throwable;

class Poller
{
    /** @var string */
    private $device_spec;
    /** @var array */
    private $module_override;

    /**
     * @var Device
     */
    private $device;
    /**
     * @var array
     */
    private $deviceArray;
    /**
     * @var \LibreNMS\OS|\LibreNMS\OS\Generic
     */
    private $os;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $device_spec, array $module_override, LoggerInterface $logger)
    {
        $this->device_spec = $device_spec;
        $this->module_override = $module_override;
        $this->logger = $logger;
        $this->parseModules();
    }

    public function poll(): Result
    {
        $results = new Result;
        $this->printHeader();

        if (Debug::isEnabled() && ! defined('PHPUNIT_RUNNING')) {
            \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
        }

        $this->logger->info("Starting polling run:\n");

        foreach (Device::whereDeviceSpec($this->device_spec)->pluck('device_id') as $device_id) {
            $results->markAttempted();
            $this->initDevice($device_id);
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
            if (empty($this->module_override)) {
                if ($this->device->status) {
                    $this->recordPerformance($measurement);
                }

                if ($helper->canPing()) {
                    $this->os->enableGraph('ping_perf');
                }

                $this->os->persistGraphs($this->device->status); // save graphs but don't delete any if device is down
                $this->logger->info(sprintf("Enabled graphs (%s): %s\n\n",
                    $this->device->graphs->count(),
                    $this->device->graphs->pluck('graph')->implode(' ')
                ));
            }

            // finalize the device poll
            $this->device->save();
            $results->markCompleted($this->device->status);
            DevicePolled::dispatch($this->device);

            $this->logger->info(sprintf("\n>>> Polled %s (%s) in %0.3f seconds <<<",
                $this->device->displayName(),
                $this->device->device_id,
                $measurement->getDuration()));
            \Log::channel('single')->alert(sprintf('INFO: device:poll %s (%s) polled in %0.3fs',
                $this->device->hostname,
                $this->device->device_id,
                $measurement->getDuration()));

            // check if the poll took too long and log an event
            if ($measurement->getDuration() > Config::get('rrd.step')) {
                Eventlog::log('Polling took longer than ' . round(Config::get('rrd.step') / 60, 2) .
                    ' minutes!  This will cause gaps in graphs.', $this->device, 'system', Severity::Error);
            }
        }

        return $results;
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

        foreach (array_keys(Config::get('poller_modules')) as $module) {
            $module_status = Module::pollingStatus($module, $this->device, $this->isModuleManuallyEnabled($module));
            $should_poll = false;
            $start_memory = memory_get_usage();
            $module_start = microtime(true);

            try {
                $instance = Module::fromName($module);
                $should_poll = $instance->shouldPoll($this->os, $module_status);

                if ($should_poll) {
                    $this->logger->info("#### Load poller module $module ####\n");
                    $this->logger->debug($module_status);

                    $instance->poll($this->os, $datastore);
                }
            } catch (Throwable $e) {
                // isolate module exceptions so they don't disrupt the polling process
                $this->logger->error("%rError polling $module module for {$this->device->hostname}.%n $e", ['color' => true]);
                Eventlog::log("Error polling $module module. Check log file for more details.", $this->device, 'poller', Severity::Error);
                report($e);
            }

            if ($should_poll) {
                $this->logger->info('');
                app(MeasurementManager::class)->printChangedStats();
                $this->saveModulePerformance($module, $module_start, $start_memory);
                $this->logger->info("#### Unload poller module $module ####\n");
            }
        }
    }

    private function saveModulePerformance(string $module, float $start_time, int $start_memory): void
    {
        $module_time = microtime(true) - $start_time;
        $module_mem = (memory_get_usage() - $start_memory);

        $this->logger->info(sprintf(">> Runtime for poller module '%s': %.4f seconds with %s bytes", $module, $module_time, $module_mem));

        app('Datastore')->put($this->deviceArray, 'poller-perf', [
            'module' => $module,
            'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
            'rrd_name' => ['poller-perf', $module],
        ], [
            'poller' => $module_time,
        ]);
        $this->os->enableGraph('poller_modules_perf');
    }

    private function isModuleManuallyEnabled(string $module): ?bool
    {
        if (empty($this->module_override)) {
            return null;
        }

        foreach ($this->module_override as $override) {
            [$override_module] = explode('/', $override);
            if ($module == $override_module) {
                return true;
            }
        }

        return false;
    }

    private function initDevice(int $device_id): void
    {
        \DeviceCache::setPrimary($device_id);
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
                $this->logger->info("Created directory : $host_rrd");
            } catch (\ErrorException $e) {
                Eventlog::log("Failed to create rrd directory: $host_rrd", $this->device);
                $this->logger->error($e);
            }
        }
    }

    private function parseModules(): void
    {
        foreach ($this->module_override as $index => $module) {
            // parse submodules (only supported by some modules)
            if (Str::contains($module, '/')) {
                [$module, $submodule] = explode('/', $module, 2);
                $existing_submodules = Config::get("poller_submodules.$module", []);
                $existing_submodules[] = $submodule;
                Config::set("poller_submodules.$module", $existing_submodules);
            }

            if (! Module::exists($module) && ! Module::legacyPollingExists($module)) {
                unset($this->module_override[$index]);
                continue;
            }

            Config::set("poller_modules.$module", 1);
        }

        $this->printModules();
    }

    private function printDeviceInfo(?string $group): void
    {
        $this->logger->info(sprintf(<<<'EOH'
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s

EOH, $this->device->hostname, $group ? " ($group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));
    }

    private function printModules(): void
    {
        $modules = array_map(function ($module) {
            $submodules = Config::get("poller_submodules.$module");

            return $module . ($submodules ? '(' . implode(',', $submodules) . ')' : '');
        }, array_keys(Config::get('poller_modules', [])));

        $this->logger->debug('Override poller modules: ' . implode(', ', $modules));
    }

    private function printHeader(): void
    {
        if (Debug::isEnabled() || Debug::isVerbose()) {
            $this->logger->info(Version::get()->header());
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

        $this->os->enableGraph('poller_perf');
    }
}
