<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use App\Providers\LegacyUserProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Tests\DBTestCase;

final class AuthLocalFallbackTest extends DBTestCase
{
    use DatabaseTransactions;

    private string $originalMechanism;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalMechanism = LibrenmsConfig::get('auth_mechanism');
        LibrenmsConfig::set('auth_mechanism', 'ldap');
        LibrenmsConfig::set('auth_ldap_server', '127.0.0.1');
        LibrenmsConfig::set('auth_ldap_port', 1);
    }

    protected function tearDown(): void
    {
        LibrenmsConfig::set('auth_mechanism', $this->originalMechanism);
        LibrenmsConfig::set('auth_fallback_local', false);
        parent::tearDown();
    }

    public function testLocalUserCanLogInWhenEnabled(): void
    {
        LibrenmsConfig::set('auth_fallback_local', true);
        $user = User::factory()->create(['auth_type' => 'mysql', 'password' => 'secret-pass']);
        $credentials = ['username' => $user->username, 'password' => 'secret-pass'];
        $provider = new LegacyUserProvider();

        $found = $provider->retrieveByCredentials($credentials);
        $this->assertSame($user->user_id, $found?->user_id);
        $this->assertTrue($provider->validateCredentials($found, $credentials));
        $this->assertFalse($provider->validateCredentials($found, ['username' => $user->username, 'password' => 'wrong']));
    }

    public function testLocalUserIsIgnoredWhenDisabled(): void
    {
        LibrenmsConfig::set('auth_fallback_local', false);
        $user = User::factory()->create(['auth_type' => 'mysql', 'password' => 'secret-pass']);
        $provider = new LegacyUserProvider();

        $this->assertNull($provider->retrieveByCredentials(['username' => $user->username, 'password' => 'secret-pass']));
        $this->assertSame('ldap', LegacyAuth::getType());
    }
}
