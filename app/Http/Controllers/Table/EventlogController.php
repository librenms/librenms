<?php

/**
 * EventlogController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Eventlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class EventlogController extends TableController
{
    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'device_group' => 'nullable|int',
            'eventtype' => 'nullable|string',
            'age' => 'nullable|int',
            'message' => 'nullable|string',
        ];
    }

    public function searchFields($request)
    {
        return ['message'];
    }

    protected function filterFields($request)
    {
        return [
            'device_id' => 'device',
            'type' => 'eventtype',
        ];
    }

    protected function sortFields($request)
    {
        return ['datetime', 'type', 'device_id', 'message', 'username'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return Eventlog::hasAccess($request->user())
            ->with('device')
            ->when($request->device_group, function ($query) use ($request): void {
                $query->inDeviceGroup($request->device_group);
            })
            ->when($request->message, function ($query) use ($request): void {
                $query->where('message', 'like', '%' . $request->message . '%');
            })
            ->when($request->age, function ($query) use ($request): void {
                $query->where('datetime', '>', Carbon::now()->subSeconds((int) $request->age));
            });
    }

    /**
     * @param  Eventlog  $eventlog
     */
    public function formatItem($eventlog)
    {
        return [
            'datetime' => $this->formatDatetime($eventlog),
            'device_id' => Blade::render('<x-device-link :device="$device"/>', ['device' => $eventlog->device]),
            'type' => $this->formatType($eventlog),
            'message' => htmlspecialchars((string) $eventlog->message),
            'username' => $eventlog->username ?: 'System',
        ];
    }

    private function formatType($eventlog)
    {
        if ($eventlog->type == 'interface') {
            if (is_numeric($eventlog->reference)) {
                $port = $eventlog->related;
                if (isset($port)) {
                    return Blade::render('<b><x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link></b>', ['port' => $port]);
                }
            }
        } elseif ($eventlog->type == 'stp') {
            return Blade::render('<x-device-link :device="$device" tab="stp">stp</x-device-link>', ['device' => $eventlog->device]);
        } elseif (in_array($eventlog->type, \LibreNMS\Enum\Sensor::values())) {
            if (is_numeric($eventlog->reference)) {
                $sensor = $eventlog->related;
                if (isset($sensor)) {
                    return '<b>' . Url::sensorLink($sensor, $sensor->sensor_descr) . '</b>';
                }
            }
        }

        return htmlspecialchars((string) $eventlog->type);
    }

    private function formatDatetime($eventlog)
    {
        $output = "<span class='alert-status ";
        $output .= $this->severityLabel($eventlog->severity);
        $output .= " eventlog-status'></span>";
        $output .= Time::format($eventlog->datetime, 'compact');

        return $output;
    }

    /**
     * @param  Severity  $eventlog_severity
     * @return string $eventlog_severity_icon
     */
    private function severityLabel($eventlog_severity)
    {
        return match ($eventlog_severity) {
            Severity::Ok => 'label-success',
            Severity::Info => 'label-info',
            Severity::Notice => 'label-primary',
            Severity::Warning => 'label-warning',
            Severity::Error => 'label-danger',
            default => 'label-default', // Unknown
        };
    }
}
