<?php

/*
 * AboutAlerts.php
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
 * @copyright  2025 Peter Childs
 * @author     Peter Childs <pjchilds@gmail.com>
 */

namespace App\Services;

use App\Models\AlertLog;
use App\Models\AlertRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AboutAlerts
{
    /**
     * @return array<string,int> rule_name => open_count
     */
    public function active(): array
    {
        return Cache::remember('alerts_active', 60, function () {
            // return zero for rules with no active alerts
            return AlertRule::where('disabled', 0)
                ->withCount(['alerts as open_count' => function ($q) {
                    $q->where('state', '>', 0)
                      ->where('open', 1);
                }])
                ->pluck('open_count', 'name')
                ->toArray();
        });
    }

    /**
     * @return array<string,int> rule_name => count in last 5m, zero if none
     */
    public function raisedLast5m(): array
    {
        return Cache::remember('alerts_raised_last_5m', 60, function () {
            $cutoff = Carbon::now()->subMinutes(5);

            $rules = AlertRule::where('disabled', 0)
                ->pluck('name');

            $counts = AlertLog::selectRaw('r.name AS rule_name, COUNT(*) AS cnt')
                ->join('alert_rules AS r', 'r.id', '=', 'alert_log.rule_id')
                ->whereRaw('r.disabled = ?', [0])
                ->where('alert_log.state', '>', 0)
                ->where('alert_log.time_logged', '>', $cutoff)
                ->groupBy('r.name')
                ->pluck('cnt', 'rule_name')
                ->toArray();

            // zero-fill any rules that had no rows in the last 5m
            return $rules->mapWithKeys(function ($ruleName) use ($counts) {
                return [$ruleName => $counts[$ruleName] ?? 0];
            })->toArray();
        });
    }
}
