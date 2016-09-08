#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage syslog
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require_once 'includes/syslog.php';
require_once 'includes/dbFacile.php';
require_once 'includes/common.php';
require_once 'includes/functions.php';

$i = "1";

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    #logfile($line);
    list($entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']) = explode("||", trim($line));
    process_syslog($entry, 1);
    unset($entry);
    unset($line);
    $i++;
}
