<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

if ($_GET[debug])
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
}

include("../includes/defaults.inc.php");
include("../config.php");
include_once("../includes/definitions.inc.php");
include("includes/functions.inc.php");
include("../includes/functions.php");
include("includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if ($_GET['query'] && $_GET['cmd'])
{
  $host = $_GET['query'];
  if (Net_IPv6::checkIPv6($host)||Net_IPv4::validateip($host)||preg_match("/^[a-zA-Z0-9.-]*$/", $host))
  {
    switch ($_GET['cmd'])
    {
      case 'whois':
        $cmd = $config['whois'] . " $host | grep -v \%";
        break;
      case 'ping':
        $cmd = $config['ping'] . " -c 5 $host";
        break;
      case 'tracert':
        $cmd = $config['mtr'] . " -r -c 5 $host";
        break;
      case 'nmap':
        if ($_SESSION['userlevel'] != '10')
        {
            echo("insufficient privileges");
        } else {
            $cmd = $config['nmap'] . " $host";
        }
        break;
    }

    if (!empty($cmd))
    {
        $output = `$cmd`;
    }
  }
}

$output = trim($output);
echo("<pre>$output</pre>");

?>
