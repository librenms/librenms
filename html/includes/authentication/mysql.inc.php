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

?>