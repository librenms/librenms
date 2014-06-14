<?php

/*
  Copyright (C) 2013 LibreNMS Contributors librenms-project@googlegroups.com
*/

// FUA

if (isset($_REQUEST['debug']))
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("includes/functions.inc.php");
include_once("../includes/functions.php");
include_once("includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if(file_exists('forms/'.$_POST['type'].'.inc.php'))
{
  include_once('forms/'.$_POST['type'].'.inc.php');
}

?>
