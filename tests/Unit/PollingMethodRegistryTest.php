<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\DiscoverDeviceMetadata;
use App\Actions\Device\DiscoverDevicePollingMethods;
use App\Actions\Device\ValidateDeviceUniqueness;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\PollingMethodAccessor;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\Probe\IcmpProbe;
use LibreNMS\Polling\Method\Probe\SnmpProbe;
use LibreNMS\Tests\TestCase;

final class PollingMethodRegistryTest extends TestCase
{
    private PollingMethodRegistry $pollingMethods;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pollingMethods = app(PollingMethodRegistry::class);
    }

    public function testRegistryContainsDefaultPollingMethods(): void
    {
        $this->assertTrue($this->pollingMethods->has(PollingMethodType::Snmp));
        $this->assertTrue($this->pollingMethods->has(PollingMethodType::Icmp));
        $this->assertTrue($this->pollingMethods->has(PollingMethodType::Ipmi));
        $this->assertTrue($this->pollingMethods->has(PollingMethodType::UnixAgent));

        $this->assertInstanceOf(SnmpPollingMethod::class, $this->pollingMethods->get(PollingMethodType::Snmp));
        $this->assertInstanceOf(IcmpPollingMethod::class, $this->pollingMethods->get(PollingMethodType::Icmp));
        $this->assertInstanceOf(IpmiPollingMethod::class, $this->pollingMethods->get(PollingMethodType::Ipmi));
        $this->assertInstanceOf(UnixAgentPollingMethod::class, $this->pollingMethods->get(PollingMethodType::UnixAgent));
    }

    public function testRegistryBehaviorMethodsProvideMethodsConfigsProbesAndSecrets(): void
    {
        // 1. Method instance
        $snmpMethod = $this->pollingMethods->require(PollingMethodType::Snmp);
        $icmpMethodDef = $this->pollingMethods->require(PollingMethodType::Icmp);
        $this->assertInstanceOf(SnmpPollingMethod::class, $snmpMethod);
        $this->assertInstanceOf(IcmpPollingMethod::class, $icmpMethodDef);

        // 2. Method probe and check
        $this->assertInstanceOf(SnmpProbe::class, $this->pollingMethods->get(PollingMethodType::Snmp)?->probe());
        $this->assertInstanceOf(IcmpProbe::class, $this->pollingMethods->get(PollingMethodType::Icmp)?->probe());

        // 3. secretType and hasSecret on PollingMethod instances
        $this->assertSame(\LibreNMS\Enum\SecretType::Snmp, $snmpMethod->secretType());
        $this->assertTrue($snmpMethod->hasSecret());
        $snmpSecretDef = \LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for($snmpMethod->secretType());
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Definitions\SecretDefinition::class, $snmpSecretDef);
        $snmpData = $snmpSecretDef->createData(['community' => 'public']);
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Data\SnmpSecretData::class, $snmpData);
        $this->assertSame('public', $snmpData->community);

        $ipmiMethod = $this->pollingMethods->require(PollingMethodType::Ipmi);
        $this->assertSame(\LibreNMS\Enum\SecretType::Ipmi, $ipmiMethod->secretType());
        $this->assertTrue($ipmiMethod->hasSecret());
        $ipmiSecretDef = \LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for($ipmiMethod->secretType());
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Definitions\SecretDefinition::class, $ipmiSecretDef);
        $ipmiData = $ipmiSecretDef->createData(['username' => 'admin', 'password' => 'pass']);
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Data\IpmiSecretData::class, $ipmiData);
        $this->assertSame('admin', $ipmiData->username);

        $this->assertNull($icmpMethodDef->secretType());
        $this->assertFalse($icmpMethodDef->hasSecret());

        $unixAgentMethod = $this->pollingMethods->require(PollingMethodType::UnixAgent);
        $this->assertNull($unixAgentMethod->secretType());
        $this->assertFalse($unixAgentMethod->hasSecret());

        // SecretDefinition::for resolution
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition::class, \LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for(\LibreNMS\Enum\SecretType::Snmp));
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition::class, \LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for('snmp'));
        $this->assertInstanceOf(\LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition::class, \LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for(\LibreNMS\Enum\SecretType::Ipmi));
        $this->assertNull(\LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for('nonexistent'));
        $this->assertNull(\LibreNMS\Polling\Secrets\Definitions\SecretDefinition::for(null));

        // 4. Method config translation from DevicePollingMethod + Secret
        $snmpDeviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $snmpDeviceMethod->setRelation('secret', new \App\Models\Secret([
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => ['community' => 'public'],
        ]));
        $snmpConfig = $snmpMethod->config($snmpDeviceMethod);
        $this->assertSame('public', $snmpConfig->community);

        $icmpDeviceMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $this->assertInstanceOf(IcmpConfig::class, $this->pollingMethods->get(PollingMethodType::Icmp)?->config($icmpDeviceMethod));

        // 5. Default affects availability
        $this->assertTrue($this->pollingMethods->require(PollingMethodType::Snmp)->defaultAffectsAvailability());
        $this->assertTrue($this->pollingMethods->require(PollingMethodType::Icmp)->defaultAffectsAvailability());
        $this->assertFalse($this->pollingMethods->require(PollingMethodType::Ipmi)->defaultAffectsAvailability());
        $this->assertFalse($this->pollingMethods->require(PollingMethodType::UnixAgent)->defaultAffectsAvailability());
    }

    public function testRegistryAllAndTypesReturnRegisteredMethods(): void
    {
        $types = $this->pollingMethods->types();
        $this->assertContains(PollingMethodType::Snmp, $types);
        $this->assertContains(PollingMethodType::Icmp, $types);
        $this->assertContains(PollingMethodType::Ipmi, $types);
        $this->assertContains(PollingMethodType::UnixAgent, $types);

        $all = $this->pollingMethods->all();
        $this->assertArrayHasKey('snmp', $all);
        $this->assertArrayHasKey('icmp', $all);
        $this->assertArrayHasKey('ipmi', $all);
        $this->assertArrayHasKey('unix-agent', $all);
    }

    public function testDevicePollingConfigReturnsConfigFromMethodOrFallback(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->device_id = 1;

        // Fallback SNMP config when no method is set
        $fallbackSnmp = $device->pollingConfig(PollingMethodType::Snmp);
        $this->assertInstanceOf(SnmpConfig::class, $fallbackSnmp);

        // Fallback ICMP config
        $fallbackIcmp = $device->pollingConfig(PollingMethodType::Icmp);
        $this->assertInstanceOf(IcmpConfig::class, $fallbackIcmp);
        $this->assertFalse($fallbackIcmp->isEnabled());

        // With explicit method attached
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $icmpMethod->setRelation('device', $device);
        $device->setRelation('pollingMethods', collect([$icmpMethod]));

        $configuredIcmp = $device->pollingConfig(PollingMethodType::Icmp);
        $this->assertInstanceOf(IcmpConfig::class, $configuredIcmp);
        $this->assertTrue($configuredIcmp->isEnabled());
    }

    public function testPollingMethodAccessorWithConstructorInjection(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->device_id = 1;

        $accessor = new PollingMethodAccessor($device, $this->pollingMethods);
        $this->assertInstanceOf(SnmpConfig::class, $accessor->get(PollingMethodType::Snmp));
        $this->assertInstanceOf(IcmpConfig::class, $accessor->get(PollingMethodType::Icmp));
        $this->assertInstanceOf(IpmiConfig::class, $accessor->get(PollingMethodType::Ipmi));
        $this->assertInstanceOf(UnixAgentConfig::class, $accessor->get(PollingMethodType::UnixAgent));

        // Test dedicated accessor methods
        $this->assertTrue($accessor->snmp()->isEnabled());
        $this->assertFalse($accessor->icmp()->isEnabled());
        $this->assertFalse($accessor->ipmi()->isEnabled());
        $this->assertFalse($accessor->unixAgent()->isEnabled());

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

        $accessorWithMethods = new PollingMethodAccessor($deviceWithMethods, $this->pollingMethods);
        $this->assertTrue($accessorWithMethods->ipmi()->isEnabled());
        $this->assertTrue($accessorWithMethods->unixAgent()->isEnabled());
    }

    public function testActionClassesAcceptConstructorInjectedRegistry(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $builder = new BuildDefaultPollingMethods($this->pollingMethods);
        $methods = $builder->execute($device);
        $this->assertCount(2, $methods);

        $discoverMethods = new DiscoverDevicePollingMethods($this->pollingMethods);
        $resultMethods = $discoverMethods->execute($device, collect());
        $this->assertCount(0, $resultMethods);

        $discoverMetadata = new DiscoverDeviceMetadata($this->pollingMethods, new ValidateDeviceUniqueness);
        $discoverMetadata->execute($device, collect());
        $this->assertSame('192.0.2.1', $device->hostname);
    }
}
