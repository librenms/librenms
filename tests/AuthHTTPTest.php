<?php
/**
 * AuthHTTP.php
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
 * @link       https://librenms.org
 * @copyright  2017 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Tests;

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

// Note that as this test set depends on mres(), it is a DBTestCase even though the database is unused
class AuthHTTPTest extends DBTestCase
{
    // Document the modules current behaviour, so that changes trigger test failures
    public function testCapabilityFunctions()
    {
        Config::set('auth_mechanism', 'http-auth');

        $a = LegacyAuth::reset();

        $this->assertTrue($a->canUpdatePasswords() === 0);
        $this->assertTrue($a->changePassword(null, null) === 0);
        $this->assertTrue($a->canManageUsers() === 1);
        $this->assertTrue($a->canUpdateUsers() === 1);
        $this->assertTrue($a->authIsExternal() === 1);
    }

    public function testOldBehaviourAgainstCurrent()
    {
        $old_username = null;
        $new_username = null;

        Config::set('auth_mechanism', 'http-auth');
        $users = array('steve',  '   steve', 'steve   ', '   steve   ', '    steve   ', '', 'CAT');
        $vars = array('REMOTE_USER', 'PHP_AUTH_USER');

        $a = LegacyAuth::reset();

        foreach ($vars as $v) {
            foreach ($users as $u) {
                $_SERVER[$v] = $u;

                // Old Behaviour
                if (isset($_SERVER['REMOTE_USER'])) {
                    $old_username = clean($_SERVER['REMOTE_USER']);
                } elseif (isset($_SERVER['PHP_AUTH_USER']) && Config::get('auth_mechanism') === 'http-auth') {
                    $old_username = clean($_SERVER['PHP_AUTH_USER']);
                }

                // Current Behaviour
                if ($a->authIsExternal()) {
                    $new_username = $a->getExternalUsername();
                }

                $this->assertFalse($old_username === null);
                $this->assertFalse($new_username === null);

                $this->assertTrue($old_username === $new_username);
            }

            unset($_SERVER[$v]);
        }

        Config::forget('auth_mechanism');
    }
}
