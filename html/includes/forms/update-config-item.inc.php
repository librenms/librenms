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

$config_id   = mres($_POST['config_id']);
$action      = mres($_POST['action']);
$config_type = mres($_POST['config_type']);

$status = 'error';

if (!is_numeric($config_id)) {
    $message = 'ERROR: No config item';
}
elseif ($action == 'update-textarea') {
    $extras = explode(PHP_EOL, $_POST['config_value']);
    $x=0;
    foreach ($extras as $option) {
        list($k,$v) = explode('=', $option, 2);
        if (!empty($k) || !empty($v)) {
            if ($config_type == 'slack') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.slack.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'Slack Transport'), 'config');
            }
            elseif ($config_type == 'hipchat') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.hipchat.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'Hipchat Transport'), 'config');
            }
            elseif ($config_type == 'pushover') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.pushover.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'Pushover Transport'), 'config');
            }
            elseif ($config_type == 'boxcar') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.boxcar.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'Boxcar Transport'), 'config');
            }
            elseif ($config_type == 'clickatell') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.clickatell.to.'.$x, 'config_value' => $k, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'Clickatell Transport'), 'config');
                $x++;
            }
            elseif ($config_type == 'playsms') {
                $db_id[] = dbInsert(array('config_name' => 'alert.transports.playsms.to.'.$x, 'config_value' => $k, 'config_group' => 'alerting', 'config_sub_group' => 'transports', 'config_default' => $v, 'config_descr' => 'PlaySMS Transport'), 'config');
                $x++;
            }
        }
    }

    $db_inserts = implode(',', $db_id);
    if (!empty($db_inserts) || empty($_POST['config_value'])) {
        if (empty($_POST['config_value'])) {
            $db_inserts = 0;
        }

        if ($config_type == 'slack') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.slack.$config_id.%' AND `config_name` != 'alert.transports.slack.$config_id.url' AND `config_id` NOT IN ($db_inserts))");
        }
        elseif ($config_type == 'hipchat') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.hipchat.$config_id.%' AND (`config_name` != 'alert.transports.hipchat.$config_id.url' AND `config_name` != 'alert.transports.hipchat.$config_id.room_id' AND `config_name` != 'alert.transports.hipchat.$config_id.from') AND `config_id` NOT IN ($db_inserts))");
        }
        elseif ($config_type == 'pushover') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.pushover.$config_id.%' AND (`config_name` != 'alert.transports.pushover.$config_id.appkey' AND `config_name` != 'alert.transports.pushover.$config_id.userkey') AND `config_id` NOT IN ($db_inserts))");
        }
        elseif ($config_type == 'boxcar') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.boxcar.$config_id.%' AND (`config_name` != 'alert.transports.boxcar.$config_id.access_token' AND `config_name` != 'alert.transports.boxcar.$config_id.userkey') AND `config_id` NOT IN ($db_inserts))");
        }
        elseif ($config_type == 'clickatell') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.clickatell.to.%' AND `config_id` NOT IN ($db_inserts))");
        }
        elseif ($config_type == 'playsms') {
            dbDelete('config', "(`config_name` LIKE 'alert.transports.playsms.to.%' AND `config_id` NOT IN ($db_inserts))");
        }
    }

    $message = 'Config item has been updated:';
    $status  = 'ok';
}
else {
    $state  = mres($_POST['config_value']);
    $update = dbUpdate(array('config_value' => $state), 'config', '`config_id`=?', array($config_id));
    if (!empty($update) || $update == '0') {
        $message = 'Alert rule has been updated.';
        $status  = 'ok';
    }
    else {
        $message = 'ERROR: Alert rule has not been updated.';
    }
}//end if

$response = array(
    'status'  => $status,
    'message' => $message,
);
echo _json_encode($response);
