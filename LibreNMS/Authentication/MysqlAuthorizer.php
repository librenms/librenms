<?php

namespace LibreNMS\Authentication;

use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

class MysqlAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;
    protected static $CAN_UPDATE_PASSWORDS = 1;

    public function authenticate($username, $password)
    {
        $hash = dbFetchCell('SELECT `password` FROM `users` WHERE `username`= ?', array($username));

        // check for old passwords
        if (strlen($hash) == 32) {
            // md5
            if (md5($password) === $hash) {
                $this->changePassword($username, $password);
                return true;
            }
        } elseif (starts_with($hash, '$1$')) {
            // old md5 crypt
            if (crypt($password, $hash) == $hash) {
                $this->changePassword($username, $password);
                return true;
            }
        } elseif (starts_with($hash, '$P$')) {
            // Phpass
            $hasher = new PasswordHash();
            if ($hasher->CheckPassword($password, $hash)) {
                $this->changePassword($username, $password);
                return true;
            }
        }

        if (password_verify($password, $hash)) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function reauthenticate($sess_id, $token)
    {
        return $this->checkRememberMe($sess_id, $token);
    }

    public function canUpdatePasswords($username = '')
    {
        /*
         * By default allow the password to be modified, unless the existing
         * user is explicitly prohibited to do so.
         */

        if (!static::$CAN_UPDATE_PASSWORDS) {
            return 0;
        } elseif (empty($username) || !$this->userExists($username)) {
            return 1;
        } else {
            return dbFetchCell('SELECT can_modify_passwd FROM users WHERE username = ?', array($username));
        }
    }

    public function changePassword($username, $password)
    {
        // check if updating passwords is allowed (mostly for classes that extend this)
        if (!static::$CAN_UPDATE_PASSWORDS) {
            return 0;
        }

        $encrypted = password_hash($password, PASSWORD_DEFAULT);
        return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username));
    }

    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 1, $description = '')
    {
        if (!$this->userExists($username)) {
            $encrypted = password_hash($password, PASSWORD_DEFAULT);
            $userid = dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'descr' => $description), 'users');
            if ($userid == false) {
                return false;
            } else {
                foreach (dbFetchRows('select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc', array($userid)) as $notif) {
                    dbInsert(array('notifications_id'=>$notif['notifications_id'],'user_id'=>$userid,'key'=>'read','value'=>1), 'notifications_attribs');
                }
            }
            return $userid;
        } else {
            return false;
        }
    }

    public function userExists($username, $throw_exception = false)
    {
        return (bool)dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username));
    }

    public function getUserlevel($username)
    {
        return dbFetchCell('SELECT `level` FROM `users` WHERE `username` = ?', array($username));
    }

    public function getUserid($username)
    {
        return dbFetchCell('SELECT `user_id` FROM `users` WHERE `username` = ?', array($username));
    }

    public function deleteUser($userid)
    {
        dbDelete('bill_perms', '`user_id` =  ?', array($userid));
        dbDelete('devices_perms', '`user_id` =  ?', array($userid));
        dbDelete('ports_perms', '`user_id` =  ?', array($userid));
        dbDelete('users_prefs', '`user_id` =  ?', array($userid));
        dbDelete('users', '`user_id` =  ?', array($userid));

        return dbDelete('users', '`user_id` =  ?', array($userid));
    }

    public function getUserlist()
    {
        return dbFetchRows('SELECT * FROM `users` ORDER BY `username`');
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
