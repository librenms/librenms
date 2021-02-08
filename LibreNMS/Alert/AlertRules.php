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
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use Carbon\Carbon;
use LibreNMS\Enum\AlertState;

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
            dbUpdate($device_alert, 'alerts', '`device_id` = ?', [$device_id]);

            return false;
        }
        //Checks each rule.
        foreach (AlertUtil::getRules($device_id) as $rule) {
            c_echo('Rule %p#' . $rule['id'] . ' (' . $rule['name'] . '):%n ');
            $extra = json_decode($rule['extra'], true);
            if (isset($extra['invert'])) {
                $inv = (bool) $extra['invert'];
            } else {
                $inv = false;
            }
            d_echo(PHP_EOL);
            if (empty($rule['query'])) {
                $rule['query'] = AlertDB::genSQL($rule['rule'], $rule['builder']);
            }
            $sql = $rule['query'];
            $qry = dbFetchRows($sql, [$device_id]);
            $cnt = count($qry);
            for ($i = 0; $i < $cnt; $i++) {
                if (isset($qry[$i]['ip'])) {
                    $qry[$i]['ip'] = inet6_ntop($qry[$i]['ip']);
                }
            }
            $s = sizeof($qry);
            if ($s == 0 && $inv === false) {
                $doalert = false;
            } elseif ($s > 0 && $inv === false) {
                $doalert = true;
            } elseif ($s == 0 && $inv === true) {
                $doalert = true;
            } else {
                $doalert = false;
            }

            $current_state = dbFetchCell('SELECT state FROM alerts WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1', [$rule['id'], $device_id]);
            if ($doalert) {
                if ($current_state == AlertState::ACKNOWLEDGED) {
                    c_echo('Status: %ySKIP');
                } elseif ($current_state >= AlertState::ACTIVE) {
                    c_echo('Status: %bNOCHG');
                    // NOCHG here doesn't mean no change full stop. It means no change to the alert state
                    // So we update the details column with any fresh changes to the alert output we might have.
                    $alert_log = dbFetchRow('SELECT alert_log.id, alert_log.details FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0
     ORDER BY alert_log.id DESC LIMIT 1', [$device_id, $rule['id']]);
                    $details = [];
                    if (! empty($alert_log['details'])) {
                        $details = json_decode(gzuncompress($alert_log['details']), true);
                    }
                    $details['contacts'] = AlertUtil::getContacts($qry);
                    $details['rule'] = $qry;
                    $details = gzcompress(json_encode($details), 9);
                    dbUpdate(['details' => $details], 'alert_log', 'id = ?', [$alert_log['id']]);
                } else {
                    $extra = gzcompress(json_encode(['contacts' => AlertUtil::getContacts($qry), 'rule'=>$qry]), 9);
                    if (dbInsert(['state' => AlertState::ACTIVE, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'details' => $extra], 'alert_log')) {
                        if (is_null($current_state)) {
                            dbInsert(['state' => AlertState::ACTIVE, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0], 'alerts');
                        } else {
                            dbUpdate(['state' => AlertState::ACTIVE, 'open' => 1, 'timestamp' => Carbon::now()], 'alerts', 'device_id = ? && rule_id = ?', [$device_id, $rule['id']]);
                        }
                        c_echo(PHP_EOL . 'Status: %rALERT');
                    }
                }
            } else {
                if (! is_null($current_state) && $current_state == AlertState::RECOVERED) {
                    c_echo('Status: %bNOCHG');
                } else {
                    if (dbInsert(['state' => AlertState::RECOVERED, 'device_id' => $device_id, 'rule_id' => $rule['id']], 'alert_log')) {
                        if (is_null($current_state)) {
                            dbInsert(['state' => AlertState::RECOVERED, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0], 'alerts');
                        } else {
                            dbUpdate(['state' => AlertState::RECOVERED, 'open' => 1, 'note' => '', 'timestamp' => Carbon::now()], 'alerts', 'device_id = ? && rule_id = ?', [$device_id, $rule['id']]);
                        }

                        c_echo(PHP_EOL . 'Status: %gOK');
                    }
                }
            }
            c_echo('%n' . PHP_EOL);
        }
    }
}
