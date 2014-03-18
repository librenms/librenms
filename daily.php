<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

include('includes/defaults.inc.php');
include('config.php');
include_once("includes/definitions.inc.php");
include("includes/functions.php");

$options = getopt("f:");

if ( $options['f'] === 'update') { echo $config['update']; }

if ( $options['f'] === 'syslog') {
  if ( is_numeric($config['syslog_purge'])) {
    if ( dbDelete('syslog', "timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)", array($config['syslog_purge'])) ) {
      echo 'Syslog cleared for entries over ' . $config['syslog_purge'] . " days\n";
    }
  }
}
if ( $options['f'] === 'eventlog') {
  if ( is_numeric($config['eventlog_purge'])) {
    if ( dbDelete('eventlog', "datetime < DATE_SUB(NOW(), INTERVAL ? DAY)", array($config['eventlog_purge'])) ) {
      echo 'Eventlog cleared for entries over ' . $config['eventlog_purge'] . " days\n";
    }
  }
}

?>
