<?php

namespace LibreNMS\Tests\Feature\Logging;

use App\Models\AuthLog;
use App\Models\User;
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

    /**
     * @return array<int, array{user: string, address: string, result: string}>
     */
    private function authLog(): array
    {
        return AuthLog::orderBy('id')->get(['user', 'address', 'result'])->toArray();
    }
}
