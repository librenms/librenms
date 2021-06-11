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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Config;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Fping;
use LibreNMS\Util\Debug;
use LibreNMS\Util\ModuleTestHelper;

class OSModulesTest extends DBTestCase
{
    use DatabaseTransactions;

    private $discoveryModules;
    private $pollerModules;

    protected function setUp(): void
    {
        parent::setUp();

        // backup modules
        $this->discoveryModules = Config::get('discovery_modules');
        $this->pollerModules = Config::get('poller_modules');
    }

    protected function tearDown(): void
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
        // stub out Log::event and Fping->ping, we don't need to store them for these tests
        $this->stubClasses();

        try {
            Debug::set(false); // avoid all undefined index errors in the legacy code
            $helper = new ModuleTestHelper($modules, $os, $variant);
            $helper->setQuiet();

            $filename = $helper->getJsonFilepath(true);
            $expected_data = $helper->getTestData();
            $results = $helper->generateTestData($this->getSnmpsim(), true);
        } catch (FileNotFoundException $e) {
            return $this->fail($e->getMessage());
        } catch (InvalidModuleException $e) {
            return $this->fail($e->getMessage());
        }

        if (is_null($results)) {
            $this->fail("$os: Failed to collect data.");
        }

        // output all discovery and poller output if debug mode is enabled for phpunit
        $phpunit_debug = in_array('--debug', $_SERVER['argv'], true);

        foreach ($modules as $module) {
            $expected = $expected_data[$module]['discovery'] ?? [];
            $actual = $results[$module]['discovery'] ?? [];
            $this->assertEquals(
                $expected,
                $actual,
                "OS $os: Discovered $module data does not match that found in $filename\n"
                . print_r(array_diff($expected, $actual), true)
                . $helper->getDiscoveryOutput($phpunit_debug ? null : $module)
                . "\nOS $os: Discovered $module data does not match that found in $filename"
            );

            if ($module === 'route') {
                // no route poller module
                continue;
            }

            if ($expected_data[$module]['poller'] == 'matches discovery') {
                $expected = $expected_data[$module]['discovery'];
            } else {
                $expected = $expected_data[$module]['poller'] ?? [];
            }
            $actual = $results[$module]['poller'] ?? [];
            $this->assertEquals(
                $expected,
                $actual,
                "OS $os: Polled $module data does not match that found in $filename\n"
                . print_r(array_diff($expected, $actual), true)
                . $helper->getPollerOutput($phpunit_debug ? null : $module)
                . "\nOS $os: Polled $module data does not match that found in $filename"
            );
        }

        DeviceCache::flush(); // clear cached devices
    }

    public function dumpedDataProvider()
    {
        $modules = [];

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

    private function stubClasses(): void
    {
        $this->app->bind('log', function ($app) {
            return \Mockery::mock('\App\Facades\LogManager[event]', [$app])
                ->shouldReceive('event');
        });

        $this->app->bind(Fping::class, function ($app) {
            $mock = \Mockery::mock('\LibreNMS\Fping');
            $mock->shouldReceive('ping')->andReturn([
                'xmt' => 3,
                'rcv' => 3,
                'loss' => 0,
                'min' => 0.62,
                'max' => 0.93,
                'avg' => 0.71,
                'dup' => 0,
                'exitcode' => 0,
            ]);

            return $mock;
        });
    }
}
