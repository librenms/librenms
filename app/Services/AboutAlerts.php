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

use App\Models\AlertRule;
use Illuminate\Support\Facades\Cache;

class AboutAlerts
{
    /**
     * @return array<string,int> rule_name => open_count
     */
    public function active(): array
    {
        return Cache::remember('alerts_active', 60, function () {
            return AlertRule::enabled()
                ->withCount('openAlerts')
                ->pluck('open_alerts_count', 'name')
                ->toArray();
        });
    }

    /**
     * @return array<string,int> rule_name => count in last 5m, zero if none
     */
    public function raisedLast5m(): array
    {
        return Cache::remember('alerts_raised_last_5m', 60, function () {
            // grab all enabled rule names
            $rules = AlertRule::enabled()
                              ->pluck('name');

            // count only active alerts logged in the last 5 minutes
            $counts = AlertRule::enabled()
                ->withCount(['alerts as recent_count' => function ($q) {
                    $q->active()
                      ->recent(5);
                }])
                ->pluck('recent_count', 'name')
                ->toArray();

            // zero–fill any rule with no recent alerts
            return $rules
                ->mapWithKeys(fn ($ruleName) => [
                    $ruleName => $counts[$ruleName] ?? 0,
                ])
                ->toArray();
        });
    }
}
