#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage cli
 * @author     LibreNMS Group <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Contributors
 *
 */

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/syslog.php");
include("includes/dbFacile.php");
include("includes/common.php");
include("includes/functions.php");

$i = "1";

$s = fopen('php://stdin','r');
while ($line = fgets($s))
{
  #logfile($line);
  list($entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']) = explode("||", trim($line));
  process_syslog($entry, 1);
  unset($entry); unset($line);
  $i++;
}

?>
