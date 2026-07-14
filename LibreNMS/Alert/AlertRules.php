<?php

/**
 * AlertRules.php
 *
 * Extending the built in logging to add an event logger function
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
 * Original Code:
 *
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use App\Facades\DeviceCache;
use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertProblem;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\Eventlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Enum\Severity;
use PDOException;

readonly class AlertRules
{
    private Device $device;

    public function __construct(
        Device|int $device
    ) {
        $this->device = is_int($device) ? DeviceCache::get($device) : $device;
    }

    /**
     * Run all alert rules for the given device.
     *
     * @return bool
     */
    public function run(): bool
    {
        if ($this->device->getMaintenanceStatus() === MaintenanceStatus::SkipAlerts) {
            Log::info('Under Maintenance, skipping alert rules check.');

            return false;
        }

        if ($this->device->disable_notify) {
            Log::info('Disable alerting is set, Clearing active alerts and skipping alert rules check');
            AlertProblem::query()->where('device_id', $this->device->device_id)->where('open', 1)
                ->update(['open' => 0, 'state' => AlertState::RECOVERED]);
            $this->device->alerts()->update([
                'state' => AlertState::CLEAR,
                'alerted' => 0,
                'open' => 0,
                'open_problem_count' => 0,
            ]);

            return false;
        }

        foreach (AlertRule::enabled()->forDevice($this->device)->get() as $rule) {
            $this->processRule($rule);
        }

        return true;
    }

    /**
     * Process a single alert rule for a device.
     *
     * @param  AlertRule  $rule
     */
    private function processRule(AlertRule $rule): void
    {
        Log::info('Rule %p#' . $rule->id . ' (' . $rule->name . '):%n ', ['color' => true]);

        $invert = (bool) ($rule->extra['invert'] ?? false);

        $sql = $rule->query ?: QueryBuilderParser::fromJson($rule->builder)->toSql();

        if (empty($sql)) {
            return;
        }

        try {
            $rows = DB::select($sql, [$this->device->device_id]);
        } catch (PDOException $e) {
            Log::error('%RError: %n' . $e->getMessage(), ['color' => true]);
            Eventlog::log("Error in alert rule $rule->name ($rule->id): " . $e->getMessage(), $this->device, 'alert', Severity::Error);

            return;
        }

        $rows = array_map(function ($row) {
            $row = (array) $row;
            if (isset($row['ip'])) {
                $row['ip'] = inet6_ntop($row['ip']);
            }

            return $row;
        }, $rows);

        $alert = $this->device->alerts()
            ->where('rule_id', $rule->id)
            ->latest('id')
            ->first();

        if ($alert?->state === AlertState::ACKNOWLEDGED) {
            Log::info('Status: %ySKIP%n', ['color' => true]);

            return;
        }

        $do_alert = ! empty($rows) !== $invert;
        $now = Carbon::now();

        $faulting = [];
        if ($do_alert) {
            if ($invert || empty($rows)) {
                $faulting[''] = ['type' => null, 'id' => null, 'rows' => $rows];
            } else {
                foreach ($rows as $row) {
                    $key = AlertUtil::generateComparisonKeyForFault($row, AlertUtil::extractIdFieldsForFault($row));
                    if (! isset($faulting[$key])) {
                        [$type, $id] = AlertUtil::entityForFault($row);
                        $faulting[$key] = ['type' => $type, 'id' => $id, 'rows' => []];
                    }
                    $faulting[$key]['rows'][] = $row;
                }
            }
        }

        /** @var array<string, AlertProblem> $existing */
        $existing = [];
        foreach (AlertProblem::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)
            ->where('open', 1)->where('state', '!=', AlertState::RECOVERED)->get() as $problem) {
            /** @var AlertProblem $problem */
            $existing[$problem->entity_key] = $problem;
        }

        foreach ($faulting as $key => $info) {
            $details = ['rule' => $info['rows'], 'contacts' => AlertUtil::getContacts($info['rows'])];
            if (isset($existing[$key])) {
                $problem = $existing[$key];
                $problem->details = $details;
                $problem->severity = $rule->severity;
                $problem->last_seen = $now;
                $problem->save();
                unset($existing[$key]);
                Log::info('Status: %bNOCHG%n', ['color' => true]);
            } else {
                $problem = new AlertProblem;
                $problem->rule_id = $rule->id;
                $problem->device_id = $this->device->device_id;
                $problem->entity_type = $info['type'];
                $problem->entity_id = $info['id'];
                $problem->entity_key = (string) $key;
                $problem->severity = $rule->severity;
                $problem->details = $details;
                $this->recordProblemTransition($problem, AlertState::ACTIVE, $now);
                Log::info(PHP_EOL . 'Status: %rALERT%n', ['color' => true]);
            }
        }

        foreach ($existing as $problem) {
            $this->recordProblemTransition($problem, AlertState::RECOVERED, $now);
            Log::info(PHP_EOL . 'Status: %gOK%n', ['color' => true]);
        }

        $this->syncAlertState($rule);
    }

    /**
     * Persist a problem state change: save the problem row (with its current details) and append the
     * matching alert_log entry. Set $problem->details before calling.
     */
    private function recordProblemTransition(AlertProblem $problem, int $state, ?Carbon $now = null): void
    {
        $now ??= Carbon::now();
        if (! $problem->exists) {
            $problem->first_seen = $now;
        }
        $problem->state = $state;
        $problem->open = 1; // recoveries stay open until the dispatcher sends the recovery notification
        $problem->alerted = 0;
        $problem->last_seen = $now;
        $problem->timestamp = $now;
        $problem->save();

        $details = is_array($problem->details) ? $problem->details : [];
        AlertLog::create([
            'rule_id' => $problem->rule_id,
            'device_id' => $problem->device_id,
            'problem_id' => $problem->id,
            'state' => $state,
            'time_logged' => $now,
            'details' => $details,
        ]);
    }

    /**
     * Update the rule-level alerts row from the current open problem count.
     * Worse/better is derived from the count delta (replaces the old fault diffing).
     */
    private function syncAlertState(AlertRule $rule): void
    {
        $base = AlertProblem::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)->where('open', 1);
        $activeCount = (clone $base)->where('state', '!=', AlertState::RECOVERED)->count();
        $unackCount = (clone $base)->where('state', AlertState::ACTIVE)->count();

        $alertRow = Alert::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)->first();
        $prevState = $alertRow?->state;
        $prevCount = $alertRow?->open_problem_count ?? 0;

        if ($activeCount == 0) {
            $newState = AlertState::RECOVERED;
        } elseif ($unackCount == 0) {
            $newState = AlertState::ACKNOWLEDGED;
        } elseif ($prevState === null || $prevState === AlertState::CLEAR) {
            $newState = AlertState::ACTIVE;
        } elseif ($activeCount > $prevCount) {
            $newState = AlertState::WORSE;
        } elseif ($activeCount < $prevCount) {
            $newState = AlertState::BETTER;
        } elseif (in_array($prevState, [AlertState::ACTIVE, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED], true)) {
            $newState = $prevState;
        } else {
            $newState = AlertState::ACTIVE;
        }

        $stateChanged = ($prevState ?? -1) !== $newState || $activeCount !== $prevCount;

        if ($alertRow) {
            $alertRow->state = $newState;
            $alertRow->open_problem_count = $activeCount;
            if ($stateChanged) {
                $alertRow->open = 1;
                $alertRow->alerted = 0;
                $alertRow->timestamp = Carbon::now();
            }
            if ($newState === AlertState::RECOVERED) {
                $alertRow->note = '';
            }
            $alertRow->save();
        } elseif ($activeCount > 0) {
            $alertRow = new Alert;
            $alertRow->state = $newState;
            $alertRow->device_id = $this->device->device_id;
            $alertRow->rule_id = $rule->id;
            $alertRow->open = 1;
            $alertRow->alerted = 0;
            $alertRow->open_problem_count = $activeCount;
            $alertRow->info = '[]';
            $alertRow->save();
        }
    }
}
