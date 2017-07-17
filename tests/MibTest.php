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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class MibTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test mib file in a directory for errors
     *
     * @group mibs
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

    /**
     * Test each mib file for errors
     *
     * @group mibs
     * @dataProvider mibFiles
     * @param $path
     * @param $file
     */
    public function testMibContents($path, $file)
    {
        global $config, $console_color;
        $file_path = "$path/$file";
        $highlighted_file = $console_color->convert("%r$file_path%n");

        static $existing_mibs;
        if (is_null($existing_mibs)) {
            $existing_mibs = array();
        }

        // extract the mib name (tried regex, but was too complex and I had to read the whole file)
        $mib_name = null;
        if ($handle = fopen($file_path, "r")) {
            $header = '';
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                if (empty($trimmed) || starts_with($trimmed, '--')) {
                    continue;
                }

                $header .= " $trimmed";
                if (str_contains($trimmed, 'DEFINITIONS')) {
                    preg_match('/(\S+)\s+(?=DEFINITIONS)/', $header, $matches);
                    $mib_name = $matches[1];
                    break;
                }
            }
            fclose($handle);
        }

        // run mib name tests
        global $console_color;

        if (empty($mib_name)) {
            $this->fail("$highlighted_file not detected as a mib file");
        } else {
            $this->assertEquals($mib_name, $file, "$highlighted_file should be named $mib_name");

            $output = shell_exec("snmptranslate -M +{$config['mib_dir']}:$path -m +$mib_name SNMPv2-MIB::system 2>&1");
            $errors = str_replace("SNMPv2-MIB::system\n", '', $output);

            $this->assertEmpty($errors, "$highlighted_file has errors!\n$errors");

            if (isset($existing_mibs[$mib_name])) {
                $existing_mibs[$mib_name][] = $file_path;
                $highligted_mib = $console_color->convert("%r$mib_name%n");
                $this->fail("$highligted_mib has duplicates: " . implode(', ', $existing_mibs[$mib_name]));
            } else {
                $existing_mibs[$mib_name] = array($file_path);
            }
        }
    }

    public function mibFiles()
    {
        global $config;

        $file_list = array();
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config['mib_dir'])) as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }
            $file_list[] = array(
                str_replace($config['install_dir'], '.', $file->getPath()),
                $file->getFilename()
            );
        }

        return $file_list;
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
