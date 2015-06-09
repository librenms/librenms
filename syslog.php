#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage syslog
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include_once("includes/syslog.php");
include_once("includes/dbFacile.php");
include_once("includes/common.php");
include_once("includes/functions.php");

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
