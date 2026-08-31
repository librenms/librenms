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

$keys = ['host', 'facility', 'priority', 'level', 'tag', 'timestamp', 'msg', 'program'];

// maps each sender to a device_id for the life of this process
$device_cache = [];

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    // Log::channel('log_file')->critical($line); // uncomment to log input to librenms.log

    $fields = explode('||', trim($line));
    if (count($fields) === 8) {
        process_syslog(array_combine($keys, $fields), 1, $device_cache);
    }

    unset($line, $fields);
}
