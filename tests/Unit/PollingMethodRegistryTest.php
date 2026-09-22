<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\DiscoverDeviceMetadata;
use App\Actions\Device\DiscoverDevicePollingMethods;
use App\Actions\Device\ValidateDeviceUniqueness;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Definitions\IcmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\IpmiPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\UnixAgentPollingMethodDefinition;
use LibreNMS\Polling\Method\PollingMethodAccessor;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\Probe\IcmpProbe;
use LibreNMS\Polling\Method\Probe\SnmpProbe;
use LibreNMS\Tests\TestCase;

final class PollingMethodRegistryTest extends TestCase
{
    private PollingMethodRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = app(PollingMethodRegistry::class);
    }

    public function testRegistryContainsDefaultPollingMethods(): void
    {
        $this->assertTrue($this->registry->has(PollingMethodType::Snmp));
        $this->assertTrue($this->registry->has(PollingMethodType::Icmp));
        $this->assertTrue($this->registry->has(PollingMethodType::Ipmi));
        $this->assertTrue($this->registry->has(PollingMethodType::UnixAgent));

        $this->assertInstanceOf(SnmpPollingMethodDefinition::class, $this->registry->definition(PollingMethodType::Snmp));
        $this->assertInstanceOf(IcmpPollingMethodDefinition::class, $this->registry->definition(PollingMethodType::Icmp));
        $this->assertInstanceOf(IpmiPollingMethodDefinition::class, $this->registry->definition(PollingMethodType::Ipmi));
        $this->assertInstanceOf(UnixAgentPollingMethodDefinition::class, $this->registry->definition(PollingMethodType::UnixAgent));
    }

    public function testRegistryBehaviorMethodsProvideDefinitionsConfigsProbesAndSecrets(): void
    {
        // 1. Definition
        $snmpDef = $this->registry->require(PollingMethodType::Snmp);
        $icmpDef = $this->registry->require(PollingMethodType::Icmp);
        $this->assertInstanceOf(SnmpPollingMethodDefinition::class, $snmpDef);
        $this->assertInstanceOf(IcmpPollingMethodDefinition::class, $icmpDef);

        // 2. Definition probe and check
        $this->assertInstanceOf(SnmpProbe::class, $this->registry->definition(PollingMethodType::Snmp)?->probe());
        $this->assertInstanceOf(IcmpProbe::class, $this->registry->definition(PollingMethodType::Icmp)?->probe());

        // 3. SecretDefinition and hasSecret
        $this->assertTrue($this->registry->hasSecret(PollingMethodType::Snmp));
        $this->assertTrue($this->registry->hasSecret(PollingMethodType::Ipmi));
        $this->assertFalse($this->registry->hasSecret(PollingMethodType::Icmp));
        $this->assertFalse($this->registry->hasSecret(PollingMethodType::UnixAgent));

        $this->assertInstanceOf(HasFieldSchema::class, $this->registry->secretDefinition(PollingMethodType::Snmp));
        $this->assertNull($this->registry->secretDefinition(PollingMethodType::Icmp));

        // 4. Definition config
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $this->assertInstanceOf(IcmpConfig::class, $this->registry->definition(PollingMethodType::Icmp)?->config($icmpMethod));

        // 5. Icon
        $this->assertSame('fa-server', $this->registry->icon(PollingMethodType::Snmp));
        $this->assertSame('fa-exchange', $this->registry->icon(PollingMethodType::Icmp));
        $this->assertSame('fa-microchip', $this->registry->icon(PollingMethodType::Ipmi));
        $this->assertSame('fa-terminal', $this->registry->icon(PollingMethodType::UnixAgent));

        // 6. Default affects availability
        $this->assertTrue($this->registry->defaultAffectsAvailability(PollingMethodType::Snmp));
        $this->assertTrue($this->registry->defaultAffectsAvailability(PollingMethodType::Icmp));
        $this->assertFalse($this->registry->defaultAffectsAvailability(PollingMethodType::Ipmi));
        $this->assertFalse($this->registry->defaultAffectsAvailability(PollingMethodType::UnixAgent));
    }

    public function testRegistryAllAndTypesReturnRegisteredDefinitions(): void
    {
        $types = $this->registry->types();
        $this->assertContains(PollingMethodType::Snmp, $types);
        $this->assertContains(PollingMethodType::Icmp, $types);
        $this->assertContains(PollingMethodType::Ipmi, $types);
        $this->assertContains(PollingMethodType::UnixAgent, $types);

        $all = $this->registry->all();
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

        $accessor = new PollingMethodAccessor($device, $this->registry);
        $this->assertInstanceOf(SnmpConfig::class, $accessor->get(PollingMethodType::Snmp));
        $this->assertInstanceOf(IcmpConfig::class, $accessor->get(PollingMethodType::Icmp));
        $this->assertNull($accessor->get(PollingMethodType::Ipmi));
        $this->assertNull($accessor->get(PollingMethodType::UnixAgent));

        // Test dedicated accessor methods
        $this->assertTrue($accessor->snmp()->isEnabled());
        $this->assertFalse($accessor->icmp()->isEnabled());
        $this->assertNull($accessor->ipmi());
        $this->assertNull($accessor->unixAgent());

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

        $accessorWithMethods = new PollingMethodAccessor($deviceWithMethods, $this->registry);
        $this->assertInstanceOf(IpmiConfig::class, $accessorWithMethods->ipmi());
        $this->assertInstanceOf(UnixAgentConfig::class, $accessorWithMethods->unixAgent());
    }

    public function testActionClassesAcceptConstructorInjectedRegistry(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $builder = new BuildDefaultPollingMethods($this->registry);
        $methods = $builder->execute($device);
        $this->assertCount(2, $methods);

        $discoverMethods = new DiscoverDevicePollingMethods($this->registry);
        $resultMethods = $discoverMethods->execute($device, collect());
        $this->assertCount(0, $resultMethods);

        $discoverMetadata = new DiscoverDeviceMetadata($this->registry, new ValidateDeviceUniqueness);
        $discoverMetadata->execute($device, collect());
        $this->assertSame('192.0.2.1', $device->hostname);
    }
}
