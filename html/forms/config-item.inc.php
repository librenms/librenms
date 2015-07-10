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
    $response = array('status'=>'error','message'=>'Need to be admin');
    echo _json_encode($response);
    exit;
}

$action = mres($_POST['action']);
$config_group = mres($_POST['config_group']);
$config_sub_group = mres($_POST['config_sub_group']);
$config_name = mres($_POST['config_name']);
$config_value = mres($_POST['config_value']);
$config_extra = mres($_POST['config_extra']);
$config_room_id = mres($_POST['config_room_id']);
$config_from = mres($_POST['config_from']);
$config_userkey = mres($_POST['config_userkey']);
$status = 'error';
$message = 'Error with config';

if ($action == 'remove' || $action == 'remove-slack' || $action == 'remove-hipchat' || $action == 'remove-pushover') {
    $config_id = mres($_POST['config_id']);
    if (empty($config_id)) {
        $message = 'No config id passed';
    } else {
        if (dbDelete('config', '`config_id`=?', array($config_id))) {
            if ($action == 'remove-slack') {
                dbDelete('config', "`config_name` LIKE 'alert.transports.slack.$config_id.%'");
            } elseif ($action == 'remove-hipchat') {
                dbDelete('config', "`config_name` LIKE 'alert.transports.hipchat.$config_id.%'");
            } elseif ($action == 'remove-pushover') {
                dbDelete('config', "`config_name` LIKE 'alert.transports.pushover.$config_id.%'");
            }
            $status = 'ok';
            $message = 'Config item removed';
        } else {
            $message = 'General error, could not remove config';
        }
    }
} elseif ($action == 'add-slack') {
    if (empty($config_value)) {
        $message = 'No Slack url provided';
    } else {
        $config_id = dbInsert(array('config_name' => 'alert.transports.slack.', 'config_value' => $config_value, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_value, 'config_descr'=>'Slack Transport'), 'config');
        if ($config_id > 0) {
            dbUpdate(array('config_name' => 'alert.transports.slack.'.$config_id.'.url'), 'config', 'config_id=?', array($config_id));
            $status = 'ok';
            $message = 'Config item created';
            $extras = explode('\n',$config_extra);
            foreach ($extras as $option) {
                list($k,$v) = explode("=", $option,2);
                if (!empty($k) || !empty($v)) {
                    dbInsert(array('config_name' => 'alert.transports.slack.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$v, 'config_descr'=>'Slack Transport'), 'config');
                }
            }
        } else {
            $message = 'Could not create config item';
        }
    }
} elseif ($action == 'add-hipchat') {
    if (empty($config_value) || empty($config_room_id) || empty($config_from)) {
        $message = 'No hipchat url, room id or from provided';
    } else {
        $config_id = dbInsert(array('config_name' => 'alert.transports.hipchat.', 'config_value' => $config_value, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_value, 'config_descr'=>'Hipchat Transport'), 'config');
        if ($config_id > 0) {
            dbUpdate(array('config_name' => 'alert.transports.hipchat.'.$config_id.'.url'), 'config', 'config_id=?', array($config_id));
            $additional_id['room_id'] = dbInsert(array('config_name' => 'alert.transports.hipchat.'.$config_id.'.room_id', 'config_value' => $config_room_id, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_room_id, 'config_descr'=>'Hipchat URL'), 'config');
            $additional_id['from'] = dbInsert(array('config_name' => 'alert.transports.hipchat.'.$config_id.'.from', 'config_value' => $config_from, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_from, 'config_descr'=>'Hipchat From'), 'config');
            $status = 'ok';
            $message = 'Config item created';
            $extras = explode('\n',$config_extra);
            foreach ($extras as $option) {
                list($k,$v) = explode("=", $option,2);
                if (!empty($k) || !empty($v)) {
                    dbInsert(array('config_name' => 'alert.transports.hipchat.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$v, 'config_descr'=>'Hipchat '.$v), 'config');
                }
            }
        } else {
            $message = 'Could not create config item';
        }
    }
} elseif ($action == 'add-pushover') {
    if (empty($config_value) || empty($config_userkey)) {
        $message = 'No pushover appkey or userkey provided';
    } else {
        $config_id = dbInsert(array('config_name' => 'alert.transports.pushover.', 'config_value' => $config_value, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_value, 'config_descr'=>'Pushover Transport'), 'config');
        if ($config_id > 0) {
            dbUpdate(array('config_name' => 'alert.transports.pushover.'.$config_id.'.appkey'), 'config', 'config_id=?', array($config_id));
            $additional_id['userkey'] = dbInsert(array('config_name' => 'alert.transports.pushover.'.$config_id.'.userkey', 'config_value' => $config_userkey, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_userkey, 'config_descr'=>'Pushver Userkey'), 'config');
            $status = 'ok';
            $message = 'Config item created';
            $extras = explode('\n',$config_extra);
            foreach ($extras as $option) {
                list($k,$v) = explode("=", $option,2);
                if (!empty($k) || !empty($v)) {
                    dbInsert(array('config_name' => 'alert.transports.pushover.'.$config_id.'.'.$k, 'config_value' => $v, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$v, 'config_descr'=>'Pushover '.$v), 'config');
                }
            }
        } else {
            $message = 'Could not create config item';
        }
    }
} else {

    if (empty($config_group) || empty($config_sub_group) || empty($config_name) || empty($config_value)) {
        $message = 'Missing config name or value';
    } else {
        $config_id = dbInsert(array('config_name' => $config_name, 'config_value' => $config_value, 'config_group' => $config_group, 'config_sub_group' => $config_sub_group, 'config_default'=>$config_value, 'config_descr'=>'API Transport'), 'config');
        if ($config_id > 0) {
            dbUpdate(array('config_name'=>$config_name.$config_id),'config','config_id=?',array($config_id));
            $status = 'ok';
            $message = 'Config item created';
        } else {
            $message = 'Could not create config item';
        }
    }
}

$response = array('status'=>$status,'message'=>$message, 'config_id'=>$config_id, 'additional_id'=>$additional_id);
echo _json_encode($response);
