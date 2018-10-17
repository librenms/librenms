<?php
/**
 * TwoFactorTest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\Browser\Pages\LoginPage;
use LibreNMS\Tests\Browser\Pages\PreferencesPage;
use LibreNMS\Tests\DuskTestCase;

class TwoFactorTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @throws \Throwable
     */
    public function testEnableTwoFactor()
    {
        $this->browse(function (Browser $browser) {
            $password = '2fa_password';
            $user = factory(User::class)->create([
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $browser
                ->loginAs($user->user_id)
                ->assertAuthenticated()
                ->visit(new PreferencesPage())


//                ->visit(new LoginPage())
//                ->type('username', $user->username)
//                ->type('password', $password)
//                ->press('#login')
//                ->assertPathIs('/');




                ->assertSelected('twofactortype','time')
                ->select('twofactortype', 'counter')
                ->press('@generate')
                ->visit(new PreferencesPage())


                ->on(new LoginPage())
                ->type('username', $user->username)
                ->type('password', $password)
                ->press('#login')
//
                ->on(new PreferencesPage())
                ->assertSelected('twofactortype','time')
                ->select('twofactortype', 'counter')
                ->press('@generate')

                ->pause(3000)
//                ->logout()
                ;

//            dd(\Session::all());

            $user->delete();
        });
    }
}
