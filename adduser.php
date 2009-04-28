#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1] && $argv[2] && $argv[3]) {
  if($argv[4]) {
   mysql_query("INSERT INTO `users` (`username`,`password`,`level`) VALUES ('".mres($argv[1])."',MD5('".$argv[2]."'),'".mres($argv[3])."'),'".mres($argv[4])."')");
  } else {
   mysql_query("INSERT INTO `users` (`username`,`password`,`level`) VALUES ('".mres($argv[1])."',MD5('".$argv[2]."'),'".mres($argv[3])."')");
  }
  if(mysql_affected_rows()) {
    echo("User ".$argv[1]." added successfully\n");
  }
} else {
  echo("Add User Tool\nUsage: ./adduser.php <username> <password> <level 1-10> [email]\n");
} 

?>
