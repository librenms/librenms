<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

enable_debug();

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("includes/functions.inc.php");
include_once("../includes/functions.php");
include_once("includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

$new_conf_type = $_POST['new_conf_type'];
$new_conf_name = $_POST['new_conf_name'];
$new_conf_desc = $_POST['new_conf_desc'];

if(empty($new_conf_name))
{
  echo("You haven't specified a config name");
  exit;
}
elseif(empty($new_conf_desc))
{
  echo("You haven't specified a config description");
  exit;
}
elseif(empty($_POST['new_conf_single_value']) && empty($_POST['new_conf_multi_value']))
{
  echo("You haven't specified a config value");
  exit;
}

if($new_conf_type == 'Single')
{
  $new_conf_type = 'single';
  $new_conf_value = $_POST['new_conf_single_value'];
  if(dbInsert(array('config_name' => $new_conf_name, 'config_value' => $new_conf_value, 'config_default' => $new_conf_value, 'config_type' => $new_conf_type, 'config_desc' => $new_conf_desc, 'config_group' => '500_Custom Settings', 'config_sub_group' => '01_Custom settings', 'config_hidden' => '0', 'config_disabled' => '0'), 'config'))
  {
    $db_inserted = 1;
  }
}
elseif($new_conf_type == 'Single Array')
{
  $new_conf_type = 'single-array';
  $new_conf_value = $_POST['new_conf_single_value'];
  if(dbInsert(array('config_name' => $new_conf_name, 'config_value' => $new_conf_value, 'config_default' => $new_conf_value, 'config_type' => $new_conf_type, 'config_desc' => $new_conf_desc, 'config_group' => '500_Custom Settings', 'config_sub_group' => '01_Custom settings', 'config_hidden' => '0', 'config_disabled' => '0'), 'config'))
  {
    $db_inserted = 1;
  }
}
elseif($new_conf_type == 'Standard Array' || $new_conf_type == 'Multi Array')
{
  if($new_conf_type == 'Standard Array')
  {
    $new_conf_type = 'array';
  }
  elseif($new_conf_type == 'Multi Array')
  {
    $new_conf_type = 'multi-array';
  }
  else
  {
    # $new_conf_type is invalid so clear values so we don't create any config
    $new_conf_value = '';
  }
  $new_conf_value = nl2br($_POST['new_conf_multi_value']);
  $values = explode('<br />',$new_conf_value);
  foreach ($values as $item)
  {
    $item = trim($item);
    if(dbInsert(array('config_name' => $new_conf_name, 'config_value' => $item, 'config_default' => $new_conf_value, 'config_type' => $new_conf_type, 'config_desc' => $new_conf_desc, 'config_group' => '500_Custom Settings', 'config_sub_group' => '01_Custom settings', 'config_hidden' => '0', 'config_disabled' => '0'), 'config'))
    {
      $db_inserted = 1;
    }
  }  
}
else
{
  echo('Bad config type!');
  $db_inserted = 0;
  exit;
}

if($db_inserted == 1)
{
  echo('Your new config item has been added');  
}
else
{
  echo('An error occurred adding your config item to the database');
}

?>
