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
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LibreNMS\Enum\Alert;
use LibreNMS\Exceptions\PollerException;
use LibreNMS\Modules\LegacyModule;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Git;
use LibreNMS\Util\StringHelpers;
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

    public function poll(): int
    {
        $polled = 0;
        $this->printHeader();

        if (Debug::isEnabled()) {
            \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
        }

        $this->logger->info("Starting polling run:\n");

        foreach ($this->buildDeviceQuery()->pluck('device_id') as $device_id) {
            $this->initDevice($device_id);
            PollingDevice::dispatch($this->device);
            $this->os = OS::make($this->deviceArray);

            $helper = new ConnectivityHelper($this->device);
            $helper->saveMetrics();

            $measurement = Measurement::start('poll');
            $measurement->manager()->checkpoint(); // don't count previous stats

            if ($helper->isUp()) {
                $this->pollModules();
            }
            $measurement->end();

            if (empty($this->module_override)) {
                // record performance
                $measurement->manager()->record('device', $measurement);
                $this->device->last_polled = Carbon::now();
                $this->device->last_ping_timetaken = $measurement->getDuration();
                app('Datastore')->put($this->deviceArray, 'poller-perf', [
                    'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                    'module' => 'ALL',
                ], [
                    'poller' => $measurement->getDuration(),
                ]);
                $this->os->enableGraph('poller_perf');

                if ($helper->canPing()) {
                    $this->os->enableGraph('ping_perf');
                }

                $this->os->persistGraphs();
                $this->logger->info(sprintf("Enabled graphs (%s): %s\n\n",
                    $this->device->graphs->count(),
                    $this->device->graphs->pluck('graph')->implode(' ')
                ));
            }

            $this->device->save();
            $polled++;

            DevicePolled::dispatch($this->device);

            $this->logger->info(sprintf("\n>>> Polled %s (%s) in %0.3f seconds <<<",
                $this->device->displayName(),
                $this->device->device_id,
                $measurement->getDuration()));

            // check if the poll took too long and log an event
            if ($measurement->getDuration() > Config::get('rrd.step')) {
                \Log::event('Polling took longer than ' . round(Config::get('rrd.step') / 60, 2) .
                    ' minutes!  This will cause gaps in graphs.', $this->device, 'system', 5);
            }
        }

        return $polled;
    }

    private function pollModules(): void
    {
        $this->filterModules();

        // update $device array status
        $this->deviceArray['status'] = $this->device->status;
        $this->deviceArray['status_reason'] = $this->device->status_reason;

        // import legacy garbage
        include_once base_path('includes/functions.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/polling/functions.inc.php');
        include_once base_path('includes/snmp.inc.php');
        include_once base_path('includes/datastore.inc.php');  // remove me

        foreach (Config::get('poller_modules') as $module => $module_status) {
            if ($this->isModuleEnabled($module, $module_status)) {
                $start_memory = memory_get_usage();
                $module_start = microtime(true);
                $this->logger->info("\n#### Load poller module $module ####");

                try {
                    $module_class = StringHelpers::toClass($module, '\\LibreNMS\\Modules\\');
                    $instance = class_exists($module_class) ? new $module_class : new LegacyModule($module);
                    $instance->poll($this->os);
                } catch (Throwable $e) {
                    // isolate module exceptions so they don't disrupt the polling process
                    $this->logger->error("%rError polling $module module for {$this->device->hostname}.%n " . $e->getMessage() . PHP_EOL . $e->getTraceAsString(), ['color' => true]);
                    \Log::event("Error polling $module module. Check log file for more details.", $this->device, 'poller', Alert::ERROR);
                }

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

    private function isModuleEnabled(string $module, bool $global_status): bool
    {
        if (! empty($this->module_override)) {
            if (in_array($module, $this->module_override)) {
                $this->logger->debug("Module $module manually enabled");

                return true;
            }

            return false;
        }

        $os_module_status = Config::get("os.{$this->device->os}.poller_modules.$module");
        $device_attrib = $this->device->getAttrib('poll_' . $module);
        $this->logger->debug(sprintf('Modules status: Global %s OS %s Device %s',
            $global_status ? '+' : '-',
            $os_module_status === null ? ' ' : ($os_module_status ? '+' : '-'),
            $device_attrib === null ? ' ' : ($device_attrib ? '+' : '-')
        ));

        if ($device_attrib
            || ($os_module_status && $device_attrib === null)
            || ($global_status && $os_module_status === null && $device_attrib === null)) {
            return true;
        }

        $reason = $device_attrib !== null ? 'by device'
                : ($os_module_status === null || $os_module_status ? 'globally' : 'by OS');
        $this->logger->debug("Module [ $module ] disabled $reason");

        return false;
    }

    private function moduleExists(string $module): bool
    {
        return class_exists(StringHelpers::toClass($module, '\\LibreNMS\\Modules\\'))
            || is_file("includes/polling/$module.inc.php");
    }

    private function buildDeviceQuery(): Builder
    {
        $query = Device::query();

        if (empty($this->device_spec)) {
            throw new PollerException('Invalid device spec');
        } elseif ($this->device_spec == 'all') {
            return $query;
        } elseif ($this->device_spec == 'even') {
            return $query->where(DB::raw('device_id % 2'), 0);
        } elseif ($this->device_spec == 'odd') {
            return $query->where(DB::raw('device_id % 2'), 1);
        } elseif (is_numeric($this->device_spec)) {
            return $query->where('device_id', $this->device_spec);
        } elseif (Str::contains($this->device_spec, '*')) {
            return $query->where('hostname', 'like', str_replace('*', '%', $this->device_spec));
        }

        return $query->where('hostname', $this->device_spec);
    }

    private function initDevice(int $device_id): void
    {
        \DeviceCache::setPrimary($device_id);
        $this->device = \DeviceCache::getPrimary();
        $this->device->ip = $this->device->overwrite_ip ?: Dns::lookupIp($this->device);

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
            mkdir($host_rrd);
            $this->logger->info("Created directory : $host_rrd");
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

            if (! $this->moduleExists($module)) {
                unset($this->module_override[$index]);
                continue;
            }

            Config::set("poller_modules.$module", 1);
        }

        $this->printModules();
    }

    private function filterModules(): void
    {
        if ($this->device->snmp_disable) {
            // only non-snmp modules
            Config::set('poller_modules', array_intersect_key(Config::get('poller_modules'), [
                'availability' => true,
                'ipmi' => true,
                'unix-agent' => true,
            ]));
        } else {
            // we always want the core module to be included, prepend it
            Config::set('poller_modules', ['core' => true] + Config::get('poller_modules'));
        }
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
            $version = \LibreNMS\Util\Version::get();
            $this->logger->info(sprintf(<<<'EOH'
===================================
Version info:
Commit SHA: %s
Commit Date: %s
DB Schema: %s
PHP: %s
MySQL: %s
RRDTool: %s
SNMP: %s
==================================
EOH,
                Git::localCommit(),
                Git::localDate(),
                vsprintf('%s (%s)', $version->database()),
                phpversion(),
                \LibreNMS\DB\Eloquent::isConnected() ? \LibreNMS\DB\Eloquent::version() : '?',
                $version->rrdtool(),
                $version->netSnmp()
            ));
        }
    }
}
