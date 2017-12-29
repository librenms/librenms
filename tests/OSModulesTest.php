<?php
/**
 * OSModulesTest.php
 *
 * Test discovery and poller modules
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

class OSModulesTest extends DBTestCase
{
    /**
     * Test all modules for a particular OS
     *
     * @group os
     * @dataProvider dumpedDataProvider
     * @param string $target_os
     * @param string $filename
     */
    public function testOS($target_os, $filename)
    {
        $this->requreSnmpsim();  // require snmpsim for tests
        global $snmpsim;

        $file = Config::get('install_dir') . '/' . $filename;
        $expected_data = json_decode(file_get_contents($file), true);

        list($os, $variant) = explode('_', $target_os, 2);
        $modules = array_keys($expected_data);
        $helper = new ModuleTestHelper($modules, $os, $variant);
        $helper->setQuiet();

        $results = $helper->generateTestData($snmpsim, true);

        foreach ($expected_data as $module => $data) {
            $this->assertEquals(
                $data['discovery'],
                $results[$module]['discovery'],
                "OS $target_os: Discovered $module data does not match that found in $filename\n" . $helper->getLastDiscoveryOutput()
            );

            $this->assertEquals(
                $data['poller'] == 'matches discovery' ? $data['discovery'] : $data['poller'],
                $results[$module]['poller'],
                "OS $target_os: Polled $module data does not match that found in $filename\n" . $helper->getLastPollerOutput()
            );
        }
    }


    public function dumpedDataProvider()
    {
        $install_dir = Config::get('install_dir');
        $dump_files = glob("$install_dir/tests/data/*.json");
        $data = array();

        foreach ($dump_files as $file) {
            $os = basename($file, '.json');
            $short_file = str_replace($install_dir.'/', '', $file);

            $data[$os] = array(
                $os,
                $short_file
            );
        }

        return $data;
    }
}
