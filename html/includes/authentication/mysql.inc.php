<?php

function authenticate($username,$password)
{
  $encrypted = md5($password);
  $sql = "SELECT username FROM `users` WHERE `username`='".$_SESSION['username']."' AND `password`='".$encrypted."'";
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

function changepassword($username,$newpassword)
{
  $encrypted = md5($password);
  $sql = "UPDATE `users` SET  password`='$encrypted' WHERE `username`='".$_SESSION['username']."'";
  $query = mysql_query($sql);
}

?>