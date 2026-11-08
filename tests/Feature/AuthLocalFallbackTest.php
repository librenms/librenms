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

        LibrenmsConfig::set('auth_fallback_local', false);
        $this->assertNull($provider->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']));
    }

    public function testLocalUserLogsInWhenTheConfiguredAuthIsDown(): void
    {
        $this->useAuthorizer(['bob' => 'dir-pass'], down: true);
        $local = $this->localUser('admin', 'local-pass');

        $found = new LegacyUserProvider()->retrieveByCredentials(['username' => 'admin', 'password' => 'local-pass']);
        $this->assertSame($local->getAuthIdentifier(), $found?->getAuthIdentifier());
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
     */
    private function useAuthorizer(array $users, bool $down = false, bool $knownAfterLogin = false): void
    {
        $authorizer = new class($users, $down, $knownAfterLogin) extends AuthorizerBase
        {
            /** @var array<string, bool> */
            private array $loggedIn = [];

            /**
             * @param  array<string, string>  $users
             */
            public function __construct(private readonly array $users, private readonly bool $down, private readonly bool $knownAfterLogin)
            {
            }

            /**
             * @param  array<string, mixed>  $credentials
             */
            public function authenticate($credentials)
            {
                $username = $credentials['username'] ?? '';
                if ($this->down || ($this->users[$username] ?? null) !== ($credentials['password'] ?? null)) {
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
                if ($this->down) {
                    throw new \RuntimeException('server is down');
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
