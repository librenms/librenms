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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;
use Symfony\Component\Yaml\Yaml;

class ModuleTestHelper
{
    private $quiet = false;
    private $modules;
    private $variant;
    private $os;
    private $snmprec_file;
    private $json_file;
    private $snmprec_dir;
    private $json_dir;
    private $file_name;
    private $module_tables;


    /**
     * ModuleTester constructor.
     * @param array|string $modules
     * @param string $os
     * @param string $variant
     */
    public function __construct($modules, $os, $variant = '')
    {
        $this->modules = $this->resolveModuleDependencies((array)$modules);
        $this->os = $os;
        $this->variant = $variant;

        // preset the file names
        if ($variant) {
            $variant = '_' . $variant;
        }
        $install_dir = Config::get('install_dir');
        $this->file_name = $os . $variant;
        $this->snmprec_dir = "$install_dir/tests/snmpsim/";
        $this->snmprec_file = $this->snmprec_dir . $this->file_name . ".snmprec";
        $this->json_dir = "$install_dir/tests/data/";
        $this->json_file = $this->json_dir . $this->file_name . ".json";

        // never store time series data
        Config::set('norrd', true);
        Config::set('noinfluxdb', true);
        Config::set('nographite', true);

        $this->module_tables = Yaml::parse($install_dir . '/tests/module_tables.yaml');
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

    public function captureFromDevice($device_id, $write = true, $prefer_new = false)
    {
        $snmp_oids = $this->collectOids($device_id);

        $device = device_by_id_cache($device_id, true);

        $snmprec_data = array();
        foreach ($snmp_oids as $oid_data) {
            $this->qPrint(" " . $oid_data['oid']);

            $snmp_options = '-OUneb -Ih';
            if ($oid_data['method'] == 'walk') {
                $data = snmp_walk($device, $oid_data['oid'], $snmp_options, $oid_data['mib']);
            } elseif ($oid_data['method'] == 'get') {
                $data = snmp_get($device, $oid_data['oid'], $snmp_options, $oid_data['mib']);
            } elseif ($oid_data['method'] == 'getnext') {
                $data = snmp_getnext($device, $oid_data['oid'], $snmp_options, $oid_data['mib']);
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
        global $debug, $vdebug, $device;

        $device = device_by_id_cache($device_id);

        // Run discovery
        ob_start();
        $save_debug = $debug;
        $save_vedbug = $vdebug;
        $debug = true;
        $vdebug = false;
        discover_device($device, $this->getArgs());
        poll_device($device, $this->getArgs());
        $debug = $save_debug;
        $vdebug = $save_vedbug;
        $collection_output = ob_get_contents();
        ob_end_clean();

        d_echo($collection_output);
        d_echo(PHP_EOL);

        // remove color
        $collection_output = preg_replace('/\033\[[\d;]+m/', '', $collection_output);

        // extract snmp queries
        $snmp_query_regex = '/SNMP\[.*snmp(?:bulk)?([a-z]+) .+:HOSTNAME:[0-9]+(.+)\]/';
        preg_match_all($snmp_query_regex, $collection_output, $snmp_matches);

        // extract mibs and group with oids
        $snmp_oids = array(
            'sysDescr.0_get' => array('oid' => 'sysDescr.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'),
            'sysObjectID.0_get' => array('oid' => 'sysObjectID.0', 'mib' => 'SNMPv2-MIB', 'method' => 'get'),
        );
        foreach ($snmp_matches[0] as $index => $line) {
            preg_match('/-m ([a-zA-Z0-9:\-]+)/', $line, $mib_matches);
            $mib = $mib_matches[1];
            $method = $snmp_matches[1][$index];
            $oids = explode(' ', trim($snmp_matches[2][$index]));
            foreach ($oids as $oid) {
                $snmp_oids["{$oid}_$method"] = array(
                    'oid' => $oid,
                    'mib' => $mib,
                    'method' => $method,
                );
            }
        }

        d_echo("OIDs to capture ");
        d_echo($snmp_oids);

        return $snmp_oids;
    }


    /**
     * Generate a module list.  Try to take dependencies into account.
     * Probably needs to be more robust
     *
     * @param array $modules
     * @return array
     */
    private function resolveModuleDependencies($modules)
    {
        $module_deps = array(
            'arp-table' => array('ports', 'arp-table'),
        );

        // generate a full list of modules
        $full_list = array();
        foreach ($modules as $module) {
            if (isset($module_deps[$module])) {
                $full_list = array_merge($full_list, $module_deps[$module]);
            } else {
                $full_list[] = $module;
            }
        }

        return array_unique($full_list);
    }

    private function getArgs()
    {
        if (empty($this->modules)) {
            return array();
        }

        return array('m' => implode(',', $this->modules));
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
        $result = array();
        foreach (explode(PHP_EOL, $snmp_data) as $line) {
            if (empty($line)) {
                continue;
            }

            if (str_contains($line, ' = ')) {
                list($oid, $raw_data) = explode(' = ', $line, 2);
                $oid = ltrim($oid, '.');

                if (empty($raw_data)) {
                    $result[] = "$oid|4|"; // empty data, we don't know type, put string
                } else {
                    list($raw_type, $data) = explode(':', $raw_data, 2);
                    if (starts_with($raw_type, 'Wrong Type (should be ')) {
                        // device returned the wrong type, save the wrong type to emulate the device behavior
                        list($raw_type, $data) = explode(':', ltrim($data), 2);
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

                list($oid, $type, $data) = explode('|', $last, 3);
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
        $snmpTypes = array(
            'STRING' => '4',
            'OID' => '6',
            'Hex-STRING' => '4x',
            'Timeticks' => '67',
            'INTEGER' => '2',
            'OCTET STRING' => '4',
            'BITS' => '4', # not sure if this is right
            'Integer32' => '2',
            'NULL' => '5',
            'OBJECT IDENTIFIER' => '6',
            'IpAddress' => '64',
            'Counter32' => '65',
            'Gauge32' => '66',
            'Opaque' => '68',
            'Counter64' => '70',
            'Network Address' => '4'
        );

        return $snmpTypes[$text];
    }

    private function saveSnmprec($data, $write = true, $prefer_new = false)
    {
        if (is_file($this->snmprec_file)) {
            $existing_data = $this->indexSnmprec(explode(PHP_EOL, file_get_contents($this->snmprec_file)));
        } else {
            $existing_data = array();
        }

        $new_data = array();
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
        uksort($results, array($this, 'compareOid'));

        $output = implode(PHP_EOL, $results) . PHP_EOL;

        if ($write) {
            $this->qPrint("Updated snmprec data $this->snmprec_file\n");
            $this->qPrint("Verify these files do not contain any private data before submitting\n");
            file_put_contents($this->snmprec_file, $output);
        }

        return $output;
    }

    private function indexSnmprec(array $snmprec_data)
    {
        $result = array();

        foreach ($snmprec_data as $line) {
            if (!empty($line)) {
                list($oid,) = explode('|', $line, 2);
                $result[$oid] = $line;
            }
        }

        return $result;
    }

    private function cleanSnmprecData(&$data)
    {
        $private_oid = array(
            '1.3.6.1.2.1.1.6.0',
            '1.3.6.1.2.1.1.4.0',
            '1.3.6.1.2.1.1.5.0',
        );

        foreach ($private_oid as $oid) {
            if (isset($data[$oid])) {
                $parts = explode('|', $data[$oid], 3);
                $parts[2] = '<private>';
                $data[$oid] = implode('|', $parts);
            }
        }
    }

    public function generateTestData(Snmpsim $snmpsim, $no_save = false)
    {
        global $device;

        // Remove existing device in case it didn't get removed previously
        if ($existing_device = device_by_name($snmpsim->getIp())) {
            delete_device($existing_device['device_id']);
        }

        // Add the test device
        try {
            Config::set('snmp.community', array($this->file_name));
            $device_id = addHost($snmpsim->getIp(), 'v2c', $snmpsim->getPort());
            $this->qPrint("Added device: $device_id\n");
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            exit;
        }

        // Populate the device variable
        $device = device_by_id_cache($device_id, true);

        $data = array();  // array to hold dumped data

        // Run discovery
        if ($this->quiet) {
            ob_start();
        }

        discover_device($device, $this->getArgs());

        if ($this->quiet) {
            $discovery_output = ob_get_contents();
            ob_end_clean();

            d_echo($discovery_output);
            d_echo(PHP_EOL);
        }

        $this->qPrint(PHP_EOL);

        // Dump the discovered data
        $data = array_merge_recursive($data, $this->dumpDb($device['device_id'], 'discovery'));

        // Run the poller
        ob_start();
        poll_device($device, $this->getArgs());
        $poller_output = ob_get_contents();
        ob_end_clean();

        d_echo($poller_output);
        d_echo(PHP_EOL);

        // Dump polled data
        $data = array_merge_recursive($data, $this->dumpDb($device['device_id'], 'poller'));

        // Remove the test device, we don't need the debug from this
        if ($device['hostname'] == $snmpsim->getIp()) {
            global $debug;
            $debug = false;
            delete_device($device['device_id']);
        }


        if (!$no_save) {
            d_echo($data);

            // Save the data to the default test data location (or elsewhere if specified)
            $existing_data = json_decode(file_get_contents($this->json_file), true);

            // insert new data, don't store duplicate data
            foreach ($data as $module => $module_data) {
                if ($module_data['discovery'] == $module_data['poller']) {
                    $existing_data[$module] = array(
                        'discovery' => $module_data['discovery'],
                        'poller' => 'matches discovery',
                    );
                } else {
                    $existing_data[$module] = $module_data;
                }
            }

            file_put_contents($this->json_file, _json_encode($existing_data));
            $this->qPrint("Saved to $this->json_file\nReady for testing!\n");
        }

        return $data;
    }

    /**
     * Dump the current database data for the module to an array
     * Mostly used for testing
     *
     * @param int $device_id The test device id
     * @param string $key a key to store the data under the module key (usually discovery or poller)
     * @return array The dumped data keyed by module -> table
     */
    public function dumpDb($device_id, $key = null)
    {
        $data = array();

        foreach ($this->getTableData() as $module => $module_tables) {
            foreach ($module_tables as $table => $info) {
                // check for custom where
                $where = isset($info['custom_where']) ? $info['custom_where'] : "WHERE `device_id`=?";
                $params = array($device_id);

                // build joins
                $join = '';
                foreach ($info['joins'] as $join_info) {
                    if (isset($join_info['custom'])) {
                        $join .= ' ' . $join_info['custom'];
                    } else {
                        list($left, $lkey) = explode('.', $join_info['left']);
                        list($right, $rkey) = explode('.', $join_info['right']);
                        $join .= " LEFT JOIN `$right` ON (`$left`.`$lkey` = `$right`.`$rkey`)";
                    }
                }

                $rows = dbFetchRows("SELECT * FROM `$table` $join $where", $params);

                // remove unwanted fields
                $keys = array_flip($info['excluded_fields']);
                $formatted_data = array_map(function ($row) use ($keys) {
                    return array_diff_key($row, $keys);
                }, $rows);

                if (isset($key)) {
                    $data[$module][$key][$table] = $formatted_data;
                } else {
                    $data[$module][$table] = $formatted_data;
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
        return array_intersect_key($this->module_tables, array_flip($this->getModules()));
    }

    /**
     * Get a list of all modules that support capturing data
     *
     * @return array
     */
    public function getSupportedModules()
    {
        return array_keys($this->module_tables);
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

    public function fetchTestData()
    {
        return json_decode(file_get_contents($this->json_file), true);
    }
}
