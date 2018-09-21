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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Syslog;
use Illuminate\Database\Eloquent\Builder;

class SyslogController extends TableController
{
    public function searchFields($request)
    {
        return ['msg'];
    }

    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'program' => 'nullable|string',
            'priority' => 'nullable|string',
            'to' => 'nullable|date',
            'from' => 'nullable|date',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        /** @var Builder $query */
        $query = Syslog::hasAccess($request->user())->with('device');

        if ($device_id = $request->get('device')) {
            $query->where('device_id', $device_id);
        }

        if ($program = $request->get('program')) {
            $query->where('program', $program);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($from = $request->get('from')) {
            $query->where('timestamp', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->where('timestamp', '<=', $to);
        }

        return $query;
    }

    public function formatItem($syslog)
    {
        $device = $syslog->device;

        return [
            'timestamp' => $syslog->timestamp,
            'level' => htmlentities($syslog->level),
            'device_id' => $device ? \LibreNMS\Util\Url::deviceLink($device, $device->shortDisplayName()) : '',
            'program' => htmlentities($syslog->program),
            'msg' => htmlentities($syslog->msg),
            'priority' => htmlentities($syslog->priority),
        ];
    }
}
