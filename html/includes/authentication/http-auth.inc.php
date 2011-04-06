<?php

function authenticate($username,$password)
{
  global $config;

  if (isset($_SERVER['REMOTE_USER']))
  {
    $_SESSION['username'] = mres($_SERVER['REMOTE_USER']);

    $sql = "SELECT username FROM `users` WHERE `username`='".$_SESSION['username'] . "'";;
    $query = mysql_query($sql);
    $row = @mysql_fetch_assoc($query);
    if ($row['username'] && $row['username'] == $_SESSION['username'])
    {
      return 1;
    }
    else
    {
      $_SESSION['username'] = $config['http_auth_guest'];
      return 1;
    }
  }
  return 0;
}

function passwordscanchange()
{
  return 0;
}

function changepassword($username,$newpassword)
{
  # Not supported
}

function auth_usermanagement()
{
  return 1;
}

function adduser($username, $password, $level, $email = "", $realname = "")
{
  mysql_query("INSERT INTO `users` (`username`,`password`,`level`, `email`, `realname`) VALUES ('".mres($username)."',MD5('".mres($password)."'),'".mres($level)."','".mres($email)."','".mres($realname)."')");

  return mysql_affected_rows();
}

function user_exists($username)
{
  return mysql_result(mysql_query("SELECT * FROM users WHERE username = '".mres($username)."'"),0);
}

function get_userlevel($username)
{
  $sql = "SELECT level FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_assoc(mysql_query($sql));
  return $row['level'];
}

function get_userid($username)
{
  $sql = "SELECT user_id FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_assoc(mysql_query($sql));
  return $row['user_id'];
}

function deluser($username)
{
  # Not supported
  return 0;
}

?>