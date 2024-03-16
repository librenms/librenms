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
use App\Models\Device;
use DeviceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Poller;

class ModuleTestHelper
{
    private $quiet = false;
    private $modules;
    private $variant;
    private $snmprec_file;
    private $json_file;
    private $snmprec_dir;
    private $json_dir;
    private $file_name;
    private $discovery_module_output = [];
    private $poller_module_output = [];
    private $discovery_output;
    private $poller_output;

    // Definitions
    // ignore these when dumping all modules
    private $exclude_from_all = ['arp-table', 'availability', 'fdb-table'];

    /**
     * ModuleTester constructor.
     *
     * @param  array|string  $modules
     * @param  string  $os
     * @param  string  $variant
     *
     * @throws InvalidModuleException
     */
    public function __construct($modules, $os, $variant = '')
    {
        $this->modules = self::resolveModuleDependencies((array) $modules);
        $this->variant = strtolower($variant);

        // preset the file names
        if ($variant) {
            $variant = '_' . $this->variant;
        }
        $install_dir = Config::get('install_dir');
        $this->file_name = $os . $variant;
        $this->snmprec_dir = "$install_dir/tests/snmpsim/";
        $this->snmprec_file = $this->snmprec_dir . $this->file_name . '.snmprec';
        $this->json_dir = "$install_dir/tests/data/";
        $this->json_file = $this->json_dir . $this->file_name . '.json';

        // never store time series data
        Config::set('rrd.enable', false);
        Config::set('hide_rrd_disabled', true);
        Config::set('influxdb.enable', false);
        Config::set('influxdbv2.enable', false);
        Config::set('graphite.enable', false);
        Config::set('prometheus.enable', false);
    }

    private static function compareOid($a, $b)
    {
        $a_oid = explode('.', $a);
        $b_oid = explode('.', $b);

        foreach ($a_oid as $index => $a_part) {
            $b_part = $b_oid[$index];
            if ($a_part > $b_part) {
                return 1; // a is higher
            } elseif ($a_part < $b_part) {
                return -1; // b is higher
            }
        }

        if (count($a_oid) < count($b_oid)) {
            return -1; // same prefix, but b has more so it is higher
        }

        return 0;
    }

    public function setQuiet($quiet = true)
    {
        $this->quiet = $quiet;
    }

    public function setSnmprecSavePath($path)
    {
        $this->snmprec_file = $path;
    }

    public function setJsonSavePath($path)
    {
        $this->json_file = $path;
    }

    public function captureFromDevice(int $device_id, bool $prefer_new = false, bool $full = false): void
    {
        if ($full) {
            $snmp_oids[][] = [
                'oid' => '.',
                'method' => 'walk',
                'mib' => null,
                'mibdir' => null,
            ];
        } else {
            $snmp_oids = $this->collectOids($device_id);
        }

        DeviceCache::setPrimary($device_id);

        foreach ($snmp_oids as $context => $context_oids) {
            $snmprec_data = [];
            foreach ($context_oids as $oid_data) {
                $this->qPrint(' ' . $oid_data['oid']);

                $snmp_options = ['-OUneb', '-Ih', '-m', '+' . $oid_data['mib']];
                if ($oid_data['method'] == 'walk') {
                    $data = \SnmpQuery::options($snmp_options)->context($context)->mibDir($oid_data['mibdir'] ?? null)->walk($oid_data['oid']);
                } elseif ($oid_data['method'] == 'get') {
                    $data = \SnmpQuery::options($snmp_options)->context($context)->mibDir($oid_data['mibdir'] ?? null)->get($oid_data['oid']);
                } elseif ($oid_data['method'] == 'getnext') {
                    $data = \SnmpQuery::options($snmp_options)->context($context)->mibDir($oid_data['mibdir'] ?? null)->next($oid_data['oid']);
                }

                if (isset($data) && $data->isValid()) {
                    $snmprec_data[] = $this->convertSnmpToSnmprec($data);
                }
            }

            $this->saveSnmprec($snmprec_data, $context, true, $prefer_new);
        }
    }

