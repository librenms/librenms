<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use App\Ldap\ActiveDirectoryUser;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Tests\DBTestCase;

class AuthTestCommandTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('auth_mechanism', 'active_directory');
        LibrenmsConfig::set('auth_ad_domain', 'example.com');
        LibrenmsConfig::set('auth_ad_url', '');
        LibrenmsConfig::set('auth_ad_base_dn', '');
        LibrenmsConfig::set('auth_ad_groups', []);
        LibrenmsConfig::set('auth_ad_require_groupmembership', false);
        LibrenmsConfig::set('auth_fallback_local', false);
        config([
            'auth.guards.web.provider' => 'active_directory',
            'ldap.default' => 'active_directory',
            'ldap.connections.active_directory' => app(ActiveDirectoryConfig::class)->connection(),
        ]);
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        DirectoryEmulator::tearDown();
        Auth::forgetGuards();
        parent::tearDown();
    }

    public function testSuccessfulLoginReportsExistingSqlAccount(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $fake->actingAs($ldap);
        $user = User::factory()->create(['username' => 'alice', 'auth_type' => 'active_directory', 'auth_id' => '3651', 'enabled' => 1]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'secret')
            ->expectsOutput('LDAP user found.')
            ->expectsOutput('SQL user found: user_id=' . $user->user_id . ', enabled=1')
            ->expectsOutput('AUTH SUCCESS')
            ->doesntExpectOutputToContain('secret')
            ->assertExitCode(0);

        $this->assertSame($ldap->getConvertedGuid(), $user->fresh()->auth_id);
        $this->assertSame(1, User::where('username', 'alice')->count());
    }

    public function testWrongPasswordReportsBindFailure(): void
    {
        DirectoryEmulator::setup('active_directory');
        $this->ldapUser();

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'wrong')
            ->expectsOutputToContain('LDAP password bind failed.')
            ->expectsOutput('AUTH FAILURE')
            ->doesntExpectOutputToContain('wrong')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'alice', 'auth_type' => 'active_directory']);
    }

    public function testMissingUserReportsLookupFailure(): void
    {
        DirectoryEmulator::setup('active_directory');

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'secret')
            ->expectsOutputToContain('LDAP user was not found.')
            ->expectsOutput('AUTH FAILURE')
            ->assertExitCode(1);
    }

    public function testDisabledSqlAccountReportsAccessRuleFailure(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser());
        User::factory()->create(['username' => 'alice', 'auth_type' => 'active_directory', 'auth_id' => '3651', 'enabled' => 0]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'secret')
            ->expectsOutputToContain('LDAP password bind succeeded, but an access rule rejected the login.')
            ->expectsOutput('AUTH FAILURE')
            ->assertExitCode(1);
    }

    public function testLocalFallbackIsIdentified(): void
    {
        DirectoryEmulator::setup('active_directory');
        LibrenmsConfig::set('auth_fallback_local', true);
        User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'password')
            ->expectsOutput('Authenticated using the local fallback account.')
            ->expectsOutput('AUTH SUCCESS')
            ->assertExitCode(0);
    }

    public function testMysqlAuthenticationStillDisplaysAccountAndRoles(): void
    {
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LegacyAuth::reset();
        $user = User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'password')
            ->expectsOutput('User (' . $user->user_id . '):')
            ->expectsOutputToContain('Roles:')
            ->expectsOutput('AUTH SUCCESS')
            ->doesntExpectOutputToContain('remember_token')
            ->doesntExpectOutputToContain('password =>')
            ->assertExitCode(0);
    }

    public function testEmptyPasswordFailsWithoutAuthenticating(): void
    {
        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', '')
            ->expectsOutput('A password is required.')
            ->assertExitCode(1);
    }

    public function testNoninteractiveExecutionFailsWithoutPrompting(): void
    {
        $this->artisan('auth:test', ['username' => 'alice', '--no-interaction' => true])
            ->expectsOutput('An interactive terminal is required to enter the password.')
            ->assertExitCode(1);
    }

    private function ldapUser(): ActiveDirectoryUser
    {
        return ActiveDirectoryUser::create([
            'cn' => 'Alice', 'samaccountname' => 'alice', 'displayname' => 'Alice Example',
            'mail' => 'alice@example.com', 'objectguid' => '1f0f2778-06d8-42d7-bf8b-2956ad7f6634',
            'useraccountcontrol' => 512,
        ]);
    }
}
