<?php

/**
 * AlertStatsController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Models\Alert;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\UI\DeviceTab;

class AlertStatsController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Alert::class);
    }

    public function slug(): string
    {
        return 'alert-stats';
    }

    public function icon(): string
    {
        return 'fa-bar-chart';
    }

    public function name(): string
    {
        return __('Alert Stats');
    }

    public function data(Device $device, Request $request): array
    {
        Gate::authorize('view', $device);
        Gate::authorize('viewAny', Alert::class);

        $dateFormat = LibrenmsConfig::get('alert_graph_date_format', '%Y-%m-%d');

        $stats = DB::table('alert_log')
            ->join('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
            ->where('alert_log.device_id', $device->device_id)
            ->where('alert_log.state', '!=', 0)
            ->selectRaw('DATE_FORMAT(time_logged, ?) as Date, COUNT(alert_log.rule_id) as totalCount, alert_rules.severity as Severity', [$dateFormat])
            ->groupBy('Date', 'alert_rules.severity')
            ->get();

        $groups = [];
        $items = [];
        foreach ($stats as $row) {
            $severity = (string) $row->Severity;
            $items[] = [
                'x' => (string) $row->Date,
                'y' => (int) $row->totalCount,
                'group' => $severity,
            ];
            if (! in_array($severity, $groups, true)) {
                $groups[] = $severity;
            }
        }

        $firstDate = array_first($items)['x'] ?? null;
        $lastDate = array_last($items)['x'] ?? null;
        $millisecondDiff = ($firstDate && $lastDate) ? abs(strtotime((string) $firstDate) - strtotime((string) $lastDate)) * 1000 : 0;

        return [
            'groups' => $groups,
            'items' => $items,
            'zoom_max' => $millisecondDiff,
        ];
    }
}
