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

$where = 1;

$sql = " FROM `alert_schedule` AS S WHERE $where";
if (!Auth::user()->hasGlobalRead()) {
    $param[] = Auth::id();
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`S`.`title` LIKE '%$searchPhrase%' OR `S`.`start` LIKE '%$searchPhrase%' OR `S`.`end` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`schedule_id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (isset($sort) && !empty($sort)) {
    list($sort_column, $sort_order) = explode(' ', trim($sort));
    if ($sort_column == 'status') {
        $sort_by_status = true;
        $sort = "`S`.`start`  $sort_order";
    }
} else {
    $sort = '`S`.`start` DESC ';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `S`.`schedule_id`, `S`.`recurring`, DATE_FORMAT(NOW(), '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS now, DATE_FORMAT(`S`.`start`, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS `start`, DATE_FORMAT(`S`.`end`, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS `end`,  DATE_FORMAT(`S`.`start_recurring_dt`, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "') AS `start_recurring_dt`, DATE_FORMAT(`S`.`end_recurring_dt`, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "') AS `end_recurring_dt`, `S`.`start_recurring_hr`, `S`.`end_recurring_hr`, `S`.`recurring_day`, `S`.`title` $sql";

foreach (dbFetchRows($sql, $param) as $schedule) {
    $status = 0;
    if ($schedule['recurring'] == 0) {
        $start  = strtotime($schedule['start']);
        $end    = strtotime($schedule['end']);
        $now    = strtotime($schedule['now']);
        if ($end < $now) {
            $status = 1;
        }

        if ($now >= $start && $now < $end) {
            $status = 2;
        }
    } else {
        $start = strtotime($schedule['start_recurring_dt']);
        $end = $schedule['end_recurring_dt'] != '' && $schedule['end_recurring_dt'] != '0000-00-00' ? strtotime($schedule['end_recurring_dt'].' '. $schedule['end_recurring_hr']) : 0;
        $now    = strtotime($schedule['now']);
        if ($end < $now && $end != 0) {
            $status =1;
        }
        if ($start <= $now && ($now <= $end || $end == 0)) {
            $status = 2;
        }
    }
    $table_rd = '';
    if ($schedule['recurring_day'] != '') {
        $array_days = array(
            0 => 'Su',
            1 => 'Mo',
            2 => 'Tu',
            3 => 'We',
            4 => 'Th',
            5 => 'Fr',
            6 => 'Sa'
        );
        $array_rd = explode(',', $schedule['recurring_day']);

        foreach ($array_rd as $key_rd => $val_rd) {
            if (array_key_exists($val_rd, $array_days)) {
                $table_rd .= $table_rd != '' ? ','. $array_days[$val_rd] : $array_days[$val_rd];
            }
        }
    }


    $response[] = array(
        'title'                  => $schedule['title'],
        'recurring'              => $schedule['recurring'] == 1 ? 'yes' : 'no',
        'start'                  => $schedule['recurring'] == 1 ? '' : $schedule['start'],
        'end'                    => $schedule['recurring'] == 1 ? '' : $schedule['end'],
        'start_recurring_dt'      => $schedule['recurring'] == 0 || $schedule['start_recurring_dt'] == '0000-00-00' ? '' : $schedule['start_recurring_dt'],
        'end_recurring_dt'         => $schedule['recurring'] == 0 || $schedule['end_recurring_dt'] == '0000-00-00' ? '' : $schedule['end_recurring_dt'],
        'start_recurring_hr'      => $schedule['recurring'] == 0 ? '' : substr($schedule['start_recurring_hr'], 0, 5),
        'end_recurring_hr'         => $schedule['recurring'] == 0 ? '' : substr($schedule['end_recurring_hr'], 0, 5),
        'recurring_day'             => $schedule['recurring'] == 0 ? '' : $table_rd,
        'id'                    => $schedule['schedule_id'],
        'status'                => $status,
    );
}

if (isset($sort_by_status) && $sort_by_status) {
    if ($sort_order == 'asc') {
        usort($response, function ($a, $b) {
            return $a['status'] - $b['status'];
        });
    } else {
        usort($response, function ($a, $b) {
            return $b['status'] - $a['status'];
        });
    }
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
