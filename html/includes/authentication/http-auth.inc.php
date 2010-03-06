<?php

function authenticate($username,$password)
{
  global $config;

  if(isset($_SERVER['REMOTE_USER'])) 
  {
    $_SESSION['username'] = mres($_SERVER['REMOTE_USER']);
    
    $sql = "SELECT username FROM `users` WHERE `username`='".$_SESSION['username'] . "'";;
    $query = mysql_query($sql);
    $row = @mysql_fetch_array($query);
    if($row['username'] && $row['username'] == $_SESSION['username']) 
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
  
  
?>