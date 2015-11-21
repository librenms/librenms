<?php


function authenticate($username, $password) {
    $encrypted_old = md5($password);
    $row           = dbFetchRow('SELECT username,password FROM `users` WHERE `username`= ?', array($username), true);
    if ($row['username'] && $row['username'] == $username) {
        // Migrate from old, unhashed password
        if ($row['password'] == $encrypted_old) {
            $row_type = dbFetchRow('DESCRIBE users password');
            if ($row_type['Type'] == 'varchar(34)') {
                changepassword($username, $password);
            }

            return 1;
        }
        else if (substr($row['password'], 0, 3) == '$1$') {
            $row_type = dbFetchRow('DESCRIBE users password');
            if ($row_type['Type'] == 'varchar(60)') {
                if ($row['password'] == crypt($password, $row['password'])) {
                    changepassword($username, $password);
                }
            }
        }

        $hasher = new PasswordHash(8, false);
        if ($hasher->CheckPassword($password, $row['password'])) {
            return 1;
        }
    }//end if

    return 0;

}//end authenticate()


function reauthenticate($sess_id, $token) {
    list($uname,$hash) = explode('|', $token);
    $session           = dbFetchRow("SELECT * FROM `session` WHERE `session_username` = '$uname' AND session_value='$sess_id'", array(), true);
    $hasher            = new PasswordHash(8, false);
    if ($hasher->CheckPassword($uname.$session['session_token'], $hash)) {
        $_SESSION['username'] = $uname;
        return 1;
    }
    else {
        return 0;
    }

}//end reauthenticate()


function passwordscanchange($username='') {
    /*
     * By default allow the password to be modified, unless the existing
     * user is explicitly prohibited to do so.
     */

    if (empty($username) || !user_exists($username)) {
        return 1;
    }
    else {
        return dbFetchCell('SELECT can_modify_passwd FROM users WHERE username = ?', array($username), true);
    }

}//end passwordscanchange()


/**
 * From: http://code.activestate.com/recipes/576894-generate-a-salt/
 * This function generates a password salt as a string of x (default = 15) characters
 * ranging from a-zA-Z0-9.
 * @param $max integer The number of characters in the string
 * @author AfroSoft <scripts@afrosoft.co.cc>
 */
function generateSalt($max=15) {
    $characterList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $i             = 0;
    $salt          = '';
    do {
        $salt .= $characterList{mt_rand(0, strlen($characterList))};
        $i++;
    } while ($i <= $max);

    return $salt;

}//end generateSalt()


function changepassword($username, $password) {
    $hasher    = new PasswordHash(8, false);
    $encrypted = $hasher->HashPassword($password);
    return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username));

}//end changepassword()


function auth_usermanagement() {
    return 1;

}//end auth_usermanagement()


function adduser($username, $password, $level, $email='', $realname='', $can_modify_passwd=1, $description='', $twofactor=0) {
    if (!user_exists($username)) {
        $hasher    = new PasswordHash(8, false);
        $encrypted = $hasher->HashPassword($password);
        $userid    = dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'descr' => $description, 'twofactor' => $twofactor), 'users');
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

}//end adduser()


function user_exists($username) {
    $return = @dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username), true);
    return $return;

}//end user_exists()


function get_userlevel($username) {
    return dbFetchCell('SELECT `level` FROM `users` WHERE `username` = ?', array($username), true);

}//end get_userlevel()


function get_userid($username) {
    return dbFetchCell('SELECT `user_id` FROM `users` WHERE `username` = ?', array($username), true);

}//end get_userid()


function deluser($username) {
    dbDelete('bill_perms', '`user_name` =  ?', array($username));
    dbDelete('devices_perms', '`user_name` =  ?', array($username));
    dbDelete('ports_perms', '`user_name` =  ?', array($username));
    dbDelete('users_prefs', '`user_name` =  ?', array($username));
    dbDelete('users', '`user_name` =  ?', array($username));

    return dbDelete('users', '`username` =  ?', array($username));

}//end deluser()


function get_userlist() {
    return dbFetchRows('SELECT * FROM `users`');

}//end get_userlist()


function can_update_users() {
    // supported so return 1
    return 1;

}//end can_update_users()


function get_user($user_id) {
    return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id), true);

}//end get_user()


function update_user($user_id, $realname, $level, $can_modify_passwd, $email) {
    dbUpdate(array('realname' => $realname, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));

}//end update_user()
