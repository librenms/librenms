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

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class AddHostCliTest extends DBTestCase
{
    use DatabaseTransactions;
    private $hostName = "testHost";

    public function testCLIsnmpV1()
    {
        $result = \Artisan::call('device:add '.$this->hostName.' -force -ccommunity --v1');
        $this->assertEquals(0, $result, "command returned non zero value");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v1", $device->snmpver, "Wrong snmp version");
    }

    public function testCLIsnmpV2()
    {
        $result = \Artisan::call('device:add '.$this->hostName.' -force -ccommunity --v2c');
        $this->assertEquals(0, $result, "command returned non zero value");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v2c", $device->snmpver, "Wrong snmp version");
    }

    public function testCLIsnmpV3UserAndPW()
    {
        $result = \Artisan::call('device:add '.$this->hostName.' -force -uSecName -AAuthPW -XPrivPW --v3');
        $this->assertEquals(0, $result, "command returned non zero value");
        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("authPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("SecName", $device->authname, "Wrong snmp v3 security username");
        $this->assertEquals("AuthPW", $device->authpass, "Wrong snmp v3 authentication password");
        $this->assertEquals("PrivPW", $device->cryptopass, "Wrong snmp v3 crypto password");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
    }

    public function testPortAssociationMode()
    {
        $modes = array('ifIndex', 'ifName', 'ifDescr', 'ifAlias');
        foreach ($modes as $index => $mode) {
            $host = "hostName".$mode;
            $result = \Artisan::call('device:add '.$host.' -force -p '.$mode.' --v1');
            $this->assertEquals(0, $result, "command returned non zero value");
            $device = Device::findByHostname($host);
            $this->assertNotNull($device);
            $this->assertEquals($index+1, $device->port_association_mode, "Wrong port association mode ".$mode);
        }
    }

    public function testSnmpTransport()
    {
        $modes = array('udp', 'udp6', 'tcp', 'tcp6');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $result = \Artisan::call('device:add '.$host.' -force -t '.$mode.' --v1');
            $this->assertEquals(0, $result, "command returned non zero value");
            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals($mode, $device->transport, "Wrong snmp transport (udp/tcp) ipv4/ipv6");
        }
    }

    public function testSnmpV3AuthProtocol()
    {
//        $modes = array('md5', 'sha', 'sha-512', 'sha-384', 'sha-256', 'sha-224');
        $modes = array('md5', 'sha');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $result = \Artisan::call('device:add '.$host.' -force -a '.$mode.' --v3');
            $this->assertEquals(0, $result, "command returned non zero value");
            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals(strtoupper($mode), $device->authalgo, "Wrong snmp v3 password algoritme");
        }
    }

    public function testSnmpV3PrivacyProtocol()
    {
        $modes = array('des', 'aes');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $result = \Artisan::call('device:add '.$host.' -force -x '.$mode.' --v3');
            $this->assertEquals(0, $result, "command returned non zero value");
            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals(strtoupper($mode), $device->cryptoalgo, "Wrong snmp v3 crypt algoritme");
        }
    }

    public function testCLIping()
    {
        $result = \Artisan::call('device:add '.$this->hostName.' -force -P -onameOfOS -whardware -sSystem --v1');
        $this->assertEquals(0, $result, "command returned non zero value");

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(1, $device->snmp_disable, "snmp is not disabled");
        $this->assertEquals("hardware", $device->hardware, "Wrong hardware name");
        $this->assertEquals("nameOfOS", $device->os, "Wrong os name");
        $this->assertEquals("System", $device->sysName, "Wrong system name");
    }
}
