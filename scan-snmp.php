#!/usr/bin/php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$search = $argv[1] . "$";

$data = trim(`cat ips-scanned.txt | grep alive | cut -d" " -f 1 | egrep $search`);

foreach (explode("\n", $data) as $ip)
{
  $snmp = shell_exec("snmpget -t 0.2 -v2c -c ".$config['community']." $ip sysName.0");
  if (strstr($snmp, "STRING"))
  {
    $hostname = trim(str_replace("SNMPv2-MIB::sysName.0 = STRING: ","", $snmp));
    if (mysql_result(mysql_query("SELECT COUNT(device_id) FROM devices WHERE hostname = '$hostname'"),0) == '0')
    {
      if (gethostbyname($hostname) == gethostbyname($hostname.".".$config['mydomain'])) { $hostname = $hostname . ".".$config['mydomain']; }
      addHost($hostname, $community, 'v2c');
      echo("Adding $hostname \n");
    }
  }
}
?>
