#!/usr/bin/php 
<?
include("config.php");
include("includes/functions.php");

# Remove a host and all related data from the system

if($argv[1]) { 
  $host = strtolower($argv[1]);
  $id = getidbyname($host);
  mysql_query("DELETE FROM `devices` WHERE `id` = '$id'");
  mysql_query("DELETE FROM `interfaces` WHERE `host` = '$id'");
  `rm -f rrd/$host-*.rrd`;
  `./cleanup.php`;
  echo("Removed $host");
} else {
  echo("Need host to remove!\n\n");
}

?>
