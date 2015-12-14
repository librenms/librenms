<?php

require_once $config['install_dir'].'/lib/pure_php_radius/radius.class.php';

$radius = new Radius($config['radius']['hostname'], $config['radius']['secret'], $config['radius']['suffix'], $config['radius']['timeout'], $config['radius']['port']);

function authenticate($username, $password) {
    global $config, $radius, $debug;

    if (empty($username)) {
        return 0;
    }
    else {
        if ($debug) {
            $radius->SetDebugMode(TRUE);
        }
        $rad = $radius->AccessRequest($username,$password);
        if($rad === true) {
            adduser($username);
            return 1;
        }
        else {
            return 0;
        }
    }
}

function reauthenticate() {
    return 0;
}


function passwordscanchange() {
    // not supported so return 0
    return 0;
}


function changepassword() {
    // not supported so return 0
    return 0;
}


function auth_usermanagement() {
    // not supported so return 0
    return 1;
}


function adduser($username, $password, $level=1, $email='', $realname='', $can_modify_passwd=0, $description='', $twofactor=0) {
    // Check to see if user is already added in the database
    global $config;
    if (!user_exists($username)) {
        $hasher    = new PasswordHash(8, false);
        $encrypted = $hasher->HashPassword($password);
        if ($config['radius']['default_level'] > 0) {
            $level = $config['radius']['default_level'];
        }
        $userid = dbInsert(array('username' => $username, 'password' => $encrypted, 'realname' => $realname, 'email' => $email, 'descr' => $description, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'twofactor' => $twofactor), 'users');
        if ($userid == false) {
            return false;
        }
        else {
            foreach (dbFetchRows('select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc',array($userid)) as $notif) {
                dbInsert(array('notifications_id'=>$notif['notifications_id'],'user_id'=>$userid,'key'=>'read','value'=>1),'notifications_attribs');
            }
        }
        return $userid;
    }
    else {
        return false;
    }
}

function user_exists($username) {
    return dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username), true);
}


function get_userlevel($username) {
    return dbFetchCell('SELECT `level` FROM `users` WHERE `username` = ?', array($username), true);
}


function get_userid($username) {
    return dbFetchCell('SELECT `user_id` FROM `users` WHERE `username` = ?', array($username), true);
}


function deluser($username) {
    dbDelete('bill_perms', '`user_name` =  ?', array($username));
    dbDelete('devices_perms', '`user_name` =  ?', array($username));
    dbDelete('ports_perms', '`user_name` =  ?', array($username));
    dbDelete('users_prefs', '`user_name` =  ?', array($username));
    dbDelete('users', '`user_name` =  ?', array($username));
    return dbDelete('users', '`username` =  ?', array($username));
}


function get_userlist() {
    return dbFetchRows('SELECT * FROM `users`');
}


function can_update_users() {
    // supported so return 1
    return 1;
}


function get_user($user_id) {
    return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id), true);
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email) {
    dbUpdate(array('realname' => $realname, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));

}
