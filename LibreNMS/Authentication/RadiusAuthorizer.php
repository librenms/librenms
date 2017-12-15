<?php

namespace LibreNMS\Authentication;

use Dapphp\Radius\Radius;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

class RadiusAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;

    /** @var Radius $radius */
    protected $radius;

    public function __construct()
    {
        $this->radius = new Radius(Config::get('radius.hostname'), Config::get('radius.secret'), Config::get('radius.suffix'), Config::get('radius.timeout'), Config::get('radius.port'));
    }

    public function authenticate($username, $password)
    {
        global $debug;

        if (empty($username)) {
            throw new AuthenticationException('Username is required');
        }

        if ($debug) {
            $this->radius->setDebug(true);
        }

        if ($this->radius->accessRequest($username, $password) === true) {
            $this->addUser($username, $password);
            return true;
        }

        throw new AuthenticationException();
    }

    public function addUser($username, $password, $level = 1, $email = '', $realname = '', $can_modify_passwd = 0, $description = '')
    {
        // Check to see if user is already added in the database
        if (!$this->userExists($username)) {
            $hasher    = new PasswordHash(8, false);
            $encrypted = $hasher->HashPassword($password);
            if (Config::get('radius.default_level') > 0) {
                $level = Config::get('radius.default_level');
            }
            $userid = dbInsert(array('username' => $username, 'password' => $encrypted, 'realname' => $realname, 'email' => $email, 'descr' => $description, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd), 'users');
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
        return dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username));
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
