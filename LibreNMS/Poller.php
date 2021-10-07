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

use App\Models\Device;
use App\Models\DeviceGraph;
use App\Polling\Measure\Measurement;
use App\Polling\Measure\MeasurementManager;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\PollerException;
use LibreNMS\Modules\LegacyModule;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Git;
use LibreNMS\Util\StringHelpers;
use Log;

class Poller extends PollingCommon
{
    /** @var string */
    private $device_spec;
    /** @var array */
    private $module_override;
    private $output;
    private $polled = 0;
    private $unreachable = 0;


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

    public function __construct(string $device_spec, array $module_override, \Illuminate\Console\OutputStyle $output)
    {
        $this->device_spec = $device_spec;
        $this->module_override = $module_override;
        $this->output = $output;
        $this->parseModules();
    }

    public function poll(): int
    {
        $polled = 0;
        $this->printHeader();

        if (Debug::isEnabled()) {
            \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
        }

        $this->output->writeln('Starting polling run:');
        $this->output->newLine();

        foreach ($this->buildDeviceQuery()->pluck('device_id') as $device_id) {
            $this->initDevice($device_id);
            $this->os = OS::make($this->deviceArray);

            $helper = new ConnectivityHelper($this->device);
            $helper->saveMetrics();

            $measurement = Measurement::start('poll');
            $measurement->manager()->checkpoint(); // don't count previous stats

            if ($helper->isUp()) {
                $this->pollModules();
            }

            // record performance
            $measurement->manager()->record('poller', $measurement->end());
            $this->device->last_polled = time();
            $this->device->last_ping_timetaken = $measurement->getDuration();
            app('Datastore')->put($this->deviceArray, 'poller-perf', [
                'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                'module' => 'ALL',
            ], [
                'poller' => $measurement->getDuration(),
            ]);
            $this->os->enableGraph('poller_modules_perf');

            $this->output->write('Enabling graphs: ');
            if ($helper->canPing()) {
                $this->os->enableGraph('ping_perf');
            }
            DeviceGraph::deleted(function ($graph) {
                $this->output->write('-');
            });
            DeviceGraph::created(function ($graph) {
                $this->output->write('+');
            });
            $this->os->persistGraphs();

            $this->device->save();
            $polled++;

            $this->output->newLine(2);
            $this->output->writeln(sprintf('Polled in %s seconds', $measurement->getDuration()));

            // check if the poll took too long and log an event
            if ($measurement->getDuration() > Config::get('rrd.step')) {
                Log::event('Polling took longer than ' . round(Config::get('rrd.step') / 60, 2) .
                    ' minutes!  This will cause gaps in graphs.', $this->device, 'system', 5);
            }
        }

        return $polled;
    }

    private function pollModules()
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
                $this->output->newLine();
                $this->output->writeln("#### Load poller module $module ####");

                try {
                    $module_class = StringHelpers::toClass($module, '\\LibreNMS\\Modules\\');
                    $instance = class_exists($module_class) ? new $module_class : new LegacyModule($module);
                    $instance->poll($this->os);
                } catch (Exception $e) {
                    // isolate module exceptions so they don't disrupt the polling process
                    $this->output->writeln($e->getTraceAsString());
                    $this->output->error("Error in $module module. " . $e->getMessage());
                    Log::error("Error in $module module. " . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
                }

                $this->saveModulePerformance($module, $module_start, $start_memory);
                app(MeasurementManager::class)->printChangedStats();
                $this->output->writeln("#### Unload poller module $module ####");
                $this->output->newLine();
            }
        }
    }

    private function saveModulePerformance($module, $start_time, $start_memory): void
    {
        $module_time = microtime(true) - $start_time;
        $module_mem = (memory_get_usage() - $start_memory);

        $this->output->newLine();
        $this->output->writeln(sprintf(">> Runtime for poller module '%s': %.4f seconds with %s bytes", $module, $module_time, $module_mem));

        app('Datastore')->put($this->deviceArray, 'poller-perf', [
            'module' => $module,
            'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
            'rrd_name' => ['poller-perf', $module],
        ], [
            'poller' => $module_time,
        ]);
        $this->os->enableGraph('poller_perf');
    }

    private function isModuleEnabled($module, $global_status): bool
    {
        $os_module_status = Config::get("os.{$this->device->os}.poller_modules.$module");
        $device_attrib = $this->device->getAttrib('poll_' . $module);
        Log::debug(sprintf('Modules status: Global %s OS %s Device %s',
            isset($module_status) ? ($module_status ? '+' : '-') : ' ',
            isset($os_module_status) ? ($os_module_status ? '+' : '-') : ' ',
            isset($device_attrib) ? ($device_attrib ? '+' : '-') : ' '
        ));

        if ($device_attrib
            || ($os_module_status && ! isset($device_attrib))
            || ($global_status && ! isset($os_module_status) && ! isset($device_attrib))) {
            return true;
        }

        $reason = (isset($device_attrib) && ! $device_attrib) ? 'by device'
                : (isset($os_module_status) && ! $os_module_status ? 'by OS' : 'globally');
        $this->output->writeln("Module [ $module ] disabled $reason");
        return false;
    }

    private function moduleExists(string $module): bool
    {
        return is_file("includes/polling/$module.inc.php");
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

    private function initDevice($device_id)
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
            $this->output->writeln("Created directory : $host_rrd");
        }
    }

    private function parseModules()
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

    private function printDeviceInfo($group)
    {
        $this->output->writeln(sprintf(<<<EOH
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s
EOH, $this->device->hostname, $group ? " ($group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));
        $this->output->newLine();
    }

    private function printModules()
    {
        $modules = array_map(function ($module) {
            $submodules = Config::get("poller_submodules.$module");

            return $module . ($submodules ? '(' . implode(',', $submodules) . ')' : '');
        }, array_keys(Config::get("poller_modules", [])));

        Log::debug("Override poller modules: " . implode(', ', $modules));
    }

    private function printHeader()
    {
        if (Debug::isEnabled() || Debug::isVerbose()) {
            $version = \LibreNMS\Util\Version::get();
            $this->output->writeln(sprintf(<<<EOH
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
