#!/usr/bin/env php
<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

chdir(dirname($argv[0]));

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
  if (isset($argv[1]) && isset($argv[2]) && isset($argv[3]))
  {
    if (!user_exists($argv[1]))
    {
      if (adduser($argv[1],$argv[2],$argv[3],@$argv[4]))
      {
        echo("User ".$argv[1]." added successfully\n");
      }
    }
    else
    {
      echo("User ".$argv[1]." already exists!\n");
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
