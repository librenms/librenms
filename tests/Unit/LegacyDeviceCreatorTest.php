<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\LegacyDeviceCreator;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\TestCase;

class LegacyDeviceCreatorTest extends TestCase
{
    public function testCreatesDeviceAndPollingMethodsForV2c(): void
    {
        $creator = new LegacyDeviceCreator(
            hostname: 'router1.example.com',
            display_template: '{{ $hostname }}',
            poller_group: 2,
            snmpver: 'v2c',
            community: 'secret-community',
            port: 1161,
            transport: 'tcp',
            port_association_mode: 'ifName',
        );

        $device = $creator->getDevice();
        $this->assertEquals('router1.example.com', $device->hostname);
        $this->assertEquals('{{ $hostname }}', $device->display_template);
        $this->assertEquals(2, $device->poller_group);

        $methods = $creator->getPollingMethods($device);
        $this->assertCount(2, $methods);

        $icmpMethod = $methods->firstWhere('method_type', PollingMethodType::Icmp);
        $this->assertNotNull($icmpMethod);
        $this->assertTrue($icmpMethod->enabled);
        $this->assertFalse($icmpMethod->affects_availability);

        $snmpMethod = $methods->firstWhere('method_type', PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $this->assertTrue($snmpMethod->enabled);
        $this->assertTrue($snmpMethod->affects_availability);
        $this->assertEquals(1161, $snmpMethod->settings['port']);
        $this->assertEquals('tcp', $snmpMethod->settings['transport']);
        $this->assertEquals('ifName', $snmpMethod->settings['port_association_mode']);

        $secret = $snmpMethod->secret;
        $this->assertNotNull($secret);
        $this->assertEquals('v2c', $secret->data['version']);
        $this->assertEquals('secret-community', $secret->data['community']);
    }

    public function testCreatesDeviceAndPollingMethodsForV3(): void
    {
        $creator = new LegacyDeviceCreator(
            hostname: 'switch1.example.com',
            snmpver: 'v3',
            authname: 'v3user',
            authpass: 'authPassword123',
            authalgo: 'SHA-256',
            cryptopass: 'privPassword123',
            cryptoalgo: 'AES-256-CFB',
            authlevel: 'authPriv',
        );

        $device = $creator->getDevice();
        $methods = $creator->getPollingMethods($device);

        $snmpMethod = $methods->firstWhere('method_type', PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $secret = $snmpMethod->secret;
        $this->assertNotNull($secret);
        $this->assertEquals('v3', $secret->data['version']);
        $this->assertEquals('v3user', $secret->data['authname']);
        $this->assertEquals('authPassword123', $secret->data['authpass']);
        $this->assertEquals('SHA-256', $secret->data['authalgo']);
        $this->assertEquals('privPassword123', $secret->data['cryptopass']);
        $this->assertEquals('AES-256-CFB', $secret->data['cryptoalgo']);
        $this->assertEquals('authPriv', $secret->data['authlevel']);
    }

    public function testPingOnlyOmitsSnmpMethod(): void
    {
        $creator = new LegacyDeviceCreator(
            hostname: 'ping-host.example.com',
            ping_only: true,
            os: 'ping',
            hardware: 'Generic Ping',
            sysName: 'ping-host',
        );

        $device = $creator->getDevice();
        $this->assertEquals('ping', $device->os);
        $this->assertEquals('Generic Ping', $device->hardware);
        $this->assertEquals('ping-host', $device->sysName);

        $methods = $creator->getPollingMethods($device);
        $this->assertCount(1, $methods);
        $this->assertNotNull($methods->firstWhere('method_type', PollingMethodType::Icmp));
        $this->assertNull($methods->firstWhere('method_type', PollingMethodType::Snmp));
    }
}