    private function collectOids($device_id)
    {
        global $device;

        $device = device_by_id_cache($device_id);
        DeviceCache::setPrimary($device_id);

        // Run discovery
        ob_start();
        $save_debug = Debug::isEnabled();
        $save_vdebug = Debug::isVerbose();
        Debug::set();
        Debug::setVerbose();
        discover_device($device, $this->parseArgs('discovery'));
        $poller = app(Poller::class, ['device_spec' => $device_id, 'module_override' => $this->modules]);
        $poller->poll();
        Debug::set($save_debug);
        Debug::setVerbose($save_vdebug);
        $collection_output = ob_get_contents();
        ob_end_clean();

        d_echo($collection_output);
        d_echo(PHP_EOL);

        // remove color
        $collection_output = preg_replace('/\033\[[\d;]+m/', '', $collection_output);

        // extract snmp queries
        $snmp_query_regex = '/SNMP\[\'.*snmp(?:bulk)?(walk|get|getnext)\' .+\'(udp|tcp|tcp6|udp6):(?:\[[0-9a-f:]+\]|[^:]+):[0-9]+\' \'(.+)\'\]/m';
        preg_match_all($snmp_query_regex, $collection_output, $snmp_matches);

        // extract mibs and group with oids
        $snmp_oids = [
            null => [
                'sysDescr.0_get' => ['oid' => 'sysDescr.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'],
                'sysObjectID.0_get' => ['oid' => 'sysObjectID.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'],
            ],
        ];
        foreach ($snmp_matches[0] as $index => $line) {
            preg_match("/'-m' '\+?([a-zA-Z0-9:\-]+)'/", $line, $mib_matches);
            $mib = $mib_matches[1] ?? null;
            preg_match("/'-M' '\+?([a-zA-Z0-9:\-\/]+)'/", $line, $mibdir_matches);
            $mibdir = $mibdir_matches[1];
            $method = $snmp_matches[1][$index];
            $oids = explode("' '", trim($snmp_matches[3][$index]));
            preg_match("/('-c' '.*@([^']+)'|'-n' '([^']+)')/", $line, $context_matches);
            $context = $context_matches[2] ?? $context_matches[3] ?? null;

            foreach ($oids as $oid) {
                $snmp_oids[$context]["{$oid}_$method"] = [
                    'oid' => $oid,
                    'mib' => $mib,
                    'mibdir' => $mibdir,
                    'method' => $method,
                ];
            }
        }

        d_echo('OIDs to capture ');
        d_echo($snmp_oids);

        return $snmp_oids;
    }

