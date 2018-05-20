<?php
/**
 * DocTest.php
 *
 * Tests for Docs.
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Tests;

use Symfony\Component\Yaml\Yaml;

class DocTest extends TestCase
{
    public function testDocExist()
    {
        $mkdocs = Yaml::parse(file_get_contents(__DIR__ . '/../mkdocs.yml'));
        $dir    = __DIR__ . '/../doc/';
        $files  = rtrim(`find $dir -name '*.md'`);
        $files = explode(PHP_EOL, str_replace($dir, '', $files));
        foreach ($files as $page) {
            $this->assertTrue(in_multi_array($page, $mkdocs), "The doc $page doesn't exist in mkdocs.yml, please add it to the relevant section");
        }
    }
}
