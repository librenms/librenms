<?php
/**
 * addhostCliTest.php
 *
 * Tests for addhost.php cli tool
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
 * @link http://librenms.org
 * @copyright  2020 Lars Elgtvedt Susaas
 * @author     Lars Elgtvedt Susaas
 */

/*
    Usage (SNMPv1/2c)    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <hostname> [community] [v1|v2c] [port] [udp|udp6|tcp|tcp6]
    Usage (SNMPv3)       :
        Config Defaults  : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <hostname> any v3 [user] [port] [udp|udp6|tcp|tcp6]
        No Auth, No Priv : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <hostname> nanp v3 [user] [port] [udp|udp6|tcp|tcp6]
        Auth, No Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <hostname> anp v3 <user> <password> [md5|sha] [port] [udp|udp6|tcp|tcp6]
        Auth,    Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <hostname> ap v3 <user> <password> <enckey> [md5|sha] [aes|des] [port] [udp|udp6|tcp|tcp6]
    Usage (ICMP only)    : ./addhost.php [-g <poller group>] [-f] -P <hostname> [os] [hardware]

    -g <poller group> allows you to add a device to be pinned to a specific poller when using distributed polling. X can be any number associated with a poller group
    -f forces the device to be added by skipping the icmp and snmp check against the host.
    -p <port assoc mode> allow you to set a port association mode for this device. By default ports are associated by 'ifIndex'.
        For Linux/Unix based devices 'ifName' or 'ifDescr' might be useful for a stable iface mapping.
        The default for this installation is 'ifIndex'
        Valid port assoc modes are: ifIndex, ifName, ifDescr, ifAlias
    -b Add the host with SNMP if it replies to it, otherwise only ICMP.
    -P Add the host with only ICMP, no SNMP or OS discovery.
*/

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class AddhostCliTest extends DBTestCase
{
    use DatabaseTransactions;
    private $hostName = "testHost";

    public function tearDown() : void
    {
        exec("./delhost.php ".$this->hostName." ");
        parent::tearDown();
    }

    public function testCLIsnmpV1()
    {
        exec("./addhost.php -g poller_group -f -p ifIndex ".$this->hostName." community v1 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(1, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v1", $device->snmpver, "Wrong snmp version");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV2()
    {
        exec("./addhost.php -g poller_group -f -p ifName ".$this->hostName." community v2c 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(2, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v2c", $device->snmpver, "Wrong snmp version");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3Any()
    {
        exec("./addhost.php -g poller_group -f -p ifDescr ".$this->hostName." any v3 username 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(3, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("noAuthNoPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
//      $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3NoAuthNoPriv()
    {
        exec("./addhost.php -g poller_group -f -p ifAlias ".$this->hostName." nanp v3 username 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(4, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("noAuthNoPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
//      $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3AuthNoPriv()
    {
        exec("./addhost.php -g poller_group -f -p ifIndex ".$this->hostName." anp v3 username password 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(1, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("authNoPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
        $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals("password", $device->authpass, "Wrong snmp v3 password");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3AuthNoPrivMd5()
    {
        exec("./addhost.php -g poller_group -f -p ifIndex ".$this->hostName." anp v3 username password md5 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(1, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("authNoPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
        $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals("password", $device->authpass, "Wrong snmp v3 password");
        $this->assertEquals("MD5", $device->authalgo, "Wrong snmp v3 password algoritme");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3AuthPrivDES()
    {
        exec("./addhost.php -g poller_group -f -p ifIndex ".$this->hostName." ap v3 username password encoder sha des 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(1, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("authPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
        $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals("password", $device->authpass, "Wrong snmp v3 password");
        $this->assertEquals("encoder", $device->cryptopass, "Wrong snmp v3 crypto password");
        $this->assertEquals("SHA", $device->authalgo, "Wrong snmp v3 auth algoritme");
        $this->assertEquals("DES", $device->cryptoalgo, "Wrong snmp v3 crypt algoritme");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIsnmpV3AuthPrivAES()
    {
        exec("./addhost.php -g poller_group -f -p ifIndex ".$this->hostName." ap v3 username password encoder md5 aes 111 tcp");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals(1, $device->port_association_mode, "Wrong port association mode");
        $this->assertEquals("authPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
        $this->assertEquals("username", $device->authname, "Wrong snmp v3 username");
        $this->assertEquals("password", $device->authpass, "Wrong snmp v3 password");
        $this->assertEquals("encoder", $device->cryptopass, "Wrong snmp v3 crypto password");
        $this->assertEquals("MD5", $device->authalgo, "Wrong snmp v3 auth algoritme");
        $this->assertEquals("AES", $device->cryptoalgo, "Wrong snmp v3 crypt algoritme");
        $this->assertEquals(111, $device->port, "Wrong snmp port");
        $this->assertEquals("tcp", $device->transport, "Wrong snmp transport (udp/tcp)");
    }

    public function testCLIping()
    {
        exec("./addhost.php -g poller_group -f -P ".$this->hostName." nameOfOS hardware");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(1, $device->snmp_disable, "snmp is not disabled");
        $this->assertEquals("hardware", $device->hardware, "Wrong os");
        $this->assertEquals("nameOfOS", $device->os, "Wrong hardware");
    }
}
