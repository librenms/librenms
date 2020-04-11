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

$query = \App\Models\AlertSchedule::query()
    ->when($searchPhrase, function ($query, $searchPhrase) {
        $query->where(function ($query) use ($searchPhrase) {
            $query->where('title', 'like', "%$searchPhrase%")
                ->orWhere('start', 'like', "%$searchPhrase%")
                ->orWhere('end', 'like', "%$searchPhrase%");
        });
    });

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

$now = Carbon::now();

//header('Caontent-Type: text/html');

$schedules = $query->get()->map(function ($schedule) use ($now) {
    /** @var \App\Models\AlertSchedule $schedule */
    $data = $schedule->only(['title', 'notes']);
    $data['id'] = $schedule->schedule_id;
    $data['start'] = $schedule->recurring ? '' : $schedule->start->toDateTimeString('minutes');
    $data['end'] = $schedule->recurring ? '' : $schedule->end->toDateTimeString('minutes');
    $data['start_recurring_dt'] = $schedule->recurring ? $schedule->start_recurring_dt : '';
    $data['start_recurring_hr'] = $schedule->recurring ? $schedule->start_recurring_hr : '';
    $data['end_recurring_dt'] = $schedule->recurring ? $schedule->end_recurring_dt : '';
    $data['end_recurring_hr'] = $schedule->recurring ? $schedule->end_recurring_hr : '';
    $data['recurring'] = $schedule->recurring ? __('Yes') : __('No');
    $data['recurring_day'] = $schedule->recurring ? implode(',', $schedule->recurring_day) : '';
    $data['status'] = $schedule->status;

    return $data;
});

echo json_encode([
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $schedules,
    'total'    => $total,
]);
