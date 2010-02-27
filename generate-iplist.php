#!/usr/bin/php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

shell_exec("rm ips.txt && touch ips.txt");

$handle = fopen("ips.txt", "w+");

$query = mysql_query("SELECT * FROM `networks`");
while ($data = mysql_fetch_array($query)) {
  $cidr = $data['cidr'];
  list ($network, $bits) = split("/", $cidr); 
  if($bits != '32' && $bits != '32' && $bits > '22') {
    $broadcast = trim(shell_exec($config['ipcalc']." $cidr | grep Broadcast | cut -d\" \" -f 2"));
    $ip = ip2long($network) + '1';
    $end = ip2long($broadcast);
    while($ip < $end) {
      $ipdotted = long2ip($ip);
      if(mysql_result(mysql_query("SELECT count(id) FROM ipaddr WHERE addr = '$ipdotted'"),0) == '0' && match_network($config['nets'], $ipdotted)) {
	fputs($handle, $ipdotted . "\n");
      }
      $ip++;
    }
  }
}

`fping -t 100 -f ips.txt > ips-scanned.txt`;

?>
