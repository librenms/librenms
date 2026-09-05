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

// one processor for the life of this process, so its caches survive between messages
$processor = new LibreNMS\Syslog\Processor();

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    // Log::channel('log_file')->critical($line); // uncomment to log input to librenms.log

    $fields = explode('||', trim($line));
    if (count($fields) === 8) {
        $processor->process(array_combine($keys, $fields));
    }

    unset($line, $fields);
}
