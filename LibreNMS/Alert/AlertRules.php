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

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\Eventlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\Severity;
use PDO;
use PDOException;

class AlertRules
{
    public function runRules($device_id)
    {
        //Check to see if under maintenance
        if (AlertUtil::isMaintenance($device_id) > 0) {
            echo "Under Maintenance, skipping alert rules check.\r\n";

            return false;
        }
        //Check to see if disable alerting is set
        if (AlertUtil::hasDisableNotify($device_id)) {
            echo "Disable alerting is set, Clearing active alerts and skipping alert rules check\r\n";
            $device_alert['state'] = AlertState::CLEAR;
            $device_alert['alerted'] = 0;
            $device_alert['open'] = 0;
            Alert::where('device_id', $device_id)
                ->update($device_alert);

            return false;
        }
        //Checks each rule.
        foreach (AlertUtil::getRules($device_id) as $rule) {
            Log::info('Rule %p#' . $rule['id'] . ' (' . $rule['name'] . '):%n ', ['color' => true]);
            $extra = json_decode($rule['extra'], true);
            if (isset($extra['invert'])) {
                $inv = (bool) $extra['invert'];
            } else {
                $inv = false;
            }
            d_echo(PHP_EOL);
            if (empty($rule['query'])) {
                $rule['query'] = QueryBuilderParser::fromJson($rule['builder'])->toSql();
            }
            $sql = $rule['query'];

            // set fetch assoc
            try {
                $query = DB::connection()->getPdo()->prepare($sql);
                $query->execute([$device_id]);

                $qry = $query->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                c_echo('%RError: %n' . $e->getMessage() . PHP_EOL);
                Eventlog::log("Error in alert rule {$rule['name']} ({$rule['id']}): " . $e->getMessage(), $device_id, 'alert', Severity::Error);
                continue; // skip this rule
            }

            $cnt = count($qry);
            for ($i = 0; $i < $cnt; $i++) {
                if (isset($qry[$i]['ip'])) {
                    $qry[$i]['ip'] = inet6_ntop($qry[$i]['ip']);
                }
            }
            $s = count($qry);
            if ($s == 0 && $inv === false) {
                $doalert = false;
            } elseif ($s > 0 && $inv === false) {
                $doalert = true;
            } elseif ($s == 0 && $inv === true) {
                $doalert = true;
            } else {
                $doalert = false;
            }

            $current_state = Alert::where('rule_id', $rule['id'])
                ->where('device_id', $device_id)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->value('state');
            if ($doalert) {
                if ($current_state == AlertState::ACKNOWLEDGED) {
                    Log::info('Status: %ySKIP%n', ['color' => true]);
                } elseif ($current_state >= AlertState::ACTIVE) {
                    Log::info('Status: %bNOCHG%n', ['color' => true]);
                    // NOCHG here doesn't mean no change full stop. It means no change to the alert state
                    // So we update the details column with any fresh changes to the alert output we might have.
                    $alert_log = AlertLog::leftJoin('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
                        ->where('alert_log.device_id', $device_id)
                        ->where('alert_log.rule_id', $rule['id'])
                        ->where('alert_rules.disabled', 0)
                        ->orderBy('alert_log.id', 'desc')
                        ->first(['alert_log.id', 'alert_log.details']);
                    $details = [];
                    if (! empty($alert_log->details)) {
                        $details = json_decode(gzuncompress($alert_log->details), true);
                    }
                    $details['contacts'] = AlertUtil::getContacts($qry);
                    $details['rule'] = $qry;
                    $details = gzcompress(json_encode($details), 9);
                    AlertLog::where('id', $alert_log->id)
                        ->update(['details' => $details]);
                } else {
                    $extra = gzcompress(json_encode(['contacts' => AlertUtil::getContacts($qry), 'rule' => $qry]), 9);
                    if (AlertLog::insert([
                        'state' => AlertState::ACTIVE,
                        'device_id' => $device_id,
                        'rule_id' => $rule['id'],
                        'details' => $extra,
                    ])) {
                        if (is_null($current_state)) {
                            Alert::insert([
                                'state' => AlertState::ACTIVE,
                                'device_id' => $device_id,
                                'rule_id' => $rule['id'],
                                'open' => 1,
                                'alerted' => 0,
                                'details' => $extra,
                            ]);
                        } else {
                            Alert::where('device_id', $device_id)
                                ->where('rule_id', $rule['id'])
                                ->update(['state' => AlertState::ACTIVE, 'open' => 1, 'timestamp' => Carbon::now()]);
                        }
                        Log::info(PHP_EOL . 'Status: %rALERT%n', ['color' => true]);
                    }
                }
            } else {
                if (! is_null($current_state) && $current_state == AlertState::RECOVERED) {
                    Log::info('Status: %bNOCHG%n', ['color' => true]);
                } else {
                    if (AlertLog::insert([
                        'state' => AlertState::RECOVERED,
                        'device_id' => $device_id,
                        'rule_id' => $rule['id'],
                    ])) {
                        if (is_null($current_state)) {
                            Alert::insert([
                                'state' => AlertState::RECOVERED,
                                'device_id' => $device_id,
                                'rule_id' => $rule['id'],
                                'open' => 1,
                                'alerted' => 0,
                            ]);
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
    }
}
