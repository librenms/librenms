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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://librenms.org
 * @copyright  2017 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Tests;

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use function strip_tags;
use function strip_tags as strip_tags1;

class AuthHTTPTest extends TestCase
{
    private $original_auth_mech;
    private $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->original_auth_mech = Config::get('auth_mechanism');
        Config::set('auth_mechanism', 'http-auth');
        $this->server = $_SERVER;
    }

    protected function tearDown(): void
    {
        Config::set('auth_mechanism', $this->original_auth_mech);
        $_SERVER = $this->server;
        parent::tearDown();
    }

    // Document the modules current behaviour, so that changes trigger test failures
    public function testCapabilityFunctions()
    {
        $a = LegacyAuth::reset();

        $this->assertFalse($a->canUpdatePasswords());
        $this->assertFalse($a->changePassword(null, null));
        $this->assertTrue($a->canManageUsers());
        $this->assertTrue($a->canUpdateUsers());
        $this->assertTrue($a->authIsExternal());
    }

    public function testOldBehaviourAgainstCurrent()
    {
        $old_username = null;
        $new_username = null;

        $users = ['steve',  '   steve', 'steve   ', '   steve   ', '    steve   ', '', 'CAT'];
        $vars = ['REMOTE_USER', 'PHP_AUTH_USER'];

        $a = LegacyAuth::reset();

        foreach ($vars as $v) {
            foreach ($users as $u) {
                $_SERVER[$v] = $u;

                // Old Behaviour
                if (isset($_SERVER['REMOTE_USER'])) {
                    $old_username = strip_tags1($_SERVER['REMOTE_USER']);
                } elseif (isset($_SERVER['PHP_AUTH_USER']) && Config::get('auth_mechanism') === 'http-auth') {
                    $old_username = strip_tags($_SERVER['PHP_AUTH_USER']);
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
    }
}
