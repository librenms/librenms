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

use JsonSchema\Constraints\Constraint;
use LibreNMS\Config;
use PHPUnit_Framework_ExpectationFailedException as PHPUnitException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlTest extends TestCase
{
    private $valid_os_discovery_keys = [
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
    ];

    private $valid_snmpget_keys = [
        'oid',
        'options',
        'mib',
        'mib_dir',
        'op',
        'value',
    ];

    private $valid_comparisons = [
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
    ];

    private $os_required_keys = [
        'os',
        'type',
        'text',
    ];

    private $os_optional_keys = [
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
    ];

    public function testOSDefinitionSchema()
    {
        $this->validateYamlFilesAgainstSchema('/includes/definitions', '/misc/os_schema.json');
    }

    public function testDiscoveryDefinitionSchema()
    {
        $this->validateYamlFilesAgainstSchema('/includes/definitions/discovery', '/misc/discovery_schema.json');
    }

    public function validateYamlFilesAgainstSchema($dir, $schema_file)
    {
        $schema = (object)['$ref' => 'file://' . Config::get('install_dir') . $schema_file];

        $empty = (object)[];
        $validator = new \JsonSchema\Validator;
        $validator->validate($empty, $schema, Constraint::CHECK_MODE_VALIDATE_SCHEMA);

        dd($validator->isValid(), $validator->getErrors());

        foreach ($this->listFiles($dir . '/*.yaml') as $info) {
            list($file, $path) = $info;

            try {
                $data = Yaml::parse(file_get_contents($path));
            } catch (ParseException $e) {
                throw new PHPUnitException("$path Could not be parsed", null, $e);
            }

            $validator->validate(
                $data,
                $schema,
                Constraint::CHECK_MODE_TYPE_CAST
            );

            $errors = collect($validator->getErrors())
                ->reduce(function ($out, $error) {
                    return sprintf("%s[%s] %s\n", $out, $error['property'], $error['message']);
                }, '');

            $this->assertTrue($validator->isValid(), "$file does not validate. Violations:\n$errors");
        }
    }

//    public function testDiscoveryYaml()
//    {
//        foreach ($this->listDiscoveryFiles() as $info) {
//            list($file, $path) = $info;
//
//            try {
//                $data = Yaml::parse(file_get_contents($path));
//            } catch (ParseException $e) {
//                throw new PHPUnitException("$path Could not be parsed");
//            }
//
//            foreach ($data['modules'] as $module => $module_data) {
//                if (array_key_exists('data', $module_data)) {
//                    foreach ($module_data['data'] as $index => $item_data) {
//                        $required_keys = ['oid', 'num_oid'];
//                        $optional_keys = ['value', 'index', 'descr', 'type', 'skip_values', 'snmp_flags', 'entPhysicalIndex'];
//                        if ($module == 'processors') {
//                            $optional_keys[] = 'precision';
//                        }
//                        $this->checkDiscoveryData($file, $module, $item_data, $required_keys, $optional_keys, $index);
//                    }
//                } else {
//                    // Item with submodules (sensors)
//                    foreach ($module_data as $type => $sub_module) {
//                        $this->assertArrayHasKey('data', $sub_module, "$type is missing data key");
//                        foreach ($sub_module['data'] as $sensor_index => $sensor) {
//                            if ($type == 'pre-cache') {
//                                // pre-cache
//                                $required_keys = ['oid'];
//                                $optional_keys = [];
//                                $this->checkDiscoveryData($file, $type, $sensor, $required_keys, $optional_keys, $sensor_index);
//                            } else {
//                                // sensor sub-type
//                                $required_keys = ['oid', 'num_oid', 'descr'];
//                                $optional_keys = [
//                                    'value',
//                                    'index',
//                                    'skip_values',
//                                    'skip_value_lt',
//                                    'divisor',
//                                    'multiplier',
//                                    'low_limit',
//                                    'low_warn_limit',
//                                    'high_limit',
//                                    'warn_limit',
//                                    'snmp_flags',
//                                    'entPhysicalIndex',
//                                    'entPhysicalIndex_measured',
//                                    'user_func'
//                                ];
//                                if ($type == 'state') {
//                                    $required_keys[] = 'states';
//                                    $optional_keys[] = 'state_name';
//                                }
//
//                                $this->checkDiscoveryData($file, $type, $sensor, $required_keys, $optional_keys, $sensor_index);
//
//                                if ($type == 'state') {
//                                    foreach ($sensor['states'] as $state_index => $state) {
//                                        $this->assertArrayHasKey('descr', $state, "$type.data.$sensor_index(${sensor['oid']}).states.$state_index is missing descr key");
//                                        $this->assertNotEmpty($state['descr'],
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) descr must not be empty");
//                                        $this->assertArrayHasKey('graph', $state,
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing graph key");
//                                        $this->assertTrue($state['graph'] === 0 || $state['graph'] === 1,
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) invalid graph value must be 0 or 1");
//                                        $this->assertArrayHasKey('value', $state,
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing value key");
//                                        $this->assertInternalType('int', $state['value'],
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) value must be an int");
//                                        $this->assertArrayHasKey('generic', $state,
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) is missing generic key");
//                                        $this->assertInternalType('int', $state['generic'],
//                                            "$type.data.$sensor_index(${sensor['oid']}).states.$state_index(${state['descr']}) generic must be an int");
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//    }

    public function listOsDefinitionFiles()
    {
        return $this->listFiles('/includes/definitions/*.yaml');
    }

    public function listDiscoveryFiles()
    {
        return $this->listFiles('/includes/definitions/discovery/*.yaml');
    }

    private function listFiles($pattern)
    {
        $pattern = Config::get('install_dir') . $pattern;

        return collect(glob($pattern))
            ->reduce(function($array, $file) {
                $name = basename($file);
                $array[$name] = [$name, $file];
                return $array;
            }, []);
    }

    /**
     * @param $file
     * @param $type
     * @param $data
     * @param $required_keys
     * @param array $optional_keys
     * @param $index
     */
    public function checkDiscoveryData($file, $type, $data, $required_keys, $optional_keys = [], $index = null)
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
