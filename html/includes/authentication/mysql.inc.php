<?php

function authenticate($username,$password)
{
  $encrypted_old = md5($password);
  $row = dbFetchRow("SELECT username,password FROM `users` WHERE `username`= ?", array($username));
  if ($row['username'] && $row['username'] == $username)
  {
    // Migrate from old, unhashed password
    if ($row['password'] == $encrypted_old)
    {
      $row = dbFetchRow("DESCRIBE users password");
      if ($row['Type'] == 'varchar(34)')
      {
        changepassword($username,$password);
      }
      return 1;
    }
    if ($row['password'] == crypt($password,$row['password']))
    {
      return 1;
    }
  }
  return 0;
}

function passwordscanchange($username="")
{
  /*
   * By default allow the password to be modified, unless the existing
   * user is explicitly prohibited to do so.
   */

  if (empty($username) || !user_exists($username))
  {
    return 1;
  } else {
    return dbFetchCell("SELECT can_modify_passwd FROM users WHERE username = ?", array($username));
  }
}

/**
 * From: http://code.activestate.com/recipes/576894-generate-a-salt/
 * This function generates a password salt as a string of x (default = 15) characters
 * ranging from a-zA-Z0-9.
 * @param $max integer The number of characters in the string
 * @author AfroSoft <scripts@afrosoft.co.cc>
 */
function generateSalt($max = 15)
{
  $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $i = 0;
  $salt = "";
  do
  {
    $salt .= $characterList{mt_rand(0,strlen($characterList))};
    $i++;
  } while ($i <= $max);

  return $salt;
}

function changepassword($username,$password)
{
  $encrypted = crypt($password,'$1$' . generateSalt(8).'$');
  return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username));
}

function auth_usermanagement()
{
  return 1;
}

function adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd='1')
{
  if (!user_exists($username))
  {
    $encrypted = crypt($password,'$1$' . generateSalt(8).'$');
    return dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd), 'users');
  } else {
    return FALSE;
  }
}

function user_exists($username)
{
  return @dbFetchCell("SELECT COUNT(*) FROM users WHERE username = ?", array($username));
}

function get_userlevel($username)
{
  return dbFetchCell("SELECT `level` FROM `users` WHERE `username` = ?", array($username));
}

function get_userid($username)
{
  return dbFetchCell("SELECT `user_id` FROM `users` WHERE `username` = ?", array($username));
}

function deluser($username)
{

  dbDelete('bill_perms', "`user_name` =  ?", array($username));
  dbDelete('devices_perms', "`user_name` =  ?", array($username));
  dbDelete('ports_perms', "`user_name` =  ?", array($username));
  dbDelete('users_prefs', "`user_name` =  ?", array($username));
  dbDelete('users', "`user_name` =  ?", array($username));

  return dbDelete('users', "`username` =  ?", array($username));

}


?>
