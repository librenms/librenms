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
use App\Models\Eventlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Enum\Severity;
use PDOException;

class AlertRules
{
    public function runRules(int $device_id): bool
    {
        $device = DeviceCache::get($device_id);

        //Check to see if under maintenance
        if (AlertUtil::getMaintenanceStatus($device_id) === MaintenanceStatus::SkipAlerts) {
            Log::info("Under Maintenance, skipping alert rules check.");

            return false;
        }

        //Check to see if disable alerting is set
        if (AlertUtil::hasDisableNotify($device_id)) {
            Log::info('Disable alerting is set, Clearing active alerts and skipping alert rules check');
            Alert::where('device_id', $device_id)
                ->update([
                    'state' => AlertState::CLEAR,
                    'alerted' => 0,
                    'open' => 0,
                ]);

            return false;
        }

        //Checks each rule.
        foreach (AlertUtil::getRules($device_id) as $rule) {
            Log::info('Rule %p#' . $rule['id'] . ' (' . $rule['name'] . '):%n ', ['color' => true]);
            $extra = json_decode((string) $rule['extra'], true);
            $invert = (bool) ($extra['invert'] ?? false);

            if (empty($rule['query'])) {
                $rule['query'] = QueryBuilderParser::fromJson($rule['builder'])->toSql();
            }
            $sql = $rule['query'];

            if (empty($sql)) {
                continue; // no sql
            }

            // set fetch assoc
            try {
                $qry = array_map(fn ($row) => (array) $row, DB::select($sql, [$device_id]));
            } catch (PDOException $e) {
                Log::error('%RError: %n' . $e->getMessage(), ['color' => true]);
                Eventlog::log("Error in alert rule {$rule['name']} ({$rule['id']}): " . $e->getMessage(), $device_id, 'alert', Severity::Error);
                continue; // skip this rule
            }

            foreach ($qry as &$row) {
                if (isset($row['ip'])) {
                    $row['ip'] = inet6_ntop($row['ip']);
                }
            }

            $matched = ! empty($qry);
            $doalert = $matched !== $invert;

            $current_state = Alert::where('rule_id', $rule['id'])
                ->where('device_id', $device_id)
                ->latest('id')
                ->value('state');

            if ($doalert) {
                if ($current_state == AlertState::ACKNOWLEDGED) {
                    Log::info('Status: %ySKIP%n', ['color' => true]);
                } elseif ($current_state >= AlertState::ACTIVE) {
                    Log::info('Status: %bNOCHG%n', ['color' => true]);
                    // NOCHG here doesn't mean no change full stop. It means no change to the alert state
                    // So we update the details column with any fresh changes to the alert output we might have.
                    $alert_log = AlertLog::join('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
                        ->where('alert_log.device_id', $device_id)
                        ->where('alert_log.rule_id', $rule['id'])
                        ->where('alert_rules.disabled', 0)
                        ->select('alert_log.*')
                        ->latest('alert_log.id')
                        ->first();

                    if ($alert_log) {
                        $details = $alert_log->details ?? [];
                        $details['contacts'] = AlertUtil::getContacts($qry);
                        $details['rule'] = $qry;
                        $alert_log->update(['details' => $details]);
                    }
                } else {
                    $extra = ['contacts' => AlertUtil::getContacts($qry), 'rule' => $qry];
                    if (AlertLog::create(['state' => AlertState::ACTIVE, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'details' => $extra])) {
                        if (is_null($current_state)) {
                            Alert::create(['state' => AlertState::ACTIVE, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0, 'info' => []]);
                        } else {
                            Alert::where('device_id', $device_id)
                                ->where('rule_id', $rule['id'])
                                ->update(['state' => AlertState::ACTIVE, 'open' => 1, 'alerted' => 0, 'timestamp' => Carbon::now()]);
                        }
                        Log::info(PHP_EOL . 'Status: %rALERT%n', ['color' => true]);
                    }
                }
            } else {
                if (! is_null($current_state) && $current_state == AlertState::RECOVERED) {
                    Log::info('Status: %bNOCHG%n', ['color' => true]);
                } else {
                    if (AlertLog::create(['state' => AlertState::RECOVERED, 'device_id' => $device_id, 'rule_id' => $rule['id']])) {
                        if (is_null($current_state)) {
                            Alert::create(['state' => AlertState::RECOVERED, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0, 'info' => []]);
                        } else {
                            Alert::where('device_id', $device_id)
                                ->where('rule_id', $rule['id'])
                                ->update(['state' => AlertState::RECOVERED, 'open' => 1, 'note' => '', 'timestamp' => Carbon::now()]);
                        }

                        Log::info(PHP_EOL . 'Status: %gOK%n', ['color' => true]);
                    }
                }
            }
        }

        return true;
    }
}
