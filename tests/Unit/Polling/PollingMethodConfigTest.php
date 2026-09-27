<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Tests\TestCase;

final class PollingMethodConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]); // secret data is encrypted
    }

    public function testDefaults(): void
    {
        $snmp = SnmpConfig::default();
        $this->assertSame('udp', $snmp->transport);
        $this->assertSame(161, $snmp->port);
        $this->assertSame(1.0, (float) $snmp->timeout);
        $this->assertSame(5, $snmp->retries);

        $unixAgent = UnixAgentConfig::default();
        $this->assertSame(6556, $unixAgent->port);
        $this->assertSame(10, $unixAgent->timeout);

        $this->assertSame('default', IcmpConfig::default()->ipVersion);
    }

    public function testFillSetsPropertiesBySettingKey(): void
    {
        $snmp = SnmpConfig::default()->fill(['transport' => 'tcp', 'max_oid' => 20, 'unknown' => 'x']);
        $this->assertSame('tcp', $snmp->transport);
        $this->assertSame(20, $snmp->maxOid);

        $this->assertSame('ipv6', IcmpConfig::default()->fill(['ip_version' => 'ipv6'])->ipVersion);
        $this->assertSame(6557, UnixAgentConfig::default()->fill(['port' => 6557])->port);
        $this->assertSame(20, $snmp->setting('max_oid'));
    }

    public function testStoredSettingsAreCastAndInvalidValuesUseDefaults(): void
    {
        $config = (new SnmpPollingMethod)->config($this->deviceMethod(PollingMethodType::Snmp, [
            'transport' => 'tcp',
            'port' => '1161',
            'timeout' => '2.5',
            'max_oid' => '0', // out of range
            'bulk' => '0',
            'context' => '',
        ]));

        $this->assertSame('tcp', $config->transport);
        $this->assertSame(1161, $config->port);
        $this->assertSame(2.5, $config->timeout);
        $this->assertSame(SnmpConfig::default()->maxOid, $config->maxOid);
        $this->assertFalse($config->bulk);
        $this->assertNull($config->context);
    }

    public function testMethodStateComesFromTheDeviceMethod(): void
    {
        $deviceMethod = $this->deviceMethod(PollingMethodType::Snmp);
        $deviceMethod->enabled = false;
        $deviceMethod->affects_availability = false;

        $config = (new SnmpPollingMethod)->config($deviceMethod);
        $this->assertFalse($config->enabled);
        $this->assertFalse($config->affectsAvailability);
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
        $v2cConfig = $method->config($v2c);
        $this->assertSame('v2c', $v2cConfig->version);
        $this->assertSame('shared', $v2cConfig->community);
        $this->assertSame(1161, $v2cConfig->port);

        $v3Config = $method->config($v3);
        $this->assertSame('v3', $v3Config->version);
        $this->assertNull($v3Config->community);
        $this->assertSame('user1', $v3Config->authname);
        $this->assertSame('AES', $v3Config->cryptoalgo); // secret data default
    }

    public function testSnmpDefaultsFollowDeviceOs(): void
    {
        LibrenmsConfig::set('os.test-os.snmp.max_repeaters', 3);
        $device = new Device(['os' => 'test-os']);

        $this->assertSame(3, (new SnmpPollingMethod)->defaultConfig($device)->maxRepeaters);

        // nothing stored, so the OS default is used rather than pinned
        $this->assertSame(3, (new SnmpPollingMethod)->config($this->deviceMethod(PollingMethodType::Snmp, device: $device))->maxRepeaters);
    }

    public function testSnmpTransportDefaultFollowsConfig(): void
    {
        LibrenmsConfig::set('snmp.transports.0', 'tcp6');

        $this->assertSame('tcp6', (new SnmpPollingMethod)->config($this->deviceMethod(PollingMethodType::Snmp))->transport);
    }

    public function testIpmiHostnameDefaultsToDeviceHostname(): void
    {
        $device = new Device(['hostname' => 'switch.example.com']);
        $method = new IpmiPollingMethod;

        $config = $method->config($this->deviceMethod(PollingMethodType::Ipmi, device: $device));
        $this->assertSame('switch.example.com', $config->hostname);
        $this->assertSame(623, $config->port);
        $this->assertSame(3, $config->timeout);
        $this->assertSame('', $config->type); // detected

        $config = $method->config($this->deviceMethod(PollingMethodType::Ipmi, [
            'hostname' => 'ipmi.example.com',
            'port' => 6230,
            'ciphersuite' => '3',
            'type' => 'lanplus',
        ], device: $device));
        $this->assertSame('ipmi.example.com', $config->hostname);
        $this->assertSame(6230, $config->port);
        $this->assertSame(3, $config->ciphersuite);
        $this->assertSame('lanplus', $config->type);
    }

    public function testDeviceUsesConfiguredMethodOrDisabledDefault(): void
    {
        $icmp = $this->deviceMethod(PollingMethodType::Icmp, ['ip_version' => 'ipv6']);
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->setRelation('pollingMethods', new Collection([$icmp]));

        $this->assertTrue($device->polling()->icmp()->enabled);
        $this->assertSame('ipv6', $device->polling()->icmp()->ipVersion);

        $this->assertFalse($device->polling()->ipmi()->enabled);
        $this->assertFalse($device->polling()->unixAgent()->enabled);
        $this->assertFalse($device->polling()->snmp()->enabled);
    }

    public function testSnmpFallsBackToLegacyDeviceFieldsWithoutPollingMethods(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->setAttribute('community', 'legacy-community');
        $device->setAttribute('port', 1161);
        $device->setRelation('attribs', new Collection);

        $config = $device->polling()->snmp();
        $this->assertTrue($config->enabled);
        $this->assertSame('legacy-community', $config->community);
        $this->assertSame(1161, $config->port);
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
