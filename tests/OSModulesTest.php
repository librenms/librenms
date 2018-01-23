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
     * @param string $target_os name of the (and variant) to test
     * @param string $filename file name of the json data
     * @param array $modules modules to test for this os
     */
    public function testOS($os, $variant, $modules)
    {
        $this->requreSnmpsim();  // require snmpsim for tests
        global $snmpsim;

        $helper = new ModuleTestHelper($modules, $os, $variant);
        $helper->setQuiet();
        $filename = $helper->getJsonFilepath(true);

        $expected_data = $helper->getTestData();
        $results = $helper->generateTestData($snmpsim, true);

        if (is_null($results)) {
            $this->fail("$os: Failed to collect data.");
        }

        foreach ($modules as $module) {
            $this->assertEquals(
                $expected_data[$module]['discovery'],
                $results[$module]['discovery'],
                "OS $os: Discovered $module data does not match that found in $filename\n"
                . $helper->getLastDiscoveryOutput()
                . "\nOS $os: Polled $module data does not match that found in $filename"
            );

            $this->assertEquals(
                $expected_data[$module]['poller'] == 'matches discovery' ? $expected_data[$module]['discovery'] : $expected_data[$module]['poller'],
                $results[$module]['poller'],
                "OS $os: Polled $module data does not match that found in $filename\n"
                . $helper->getLastPollerOutput()
                . "\nOS $os: Polled $module data does not match that found in $filename"
            );
        }
    }


    public function dumpedDataProvider()
    {
        $modules = array();

        if (getenv('TEST_MODULES')) {
            $modules = explode(',', getenv('TEST_MODULES'));
        }

        return ModuleTestHelper::findOsWithData($modules);
    }
}
