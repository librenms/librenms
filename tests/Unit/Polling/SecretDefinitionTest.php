<?php

namespace LibreNMS\Tests\Unit\Polling;

use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;
use LibreNMS\Tests\TestCase;

final class SecretDefinitionTest extends TestCase
{
    public function testMaskHidesOnlySensitiveValues(): void
    {
        $masked = (new SnmpSecretDefinition)->mask(['version' => 'v3', 'authname' => 'user', 'authpass' => 'secret', 'cryptopass' => '']);

        $this->assertSame(['version' => 'v3', 'authname' => 'user', 'authpass' => SecretDefinition::MASK, 'cryptopass' => ''], $masked);
    }

    public function testUnmaskRestoresUnchangedValues(): void
    {
        $original = ['version' => 'v3', 'authpass' => 'secret', 'cryptopass' => 'crypt'];
        $submitted = ['version' => 'v3', 'authpass' => SecretDefinition::MASK, 'cryptopass' => 'changed'];

        $this->assertSame(
            ['version' => 'v3', 'authpass' => 'secret', 'cryptopass' => 'changed'],
            (new SnmpSecretDefinition)->unmask($submitted, $original)
        );
    }

    public function testSchemaDefaults(): void
    {
        $this->assertSame(
            ['version' => 'v2c', 'authlevel' => 'noAuthNoPriv', 'authalgo' => 'SHA', 'cryptoalgo' => 'AES'],
            SecretType::Snmp->definition()->schemaDefaults()
        );
    }

    public function testDataFillsDefaults(): void
    {
        $snmp = SnmpSecretData::fromArray(['community' => 'public']);
        $this->assertSame('v2c', $snmp->version);
        $this->assertSame('public', $snmp->community);

        $ipmi = IpmiSecretData::fromArray(['username' => 'admin']);
        $this->assertSame('admin', $ipmi->username);
        $this->assertSame('', $ipmi->password);
    }
}
