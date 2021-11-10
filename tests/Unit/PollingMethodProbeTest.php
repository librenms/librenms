<?php

namespace LibreNMS\Tests\Unit;

use App\Casts\EncryptedArray;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Probe\IcmpProbe;
use LibreNMS\Polling\Method\Probe\IpmiProbe;
use LibreNMS\Polling\Method\Probe\ProbeResult;
use LibreNMS\Polling\Method\Probe\SnmpProbe;
use LibreNMS\Polling\Method\Probe\UnixAgentProbe;
use LibreNMS\Tests\TestCase;

final class PollingMethodProbeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    }

    public function testDefinitionCreatesFreshProbeInstance(): void
    {
        $this->assertInstanceOf(SnmpProbe::class, PollingMethodType::Snmp->definition()->probe());
        $this->assertInstanceOf(IcmpProbe::class, PollingMethodType::Icmp->definition()->probe());
        $this->assertInstanceOf(IpmiProbe::class, PollingMethodType::Ipmi->definition()->probe());
        $this->assertInstanceOf(UnixAgentProbe::class, PollingMethodType::UnixAgent->definition()->probe());
    }

    public function testUnixAgentProbeUsesResolvedConfigPortAndTimeout(): void
    {
        $device = new Device(['hostname' => '127.0.0.1']);
        $unixMethod = DevicePollingMethod::transient(
            type: PollingMethodType::UnixAgent,
            settings: ['port' => 6556, 'timeout' => 5],
            device: $device,
            affectsAvailability: true,
            enabled: true,
        );
        $device->setRelation('pollingMethods', collect([$unixMethod]));

        $probe = PollingMethodType::UnixAgent->definition()->probe();

        $result = $probe->check($device);
        $this->assertIsBool($result->isSuccess());
        $this->assertEquals(6556, $result->stat('port'));
        $this->assertEquals(5, $result->stat('timeout'));
    }

    public function testDisablingPollingMethodTrimsStatusReasonAndUpdatesDeviceStatus(): void
    {
        $device = \Mockery::mock(Device::class)->makePartial();
        $device->status = false;
        $device->status_reason = 'icmp,snmp';
        $device->shouldReceive('save')->andReturn(true);

        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => false,
            'device_id' => 1,
        ]);
        $method->setRelation('device', $device);

        $observer = new \App\Observers\DevicePollingMethodObserver();
        $observer->saved($method);

        $this->assertEquals('icmp', $device->status_reason);
        $this->assertEquals(0, $device->status);

        // Disable ICMP as well
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => false,
            'device_id' => 1,
        ]);
        $icmpMethod->setRelation('device', $device);
        $observer->saved($icmpMethod);

        $this->assertEquals('', $device->status_reason);
        $this->assertEquals(1, $device->status);
    }

    public function testProbeResultStoresAndRetrievesStats(): void
    {
        $result = ProbeResult::success([
            'latency' => 12.5,
            'response_code' => 200,
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(12.5, $result->stat('latency'));
        $this->assertEquals(200, $result->stat('response_code'));
        $this->assertNull($result->stat('missing'));
        $this->assertEquals('default', $result->stat('missing', 'default'));
    }

    public function testSecretDecryptionExceptionThrownOnInvalidPayload(): void
    {
        $cast = new EncryptedArray();
        $secret = new Secret();

        $this->expectException(SecretDecryptionException::class);
        $cast->get($secret, 'data', 'invalid-encrypted-payload', []);
    }

    public function testSeparateDevicesMayHaveIndependentSettingsSecretsAndState(): void
    {
        $sharedSecret = new Secret([
            'secret_type' => 'snmp',
            'data' => [
                'version' => 'v2c',
                'community' => 'shared-community',
            ],
        ]);

        $customSecret = new Secret([
            'secret_type' => 'snmp',
            'data' => [
                'version' => 'v3',
                'authlevel' => 'authPriv',
                'authname' => 'user1',
                'authpass' => 'pass12345',
                'authalgo' => 'SHA',
                'cryptopass' => 'crypt12345',
                'cryptoalgo' => 'AES',
            ],
        ]);

        $device1 = new Device(['hostname' => 'device1.example.com']);
        $device2 = new Device(['hostname' => 'device2.example.com']);

        $method1 = DevicePollingMethod::transient(
            type: PollingMethodType::Snmp,
            settings: ['port' => 161, 'transport' => 'udp'],
            device: $device1,
            affectsAvailability: true,
            enabled: true,
        );
        $method1->setRelation('secret', $sharedSecret);

        $method2 = DevicePollingMethod::transient(
            type: PollingMethodType::Snmp,
            settings: ['port' => 1161, 'transport' => 'tcp'],
            device: $device2,
            affectsAvailability: false,
            enabled: false,
        );
        $method2->setRelation('secret', $sharedSecret);

        $config1 = SnmpConfig::fromModel($method1);
        $config2 = SnmpConfig::fromModel($method2);

        /** 1. Shared secret between devices */
        $this->assertEquals('shared-community', $config1->community);
        $this->assertEquals('shared-community', $config2->community);

        /** 2. Independent settings */
        $this->assertEquals(161, $config1->port);
        $this->assertEquals(1161, $config2->port);
        $this->assertEquals('udp', $config1->transport);
        $this->assertEquals('tcp', $config2->transport);

        /** 3. Independent enabled & affectsAvailability states */
        $this->assertTrue($config1->isEnabled());
        $this->assertFalse($config2->isEnabled());
        $this->assertTrue($method1->affects_availability);
        $this->assertFalse($method2->affects_availability);

        /** 4. Independent secrets */
        $method2->setRelation('secret', $customSecret);
        $method2->invalidateConfigCache();
        $config2Updated = SnmpConfig::fromModel($method2);

        $this->assertEquals('v2c', $config1->version);
        $this->assertEquals('shared-community', $config1->community);

        $this->assertEquals('v3', $config2Updated->version);
        $this->assertEquals('user1', $config2Updated->authname);
    }
}
