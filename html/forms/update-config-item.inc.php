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

if(is_admin() === false) {
    die('ERROR: You need to be admin');
}

$status = 'error';

if (!is_numeric($_POST['config_id'])) {
    $message = 'ERROR: No alert selected';
    exit;
} else {
    if($_POST['config_value'] === true) {
        $state = TRUE;
    } else {
        $state = FALSE;
    }
    $state = $_POST['config_value'];
    $update = dbUpdate(array('config_value' => $state), 'config', '`config_id`=?', array($_POST['config_id']));
    if(!empty($update) || $update == '0')
    {
        $message = 'Alert rule has been updated.';
        $status = 'ok';
    } else {
        $message = 'ERROR: Alert rule has not been updated.';
    }
}

$response = array('status'=>$status,'message'=>$message);
echo _json_encode($response);