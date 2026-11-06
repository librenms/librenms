<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Tests\TestCase;
use UnexpectedValueException;

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

        $this->assertSame([], $registry->get(PollingMethodType::Icmp)->secretTypes());
        $this->assertSame([SecretType::Ipmi], $registry->get(PollingMethodType::Ipmi)->secretTypes());
        $this->assertSame([], $registry->get(PollingMethodType::UnixAgent)->secretTypes());

        $snmp = $registry->get(PollingMethodType::Snmp);
        $this->assertSame([SecretType::SnmpCommunity, SecretType::SnmpV3], $snmp->secretTypes());
    }

    public function testSecretTypeFrom(): void
    {
        $registry = new PollingMethodRegistry;
        $snmp = $registry->get(PollingMethodType::Snmp);
        $ipmi = $registry->get(PollingMethodType::Ipmi);

        $this->assertSame(SecretType::SnmpV3, $snmp->secretTypeFrom('snmp-v3'));
        $this->assertSame(SecretType::SnmpCommunity, $snmp->secretTypeFrom(SecretType::SnmpCommunity));
        $this->assertNull($snmp->secretTypeFrom(null), 'a type must be chosen from several');
        $this->assertNull($snmp->secretTypeFrom('ipmi'), 'snmp does not use ipmi secrets');
        $this->assertNull($snmp->secretTypeFrom('snmp'), 'the old type is gone');

        $this->assertSame(SecretType::Ipmi, $ipmi->secretTypeFrom(null), 'the only type is chosen');
        $this->assertNull($ipmi->secretTypeFrom('snmp-v3'));
        $this->assertNull($registry->get(PollingMethodType::Icmp)->secretTypeFrom(null));
    }

    public function testConfigRejectsASecretOfAnotherType(): void
    {
        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp]);
        $deviceMethod->setRelation('secret', new Secret(['secret_type' => SecretType::Ipmi, 'data' => ['username' => 'admin']]));

        $this->expectException(UnexpectedValueException::class);
        (new SnmpPollingMethod)->config(new Device, $deviceMethod);
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
