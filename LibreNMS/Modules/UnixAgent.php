<?php

/**
 * UnixAgent.php
 *
 * Poll the check_mk compatible unix agent
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Application;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Package;
use App\Models\Process;
use App\Models\Sensor;
use ErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

/**
 * Parsed agent output, keyed by section name.
 * Sections with a dash are nested (<<<munin-cpu>>> is ['munin']['cpu']) and known applications are copied to 'app'.
 *
 * @phpstan-type AgentData array{
 *     app?: array<string, string|array<mixed>>,
 *     dmi?: array<string, string>,
 *     munin?: array<string, string>,
 *     hddtemp?: string,
 *     drbd?: string,
 *     ps?: string,
 *     'ps:sep(9)'?: string,
 *     rpm?: string,
 *     dpkg?: string,
 *     pacman?: string,
 *     ...<string, string|array<string, string>>
 * }
 * @phpstan-type ProcessData array{pid: string, user: string, vsz: int|string, rss: string, cputime: string, command: string}
 */
class UnixAgent implements Module
{
    /**
     * Agent sections that are applications and will be automatically enabled
     */
    private const AGENT_APPS = [
        'apache',
        'bind',
        'ceph',
        'mysql',
        'nginx',
        'os-updates',
        'php-fpm',
        'powerdns',
        'powerdns-recursor',
        'proxmox',
        'redis',
        'rrdcached',
        'tinydns',
        'gpsd',
    ];

    private const CACHE_KEY = 'unix_agent_data.';

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->unixAgentIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();

        $start = microtime(true);
        $raw = $this->fetch($device);
        $agent_time = round((microtime(true) - $start) * 1000);

        if (empty($raw)) {
            Cache::driver('array')->put(self::CACHE_KEY . $device->device_id, []);

            return;
        }

        Log::info("Agent execution time: {$agent_time}ms");
        $datastore->put($os->getDeviceArray(), 'agent', [
            'rrd_def' => RrdDefinition::make()->addDataset('time', 'GAUGE', 0),
        ], [
            'time' => $agent_time,
        ]);
        $os->enableGraph('agent');

        $agent_data = $this->parse($raw);
        Log::debug('Agent data', $agent_data);

        $this->pollPackages($device, $agent_data);
        $this->pollMunin($os, $datastore, (array) ($agent_data['munin'] ?? []));
        $this->pollHddtemp($os, $datastore, (string) ($agent_data['hddtemp'] ?? ''));
        $this->pollProcesses($device, $agent_data);

        $apps = $this->discoverApplications($device, $agent_data);
        if (! empty($apps)) {
            $agent_data['app'] = $apps;
        }

        $this->updateHardwareFromDmi($device, (array) ($agent_data['dmi'] ?? []));

