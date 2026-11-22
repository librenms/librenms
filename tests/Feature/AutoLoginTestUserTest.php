<?php

namespace LibreNMS\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class AutoLoginTestUserTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testLogsInConfiguredUserInLocalEnvironment(): void
    {
        $user = User::factory()->admin()->create();
        config(['auth.test_user' => $user->username]);
        $this->app->instance('env', 'local');

        $this->get('/about')->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function testDoesNothingOutsideLocalEnvironment(): void
    {
        $user = User::factory()->admin()->create();
        config(['auth.test_user' => $user->username]);
        $this->app->instance('env', 'production');

        $this->get('/about')->assertRedirect();

        $this->assertGuest();
    }

    public function testDoesNothingWithoutConfiguredUser(): void
    {
        config(['auth.test_user' => null]);
        $this->app->instance('env', 'local');

        $this->get('/about')->assertRedirect();

        $this->assertGuest();
    }
}
