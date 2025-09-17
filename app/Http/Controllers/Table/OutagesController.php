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
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class OutagesController extends TableController
{
    protected $model = DeviceOutage::class;

    public function rules(): array
    {
        return [
            'device' => 'nullable|int',
            'from' => 'nullable|date_or_relative',
            'to' => 'nullable|date_or_relative',
            'status' => 'nullable|in:current,previous,all',
        ];
    }

    protected function filterFields($request): array
    {
        return [
            'device_id' => 'device',
        ];
    }

    protected function sortFields($request): array
    {
        return ['going_down', 'up_again', 'device_id'];
    }

    /**
     * Defines the base query for this resource
     */
    public function baseQuery(Request $request): Builder
    {
        $from_ts = Time::parseInput($request->from);
        $to_ts = Time::parseInput($request->to);

        return DeviceOutage::hasAccess($request->user())
            ->with('device')
            ->when($from_ts || $to_ts, function ($query) use ($from_ts, $to_ts) {
                $query->where(function ($q) use ($from_ts, $to_ts) {
                    // Outage starts within range
                    $this->applyDateRangeCondition($q, 'going_down', $from_ts, $to_ts);

                    // OR outage ends within range (if it has ended)
                    $q->orWhere(function ($subQuery) use ($from_ts, $to_ts) {
                        $subQuery->where('up_again', '>', 0);
                        $this->applyDateRangeCondition($subQuery, 'up_again', $from_ts, $to_ts);
                    });
                });
            })
            ->when($request->status === 'current', function ($query) {
                $query->where(function ($q) {
                    $q->orWhere('up_again', 0);
                });
            })
            ->when($request->status === 'previous', function ($query) {
                $query->where('up_again', '>', 0);
            });
    }

    /**
     * @param  DeviceOutage  $outage
     */
    public function formatItem($outage): array
    {
        $start = $this->formatDatetime($outage->going_down);
        $end = $outage->up_again ? $this->formatDatetime($outage->up_again) : '-';

        return [
            'status' => $this->statusLabel($outage),
            'going_down' => $start,
            'up_again' => $end,
            'device_id' => Url::modernDeviceLink($outage->device),
            'duration' => $this->asDuration($outage)->forHumans(['parts' => 2]),
        ];
    }

    private function asDuration(DeviceOutage $outage): CarbonInterval
    {
        $start = Carbon::createFromTimestamp($outage->going_down);
        $end = $outage->up_again ? Carbon::createFromTimestamp($outage->up_again) : Carbon::now();

        return $end->diffAsCarbonInterval($start);
    }

    private function formatDatetime(?int $timestamp): string
    {
        if (! $timestamp) {
            return '';
        }

        // Convert epoch to local time
        return Carbon::createFromTimestamp($timestamp, session('preferences.timezone'))
            ->format(LibrenmsConfig::get('dateformat.compact'));
    }

    private function statusLabel(DeviceOutage $outage): string
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
    protected function getExportHeaders(): array
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
    protected function formatExportRow($outage): array
    {
        return [
            $outage->device ? $outage->device->displayName() : '',
            Carbon::createFromTimestamp($outage->going_down)->toISOString(),
            $outage->up_again ? Carbon::createFromTimestamp($outage->up_again)->toISOString() : '-',
            $this->asDuration($outage)->format('%H:%I:%S'),
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
