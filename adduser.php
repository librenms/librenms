#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

if (file_exists('html/includes/authentication/' . $config['auth_mechanism'] . '.inc.php'))
{
  include('html/includes/authentication/' . $config['auth_mechanism'] . '.inc.php');
}
else
{
  echo("ERROR: no valid auth_mechanism defined.\n");
  exit();
}       
       
if (auth_usermanagement())
{
  if ($argv[1] && $argv[2] && $argv[3])
  {
    if (adduser($argv[1],$argv[2],$argv[3],$argv[4]))
    {
      echo("User ".$argv[1]." added successfully\n");
    }
  }
  else
  {
    echo("Add User Tool\nUsage: ./adduser.php <username> <password> <level 1-10> [email]\n");
  }
}
else
{
  echo("Auth module does not allow adding users!\n");
}

?>
