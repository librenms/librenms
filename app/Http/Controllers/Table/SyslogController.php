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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Syslog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Enum\SyslogSeverity;

/**
 * @extends TableController<Syslog>
 */
class SyslogController extends TableController
{
    public function rules(): array
    {
        return [
            'device' => 'nullable|int',
            'device_group' => 'nullable|int',
            'program' => 'nullable|string',
            'priority' => 'nullable|string',
            'to' => 'nullable|date',
            'from' => 'nullable|date',
            'level' => 'nullable|string',
        ];
    }

    public function searchFields(Request $request): array
    {
        return ['msg'];
    }

    public function filterFields(Request $request): array
    {
        return [
            'device_id' => 'device',
            'program' => 'program',
            'priority' => 'priority',
        ];
    }

    public function sortFields(Request $request): array
    {
        return ['label', 'timestamp', 'level', 'device_id', 'program', 'msg', 'priority'];
    }

    /**
     * Defines the base query for this resource
     */
    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Syslog::class);

        return Syslog::hasAccess($request->user())
            ->with('device')
            ->when($request->device_group, function ($query, $group): void {
                $query->inDeviceGroup($group);
            })
            ->when($request->from, function ($query, $from): void {
                $query->where('timestamp', '>=', $from);
            })
            ->when($request->to, function ($query, $to): void {
                $query->where('timestamp', '<=', $to);
            })
            ->when($request->level, function ($query, $level): void {
                if ($level >= 7) {
                    return;  // include everything
                }

                $levels = array_slice(SyslogSeverity::LEVELS, 0, $level + 1);
                $query->whereIn('level', $levels);
            });
    }

    /**
     * @param  Syslog  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'label' => $this->setLabel($model),
            'timestamp' => $model->timestamp,
            'level' => htmlentities((string) $model->level),
            'device_id' => Blade::render('<x-device-link :device="$device"/>', ['device' => $model->device]),
            'program' => htmlentities((string) $model->program),
            'msg' => htmlentities((string) $model->msg),
            'priority' => htmlentities((string) $model->priority),
        ];
    }

    private function setLabel(Syslog $syslog): string
    {
        $output = "<span class='alert-status ";
        $output .= $this->priorityLabel($syslog->priority);
        $output .= "'>";
        $output .= '</span>';

        return $output;
    }

    private function priorityLabel(string $syslog_priority): string
    {
        return match ($syslog_priority) {
            'debug' => 'label-default',
            'info' => 'label-info',
            'notice' => 'label-primary',
            'warning' => 'label-warning',
            'err', 'crit', 'alert', 'emerg' => 'label-danger',
            default => '',
        };
    }
}
