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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link https://www.librenms.org
 *
 * @copyright  2020 Lars Elgtvedt Susaas
 * @author     Lars Elgtvedt Susaas
 */

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AddHostCliTest extends DBTestCase
{
    use DatabaseTransactions;

    /** @var string */
    private $hostName = 'testHost';

    public function testCLIsnmpV1(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-c' => 'community', '--v1' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals('community', $device->community, 'Wrong snmp community');
        $this->assertEquals('v1', $device->snmpver, 'Wrong snmp version');
    }

    public function testCLIsnmpV2(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-c' => 'community', '--v2c' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals('community', $device->community, 'Wrong snmp community');
        $this->assertEquals('v2c', $device->snmpver, 'Wrong snmp version');
    }

    public function testCLIsnmpV3UserAndPW(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-u' => 'SecName', '-A' => 'AuthPW', '-X' => 'PrivPW', '--v3' => true])
        ->assertExitCode(0)
        ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, 'snmp is disabled');
        $this->assertEquals('authPriv', $device->authlevel, 'Wrong snmp v3 authlevel');
        $this->assertEquals('SecName', $device->authname, 'Wrong snmp v3 security username');
        $this->assertEquals('AuthPW', $device->authpass, 'Wrong snmp v3 authentication password');
        $this->assertEquals('PrivPW', $device->cryptopass, 'Wrong snmp v3 crypto password');
        $this->assertEquals('v3', $device->snmpver, 'Wrong snmp version');
    }

    public function testPortAssociationMode(): void
    {
        $modes = ['ifIndex', 'ifName', 'ifDescr', 'ifAlias'];
        foreach ($modes as $index => $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-p' => $mode, '--v1' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);
            $this->assertEquals($index + 1, $device->port_association_mode, 'Wrong port association mode ' . $mode);
        }
    }

    public function testSnmpTransport(): void
    {
        $modes = ['udp', 'udp6', 'tcp', 'tcp6'];
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-t' => $mode, '--v1' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals($mode, $device->transport, 'Wrong snmp transport (udp/tcp) ipv4/ipv6');
        }
    }

    public function testSnmpV3AuthProtocol(): void
    {
        $modes = \LibreNMS\SNMPCapabilities::supportedAuthAlgorithms();
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-a' => $mode, '--v3' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals(strtoupper($mode), $device->authalgo, 'Wrong snmp v3 password algorithm');
        }
    }

    public function testSnmpV3PrivacyProtocol(): void
    {
        $modes = \LibreNMS\SNMPCapabilities::supportedCryptoAlgorithms();
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-x' => $mode, '--v3' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals(strtoupper($mode), $device->cryptoalgo, 'Wrong snmp v3 crypt algorithm');
        }
    }

    public function testCLIping(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-P' => true, '-o' => 'nameOfOS', '-w' => 'hardware', '-s' => 'System', '--v1' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(1, $device->snmp_disable, 'snmp is not disabled');
        $this->assertEquals('hardware', $device->hardware, 'Wrong hardware name');
        $this->assertEquals('nameOfOS', $device->os, 'Wrong os name');
        $this->assertEquals('system', $device->sysName, 'Wrong system name');
    }

    public function testExistingDevice(): void
    {
        $this->artisan('device:add', ['device spec' => 'existing', '--force' => true])
            ->assertExitCode(0)
            ->execute();
        $this->artisan('device:add', ['device spec' => 'existing'])
            ->assertExitCode(3)
            ->execute();
    }
}
