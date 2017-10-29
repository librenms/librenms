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

class YamlTest extends \PHPUnit_Framework_TestCase
{

    public function testOSYaml()
    {
        $pattern = Config::get('install_dir') . '/includes/definitions/*.yaml';
        foreach (glob($pattern) as $file) {
            try {
                $data = Yaml::parse(file_get_contents($file));
            } catch (ParseException $e) {
                throw new PHPUnitException("$file Could not be parsed");
            }

            $this->assertArrayHasKey('os', $data, $file);
            $this->assertArrayHasKey('type', $data, $file);
            $this->assertArrayHasKey('text', $data, $file);
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

        foreach ($data['modules'] as $module => $sub_modules) {
            foreach ($sub_modules as $type => $sub_module) {
                $this->assertArrayHasKey('data', $sub_module, "$type is missing data key");
                foreach ($sub_module['data'] as $sensor_index => $sensor) {
                    $this->assertArrayHasKey('oid', $sensor, "$type.data.$sensor_index is missing oid key");
                    if ($type !== 'pre-cache') {
                        $this->assertArrayHasKey('num_oid', $sensor, "$type.data.$sensor_index(${sensor['oid']}) is missing num_oid key");
                        $this->assertArrayHasKey('descr', $sensor, "$type.data.$sensor_index(${sensor['oid']}) is missing descr key");
                    }

                    if ($type === 'state') {
                        $this->assertArrayHasKey('states', $sensor, "$type.data(${sensor['oid']}) is missing states key");

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

    public function listDiscoveryFiles()
    {
        $pattern = Config::get('install_dir') . '/includes/definitions/discovery/*.yaml';
        return array_map(function ($file) {
            return array(basename($file));
        }, glob($pattern));
    }
}
