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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use PHPUnit_Framework_ExpectationFailedException as PHPUnitException;

class OSDiscoveryTest extends \PHPUnit_Framework_TestCase
{
    private static $unchecked_files;

    /**
     * Populate a list of files to check and make sure it isn't empty
     */
    public function testHaveFilesToTest()
    {
        global $config;

        $glob = $config['install_dir'] . "/tests/snmpsim/*.snmprec";

        self::$unchecked_files = array_flip(array_map(function ($file) {
            return basename($file, '.snmprec');
        }, glob($glob)));

        $this->assertNotEmpty(self::$unchecked_files);
    }

    /**
     * Test each OS provided by osProvider
     *
     * @depends      testHaveFilesToTest
     * @dataProvider osProvider
     * @param $os_name
     */
    public function testOS($os_name)
    {
        global $config;

        $this->assertNotEmpty(
            self::$unchecked_files,
            'Something wrong with the tests, $unchecked_files should be populated'
        );

        $glob = $config['install_dir'] . "/tests/snmpsim/$os_name*.snmprec";
        $files = array_map(function ($file) {
            return basename($file, '.snmprec');
        }, glob($glob));
        $files = array_filter($files, function ($file) use ($os_name) {
            return $file == $os_name || starts_with($file, $os_name . '_');
        });

        if (empty($files)) {
            throw new PHPUnitException("No snmprec files found for $os_name!");
        }

        foreach ($files as $file) {
            $this->checkOS($os_name, $file);
            unset(self::$unchecked_files[$file]);  // This file has been tested
        }
    }

    /**
     * Test that all files have been tested (removed from self::$unchecked_files
     * Except skel.snmprec, the example file.
     *
     * @depends testOS
     */
    public function testAllFilesTested()
    {
        $this->assertEquals(array('skel'), array_keys(self::$unchecked_files));
    }

    /**
     * Set up and test an os
     * If $filename is not set, it will use the snmprec file matching $expected_os
     *
     * @param string $expected_os The os we should get back from getHostOS()
     * @param string $filename the name of the snmprec file to use
     */
    private function checkOS($expected_os, $filename = null)
    {
        $community = $filename ?: $expected_os;
        global $debug;
        $debug = true;
        ob_start();
        $os = getHostOS($this->genDevice($community));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($expected_os, $os, "Test file: $community.snmprec\n$output");
    }

    /**
     * Generate a fake $device array
     *
     * @param string $community The snmp community to set
     * @return array resulting device array
     */
    private function genDevice($community)
    {
        return array(
            'device_id' => 1,
            'hostname' => '127.0.0.1',
            'snmpver' => 'v2c',
            'port' => 11161,
            'timeout' => 3,
            'retries' => 0,
            'snmp_max_repeaters' => 10,
            'community' => $community,
            'os' => 'generic',
            'os_group' => '',
        );
    }

    /**
     * Provides a list of OS to generate tests.
     *
     * @return array
     */
    public function osProvider()
    {
        global $config;

        // make sure all OS are loaded
        if (count($config['os']) < count(glob($config['install_dir'].'/includes/definitions/*.yaml'))) {
            load_all_os();
        }

        $excluded_os = array(
            'default',
            'generic',
        );
        $all_os = array_diff(array_keys($config['os']), $excluded_os);

        array_walk($all_os, function (&$os) {
            $os = array($os);
        });

        return $all_os;
    }
}
