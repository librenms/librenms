<?php

namespace LibreNMS\Tests\Unit;

use App\Casts\EncryptedArray;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Tests\TestCase;

final class PollingMethodProbeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    }

    public function testPollingMethodsReturnProbeResult(): void
    {
        /** @var \LibreNMS\Polling\Method\PollingMethodRegistry $registry */
        $registry = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class);
        $device = new Device();

        $this->assertInstanceOf(ProbeResult::class, $registry->require(PollingMethodType::Snmp)->probe($device));
        $this->assertInstanceOf(ProbeResult::class, $registry->require(PollingMethodType::Icmp)->probe($device));
        $this->assertInstanceOf(ProbeResult::class, $registry->require(PollingMethodType::Ipmi)->probe($device));
        $this->assertInstanceOf(ProbeResult::class, $registry->require(PollingMethodType::UnixAgent)->probe($device));
    }

    public function testUnixAgentProbeUsesResolvedConfigPortAndTimeout(): void
    {
        $device = new Device(['hostname' => '127.0.0.1']);
        $unixMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::UnixAgent,
            'settings' => ['port' => 6556, 'timeout' => 5],
            'affects_availability' => true,
            'enabled' => true,
        ]);
        $unixMethod->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$unixMethod]));

        $method = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require(PollingMethodType::UnixAgent);

        $result = $method->probe($device);
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

        $method1 = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => ['port' => 161, 'transport' => 'udp'],
            'affects_availability' => true,
            'enabled' => true,
        ]);
        $method1->setRelation('device', $device1);
        $method1->setRelation('secret', $sharedSecret);

        $method2 = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => ['port' => 1161, 'transport' => 'tcp'],
            'affects_availability' => false,
            'enabled' => false,
        ]);
        $method2->setRelation('device', $device2);
        $method2->setRelation('secret', $sharedSecret);

        $config1 = SnmpConfig::fromPollingMethod($method1);
        $config2 = SnmpConfig::fromPollingMethod($method2);

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
        $config2Updated = SnmpConfig::fromPollingMethod($method2);

        $this->assertEquals('v2c', $config1->version);
        $this->assertEquals('shared-community', $config1->community);

        $this->assertEquals('v3', $config2Updated->version);
        $this->assertEquals('user1', $config2Updated->authname);
    }

    public function testCheckDeviceAvailabilityHandlesSecretDecryptionGracefully(): void
    {
        $device = new Device(['hostname' => 'corrupt-key.example.com', 'status' => true]);
        $device->device_id = 1;

        $badSecret = \Mockery::mock(Secret::class)->makePartial();
        $badSecret->secret_type = \LibreNMS\Enum\SecretType::Snmp;
        $badSecret->shouldReceive('getAttribute')->with('data')->andThrow(
            SecretDecryptionException::failedToDecrypt('The payload is invalid.')
        );

        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => [],
            'affects_availability' => true,
            'enabled' => true,
        ]);
        $method->setRelation('device', $device);
        $method->setRelation('secret', $badSecret);
        $device->setRelation('pollingMethods', collect([$method]));

        $checker = app(\App\Actions\Device\CheckDeviceAvailability::class);

        $status = $checker->execute($device, false);

        $this->assertTrue($status);
        $this->assertNull($method->last_check_successful);
        $this->assertNotEquals('snmp', $device->status_reason);
    }
}
