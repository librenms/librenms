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
 * @copyright  (C) 2006 - 2011 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

include("includes/defaults.inc.php");
include("config.php");
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
