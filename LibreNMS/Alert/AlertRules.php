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
            $this->device->alerts()
                ->update([
                    'state' => AlertState::CLEAR,
                    'alerted' => 0,
                    'open' => 0,
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

        $do_alert = ! empty($rows) !== $invert;

        $alert = $this->device->alerts()
            ->where('rule_id', $rule->id)
            ->latest('id')
            ->first();

        if ($do_alert) {
            $this->handleAlertTrigger($rule, $rows, $alert);
        } else {
            $this->handleAlertRecovery($rule, $alert);
        }
    }

    /**
     * Handle triggering or updating an active alert.
     *
     * @param  AlertRule  $rule
     * @param  array  $rows
     * @param  Alert|null  $alert
     */
    private function handleAlertTrigger(AlertRule $rule, array $rows, ?Alert $alert): void
    {
        $current_state = $alert?->state;

        if ($current_state == AlertState::ACKNOWLEDGED) {
            Log::info('Status: %ySKIP%n', ['color' => true]);

            return;
        }

        if (in_array($current_state, [AlertState::ACTIVE, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED], true)) {
            Log::info('Status: %bNOCHG%n', ['color' => true]);

            // NOCHG here doesn't mean no change full stop. It means no change to the alert state
            // So we update the details column with any fresh changes to the alert output we might have.
            $alert_log = $this->device->alertLogs()
                ->where('rule_id', $rule->id)
                ->latest('id')
                ->first();

            if ($alert_log) {
                $details = $alert_log->details ?? [];
                $details['contacts'] = AlertUtil::getContacts($rows);
                $details['rule'] = $rows;
                $alert_log->update(['details' => $details]);
            }

            return;
        }

        // State is not active, trigger alert
        $extra = ['contacts' => AlertUtil::getContacts($rows), 'rule' => $rows];
        if ($this->device->alertLogs()->create(['state' => AlertState::ACTIVE, 'rule_id' => $rule->id, 'details' => $extra])) {
            $alert_data = [
                'state' => AlertState::ACTIVE,
                'open' => 1,
                'alerted' => 0,
                'timestamp' => Carbon::now(),
            ];

            if ($alert) {
                $alert->update($alert_data);
            } else {
                $this->device->alerts()->create(array_merge($alert_data, [
                    'rule_id' => $rule->id,
                    'info' => [],
                ]));
            }
            Log::info(PHP_EOL . 'Status: %rALERT%n', ['color' => true]);
        }
    }

    /**
     * Handle recovery of an alert.
     *
     * @param  AlertRule  $rule
     * @param  Alert|null  $alert
     */
    private function handleAlertRecovery(AlertRule $rule, ?Alert $alert): void
    {
        if ($alert && $alert->state == AlertState::RECOVERED) {
            Log::info('Status: %bNOCHG%n', ['color' => true]);

            return;
        }

        if ($this->device->alertLogs()->create(['state' => AlertState::RECOVERED, 'rule_id' => $rule->id])) {
            $alert_data = [
                'state' => AlertState::RECOVERED,
                'open' => 1,
                'timestamp' => Carbon::now(),
            ];

            if ($alert) {
                $alert->update(array_merge($alert_data, ['note' => '']));
            } else {
                $this->device->alerts()->create(array_merge($alert_data, [
                    'rule_id' => $rule->id,
                    'alerted' => 0,
                    'info' => [],
                ]));
            }

            Log::info(PHP_EOL . 'Status: %gOK%n', ['color' => true]);
        }
    }
}
