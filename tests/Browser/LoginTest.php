<?php

namespace LibreNMS\Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\Browser\Pages\LoginPage;
use LibreNMS\Tests\DuskTestCase;

/**
 * Class LoginTest
 * @package LibreNMS\Tests\Browser
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
            $user = factory(User::class)->create([
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $browser->visit(new LoginPage())
                ->type('username', $user->username)
                ->type('password', $password)
                ->press('#login')
                ->assertPathIs('/');

            $user->delete();
        });
    }
}
