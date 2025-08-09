<?php

/**
 * OutagesController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Facades\LibrenmsConfig;
use App\Models\DeviceOutage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;

class OutagesController extends TableController
{
    protected $model = DeviceOutage::class;

    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'to' => 'nullable|date',
            'from' => 'nullable|date',
            'status' => 'nullable|in:current,previous,all',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'device_id' => 'device',
        ];
    }

    protected function sortFields($request)
    {
        return ['going_down', 'up_again', 'device_id'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        $from_ts = $request->from ? Carbon::parse($request->from)->getTimestamp() : null;
        $to_ts = $request->to ? Carbon::parse($request->to)->getTimestamp() : null;

        return DeviceOutage::hasAccess($request->user())
            ->with('device')
            ->when($from_ts || $to_ts, function ($query) use ($from_ts, $to_ts) {
                $query->where(function ($q) use ($from_ts, $to_ts) {
                    // Outage starts within range
                    $this->applyDateRangeCondition($q, 'going_down', $from_ts, $to_ts);

                    // OR outage ends within range (if it has ended)
                    $q->orWhere(function ($subQuery) use ($from_ts, $to_ts) {
                        $subQuery->whereNotNull('up_again')
                            ->where('up_again', '>', 0);
                        $this->applyDateRangeCondition($subQuery, 'up_again', $from_ts, $to_ts);
                    });
                });
            })
            ->when($request->status === 'current', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('up_again')
                      ->orWhere('up_again', 0);
                });
            })
            ->when($request->status === 'previous', function ($query) {
                $query->where('up_again', '>', 0);
            });
    }

    /**
     * @param  DeviceOutage  $outage
     */
    public function formatItem($outage)
    {
        $start = $this->formatDatetime($outage->going_down);
        $end = $outage->up_again ? $this->formatDatetime($outage->up_again) : '-';
        $duration = ($outage->up_again ?: time()) - $outage->going_down;

        return [
            'status' => $this->statusLabel($outage),
            'going_down' => $start,
            'up_again' => $end,
            'device_id' => Blade::render('<x-device-link :device="$device"/>', ['device' => $outage->device]),
            'duration' => $this->formatTime($duration),
        ];
    }

    private function formatTime($duration)
    {
        $day_seconds = 86400;

        $duration_days = (int) ($duration / $day_seconds);

        $output = '';
        if ($duration_days) {
            $output .= $duration_days . 'd ';
        }
        $output .= (new Carbon($duration))->format(LibrenmsConfig::get('dateformat.time'));

        return $output;
    }

    private function formatDatetime($timestamp)
    {
        if (! $timestamp) {
            $timestamp = 0;
        }

        // Convert epoch to local time
        return Carbon::createFromTimestamp($timestamp, session('preferences.timezone'))
            ->format(LibrenmsConfig::get('dateformat.compact'));
    }

    private function statusLabel($outage)
    {
        if (empty($outage->up_again)) {
            $label = 'label-danger';
        } else {
            $label = 'label-success';
        }

        $output = "<span class='alert-status " . $label . "'></span>";

        return $output;
    }

    /**
     * Get headers for CSV export
     *
     * @return array
     */
    protected function getExportHeaders()
    {
        return [
            'Device Hostname',
            'Start',
            'End',
            'Duration',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  DeviceOutage  $outage
     * @return array
     */
    protected function formatExportRow($outage)
    {
        return [
            $outage->device ? $outage->device->displayName() : '',
            $this->formatDatetime($outage->going_down),
            $outage->up_again ? $this->formatDatetime($outage->up_again) : '-',
            $this->formatTime(($outage->up_again ?: time()) - $outage->going_down),
        ];
    }

    private function applyDateRangeCondition(Builder $query, string $column, ?int $from_ts, ?int $to_ts): void
    {
        if ($from_ts && $to_ts) {
            $query->whereBetween($column, [$from_ts, $to_ts]);
        } elseif ($from_ts) {
            $query->where($column, '>=', $from_ts);
        } elseif ($to_ts) {
            $query->where($column, '<=', $to_ts);
        }
    }
}
