<?php
/**
 * AuthorizerBase.php
 *
 * authentication functions
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Interfaces\Authentication\Authorizer;
use LibreNMS\Exceptions\AuthenticationException;

abstract class AuthorizerBase implements Authorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = 0;
    protected static $CAN_UPDATE_USER = 0;
    protected static $CAN_UPDATE_PASSWORDS = 0;
    protected static $AUTH_IS_EXTERNAL = 0;

    /**
     * Log out the user, unset cookies, destroy the session
     *
     * @param string $message The logout message.
     */
    public function logOutUser($message = 'Logged Out')
    {
        global $auth_message;

        dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged Out'), 'authlog');

        $this->clearRememberMe($_SESSION['username']);

        unset($_SESSION['authenticated']);
        session_destroy();

        $auth_message = $message; // global variable used to display a message to the user
    }

    /**
     * Log in the user and set up a few login tasks
     * $_SESSION['username'] must be set prior to calling this function
     * If twofactor authentication is enabled, it will be checked here.
     *
     * If everything goes well, $_SESSION['authenticated'] will be true after this function completes.
     * @return bool If the user was successfully logged in.
     * @throws AuthenticationException if anything failed why trying to log in
     */
    public function logInUser()
    {
        // set up variables, but don't override existing ones (ad anonymous bind can only get user_id at login)
        if (!isset($_SESSION['userlevel'])) {
            $_SESSION['userlevel'] = $this->getUserlevel($_SESSION['username']);
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = $this->getUserid($_SESSION['username']);
        }

        // check for valid user_id
        if ($_SESSION['user_id'] === false || $_SESSION['user_id'] < 0) {
            throw new AuthenticationException('Invalid Credentials');
        }

        if (!$this->sessionAuthenticated()) {
            // check twofactor
            if (Config::get('twofactor') === true && !isset($_SESSION['twofactor'])) {
                if (TwoFactor::showForm()) {
                    return false; // not done yet, one more cycle to show the 2fa form
                }
            }

            // if two factor isn't enabled or it has passed already ware are logged in
            if (!Config::get('twofactor') || $_SESSION['twofactor']) {
                $_SESSION['authenticated'] = true;
                dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged In'), 'authlog');
            }
        }

        if ($this->sessionAuthenticated()) {
            $this->setRememberMe();
        }

        return true;
    }

    /**
     * Check if the session is authenticated
     *
     * @return bool
     */
    public function sessionAuthenticated()
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'];
    }

    /**
     * Set or update the remember me cookie if $_SESSION['remember'] is set
     * If setting a new cookie, $_SESSION['username'] must be set
     */
    protected function setRememberMe()
    {
        if (!isset($_SESSION['remember'])) {
            return;
        }
        unset($_SESSION['remember']);

        $sess_id = session_id();
        $expiration = time() + 60 * 60 * 24 * Config::get('auth_remember');

        $db_entry = array(
            'session_value' => $sess_id,
            'session_expiry' => $expiration,
        );

        if (isset($_COOKIE['token'], $_COOKIE['auth'])) {
            $token_id = $_COOKIE['token'];
            $auth = $_COOKIE['auth'];
            dbUpdate($db_entry, 'session', 'session_auth=?', array($_COOKIE['auth']));
        } else {
            $token = strgen();
            $auth = strgen();
            $token_id = $_SESSION['username'] . '|' . password_hash($_SESSION['username'] . $token, PASSWORD_DEFAULT);

            $db_entry['session_username'] = $_SESSION['username'];
            $db_entry['session_token'] = $token;
            $db_entry['session_auth'] = $auth;
            dbInsert($db_entry, 'session');
        }

        setcookie('sess_id', $sess_id, $expiration, '/', null, Config::get('secure_cookies'), true);
        setcookie('token', $token_id, $expiration, '/', null, Config::get('secure_cookies'), true);
        setcookie('auth', $auth, $expiration, '/', null, Config::get('secure_cookies'), true);
    }

    /**
     * Check the remember me cookie
     * If the cookie is valid, $_SESSION['username'] will be set
     *
     * @param string $sess_id sess_id cookie value
     * @param string $token token cookie value
     * @return bool is the remember me token valid
     * @throws AuthenticationException thrown if the cookie is invalid
     */
    protected function checkRememberMe($sess_id, $token)
    {
        list($uname, $hash) = explode('|', $token);
        $session = dbFetchRow(
            "SELECT * FROM `session` WHERE `session_username`=? AND `session_value`=?",
            array($uname, $sess_id)
        );

        if (password_verify($uname . $session['session_token'], $hash)) {
            $_SESSION['username'] = $uname;
            return true;
        }

        $this->clearRememberMe($uname);
        throw new AuthenticationException('Cookie invalid, please log in.');
    }

    /**
     * Clear remember cookie and remove our database record
     *
     * @param $username
     */
    protected function clearRememberMe($username)
    {
        dbDelete(
            'session',
            '`session_username` =  ? AND `session_value` = ?',
            array($username, $_COOKIE['sess_id'])
        );

        unset($_COOKIE);

        $time = time() - 60 * 60 * 24 * Config::get('auth_remember'); // time in the past to make sure

        setcookie('sess_id', '', $time, '/', null, Config::get('secure_cookies'));
        setcookie('token', '', $time, '/', null, Config::get('secure_cookies'));
        setcookie('auth', '', $time, '/', null, Config::get('secure_cookies'));
    }

    public function reauthenticate($sess_id, $token)
    {
        //not supported by default
        return false;
    }

    public function canUpdatePasswords($username = '')
    {
        return static::$CAN_UPDATE_PASSWORDS;
    }

    public function changePassword($username, $newpassword)
    {
        //not supported by default
        return 0;
    }

    public function canManageUsers()
    {
        return static::$HAS_AUTH_USERMANAGEMENT;
    }

    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 0, $description = '')
    {
        //not supported by default
        return 0;
    }

    public function deleteUser($userid)
    {
        //not supported by default
        return 0;
    }

    public function canUpdateUsers()
    {
        return static::$CAN_UPDATE_USER;
    }

    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email)
    {
        //not supported by default
        return 0;
    }

    public function authIsExternal()
    {
        return static::$AUTH_IS_EXTERNAL;
    }

    public function getExternalUsername()
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            return clean($_SERVER['REMOTE_USER']);
        } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
            return clean($_SERVER['PHP_AUTH_USER']);
        }
    }
}
