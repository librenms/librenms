<?php

function utime()
{
  $time = explode(" ", microtime());
  $usec = (double)$time[0];
  $sec = (double)$time[1];
  return $sec + $usec;
}

$start = utime();

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

$end = utime(); $run = $end - $start;;

if($debug) { echo("<br />Runtime ".$run." secs"); 

    echo('<br />MySQL: Cell    '.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,3).'s'.
                      ' Row    '.($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,3).'s'.
                      ' Rows   '.($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,3).'s'.
                      ' Column '.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,3).'s');


}




?>
