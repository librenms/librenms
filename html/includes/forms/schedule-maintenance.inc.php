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
    header('Content-type: text/plain');
    die('ERROR: You need to be admin');
}

$sub_type = $_POST['sub_type'];

if ($sub_type == 'new-maintenance') {
    // Defaults
    $status = 'error';
    $update = 0;

    $schedule_id = mres($_POST['schedule_id']);
    if ($schedule_id > 0) {
        $update = 1;
    }

    $title = mres($_POST['title']);
    $notes = mres($_POST['notes']);
    $start = mres($_POST['start']);
    $end   = mres($_POST['end']);
    $maps  = mres($_POST['maps']);

    if (empty($title)) {
        $message = 'Missing title<br />';
    }

    if (empty($start)) {
        $message .= 'Missing start date<br />';
    }

    if (empty($end)) {
        $message .= 'Missing end date<br />';
    }

    if (!is_array($_POST['maps'])) {
        $message .= 'Not mapped to any groups or devices<br />';
    }

    if (empty($message)) {
        if (empty($schedule_id)) {
            $schedule_id = dbInsert(array('start' => $start, 'end' => $end, 'title' => $title, 'notes' => $notes), 'alert_schedule');
        } else {
            dbUpdate(array('start' => $start, 'end' => $end, 'title' => $title, 'notes' => $notes), 'alert_schedule', '`schedule_id`=?', array($schedule_id));
        }

        if ($schedule_id > 0) {
            $items = array();
            $fail  = 0;

            if ($update == 1) {
                dbDelete('alert_schedule_items', '`schedule_id`=?', array($schedule_id));
            }

            foreach ($_POST['maps'] as $target) {
                $target = target_to_id($target);
                $item   = dbInsert(array('schedule_id' => $schedule_id, 'target' => $target), 'alert_schedule_items');
                if ($item > 0) {
                    array_push($items, $item);
                } else {
                    $fail = 1;
                }
            }

            if ($fail == 1 && $update == 0) {
                foreach ($items as $item) {
                    dbDelete('alert_schedule_items', '`item_id`=?', array($item));
                }

                dbDelete('alert_schedule', '`schedule_id`=?', array($schedule_id));
                $message = 'Issue scheduling maintenance';
            } else {
                $status  = 'ok';
                $message = 'Scheduling maintenance ok';
            }
        } else {
            $message = 'Issue scheduling maintenance';
        }//end if
    }//end if

    $response = array(
        'status'  => $status,
        'message' => $message,
    );
} elseif ($sub_type == 'parse-maintenance') {
    $schedule_id = mres($_POST['schedule_id']);
    $schedule    = dbFetchRow('SELECT * FROM `alert_schedule` WHERE `schedule_id`=?', array($schedule_id));
    $items       = array();
    foreach (dbFetchRows('SELECT `target` FROM `alert_schedule_items` WHERE `schedule_id`=?', array($schedule_id)) as $targets) {
        $targets = id_to_target($targets['target']);
        array_push($items, $targets);
    }

    $response = array(
        'start'   => $schedule['start'],
        'end'     => $schedule['end'],
        'title'   => $schedule['title'],
        'notes'   => $schedule['notes'],
        'targets' => $items,
    );
} elseif ($sub_type == 'del-maintenance') {
    $schedule_id = mres($_POST['del_schedule_id']);
    dbDelete('alert_schedule_items', '`schedule_id`=?', array($schedule_id));
    dbDelete('alert_schedule', '`schedule_id`=?', array($schedule_id));
    $status   = 'ok';
    $message  = 'Maintenance schedule has been removed';
    $response = array(
        'status'  => $status,
        'message' => $message,
    );
}//end if
header('Content-type: application/json');
echo _json_encode($response);
