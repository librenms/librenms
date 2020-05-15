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

use DeviceCache;
use LibreNMS\Config;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\ModuleTestHelper;

class OSModulesTest extends DBTestCase
{
    private $discoveryModules;
    private $pollerModules;

    public function setUp(): void
    {
        parent::setUp();

        // backup modules
        $this->discoveryModules = Config::get('discovery_modules');
        $this->pollerModules = Config::get('poller_modules');
    }

    public function tearDown(): void
    {
        // restore modules
        Config::set('discovery_modules', $this->discoveryModules);
        Config::set('poller_modules', $this->pollerModules);

        parent::tearDown();
    }

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

        // stub out Log::event, we don't need to store them for these tests
        $this->app->bind('log', function ($app) {
            return \Mockery::mock('\App\Facades\LogManager[event]', [$app])
                ->shouldReceive('event');
        });

        try {
            set_debug(false); // avoid all undefined index errors in the legacy code
            $helper = new ModuleTestHelper($modules, $os, $variant);
            $helper->setQuiet();

            $filename = $helper->getJsonFilepath(true);
            $expected_data = $helper->getTestData();
            $results = $helper->generateTestData($this->getSnmpsim(), true);
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
            // Discovery
            $expected = $expected_data[$module]['discovery'];
            $actual = $results[$module]['discovery'];

            foreach (array_merge(array_keys($expected), array_keys($actual)) as $table) {
                $this->assertModuleTableSame('discovery', $module, $table, $expected, $actual);
            }

            // Poller
            if ($expected_data[$module]['poller'] !== 'matches discovery') {
                $expected = $expected_data[$module]['poller'];
            }
            $actual = $results[$module]['poller'];
            foreach (array_merge(array_keys($expected), array_keys($actual)) as $table) {
                $this->assertModuleTableSame('poller', $module, $table, $expected, $actual);
            }
        }

        DeviceCache::flush(); // clear cached devices
    }

    private function assertModuleTableSame($process, $module, $table, $expected, $actual)
    {
        $this->assertArrayHasKey($table, $expected, "\e[0;31mUnexpected $module $process data in table $table: \e[0m\n" . json_encode($actual[$table], JSON_PRETTY_PRINT));
        $this->assertArrayHasKey($table, $actual, "\e[0;31mMissing $module $process data in table $table, expected: \e[0m\n" . json_encode($expected[$table], JSON_PRETTY_PRINT));

        $expectedCount = count($expected[$table]);
        $actualCount = count($actual[$table]);
        $message = $expectedCount < $actualCount ? "More" : "Less";
        $this->assertCount($expectedCount, $actual[$table], "\e[0;31m$message records than expected for $module $process in table $table\e[0m");
        $this->assertEquals($expected[$table], $actual[$table]);
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
