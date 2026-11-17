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

c_echo('%RWarning: delhost.php is deprecated!%n Use %9lnms device:remove%n instead.' . PHP_EOL . PHP_EOL);

// Remove a host and all related data from the system
if ($argv[1]) {
    $host = strtolower($argv[1]);
    $device = DeviceCache::get($host);
    if (! $device->exists) {
        echo "Host doesn't exist!\n";
    } elseif ($device->delete()) {
        echo "Removed device {$device->hostname}\n";
    } else {
        echo "Failed to remove device {$device->hostname}\n";
    }
} else {
    echo "Host Removal Tool\nUsage: ./delhost.php <hostname>\n";
}
