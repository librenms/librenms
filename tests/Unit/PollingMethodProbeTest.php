<?php

namespace LibreNMS\Tests\Unit;

use App\Casts\EncryptedSecret;
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
        $cast = new EncryptedSecret();
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

        $this->assertFalse($status);
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);
        $this->assertFalse($method->last_check_successful);
        $this->assertNotNull($method->last_checked_at);
    }

    public function testCheckDeviceAvailabilityWithCorruptedSecretAllowsOtherMethodsToProbe(): void
    {
        $device = new Device(['hostname' => 'dual-method.example.com', 'status' => true]);
        $device->device_id = 1;

        $badSecret = \Mockery::mock(Secret::class)->makePartial();
        $badSecret->secret_type = \LibreNMS\Enum\SecretType::Snmp;
        $badSecret->shouldReceive('getAttribute')->with('data')->andThrow(
            SecretDecryptionException::failedToDecrypt('Bad key')
        );

        $snmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => [],
            'affects_availability' => false,
            'enabled' => true,
        ]);
        $snmpMethod->setRelation('device', $device);
        $snmpMethod->setRelation('secret', $badSecret);

        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'settings' => [],
            'affects_availability' => true,
            'enabled' => true,
        ]);
        $icmpMethod->setRelation('device', $device);

        $mockFping = \Mockery::mock(\LibreNMS\Data\Source\Icmp\Fping::class);
        $mockFping->shouldReceive('ping')->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp('127.0.0.1'));
        $this->app->instance(\LibreNMS\Data\Source\Icmp\Fping::class, $mockFping);

        $device->setRelation('pollingMethods', collect([$snmpMethod, $icmpMethod]));

        $checker = app(\App\Actions\Device\CheckDeviceAvailability::class);
        $status = $checker->execute($device, false);

        // SNMP failed due to decryption exception, but didn't abort ICMP check
        $this->assertFalse($snmpMethod->last_check_successful);
        $this->assertTrue($icmpMethod->last_check_successful);
        // Since only ICMP affects availability and succeeded, device status is up
        $this->assertTrue($status);
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
    }

    public function testNullLastCheckSuccessfulIsUnknownNotFailed(): void
    {
        $device = new Device(['hostname' => 'unprobed.example.com', 'status' => true]);
        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'last_check_successful' => null,
        ]);
        $method->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$method]));

        $helper = new \LibreNMS\Polling\ConnectivityHelper($device);
        $this->assertTrue($helper->isAvailable());

        $setAvailability = app(\App\Actions\Device\SetDeviceAvailability::class);
        $this->assertTrue($setAvailability->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
    }

    public function testIpmiConfigReadsFromPollingMethodSettings(): void
    {
        $device = new Device(['hostname' => 'ipmi.example.com']);

        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Ipmi,
            'settings' => ['type' => 'lanplus'],
            'enabled' => true,
        ]);
        $method->setRelation('device', $device);

        $config = \LibreNMS\Polling\Method\Config\IpmiConfig::fromPollingMethod($method);
        $this->assertEquals('lanplus', $config->type);
    }

    public function testUnixAgentProbeOnDeadPortReturnsFailureResult(): void
    {
        // 192.0.2.1 (TEST-NET-1) or 127.0.0.1 on an unused port with 1s timeout
        $device = new Device(['hostname' => '127.0.0.1']);
        $unixMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::UnixAgent,
            'settings' => ['port' => 1, 'timeout' => 1],
            'affects_availability' => true,
            'enabled' => true,
        ]);
        $unixMethod->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$unixMethod]));

        $method = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require(PollingMethodType::UnixAgent);

        $result = $method->probe($device);
        $this->assertFalse($result->isSuccess());
        $this->assertEquals(1, $result->stat('port'));
        $this->assertEquals(1, $result->stat('timeout'));
    }

    public function testSnmpConfigTransportDefaultMatchesConfigTransports(): void
    {
        \App\Facades\LibrenmsConfig::set('snmp.transports.0', 'tcp6');

        $device = new Device(['hostname' => 'snmp.example.com']);
        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => [],
            'enabled' => true,
        ]);
        $method->setRelation('device', $device);

        $config = SnmpConfig::fromPollingMethod($method);
        $this->assertEquals('tcp6', $config->transport);

        \App\Facades\LibrenmsConfig::set('snmp.transports.0', 'udp');
    }

    public function testFilterOverridesOmitsMatchingDefaultsAndRetainsOverrides(): void
    {
        $method = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require(PollingMethodType::Snmp);

        $input = [
            'transport' => 'udp',
            'port' => 1161,
            'timeout' => 1,
            'retries' => 3,
        ];

        $overrides = $method->filterOverrides($input);
        $this->assertArrayNotHasKey('transport', $overrides);
        $this->assertArrayNotHasKey('timeout', $overrides);
        $this->assertEquals(1161, $overrides['port']);
        $this->assertEquals(3, $overrides['retries']);
    }

    public function testIcmpPollingMethodProbePassesConfiguredAddressFamily(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $snmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => ['transport' => 'udp6'],
        ]);

        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'settings' => ['ip_version' => 'default'],
        ]);
        $icmpMethod->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$snmpMethod, $icmpMethod]));

        $mockFping = \Mockery::mock(\LibreNMS\Data\Source\Icmp\Fping::class);
        $this->app->instance(\LibreNMS\Data\Source\Icmp\Fping::class, $mockFping);

        $icmpPollingMethod = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require(PollingMethodType::Icmp);

        // 1. ip_version = 'default' -> passes null
        $mockFping->shouldReceive('ping')->with('192.0.2.1', null)->once()->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp('192.0.2.1'));
        $result = $icmpPollingMethod->probe($device);
        $this->assertTrue($result->isSuccess());

        // 2. ip_version = 'match_snmp_transport' -> passes AddressFamily::IPv6 (since transport is udp6)
        $icmpMethod->settings = ['ip_version' => 'match_snmp_transport'];
        $mockFping->shouldReceive('ping')->with('192.0.2.1', \LibreNMS\Enum\AddressFamily::IPv6)->once()->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp('192.0.2.1'));
        $result = $icmpPollingMethod->probe($device);
        $this->assertTrue($result->isSuccess());

        // 3. ip_version = 'ipv4' -> passes AddressFamily::IPv4
        $icmpMethod->settings = ['ip_version' => 'ipv4'];
        $mockFping->shouldReceive('ping')->with('192.0.2.1', \LibreNMS\Enum\AddressFamily::IPv4)->once()->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp('192.0.2.1'));
        $result = $icmpPollingMethod->probe($device);
        $this->assertTrue($result->isSuccess());

        // 4. ip_version = 'ipv6' -> passes AddressFamily::IPv6
        $icmpMethod->settings = ['ip_version' => 'ipv6'];
        $mockFping->shouldReceive('ping')->with('192.0.2.1', \LibreNMS\Enum\AddressFamily::IPv6)->once()->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp('192.0.2.1'));
        $result = $icmpPollingMethod->probe($device);
        $this->assertTrue($result->isSuccess());
    }

    public function testIcmpPollingMethodDiscoverPreservesSnmpTransportAndDetectsReachabilityFailure(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $snmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => ['transport' => 'udp6'],
        ]);
        $device->setRelation('pollingMethods', collect([$snmpMethod]));

        $candidateIcmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'settings' => ['ip_version' => 'match_snmp_transport'],
        ]);

        $mockFping = \Mockery::mock(\LibreNMS\Data\Source\Icmp\Fping::class);
        $this->app->instance(\LibreNMS\Data\Source\Icmp\Fping::class, $mockFping);

        $icmpPollingMethod = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require(PollingMethodType::Icmp);

        // When discover is called on candidate ICMP method, it should preserve the SNMP method, see udp6, and ping IPv6
        $mockFping->shouldReceive('ping')
            ->with('192.0.2.1', \LibreNMS\Enum\AddressFamily::IPv6)
            ->once()
            ->andReturn(\LibreNMS\Data\Source\Icmp\FpingResponse::createError(\LibreNMS\Enum\FpingExitCode::Unreachable, '192.0.2.1'));

        $result = $icmpPollingMethod->discover($device, $candidateIcmpMethod);
        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->errorMessage());
    }

    public function testSnmpPollingMethodProbeUsesBackendDirectlyWithExplicitConfig(): void
    {
        $device = new Device(['hostname' => 'snmp.test.local']);
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'custom-community',
            transport: 'udp',
            port: 1161,
        );

        $mockBackend = \Mockery::mock(\LibreNMS\Data\Source\Snmp\SnmpBackendInterface::class);
        $mockBackend->shouldReceive('get')
            ->once()
            ->with(
                'snmp.test.local',
                ['SNMPv2-MIB::sysObjectID.0'],
                $config,
                \Mockery::type(\LibreNMS\Data\Source\Snmp\SnmpQueryOptions::class)
            )
            ->andReturn(new \LibreNMS\Data\Source\Snmp\RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.9.1.1', 0));

        $method = new \LibreNMS\Polling\Method\Methods\SnmpPollingMethod($mockBackend);
        $result = $method->probe($device, $config);

        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->errorMessage());
    }
}
