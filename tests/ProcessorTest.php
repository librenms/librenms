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
use LibreNMS\Util\ModuleTestHelper;

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
        global $snmpsim;

        $helper = new ModuleTestHelper($this->module, $target_os);
        $helper->setQuiet();

        $result = $helper->generateTestData($snmpsim, $target_os, '', true);

        $this->assertEquals(
            $expected_data['discovery'],
            $result['discovery'],
            "OS $target_os: Discovered {$this->module} data does not match that found in $filename"
        );

        $this->assertEquals(
            $expected_data['poller'] == 'matches discovery' ? $expected_data['discovery'] : $expected_data['poller'],
            $result['poller'],
            "OS $target_os: Polled {$this->module} data does not match that found in $filename"
        );
    }


    public function dumpedDataProvider()
    {
        $install_dir = Config::get('install_dir');
        $dump_files = glob("$install_dir/tests/data/*.json");
        $data = array();

        foreach ($dump_files as $file) {
            $os = basename($file, '.json');
            $short_file = str_replace($install_dir.'/', '', $file);
            $dumped_data = json_decode(file_get_contents($file), true);
            if (isset($dumped_data[$this->module])) {
                $data[$os] = array(
                    $os,
                    $short_file,
                    $dumped_data[$this->module]
                );
            }
        }

        return $data;
    }
}
