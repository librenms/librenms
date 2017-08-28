<?php
/**
 * CommonFunctionsTest.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;

class CommonFunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testStrContains()
    {
        $data = 'This is a test. Just Testing.';

        $this->assertTrue(str_contains($data, 'Just'));
        $this->assertFalse(str_contains($data, 'just'));

        $this->assertTrue(str_contains($data, 'juSt', true));
        $this->assertFalse(str_contains($data, 'nope', true));

        $this->assertTrue(str_contains($data, array('not', 'this', 'This')));
        $this->assertFalse(str_contains($data, array('not', 'this')));

        $this->assertTrue(str_contains($data, array('not', 'thIs'), true));
        $this->assertFalse(str_contains($data, array('not', 'anything'), true));
    }

    public function testStartsWith()
    {
        $data = 'This is a test. Just Testing that.';

        $this->assertTrue(starts_with($data, 'This'));
        $this->assertFalse(starts_with($data, 'this'));

        $this->assertTrue(starts_with($data, 'thIs', true));
        $this->assertFalse(starts_with($data, 'test', true));

        $this->assertTrue(starts_with($data, array('this', 'Test', 'This')));
        $this->assertFalse(starts_with($data, array('this', 'Test')));

        $this->assertTrue(starts_with($data, array('Test', 'no', 'thiS'), true));
        $this->assertFalse(starts_with($data, array('just', 'Test'), true));
    }

    public function testEndsWith()
    {
        $data = 'This is a test. Just Testing';

        $this->assertTrue(ends_with($data, 'Testing'));
        $this->assertFalse(ends_with($data, 'testing'));

        $this->assertTrue(ends_with($data, 'testIng', true));
        $this->assertFalse(ends_with($data, 'test', true));

        $this->assertTrue(ends_with($data, array('this', 'Testing', 'This')));
        $this->assertFalse(ends_with($data, array('this', 'Test')));

        $this->assertTrue(ends_with($data, array('this', 'tesTing', 'no'), true));
        $this->assertFalse(ends_with($data, array('this', 'Test'), true));
    }

    public function testRrdDescriptions()
    {
        $data = 'Toner, S/N:CR_UM-16021314488.';
        $this->assertEquals('Toner, S/N CR_UM-16021314488.', safedescr($data));
    }

    public function testSetNull()
    {
        $this->assertNull(set_null('BAD-DATA'));
        $this->assertEquals(0, set_null(0));
        $this->assertEquals(25, set_null(25));
        $this->assertEquals(-25, set_null(-25));
        $this->assertEquals(99, set_null(' ', 99));
        $this->assertNull(set_null(-25, null, 0));
        $this->assertEquals(2, set_null(2, 0, 2));
    }

    public function testDisplay()
    {
        $this->assertEquals('&lt;html&gt;string&lt;/html&gt;', display('<html>string</html>'));
        $this->assertEquals('&lt;script&gt;alert("test")&lt;/script&gt;', display('<script>alert("test")</script>'));

        $tmp_config = array(
            'HTML.Allowed'    => 'b,iframe,i,ul,li,h1,h2,h3,h4,br,p',
            'HTML.Trusted'    => true,
            'HTML.SafeIframe' => true,
        );

        $this->assertEquals('<b>Bold</b>', display('<b>Bold</b>', $tmp_config));
        $this->assertEquals('', display('<script>alert("test")</script>', $tmp_config));
    }

    public function testStringToClass()
    {
        $this->assertSame('LibreNMS\OS\Os', str_to_class('OS', 'LibreNMS\\OS\\'));
        $this->assertSame('SpacesName', str_to_class('spaces name'));
        $this->assertSame('DashName', str_to_class('dash-name'));
        $this->assertSame('UnderscoreName', str_to_class('underscore_name'));
        $this->assertSame('LibreNMS\\AllOfThemName', str_to_class('all OF-thEm_NaMe', 'LibreNMS\\'));
    }
}
