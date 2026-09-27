<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\DiscoverDeviceMetadata;
use App\Actions\Device\DiscoverDevicePollingMethods;
use App\Actions\Device\ValidateDeviceUniqueness;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Str;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\PollingMethodAccessor;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;
use LibreNMS\Tests\TestCase;

final class PollingMethodTypeTest extends TestCase
{
    public function testTypesProvideMethods(): void
    {
        $this->assertInstanceOf(SnmpPollingMethod::class, PollingMethodType::Snmp->method());
        $this->assertInstanceOf(IcmpPollingMethod::class, PollingMethodType::Icmp->method());
        $this->assertInstanceOf(IpmiPollingMethod::class, PollingMethodType::Ipmi->method());
        $this->assertInstanceOf(UnixAgentPollingMethod::class, PollingMethodType::UnixAgent->method());
    }

    /**
     * Settings are filled into the config by name, so every field needs a matching config property.
     */
    public function testDefinitionFieldsMatchConfigProperties(): void
    {
        foreach (PollingMethodType::cases() as $type) {
            $config = $type->method()->defaultConfig();
            foreach (array_keys($type->definition()->fields()) as $key) {
                $this->assertTrue(property_exists($config, Str::camel($key)), "{$type->value} config has no property for setting $key");
            }
        }
    }

