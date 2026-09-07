<?php

/**
 * ModuleTester.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use DeviceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;

class ModuleTestHelper
{
    private bool $quiet = false;
    private readonly string $variant;
    private readonly string $snmprec_file;
    private readonly string $json_file;
    private readonly string $snmprec_dir;
    private readonly string $json_dir;
    private readonly string $file_name;
    private array $discovery_module_output = [];
    private array $poller_module_output = [];
    private string $discovery_output;
    private string $poller_output;

    // Definitions
    // ignore these when dumping all modules
    private array $exclude_from_all = ['arp-table', 'availability', 'fdb-table'];

    /**
     * ModuleTester constructor.
     *
     * @param  ModuleList  $modules
     * @param  string  $os
     * @param  string  $variant
     */
    public function __construct(private readonly ModuleList $modules, string $os, string $variant = '')
    {
        $this->variant = strtolower($variant);

        // preset the file names
        if ($variant) {
            $variant = '_' . $this->variant;
        }
        $install_dir = LibrenmsConfig::get('install_dir');
        $this->file_name = $os . $variant;
        $this->snmprec_dir = "$install_dir/tests/snmpsim/";
        $this->snmprec_file = $this->snmprec_dir . $this->file_name . '.snmprec';
        $this->json_dir = "$install_dir/tests/data/";
        $this->json_file = $this->json_dir . $this->file_name . '.json';

        // never store time series data
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('hide_rrd_disabled', true);
        LibrenmsConfig::set('influxdb.enable', false);
        LibrenmsConfig::set('influxdbv2.enable', false);
        LibrenmsConfig::set('graphite.enable', false);
        LibrenmsConfig::set('prometheus.enable', false);
        LibrenmsConfig::set('kafka.enable', false);
    }

    public function setQuiet(bool $quiet = true): void
    {
        $this->quiet = $quiet;
    }

    /**
     * Generate a list of os containing test data for $modules (an empty array means all)
     *
     * Returns an array indexed by the basename ($os or $os_$variant)
     * Each entry contains [$os, $variant, $valid_modules]
     * $valid_modules is an array of selected modules this os has test data for
     *
     * @param  string[]  $modules
     * @return array{string, string, array<string, bool|string[]>}[]
     *
     * @throws InvalidModuleException
     */
    public static function findOsWithData(array $modules = [], ?string $os_filter = null, ?string $base_path = null): array
    {
        $base_path ??= (function_exists('base_path') ? base_path() : realpath(__DIR__ . '/../..'));
        $cacheKey = 'os_modules_' . md5(implode(',', $modules) . '|' . ($os_filter ?? '') . '|' . $base_path);
        $dataPath = $base_path . '/tests/data';

        return DataProviderCache::remember($cacheKey, $dataPath, function () use ($modules, $os_filter, $base_path) {
            $os_list = [];

            foreach (glob($base_path . '/tests/data/*.json') as $file) {
                $base_name = basename($file, '.json');
                [$os, $variant] = self::extractVariant($file, $base_path);

                if ($os_filter != '' && $os_filter != $os) {
                    continue;
                }

                // calculate valid modules
                $decoded = json_decode(file_get_contents($file), true);

                if (json_last_error()) {
                    echo "Invalid json data: $base_name\n";
                    exit(1);
                }

                $data_modules = array_keys($decoded);

                if (empty($modules)) {
                    $valid_modules = $data_modules;
                } else {
                    $valid_modules = array_intersect($modules, $data_modules);
                }

                if (empty($valid_modules)) {
                    continue;  // no test data for selected modules
                }

                try {
                    $os_list[$base_name] = [
                        $os,
                        $variant,
                        self::resolveModuleDependencies($valid_modules),
                    ];
                } catch (InvalidModuleException $e) {
                    throw new InvalidModuleException('Invalid module ' . $e->getMessage() . " in $os $variant");
                }
            }

            return $os_list;
        });
    }

    /**
     * Given a json filename or basename, extract os and variant
     *
     * @param  string  $os_file  Either a filename or the basename
     * @return array{string, string} [$os, $variant]
     */
    public static function extractVariant(string $os_file, ?string $base_path = null): array
    {
        $full_name = basename($os_file, '.json');
        $resource_path = rtrim($base_path ? rtrim($base_path, '/') . '/resources' : resource_path(), '/');

        if (! str_contains($full_name, '_')) {
            return [$full_name, ''];
        } elseif (is_file("$resource_path/definitions/os_detection/$full_name.yaml")) {
            return [$full_name, ''];
        } else {
            [$rvar, $ros] = explode('_', strrev($full_name), 2);

            return [strrev($ros), strrev($rvar)];
        }
    }

    /**
     * Generate a module list.  Try to take dependencies into account.
     * Probably needs to be more robust
     *
     * @param  array  $modules
     * @return array<string, bool|string[]>
     *
     * @throws InvalidModuleException
     */
    private static function resolveModuleDependencies(array $modules): array
    {
        // generate a full list of modules
        $full_list = [];
        foreach ($modules as $index => $module) {
            $module = is_string($index) ? $index : $module;

            // only allow valid modules
            if (! Module::exists($module)) {
                throw new InvalidModuleException("Invalid module name: $module");
            }

            foreach (Module::fromName($module)->dependencies() as $dependency) {
                $full_list[$dependency] = true;
            }

            $full_list[$module] = true;
        }

        return $full_list;
    }

    private function qPrint(mixed $var): void
    {
        if ($this->quiet) {
            return;
        }

        if (is_array($var)) {
            print_r($var);
        } else {
            echo $var;
        }
    }

    /**
     * Run discovery and polling against snmpsim data and create a database dump.
     *
     * @throws FileNotFoundException
     */
    public function generateTestData(string $snmpSimIp, int $snmpSimPort): ?array
    {
        global $device;
        LibrenmsConfig::set('rrd.enable', false); // disable rrd
        LibrenmsConfig::set('rrdtool_version', '1.7.2'); // don't detect rrdtool version, rrdtool is not install on ci

        // don't allow external DNS queries that could fail
        try {
            app()->bind(AutonomousSystem::class, function ($app, $parameters) {
                $asn = $parameters['asn'] ?? '?';
                $mock = \Mockery::mock(AutonomousSystem::class);
                $mock->shouldReceive('name')->withAnyArgs()->zeroOrMoreTimes()->andReturnUsing(fn (
                ) => "AS$asn-MOCK-TEXT");

                return $mock;
            });
        } catch (\ReflectionException) {
            Log::error('Failed to mock AutonomousSystem');

            return null;
        }

        if (! is_file($this->snmprec_file)) {
            throw new FileNotFoundException("$this->snmprec_file does not exist!");
        }

        // Remove existing device in case it didn't get removed previously, if we're not running in CI
        if (! getenv('CI') && DeviceCache::get($snmpSimIp)->exists) {
            Device::query()->where('hostname', $snmpSimIp)->get()->each->delete();
            DeviceCache::flush();
        }

        // Add the test device
        try {
            $new_device = new Device([
                'hostname' => $snmpSimIp,
                'snmpver' => 'v2c',
                'transport' => 'udp',
                'community' => $this->file_name,
                'port' => $snmpSimPort,
                'disabled' => 1, // disable to block normal pollers
            ]);
            (new ValidateDeviceAndCreate($new_device, true))->execute();
            $device_id = $new_device->device_id;

            $this->qPrint("Added device: $device_id\n");
        } catch (\Exception $e) {
            echo $this->file_name . ': ' . $e->getMessage() . PHP_EOL;

            return null;
        }

        // Populate the device variable
        $device = DeviceCache::refresh((int) $device_id);
        DeviceCache::setPrimary($device_id);

        $data = [];  // array to hold dumped data

        // Run discovery
        $save_debug = Debug::isEnabled();
        $save_vedbug = Debug::isVerbose();
        $log_driver = Log::getDefaultDriver();

        if ($this->quiet) {
            Debug::setOnly();
            Debug::setVerbose();
            Debug::enableCliDebugOutput();
        }
        ob_start();
        Log::setDefaultDriver('stdout');

        (new DiscoverDevice($device_id, $this->modules))->handle();

        $this->discovery_output = ob_get_contents();
        if ($this->quiet) {
            Debug::setOnly($save_debug);
            Debug::setVerbose($save_vedbug);
            Debug::disableCliDebugOutput();
        } else {
            ob_flush();
        }
        Log::setDefaultDriver($log_driver);
        ob_end_clean();

        $this->qPrint(PHP_EOL);

        // Parse discovered modules
        $this->discovery_module_output = $this->extractModuleOutput($this->discovery_output, 'discovery');
        $discovered_modules = array_keys($this->discovery_module_output);

        // Dump the discovered data
        $data = array_merge_recursive($data, $this->dumpDb($device['device_id'], $discovered_modules, 'discovery'));
        DeviceCache::get($device_id)->refresh(); // refresh the device

        // Run the poller
        if ($this->quiet) {
            Debug::setOnly();
            Debug::setVerbose();
            Debug::enableCliDebugOutput();
        }
        ob_start();
        Log::setDefaultDriver('stdout');

        (new PollDevice($device_id, $this->modules))->handle();

        $this->poller_output = ob_get_contents();
        if ($this->quiet) {
            Debug::setOnly($save_debug);
            Debug::setVerbose($save_vedbug);
            Debug::disableCliDebugOutput();
        } else {
            ob_flush();
        }
        Log::setDefaultDriver($log_driver);
        ob_end_clean();

        // Parse polled modules
        $this->poller_module_output = $this->extractModuleOutput($this->poller_output, 'poller');
        $polled_modules = array_keys($this->poller_module_output);

        // Dump polled data
        $data = array_merge_recursive($data, $this->dumpDb($device_id, $polled_modules, 'poller'));

        // Remove the test device, if we're not running in CI
        if (! getenv('CI') && $device['hostname'] == $snmpSimIp) {
            // we don't need the debug from this
            Debug::set(false);
            delete_device($device_id);
        }

        return $data;
    }

    /**
     * @param  string  $output  poller or discovery output
     * @param  string  $type  poller|disco identified by "#### Load disco module" string
     * @return array
     */
    private function extractModuleOutput(string $output, string $type): array
    {
        $module_output = [];
        $module_start = "#### Load $type module ";
        $module_end = "#### Unload $type module %s ####";
        $parts = explode($module_start, $output);
        array_shift($parts); // throw away first part of output
        foreach ($parts as $part) {
            // find the module name
            $module = strtok($part, ' ');

            // insert the name into the end string
            $end = sprintf($module_end, $module);

            // find the end
            $end_pos = strrpos($part, $end) ?: -1;

            // save output, re-add bits we used for parsing
            $module_output[$module] = $module_start . substr($part, 0, $end_pos) . $end;
        }

        return $module_output;
    }

    /**
     * Dump the current database data for the module to an array
     * Mostly used for testing
     *
     * @param  int  $device_id  The test device id
     * @param  string[]  $modules  to capture data for (should be a list of modules that were actually run)
     * @param  string  $type  a key to store the data under the module key (usually discovery or poller)
     * @return array The dumped data keyed by module -> table
     */
    public function dumpDb(int $device_id, array $modules, string $type): array
    {
        $data = [];

        // don't dump some modules by default unless they are manually listed
        if (! $this->modules->hasOverride()) {
            $modules = array_diff($modules, $this->exclude_from_all);
        }

        // only dump data for the given modules (and modules that support dumping)
        foreach ($modules as $module) {
            $module_data = Module::fromName($module)->dump(DeviceCache::get($device_id), $type);
            if ($module_data !== null) {
                $data[$module][$type] = $this->dumpToArray($module_data);
            }
        }

        return $data;
    }

    private function dumpToArray(iterable $data): array
    {
        $output = [];

        foreach ($data as $table => $table_data) {
            foreach ($table_data as $item) {
                $output[$table][] = is_a($item, Model::class)
                    ? Arr::except($item->getAttributes(), $item->getHidden()) // don't apply accessors
                    : (array) $item;
            }
        }

        return $output;
    }

    /**
     * Get the output from the last discovery that was run
     * If module was specified, only return that module's output
     */
    public function getDiscoveryOutput(?string $module = null): string
    {
        if ($module) {
            return $this->discovery_module_output[$module]
                ?? "Module $module not run. Modules: " . implode(',', array_keys($this->discovery_module_output));
        }

        return $this->discovery_output;
    }

    /**
     * Get output from the last poller that was run
     * If module was specified, only return that module's output
     */
    public function getPollerOutput(?string $module = null): string
    {
        if ($module) {
            if (isset($this->poller_module_output[$module])) {
                return $this->poller_module_output[$module];
            } else {
                return "Module $module not run. Modules: " . implode(',', array_keys($this->poller_module_output));
            }
        }

        return $this->poller_output;
    }

    public function getTestData(): array
    {
        return json_decode(file_get_contents($this->json_file), true);
    }

    public function getJsonFilepath(bool $short = false): string
    {
        if ($short) {
            return ltrim(str_replace(LibrenmsConfig::get('install_dir'), '', $this->json_file), '/');
        }

        return $this->json_file;
    }
}
