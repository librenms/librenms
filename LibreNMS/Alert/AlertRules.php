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
use App\Models\AlertFault;
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
        $this->device = DeviceCache::get(is_int($device) ? $device : $device->device_id);
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
            AlertFault::query()->where('device_id', $this->device->device_id)->where('open', 1)
                ->update(['open' => 0, 'state' => AlertState::RECOVERED]);
            $this->device->alerts()->update([
                'state' => AlertState::CLEAR,
                'alerted' => 0,
                'open' => 0,
                'open_fault_count' => 0,
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
                    $key = AlertUtil::faultKeyForRow($row);
                    if (! isset($faulting[$key])) {
                        [$type, $id] = AlertUtil::entityForFault($row);
                        $faulting[$key] = ['type' => $type, 'id' => $id, 'rows' => []];
                    }
                    $faulting[$key]['rows'][] = $row;
                }
            }
        }

        /** @var array<string, AlertFault> $existing */
        $existing = [];
        foreach (AlertFault::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)
            ->where('open', 1)->where('state', '!=', AlertState::RECOVERED)->get() as $fault) {
            /** @var AlertFault $fault */
            $existing[$fault->entity_key] = $fault;
        }

        foreach ($faulting as $key => $info) {
            $details = ['rule' => $info['rows'], 'contacts' => AlertUtil::getContacts($info['rows'])];
            if (isset($existing[$key])) {
                $fault = $existing[$key];
                $fault->details = $details;
                $fault->severity = $rule->severity;
                $fault->last_seen = $now;
                if ($info['type'] !== null && $info['id'] !== null) {
                    $fault->entity_type = $info['type'];
                    $fault->entity_id = $info['id'];
                }
                $fault->save();
                unset($existing[$key]);
                Log::info('Status: %bNOCHG%n', ['color' => true]);
            } else {
                $fault = new AlertFault;
                $fault->rule_id = $rule->id;
                $fault->device_id = $this->device->device_id;
                $fault->entity_type = $info['type'];
                $fault->entity_id = $info['id'];
                $fault->entity_key = (string) $key;
                $fault->severity = $rule->severity;
                $fault->details = $details;
                $this->recordFaultTransition($fault, AlertState::ACTIVE, $now);
                Log::info(PHP_EOL . 'Status: %rALERT%n', ['color' => true]);
            }
        }

        foreach ($existing as $fault) {
            $this->recordFaultTransition($fault, AlertState::RECOVERED, $now);
            Log::info(PHP_EOL . 'Status: %gOK%n', ['color' => true]);
        }

        $this->syncAlertState($rule);
    }

    /**
     * Persist a fault state change: save the fault row (with its current details) and append the
     * matching alert_log entry. Set $fault->details before calling.
     */
    private function recordFaultTransition(AlertFault $fault, int $state, ?Carbon $now = null): void
    {
        $now ??= Carbon::now();
        if (! $fault->exists) {
            $fault->first_seen = $now;
        }
        $fault->state = $state;
        $fault->open = 1; // recoveries stay open until the dispatcher sends the recovery notification
        $fault->alerted = 0;
        $fault->last_seen = $now;
        $fault->timestamp = $now;
        $fault->save();

        $details = is_array($fault->details) ? $fault->details : [];
        AlertLog::create([
            'rule_id' => $fault->rule_id,
            'device_id' => $fault->device_id,
            'fault_id' => $fault->id,
            'state' => $state,
            'time_logged' => $now,
            'details' => $details,
        ]);
    }

    /**
     * Update the rule-level alerts row from the current open fault count.
     * Worse/better is derived from the count delta (replaces the old fault diffing).
     */
    public function syncAlertState(AlertRule $rule): void
    {
        $base = AlertFault::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)->where('open', 1);
        $activeCount = (clone $base)->where('state', '!=', AlertState::RECOVERED)->count();
        $unackCount = (clone $base)->where('state', AlertState::ACTIVE)->count();

        $alertRow = Alert::query()->where('rule_id', $rule->id)->where('device_id', $this->device->device_id)->first();
        $prevState = $alertRow?->state;
        $prevCount = $alertRow !== null ? (int) $alertRow->open_fault_count : 0;

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
            $alertRow->open_fault_count = $activeCount;
            if ($stateChanged) {
                $alertRow->open = 1;
                // Keep alerted when entering acknowledged so runAcks can dedupe via alerted=ACK after notify.
                if ($newState !== AlertState::ACKNOWLEDGED) {
                    $alertRow->alerted = 0;
                }
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
            $alertRow->open_fault_count = $activeCount;
            $alertRow->info = [];
            $alertRow->save();
        }
    }
}
