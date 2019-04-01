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

use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\ModuleTestHelper;

class OSModulesTest extends DBTestCase
{
    /**
     * Test all modules for a particular OS
     *
     * @group os
     * @dataProvider dumpedDataProvider
     */
    public function testDataIsValid($os, $variant, $modules)
    {
        // special case if data provider throws exception
        if ($os === false) {
            $this->fail($modules);
        }

        $this->assertNotEmpty($modules, "No modules to test for $os $variant");
    }

    /**
     * Test all modules for a particular OS
     *
     * @group os
     * @dataProvider dumpedDataProvider
     * @param string $os base os
     * @param string $variant optional variant
     * @param array $modules modules to test for this os
     */
    public function testOS($os, $variant, $modules)
    {
        $this->requireSnmpsim();  // require snmpsim for tests
        global $snmpsim;
        load_all_os(); // wiped out by application refresh

        try {
            $helper = new ModuleTestHelper($modules, $os, $variant);
            $helper->setQuiet();

            $filename = $helper->getJsonFilepath(true);
            $expected_data = $helper->getTestData();
            $results = $helper->generateTestData($snmpsim, true);
        } catch (FileNotFoundException $e) {
            $this->fail($e->getMessage());
            return;
        } catch (InvalidModuleException $e) {
            $this->fail($e->getMessage());
            return;
        }

        if (is_null($results)) {
            $this->fail("$os: Failed to collect data.");
        }

        // output all discovery and poller output if debug mode is enabled for phpunit
        $debug = in_array('--debug', $_SERVER['argv'], true);

        foreach ($modules as $module) {
            $expected = $expected_data[$module]['discovery'];
            $actual = $results[$module]['discovery'];
            $this->assertEquals(
                $expected,
                $actual,
                "OS $os: Discovered $module data does not match that found in $filename\n"
                . print_r(array_diff($expected, $actual), true)
                . $helper->getDiscoveryOutput($debug ? null : $module)
                . "\nOS $os: Discovered $module data does not match that found in $filename"
            );

            if ($expected_data[$module]['poller'] == 'matches discovery') {
                $expected = $expected_data[$module]['discovery'];
            } else {
                $expected = $expected_data[$module]['poller'];
            }
            $actual = $results[$module]['poller'];
            $this->assertEquals(
                $expected,
                $actual,
                "OS $os: Polled $module data does not match that found in $filename\n"
                . print_r(array_diff($expected, $actual), true)
                . $helper->getPollerOutput($debug ? null : $module)
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

        try {
            return ModuleTestHelper::findOsWithData($modules);
        } catch (InvalidModuleException $e) {
            // special case for exception
            return [[false, false, $e->getMessage()]];
        }
    }
}
