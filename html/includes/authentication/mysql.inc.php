<?php

function authenticate($username,$password)
{
  $encrypted = md5($password);
  $sql = "SELECT username FROM `users` WHERE `username`='".$username."' AND `password`='".$encrypted."'";
  $query = mysql_query($sql);
  $row = @mysql_fetch_array($query);
  if($row['username'] && $row['username'] == $username) 
  {
    return 1;
  }
  return 0;
}

function passwordscanchange()
{
  return 1;
}

function changepassword($username,$password)
{
  $encrypted = md5($password);
  $sql = "UPDATE `users` SET `password` = '$encrypted' WHERE `username`='".$username."'";
  $query = mysql_query($sql);
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
  $row = mysql_fetch_array(mysql_query($sql));
  return $row['level'];
}
 
function get_userid($username)
{
  $sql = "SELECT user_id FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_array(mysql_query($sql));
  return $row['user_id'];
}
 
?>
