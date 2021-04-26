<?php

use App\Models\UserPref;
use Illuminate\Support\Str;

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

if (! Auth::user()->hasGlobalAdmin()) {
    header('Content-type: text/plain');
    exit('ERROR: You need to be admin');
}

$sub_type = $_POST['sub_type'];

if ($sub_type == 'new-maintenance') {
    // Defaults
    $status = 'error';
    $update = 0;
    $message = '';

    $schedule_id = $_POST['schedule_id'];
    if ($schedule_id > 0) {
        $update = 1;
    }

    $title = $_POST['title'];
    $notes = $_POST['notes'];
    $recurring = $_POST['recurring'] ? 1 : 0;
    $start_recurring_dt = $_POST['start_recurring_dt'];
    $end_recurring_dt = $_POST['end_recurring_dt'];
    $start_recurring_hr = $_POST['start_recurring_hr'];
    $end_recurring_hr = $_POST['end_recurring_hr'];
    $recurring_day = $_POST['recurring_day'];
    $start = $_POST['start'];
    [$duration_hour, $duration_min] = explode(':', $_POST['duration']);
    $end = $_POST['end'];
    $maps = $_POST['maps'];

    if (isset($duration_hour) && isset($duration_min)) {
        $end = date('Y-m-d H:i:00', strtotime('+' . intval($duration_hour) . ' hour ' . intval($duration_min) . ' minute', strtotime($start)));
    }

    if (empty($title)) {
        $message = 'Missing title<br />';
    }

    if (! in_array($recurring, [0, 1])) {
        $message .= 'Missing recurring choice<br />';
    }

    // check values if recurring is set to yes
    $recurring_day = null;
    if ($recurring == 1) {
        if (empty($start_recurring_dt)) {
            $message .= 'Missing start recurring date<br />';
        } else {
            // check if date is correct
            [$ysrd, $msrd, $dsrd] = explode('-', $start_recurring_dt);
            if (! checkdate($msrd, $dsrd, $ysrd)) {
                $message .= 'Please check start recurring date<br />';
            }
        }
        // end recurring dt not mandatory.. but if set, check if correct
        if (! empty($end_recurring_dt) && $end_recurring_dt != '0000-00-00' && $end_recurring_dt != '') {
            [$yerd, $merd, $derd] = explode('-', $end_recurring_dt);
            if (! checkdate($merd, $derd, $yerd)) {
                $message .= 'Please check end recurring date<br />';
            }
        } else {
            $end_recurring_dt = '9000-09-09';
        }

        if (empty($start_recurring_hr)) {
            $message .= 'Missing start recurring hour<br />';
        }

        if (empty($end_recurring_hr)) {
            $message .= 'Missing end recurring hour<br />';
        }

        if (isset($_POST['recurring_day']) && is_array($_POST['recurring_day']) && ! empty($_POST['recurring_day'])) {
            $recurring_day = $_POST['recurring_day'];
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
    }

    if (! is_array($_POST['maps'])) {
        $message .= 'Not mapped to any groups or devices<br />';
    }

    if (empty($message)) {
        $alert_schedule = \App\Models\AlertSchedule::findOrNew($schedule_id);
        $alert_schedule->title = $title;
        $alert_schedule->notes = $notes;
        $alert_schedule->recurring = $recurring;
        $alert_schedule->start = $start;
        $alert_schedule->end = $end;

        if ($recurring) {
            $alert_schedule->start_recurring_dt = $start_recurring_dt;
            $alert_schedule->start_recurring_hr = $start_recurring_hr;
            $alert_schedule->end_recurring_dt = $end_recurring_dt;
            $alert_schedule->end_recurring_hr = $end_recurring_hr;
            $alert_schedule->recurring_day = $recurring_day;
        }
        $alert_schedule->save();

        if ($alert_schedule->schedule_id > 0) {
            $items = [];
            $fail = 0;

            if ($update == 1) {
                dbDelete('alert_schedulables', '`schedule_id`=?', [$alert_schedule->schedule_id]);
            }

            foreach ($_POST['maps'] as $target) {
                $type = 'device';
                if (Str::startsWith($target, 'l')) {
                    $type = 'location';
                    $target = substr($target, 1);
                } elseif (Str::startsWith($target, 'g')) {
                    $type = 'device_group';
                    $target = substr($target, 1);
                }

                $item = dbInsert(['schedule_id' => $alert_schedule->schedule_id, 'alert_schedulable_type' => $type, 'alert_schedulable_id' => $target], 'alert_schedulables');
                if ($notes && $type = 'device' && UserPref::getPref(Auth::user(), 'add_schedule_note_to_device')) {
                    $device_notes = dbFetchCell('SELECT `notes` FROM `devices` WHERE `device_id` = ?;', [$target]);
                    $device_notes .= ((empty($device_notes)) ? '' : PHP_EOL) . date('Y-m-d H:i') . ' Alerts delayed: ' . $notes;
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
                    dbDelete('alert_schedulables', '`item_id`=?', [$item]);
                }

                dbDelete('alert_schedule', '`schedule_id`=?', [$alert_schedule->schedule_id]);
                $message = 'Issue scheduling maintenance';
            } else {
                $status = 'ok';
                $message = 'Scheduling maintenance ok';
            }
        } else {
            $message = 'Issue scheduling maintenance';
        }//end if
    }//end if

    $response = [
        'status'  => $status,
        'message' => $message,
    ];
} elseif ($sub_type == 'parse-maintenance') {
    $alert_schedule = \App\Models\AlertSchedule::findOrFail($_POST['schedule_id']);
    $items = [];

    foreach (dbFetchRows('SELECT `alert_schedulable_type`, `alert_schedulable_id` FROM `alert_schedulables` WHERE `schedule_id`=?', [$alert_schedule->schedule_id]) as $target) {
        $id = $target['alert_schedulable_id'];
        if ($target['alert_schedulable_type'] == 'location') {
            $text = dbFetchCell('SELECT location FROM locations WHERE id = ?', [$id]);
            $id = 'l' . $id;
        } elseif ($target['alert_schedulable_type'] == 'device_group') {
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

    $response = $alert_schedule->toArray();
    $response['recurring_day'] = $alert_schedule->getOriginal('recurring_day');
    $response['targets'] = $items;
} elseif ($sub_type == 'del-maintenance') {
    $schedule_id = $_POST['del_schedule_id'];
    dbDelete('alert_schedule', '`schedule_id`=?', [$schedule_id]);
    dbDelete('alert_schedulables', '`schedule_id`=?', [$schedule_id]);
    $status = 'ok';
    $message = 'Maintenance schedule has been removed';
    $response = [
        'status'  => $status,
        'message' => $message,
    ];
}//end if
header('Content-type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
