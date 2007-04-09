#!/usr/bin/php 
<?
include("config.php");
include("includes/functions.php");

# Remove a host and all related data from the system

if($argv[1]) { 
  $host = strtolower($argv[1]);
  $id = getidbyname($host);
  if($id) {
    delHost($id);
    echo("Removed $host\n");
  } else {
    echo("Host doesn't exist!\n");
  }
} else {
    echo("Need host to remove!\n\n");
}

?>
