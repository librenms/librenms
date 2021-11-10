<?php

/**
 * addhostCliTest.php
 *
 * Tests for lnms device:add cli tool
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
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Add Host CLI')]
final class AddHostCliTest extends DBTestCase
{
    use DatabaseTransactions;

    /** @var string */
    private $hostName = 'testHost';

    #[TestDox('CLI SNMP v1')]
    public function testCLIsnmpV1(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-c' => 'community', '--v1' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);



        $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $secret = $snmpMethod->secret;
        $this->assertNotNull($secret);
        $this->assertEquals('community', $secret->data['community']);
        $this->assertEquals('v1', $secret->data['version']);

        $icmpMethod = $device->getPollingMethod(PollingMethodType::Icmp);
        $this->assertNotNull($icmpMethod);
        $this->assertTrue($icmpMethod->enabled);
    }

    #[TestDox('CLI SNMP v2')]
    public function testCLIsnmpV2(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-c' => 'community', '--v2c' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);



        $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $secret = $snmpMethod->secret;
        $this->assertNotNull($secret);
        $this->assertEquals('community', $secret->data['community']);
        $this->assertEquals('v2c', $secret->data['version']);
    }

    #[TestDox('CLI SNMP v3 user and password')]
    public function testCLIsnmpV3UserAndPW(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-u' => 'SecName', '-A' => 'AuthPW', '-X' => 'PrivPW', '--v3' => true])
        ->assertExitCode(0)
        ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);



        $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $secret = $snmpMethod->secret;
        $this->assertNotNull($secret);
        $this->assertEquals('v3', $secret->data['version']);
        $this->assertEquals('SecName', $secret->data['authname']);
        $this->assertEquals('AuthPW', $secret->data['authpass']);
        $this->assertEquals('PrivPW', $secret->data['cryptopass']);
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

    #[TestDox('SNMP transport')]
    public function testSnmpTransport(): void
    {
        $modes = ['udp', 'udp6', 'tcp', 'tcp6'];
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-t' => $mode, '--v1' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
            $this->assertEquals($mode, $snmpMethod->settings['transport'], 'Wrong snmp transport (udp/tcp) ipv4/ipv6');
        }
    }

    #[TestDox('SNMP v3 auth protocol')]
    public function testSnmpV3AuthProtocol(): void
    {
        $modes = \LibreNMS\SNMPCapabilities::supportedAuthAlgorithms();
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-a' => $mode, '--v3' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
            $this->assertEquals(strtoupper((string) $mode), $snmpMethod->secret->data['authalgo'], 'Wrong snmp v3 password algorithm');
        }
    }

    #[TestDox('SNMP v3 privacy protocol')]
    public function testSnmpV3PrivacyProtocol(): void
    {
        $modes = \LibreNMS\SNMPCapabilities::supportedCryptoAlgorithms();
        foreach ($modes as $mode) {
            $host = 'hostName' . $mode;
            $this->artisan('device:add', ['device spec' => $host, '--force' => true, '-x' => $mode, '--v3' => true])
                ->assertExitCode(0)
                ->execute();

            $device = Device::findByHostname($host);
            $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
            $this->assertEquals(strtoupper((string) $mode), $snmpMethod->secret->data['cryptoalgo'], 'Wrong snmp v3 crypt algorithm');
        }
    }

    #[TestDox('CLI ping')]
    public function testCLIping(): void
    {
        $this->artisan('device:add', ['device spec' => $this->hostName, '--force' => true, '-P' => true, '-o' => 'nameOfOS', '-w' => 'hardware', '-s' => 'System', '--v1' => true])
            ->assertExitCode(0)
            ->execute();

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);


        $this->assertEquals('hardware', $device->hardware, 'Wrong hardware name');
        $this->assertEquals('nameOfOS', $device->os, 'Wrong os name');
        $this->assertEquals('system', $device->sysName, 'Wrong system name');

        $this->assertNull($device->getPollingMethod(PollingMethodType::Snmp));
        $this->assertNotNull($device->getPollingMethod(PollingMethodType::Icmp));
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
