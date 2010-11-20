<?php

if ($_GET['debug']) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

include("../includes/defaults.inc.php");
include("../config.php");
include("includes/functions.inc.php");
include("../includes/common.php");
include("../includes/rewrites.php");
include("includes/authenticate.inc.php");


if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if (is_numeric($_GET['device_id']))
{
  $ports = mysql_query("SELECT * FROM ports WHERE device_id = '".$_GET['device_id']."'");
  while($interface = mysql_fetch_array($ports)) 
  {
    echo("obj.options[obj.options.length] = new Option('".$interface['ifDescr']." - ".$interface['ifAlias']."','".$interface['interface_id']."');\n");
  }     
}

?> 
