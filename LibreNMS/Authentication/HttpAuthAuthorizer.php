<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

class HttpAuthAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;

    public function authenticate($username, $password)
    {
        if ($this->userExists($username)) {
            return true;
        }

        throw new AuthenticationException('No matching user found and http_auth_guest is not set');
    }


    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 1, $description = '')
    {
        if (!$this->userExists($username)) {
            $hasher    = new PasswordHash(8, false);
            $encrypted = $hasher->HashPassword($password);
            $userid    = dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'descr' => $description), 'users');
            if ($userid == false) {
                return false;
            } else {
                foreach (dbFetchRows('select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc', array($userid)) as $notif) {
                    dbInsert(array('notifications_id' => $notif['notifications_id'], 'user_id' => $userid, 'key' => 'read', 'value' => 1), 'notifications_attribs');
                }
            }
            return $userid;
        } else {
            return false;
        }
    }


    public function userExists($username, $throw_exception = false)
    {
        $query  = 'SELECT COUNT(*) FROM `users` WHERE `username`=?';
        $params = array($username);

        if (Config::has('http_auth_guest')) {
            $query    .= ' OR `username`=?';
            $params[] = Config::get('http_auth_guest');
        }

        return dbFetchCell($query, $params) > 0;
    }


    public function getUserlevel($username)
    {
        $user_level = dbFetchCell('SELECT `level` FROM `users` WHERE `username`=?', array($username));

        if ($user_level) {
            return $user_level;
        }

        if (Config::has('http_auth_guest')) {
            return dbFetchCell('SELECT `level` FROM `users` WHERE `username`=?', array(Config::get('http_auth_guest')));
        }

        return 0;
    }


    public function getUserid($username)
    {
        $user_id = dbFetchCell('SELECT `user_id` FROM `users` WHERE `username`=?', array($username));

        if ($user_id) {
            return $user_id;
        }

        if (Config::has('http_auth_guest')) {
            return dbFetchCell('SELECT `user_id` FROM `users` WHERE `username`=?', array(Config::get('http_auth_guest')));
        }

        return -1;
    }


    public function getUserlist()
    {
        return dbFetchRows('SELECT * FROM `users`');
    }


    public function getUser($user_id)
    {
        return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id));
    }


    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email)
    {
        dbUpdate(array('realname' => $realname, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));
    }
}
