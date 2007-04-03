<?php

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
$output = trim($output);
echo("<pre>$output</pre>");

?>
