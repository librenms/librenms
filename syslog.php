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

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    //logfile($line);

    $fields = explode('||', trim($line));
    if (count($fields) === 8) {
        process_syslog(array_combine($keys, $fields), 1);
    }

    unset($line, $fields);
}
