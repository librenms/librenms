<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Secret;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\TestCase;

final class KeyRotateTest extends TestCase
{
    use DatabaseTransactions;

    private ?string $originalKey = null;
    private ?Secret $createdSecret = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalKey = config('app.key');
    }

    protected function tearDown(): void
    {
        $this->createdSecret?->delete();
        \App\Models\Config::where('config_name', 'validation.encryption.test')->delete();

        if ($this->originalKey !== null) {
            config(['app.key' => $this->originalKey]);
            app()->forgetInstance('encrypter');
            app()->forgetInstance(\Illuminate\Contracts\Encryption\Encrypter::class);
        }
        parent::tearDown();
    }

    public function testKeyRotateReEncryptsSecretsAndCanary(): void
    {
        $oldCipher = 'AES-256-CBC';
        $oldKeyRaw = random_bytes(32);
        $oldKey = 'base64:' . base64_encode($oldKeyRaw);

        $newKeyRaw = random_bytes(32);
        $newKey = 'base64:' . base64_encode($newKeyRaw);

        config(['app.cipher' => $oldCipher]);
        config(['app.key' => $oldKey]);
        app()->forgetInstance('encrypter');
        app()->forgetInstance(\Illuminate\Contracts\Encryption\Encrypter::class);

        // Create a test canary in old key
        $oldEncrypter = new Encrypter($oldKeyRaw, $oldCipher);
        \App\Facades\LibrenmsConfig::persist(
            'validation.encryption.test',
            $oldEncrypter->encryptString('valid')
        );

        // Create a Secret model with old key
        $secret = new Secret([
            'description' => 'Test SNMP secret ' . uniqid(),
            'secret_type' => SecretType::Snmp,
            'data' => [
                'version' => 'v2c',
                'community' => 'my-secret-community',
            ],
        ]);
        $secret->save();
        $this->createdSecret = $secret;

        $this->assertSame('my-secret-community', $secret->fresh()->data['community']);

        // Set new key in config and rebind encrypter
        config(['app.key' => $newKey]);
        app()->forgetInstance('encrypter');
        app()->forgetInstance(\Illuminate\Contracts\Encryption\Encrypter::class);

        // Run key:rotate command
        $code = Artisan::call('key:rotate', [
            'old_key' => $oldKey,
            '--no-interaction' => true,
        ]);
        $output = Artisan::output();
        $this->assertSame(0, $code, "key:rotate failed with output:\n$output");

        $reloadedSecret = $secret->fresh();
        $this->assertSame('my-secret-community', $reloadedSecret->data['community']);
    }
}
