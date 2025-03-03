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

// Remove a host and all related data from the system
if ($argv[1]) {
    $host = strtolower($argv[1]);
    $id = getidbyname($host);
    if ($id) {
        echo delete_device($id) . "\n";
    } else {
        echo "Host doesn't exist!\n";
    }
} else {
    echo "Host Removal Tool\nUsage: ./delhost.php <hostname>\n";
}
