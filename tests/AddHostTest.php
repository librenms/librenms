<?php
/**
 * addhostTest.php
 *
 * Tests for addhost funcion
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
 * @link https://www.librenms.org
 * @copyright  2020 Lars Elgtvedt Susaas
 * @author     Lars Elgtvedt Susaas
 */

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Config;

class AddHostTest extends DBTestCase
{
    use DatabaseTransactions;
    private $host = 'testHost';

    public function testAddsnmpV1()
    {
        addHost($this->host, 'v1', 111, 'tcp', 0, true, 'ifIndex');
        $device = Device::findByHostname($this->host);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals(1, $device->port_association_mode, 'Wrong port association mode');
        $this->assertEquals('v1', $device->snmpver, 'Wrong snmp version');
        $this->assertEquals(111, $device->port, 'Wrong snmp port');
        $this->assertEquals('tcp', $device->transport, 'Wrong snmp transport (udp/tcp)');
    }

    public function testAddsnmpV2()
    {
        addHost($this->host, 'v2c', 111, 'tcp', 0, true, 'ifName');
        $device = Device::findByHostname($this->host);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals(2, $device->port_association_mode, 'Wrong port association mode');
        $this->assertEquals(Config::get('snmp.community')[0], $device->community, 'Wrong snmp community');
        $this->assertEquals('v2c', $device->snmpver, 'Wrong snmp version');
    }

    public function testAddsnmpV3()
    {
        addHost($this->host, 'v3', 111, 'tcp', 0, true, 'ifIndex');
        $device = Device::findByHostname($this->host);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals(1, $device->port_association_mode, 'Wrong port association mode');
        $this->assertEquals(Config::get('snmp.v3')[0]['authlevel'], $device->authlevel, 'Wrong snmp v3 authlevel');
        $this->assertEquals('v3', $device->snmpver, 'Wrong snmp version');
        $this->assertEquals(Config::get('snmp.v3')[0]['authname'], $device->authname, 'Wrong snmp v3 username');
        $this->assertEquals(Config::get('snmp.v3')[0]['authpass'], $device->authpass, 'Wrong snmp v3 password');
    }

    public function testAddping()
    {
        $additional = [
            'snmp_disable' => 1,
            'os'           => 'nameOfOS',
            'hardware'     => 'hardware',
        ];
        addHost($this->host, '', 0, 0, 0, true, 'ifIndex', $additional);
        $device = Device::findByHostname($this->host);
        $this->assertNotNull($device);
        $this->assertEquals(1, $device->snmp_disable, 'snmp is not disabled');
        $this->assertEquals('hardware', $device->hardware, 'Wrong hardware');
        $this->assertEquals('nameOfOS', $device->os, 'Wrong os');
    }
}
