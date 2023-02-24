<?php
/*
 * StringHelperTest.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\StringHelpers;

class StringHelperTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testInferEncoding(): void
    {
        $this->assertEquals(null, StringHelpers::inferEncoding(null));
        $this->assertEquals('', StringHelpers::inferEncoding(''));
        $this->assertEquals('~null', StringHelpers::inferEncoding('~null'));
        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding('Øverbyvegen'));

        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding(base64_decode('w5h2ZXJieXZlZ2Vu')));
        $this->assertEquals('Øverbyvegen', StringHelpers::inferEncoding(base64_decode('2HZlcmJ5dmVnZW4=')));

        config(['app.charset' => 'Shift_JIS']);
        $this->assertEquals('コンサート', StringHelpers::inferEncoding(base64_decode('g1KDk4NUgVuDZw==')));
    }

    public function testIsStringable(): void
    {
        $this->assertTrue(StringHelpers::isStringable(null));
        $this->assertTrue(StringHelpers::isStringable(''));
        $this->assertTrue(StringHelpers::isStringable('string'));
        $this->assertTrue(StringHelpers::isStringable(-1));
        $this->assertTrue(StringHelpers::isStringable(1.0));
        $this->assertTrue(StringHelpers::isStringable(false));

        $this->assertFalse(StringHelpers::isStringable([]));
        $this->assertFalse(StringHelpers::isStringable((object) []));

        $stringable = new class
        {
            public function __toString()
            {
                return '';
            }
        };
        $this->assertTrue(StringHelpers::isStringable($stringable));

        $nonstringable = new class
        {
        };
        $this->assertFalse(StringHelpers::isStringable($nonstringable));
    }
}
