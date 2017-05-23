<?php

use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

function authenticate($username, $password)
{
    if (user_exists($username)) {
        return true;
    }

    throw new AuthenticationException('No matching user found and http_auth_guest is not set');
}


function reauthenticate($sess_id, $token)
{
    return false;
}


function passwordscanchange($username = '')
{
    return 0;
}


function changepassword($username, $newpassword)
{
    // Not supported
}


function auth_usermanagement()
{
    return 1;
}


function adduser($username, $password, $level, $email = '', $realname = '', $can_modify_passwd = 1, $description = '')
{
    if (!user_exists($username)) {
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
}


function user_exists($username)
{
    global $config;

    return dbFetchCell(
        'SELECT COUNT(*) FROM `users` WHERE `username`=? OR `username`=?',
        array($username, $config['http_auth_guest'])
    ) > 0;
}


function get_userlevel($username)
{
    global $config;

    return dbFetchCell(
        'SELECT `level` FROM `users` WHERE `username`=? OR `username`=?',
        array($username, $config['http_auth_guest'])
    );
}


function get_userid($username)
{
    global $config;

    return dbFetchCell(
        'SELECT `user_id` FROM `users` WHERE `username`=? OR `username`=?',
        array($username, $config['http_auth_guest'])
    );
}


function deluser($username)
{
    // Not supported
    return 0;
}


function get_userlist()
{
    return dbFetchRows('SELECT * FROM `users`');
}


function can_update_users()
{
    // supported so return 1
    return 1;
}


function get_user($user_id)
{
    return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id));
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email)
{
    dbUpdate(array('realname' => $realname, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));
}
