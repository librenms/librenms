<?php

namespace LibreNMS\Tests\Unit\Polling;

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
     * Configs fall back to the default settings, so every field needs a default and a label.
     */
    public function testEverySettingHasADefaultAndLabel(): void
    {
        foreach (PollingMethodType::cases() as $type) {
            $method = (new PollingMethodRegistry)->get($type);
            $fields = array_keys($method->definition()->fields());

            $this->assertEqualsCanonicalizing($fields, array_keys($method->defaults()), "{$type->value} default settings do not match its fields");

            foreach ($fields as $key) {
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

        $this->assertTrue($registry->get(PollingMethodType::Icmp)->defaultAffectsAvailability());
        $this->assertFalse($registry->get(PollingMethodType::Ipmi)->defaultAffectsAvailability());
        $this->assertTrue($registry->get(PollingMethodType::Snmp)->defaultAffectsAvailability());
        $this->assertFalse($registry->get(PollingMethodType::UnixAgent)->defaultAffectsAvailability());
    }
}