    /**
     * Generate a list of os containing test data for $modules (an empty array means all)
     *
     * Returns an array indexed by the basename ($os or $os_$variant)
     * Each entry contains [$os, $variant, $valid_modules]
     * $valid_modules is an array of selected modules this os has test data for
     *
     * @param  array  $modules
     * @return array
     *
     * @throws InvalidModuleException
     */
    public static function findOsWithData($modules = [], string $os_filter = null)
    {
        $os_list = [];

        foreach (glob(Config::get('install_dir') . '/tests/data/*.json') as $file) {
            $base_name = basename($file, '.json');
            [$os, $variant] = self::extractVariant($file);

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
    }

    /**
     * Given a json filename or basename, extract os and variant
     *
     * @param  string  $os_file  Either a filename or the basename
     * @return array [$os, $variant]
     */
    public static function extractVariant($os_file)
    {
        $full_name = basename($os_file, '.json');

        if (! Str::contains($full_name, '_')) {
            return [$full_name, ''];
        } elseif (is_file(Config::get('install_dir') . "/includes/definitions/$full_name.yaml")) {
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
     * @return array
     *
     * @throws InvalidModuleException
     */
    private static function resolveModuleDependencies(array $modules): array
    {
        // generate a full list of modules
        $full_list = [];
        foreach ($modules as $module) {
            // only allow valid modules
            if (! (Config::has("poller_modules.$module") || Config::has("discovery_modules.$module"))) {
                throw new InvalidModuleException("Invalid module name: $module");
            }

            $full_list = array_merge($full_list, Module::fromName($module)->dependencies());
            $full_list[] = $module;
        }

        return array_unique($full_list);
    }

    private function parseArgs($type)
    {
        if (empty($this->modules)) {
            return false;
        }

        return parse_modules($type, ['m' => implode(',', $this->modules)]);
    }

    private function qPrint($var)
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

    private function convertSnmpToSnmprec(SnmpResponse $snmp_data): array
    {
        $result = [];
        foreach (explode(PHP_EOL, $snmp_data->raw) as $line) {
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^\.[.\d]+ =/', $line)) {
                [$oid, $raw_data] = explode(' =', $line, 2);
                $oid = ltrim($oid, '.');
                $raw_data = trim($raw_data);

                if (empty($raw_data) || $raw_data == '""') {
                    $result[] = "$oid|4|"; // empty data, we don't know type, put string
                } else {
                    [$raw_type, $data] = explode(':', $raw_data, 2);
                    if (Str::startsWith($raw_type, 'Wrong Type (should be ')) {
                        // device returned the wrong type, save the wrong type to emulate the device behavior
                        [$raw_type, $data] = explode(':', ltrim($data), 2);
                    }

                    $type = $this->getSnmprecType($raw_type);

                    $data = ltrim($data, ' ');
                    if (Str::startsWith($data, '"') && Str::endsWith($data, '"')) {
                        // raw string surrounded by quotes, strip extra escapes
                        $data = stripslashes(substr($data, 1, -1));
                    }

                    if ($type == '6') {
                        // remove leading . from oid data
                        $data = ltrim($data, '.');
                    } elseif ($type == '4x') {
                        // remove spaces from hex-strings
                        $data = str_replace(' ', '', $data);
                    } elseif ($type == '67') {
                        // extract timeticks value (-Ot removes type info)
                        preg_match('/\((\d+)\)/', $data, $match);
                        $data = $match[1];
                    }

                    $result[] = "$oid|$type|$data";
                }
            } else {
                // multi-line data, append to last
                $last = end($result);

                [$oid, $type, $data] = explode('|', $last, 3);
                if ($type == '4x') {
                    $result[key($result)] .= bin2hex(PHP_EOL . $line);
                } else {
                    $result[key($result)] = "$oid|4x|" . bin2hex($data . PHP_EOL . $line);
                }
            }
        }

        return $result;
    }

    private function getSnmprecType($text)
    {
        $snmpTypes = [
            'STRING' => '4',
            'OID' => '6',
            'Hex-STRING' => '4x',
            'Timeticks' => '67',
            'INTEGER' => '2',
            'OCTET STRING' => '4',
            'BITS' => '4', // not sure if this is right
            'Integer32' => '2',
            'NULL' => '5',
            'OBJECT IDENTIFIER' => '6',
            'IpAddress' => '64',
            'Counter32' => '65',
            'Gauge32' => '66',
            'Opaque' => '68',
            'Counter64' => '70',
            'Network Address' => '4',
        ];

        return $snmpTypes[$text];
    }

    private function saveSnmprec(array $data, ?string $context = null, bool $write = true, bool $prefer_new = false): string
    {
        $filename = $this->snmprec_file;

        if ($context) {
            $filename = str_replace('.snmprec', '', $filename) . "@$context.snmprec";
        }

        if (is_file($filename)) {
            $existing_data = $this->indexSnmprec(explode(PHP_EOL, file_get_contents($filename)));
        } else {
            $existing_data = [];
        }

        $new_data = [];
        foreach ($data as $part) {
            $new_data = array_merge($new_data, $this->indexSnmprec($part));
        }

        $this->cleanSnmprecData($new_data);

        // merge new and existing data
        if ($prefer_new) {
            $results = array_merge($existing_data, $new_data);
        } else {
            $results = array_merge($new_data, $existing_data);
        }

        // put data in the proper order for snmpsim
        uksort($results, [$this, 'compareOid']);

        $output = implode(PHP_EOL, $results) . PHP_EOL;

        if ($write) {
            if (empty($results)) {
                $this->qPrint("No data for $filename\n");
            } else {
                $this->qPrint("\nSaved snmprec data $filename\n");
                file_put_contents($filename, $output);
            }
        }

        return $output;
    }

    private function indexSnmprec(array $snmprec_data)
    {
        $result = [];

        foreach ($snmprec_data as $line) {
            if (! empty($line)) {
                [$oid] = explode('|', $line, 2);
                $result[$oid] = $line;
            }
        }

        return $result;
    }

    private function cleanSnmprecData(&$data)
    {
        $private_oid = [
            '1.3.6.1.2.1.1.6.0',
            '1.3.6.1.2.1.1.4.0',
            '1.3.6.1.2.1.1.5.0',
        ];

        foreach ($private_oid as $oid) {
            if (isset($data[$oid])) {
                $parts = explode('|', $data[$oid], 3);
                $parts[2] = $parts[1] === '4' ? '<private>' : '3C707269766174653E';
                $data[$oid] = implode('|', $parts);
            }
        }

        // IF-MIB::ifPhysAddress, Make sure it is in hex format
        foreach ($data as $oid => $oid_data) {
            if (str_starts_with($oid, '1.3.6.1.2.1.2.2.1.6.')) {
                $parts = explode('|', $oid_data, 3);
                $mac = Mac::parse($parts[2])->hex();
                if ($mac) {
                    $parts[2] = $mac;
                    $parts[1] = '4x';
                    $data[$oid] = implode('|', $parts);
                }
            }
        }
    }

    /**
     * Run discovery and polling against snmpsim data and create a database dump
     * Save the dumped data to tests/data/<os>.json
     *
     * @param  Snmpsim  $snmpsim
     * @param  bool  $no_save
     * @return array|null
     *
     * @throws FileNotFoundException
     */
    public function generateTestData(Snmpsim $snmpsim, $no_save = false)
    {
        global $device;
        Config::set('rrd.enable', false); // disable rrd
        Config::set('rrdtool_version', '1.7.2'); // don't detect rrdtool version, rrdtool is not install on ci

        // don't allow external DNS queries that could fail
        app()->bind(\LibreNMS\Util\AutonomousSystem::class, function ($app, $parameters) {
            $asn = $parameters['asn'];
            $mock = \Mockery::mock(\LibreNMS\Util\AutonomousSystem::class);
            $mock->shouldReceive('name')->withAnyArgs()->zeroOrMoreTimes()->andReturnUsing(function () use ($asn) {
                return "AS$asn-MOCK-TEXT";
            });

            return $mock;
        });

        if (! is_file($this->snmprec_file)) {
            throw new FileNotFoundException("$this->snmprec_file does not exist!");
        }

        // Remove existing device in case it didn't get removed previously
        if (($existing_device = device_by_name($snmpsim->getIp())) && isset($existing_device['device_id'])) {
            delete_device($existing_device['device_id']);
        }

        // Add the test device
        try {
            $new_device = new Device([
                'hostname' => $snmpsim->getIp(),
                'version' => 'v2c',
                'community' => $this->file_name,
                'port' => $snmpsim->getPort(),
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
        $device = device_by_id_cache($device_id, true);
        DeviceCache::setPrimary($device_id);

        $data = [];  // array to hold dumped data

        // Run discovery
        $save_debug = Debug::isEnabled();
        $save_vedbug = Debug::isVerbose();
        if ($this->quiet) {
            Debug::setOnly();
            Debug::setVerbose();
        }
        ob_start();

        discover_device($device, $this->parseArgs('discovery'));

        $this->discovery_output = ob_get_contents();
        if ($this->quiet) {
            Debug::setOnly($save_debug);
            Debug::setVerbose($save_vedbug);
        } else {
            ob_flush();
        }
        ob_end_clean();

        $this->qPrint(PHP_EOL);

        // Parse discovered modules
        $this->discovery_module_output = $this->extractModuleOutput($this->discovery_output, 'disco');
        $discovered_modules = array_keys($this->discovery_module_output);

        // Dump the discovered data
        $data = array_merge_recursive($data, $this->dumpDb($device['device_id'], $discovered_modules, 'discovery'));
        DeviceCache::get($device_id)->refresh(); // refresh the device

        // Run the poller
        if ($this->quiet) {
            Debug::setOnly();
            Debug::setVerbose();
        }
        ob_start();

        \Log::setDefaultDriver('console');
        $poller = app(Poller::class, ['device_spec' => $device_id, 'module_override' => $this->modules]);
        $poller->poll();

        $this->poller_output = ob_get_contents();
        if ($this->quiet) {
            Debug::setOnly($save_debug);
            Debug::setVerbose($save_vedbug);
        } else {
            ob_flush();
        }
        ob_end_clean();

        // Parse polled modules
        $this->poller_module_output = $this->extractModuleOutput($this->poller_output, 'poller');
        $polled_modules = array_keys($this->poller_module_output);

        // Dump polled data
        $data = array_merge_recursive($data, $this->dumpDb($device_id, $polled_modules, 'poller'));

        // Remove the test device, we don't need the debug from this
        if ($device['hostname'] == $snmpsim->getIp()) {
            Debug::set(false);
            delete_device($device_id);
        }

        if (! $no_save) {
            d_echo($data);

            // Save the data to the default test data location (or elsewhere if specified)
            $existing_data = json_decode(file_get_contents($this->json_file), true);

            // insert new data, don't store duplicate data
            foreach ($data as $module => $module_data) {
                // skip saving modules with no data
                if (empty($module_data['discovery']) && empty($module_data['poller'])) {
                    continue;
                }
                if ($module_data['discovery'] == $module_data['poller']) {
                    $existing_data[$module] = [
                        'discovery' => $module_data['discovery'],
                        'poller' => 'matches discovery',
                    ];
                } else {
                    $existing_data[$module] = $module_data;
                }
            }

            file_put_contents($this->json_file, json_encode($existing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
            $this->qPrint("Saved to $this->json_file\nReady for testing!\n");
        }

        return $data;
    }

    /**
     * @param  string  $output  poller or discovery output
     * @param  string  $type  poller|disco identified by "#### Load disco module" string
     * @return array
     */
    private function extractModuleOutput($output, $type)
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
     * @param  array  $modules  to capture data for (should be a list of modules that were actually run)
     * @param  string  $type  a key to store the data under the module key (usually discovery or poller)
     * @return array The dumped data keyed by module -> table
     */
    public function dumpDb($device_id, $modules, $type)
    {
        $data = [];

        // don't dump some modules by default unless they are manually listed
        if (empty($this->modules)) {
            $modules = array_diff($modules, $this->exclude_from_all);
        }

        // only dump data for the given modules (and modules that support dumping)
        foreach ($modules as $module) {
            $module_data = Module::fromName($module)->dump(DeviceCache::get($device_id));
            if ($module_data !== false) {
                $data[$module][$type] = $this->dumpToArray($module_data);
            }
        }

        return $data;
    }

    /**
     * @param  array|\Illuminate\Support\Collection|\stdClass  $data
     * @return array
     */
    private function dumpToArray($data): array
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
     *
     * @param  null  $module
     * @return mixed
     */
    public function getDiscoveryOutput($module = null)
    {
        if ($module) {
            if (isset($this->discovery_module_output[$module])) {
                return $this->discovery_module_output[$module];
            } else {
                return "Module $module not run. Modules: " . implode(',', array_keys($this->poller_module_output));
            }
        }

        return $this->discovery_output;
    }

    /**
     * Get output from the last poller that was run
     * If module was specified, only return that module's output
     *
     * @param  null  $module
     * @return mixed
     */
    public function getPollerOutput($module = null)
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

    public function getTestData()
    {
        return json_decode(file_get_contents($this->json_file), true);
    }

    public function getJsonFilepath($short = false)
    {
        if ($short) {
            return ltrim(str_replace(Config::get('install_dir'), '', $this->json_file), '/');
        }

        return $this->json_file;
    }
}
