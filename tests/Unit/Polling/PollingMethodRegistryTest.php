<?php

namespace LibreNMS\Tests\Unit\Polling;

use Illuminate\Support\Str;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Tests\TestCase;

final class PollingMethodRegistryTest extends TestCase
{
    public function testEveryTypeHasAMethod(): void
    {
        $registry = new PollingMethodRegistry;

        $this->assertInstanceOf(IcmpPollingMethod::class, $registry->get(PollingMethodType::Icmp));
        $this->assertInstanceOf(IpmiPollingMethod::class, $registry->get(PollingMethodType::Ipmi));
        $this->assertInstanceOf(SnmpPollingMethod::class, $registry->get(PollingMethodType::Snmp));
        $this->assertInstanceOf(UnixAgentPollingMethod::class, $registry->get(PollingMethodType::UnixAgent));
    }

    /**
     * Settings are filled into the config by name, so every field needs a matching config property and a label.
     */
    public function testEverySettingHasAConfigPropertyAndLabel(): void
    {
        foreach (PollingMethodType::cases() as $type) {
            $method = (new PollingMethodRegistry)->get($type);
            $config = $method->defaultConfig();

            foreach (array_keys($method->definition()->fields()) as $key) {
                $this->assertTrue(property_exists($config, Str::camel($key)), "{$type->value} config has no property for setting $key");
                $this->assertTrue(trans()->has("poller.method_settings.{$type->value}.$key"), "{$type->value} setting $key has no label");
            }
        }
    }

    public function testMethodSecretTypes(): void
    {
        $registry = new PollingMethodRegistry;

        $this->assertNull($registry->get(PollingMethodType::Icmp)->secretType());
        $this->assertSame(SecretType::Ipmi, $registry->get(PollingMethodType::Ipmi)->secretType());
        $this->assertSame(SecretType::Snmp, $registry->get(PollingMethodType::Snmp)->secretType());
        $this->assertNull($registry->get(PollingMethodType::UnixAgent)->secretType());
    }

    public function testOnlySnmpAndIcmpAffectAvailabilityByDefault(): void
    {
        $registry = new PollingMethodRegistry;

        $this->assertTrue($registry->get(PollingMethodType::Icmp)->defaultConfig()->affectsAvailability);
        $this->assertFalse($registry->get(PollingMethodType::Ipmi)->defaultConfig()->affectsAvailability);
        $this->assertTrue($registry->get(PollingMethodType::Snmp)->defaultConfig()->affectsAvailability);
        $this->assertFalse($registry->get(PollingMethodType::UnixAgent)->defaultConfig()->affectsAvailability);
    }
}
