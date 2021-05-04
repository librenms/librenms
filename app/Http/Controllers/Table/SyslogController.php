<?php
/**
 * SyslogController.php
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

use App\Models\Syslog;

class SyslogController extends TableController
{
    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'device_group' => 'nullable|int',
            'program' => 'nullable|string',
            'priority' => 'nullable|string',
            'to' => 'nullable|date',
            'from' => 'nullable|date',
        ];
    }

    public function searchFields($request)
    {
        return ['msg'];
    }

    public function filterFields($request)
    {
        return [
            'device_id' => 'device',
            'program' => 'program',
            'priority' => 'priority',
        ];
    }

    public function sortFields($request)
    {
        return ['label', 'timestamp', 'level', 'device_id', 'program', 'msg', 'priority'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return Syslog::hasAccess($request->user())
            ->with('device')
            ->when($request->device_group, function ($query) use ($request) {
                $query->inDeviceGroup($request->device_group);
            })
            ->when($request->from, function ($query) use ($request) {
                $query->where('timestamp', '>=', $request->from);
            })
            ->when($request->to, function ($query) use ($request) {
                $query->where('timestamp', '<=', $request->to);
            });
    }

    /**
     * @param Syslog $syslog
     */
    public function formatItem($syslog)
    {
        $device = $syslog->device;

        return [
            'label' => $this->setLabel($syslog),
            'timestamp' => $syslog->timestamp,
            'level' => htmlentities($syslog->level),
            'device_id' => $device ? \LibreNMS\Util\Url::deviceLink($device, $device->shortDisplayName()) : '',
            'program' => htmlentities($syslog->program),
            'msg' => htmlentities($syslog->msg),
            'priority' => htmlentities($syslog->priority),
        ];
    }

    private function setLabel($syslog)
    {
        $output = "<span class='alert-status ";
        $output .= $this->priorityLabel($syslog->priority);
        $output .= "'>";
        $output .= '</span>';

        return $output;
    }

    /**
     * @param int $syslog_priority
     * @return string
     */
    private function priorityLabel($syslog_priority)
    {
        switch ($syslog_priority) {
            case 'debug':
                return 'label-default'; //Debug
            case 'info':
                return 'label-info'; //Informational
            case 'notice':
                return 'label-primary'; //Notice
            case 'warning':
                return 'label-warning'; //Warning
            case 'err':
                return 'label-danger'; //Error
            case 'crit':
                return 'label-danger'; //Critical
            case 'alert':
                return 'label-danger'; //Alert
            case 'emerg':
                return 'label-danger'; //Emergency
            default:
                return '';
        }
    }

    // end syslog_priority
}
