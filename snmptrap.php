#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage snmptraps
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @copyright  (C) 2018 LibreNMS
 * Adapted from old snmptrap.php handler
 */

chdir(__DIR__); // cwd to the directory containing this script

$init_modules = array();
require __DIR__ . '/includes/init.php';

$options = getopt('d::');

if (set_debug(isset($options['d']))) {
    echo "DEBUG!\n";
}

// Creates an array with trap info
while ($f = fgets(STDIN)) {
    $entry[] = $f;
}

snmptrap($entry);
