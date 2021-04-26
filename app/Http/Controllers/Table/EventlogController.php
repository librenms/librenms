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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Eventlog;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Enum\Alert;
use LibreNMS\Util\Url;

class EventlogController extends TableController
{
    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'device_group' => 'nullable|int',
            'eventtype' => 'nullable|string',
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return Eventlog::hasAccess($request->user())
            ->with('device')
            ->when($request->device_group, function ($query) use ($request) {
                $query->inDeviceGroup($request->device_group);
            });
    }

    /**
     * @param Eventlog $eventlog
     */
    public function formatItem($eventlog)
    {
        return [
            'datetime' => $this->formatDatetime($eventlog),
            'device_id' => $eventlog->device ? Url::deviceLink($eventlog->device, $eventlog->device->shortDisplayName()) : null,
            'type' => $this->formatType($eventlog),
            'message' => htmlspecialchars($eventlog->message),
            'username' => $eventlog->username ?: 'System',
        ];
    }

    private function formatType($eventlog)
    {
        if ($eventlog->type == 'interface') {
            if (is_numeric($eventlog->reference)) {
                $port = $eventlog->related;
                if (isset($port)) {
                    return '<b>' . Url::portLink($port, $port->getShortLabel()) . '</b>';
                }
            }
        } elseif ($eventlog->type == 'stp') {
            return Url::deviceLink($eventlog->device, $eventlog->type, ['tab' => 'stp']);
        } elseif (in_array($eventlog->type, \App\Models\Sensor::getTypes())) {
            if (is_numeric($eventlog->reference)) {
                $sensor = $eventlog->related;
                if (isset($sensor)) {
                    return '<b>' . Url::sensorLink($sensor, $sensor->sensor_descr) . '</b>';
                }
            }
        }

        return $eventlog->type;
    }

    private function formatDatetime($eventlog)
    {
        $output = "<span class='alert-status ";
        $output .= $this->severityLabel($eventlog->severity);
        $output .= " eventlog-status'></span><span style='display:inline;'>";
        $output .= (new Carbon($eventlog->datetime))->format(Config::get('dateformat.compact'));
        $output .= '</span>';

        return $output;
    }

    /**
     * @param int $eventlog_severity
     * @return string $eventlog_severity_icon
     */
    private function severityLabel($eventlog_severity)
    {
        switch ($eventlog_severity) {
            case Alert::OK:
                return 'label-success'; //OK
            case Alert::INFO:
                return 'label-info'; //Informational
            case Alert::NOTICE:
                return 'label-primary'; //Notice
            case Alert::WARNING:
                return 'label-warning'; //Warning
            case Alert::ERROR:
                return 'label-danger'; //Critical
            default:
                return 'label-default'; //Unknown
        }
    }

    // end eventlog_severity
}
