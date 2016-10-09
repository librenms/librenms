<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

enable_debug();

require_once '../includes/defaults.inc.php';
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once 'includes/functions.inc.php';
require_once '../includes/functions.php';
require_once 'includes/authenticate.inc.php';

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

$new_conf_type = $_POST['new_conf_type'];
$new_conf_name = $_POST['new_conf_name'];
$new_conf_desc = $_POST['new_conf_desc'];

if (empty($new_conf_name)) {
    echo "You haven't specified a config name";
    exit;
} elseif (empty($new_conf_desc)) {
    echo "You haven't specified a config description";
    exit;
} elseif (empty($_POST['new_conf_single_value']) && empty($_POST['new_conf_multi_value'])) {
    echo "You haven't specified a config value";
    exit;
}

$db_inserted = '0';

if ($new_conf_type == 'Single') {
    $new_conf_type  = 'single';
    $new_conf_value = $_POST['new_conf_single_value'];
    $db_inserted    = add_config_item($new_conf_name, $new_conf_value, $new_conf_type, $new_conf_desc);
} elseif ($new_conf_type == 'Single Array') {
    $new_conf_type  = 'single-array';
    $new_conf_value = $_POST['new_conf_single_value'];
    $db_inserted    = add_config_item($new_conf_name, $new_conf_value, $new_conf_type, $new_conf_desc);
} elseif ($new_conf_type == 'Standard Array' || $new_conf_type == 'Multi Array') {
    if ($new_conf_type == 'Standard Array') {
        $new_conf_type = 'array';
    } elseif ($new_conf_type == 'Multi Array') {
        $new_conf_type = 'multi-array';
    } else {
        // $new_conf_type is invalid so clear values so we don't create any config
        $new_conf_value = '';
    }

    $new_conf_value = nl2br($_POST['new_conf_multi_value']);
    $values         = explode('<br />', $new_conf_value);
    foreach ($values as $item) {
        $new_conf_value  = trim($item);
        $db_inserted = add_config_item($new_conf_name, $new_conf_value, $new_conf_type, $new_conf_desc);
    }
} else {
    echo 'Bad config type!';
    $db_inserted = 0;
    exit;
}//end if

if ($db_inserted == 1) {
    echo 'Your new config item has been added';
} else {
    echo 'An error occurred adding your config item to the database';
}
