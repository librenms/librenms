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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Component;
use LibreNMS\Config;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use Symfony\Component\Yaml\Yaml;

class ModuleTestHelper
{
    private static $module_tables;

    private $quiet = false;
    private $modules;
    private $variant;
    private $os;
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
    private $exclude_from_all = ['arp-table', 'fdb-table'];
    private static $module_deps = [
        'arp-table' => ['ports', 'arp-table'],
        'fdb-table' => ['ports', 'vlans', 'fdb-table'],
        'vlans' => ['ports', 'vlans'],
        'vrf' => ['ports', 'vrf'],
        'mpls' => ['ports', 'vrf', 'mpls'],
        'nac' => ['ports', 'nac'],
        'ospf' => ['ports', 'ospf'],
        'cisco-mac-accounting' => ['ports', 'cisco-mac-accounting'],
    ];

    /**
     * ModuleTester constructor.
     * @param array|string $modules
     * @param string $os
     * @param string $variant
     * @throws InvalidModuleException
     */
    public function __construct($modules, $os, $variant = '')
    {
        global $influxdb;

        $this->modules = self::resolveModuleDependencies((array) $modules);
        $this->os = strtolower($os);
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
        Config::set('graphite.enable', false);
        Config::set('prometheus.enable', false);

        if (is_null(self::$module_tables)) {
            // only load the yaml once, then keep it in memory
            self::$module_tables = Yaml::parse(file_get_contents($install_dir . '/tests/module_tables.yaml'));
        }
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

    public function captureFromDevice($device_id, $write = true, $prefer_new = false, $full = false)
    {
        if ($full) {
            $snmp_oids[] = [
                'oid' => '.',
                'method' => 'walk',
                'mib' => null,
                'mibdir' => null,
            ];
        } else {
            $snmp_oids = $this->collectOids($device_id);
        }

        $device = device_by_id_cache($device_id, true);
        DeviceCache::setPrimary($device_id);

        $snmprec_data = [];
        foreach ($snmp_oids as $oid_data) {
            $this->qPrint(' ' . $oid_data['oid']);

            $snmp_options = ['-OUneb', '-Ih'];
            if ($oid_data['method'] == 'walk') {
                $data = snmp_walk($device, $oid_data['oid'], $snmp_options, $oid_data['mib'], $oid_data['mibdir']);
            } elseif ($oid_data['method'] == 'get') {
                $data = snmp_get($device, $oid_data['oid'], $snmp_options, $oid_data['mib'], $oid_data['mibdir']);
            } elseif ($oid_data['method'] == 'getnext') {
                $data = snmp_getnext($device, $oid_data['oid'], $snmp_options, $oid_data['mib'], $oid_data['mibdir']);
            }

            if (isset($data) && $data !== false) {
                $snmprec_data[] = $this->convertSnmpToSnmprec($data);
            }
        }

        $this->qPrint(PHP_EOL);

        return $this->saveSnmprec($snmprec_data, $write, $prefer_new);
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
        Debug::setVerbose(false);
        discover_device($device, $this->parseArgs('discovery'));
        poll_device($device, $this->parseArgs('poller'));
        Debug::set($save_debug);
        Debug::setVerbose($save_vdebug);
        $collection_output = ob_get_contents();
        ob_end_clean();

        d_echo($collection_output);
        d_echo(PHP_EOL);

        // remove color
        $collection_output = preg_replace('/\033\[[\d;]+m/', '', $collection_output);

        // extract snmp queries
        $snmp_query_regex = '/SNMP\[.*snmp(?:bulk)?([a-z]+)\' .+:HOSTNAME:[0-9]+\' \'(.+)\'\]/';
        preg_match_all($snmp_query_regex, $collection_output, $snmp_matches);

        // extract mibs and group with oids
        $snmp_oids = [
            'sysDescr.0_get' => ['oid' => 'sysDescr.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'],
            'sysObjectID.0_get' => ['oid' => 'sysObjectID.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'],
        ];
        foreach ($snmp_matches[0] as $index => $line) {
            preg_match("/'-m' '\+?([a-zA-Z0-9:\-]+)'/", $line, $mib_matches);
            $mib = $mib_matches[1];
            preg_match("/'-M' '\+?([a-zA-Z0-9:\-\/]+)'/", $line, $mibdir_matches);
            $mibdir = $mibdir_matches[1];
            $method = $snmp_matches[1][$index];
            $oids = explode("' '", trim($snmp_matches[2][$index]));

            foreach ($oids as $oid) {
                $snmp_oids["{$oid}_$method"] = [
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
     * @param array $modules
     * @return array
     * @throws InvalidModuleException
     */
    public static function findOsWithData($modules = [])
    {
        $os_list = [];

        foreach (glob(Config::get('install_dir') . '/tests/data/*.json') as $file) {
            $base_name = basename($file, '.json');
            [$os, $variant] = self::extractVariant($file);

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
     * @param string $os_file Either a filename or the basename
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
     * @param array $modules
     * @return array
     * @throws InvalidModuleException
     */
    private static function resolveModuleDependencies($modules)
    {
        // generate a full list of modules
        $full_list = [];
        foreach ($modules as $module) {
            // only allow valid modules
            if (! (Config::has("poller_modules.$module") || Config::has("discovery_modules.$module"))) {
                throw new InvalidModuleException("Invalid module name: $module");
            }

            if (isset(self::$module_deps[$module])) {
                $full_list = array_merge($full_list, self::$module_deps[$module]);
            } else {
                $full_list[] = $module;
            }
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

    private function convertSnmpToSnmprec($snmp_data)
    {
        $result = [];
        foreach (explode(PHP_EOL, $snmp_data) as $line) {
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^\.[.\d]+ =/', $line)) {
                [$oid, $raw_data] = explode(' =', $line, 2);
                $oid = ltrim($oid, '.');
                $raw_data = trim($raw_data);

                if (empty($raw_data)) {
                    $result[] = "$oid|4|"; // empty data, we don't know type, put string
                } else {
                    [$raw_type, $data] = explode(':', $raw_data, 2);
                    if (Str::startsWith($raw_type, 'Wrong Type (should be ')) {
                        // device returned the wrong type, save the wrong type to emulate the device behavior
                        [$raw_type, $data] = explode(':', ltrim($data), 2);
                    }
                    $data = ltrim($data, ' "');
                    $type = $this->getSnmprecType($raw_type);

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

    private function saveSnmprec($data, $write = true, $prefer_new = false)
    {
        if (is_file($this->snmprec_file)) {
            $existing_data = $this->indexSnmprec(explode(PHP_EOL, file_get_contents($this->snmprec_file)));
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
            $this->qPrint("\nUpdated snmprec data $this->snmprec_file\n");
            $this->qPrint("\nVerify this file does not contain any private data before submitting!\n");
            file_put_contents($this->snmprec_file, $output);
        }

        return $output;
    }

    private function indexSnmprec(array $snmprec_data)
    {
        $result = [];

        foreach ($snmprec_data as $line) {
            if (! empty($line)) {
                [$oid,] = explode('|', $line, 2);
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
    }

    /**
     * Run discovery and polling against snmpsim data and create a database dump
     * Save the dumped data to tests/data/<os>.json
     *
     * @param Snmpsim $snmpsim
     * @param bool $no_save
     * @return array|null
     * @throws FileNotFoundException
     */
    public function generateTestData(Snmpsim $snmpsim, $no_save = false)
    {
        global $device;
        Config::set('rrd.enable', false); // disable rrd

        if (! is_file($this->snmprec_file)) {
            throw new FileNotFoundException("$this->snmprec_file does not exist!");
        }

        // Remove existing device in case it didn't get removed previously
        if ($existing_device = device_by_name($snmpsim->getIp())) {
            delete_device($existing_device['device_id']);
        }

        // Add the test device
        try {
            Config::set('snmp.community', [$this->file_name]);
            $device_id = addHost($snmpsim->getIp(), 'v2c', $snmpsim->getPort());

            // disable to block normal pollers
            dbUpdate(['disabled' => 1], 'devices', 'device_id=?', [$device_id]);

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
        $device = device_by_id_cache($device_id, true); // refresh the device array

        // Run the poller
        if ($this->quiet) {
            Debug::setOnly();
            Debug::setVerbose();
        }
        ob_start();

        poll_device($device, $this->parseArgs('poller'));

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
        $data = array_merge_recursive($data, $this->dumpDb($device['device_id'], $polled_modules, 'poller'));

        // Remove the test device, we don't need the debug from this
        if ($device['hostname'] == $snmpsim->getIp()) {
            Debug::set(false);
            delete_device($device['device_id']);
        }

        if (! $no_save) {
            d_echo($data);

            // Save the data to the default test data location (or elsewhere if specified)
            $existing_data = json_decode(file_get_contents($this->json_file), true);

            // insert new data, don't store duplicate data
            foreach ($data as $module => $module_data) {
                // skip saving modules with no data
                if ($this->dataIsEmpty($module_data['discovery']) && $this->dataIsEmpty($module_data['poller'])) {
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
     * @param string $output poller or discovery output
     * @param string $type poller|disco identified by "#### Load disco module" string
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
     * @param int $device_id The test device id
     * @param array $modules to capture data for (should be a list of modules that were actually run)
     * @param string $key a key to store the data under the module key (usually discovery or poller)
     * @return array The dumped data keyed by module -> table
     */
    public function dumpDb($device_id, $modules, $key = null)
    {
        $data = [];
        $module_dump_info = $this->getTableData();

        // don't dump some modules by default unless they are manually listed
        if (empty($this->modules)) {
            $modules = array_diff($modules, $this->exclude_from_all);
        }

        // only dump data for the given modules
        foreach ($modules as $module) {
            foreach ($module_dump_info[$module] ?: [] as $table => $info) {
                if ($table == 'component') {
                    if (isset($key)) {
                        $data[$module][$key][$table] = $this->collectComponents($device_id);
                    } else {
                        $data[$module][$table] = $this->collectComponents($device_id);
                    }
                    continue;
                }

                // check for custom where
                $where = isset($info['custom_where']) ? $info['custom_where'] : "WHERE `$table`.`device_id`=?";
                $params = [$device_id];

                // build joins
                $join = '';
                $select = ["`$table`.*"];
                foreach ($info['joins'] ?: [] as $join_info) {
                    if (isset($join_info['custom'])) {
                        $join .= ' ' . $join_info['custom'];

                        $default_select = [];
                    } else {
                        [$left, $lkey] = explode('.', $join_info['left']);
                        [$right, $rkey] = explode('.', $join_info['right']);
                        $join .= " LEFT JOIN `$right` ON (`$left`.`$lkey` = `$right`.`$rkey`)";

                        $default_select = ["`$right`.*"];
                    }

                    // build selects
                    $select = array_merge($select, isset($join_info['select']) ? (array) $join_info['select'] : $default_select);
                }

                if (isset($info['order_by'])) {
                    $order_by = " ORDER BY {$info['order_by']}";
                } else {
                    $order_by = '';
                }

                $fields = implode(', ', $select);
                $rows = dbFetchRows("SELECT $fields FROM `$table` $join $where $order_by", $params);

                // remove unwanted fields
                if (isset($info['included_fields'])) {
                    $keys = array_flip($info['included_fields']);
                    $rows = array_map(function ($row) use ($keys) {
                        return array_intersect_key($row, $keys);
                    }, $rows);
                } elseif (isset($info['excluded_fields'])) {
                    $keys = array_flip($info['excluded_fields']);
                    $rows = array_map(function ($row) use ($keys) {
                        return array_diff_key($row, $keys);
                    }, $rows);
                }

                if (isset($key)) {
                    $data[$module][$key][$table] = $rows;
                } else {
                    $data[$module][$table] = $rows;
                }
            }
        }

        return $data;
    }

    /**
     * Get list of tables used by a module
     * Includes a list of fields that will not be considered for testing
     *
     * @return array
     */
    public function getTableData()
    {
        return array_intersect_key(self::$module_tables, array_flip($this->getModules()));
    }

    /**
     * Get the output from the last discovery that was run
     * If module was specified, only return that module's output
     *
     * @param null $module
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
     * @param null $module
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

    /**
     * Get a list of all modules that support capturing data
     *
     * @return array
     */
    public function getSupportedModules()
    {
        return array_keys(self::$module_tables);
    }

    /**
     * Get a list of modules to capture data for
     * If modules is empty, returns all supported modules
     *
     * @return array
     */
    private function getModules()
    {
        return empty($this->modules) ? $this->getSupportedModules() : $this->modules;
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

    private function collectComponents($device_id)
    {
        $components = (new Component())->getComponents($device_id)[$device_id] ?? [];
        $components = Arr::sort($components, function ($item) {
            return $item['type'] . $item['label'];
        });

        return array_values($components);
    }

    private function dataIsEmpty($data)
    {
        foreach ($data as $table_data) {
            if (! empty($table_data)) {
                return false;
            }
        }

        return true;
    }
}
