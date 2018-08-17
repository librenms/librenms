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

use LibreNMS\Config;

class CommonFunctionsTest extends TestCase
{
    public function testStrContains()
    {
        $data = 'This is a test. Just Testing.';

        $this->assertTrue(str_contains($data, 'Just'));
        $this->assertFalse(str_contains($data, 'just'));

        $this->assertTrue(str_i_contains($data, 'juSt'));
        $this->assertFalse(str_i_contains($data, 'nope'));

        $this->assertTrue(str_contains($data, array('not', 'this', 'This')));
        $this->assertFalse(str_contains($data, array('not', 'this')));

        $this->assertTrue(str_i_contains($data, array('not', 'thIs')));
        $this->assertFalse(str_i_contains($data, array('not', 'anything')));
    }

    public function testStartsWith()
    {
        $data = 'This is a test. Just Testing that.';

        $this->assertTrue(starts_with($data, 'This'));
        $this->assertFalse(starts_with($data, 'this'));

        $this->assertTrue(starts_with($data, array('this', 'Test', 'This')));
        $this->assertFalse(starts_with($data, array('this', 'Test')));
    }

    public function testEndsWith()
    {
        $data = 'This is a test. Just Testing';

        $this->assertTrue(ends_with($data, 'Testing'));
        $this->assertFalse(ends_with($data, 'testing'));

        $this->assertTrue(ends_with($data, array('this', 'Testing', 'This')));
        $this->assertFalse(ends_with($data, array('this', 'Test')));
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

    public function testIsValidHostname()
    {
        $this->assertTrue(is_valid_hostname('a'), 'a');
        $this->assertTrue(is_valid_hostname('a.'), 'a.');
        $this->assertTrue(is_valid_hostname('0'), '0');
        $this->assertTrue(is_valid_hostname('a.b'), 'a.b');
        $this->assertTrue(is_valid_hostname('localhost'), 'localhost');
        $this->assertTrue(is_valid_hostname('google.com'), 'google.com');
        $this->assertTrue(is_valid_hostname('news.google.co.uk'), 'news.google.co.uk');
        $this->assertTrue(is_valid_hostname('xn--fsqu00a.xn--0zwm56d'), 'xn--fsqu00a.xn--0zwm56d');
        $this->assertTrue(is_valid_hostname('www.averylargedomainthatdoesnotreallyexist.com'), 'www.averylargedomainthatdoesnotreallyexist.com');
        $this->assertTrue(is_valid_hostname('cont-ains.h-yph-en-s.com'), 'cont-ains.h-yph-en-s.com');
        $this->assertTrue(is_valid_hostname('cisco-3750x'), 'cisco-3750x');
        $this->assertFalse(is_valid_hostname('cisco_3750x'), 'cisco_3750x');
        $this->assertFalse(is_valid_hostname('goo gle.com'), 'goo gle.com');
        $this->assertFalse(is_valid_hostname('google..com'), 'google..com');
        $this->assertFalse(is_valid_hostname('google.com '), 'google.com ');
        $this->assertFalse(is_valid_hostname('google-.com'), 'google-.com');
        $this->assertFalse(is_valid_hostname('.google.com'), '.google.com');
        $this->assertFalse(is_valid_hostname('..google.com'), '..google.com');
        $this->assertFalse(is_valid_hostname('<script'), '<script');
        $this->assertFalse(is_valid_hostname('alert('), 'alert(');
        $this->assertFalse(is_valid_hostname('.'), '.');
        $this->assertFalse(is_valid_hostname('..'), '..');
        $this->assertFalse(is_valid_hostname(' '), 'Just a space');
        $this->assertFalse(is_valid_hostname('-'), '-');
        $this->assertFalse(is_valid_hostname(''), 'Empty string');
    }

    public function testResolveGlues()
    {
        $this->dbSetUp();

        $this->assertFalse(ResolveGlues(array('dbSchema'), 'device_id'));

        $this->assertSame(array('devices.device_id'), ResolveGlues(array('devices'), 'device_id'));
        $this->assertSame(array('sensors.device_id'), ResolveGlues(array('sensors'), 'device_id'));

        // does not work right with current code
//        $expected = array('bill_data.bill_id', 'bill_ports.port_id', 'ports.device_id');
//        $this->assertSame($expected, ResolveGlues(array('bill_data'), 'device_id'));

        $expected = array('application_metrics.app_id', "applications.device_id");
        $this->assertSame($expected, ResolveGlues(array('application_metrics'), 'device_id'));


        $expected = array('state_translations.state_index_id', 'sensors_to_state_indexes.sensor_id', 'sensors.device_id');
        $this->assertSame($expected, ResolveGlues(array('state_translations'), 'device_id'));

        $expected = array('ipv4_addresses.port_id', 'ports.device_id');
        $this->assertSame($expected, ResolveGlues(array('ipv4_addresses'), 'device_id'));

        $this->dbTearDown();
    }

    public function testFormatHostname()
    {
        $device_dns = [
            'hostname' => 'test.librenms.org',
            'sysName' => 'Testing DNS'
        ];
        $device_ip = [
            'hostname' => '192.168.1.2',
            'sysName' => 'Testing IP'
        ];

        // both false
        Config::set('force_ip_to_sysname', false);
        Config::set('force_hostname_to_sysname', false);
        $this->assertEquals('test.librenms.org', format_hostname($device_dns));
        $this->assertEquals('Not DNS', format_hostname($device_dns, 'Not DNS'));
        $this->assertEquals('192.168.5.5', format_hostname($device_dns, '192.168.5.5'));
        $this->assertEquals('192.168.1.2', format_hostname($device_ip));
        $this->assertEquals('hostname.like', format_hostname($device_ip, 'hostname.like'));
        $this->assertEquals('10.10.10.10', format_hostname($device_ip, '10.10.10.10'));

        // ip to sysname
        Config::set('force_ip_to_sysname', true);
        Config::set('force_hostname_to_sysname', false);
        $this->assertEquals('test.librenms.org', format_hostname($device_dns));
        $this->assertEquals('Not DNS', format_hostname($device_dns, 'Not DNS'));
        $this->assertEquals('Testing DNS', format_hostname($device_dns, '192.168.5.5'));
        $this->assertEquals('Testing IP', format_hostname($device_ip));
        $this->assertEquals('hostname.like', format_hostname($device_ip, 'hostname.like'));
        $this->assertEquals('Testing IP', format_hostname($device_ip, '10.10.10.10'));

        // dns to sysname
        Config::set('force_ip_to_sysname', false);
        Config::set('force_hostname_to_sysname', true);
        $this->assertEquals('Testing DNS', format_hostname($device_dns));
        $this->assertEquals('Not DNS', format_hostname($device_dns, 'Not DNS'));
        $this->assertEquals('192.168.5.5', format_hostname($device_dns, '192.168.5.5'));
        $this->assertEquals('192.168.1.2', format_hostname($device_ip));
        $this->assertEquals('Testing IP', format_hostname($device_ip, 'hostname.like'));
        $this->assertEquals('10.10.10.10', format_hostname($device_ip, '10.10.10.10'));

        // both true
        Config::set('force_ip_to_sysname', true);
        Config::set('force_hostname_to_sysname', true);
        $this->assertEquals('Testing DNS', format_hostname($device_dns));
        $this->assertEquals('Not DNS', format_hostname($device_dns, 'Not DNS'));
        $this->assertEquals('Testing DNS', format_hostname($device_dns, '192.168.5.5'));
        $this->assertEquals('Testing IP', format_hostname($device_ip));
        $this->assertEquals('Testing IP', format_hostname($device_ip, 'hostname.like'));
        $this->assertEquals('Testing IP', format_hostname($device_ip, '10.10.10.10'));
    }

    public function testPortAssociation()
    {
        $modes = [
            1 => 'ifIndex',
            2 => 'ifName',
            3 => 'ifDescr',
            4 => 'ifAlias',
        ];

        $this->assertEquals($modes, get_port_assoc_modes());
        $this->assertEquals('ifIndex', get_port_assoc_mode_name(1));
        $this->assertEquals(1, get_port_assoc_mode_id('ifIndex'));
        $this->assertFalse(get_port_assoc_mode_name(666));
        $this->assertFalse(get_port_assoc_mode_id('lucifer'));
    }
}
