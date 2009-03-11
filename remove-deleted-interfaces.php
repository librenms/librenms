#!/usr/bin/php

### Purge deleted interfaces from the database

<?
include("config.php");
include("includes/functions.php");

$query = mysql_query("SELECT * FROM interfaces AS I, devices as D WHERE I.device_id = D.device_id AND I.deleted = '1'");

while($interface = mysql_fetch_array($query)) {

  mysql_query("DELETE from interfaces where interface_id = '" . $interface['interface_id'] . "'");
  mysql_query("DELETE from `adjacencies` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
  mysql_query("DELETE from `links` WHERE `src_if` = '" . $interface['interface_id'] . "'");
  mysql_query("DELETE from `links` WHERE `dst_if` = '" . $interface['interface_id'] . "'");
  mysql_query("DELETE from `ipaddr` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
  echo("Removed interface " . $interface['ifDescr'] . " from " . $interface['hostname'] . "\n");

}

?>
