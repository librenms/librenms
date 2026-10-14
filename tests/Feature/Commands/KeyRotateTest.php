<?php

namespace LibreNMS\Tests\Feature\Commands;

use App\Facades\LibrenmsConfig;
use Illuminate\Encryption\Encrypter;
use LibreNMS\Tests\InMemoryDbTestCase;

final class KeyRotateTest extends InMemoryDbTestCase
{
    public function testRotatesWithoutInteraction(): void
    {
        $cipher = config('app.cipher');
        $oldKey = Encrypter::generateKey($cipher);
        $newKey = Encrypter::generateKey($cipher);
        config(['app.key' => 'base64:' . base64_encode($newKey)]);

        LibrenmsConfig::persist('validation.encryption.test', (new Encrypter($oldKey, $cipher))->encryptString('valid'));

        $this->artisan('key:rotate', ['old_key' => 'base64:' . base64_encode($oldKey), '--no-interaction' => true])
            ->assertExitCode(0);

        $this->assertSame('valid', (new Encrypter($newKey, $cipher))->decryptString(LibrenmsConfig::get('validation.encryption.test')));
    }
}
