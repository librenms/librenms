#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

shell_exec("rm ips.txt && touch ips.txt");

$handle = fopen("ips.txt", "w+");

$query = mysql_query("SELECT * FROM `ipv4_networks`");
while ($data = mysql_fetch_array($query)) {
  $cidr = $data['ipv4_network'];
  list ($network, $bits) = preg_split("@\/@", $cidr); 
  if($bits != '32' && $bits != '32' && $bits > '22') {
    $broadcast = trim(shell_exec($config['ipcalc']." $cidr | grep Broadcast | cut -d\" \" -f 2"));
    $ip = ip2long($network) + '1';
    $end = ip2long($broadcast);
    while($ip < $end) {
      $ipdotted = long2ip($ip);
      if(mysql_result(mysql_query("SELECT count(ipv4_address_id) FROM ipv4_addresses WHERE ipv4_address = '$ipdotted'"),0) == '0' && match_network($config['nets'], $ipdotted)) {
	fputs($handle, $ipdotted . "\n");
      }
      $ip++;
    }
  }
}

`fping -t 100 -f ips.txt > ips-scanned.txt`;

?>
