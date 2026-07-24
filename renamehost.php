#!/usr/bin/env php
<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage cli
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

$init_modules = [];
require __DIR__ . '/includes/init.php';

use App\Facades\DeviceCache;
use App\Models\Device;

// Remove a host and all related data from the system
if ($argv[1] && $argv[2]) {
    $hostname = $argv[1];
    $device = DeviceCache::get($hostname);
    $new_hostname = $argv[2];

    if (! $device->exists) {
        echo "Existing device not found\n";
        exit(1);
    } else {
        try {
            $device->hostname = $new_hostname;
            $device->save();
            echo "Renamed $hostname\n";
            exit(0);
        } catch (\Throwable) {
            echo "Device failed to be renamed\n";
            exit(1);
        }
    }
} else {
    echo "Host Rename Tool\nUsage: ./renamehost.php <old hostname> <new hostname>\n";
    exit(1);
}
