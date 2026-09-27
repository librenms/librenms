<?php

namespace LibreNMS\Tests\Unit\Casts;

use App\Casts\EncryptedSecret;
use App\Models\Secret;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Tests\TestCase;

final class EncryptedSecretTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    }

    public function testRoundTrip(): void
    {
        $cast = new EncryptedSecret;
        $stored = $cast->set(new Secret, 'data', ['community' => 'public'], []);

        $this->assertIsString($stored);
        $this->assertStringNotContainsString('public', $stored);
        $this->assertSame(['community' => 'public'], $cast->get(new Secret, 'data', $stored, []));
    }

    public function testEmptyDataIsStoredAsNull(): void
    {
        $cast = new EncryptedSecret;

        $this->assertNull($cast->set(new Secret, 'data', [], []));
        $this->assertSame([], $cast->get(new Secret, 'data', null, []));
    }

    public function testInvalidPayloadThrows(): void
    {
        $this->expectException(SecretDecryptionException::class);

        (new EncryptedSecret)->get(new Secret, 'data', 'invalid-encrypted-payload', []);
    }
}
