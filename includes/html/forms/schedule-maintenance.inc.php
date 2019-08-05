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

if (!Auth::user()->hasGlobalAdmin()) {
    header('Content-type: text/plain');
    die('ERROR: You need to be admin');
}

$sub_type = $_POST['sub_type'];

if ($sub_type == 'new-maintenance') {
    // Defaults
    $status = 'error';
    $update = 0;
    $message = '';

    $schedule_id = mres($_POST['schedule_id']);
    if ($schedule_id > 0) {
        $update = 1;
    }

    $title = mres($_POST['title']);
    $notes = mres($_POST['notes']);
    $recurring = mres($_POST['recurring']);
    $start_recurring_dt = mres($_POST['start_recurring_dt']);
    $end_recurring_dt = mres($_POST['end_recurring_dt']);
    $start_recurring_hr = mres($_POST['start_recurring_hr']);
    $end_recurring_hr = mres($_POST['end_recurring_hr']);
    $recurring_day = mres($_POST['recurring_day']);
    $start = mres($_POST['start']);
    $end   = mres($_POST['end']);
    $maps  = mres($_POST['maps']);

    if (empty($title)) {
        $message = 'Missing title<br />';
    }

    if (!in_array($recurring, array(0,1))) {
        $message .= 'Missing recurring choice<br />';
    }

    // check values if recurring is set to yes
    if ($recurring == 1) {
        if (empty($start_recurring_dt)) {
            $message .= 'Missing start recurring date<br />';
        } else {
            // check if date is correct
            list($ysrd, $msrd, $dsrd) = explode('-', $start_recurring_dt);
            if (!checkdate($msrd, $dsrd, $ysrd)) {
                $message .= 'Please check start recurring date<br />';
            }
        }
        // end recurring dt not mandatory.. but if set, check if correct
        if (!empty($end_recurring_dt) && $end_recurring_dt != '0000-00-00' && $end_recurring_dt != '') {
            list($yerd, $merd, $derd) = explode('-', $end_recurring_dt);
            if (!checkdate($merd, $derd, $yerd)) {
                $message .= 'Please check end recurring date<br />';
            }
        } else {
            $end_recurring_dt = null;
        }

        if (empty($start_recurring_hr)) {
            $message .= 'Missing start recurring hour<br />';
        }

        if (empty($end_recurring_hr)) {
            $message .= 'Missing end recurring hour<br />';
        }

        if (isset($_POST['recurring_day']) && is_array($_POST['recurring_day']) && !empty($_POST['recurring_day'])) {
            $recurring_day = implode(',', $_POST['recurring_day']);
        } else {
            $recurring_day = null;
        }

        // recurring = 1 => empty no reccurency values to be sure.
        $start = '0000-00-00 00:00:00';
        $end = '0000-00-00 00:00:00';
    } else {
        if (empty($start)) {
            $message .= 'Missing start date<br />';
        }

        if (empty($end)) {
            $message .= 'Missing end date<br />';
        }

        // recurring = 0 => empty no reccurency values to be sure.
        $start_recurring_dt = '1970-01-02';
        $end_recurring_dt = '1970-01-02';
        $start_recurring_hr = '00:00:00';
        $end_recurring_hr = '00:00:00';
        $recurring_day = null;
    }

    if (!is_array($_POST['maps'])) {
        $message .= 'Not mapped to any groups or devices<br />';
    }

    if (empty($message)) {
        if (empty($schedule_id)) {
            $schedule_id = dbInsert(array('recurring' => $recurring, 'start' => $start, 'end' => $end, 'start_recurring_dt' => $start_recurring_dt, 'end_recurring_dt' => $end_recurring_dt, 'start_recurring_hr' => $start_recurring_hr, 'end_recurring_hr' => $end_recurring_hr, 'recurring_day' => $recurring_day, 'title' => $title, 'notes' => $notes), 'alert_schedule');
        } else {
            dbUpdate(array('recurring' => $recurring, 'start' => $start, 'end' => $end, 'start_recurring_dt' => $start_recurring_dt, 'end_recurring_dt' => $end_recurring_dt, 'start_recurring_hr' => $start_recurring_hr, 'end_recurring_hr' => $end_recurring_hr, 'recurring_day' => $recurring_day, 'title' => $title, 'notes' => $notes), 'alert_schedule', '`schedule_id`=?', array($schedule_id));
        }

        if ($schedule_id > 0) {
            $items = array();
            $fail  = 0;

            if ($update == 1) {
                dbDelete('alert_schedulables', '`schedule_id`=?', array($schedule_id));
            }

            foreach ($_POST['maps'] as $target) {
                $type = 'device';
                if (starts_with($target, 'g')) {
                    $type = 'device_group';
                    $target = substr($target, 1);
                }

                $item = dbInsert(['schedule_id' => $schedule_id, 'alert_schedulable_type' => $type, 'alert_schedulable_id' => $target], 'alert_schedulables');
                if ($notes && $type = 'device' && get_user_pref('add_schedule_note_to_device', false)) {
                    $device_notes = dbFetchCell('SELECT `notes` FROM `devices` WHERE `device_id` = ?;', [$target]);
                    $device_notes.= ((empty($device_notes)) ? '' : PHP_EOL) . date("Y-m-d H:i") . ' Alerts delayed: ' . $notes;
                    dbUpdate(['notes' => $device_notes], 'devices', '`device_id` = ?', [$target]);
                }
                if ($item > 0) {
                    array_push($items, $item);
                } else {
                    $fail = 1;
                }
            }

            if ($fail == 1 && $update == 0) {
                foreach ($items as $item) {
                    dbDelete('alert_schedulables', '`item_id`=?', array($item));
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
    $items       = [];
    foreach (dbFetchRows('SELECT `alert_schedulable_type`, `alert_schedulable_id` FROM `alert_schedulables` WHERE `schedule_id`=?', [$schedule_id]) as $target) {
        $id = $target['alert_schedulable_id'];
        if ($target['alert_schedulable_type'] == 'device_group') {
            $text = dbFetchCell('SELECT name FROM device_groups WHERE id = ?', [$id]);
            $id = 'g' . $id;
        } else {
            $text = dbFetchCell('SELECT hostname FROM devices WHERE device_id = ?', [$id]);
        }
        $items[] = [
            'id' => $id,
            'text' => $text,
        ];
    }

    $response = array(
        'start'                   => $schedule['start'],
        'end'                     => $schedule['end'],
        'title'                   => $schedule['title'],
        'notes'                   => $schedule['notes'],
        'recurring'               => $schedule['recurring'],
        'start_recurring_dt'    => ($schedule['start_recurring_dt'] != '0000-00-00' ? $schedule['start_recurring_dt']: '1970-01-02 00:00:01'),
        'end_recurring_dt'      => ($schedule['end_recurring_dt']!= '0000-00-00' ? $schedule['end_recurring_dt'] : '1970-01-02 00:00:01'),
        'start_recurring_hr'    => substr($schedule['start_recurring_hr'], 0, 5),
        'end_recurring_hr'       => substr($schedule['end_recurring_hr'], 0, 5),
        'recurring_day'           => $schedule['recurring_day'],
        'targets'                => $items,
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
