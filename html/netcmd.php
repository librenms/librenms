<?php

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);


if($_GET[debug]) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
}

include("../config.php");
include("../includes/functions.php");
include("includes/authenticate.inc");

if($_GET['query'] && $_GET['cmd']) {
  $host = $_GET['query'];
  if(Net_IPv6::checkIPv6($host)||Net_IPv4::validateip($host)||preg_match("/^[a-zA-Z0-9.]*$/", $host)) {
    switch ($_GET['cmd']) {
      case 'whois':
        $output = `/usr/bin/whois $host | grep -v \%`;
        break;
      case 'ping':
        $output = `/bin/ping $host`;
        break;
      case 'tracert':
        $output = `/usr/sbin/traceroute $host`;
        break;
      case 'nmap':
        $output = `/usr/bin/nmap $host`;
        break; 
    }
  }
}

$output = trim($output);
echo("<pre>$output</pre>");

?>
