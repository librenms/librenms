#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");
include("includes/cdp.php");

$device_query = mysql_query("SELECT id,hostname,community FROM `devices` WHERE `ignore` = '0' AND status = '1' AND `os` = 'IOS' ORDER BY `id` ASC");
while ($device = mysql_fetch_row($device_query)) {

  echo("Detecting CDP neighbours on $device[1]...\n");
  $snmp = new snmpCDP($device[1], $device[2]);
  $ports = $snmp->getports();
  $cdp = $snmp->explore_cdp($ports);

  foreach (array_keys($cdp) as $key) {
    $port = $ports[$key];
    $link = $cdp[$key];
    $loc_if[$key] = @mysql_result(mysql_query("SELECT `id` FROM `interfaces` WHERE host = '" . $device['id'] . "' AND `if` = '" . $port['desc'] . "'"), 0);
    echo( $key . "||" . $hostname . "||" . $loc_if[$key] . "||" . $port['desc'] . "||" . $link['host'] . "||" . $link['port'] . "\n" );
  }

}

?>
