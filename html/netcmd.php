<?php

include_once("Net/IPv6.php");

function check_ip($ip)
{
  if ($ip == long2ip(ip2long($ip))) {
    return true;
  } else {
      return false;
  }
}

if($_GET['query']) {
  $ip = $_GET['query'];
  if(Net_IPv6::checkIPv6($ip)||check_ip($ip)) {
    switch ($_GET[cmd]) {
      case 'whois':
        $output = `/usr/bin/whois $_GET[query] | grep -v \%`;
        break;
      case 'ping':
        $output = `/bin/ping $_GET[query]`;
        break;
      case 'tracert':
        $output = `/usr/sbin/traceroute $_GET[query]`;
        break;
      case 'nmap':
        $output = `/usr/bin/nmap $_GET[query]`;
        break; 
    }
  }
}

$output = trim($output);
echo("<pre>$output</pre>");

?>
