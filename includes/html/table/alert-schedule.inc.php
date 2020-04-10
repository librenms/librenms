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

use Carbon\Carbon;

if (!Auth::user()->hasGlobalRead()) {
    return [
        'current'  => 0,
        'rowCount' => 0,
        'rows'     => [],
        'total'    => 0,
    ];
}

$query = \App\Models\AlertSchedule::query();

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $query->where(function ($query) use ($searchPhrase) {
        $query->where('title', 'like', "%$searchPhrase%")
            ->orWhere('start', 'like', "%$searchPhrase%")
            ->orWhere('end', 'like', "%$searchPhrase%");
    });
}

$total = $query->count();

if ($rowCount != -1) {
    $query->offset(($current * $rowCount) - $rowCount)
        ->limit($rowCount);
}

if (isset($sort) && !empty($sort)) {
    list($sort_column, $sort_order) = explode(' ', trim($sort));
    $columns = [
        'start_recurring_dt' => DB::raw('DATE(`start`)'),
        'start_recurring_ht' => DB::raw('TIME(`start`)'),
        'end_recurring_dt' => DB::raw('DATE(`end`)'),
        'end_recurring_ht' => DB::raw('TIME(`end`)'),
        'title' => 'title',
        'recurring' => 'recurring',
        'start' => 'start',
        'end' => 'end',
        'status' => DB::raw("end < '" . Carbon::now('UTC') ."'"), // only partition lapsed
    ];
    if (array_key_exists($sort_column, $columns)) {
        $query->orderBy($columns[$sort_column], $sort_order == 'asc' ? 'asc' : 'desc')->orderBy('title');
    }
} else {
    $query->orderBy('start')->orderBy('title');
}

$now = Carbon::now(config('app.timezone'));
$days = [
    'from' => ['1', '2', '3', '4', '5', '6', '7'],
    'to' => ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']
];

$schedules = $query->get()->map(function ($schedule) use ($now, $days) {
    $start = Carbon::parse($schedule->start, 'UTC')->tz(config('app.timezone'));
    $end   = Carbon::parse($schedule->end, 'UTC')->tz(config('app.timezone'));

    $status = $end < $now ? 1 : 0; // set or lapsed
    // check if current
    if ($now->between($start, $end) && (!$schedule->recurring || $now->between($start->toTimeString(), $end->toTimeString()))) {
        $status = 2;
    }

    return [
        'title'                  => $schedule->title,
        'recurring'              => $schedule->recurring ? 'yes' : 'no',
        'start'                  => $schedule->recurring ? '' : $start->toDateTimeString('minute'),
        'end'                    => $schedule->recurring ? '' : $end->toDateTimeString('minute'),
        'start_recurring_dt'     => $schedule->recurring == 0  ? '' : $start->toDateString(),
        'end_recurring_dt'       => $schedule->recurring == 0 || $end->year == 9000 ? '' : $end->toDateString(),
        'start_recurring_hr'     => $schedule->recurring == 0 ? '' : $start->toTimeString('minute'),
        'end_recurring_hr'       => $schedule->recurring == 0 ? '' : $end->toTimeString('minute'),
        'recurring_day'          => $schedule->recurring == 0 ? '' : str_replace($days['from'], $days['to'], $schedule->recurring_day),
        'id'                     => $schedule->schedule_id,
        'status'                 => $status,
    ];
});

echo json_encode([
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $schedules,
    'total'    => $total,
]);
