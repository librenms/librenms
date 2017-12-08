<?php
/**
 * OSDiscoveryTest.php
 *
 * Test all discovery for all OS
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

namespace LibreNMS\Tests;

use LibreNMS\Config;
use LibreNMS\Device\Processor;

class ProcessorTest extends DBTestCase
{
    private $module = 'processors';

    /**
     * @dataProvider dumpedDataProvider
     * @param $target_os
     * @param $filename
     * @param $expected_data
     */
    public function testProcessor($target_os, $filename, $expected_data)
    {
        $this->requreSnmpsim();  // require snmpsim for now

        // Remove existing device in case it didn't get removed previously
        if ($existing_device = device_by_name($this->snmpsimIp)) {
            delete_device($existing_device['device_id']);
        }


        // Add the test device
        Config::set('snmp.community', array($target_os));
        $device_id = addHost($this->snmpsimIp, 'v2c', $this->snmpsimPort);

        // Populate the device variable
        global $device;
        $device = device_by_id_cache($device_id);

        // Run discovery
        ob_start();
        discover_device($device, array('m' => $this->module));
        ob_end_clean();

        // Dump the discovered data
        $discover_data = dump_module_data($device_id, $this->module);

        // Run poller
        Config::set('norrd', true);
        Config::set('noinfluxdb', true);
        Config::set('nographite', true);

        ob_start();
        poll_device($device, array('m' => $this->module));
        ob_end_clean();

        // Dump the discovered data
        $poll_data = dump_module_data($device_id, $this->module);

        // Remove the test device
        delete_device($device_id);

        $this->assertEquals(
            $expected_data,
            $discover_data,
            "OS $target_os: Discovered {$this->module} data does not match that found in $filename"
        );

        $this->assertEquals(
            $expected_data,
            $poll_data,
            "OS $target_os: Polled {$this->module} data does not match that found in $filename"
        );
    }


    public function dumpedDataProvider()
    {
        $install_dir = Config::get('install_dir');
        $dump_files = glob( "$install_dir/tests/data/*.json");
        $data = array();

        foreach ($dump_files as $file) {
            $os = basename($file, '.json');
            $short_file = str_replace($install_dir.'/', '', $file);
            $dumped_data = json_decode(file_get_contents($file), true);
            if (isset($dumped_data[$this->module])) {
                $data[$os] = array(
                    $os,
                    $short_file,
                    array($this->module => $dumped_data[$this->module])
                );
            }
        }

        return $data;
    }
}
