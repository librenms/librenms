<?php

namespace LibreNMS\Tests\Unit\Casts;

use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\TestCase;

final class StoredTypeEnumTest extends TestCase
{
    public function testKnownTypesAreCast(): void
    {
        $secret = new Secret(['secret_type' => SecretType::SnmpV3]);
        $this->assertSame(SecretType::SnmpV3, $secret->secret_type);
        $this->assertSame('snmp-v3', $secret->typeName());
        $this->assertSame(SecretType::SnmpV3->label(), $secret->typeLabel());

        $method = new DevicePollingMethod(['method_type' => 'icmp']);
        $this->assertSame(PollingMethodType::Icmp, $method->method_type);
        $this->assertSame('icmp', $method->toArray()['method_type']);
    }

    public function testUnknownTypesKeepTheStoredValue(): void
    {
        $secret = new Secret(['secret_type' => 'future-secret']);
        $this->assertSame(SecretType::Unknown, $secret->secret_type);
        $this->assertSame('future-secret', $secret->typeName());
        $this->assertSame('Unknown (future-secret)', $secret->typeLabel());

        // assigning the cast value back does not overwrite the stored type
        $secret->secret_type = $secret->secret_type;
        $this->assertSame('future-secret', $secret->typeName());

        $method = new DevicePollingMethod(['method_type' => 'future-method']);
        $method->syncOriginal();
        $this->assertSame(PollingMethodType::Unknown, $method->method_type);
        $this->assertSame('future-method', $method->toArray()['method_type']);
        $method->method_type = PollingMethodType::Unknown;
        $this->assertFalse($method->isDirty('method_type'));
    }

    public function testUnknownIsNotValidInput(): void
    {
        $this->assertNull(SecretType::tryFromKnown('unknown'));
        $this->assertNull(PollingMethodType::tryFromKnown('unknown'));
        $this->assertSame(PollingMethodType::Snmp, PollingMethodType::tryFromKnown('snmp'));
        $this->assertNotContains(SecretType::Unknown, SecretType::known());
        $this->assertNotContains(PollingMethodType::Unknown, PollingMethodType::known());
    }
}
