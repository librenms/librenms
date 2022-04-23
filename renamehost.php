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

// Remove a host and all related data from the system
if ($argv[1] && $argv[2]) {
    $host = strtolower($argv[1]);
    $id = getidbyname($host);
    if ($id) {
        $tohost = strtolower($argv[2]);
        $toid = getidbyname($tohost);
        if ($toid) {
            echo "NOT renamed. New hostname $tohost already exists.\n";
            exit(1);
        } else {
            $result = renamehost($id, $tohost, 'console');
            if ($result == '') {
                echo "Renamed $host\n";
                exit(0);
            } else {
                echo "NOT renamed: $result";
                exit(1);
            }
        }
    } else {
        echo "Host doesn't exist!\n";
        exit(1);
    }
} else {
    echo "Host Rename Tool\nUsage: ./renamehost.php <old hostname> <new hostname>\n";
    exit(1);
}