    public function testSettingsAreCastAndFilledIntoConfig(): void
    {
        $deviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'settings' => ['transport' => 'tcp', 'port' => '1161', 'timeout' => '2.5', 'max_oid' => '0', 'bulk' => '0', 'context' => ''],
        ]);
        $deviceMethod->setRelation('device', new Device(['hostname' => 'example.com']));
        $deviceMethod->setRelation('secret', null);

        $config = PollingMethodType::Snmp->method()->config($deviceMethod);
        $this->assertInstanceOf(SnmpConfig::class, $config);
        $this->assertSame('tcp', $config->transport);
        $this->assertSame(1161, $config->port);
        $this->assertSame(2.5, $config->timeout);
        $this->assertSame(SnmpConfig::default()->maxOid, $config->maxOid); // out of range, default
        $this->assertFalse($config->bulk);
        $this->assertNull($config->context);
    }

    public function testIpmiDetectedTypeIsASetting(): void
    {
        $settings = PollingMethodType::Ipmi->definition()->filterOverrides(['ciphersuite' => '3', 'type' => 'lanplus', 'unknown' => 'x']);
        $this->assertSame(['ciphersuite' => 3, 'type' => 'lanplus'], $settings);

        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Ipmi, 'settings' => $settings]);
        $deviceMethod->setRelation('device', new Device(['hostname' => 'bmc.example.com']));
        $deviceMethod->setRelation('secret', null);

        $config = PollingMethodType::Ipmi->method()->config($deviceMethod);
        $this->assertInstanceOf(IpmiConfig::class, $config);
        $this->assertSame('lanplus', $config->type);
        $this->assertSame(3, $config->ciphersuite);
        $this->assertSame('bmc.example.com', $config->hostname);
    }

    public function testMethodsProvideConfigsProbesAndSecrets(): void
    {
        $snmpMethod = PollingMethodType::Snmp->method();
        $icmpMethod = PollingMethodType::Icmp->method();

        $this->assertFalse($snmpMethod->probe(new Device(), new SnmpConfig())->isSuccess());
        $this->assertFalse($icmpMethod->probe(new Device(), new IcmpConfig())->isSuccess());

        $this->assertSame(SecretType::Snmp, $snmpMethod->secretType());
        $this->assertTrue($snmpMethod->hasSecret());
        $this->assertInstanceOf(SnmpSecretDefinition::class, SecretType::Snmp->definition());
        $snmpData = SecretType::Snmp->data(['community' => 'public']);
        $this->assertInstanceOf(SnmpSecretData::class, $snmpData);
        $this->assertSame('public', $snmpData->community);

        $ipmiMethod = PollingMethodType::Ipmi->method();
        $this->assertSame(SecretType::Ipmi, $ipmiMethod->secretType());
        $this->assertTrue($ipmiMethod->hasSecret());
        $this->assertInstanceOf(IpmiSecretDefinition::class, SecretType::Ipmi->definition());
        $ipmiData = SecretType::Ipmi->data(['username' => 'admin', 'password' => 'pass']);
        $this->assertInstanceOf(IpmiSecretData::class, $ipmiData);
        $this->assertSame('admin', $ipmiData->username);

        $this->assertNull($icmpMethod->secretType());
        $this->assertFalse($icmpMethod->hasSecret());
        $this->assertNull(PollingMethodType::UnixAgent->method()->secretType());
        $this->assertFalse(PollingMethodType::UnixAgent->method()->hasSecret());

        // Method config from DevicePollingMethod + Secret
        $snmpDeviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $snmpDeviceMethod->setRelation('secret', new Secret([
            'secret_type' => SecretType::Snmp,
            'data' => ['community' => 'public'],
        ]));
        $snmpConfig = $snmpMethod->config($snmpDeviceMethod);
        $this->assertInstanceOf(SnmpConfig::class, $snmpConfig);
        $this->assertSame('public', $snmpConfig->community);

        $icmpDeviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $this->assertInstanceOf(IcmpConfig::class, $icmpMethod->config($icmpDeviceMethod));

        // Default affects availability
        $this->assertTrue(PollingMethodType::Snmp->method()->defaultConfig()->affectsAvailability);
        $this->assertTrue(PollingMethodType::Icmp->method()->defaultConfig()->affectsAvailability);
        $this->assertFalse(PollingMethodType::Ipmi->method()->defaultConfig()->affectsAvailability);
        $this->assertFalse(PollingMethodType::UnixAgent->method()->defaultConfig()->affectsAvailability);
    }

    public function testDevicePollingConfigReturnsConfigFromMethodOrFallback(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->device_id = 1;

        // Fallback SNMP config when no method is set
        $fallbackSnmp = $device->polling()->get(PollingMethodType::Snmp);
        $this->assertInstanceOf(SnmpConfig::class, $fallbackSnmp);

        // Fallback ICMP config
        $fallbackIcmp = $device->polling()->get(PollingMethodType::Icmp);
        $this->assertInstanceOf(IcmpConfig::class, $fallbackIcmp);
        $this->assertFalse($fallbackIcmp->enabled);

        // With explicit method attached
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $icmpMethod->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$icmpMethod]));

        $configuredIcmp = $device->polling()->get(PollingMethodType::Icmp);
        $this->assertInstanceOf(IcmpConfig::class, $configuredIcmp);
        $this->assertTrue($configuredIcmp->enabled);
    }

    public function testPollingMethodAccessorWithConstructorInjection(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->device_id = 1;

        $accessor = new PollingMethodAccessor($device);
        $this->assertInstanceOf(SnmpConfig::class, $accessor->get(PollingMethodType::Snmp));
        $this->assertInstanceOf(IcmpConfig::class, $accessor->get(PollingMethodType::Icmp));
        $this->assertInstanceOf(IpmiConfig::class, $accessor->get(PollingMethodType::Ipmi));
        $this->assertInstanceOf(UnixAgentConfig::class, $accessor->get(PollingMethodType::UnixAgent));

        // Test dedicated accessor methods
        $this->assertTrue($accessor->snmp()->enabled);
        $this->assertFalse($accessor->icmp()->enabled);
        $this->assertFalse($accessor->ipmi()->enabled);
        $this->assertFalse($accessor->unixAgent()->enabled);

        // With methods attached
        $deviceWithMethods = new Device(['hostname' => '192.0.2.1']);
        $deviceWithMethods->device_id = 1;
        $ipmiMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Ipmi,
            'enabled' => true,
            'affects_availability' => false,
        ]);
        $ipmiMethod->setRelation('device', $deviceWithMethods);
        $unixAgentMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::UnixAgent,
            'enabled' => true,
            'affects_availability' => false,
        ]);
        $unixAgentMethod->setRelation('device', $deviceWithMethods);
        $deviceWithMethods->setRelation('pollingMethods', collect([$ipmiMethod, $unixAgentMethod]));

        $accessorWithMethods = new PollingMethodAccessor($deviceWithMethods);
        $this->assertTrue($accessorWithMethods->ipmi()->enabled);
        $this->assertTrue($accessorWithMethods->unixAgent()->enabled);
    }

    public function testActionClasses(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $methods = app(BuildDefaultPollingMethods::class)->execute($device);
        $this->assertCount(2, $methods);

        $resultMethods = (new DiscoverDevicePollingMethods)->execute($device, collect());
        $this->assertCount(0, $resultMethods);

        $discoverMetadata = new DiscoverDeviceMetadata(new ValidateDeviceUniqueness);
        $discoverMetadata->execute($device, collect());
        $this->assertSame('192.0.2.1', $device->hostname);
    }

    public function testIcmpPollingMethodFieldsAndDefaults(): void
    {
        $fields = PollingMethodType::Icmp->definition()->fields();

        $this->assertArrayHasKey('ip_version', $fields);
        $this->assertSame('select', $fields['ip_version']->type);
        $this->assertSame('default', $fields['ip_version']->getDefault());
        $this->assertEquals([
            'default' => 'Auto',
            'match_snmp_transport' => 'Match SNMP Transport',
            'ipv4' => 'IPv4 Only',
            'ipv6' => 'IPv6 Only',
        ], $fields['ip_version']->options);
    }

    public function testIcmpPollingMethodAddressFamilyResolution(): void
    {
        /** @var \LibreNMS\Polling\Method\Methods\IcmpPollingMethod $icmpMethod */
        $icmpMethod = PollingMethodType::Icmp->method();

        $deviceIpv4 = new Device(['hostname' => '192.0.2.1']);
        $snmpMethodIpv4 = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => ['transport' => 'udp'],
        ]);
        $deviceIpv4->setRelation('pollingMethods', collect([$snmpMethodIpv4]));

        $deviceIpv6 = new Device(['hostname' => '2001:db8::1']);
        $snmpMethodIpv6 = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => ['transport' => 'udp6'],
        ]);
        $deviceIpv6->setRelation('pollingMethods', collect([$snmpMethodIpv6]));

        // Default -> null (no flag)
        $defaultConfig = new IcmpConfig(ipVersion: 'default');
        $this->assertNull($icmpMethod->resolveAddressFamily($deviceIpv4, $defaultConfig));
        $this->assertNull($icmpMethod->resolveAddressFamily($deviceIpv6, $defaultConfig));

        // IPv4 only -> AddressFamily::IPv4
        $ipv4Config = new IcmpConfig(ipVersion: 'ipv4');
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv4, $icmpMethod->resolveAddressFamily($deviceIpv4, $ipv4Config));
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv4, $icmpMethod->resolveAddressFamily($deviceIpv6, $ipv4Config));

        // IPv6 only -> AddressFamily::IPv6
        $ipv6Config = new IcmpConfig(ipVersion: 'ipv6');
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($deviceIpv4, $ipv6Config));
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($deviceIpv6, $ipv6Config));

        // Match SNMP Transport -> matches device's SNMP transport family
        $matchSnmpConfig = new IcmpConfig(ipVersion: 'match_snmp_transport');
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv4, $icmpMethod->resolveAddressFamily($deviceIpv4, $matchSnmpConfig));
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($deviceIpv6, $matchSnmpConfig));

        // Test PollingMethod::config()
        $deviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'settings' => ['ip_version' => 'ipv6'],
        ]);
        $fromMethodConfig = $icmpMethod->config($deviceMethod);
        $this->assertInstanceOf(IcmpConfig::class, $fromMethodConfig);
        $this->assertSame('ipv6', $fromMethodConfig->ipVersion);
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($deviceIpv4, $fromMethodConfig));

        // Test Device::polling()
        $deviceIpv4->setRelation('pollingMethods', collect([$deviceMethod]));
        $this->assertSame('ipv6', $deviceIpv4->polling()->icmp()->ipVersion);
        $this->assertSame(\LibreNMS\Enum\AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($deviceIpv4));
    }
}
