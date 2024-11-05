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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Data\Source\NetSnmpQuery;
use LibreNMS\Modules\Core;
use LibreNMS\Tests\Mocks\SnmpQueryMock;
use LibreNMS\Util\Debug;
use LibreNMS\Util\OS;

class OSDiscoveryTest extends TestCase
{
    private static $unchecked_files;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $glob = Config::get('install_dir') . '/tests/snmpsim/*.snmprec';

        self::$unchecked_files = array_flip(array_filter(array_map(function ($file) {
            return basename($file, '.snmprec');
        }, glob($glob)), function ($file) {
            return ! Str::contains($file, '@');
        }));
    }

    /**
     * Populate a list of files to check and make sure it isn't empty
     */
    public function testHaveFilesToTest(): void
    {
        $this->assertNotEmpty(self::$unchecked_files);
    }

    /**
     * Test each OS provided by osProvider
     *
     * @group os
     *
     * @dataProvider osProvider
     *
     * @param  string  $os_name
     */
    public function testOSDetection($os_name): void
    {
        if (! getenv('SNMPSIM')) {
            $this->app->bind(NetSnmpQuery::class, SnmpQueryMock::class);
        }

        $glob = Config::get('install_dir') . "/tests/snmpsim/$os_name*.snmprec";
        $files = array_map(function ($file) {
            return basename($file, '.snmprec');
        }, glob($glob));
        $files = array_filter($files, function ($file) use ($os_name) {
            if (Str::contains($file, '@')) {
                return false;
            }

            return $file == $os_name || Str::startsWith($file, $os_name . '_');
        });

        if (empty($files)) {
            $this->fail("No snmprec files found for $os_name!");
        }

        foreach ($files as $file) {
            $this->checkOS($os_name, $file);
            unset(self::$unchecked_files[$file]);  // This file has been tested
        }
    }

    /**
     * Test that all files have been tested (removed from self::$unchecked_files
     *
     * @depends testOSDetection
     */
    public function testAllFilesTested(): void
    {
        $this->assertEmpty(
            self::$unchecked_files,
            'Not all snmprec files were checked: ' . print_r(array_keys(self::$unchecked_files), true)
        );
    }

    /**
     * Set up and test an os
     * If $filename is not set, it will use the snmprec file matching $expected_os
     *
     * @param  string  $expected_os  The os we should get back from getHostOS()
     * @param  string  $filename  the name of the snmprec file to use
     */
    private function checkOS($expected_os, $filename = null)
    {
        $start = microtime(true);

        $community = $filename ?: $expected_os;
        Debug::set();
        Debug::setVerbose();
        ob_start();
        $os = Core::detectOS($this->genDevice($community));
        $output = ob_get_contents();
        ob_end_clean();
        Debug::set(false);
        Debug::setVerbose(false);

        $this->assertLessThan(10, microtime(true) - $start, "OS $expected_os took longer than 10s to detect");
        $this->assertEquals($expected_os, $os, "Test file: $community.snmprec\n$output");
    }

    /**
     * Generate a fake $device array
     *
     * @param  string  $community  The snmp community to set
     * @return Device resulting device array
     */
    private function genDevice($community): Device
    {
        return new Device([
            'hostname' => $this->getSnmpsim()->ip,
            'snmpver' => 'v2c',
            'port' => $this->getSnmpsim()->port,
            'timeout' => 3,
            'retries' => 0,
            'snmp_max_repeaters' => 10,
            'community' => $community,
            'os' => 'generic',
        ]);
    }

    /**
     * Provides a list of OS to generate tests.
     *
     * @return array
     */
    public function osProvider()
    {
        // make sure all OS are loaded
        $config_os = array_keys(Config::get('os'));
        if (count($config_os) < count(glob(Config::get('install_dir') . '/includes/definitions/*.yaml'))) {
            OS::loadAllDefinitions(false, true);
            $config_os = array_keys(Config::get('os'));
        }

        $excluded_os = [
            'default',
            'generic',
            'ping',
        ];
        $filtered_os = array_diff($config_os, $excluded_os);

        $all_os = [];
        foreach ($filtered_os as $os) {
            $all_os[$os] = [$os];
        }

        return $all_os;
    }
}
