<?php

namespace LibreNMS\Tests\Feature\Logging;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Tests\DBTestCase;

final class AuthLogTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testLoginAndLogoutAreLogged(): void
    {
        $user = User::factory()->create();

        Auth::attempt(['username' => $user->username, 'password' => 'password']);
        Auth::logout();

        $this->assertSame([
            ['user' => $user->username, 'address' => '127.0.0.1', 'result' => 'Logged In'],
            ['user' => $user->username, 'address' => '127.0.0.1', 'result' => 'Logged Out'],
        ], $this->authLog());
    }

    public function testRejectedPasswordIsLoggedOnce(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Auth::attempt(['username' => $user->username, 'password' => 'wrong']));

        $this->assertSame([
            ['user' => $user->username, 'address' => '127.0.0.1', 'result' => 'Invalid credentials'],
        ], $this->authLog());
    }

    public function testUnknownUserIsLogged(): void
    {
        $this->assertFalse(Auth::attempt(['username' => 'nobody', 'password' => 'wrong']));

        $this->assertSame([
            ['user' => 'nobody', 'address' => '127.0.0.1', 'result' => 'User not found'],
        ], $this->authLog());
    }

    public function testFailedLoginByOtherIdentifierIsLogged(): void
    {
        event(new Failed('web', null, ['email' => 'nobody@example.com', 'password' => 'wrong']));

        $this->assertSame([
            ['user' => 'nobody@example.com', 'address' => '127.0.0.1', 'result' => 'User not found'],
        ], $this->authLog());
    }

    public function testInvalidApiTokenIsLogged(): void
    {
        $user = User::factory()->create(['enabled' => 0]);

        $this->getJson('/api/v0/devices', ['X-Auth-Token' => 'not-a-token'])->assertUnauthorized();
        $this->getJson('/api/v0/devices', ['X-Auth-Token' => $user->createToken('test')->plainTextToken])->assertUnauthorized();
        $this->getJson('/api/v0/devices')->assertUnauthorized(); // no token presented, not an attempt

        $this->assertSame([
            ['user' => '', 'address' => '127.0.0.1', 'result' => 'Invalid API token'],
            ['user' => $user->username, 'address' => '127.0.0.1', 'result' => 'Invalid API token'],
        ], $this->authLog());
    }

    public function testAuthLogCanAlsoBeWrittenToFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'authlog');
        config([
            'logging.channels.auth.channels' => ['auth_db', 'auth_file'],
            'logging.channels.auth_file.path' => $file,
        ]);

        try {
            $this->assertFalse(Auth::attempt(['username' => 'nobody', 'password' => 'wrong']));

            $this->assertStringContainsString('[WARNING] User not found {"user":"nobody","address":"127.0.0.1"}', file_get_contents($file));
            $this->assertCount(1, $this->authLog());
        } finally {
            unlink($file);
        }
    }

    /**
     * @return array<int, array{user: string, address: string, result: string}>
     */
    private function authLog(): array
    {
        return AuthLog::orderBy('id')->get(['user', 'address', 'result'])->toArray();
    }
}
