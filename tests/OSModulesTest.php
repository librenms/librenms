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
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use LibreNMS\Config;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
use LibreNMS\Exceptions\FileNotFoundException;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Number;
use PHPUnit\Util\Color;

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
     *
     * @dataProvider dumpedDataProvider
     */
    public function testDataIsValid($os, $variant, $modules): void
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
     *
     * @dataProvider dumpedDataProvider
     *
     * @param  string  $os  base os
     * @param  string  $variant  optional variant
     * @param  array  $modules  modules to test for this os
     */
    public function testOS($os, $variant, $modules): void
    {
        // Lock testing time
        $this->travelTo(new \DateTime('2022-01-01 00:00:00'));
        $this->requireSnmpsim();  // require snmpsim for tests
        // stub out Eventlog::log and Fping->ping, we don't need to store them for these tests
        $this->stubClasses();

        try {
            Debug::set(false); // avoid all undefined index errors in the legacy code
            $helper = new ModuleTestHelper($modules, $os, $variant);
            $helper->setQuiet();

            $filename = $helper->getJsonFilepath(true);
            $expected_data = $helper->getTestData();
            $results = $helper->generateTestData($this->getSnmpsim(), true);
        } catch (FileNotFoundException|InvalidModuleException $e) {
            $this->fail($e->getMessage());
        }

        if (is_null($results)) {
            $this->fail("$os: Failed to collect data.");
        }

        // output all discovery and poller output if debug mode is enabled for phpunit
        $phpunit_debug = in_array('--debug', $_SERVER['argv'], true);

        foreach ($modules as $module) {
            $expected = $expected_data[$module]['discovery'] ?? [];
            $actual = $results[$module]['discovery'] ?? [];
            $this->checkTestData($expected, $actual, 'Discovered', $os, $module, $filename, $helper, $phpunit_debug);

            // modules without polling
            if (in_array($module, ['route', 'vlans'])) {
                continue;
            }

            if ($expected_data[$module]['poller'] !== 'matches discovery') {
                $expected = $expected_data[$module]['poller'] ?? [];
            }
            $actual = $results[$module]['poller'] ?? [];
            $this->checkTestData($expected, $actual, 'Polled', $os, $module, $filename, $helper, $phpunit_debug);
        }

        $this->assertTrue(true, "Tested $os successfully"); // avoid no asserts error

        DeviceCache::flush(); // clear cached devices
        $this->travelBack();
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
        $this->app->bind(\App\Models\Eventlog::class, function ($app) {
            $mock = \Mockery::mock(\App\Models\Eventlog::class);
            $mock->shouldReceive('_log');

            return $mock;
        });

        $this->app->bind(Fping::class, function ($app) {
            $mock = \Mockery::mock(\LibreNMS\Data\Source\Fping::class);
            $mock->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());

            return $mock;
        });
    }

    private function checkTestData(array $expected, array $actual, string $type, string $os, mixed $module, string $filename, ModuleTestHelper $helper, bool $phpunit_debug): void
    {
        // try simple and fast comparison first, if that fails, do a costly/well formatted comparison
        if ($expected != $actual) {
            $message = Color::colorize('bg-red', "OS $os: $type $module data does not match that found in $filename");
            $message .= PHP_EOL;
            $message .= ($type == 'Discovered'
                ? $helper->getDiscoveryOutput($phpunit_debug ? null : $module)
                : $helper->getPollerOutput($phpunit_debug ? null : $module));

            // convert to dot notation so the array is flat and easier to compare visually
            $expected = Arr::dot($expected);
            $actual = Arr::dot($actual);

            // json will store 43.0 as 43, Number::cast will change those to integers too
            foreach ($actual as $index => $value) {
                if (is_float($value)) {
                    $actual[$index] = Number::cast($value);
                }
            }

            $this->assertSame($expected, $actual, $message);
        }
    }
}
