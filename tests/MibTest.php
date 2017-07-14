<?php
/**
 * MibTest.php
 *
 * Test Mib files for errors
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

class MibTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test mib file in a directory for errors
     * @dataProvider mibDirs
     * @param $dir
     */
    public function testMibDirectory($dir)
    {
        global $config;

        $output = shell_exec("snmptranslate -M +{$config['mib_dir']}:$dir -m +ALL SNMPv2-MIB::system 2>&1");
        $errors = str_replace("SNMPv2-MIB::system\n", '', $output);

        $this->assertEmpty($errors, "MIBs in $dir have errors!\n$errors");
    }

    public function mibDirs()
    {
        global $config;

        $dirs = glob($config['mib_dir'] . '/*', GLOB_ONLYDIR);
        array_unshift($dirs, $config['mib_dir']);

        return array_map(function ($dir) {
            return array($dir);
        }, $dirs);
    }
}
