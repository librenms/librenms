<?php

namespace LibreNMS\Tests\Browser;

use App\Models\User;
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
    /**
     * @test
     * @throws \Throwable
     */
    public function user_can_login()
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
