<?php

namespace LibreNMS\Tests\Unit\Polling;

use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpCommunitySecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpV3SecretDefinition;
use LibreNMS\Tests\TestCase;

final class SecretDefinitionTest extends TestCase
{
    public function testMaskHidesOnlySensitiveValues(): void
    {
        $masked = (new SnmpV3SecretDefinition)->mask(['authname' => 'user', 'authpass' => 'secret', 'cryptopass' => '']);

        $this->assertSame(['authname' => 'user', 'authpass' => SecretDefinition::MASK, 'cryptopass' => ''], $masked);
        $this->assertSame(['community' => SecretDefinition::MASK], (new SnmpCommunitySecretDefinition)->mask(['community' => 'public']));
    }

    public function testUnmaskRestoresUnchangedValues(): void
    {
        $original = ['authname' => 'user', 'authpass' => 'secret', 'cryptopass' => 'crypt'];
        $submitted = ['authname' => 'user', 'authpass' => SecretDefinition::MASK, 'cryptopass' => 'changed'];

        $this->assertSame(
            ['authname' => 'user', 'authpass' => 'secret', 'cryptopass' => 'changed'],
            (new SnmpV3SecretDefinition)->unmask($submitted, $original)
        );
    }

    public function testSchemaDefaults(): void
    {
        $this->assertSame([], SecretType::SnmpCommunity->definition()->schemaDefaults());
        $this->assertSame(
            ['authlevel' => 'noAuthNoPriv', 'authalgo' => 'MD5', 'cryptoalgo' => 'AES'],
            SecretType::SnmpV3->definition()->schemaDefaults()
        );
    }

    public function testSnmpV3SecretDataDefaultsMatchTheForm(): void
    {
        $this->assertEquals(SecretType::SnmpV3->definition()->schemaDefaults(), SnmpV3SecretData::fromArray([])->toArray());
    }

    public function testNoSnmpFieldHasAVersion(): void
    {
        foreach ([SecretType::SnmpCommunity, SecretType::SnmpV3] as $type) {
            $this->assertArrayNotHasKey('version', $type->definition()->fields(), "$type->value secrets must not hold the version, it is a polling method setting");
        }
    }

    public function testDataFillsDefaults(): void
    {
        $community = SecretType::SnmpCommunity->data(['community' => 'public']);
        $this->assertInstanceOf(SnmpCommunitySecretData::class, $community);
        $this->assertSame('public', $community->community);

        $v3 = SecretType::SnmpV3->data(['authname' => 'admin']);
        $this->assertInstanceOf(SnmpV3SecretData::class, $v3);
        $this->assertSame('admin', $v3->authname);
        $this->assertSame('noAuthNoPriv', $v3->authlevel);

        $ipmi = IpmiSecretData::fromArray(['username' => 'admin']);
        $this->assertSame('admin', $ipmi->username);
        $this->assertSame('', $ipmi->password);
    }
}
