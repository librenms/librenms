#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */
$init_modules = [];
require __DIR__ . '/includes/init.php';

$i = '1';

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    //logfile($line);
    [$entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']] = explode('||', trim($line));
    process_syslog($entry, 1);
    unset($entry);
    unset($line);
    $i++;
}
