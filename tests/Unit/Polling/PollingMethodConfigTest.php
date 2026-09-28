<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DeviceAttrib;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Tests\TestCase;

final class PollingMethodConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]); // secret data is encrypted
    }

    public function testUnsetSettingsUseTheConfiguredDefaults(): void
    {
        LibrenmsConfig::set('snmp.transports', ['tcp', 'udp']);
        LibrenmsConfig::set('snmp.port', 1161);
        LibrenmsConfig::set('snmp.timeout', 2.5);
        LibrenmsConfig::set('snmp.retries', 3);
        LibrenmsConfig::set('unix-agent.port', 6557);
        LibrenmsConfig::set('unix-agent.connection-timeout', 7);
        $device = new Device(['hostname' => 'example.com']);

        $snmp = $this->config(new SnmpPollingMethod, $this->deviceMethod(PollingMethodType::Snmp, device: $device));
        $this->assertSame('tcp', $snmp->transport);
        $this->assertSame(1161, $snmp->port);
        $this->assertSame(2.5, $snmp->timeout);
        $this->assertSame(3, $snmp->retries);

        $unixAgent = (new UnixAgentPollingMethod)->config($device);
        $this->assertSame(6557, $unixAgent->port);
        $this->assertSame(7, $unixAgent->timeout);

        $this->assertSame('default', (new IcmpPollingMethod)->config($device)->ipVersion);
    }

    public function testStoredSettingsOverrideDefaults(): void
    {
        $method = new SnmpPollingMethod;
        $config = $this->config($method, $this->deviceMethod(PollingMethodType::Snmp, ['transport' => 'tcp', 'max_oid' => 20]));

        $this->assertSame('tcp', $config->transport);
        $this->assertSame(20, $config->maxOid);
        $this->assertSame($method->defaults()['port'], $config->port);

        $this->assertSame('ipv6', $this->config(new IcmpPollingMethod, $this->deviceMethod(PollingMethodType::Icmp, ['ip_version' => 'ipv6']))->ipVersion);
        $this->assertSame(6557, $this->config(new UnixAgentPollingMethod, $this->deviceMethod(PollingMethodType::UnixAgent, ['port' => 6557]))->port);
    }

    public function testSecretIsFilledIntoConfig(): void
    {
        $v2c = $this->deviceMethod(PollingMethodType::Snmp, ['port' => 1161], ['version' => 'v2c', 'community' => 'shared']);
        $v3 = $this->deviceMethod(PollingMethodType::Snmp, [], [
            'version' => 'v3',
            'authlevel' => 'authPriv',
            'authname' => 'user1',
            'authpass' => 'pass12345',
            'cryptopass' => 'crypt12345',
        ]);

        $method = new SnmpPollingMethod;
        $v2cConfig = $this->config($method, $v2c);
        $this->assertSame('v2c', $v2cConfig->version);
        $this->assertSame('shared', $v2cConfig->community);
        $this->assertSame(1161, $v2cConfig->port);

        $v3Config = $this->config($method, $v3);
        $this->assertSame('v3', $v3Config->version);
        $this->assertNull($v3Config->community);
        $this->assertSame('user1', $v3Config->authname);
        $this->assertSame('AES', $v3Config->cryptoalgo); // secret data default
    }

    public function testSnmpDefaultsFollowDeviceOs(): void
    {
        LibrenmsConfig::set('os.test-os.snmp.max_repeaters', 3);
        $device = new Device(['os' => 'test-os']);

        $this->assertSame(3, (new SnmpPollingMethod)->defaults($device)['max_repeaters']);

        // nothing stored, so the OS default is used rather than pinned
        $this->assertSame(3, $this->config(new SnmpPollingMethod, $this->deviceMethod(PollingMethodType::Snmp, device: $device))->maxRepeaters);
    }

    public function testSnmpTransportDefaultFollowsConfig(): void
    {
        LibrenmsConfig::set('snmp.transports.0', 'tcp6');

        $this->assertSame('tcp6', $this->config(new SnmpPollingMethod, $this->deviceMethod(PollingMethodType::Snmp))->transport);
    }

    public function testIpmiHostnameDefaultsToDeviceHostname(): void
    {
        $device = new Device(['hostname' => 'switch.example.com']);
        $method = new IpmiPollingMethod;

        $config = $this->config($method, $this->deviceMethod(PollingMethodType::Ipmi, device: $device));
        $this->assertSame('switch.example.com', $config->hostname);
        $this->assertSame(623, $config->port);
        $this->assertSame(3, $config->timeout);
        $this->assertSame('', $config->type); // detected

        $config = $this->config($method, $this->deviceMethod(PollingMethodType::Ipmi, [
            'hostname' => 'ipmi.example.com',
            'port' => 6230,
            'ciphersuite' => 3,
            'type' => 'lanplus',
        ], device: $device));
        $this->assertSame('ipmi.example.com', $config->hostname);
        $this->assertSame(6230, $config->port);
        $this->assertSame(3, $config->ciphersuite);
        $this->assertSame('lanplus', $config->type);
    }

    public function testDeviceUsesConfiguredMethodOrDefault(): void
    {
        $icmp = $this->deviceMethod(PollingMethodType::Icmp, ['ip_version' => 'ipv6']);
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->setRelation('pollingMethods', new Collection([$icmp]));

        $this->assertTrue($device->polling()->isEnabled(PollingMethodType::Icmp));
        $this->assertSame('ipv6', $device->polling()->icmp()->ipVersion);

        $this->assertFalse($device->polling()->isEnabled(PollingMethodType::Ipmi));
        $this->assertSame('192.0.2.1', $device->polling()->ipmi()->hostname);
        $this->assertSame(6556, $device->polling()->unixAgent()->port);
    }

    public function testSnmpFallsBackToLegacyDeviceFieldsWithoutPollingMethods(): void
    {
        LibrenmsConfig::set('os.test-os.snmp.max_repeaters', 10);

        $device = new Device(['hostname' => '192.0.2.1', 'os' => 'test-os']);
        $device->setAttribute('community', 'legacy-community');
        $device->setAttribute('port', 1161);
        $device->setAttribute('timeout', 0);
        $device->setRelation('pollingMethods', new Collection); // the migration did not reach this device
        $device->setRelation('attribs', new Collection([
            new DeviceAttrib(['attrib_type' => 'snmp_max_repeaters', 'attrib_value' => '0']),
            new DeviceAttrib(['attrib_type' => 'snmp_max_oid', 'attrib_value' => '20']),
        ]));

        $config = $device->polling()->snmp();
        $this->assertSame('legacy-community', $config->community);
        $this->assertSame(1161, $config->port);
        $this->assertSame(20, $config->maxOid);

        // legacy treated these as unset
        $this->assertSame(10, $config->maxRepeaters);
        $this->assertEquals(LibrenmsConfig::get('snmp.timeout'), $config->timeout);
    }

    /**
     * @template T of PollingMethodConfig
     *
     * @param  PollingMethod<T>  $method
     * @return T
     */
    private function config(PollingMethod $method, DevicePollingMethod $deviceMethod): PollingMethodConfig
    {
        return $method->config($deviceMethod->device, $deviceMethod);
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>|null  $secretData
     */
    private function deviceMethod(PollingMethodType $type, array $settings = [], ?array $secretData = null, ?Device $device = null): DevicePollingMethod
    {
        $deviceMethod = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => true,
            'affects_availability' => true,
            'settings' => $settings,
        ]);
        $deviceMethod->setRelation('device', $device ?? new Device(['hostname' => 'example.com']));
        $deviceMethod->setRelation('secret', $secretData === null ? null : new Secret([
            'secret_type' => $type === PollingMethodType::Ipmi ? SecretType::Ipmi : SecretType::Snmp,
            'data' => $secretData,
        ]));

        return $deviceMethod;
    }
}
