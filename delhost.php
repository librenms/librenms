#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

chdir(dirname($argv[0]));

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

// Remove a host and all related data from the system
if ($argv[1]) {
    $host = strtolower($argv[1]);
    $id   = getidbyname($host);
    if ($id) {
        echo delete_device($id)."\n";
    } else {
        echo "Host doesn't exist!\n";
    }
} else {
    echo "Host Removal Tool\nUsage: ./delhost.php <hostname>\n";
}
