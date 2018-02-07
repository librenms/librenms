<?php
/**
 * YamlTest.php
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <librenms+n@laf.io>
 */

namespace LibreNMS\Tests;

use LibreNMS\Config;
use PHPUnit_Framework_ExpectationFailedException as PHPUnitException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlTest extends TestCase
{
    private $valid_os_discovery_keys = array(
        'sysDescr',
        'sysDescr_except',
        'sysObjectID',
        'sysObjectID_except',
        'sysDescr_regex',
        'sysDescr_regex_except',
        'sysObjectID_regex',
        'sysObjectID_regex_except',
        'snmpget',
        'snmpget_except'
    );

    private $valid_snmpget_keys = array(
        'oid',
        'options',
        'mib',
        'mib_dir',
        'op',
        'value',
    );

    private $valid_comparisons = array(
        '=',
        '!=',
        '==',
        '!==',
        '<=',
        '>=',
        '<',
        '>',
        'starts',
        'ends',
        'contains',
        'regex',
    );

    private $os_required_keys = array(
        'os',
        'type',
        'text',
    );

    private $os_optional_keys = array(
        'discovery', // move to required after ubnt is done
        'over',
        'mib_dir',
        'group',
        'icon',
        'poller_modules',
        'discovery_modules',
        'icons',
        'ifname',
        'good_if',
        'bad_if',
        'bad_ifXEntry',
        'bad_if_regexp',
        'empty_ifdescr',
        'ifXmcbc', // what is this?
        'bad_snmpEngineTime',
        'bad_hrSystemUptime',
        'bad_uptime',
        'nobulk',
        'rfc1628_compat',
        'register_mibs',
        'processor_stacked',
        'ignore_mount_string'
    );


    public function testOSYaml()
    {
        $pattern = Config::get('install_dir') . '/includes/definitions/*.yaml';
        foreach (glob($pattern) as $file) {
            try {
                $data = Yaml::parse(file_get_contents($file));
            } catch (ParseException $e) {
                throw new PHPUnitException("$file Could not be parsed");
            }

            $this->checkDiscoveryData($file, 'OS', $data, $this->os_required_keys, $this->os_optional_keys);

            // test discovery keys
            if (isset($data['discovery'])) {
                foreach ((array)$data['discovery'] as $group) {
                    // make sure we have at least one valid discovery key
                    $keys = array_keys($group);
                    $this->assertNotEmpty($keys, "$file: contains no os discovery keys");
                    $this->assertNotEmpty(
                        array_intersect($keys, $this->valid_os_discovery_keys),
                        "$file: contains no valid os discovery keys: " . var_export($keys, true)
                    );

                    foreach ((array)$group as $key => $item) {
                        $this->assertContains($key, $this->valid_os_discovery_keys, "$file: invalid discovery type $key");

                        if (starts_with($key, 'snmpget')) {
                            foreach ($item as $get_key => $get_val) {
                                $this->assertContains($get_key, $this->valid_snmpget_keys, "$file: invalid snmpget option $get_key");
                            }
                            $this->assertArrayHasKey('oid', $item, "$file: snmpget discovery must specify oid");
                            $this->assertArrayHasKey('value', $item, "$file: snmpget discovery must specify value");
                            if (isset($item['op'])) {
                                $this->assertContains($item['op'], $this->valid_comparisons, "$file: invalid op ${item['op']}");
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @dataProvider listDiscoveryFiles
     * @param $file
     */
    public function testDiscoveryYaml($file)
    {
        try {
            $data = Yaml::parse(file_get_contents(Config::get('install_dir') . "/includes/definitions/discovery/$file"));
        } catch (ParseException $e) {
            throw new PHPUnitException("includes/definitions/discovery/$file Could not be parsed");
        }

        foreach ($data['modules'] as $module => $module_data) {
            if (array_key_exists('data', $module_data)) {
                foreach ($module_data['data'] as $index => $item_data) {
                    $required_keys = array('oid', 'num_oid');
                    $optional_keys = array('value', 'index', 'descr', 'type', 'skip_values', 'snmp_flags', 'entPhysicalIndex');
                    if ($module == 'processors') {
                        $optional_keys[] = 'precision';
                    }
                    $this->checkDiscoveryData($file, $module, $item_data, $required_keys, $optional_keys, $index);
                }
            } else {
                // Item with submodules (sensors)
                foreach ($module_data as $type => $sub_module) {
                    $this->assertArrayHasKey('data', $sub_module, "$type is missing data key");
                    foreach ($sub_module['data'] as $sensor_index => $sensor) {
                        if ($type == 'pre-cache') {
                            // pre-cache
                            $required_keys = array('oid');
                            $optional_keys = array();
                            $this->checkDiscoveryData($file, $type, $sensor, $required_keys, $optional_keys, $sensor_index);
                        } else {
                            // sensor sub-type
                            $required_keys = array('oid', 'num_oid', 'descr');
                            $optional_keys = array('value', 'index', 'skip_values', 'skip_value_lt', 'divisor', 'multiplier', 'low_limit', 'low_warn_limit', 'high_limit', 'warn_limit', 'snmp_flags', 'entPhysicalIndex', 'entPhysicalIndex_measured', 'user_func');
                            if ($type == 'state') {
                                $required_keys[] = 'states';
                                $optional_keys[] = 'state_name';
                            }

                            $this->checkDiscoveryData($file, $type, $sensor, $required_keys, $optional_keys, $sensor_index);

                            if ($type == 'state') {
                                foreach ($sensor['states'] as $state_index => $state) {
                                    $this->assertArrayHasKey('descr', $state, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index is missing descr key");
                                    $this->assertNotEmpty($state['descr'], "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) descr must not be empty");
                                    $this->assertArrayHasKey('graph', $state, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing graph key");
                                    $this->assertTrue($state['graph'] === 0 || $state['graph'] === 1, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) invalid graph value must be 0 or 1");
                                    $this->assertArrayHasKey('value', $state, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing value key");
                                    $this->assertInternalType('int', $state['value'], "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) value must be an int");
                                    $this->assertArrayHasKey('generic', $state, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing generic key");
                                    $this->assertInternalType('int', $state['generic'], "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) generic must be an int");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function listDiscoveryFiles()
    {
        $pattern = Config::get('install_dir') . '/includes/definitions/discovery/*.yaml';
        return array_map(function ($file) {
            return array(basename($file));
        }, glob($pattern));
    }

    /**
     * @param $file
     * @param $type
     * @param $data
     * @param $required_keys
     * @param array $optional_keys
     * @param $index
     */
    public function checkDiscoveryData($file, $type, $data, $required_keys, $optional_keys = array(), $index = null)
    {
        foreach ($required_keys as $key) {
            $this->assertArrayHasKey($key, $data, "$file: $type.data.$index is missing $key key");
            unset($data[$key]);
        }

        foreach ($optional_keys as $key) {
            unset($data[$key]);
        }

        foreach ($data as $invalid_key => $invalid_data) {
            $this->fail("$file: invalid $type option $invalid_key");
        }
    }
}
