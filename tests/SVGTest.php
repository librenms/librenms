<?php
/**
 * SVGTest.php
 *
 * -Description-
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

use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Class SVGTest
 * @group os
 */
class SVGTest extends TestCase
{
    public function testSVGContainsPNG()
    {
        foreach ($this->getSvgFiles() as $file => $_unused) {
            $svg = file_get_contents($file);

            $this->assertFalse(
                Str::contains($svg, 'data:image/'),
                "$file contains a bitmap image, please use a regular png or valid svg"
            );
        }
    }

    public function testSVGHasLengthWidth()
    {
        foreach ($this->getSvgFiles() as $file => $_unused) {
            if ($file == 'html/images/safari-pinned-tab.svg') {
                continue;
            }

            $svg = file_get_contents($file);

            $this->assertEquals(
                0,
                preg_match('/<svg[^>]*(length|width)=/', $svg, $matches),
                "$file: SVG files must not contain length or width attributes "
            );
        }
    }

    public function testSVGHasViewBox()
    {
        foreach ($this->getSvgFiles() as $file => $_unused) {
            $svg = file_get_contents($file);

            $this->assertTrue(
                Str::contains($svg, 'viewBox'),
                "$file: SVG files must have the viewBox attribute set"
            );
        }
    }

    private function getSvgFiles()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('html/images'));

        return new RegexIterator($iterator, '/^.+\.svg$/i', RecursiveRegexIterator::GET_MATCH);
    }
}
