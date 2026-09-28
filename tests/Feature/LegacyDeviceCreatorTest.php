<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\LegacyDeviceCreator;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class LegacyDeviceCreatorTest extends DBTestCase
{
    use DatabaseTransactions;

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
        $this->assertTrue($icmpMethod->affects_availability); // same default as the other ways of adding a device

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

    public function testWithoutCredentialsTheDefaultsAreUsed(): void
    {
        // the device:add option defaults are not credentials
        $creator = new LegacyDeviceCreator(hostname: 'defaults.example.com', authname: 'root', authalgo: 'MD5', cryptoalgo: 'AES');

        $snmpMethod = $creator->getPollingMethods($creator->getDevice())->firstWhere('method_type', PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $this->assertNull($snmpMethod->secret);
    }

    public function testOnlyAVersionTriesTheDefaultCredentialsForThatVersion(): void
    {
        $v2c = $this->snmpSecret(['version' => 'v2c', 'community' => 'first']);
        $v1 = $this->snmpSecret(['version' => 'v1', 'community' => 'second']);
        $v1Other = $this->snmpSecret(['version' => 'v1', 'community' => 'third']);
        LibrenmsConfig::set('snmp.default_credentials', [$v2c->id, $v1Other->id, $v1->id]);

        $probed = [];
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, SnmpConfig $config) use (&$probed) {
            $probed[] = "$config->version $config->community";

            return $config->community === 'second'
                ? new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.9.1.1', '', 0)
                : new RawSnmpResponse('', 'Timeout: No Response', 1);
        });

        $creator = new LegacyDeviceCreator(hostname: 'v1-only.example.com', snmpver: 'v1');
        $device = $creator->getDevice();
        $snmpMethod = $creator->getPollingMethods($device)->firstWhere('method_type', PollingMethodType::Snmp);

        $this->assertTrue((new SnmpPollingMethod($backend))->discover($device, $snmpMethod)->isSuccess());
        $this->assertSame(['v1 third', 'v1 second'], $probed);
        $this->assertSame($v1->id, $snmpMethod->secret->id); // the working default replaces the version only secret
    }

    public function testForcedAddWithOnlyAVersionUsesTheFirstDefaultForThatVersion(): void
    {
        $v2c = $this->snmpSecret(['version' => 'v2c', 'community' => 'first']);
        $v3 = $this->snmpSecret(['version' => 'v3', 'authlevel' => 'authNoPriv', 'authname' => 'user', 'authpass' => 'password1']);
        LibrenmsConfig::set('snmp.default_credentials', [$v2c->id, $v3->id]);

        $this->assertTrue((new LegacyDeviceCreator(hostname: 'forced-v3.example.com', snmpver: 'v3', force: true))->execute());

        $device = Device::where('hostname', 'forced-v3.example.com')->firstOrFail();
        $this->assertSame($v3->id, $device->pollingMethod(PollingMethodType::Snmp)->secret_id);
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function snmpSecret(array $data): Secret
    {
        return Secret::create([
            'description' => 'Default ' . implode(' ', $data),
            'secret_type' => SecretType::Snmp,
            'data' => $data,
        ]);
    }
}
