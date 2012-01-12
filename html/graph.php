<?php

include_once("Net/IPv4.php");

if (isset($_GET['debug']))
{
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', E_ALL);
}
else
{
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/common.php");
include_once("../includes/dbFacile.php");
include_once("../includes/rewrites.php");
include_once("includes/functions.inc.php");
include_once("../includes/rrdtool.inc.php");
include_once("includes/authenticate.inc.php");

if (isset($config['allow_unauth_graphs']) && $config['allow_unauth_graphs'])
{
  $auth = "1"; ## hardcode auth for all with config function
} else {
  if (!$_SESSION['authenticated'])
  {
    graph_error("Session not authenticated");
    exit;
  }
}

include("includes/graphs/graph.inc.php");

?>
