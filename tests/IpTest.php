<?php
/**
 * IpTest.php
 *
 * Tests Util\IP classes
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

use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;

class IpTest extends TestCase
{
    public function testIsValid()
    {
        $this->assertTrue(IP::isValid('192.168.0.1'));
        $this->assertTrue(IP::isValid('192.168.0.1'));
        $this->assertTrue(IP::isValid('2001:4860:4860::8888'));
        $this->assertTrue(IPv4::isValid('192.168.0.1'));
        $this->assertTrue(IPv6::isValid('2001:4860:4860::8888'));
        $this->assertFalse(IPv4::isValid('2001:4860:4860::8888'));
        $this->assertFalse(IPv6::isValid('192.168.0.1'));
        $this->assertFalse(IP::isValid('not_an_ip'));

        $this->assertTrue(IPv4::isValid('8.8.8.8', true));
        $this->assertTrue(IP::isValid('8.8.8.8', true));
        $this->assertTrue(IPv4::isValid('192.168.0.1', true));
        $this->assertTrue(IPv6::isValid('FF81::', true));
        $this->assertTrue(IPv6::isValid('2001:db8:85a3::8a2e:370:7334', true));
        $this->assertFalse(IPv4::isValid('127.0.0.1', true));
        $this->assertFalse(IPv6::isValid('::1', true));
        $this->assertFalse(IP::isValid('169.254.1.1', true));
        $this->assertFalse(IP::isValid('fe80::1', true));
        $this->assertFalse(IPv4::isValid('fe80::1', true));
        $this->assertFalse(IP::isValid('Falafel', true));
    }

    public function testIpParse()
    {
        $this->assertEquals('192.168.0.1', IP::parse('192.168.0.1'));
        $this->assertEquals('127.0.0.1', IP::parse('127.0.0.1'));
        $this->assertEquals('2001:db8:85a3::8a2e:370:7334', IP::parse('2001:db8:85a3::8a2e:370:7334'));
        $this->assertEquals('::1', IP::parse('::1'));

        $this->assertEquals('192.168.0.1', new IPv4('192.168.0.1'));
        $this->assertEquals('127.0.0.1', new IPv4('127.0.0.1'));
        $this->assertEquals('2001:db8:85a3::8a2e:370:7334', new IPv6('2001:db8:85a3::8a2e:370:7334'));
        $this->assertEquals('::1', new IPv6('::1'));

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        new IPv6('192.168.0.1');
        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        new IPv6('127.0.0.1');
        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        new IPv4('2001:db8:85a3::8a2e:370:7334');
        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        new IPv4('::1');
    }

    public function testHexToIp()
    {
        $this->assertEquals('192.168.1.254', IP::fromHexString('c0 a8 01 fe'));
        $this->assertEquals('192.168.1.254', IP::fromHexString('c0a801fe'));
        $this->assertEquals('192.168.1.254', IP::fromHexString('c0 a8 01 fe '));
        $this->assertEquals('192.168.1.254', IP::fromHexString('"c0 a8 01 fe"'));
        $this->assertEquals('192.168.1.254', IP::fromHexString('192.168.1.254'));

        $this->assertEquals('2001:db8::2:1', IP::fromHexString('2001:db8::2:1'));
        $this->assertEquals('2001:db8::2:1', IP::fromHexString('20 01 0d b8 00 00 00 00 00 00 00 00 00 02 00 01'));
        $this->assertEquals('2001:db8::2:1', IP::fromHexString('"20 01 0d b8 00 00 00 00 00 00 00 00 00 02 00 01"'));
        $this->assertEquals('2001:db8::2:1', IP::fromHexString('"20:01:0d:b8:00:00:00:00:00:00:00:00:00:02:00:01"'));
        $this->assertEquals('2001:db8::2:1', IP::fromHexString('"20.01.0d.b8.00.00.00.00.00.00.00.00.00.02.00.01"'));
        $this->assertEquals('2001:db8::2:1', IP::fromHexString('20010db8000000000000000000020001'));

        $this->assertEquals('::', IP::fromHexString('00000000000000000000000000000000'));

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        IP::fromHexString('c0 a8 01 01 fe');

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        IP::fromHexString('20 01 0d b8 00 00 00 00 00 00 00 00 00 02 00 00 00 01');
    }

    public function testNetmask2Cidr()
    {
        $this->assertSame(32, IPv4::netmask2cidr('255.255.255.255'));
        $this->assertSame(30, IPv4::netmask2cidr('255.255.255.252'));
        $this->assertSame(26, IPv4::netmask2cidr('255.255.255.192'));
        $this->assertSame(16, IPv4::netmask2cidr('255.255.0.0'));
    }

    public function testIpInNetwork()
    {
        $this->assertTrue(IP::parse('192.168.1.0')->inNetwork('192.168.1.0/24'));
        $this->assertTrue(IP::parse('192.168.1.32')->inNetwork('192.168.1.0/24'));
        $this->assertTrue(IP::parse('192.168.1.254')->inNetwork('192.168.1.0/24'));
        $this->assertTrue(IP::parse('192.168.1.255')->inNetwork('192.168.1.0/24'));
        $this->assertFalse(IP::parse('192.168.1.1')->inNetwork('192.168.1.0'));
        $this->assertFalse(IP::parse('10.4.3.2')->inNetwork('192.168.1.0/16'));

        $this->assertTrue(IP::parse('::1')->inNetwork('::/64'));
        $this->assertTrue(IP::parse('2001:db7:85a3::8a2e:370:7334')->inNetwork('::/0'));
        $this->assertFalse(IP::parse('2001:db7:85a3::8a2e:370:7334')->inNetwork('2001:db8:85a3::/64'));
        $this->assertTrue(IP::parse('2001:db8:85a3::8a2e:370:7334')->inNetwork('2001:db8:85a3::/64'));
        $this->assertTrue(IP::parse('2001:db8:85a3::8a2e:370:7334')->inNetwork('2001:db8:85a3::8a2e:370:7334/128'));
        $this->assertFalse(IP::parse('2001:db8:85a3::8a2e:370:7335')->inNetwork('2001:db8:85a3::8a2e:370:7334/128'));

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        IP::parse('42')->inNetwork('192.168.1.0/4');

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        IP::parse('192.168.1.256')->inNetwork('192.168.1.0/24');

        $this->expectException('LibreNMS\Exceptions\InvalidIpException');
        IP::parse('192.168.1.0')->inNetwork('192.168.1.0');
    }

    public function testIpv6Compress()
    {
        $this->assertEquals('::1', IP::parse('0:0:0:0:0:0:0:1'));
        $this->assertSame('::1', IP::parse('0:0:0:0:0:0:0:1')->compressed());
        $this->assertSame('::', IP::parse('0:0:0:0:0:0:0:0')->compressed());
        $this->assertSame('::', IP::parse('0000:0000:0000:0000:0000:0000:0000:0000')->compressed());
        $this->assertSame('2001:db8:85a3::8a2e:370:7334', IP::parse('2001:0db8:85a3:0000:0000:8a2e:0370:7334')->compressed());
    }

    public function testIpv6Uncompress()
    {
        $this->assertSame('0000:0000:0000:0000:0000:0000:0000:0001', IP::parse('::1')->uncompressed());
        $this->assertSame('0000:0000:0000:0000:0000:0000:0000:0000', IP::parse('::')->uncompressed());
        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', IP::parse('2001:db8:85a3::8a2e:370:7334')->uncompressed());
        $this->assertSame('2001:0db8:85a3:0001:0001:8a2e:0370:7334', IP::parse('2001:db8:85a3:1:1:8a2e:370:7334')->uncompressed());
    }

    public function testNetworkFromIp()
    {
        $this->assertSame('192.168.1.0/24', IP::parse('192.168.1.34')->getNetwork(24));
        $this->assertSame('192.168.1.0/24', IP::parse('192.168.1.0/24')->getNetwork());
        $this->assertSame('192.168.1.0/24', IP::parse('192.168.1.255/24')->getNetwork());
        $this->assertSame('192.168.1.0', IP::parse('192.168.1.34')->getNetworkAddress(24));
        $this->assertSame('192.168.16.0/20', IP::parse('192.168.23.45')->getNetwork(20));

        $this->assertSame('2001:db8:85a3::/64', IP::parse('2001:db8:85a3:0:341a:8a2e:0370:7334')->getNetwork(64));
        $this->assertSame('2001:db8:85a3:3400::/54', IP::parse('2001:db8:85a3:369a::370:7334/54')->getNetwork());
        $this->assertSame('2001:db8:85a3:3600::/55', IP::parse('2001:db8:85a3:369a::370:7334/55')->getNetwork());
        $this->assertSame('2001:db8:85a3:341a::370:7334/128', IP::parse('2001:db8:85a3:341a::370:7334')->getNetwork());
        $this->assertSame('2001:db8:85a3:341a::370:7334', IP::parse('2001:db8:85a3:341a::370:7334/128')->getNetworkAddress());
    }

    public function testToSnmpIndex()
    {
        $this->assertSame('192.168.1.5', IP::parse('192.168.1.5')->toSnmpIndex());
        $this->assertSame('32.1.8.120.224.0.130.226.134.161.0.0.0.0.0.0', IP::parse('2001:878:e000:82e2:86a1:0000:0000:0000')->toSnmpIndex());
        $this->assertSame('0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1', IP::parse('::1')->toSnmpIndex());
        $this->assertSame('32.1.8.120.0.0.224.0.0.130.0.226.0.136.0.161', IP::parse('2001:0878:0000:e000:0082:00e2:0088:00a1')->toSnmpIndex());
    }
}
