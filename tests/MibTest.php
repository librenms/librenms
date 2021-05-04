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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use Exception;
use Illuminate\Support\Str;
use LibreNMS\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class MibTest
 */
class MibTest extends TestCase
{
    /**
     * Test mib file in a directory for errors
     *
     * @group mibs
     * @dataProvider mibDirs
     * @param string $dir
     */
    public function testMibDirectory($dir)
    {
        $output = shell_exec('snmptranslate -M +' . Config::get('mib_dir') . ":$dir -m +ALL SNMPv2-MIB::system 2>&1");
        $errors = str_replace("SNMPv2-MIB::system\n", '', $output);

        $this->assertEmpty($errors, "MIBs in $dir have errors!\n$errors");
    }

    /**
     * Test that each mib only exists once.
     *
     * @group mibs
     * @dataProvider mibFiles
     * @param string $path
     * @param string $file
     * @param string $mib_name
     */
    public function testDuplicateMibs($path, $file, $mib_name)
    {
        global $console_color;

        $file_path = "$path/$file";
        $highligted_mib = $console_color->convert("%r$mib_name%n");

        static $existing_mibs;
        if (is_null($existing_mibs)) {
            $existing_mibs = [];
        }

        if (isset($existing_mibs[$mib_name])) {
            $existing_mibs[$mib_name][] = $file_path;

            $this->fail("$highligted_mib has duplicates: " . implode(', ', $existing_mibs[$mib_name]));
        } else {
            $existing_mibs[$mib_name] = [$file_path];
        }
    }

    /**
     * Test that the file name matches the mib name
     *
     * @group mibs
     * @dataProvider mibFiles
     * @param string $path
     * @param string $file
     * @param string $mib_name
     */
    public function testMibNameMatches($path, $file, $mib_name)
    {
        global $console_color;

        $file_path = "$path/$file";
        $highlighted_file = $console_color->convert("%r$file_path%n");
        $this->assertEquals($mib_name, $file, "$highlighted_file should be named $mib_name");
    }

    /**
     * Test each mib file for errors
     *
     * @group mibs
     * @dataProvider mibFiles
     * @param string $path
     * @param string $file
     * @param string $mib_name
     */
    public function testMibContents($path, $file, $mib_name)
    {
        global $console_color;
        $file_path = "$path/$file";
        $highlighted_file = $console_color->convert("%r$file_path%n");

        $output = shell_exec('snmptranslate -M +' . Config::get('mib_dir') . ":$path -m +$mib_name SNMPv2-MIB::system 2>&1");
        $errors = str_replace("SNMPv2-MIB::system\n", '', $output);

        $this->assertEmpty($errors, "$highlighted_file has errors!\n$errors");
    }

    /**
     * Get a list of all mib files with the name of the mib.
     * Called for each test that uses it before class setup.
     * @return array path, filename, mib_name
     */
    public function mibFiles()
    {
        $file_list = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Config::get('mib_dir'))) as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }
            $mib_path = str_replace(Config::get('mib_dir') . '/', '', $file->getPathName());
            $file_list[$mib_path] = [
                str_replace(Config::get('install_dir'), '.', $file->getPath()),
                $file->getFilename(),
                $this->extractMibName($file->getPathname()),
            ];
        }

        return $file_list;
    }

    /**
     * List all directories inside the mib directory
     * @return array
     */
    public function mibDirs()
    {
        $dirs = glob(Config::get('mib_dir') . '/*', GLOB_ONLYDIR);
        array_unshift($dirs, Config::get('mib_dir'));

        $final_list = [];
        foreach ($dirs as $dir) {
            $relative_dir = str_replace(Config::get('mib_dir') . '/', '', $dir);
            $final_list[$relative_dir] = [$dir];
        }

        return $final_list;
    }

    /**
     * Extract the mib name from a file
     *
     * @param string $file
     * @return mixed
     * @throws Exception
     */
    private function extractMibName($file)
    {
        // extract the mib name (tried regex, but was too complex and I had to read the whole file)
        $mib_name = null;
        if ($handle = fopen($file, 'r')) {
            $header = '';
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                if (empty($trimmed) || Str::startsWith($trimmed, '--')) {
                    continue;
                }

                $header .= " $trimmed";
                if (Str::contains($trimmed, 'DEFINITIONS')) {
                    preg_match('/(\S+)\s+(?=DEFINITIONS)/', $header, $matches);
                    fclose($handle);

                    return $matches[1];
                }
            }
            fclose($handle);
        }

        throw new Exception("Could not extract mib name from file ($file)");
    }
}