        // store results for the applications module
        Cache::driver('array')->put(self::CACHE_KEY . $device->device_id, $agent_data);
    }

    /**
     * Get the agent data parsed while polling the given device.
     * Empty if the unix-agent module did not run or the agent did not respond.
     *
     * @return AgentData
     */
    public static function getData(int $device_id): array
    {
        return Cache::driver('array')->get(self::CACHE_KEY . $device_id, []);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->processes()->exists()
            || $device->packages()->exists()
            || $device->muninPlugins()->exists()
            || $device->sensors()->where('poller_type', 'agent')->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        $plugin_ids = $device->muninPlugins()->pluck('mplug_id');
        DB::table('munin_plugins_ds')->whereIn('mplug_id', $plugin_ids)->delete();

        return $device->processes()->delete()
            + $device->packages()->delete()
            + $device->muninPlugins()->delete()
            + $device->sensors()->where('poller_type', 'agent')->delete();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dump(Device $device, string $type): ?array
    {
        return null; // no test data
    }

    private function fetch(Device $device): ?string
    {
        $config = $device->polling()->unixAgent();
        $port = $config->port;

        try {
            $target = Rewrite::addIpv6Brackets($device->pollerTarget());
            $socket = @fsockopen($target, $port, $errno, $errstr, $config->timeout);
        } catch (ErrorException $e) {
            Log::error($e->getMessage()); // usually connection timed out

            return null;
        }

        if (! $socket) {
            Log::error("Connection to UNIX agent failed on port $port: $errstr");

            return null;
        }

        stream_set_timeout($socket, (int) LibrenmsConfig::get('unix-agent.read-timeout'));

        $raw = '';
        $info = stream_get_meta_data($socket);
        while (! feof($socket) && ! $info['timed_out']) {
            $raw .= fgets($socket, 128);
            $info = stream_get_meta_data($socket);
        }
        fclose($socket);

        if ($info['timed_out']) {
            Log::error("Connection to UNIX agent timed out during fetch on port $port");
        }

        return $raw;
    }

    /**
     * Split the raw agent output into sections.
     * Sections with a dash are nested: <<<munin-cpu>>> becomes $data['munin']['cpu']
     * Known applications are also placed under $data['app']
     *
     * @return AgentData
     */
    private function parse(string $raw): array
    {
        $agent_data = [];

        foreach (explode('<<<', $raw) as $section) {
            if (empty($section)) {
                continue;
            }

            [$name, $data] = array_pad(explode('>>>', $section, 2), 2, '');
            $data = trim($data);

            if (in_array($name, self::AGENT_APPS)) {
                $agent_data['app'][$name] = $data;
            }

            if (str_contains($name, '-')) {
                [$group, $sub] = explode('-', $name, 2);
                $agent_data[$group][$sub] = $data;
            } else {
                $agent_data[$name] = $data;
            }
        }

        if (isset($agent_data['dmi']) && is_string($agent_data['dmi'])) {
            $agent_data['dmi'] = $this->parseKeyValue($agent_data['dmi']);
        }

        return $agent_data;
    }

    /**
     * @return array<string, string>
     */
    private function parseKeyValue(string $data): array
    {
        $result = [];

        foreach (explode("\n", $data) as $line) {
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $result[$key] = trim($value);
            }
        }

        return $result;
    }

    /**
     * @param  AgentData  $agent_data
     */
    private function pollPackages(Device $device, array $agent_data): void
    {
        $managers = [
            'rpm' => function (string $line): Package {
                [$name, $version, $build, $arch, $size] = array_pad(explode(' ', $line), 5, '');

                return new Package([
                    'manager' => 'rpm',
                    'name' => $name,
                    'arch' => $arch,
                    'version' => $version,
                    'build' => $build,
                    'size' => $size,
                    'status' => true,
                ]);
            },
            'dpkg' => function (string $line): Package {
                [$name, $version, $arch, $size] = array_pad(explode(' ', $line), 4, '');

                return new Package([
                    'manager' => 'deb',
                    'name' => $name,
                    'arch' => $arch,
                    'version' => $version,
                    'build' => '',
                    'size' => Number::cast($size) * 1024,
                    'status' => true,
                ]);
            },
            'pacman' => function (string $line): Package {
                [$name, $version, $arch, $size] = array_pad(explode(' ', $line), 4, '');

                return new Package([
                    'manager' => 'pacman',
                    'name' => $name,
                    'arch' => $arch,
                    'version' => $version,
                    'build' => '',
                    'size' => (int) Number::toBytes($size),
                    'status' => true,
                ]);
            },
        ];

        foreach ($managers as $key => $parser) {
            if (empty($agent_data[$key])) {
                continue;
            }

            Log::info("$key packages");

            // mark all existing packages as removed, then flag the ones still installed
            $packages = $device->packages->each(function (Package $package): void {
                $package->status = false;
            })->keyBy->getCompositeKey();

            foreach (explode("\n", $agent_data[$key]) as $line) {
                $package = $parser($line);

                if (! $package->isValid()) {
                    continue; // failed to parse
                }

                $package_key = $package->getCompositeKey();
                if ($existing = $packages->get($package_key)) {
                    $existing->fill($package->attributesToArray());
                } else {
                    $packages->put($package_key, $package);
                }
            }

            $device->packages()->saveMany($packages->where('status', true));
            $packages->where('status', false)->each->delete();

            return; // only one package manager per device
        }
    }

    /**
     * @param  array<string, string>  $plugins
     */
    private function pollMunin(OS $os, DataStorageInterface $datastore, array $plugins): void
    {
        if (empty($plugins)) {
            return;
        }

        $device = $os->getDevice();

        foreach ($plugins as $plugin_type => $plugin_data) {
            Log::info("Munin Plugin: $plugin_type");

            $graph = [];
            $values = [];
            foreach (explode("\n", $plugin_data) as $line) {
                [$key, $value] = array_pad(explode(' ', $line, 2), 2, '');
                if (str_starts_with($key, 'graph_')) {
                    $graph[substr($key, 6)] = $value;
                } elseif (str_contains($key, '.')) {
                    [$metric, $field] = explode('.', $key, 2);
                    $values[$metric][$field] = $value;
                }
            }

            $plugin = $device->muninPlugins()->firstOrCreate(['mplug_type' => $plugin_type], [
                'mplug_category' => strtolower($graph['category'] ?? '') ?: 'general',
                'mplug_title' => $graph['title'] ?? null,
                'mplug_vlabel' => $graph['vlabel'] ?? null,
                'mplug_args' => $graph['args'] ?? null,
                'mplug_info' => $graph['info'] ?? null,
            ]);

            $data_sources = [];
            foreach ($values as $name => $data) {
                $type = ($data['type'] ?? '') ?: 'GAUGE';

                $datastore->put($os->getDeviceArray(), 'munin-plugins', [
                    'plugin' => $plugin_type,
                    'rrd_def' => RrdDefinition::make()->addDataset('val', $type),
                    'rrd_name' => "munin/{$plugin_type}_$name",
                ], [
                    'val' => $data['value'] ?? null,
                ]);

                $data_sources[] = [
                    'mplug_id' => $plugin->mplug_id,
                    'ds_name' => $name,
                    'ds_type' => $type,
                    'ds_label' => ($data['label'] ?? '') ?: $name,
                    'ds_cdef' => $data['cdef'] ?? '',
                    'ds_draw' => ($data['draw'] ?? '') ?: 'LINE1.5',
                    'ds_info' => $data['info'] ?? '',
                    'ds_extinfo' => $data['extinfo'] ?? '',
                    'ds_min' => $data['min'] ?? '',
                    'ds_max' => $data['max'] ?? '',
                    'ds_graph' => ($data['graph'] ?? '') ?: 'yes',
                    'ds_negative' => $data['negative'] ?? '',
                    'ds_warning' => $data['warning'] ?? '',
                    'ds_critical' => $data['critical'] ?? '',
                    'ds_colour' => $data['colour'] ?? '',
                    'ds_sum' => $data['sum'] ?? '',
                    'ds_stack' => $data['stack'] ?? '',
                    'ds_line' => $data['line'] ?? '',
                ];
            }

            // unique on mplug_id + ds_name, existing data sources are left as is
            DB::table('munin_plugins_ds')->insertOrIgnore($data_sources);
        }
    }

    /**
     * hddtemp format: |/dev/sda|WDC WD1003FBYX|35|C||/dev/sdb|...|
     */
    private function pollHddtemp(OS $os, DataStorageInterface $datastore, string $hddtemp): void
    {
        $hddtemp = trim($hddtemp, '|');
        if ($hddtemp === '') {
            return; // hddtemp not installed or not responding, leave existing sensors alone
        }

        $device = $os->getDevice();

        // capture current values before sync overwrites them
        $previous = $device->sensors()
            ->where('sensor_class', 'temperature')
            ->where('poller_type', 'agent')
            ->pluck('sensor_current', 'sensor_index');

        $temperatures = [];
        $sensor_discovery = new \App\Discovery\Sensor($device);
        foreach (explode('||', $hddtemp) as $index => $disk) {
            [$block_device, $descr, $temperature] = array_pad(explode('|', $disk, 4), 3, '');
            $temperature = trim(str_replace('C', '', $temperature));
            $sensor_index = $index + 1;

            $sensor = new Sensor([
                'poller_type' => 'agent',
                'sensor_class' => 'temperature',
                'device_id' => $device->device_id,
                'sensor_oid' => '',
                'sensor_index' => $sensor_index,
                'sensor_type' => 'hddtemp',
                'sensor_descr' => "$block_device: $descr",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'rrd_type' => 'GAUGE',
            ]);

            if (is_numeric($temperature)) {
                $temperatures[$sensor_index] = (float) $temperature;
                $sensor->sensor_current = (float) $temperature;
            } elseif (! $previous->has($sensor_index)) {
                continue; // no temperature (SLP, NA, ERR), only keep sensors that already exist
            }

            $sensor_discovery->discover($sensor);
        }

        $sensors = $sensor_discovery->sync(sensor_class: 'temperature', poller_type: 'agent');

        foreach ($sensors as $sensor) {
            if (isset($temperatures[$sensor->sensor_index])) {
                $this->updateSensor($os, $datastore, $sensor, $previous->get($sensor->sensor_index));
            }
        }
    }

    private function updateSensor(OS $os, DataStorageInterface $datastore, Sensor $sensor, ?float $previous): void
    {
        $value = $sensor->sensor_current;
        Log::info("$sensor->sensor_descr: $value {$sensor->unit()}");

        $rrd_key = LibrenmsConfig::getOsSetting($os->getDevice()->os, 'sensor_descr') ? $sensor->sensor_descr : $sensor->sensor_index;
        $datastore->put($os->getDeviceArray(), 'sensor', [
            'sensor_class' => $sensor->sensor_class,
            'sensor_type' => $sensor->sensor_type,
            'sensor_descr' => $sensor->sensor_descr,
            'sensor_index' => $sensor->sensor_index,
            'rrd_name' => ['sensor', $sensor->sensor_class, $sensor->sensor_type, $rrd_key],
            'rrd_def' => RrdDefinition::make()->addDataset('sensor', $sensor->rrd_type),
        ], [
            'sensor' => $value,
        ]);

        if ($previous === null || $value == $previous) {
            return;
        }

        $sensor->sensor_prev = $previous;
        $sensor->save();

        if ($sensor->sensor_alert) {
            $class = $sensor->classDescr();
            $unit = $sensor->unit();

            if ($sensor->sensor_limit_low !== null && $previous > $sensor->sensor_limit_low && $value < $sensor->sensor_limit_low) {
                Eventlog::log("$class under threshold: $value $unit (< $sensor->sensor_limit_low $unit)", $sensor->device_id, $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
            } elseif ($sensor->sensor_limit !== null && $previous < $sensor->sensor_limit && $value > $sensor->sensor_limit) {
                Eventlog::log("$class above threshold: $value $unit (> $sensor->sensor_limit $unit)", $sensor->device_id, $sensor->sensor_class, Severity::Warning, $sensor->sensor_id);
            }
        }
    }

    /**
     * @param  AgentData  $agent_data
     */
    private function pollProcesses(Device $device, array $agent_data): void
    {
        if (! empty($agent_data['ps'])) {
            $processes = $this->parseUnixProcesses($agent_data['ps']);
        } elseif (! empty($agent_data['ps:sep(9)'])) {
            $processes = $this->parseWindowsProcesses($agent_data['ps:sep(9)']);
        } else {
            return;
        }

        Log::info('Processes: ' . $processes->count());

        $device->processes()->delete();
        foreach ($processes->chunk(1000) as $chunk) {
            Process::insert($chunk->map(fn ($process) => ['device_id' => $device->device_id] + $process)->all());
        }
    }

    /**
     * format: (user,vsz,rss,cputime,pid) command
     *
     * @return Collection<int, ProcessData>
     */
    private function parseUnixProcesses(string $data): Collection
    {
        $processes = new Collection;

        foreach (explode("\n", $data) as $line) {
            if (preg_match('/\((.*),([0-9]*),([0-9]*),([-0-9:.]*),([0-9]*)\) (.+)/', $line, $matches)) {
                [, $user, $vsz, $rss, $cputime, $pid, $command] = $matches;
                $processes->push([
                    'pid' => $pid,
                    'user' => $user,
                    'vsz' => $vsz,
                    'rss' => $rss,
                    'cputime' => $cputime,
                    'command' => $command,
                ]);
            }
        }

        return $processes;
    }

    /**
     * format: (user,VirtualSize,WorkingSetSize,0,ProcessId,PageFileUsage,UserModeTime,KernelModeTime,HandleCount,ThreadCount[,uptime])\tname
     *
     * @return Collection<int, ProcessData>
     */
    private function parseWindowsProcesses(string $data): Collection
    {
        $processes = new Collection;

        foreach (explode("\n", $data) as $line) {
            if (! preg_match('/\(([^,;]+),([0-9]*),([0-9]*),([0-9]*),([0-9]*),([0-9]*),([0-9]*),([0-9]*),([0-9]*),([0-9]*)?,?([0-9]*)\)(.*)/', $line, $matches)) {
                continue;
            }

            [, $user, , $working_set, , $pid, $page_file, $user_time, $kernel_time] = $matches;
            $name = trim($matches[12]);

            if (empty($name)) {
                continue;
            }

            // times are in 100ns units
            $seconds = intdiv((int) $user_time + (int) $kernel_time, 10000000);
            $days = intdiv($seconds, 86400);
            $cputime = ($days > 0 ? "$days-" : '') . sprintf('%02d:%02d:%02d', intdiv($seconds, 3600) % 24, intdiv($seconds, 60) % 60, $seconds % 60);

            $processes->push([
                'pid' => $pid,
                'user' => $user,
                'vsz' => (int) $page_file + (int) $working_set,
                'rss' => $working_set,
                'cputime' => $cputime,
                'command' => $name,
            ]);
        }

        return $processes;
    }

    /**
     * Enable applications found in the agent data.
     * memcached and drbd are expanded into per-instance data for their application pollers.
     *
     * @param  AgentData  $agent_data
     * @return array<string, string|array<mixed>>
     */
    private function discoverApplications(Device $device, array $agent_data): array
    {
        $apps = $agent_data['app'] ?? [];

        foreach (array_keys($apps) as $app_type) {
            if (in_array($app_type, self::AGENT_APPS)) {
                $this->enableApplication($device, $app_type);
            }
        }

        if (! empty($apps['memcached']) && is_string($apps['memcached'])) {
            $memcached = json_decode($apps['memcached'], true);
            $apps['memcached'] = is_array($memcached) ? $memcached : [];
            foreach (array_keys($apps['memcached']) as $instance) {
                $this->enableApplication($device, 'memcached', (string) $instance);
            }
        }

        if (! empty($agent_data['drbd'])) {
            $drbd = [];
            foreach (explode("\n", $agent_data['drbd']) as $line) {
                [$drbd_dev, $drbd_data] = array_pad(explode(':', $line, 2), 2, '');
                if (str_starts_with($drbd_dev, 'drbd')) {
                    $drbd[$drbd_dev] = $drbd_data;
                    $this->enableApplication($device, 'drbd', $drbd_dev);
                }
            }
            $apps['drbd'] = $drbd;
        }

        return $apps;
    }

    /**
     * Enable an application if it has never been enabled.
     * Applications deleted by the user are not re-enabled.
     */
    private function enableApplication(Device $device, string $app_type, ?string $instance = null): void
    {
        $query = Application::withTrashed()->where('device_id', $device->device_id)->where('app_type', $app_type);
        if ($instance !== null) {
            $query->where('app_instance', $instance);
        }

        if ($query->doesntExist()) {
            Log::info("Found new application '$app_type'" . ($instance ? " $instance" : ''));
            $device->applications()->create([
                'app_type' => $app_type,
                'app_status' => '',
                'app_instance' => $instance ?? '',
            ]);
        }
    }

    /**
     * @param  array<string, string>  $dmi
     */
    private function updateHardwareFromDmi(Device $device, array $dmi): void
    {
        if (empty($dmi)) {
            return;
        }

        // prefer system values unless they are the generic placeholder, then fall back to baseboard
        $value = fn (string $system_key, string $baseboard_key, string $generic) => isset($dmi[$system_key]) && $dmi[$system_key] !== $generic
            ? $dmi[$system_key]
            : $dmi[$baseboard_key] ?? '';

        $manufacturer = $value('system-manufacturer', 'baseboard-manufacturer', 'System Manufacturer');
        $product = $value('system-product-name', 'baseboard-product-name', 'System Product Name');
        if ($manufacturer || $product) {
            $device->hardware = str_replace([
                ' Computer Corporation',
                ' Corporation',
                ' Inc.',
            ], '', implode(' ', array_filter([$manufacturer, $product])));
        }

        $serial = $value('system-serial-number', 'baseboard-serial-number', 'System Serial Number');
        if ($serial) {
            $device->serial = $serial;
        }

        $device->save();
    }
}
