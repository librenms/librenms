<?php

namespace LibreNMS\Tests\Unit\Casts;

use App\Casts\EncryptedSecret;
use App\Models\Secret;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Tests\TestCase;
use Mockery;

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

    public function testEmptyDataIsStoredEncrypted(): void
    {
        $cast = new EncryptedSecret;

        foreach ([[], null] as $value) {
            $stored = $cast->set(new Secret, 'data', $value, []);
            $this->assertIsString($stored);
            $this->assertSame([], $cast->get(new Secret, 'data', $stored, []));
        }

        $this->assertSame([], $cast->get(new Secret, 'data', null, []));
    }

    public function testDecryptsOnceForEachModelUntilTheValueChanges(): void
    {
        $secret = new Secret(['data' => ['community' => 'public']]);
        $decrypts = $this->countDecrypts();

        $this->assertSame(['community' => 'public'], $secret->data);
        $this->assertSame(['community' => 'public'], $secret->data);
        $this->assertSame(1, $decrypts->count);

        // another model with the same payload decrypts its own
        $other = (new Secret)->setRawAttributes(['data' => $secret->getAttributes()['data']]);
        $this->assertSame(['community' => 'public'], $other->data);
        $this->assertSame(2, $decrypts->count);

        $secret->data = ['community' => 'private'];
        $this->assertSame(['community' => 'private'], $secret->data);
        $this->assertSame(['community' => 'private'], $secret->data);
        $this->assertSame(3, $decrypts->count);
    }

    public function testInvalidPayloadThrows(): void
    {
        $this->expectException(SecretDecryptionException::class);

        (new EncryptedSecret)->get(new Secret, 'data', 'invalid-encrypted-payload', []);
    }

    private function countDecrypts(): object
    {
        $counter = new class
        {
            public int $count = 0;
        };

        $encrypter = app('encrypter');
        $spy = Mockery::mock($encrypter);
        $spy->shouldReceive('decrypt')->andReturnUsing(function (...$args) use ($encrypter, $counter) {
            $counter->count++;

            return $encrypter->decrypt(...$args);
        });
        $this->app->instance('encrypter', $spy);

        return $counter;
    }
}
