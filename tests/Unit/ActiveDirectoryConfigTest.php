<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ActiveDirectoryConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('auth_mechanism', 'active_directory');
        LibrenmsConfig::set('auth_ad_domain', 'example.com');
        LibrenmsConfig::set('auth_ad_url', '');
        LibrenmsConfig::set('auth_ad_base_dn', '');
        LibrenmsConfig::set('auth_ad_binddn', '');
        LibrenmsConfig::set('auth_ad_binduser', '');
        LibrenmsConfig::set('auth_ad_bindpassword', '');
        LibrenmsConfig::set('auth_ad_starttls', 'disabled');
        LibrenmsConfig::set('auth_fallback_local', false);
    }

    public function testDomainSuppliesDefaultHostAndBaseDn(): void
    {
        $config = app(ActiveDirectoryConfig::class)->connection();

        $this->assertSame(['example.com'], $config['hosts']);
        $this->assertSame('dc=example,dc=com', $config['base_dn']);
        $this->assertFalse($config['use_tls']);
    }

    public function testLegacyConnectionSettingsAndBindDnAreMapped(): void
    {
        LibrenmsConfig::set('auth_ad_url', 'ldaps://dc1.example.com:1636 ldaps://dc2.example.com:1636');
        LibrenmsConfig::set('auth_ad_base_dn', 'ou=Staff,dc=example,dc=com');
        LibrenmsConfig::set('auth_ad_binduser', 'ignored');
        LibrenmsConfig::set('auth_ad_binddn', 'cn=Service,dc=example,dc=com');
        LibrenmsConfig::set('auth_ad_bindpassword', 'secret');
        LibrenmsConfig::set('auth_ad_timeout', 9);
        LibrenmsConfig::set('auth_ad_check_certificates', true);

        $config = app(ActiveDirectoryConfig::class)->connection();

        $this->assertSame(['dc1.example.com', 'dc2.example.com'], $config['hosts']);
        $this->assertSame(1636, $config['port']);
        $this->assertTrue($config['use_tls']);
        $this->assertFalse($config['use_starttls']);
        $this->assertSame('ou=Staff,dc=example,dc=com', $config['base_dn']);
        $this->assertSame('cn=Service,dc=example,dc=com', $config['username']);
        $this->assertSame('secret', $config['password']);
        $this->assertSame(9, $config['timeout']);
        $this->assertSame(LDAP_OPT_X_TLS_DEMAND, $config['options'][LDAP_OPT_X_TLS_REQUIRE_CERT]);
    }

    #[DataProvider('bindNames')]
    public function testBindNamesAreQualifiedOnlyWhenNecessary(string $name, string $expected): void
    {
        LibrenmsConfig::set('auth_ad_binduser', $name);

        $this->assertSame($expected, app(ActiveDirectoryConfig::class)->connection()['username']);
    }

    public static function bindNames(): array
    {
        return [
            ['service', 'service@example.com'],
            ['service@other.example', 'service@other.example'],
            ['EXAMPLE\\service', 'EXAMPLE\\service'],
            ['cn=Service,dc=example,dc=com', 'cn=Service,dc=example,dc=com'],
            ['', ''],
        ];
    }

    public function testBothStartTlsModesRequireEncryption(): void
    {
        foreach (['optional', 'required'] as $mode) {
            LibrenmsConfig::set('auth_ad_starttls', $mode);
            $this->assertTrue(app(ActiveDirectoryConfig::class)->connection()['use_starttls']);
        }
    }

    public function testMixedServerProtocolsFailWithActionableError(): void
    {
        LibrenmsConfig::set('auth_ad_url', 'ldap://dc1.example.com ldaps://dc2.example.com');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('same protocol and port');
        app(ActiveDirectoryConfig::class)->connection();
    }

    public function testCredentialsMapLoginNamesAndConstrainLocalFallback(): void
    {
        LibrenmsConfig::set('auth_fallback_local', true);
        $config = app(ActiveDirectoryConfig::class);

        $this->assertSame([
            'samaccountname' => 'alice', 'password' => 'secret',
            'fallback' => ['username' => 'EXAMPLE\\alice', 'password' => 'secret', 'auth_type' => 'mysql', 'enabled' => 1],
        ], $config->credentials(['username' => 'EXAMPLE\\alice', 'password' => 'secret']));
        LibrenmsConfig::set('auth_fallback_local', false);
        $this->assertSame(['userprincipalname' => 'alice@example.com', 'password' => 'secret'], $config->credentials(['username' => 'alice@example.com', 'password' => 'secret']));
    }

    public function testOtherAuthMechanismsKeepTheirCredentials(): void
    {
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        $credentials = ['username' => 'alice', 'password' => 'secret'];

        $this->assertSame($credentials, app(ActiveDirectoryConfig::class)->credentials($credentials));
    }
}
