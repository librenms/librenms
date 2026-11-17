<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use App\Providers\ActiveDirectoryServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LdapRecord\Connection;
use LdapRecord\Container;
use LibreNMS\Tests\TestCase;

class ActiveDirectoryServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Container::flush();
        LibrenmsConfig::set('auth_mechanism', 'active_directory');
        LibrenmsConfig::set('auth_ad_domain', 'example.com');
        LibrenmsConfig::set('auth_ad_url', '');
        LibrenmsConfig::set('auth_ad_debug', false);
        config(['ldap.connections.active_directory' => null]);
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        Container::flush();
        Auth::forgetGuards();
        parent::tearDown();
    }

    public function testLegacySettingsRegisterTheDefaultConnection(): void
    {
        (new ActiveDirectoryServiceProvider($this->app))->register();

        $this->assertSame('active_directory', config('auth.guards.web.provider'));
        $this->assertSame('active_directory', config('ldap.default'));
        $this->assertFalse(config('ldap.logging.enabled'));
        $this->assertSame(['example.com'], config('ldap.connections.active_directory.hosts'));
        $this->assertSame(['example.com'], Container::getDefaultConnection()->getConfiguration()->get('hosts'));
    }

    public function testExplicitConnectionIsPreservedDespiteInvalidLegacySettings(): void
    {
        $connection = ['hosts' => ['explicit.example.com'], 'base_dn' => 'dc=explicit,dc=example,dc=com'];
        config(['ldap.connections.active_directory' => $connection]);
        $ldap = new Connection($connection);
        Container::addConnection($ldap, 'active_directory');
        LibrenmsConfig::set('auth_ad_url', 'invalid');
        LibrenmsConfig::set('auth_ad_debug', true);

        (new ActiveDirectoryServiceProvider($this->app))->register();

        $this->assertSame($connection, config('ldap.connections.active_directory'));
        $this->assertSame($ldap, Container::getDefaultConnection());
        $this->assertSame('active_directory', config('auth.guards.web.provider'));
        $this->assertSame('active_directory', config('ldap.default'));
        $this->assertTrue(config('ldap.logging.enabled'));
    }

    public function testInvalidLegacySettingsAllowCommandsButCannotUseAnotherConnection(): void
    {
        $authLog = Log::channel('null');
        $log = Log::spy();
        $log->shouldReceive('channel')->with('auth')->andReturn($authLog);
        Container::addConnection(new Connection(['hosts' => ['other.example.com']]), 'default');
        Container::addConnection(new Connection(['hosts' => ['stale.example.com']]), 'active_directory');
        LibrenmsConfig::set('auth_ad_url', 'ldap://dc1.example.com ldaps://dc2.example.com');

        (new ActiveDirectoryServiceProvider($this->app))->register();

        $this->artisan('list', ['--raw' => true])->assertExitCode(0);
        $this->assertSame('active_directory', config('auth.guards.web.provider'));
        $this->assertSame('active_directory', Container::getDefaultConnectionName());
        $this->assertFalse(Container::hasConnection('active_directory'));
        $this->assertFalse(Auth::once(['samaccountname' => 'alice', 'password' => 'secret']));
        $this->assertGuest();
        $log->shouldHaveReceived('error')->once();
    }
}
