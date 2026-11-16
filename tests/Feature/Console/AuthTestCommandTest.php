<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Tests\DBTestCase;

class AuthTestCommandTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testMysqlAuthenticationStillDisplaysAccount(): void
    {
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LegacyAuth::reset();
        $user = User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'password')
            ->expectsOutput('User (' . $user->user_id . '):')
            ->expectsOutput('AUTH SUCCESS')
            ->doesntExpectOutputToContain('remember_token')
            ->doesntExpectOutputToContain('password =>')
            ->assertExitCode(0);
    }

    public function testMysqlAuthenticationFailureReturnsFailureExitCode(): void
    {
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LegacyAuth::reset();
        User::factory()->create(['username' => 'alice', 'auth_type' => 'mysql', 'enabled' => 1]);

        $this->artisan('auth:test', ['username' => 'alice'])
            ->expectsQuestion('Password', 'wrong')
            ->expectsOutputToContain('Error:')
            ->expectsOutput('AUTH FAILURE')
            ->doesntExpectOutputToContain('wrong')
            ->assertExitCode(1);
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
}
