<?php

namespace LibreNMS\Tests\Browser;

use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use LibreNMS\Config;
use LibreNMS\Tests\Browser\Pages\LoginPage;
use LibreNMS\Tests\Browser\Pages\TwoFactorPage;
use LibreNMS\Tests\DuskTestCase;

/**
 * Class LoginTest
 * @group browser
 */
class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @throws \Throwable
     */
    public function testUserCanLogin()
    {
        $this->browse(function (Browser $browser) {
            $password = 'some_password';
            $user = User::factory()->create([
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $browser->visit(new LoginPage())
                ->type('username', $user->username)
                ->type('password', 'wrong_password')
                ->press('@login')
                ->assertPathIs('/login')
                ->type('username', $user->username)
                ->type('password', $password)
                ->press('@login')
                ->assertPathIs('/')
                ->logout();

            $user->delete();
        });
    }

    /**
     * @throws \Throwable
     */
    public function test2faLogin()
    {
        $this->browse(function (Browser $browser) {
            $password = 'another_password';
            $user = User::factory()->create([
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            Config::persist('twofactor', true); // set to db
            UserPref::setPref($user, 'twofactor', [
                'key' => '5P3FLXBX7NU3ZBFOTWZL2GL5MKFEWBOA', // known key: 634456, 613687, 064292
                'fails' => 0,
                'last' => 0,
                'counter' => 1,
            ]);

            $browser->visit(new LoginPage())
                ->type('username', $user->username)
                ->type('password', $password)
                ->press('#login')
                ->on(new TwoFactorPage())
                ->assertFocused('@input')
                ->keys('@input', '999999', '{enter}') // try the wrong code first
                ->assertPathIs('/2fa')
                ->keys('@input', '634456', '{enter}')
                ->assertPathIs('/')
                ->logout();

            $user->delete();
        });
    }
}
