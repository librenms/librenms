<?php

namespace LibreNMS\Authentication;

use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

class MysqlAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;

    public function authenticate($username, $password)
    {
        $encrypted_old = md5($password);
        $row           = dbFetchRow('SELECT username,password FROM `users` WHERE `username`= ?', array($username));
        if ($row['username'] && $row['username'] == $username) {
            // Migrate from old, unhashed password
            if ($row['password'] == $encrypted_old) {
                $row_type = dbFetchRow('DESCRIBE users password');
                if ($row_type['Type'] == 'varchar(34)') {
                    $this->changePassword($username, $password);
                }

                return true;
            } elseif (substr($row['password'], 0, 3) == '$1$') {
                $row_type = dbFetchRow('DESCRIBE users password');
                if ($row_type['Type'] == 'varchar(60)') {
                    if ($row['password'] == crypt($password, $row['password'])) {
                        $this->changePassword($username, $password);
                    }
                }
            }

            $hasher = new PasswordHash(8, false);
            if ($hasher->CheckPassword($password, $row['password'])) {
                return true;
            }
        }//end if

        throw new AuthenticationException();
    }//end authenticate()


    public function reauthenticate($sess_id, $token)
    {
        return $this->checkRememberMe($sess_id, $token);
    }//end reauthenticate()


    public function canUpdatePasswords($username = '')
    {
        /*
         * By default allow the password to be modified, unless the existing
         * user is explicitly prohibited to do so.
         */

        if (empty($username) || !$this->userExists($username)) {
            return 1;
        } else {
            return dbFetchCell('SELECT can_modify_passwd FROM users WHERE username = ?', array($username));
        }
    }//end passwordscanchange()


    /**
     * From: http://code.activestate.com/recipes/576894-generate-a-salt/
     * This public function generates a password salt as a string of x (default = 15) characters
     * ranging from a-zA-Z0-9.
     * @param $max integer The number of characters in the string
     * @author AfroSoft <scripts@afrosoft.co.cc>
     */
    public function generateSalt($max = 15)
    {
        $characterList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $i             = 0;
        $salt          = '';
        do {
            $salt .= $characterList{mt_rand(0, strlen($characterList))};
            $i++;
        } while ($i <= $max);

        return $salt;
    }//end generateSalt()


    public function changePassword($username, $password)
    {
        $hasher    = new PasswordHash(8, false);
        $encrypted = $hasher->HashPassword($password);
        return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username));
    }//end changepassword()

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
                    dbInsert(array('notifications_id'=>$notif['notifications_id'],'user_id'=>$userid,'key'=>'read','value'=>1), 'notifications_attribs');
                }
            }
            return $userid;
        } else {
            return false;
        }
    }//end adduser()


    public function userExists($username, $throw_exception = false)
    {
        $return = @dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username));
        return $return;
    }//end userExists()


    public function getUserlevel($username)
    {
        return dbFetchCell('SELECT `level` FROM `users` WHERE `username` = ?', array($username));
    }//end getUserlevel()


    public function getUserid($username)
    {
        return dbFetchCell('SELECT `user_id` FROM `users` WHERE `username` = ?', array($username));
    }//end getUserid()


    public function deleteUser($userid)
    {
        dbDelete('bill_perms', '`user_id` =  ?', array($userid));
        dbDelete('devices_perms', '`user_id` =  ?', array($userid));
        dbDelete('ports_perms', '`user_id` =  ?', array($userid));
        dbDelete('users_prefs', '`user_id` =  ?', array($userid));
        dbDelete('users', '`user_id` =  ?', array($userid));

        return dbDelete('users', '`user_id` =  ?', array($userid));
    }//end deluser()


    public function getUserlist()
    {
        return dbFetchRows('SELECT * FROM `users` ORDER BY `username`');
    }//end getUserlist()


    public function getUser($user_id)
    {
        return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id));
    }//end getUser()


    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email)
    {
        dbUpdate(array('realname' => $realname, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));
    }//end updateUser()
}
