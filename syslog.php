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

$processor = new LibreNMS\Syslog\Processor();

$s = fopen('php://stdin', 'r');
while ($line = fgets($s)) {
    // Log::channel('log_file')->critical($line); // uncomment to log input to librenms.log
    $processor->process($line);
}
