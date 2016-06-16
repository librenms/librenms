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

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

require_once '../includes/device-groups.inc.php';

$pattern  = $_POST['patterns'];
$group_id = $_POST['group_id'];
$name     = mres($_POST['name']);
$desc     = mres($_POST['desc']);

if (is_array($pattern)) {
    $pattern = implode(' ', $pattern);
}
else if (!empty($_POST['pattern']) && !empty($_POST['condition']) && !empty($_POST['value'])) {
    $pattern = '%'.$_POST['pattern'].' '.$_POST['condition'].' ';
    if (is_numeric($_POST['value'])) {
        $pattern .= $_POST['value'];
    }
    else {
        $pattern .= '"'.$_POST['value'].'"';
    }
}

if (empty($pattern)) {
    $update_message = 'ERROR: No group was generated';
}
else if (is_numeric($group_id) && $group_id > 0) {
    if (EditDeviceGroup($group_id, $name, $desc, $pattern)) {
        $update_message = "Edited Group: <i>$name: $pattern</i>";
    }
    else {
        $update_message = 'ERROR: Failed to edit Group: <i>'.$pattern.'</i>';
    }
}
else {
    if (AddDeviceGroup($name, $desc, $pattern)) {
        $update_message = "Added Group: <i>$name: $pattern</i>";
    }
    else {
        $update_message = 'ERROR: Failed to add Group: <i>'.$pattern.'</i>';
    }
}

echo $update_message;
