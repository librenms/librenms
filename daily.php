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

if ($options['f'] === 'update') {
    echo $config['update'];
}

if ($options['f'] === 'syslog') {
  if (is_numeric($config['syslog_purge'])) {
    $rows = dbFetchRow("SELECT MIN(seq) FROM syslog");
    while(TRUE) {
      $limit = dbFetchRow("SELECT seq FROM syslog WHERE seq >= ? ORDER BY seq LIMIT 1000,1", array($rows));
      if(empty($limit)) {
          break;
      }
      if (dbDelete('syslog', "seq >= ? AND seq < ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)", array($rows,$limit,$config['syslog_purge'])) > 0) {
        $rows = $limit;
        echo 'Syslog cleared for entries over ' . $config['syslog_purge'] . " days 1000 limit\n";
      } else {
          break;
      }
    }
    dbDelete('syslog', "seq >= ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)", array($rows,$config['syslog_purge']));
  }
}
if ($options['f'] === 'eventlog') {
  if (is_numeric($config['eventlog_purge'])) {
    if (dbDelete('eventlog', "datetime < DATE_SUB(NOW(), INTERVAL ? DAY)", array($config['eventlog_purge'])) ) {
      echo 'Eventlog cleared for entries over ' . $config['eventlog_purge'] . " days\n";
    }
  }
}
if ($options['f'] === 'authlog') {
    if (is_numeric($config['authlog_purge'])) {
        if (dbDelete('authlog', "datetime < DATE_SUB(NOW(), INTERVAL ? DAY)", array($config['authlog_purge'])) ) {
            echo 'Authlog cleared for entries over ' . $config['authlog_purge'] . " days\n";
        }
    }
}

?>
