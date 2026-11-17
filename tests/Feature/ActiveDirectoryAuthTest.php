<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use App\Ldap\ActiveDirectoryGroups;
use App\Ldap\ActiveDirectoryUser;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\Laravel\Auth\DatabaseUserProvider;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\Group;
use LibreNMS\Tests\DBTestCase;
use Mockery;

class ActiveDirectoryAuthTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Container::flush();
        LibrenmsConfig::set('auth_mechanism', 'active_directory');
        LibrenmsConfig::set('auth_ad_domain', 'example.com');
        LibrenmsConfig::set('auth_ad_url', '');
        LibrenmsConfig::set('auth_ad_base_dn', '');
        LibrenmsConfig::set('auth_ad_user_filter', '(objectclass=user)');
        LibrenmsConfig::set('auth_ad_group_filter', '(objectclass=group)');
        LibrenmsConfig::set('auth_ad_groups', []);
        LibrenmsConfig::set('auth_ad_require_groupmembership', false);
        LibrenmsConfig::set('auth_ad_global_read', false);
        LibrenmsConfig::set('auth_fallback_local', false);
        config([
            'auth.guards.web.provider' => 'active_directory',
            'ldap.default' => 'active_directory',
            'ldap.connections.active_directory' => app(ActiveDirectoryConfig::class)->connection(),
        ]);
        Container::addConnection(new Connection(config('ldap.connections.active_directory')), 'active_directory');
        Container::setDefaultConnection('active_directory');
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        DirectoryEmulator::tearDown();
        Auth::forgetGuards();
        parent::tearDown();
    }

    public function testLoginUsesStandardProviderAndSynchronizesExistingAccount(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $fake->actingAs($ldap);
        $existing = User::factory()->admin()->create(['username' => 'alice', 'auth_type' => 'active_directory', 'auth_id' => '1001', 'enabled' => 1]);

        $this->post('/login', ['username' => 'alice', 'password' => 'secret'])->assertRedirect('/');

        $this->assertInstanceOf(DatabaseUserProvider::class, Auth::getProvider());
        $this->assertAuthenticatedAs($existing);
        $existing->refresh();
        $this->assertSame($ldap->getConvertedGuid(), $existing->auth_id);
        $this->assertSame('active_directory', $existing->auth_type);
        $this->assertSame('Alice Example', $existing->realname);
        $this->assertSame('alice@example.com', $existing->email);
        $this->assertSame(0, $existing->can_modify_passwd);
        $this->assertFalse($existing->hasRole('admin'));
    }

    public function testFailedPasswordDoesNotCreateAnAccount(): void
    {
        $log = Log::spy();
        DirectoryEmulator::setup('active_directory');
        $this->ldapUser();

        $this->post('/login', ['username' => 'alice', 'password' => 'wrong'])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertDatabaseHas('authlog', ['user' => 'alice', 'result' => 'Invalid credentials']);
        $this->assertDatabaseMissing('users', ['auth_type' => 'active_directory', 'username' => 'alice']);
        $log->shouldHaveReceived('warning')->once()->with('Active Directory authentication failed', [
            'username' => 'alice',
            'user_id' => null,
            'reason' => 'Password validation or an access rule failed',
        ]);
    }

    public function testMissingDirectoryUserLogsLookupFailure(): void
    {
        $log = Log::spy();
        DirectoryEmulator::setup('active_directory');

        $this->assertFalse(Auth::once($this->credentials()));

        $log->shouldHaveReceived('warning')->once()->with('Active Directory authentication failed', [
            'username' => 'alice',
            'user_id' => null,
            'reason' => 'No LDAP user found and no matching local fallback account',
        ]);
    }

    public function testGroupRequirementRejectsNonmembers(): void
    {
        $log = Log::spy();
        LibrenmsConfig::set('auth_ad_debug', true);
        LibrenmsConfig::set('auth_ad_require_groupmembership', true);
        LibrenmsConfig::set('auth_ad_groups', ['Admins' => ['roles' => ['admin']]]);
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $fake->actingAs($ldap);

        $this->assertFalse(Auth::attempt($this->credentials()));
        $this->assertDatabaseMissing('users', ['auth_type' => 'active_directory', 'username' => 'alice']);
        $log->shouldHaveReceived('warning')->with('Active Directory access denied', [
            'reason' => 'User is not a member of any configured AD group',
            'dn' => $ldap->getDn(),
            'user_id' => null,
            'configured_groups' => ['Admins'],
        ])->once();
        $log->shouldHaveReceived('debug')->with('Active Directory group membership check', [
            'group' => 'Admins',
            'matching_groups' => 0,
            'is_member' => false,
            'user_dn' => $ldap->getDn(),
        ])->once();
    }

    public function testNestedGroupRolesAndLegacyLevelsAreSynchronized(): void
    {
        LibrenmsConfig::set('auth_ad_require_groupmembership', true);
        LibrenmsConfig::set('auth_ad_groups', ['Admins' => ['roles' => ['admin']], 'Readers' => ['level' => 5]]);
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $fake->actingAs($ldap);
        $admins = Group::create(['cn' => 'Admins']);
        $readers = Group::create(['cn' => 'Readers']);
        $child = Group::create(['cn' => 'Child', 'memberof' => [$admins->getDn(), $readers->getDn()]]);
        $ldap->update(['memberof' => [$child->getDn()]]);
        $child->members()->attach($ldap);
        $admins->members()->attach($child);
        $readers->members()->attach($child);

        $this->assertTrue(Auth::attempt($this->credentials()));
        $this->assertTrue(Auth::user()->hasAllRoles(['admin', 'global-read']));
    }

    public function testMembershipChecksReuseRecursiveGroups(): void
    {
        LibrenmsConfig::set('auth_ad_groups', ['Admins' => [], 'Readers' => []]);
        DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $admins = Group::create(['cn' => 'Admins']);
        $readers = Group::create(['cn' => 'Readers']);
        $admins->members()->attach($ldap);
        $readers->members()->attach($ldap);
        $user = Mockery::mock(ActiveDirectoryUser::class)->makePartial();
        $user->shouldReceive('groups')->once()->andReturn($ldap->groups());

        $this->assertSame(['Admins', 'Readers'], app(ActiveDirectoryGroups::class)->memberships($user));
    }

    public function testMembershipChecksSkipRecursiveGroupsWithoutUniqueMatches(): void
    {
        LibrenmsConfig::set('auth_ad_groups', ['Missing' => [], 'Ambiguous' => []]);
        DirectoryEmulator::setup('active_directory');
        Group::create(['dn' => 'cn=Ambiguous,ou=First,dc=example,dc=com', 'cn' => 'Ambiguous']);
        Group::create(['dn' => 'cn=Ambiguous,ou=Second,dc=example,dc=com', 'cn' => 'Ambiguous']);
        $user = Mockery::mock(ActiveDirectoryUser::class)->makePartial();
        $user->shouldNotReceive('groups');

        $this->assertSame([], app(ActiveDirectoryGroups::class)->memberships($user));
    }

    public function testDisabledDirectoryAccountsCannotLogin(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser(['useraccountcontrol' => 514]);
        $fake->actingAs($ldap);

        $this->assertFalse(Auth::attempt($this->credentials()));
    }

    public function testDisabledLibreNmsAccountsCannotLogin(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser());
        User::factory()->create(['username' => 'alice', 'auth_type' => 'active_directory', 'auth_id' => '1001', 'enabled' => 0]);

        $this->assertFalse(Auth::attempt($this->credentials()));
    }

    public function testLocalFallbackCannotClaimADAccount(): void
    {
        DirectoryEmulator::setup('active_directory');
        LibrenmsConfig::set('auth_fallback_local', true);
        $local = User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->assertTrue(Auth::attempt($this->credentials('password')));
        $this->assertAuthenticatedAs($local);
    }

    public function testExistingLocalAccountIsNotOverwrittenDuringImport(): void
    {
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser());
        $local = User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->assertTrue(Auth::attempt($this->credentials()));
        $this->assertNotSame($local->user_id, Auth::id());
        $this->assertSame('mysql', $local->fresh()->auth_type);
    }

    public function testCustomUserFilterAndEscapingRestrictLookup(): void
    {
        LibrenmsConfig::set('auth_ad_user_filter', '(department=Engineering)');
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser(['department' => 'Operations']));

        $this->assertFalse(Auth::attempt($this->credentials()));
        $this->assertFalse(Auth::attempt(['samaccountname' => '*)(|(objectclass=*))', 'password' => 'secret']));
        $this->assertDatabaseMissing('users', ['auth_type' => 'active_directory', 'username' => 'alice']);
    }

    public function testEscapingRejectsInjectionForUserMatchingCustomFilter(): void
    {
        LibrenmsConfig::set('auth_ad_user_filter', '(department=Engineering)');
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser(['department' => 'Engineering']));

        $this->assertTrue(Auth::once($this->credentials()));
        Auth::forgetGuards();
        $this->assertFalse(Auth::attempt(['samaccountname' => '*)(|(objectclass=*))', 'password' => 'secret']));
        $this->assertGuest();
    }

    public function testCustomGroupFilterRejectsOtherwiseValidMembership(): void
    {
        LibrenmsConfig::set('auth_ad_require_groupmembership', true);
        LibrenmsConfig::set('auth_ad_groups', ['Admins' => ['roles' => ['admin']]]);
        LibrenmsConfig::set('auth_ad_group_filter', '(description=LibreNMS)');
        $fake = DirectoryEmulator::setup('active_directory');
        $ldap = $this->ldapUser();
        $fake->actingAs($ldap);
        $group = Group::create(['cn' => 'Admins', 'description' => 'Other application']);
        $ldap->update(['memberof' => [$group->getDn()]]);
        $group->members()->attach($ldap);

        $this->assertFalse(Auth::attempt($this->credentials()));
        $this->assertDatabaseMissing('users', ['auth_type' => 'active_directory', 'username' => 'alice']);
    }

    public function testDisabledLocalFallbackAccountCannotLogin(): void
    {
        DirectoryEmulator::setup('active_directory');
        LibrenmsConfig::set('auth_fallback_local', true);
        User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 0]);

        $this->assertFalse(Auth::attempt($this->credentials('password')));
    }

    public function testGlobalReadRoleAndPasswordPolicyApplyToNewAccounts(): void
    {
        LibrenmsConfig::set('auth_ad_global_read', true);
        $fake = DirectoryEmulator::setup('active_directory');
        $fake->actingAs($this->ldapUser());

        $this->assertTrue(Auth::attempt($this->credentials()));
        $this->assertTrue(Auth::user()->hasRole('global-read'));
        $this->assertNull(Auth::user()->password);
    }

    /**
     * @return array{username?: string, password?: string, samaccountname?: string, userprincipalname?: string, fallback?: array{username: string, password: string, auth_type: string, enabled: int}}
     */
    private function credentials(string $password = 'secret'): array
    {
        return app(ActiveDirectoryConfig::class)->credentials(['username' => 'alice', 'password' => $password]);
    }

    /**
     * @param  array<string, string|int>  $attributes
     */
    private function ldapUser(array $attributes = []): ActiveDirectoryUser
    {
        return ActiveDirectoryUser::create(array_merge([
            'cn' => 'Alice', 'samaccountname' => 'alice', 'displayname' => 'Alice Example',
            'mail' => 'alice@example.com', 'objectguid' => '1f0f2778-06d8-42d7-bf8b-2956ad7f6634',
            'useraccountcontrol' => 512,
        ], $attributes));
    }
}
