<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use App\Providers\LegacyUserProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Authentication\AuthorizerBase;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AuthLocalFallbackTest extends DBTestCase
{
    use DatabaseTransactions;

    private string $originalMechanism;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalMechanism = LibrenmsConfig::get('auth_mechanism');
        LibrenmsConfig::set('auth_mechanism', 'ldap');
        LibrenmsConfig::set('auth_fallback_local', true);
    }

    protected function tearDown(): void
    {
        LibrenmsConfig::set('auth_mechanism', $this->originalMechanism);
        LibrenmsConfig::set('auth_fallback_local', false);
        LegacyAuth::reset();
        parent::tearDown();
    }

    public function testLocalUserLogsInWhenTheConfiguredAuthDoesNotKnowThem(): void
    {
        $this->useAuthorizer(['bob' => 'dir-pass']);
        $local = $this->localUser('admin', 'local-pass');
        $provider = new LegacyUserProvider();

        $found = $provider->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']);
        $this->assertSame($local->getAuthIdentifier(), $found?->getAuthIdentifier());
        $this->assertTrue($provider->validateCredentials($found, ['username' => 'admin', 'password' => 'local-pass']));
        $this->assertFalse($provider->validateCredentials($found, ['username' => 'admin', 'password' => 'wrong']));

        $local->enabled = false;
        $local->save();
        $this->assertFalse($provider->validateCredentials($found, ['username' => 'admin', 'password' => 'local-pass']));

        LibrenmsConfig::set('auth_fallback_local', false);
        $this->assertNull($provider->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']));
    }

    /**
     * @param  'lookup'|'authenticate'  $failing
     */
    #[DataProvider('outages')]
    public function testLocalUserLogsInWhenTheConfiguredAuthIsDown(string $failing): void
    {
        $this->useAuthorizer(['bob' => 'dir-pass'], failing: $failing);
        $local = $this->localUser('admin', 'local-pass');
        $provider = new LegacyUserProvider();

        $found = $provider->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']);
        $this->assertSame($local->getAuthIdentifier(), $found?->getAuthIdentifier());

        LibrenmsConfig::set('auth_fallback_local', false);
        $this->expectException(\ErrorException::class);
        $provider->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function outages(): array
    {
        return [
            'user lookup fails' => ['lookup'],
            // like ldap: the lookup hides the error, then authenticate() hits the server
            'authenticate fails' => ['authenticate'],
        ];
    }

    public function testExternalAuthDoesNotFallBackToLocalUsers(): void
    {
        LibrenmsConfig::set('auth_mechanism', 'http-auth');
        $this->useAuthorizer(['bob' => 'dir-pass'], external: true);
        $this->localUser('admin', 'local-pass');

        $this->assertNull(new LegacyUserProvider()->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']));
    }

    public function testKnownUserIsNotCheckedAgainstTheLocalPassword(): void
    {
        $this->useAuthorizer(['bob' => 'dir-pass']);
        $this->localUser('bob', 'local-pass');
        $provider = new LegacyUserProvider();

        $found = $provider->retrieveByCredentials(['username' => 'bob', 'password' => 'local-pass']);
        $this->assertInstanceOf(User::class, $found);
        $this->assertSame('ldap', $found->auth_type);
        $this->assertFalse($provider->validateCredentials($found, ['username' => 'bob', 'password' => 'local-pass']));
    }

    public function testUserCreatedOnLoginIsPreferredOverTheLocalUser(): void
    {
        // like radius: the user is only known after a successful authenticate()
        LibrenmsConfig::set('auth_mechanism', 'radius');
        $this->useAuthorizer(['bob' => 'radius-pass'], knownAfterLogin: true);
        $this->localUser('bob', 'local-pass');

        $found = new LegacyUserProvider()->retrieveByCredentials(['username' => 'bob', 'password' => 'radius-pass']);
        $this->assertInstanceOf(User::class, $found);
        $this->assertSame('radius', $found->auth_type);
    }

    private function localUser(string $username, string $password): User
    {
        $user = User::factory()->create(['username' => $username, 'auth_type' => 'mysql']);
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    /**
     * @param  array<string, string>  $users  username => password
     * @param  'lookup'|'authenticate'|null  $failing  which call errors like an unreachable server
     */
    private function useAuthorizer(array $users, ?string $failing = null, bool $knownAfterLogin = false, bool $external = false): void
    {
        $authorizer = new class($users, $failing, $knownAfterLogin, $external) extends AuthorizerBase
        {
            /** @var array<string, bool> */
            private array $loggedIn = [];

            /**
             * @param  array<string, string>  $users
             */
            public function __construct(
                private readonly array $users,
                private readonly ?string $failing,
                private readonly bool $knownAfterLogin,
                private readonly bool $external,
            ) {
            }

            public function authIsExternal()
            {
                return $this->external;
            }

            /**
             * @param  array<string, mixed>  $credentials
             */
            public function authenticate($credentials)
            {
                if ($this->failing === 'authenticate') {
                    throw new \ErrorException("ldap_bind(): Unable to bind to server: Can't contact LDAP server");
                }

                $username = $credentials['username'] ?? '';
                if (($this->users[$username] ?? null) !== ($credentials['password'] ?? null)) {
                    throw new AuthenticationException();
                }
                $this->loggedIn[$username] = true;

                return true;
            }

            public function userExists($username, $throw_exception = false)
            {
                return isset($this->users[$username]);
            }

            public function getUserid($username)
            {
                if ($this->failing === 'lookup') {
                    throw new \ErrorException("ldap_search(): Search: Can't contact LDAP server");
                }
                if ($this->failing === 'authenticate') {
                    return -1;
                }

                return isset($this->users[$username]) && (! $this->knownAfterLogin || isset($this->loggedIn[$username])) ? $username : -1;
            }

            /**
             * @return array<string, mixed>|false
             */
            public function getUser($user_id)
            {
                return isset($this->users[$user_id]) ? ['username' => $user_id] : false;
            }
        };

        new \ReflectionProperty(LegacyAuth::class, '_instance')->setValue(null, $authorizer);
    }
}
