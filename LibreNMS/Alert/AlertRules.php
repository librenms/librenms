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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Original Code:
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 *
 * Modeified by:
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use App\Models\Device;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Alert\AlertDB;

class AlertRules
{
    public function runRules($device_id)
    {

        //Check to see if under maintenance
        if (AlertUtil::isMaintenance($device_id) > 0) {
            echo "Under Maintenance, Skipping alerts.\r\n";
            return false;
        }
        //Checks each rule.
        foreach (AlertUtil::getRules($device_id) as $rule) {
            c_echo('Rule %p#'.$rule['id'].' (' . $rule['name'] . '):%n ');
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
            $qry = dbFetchRows($sql, array($device_id));
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

            $current_state = dbFetchCell("SELECT state FROM alerts WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1", [$rule['id'], $device_id]);
            if ($doalert) {
                if ($current_state == 2) {
                    c_echo('Status: %ySKIP');
                } elseif ($current_state >= 1) {
                    c_echo('Status: %bNOCHG');
                    // NOCHG here doesn't mean no change full stop. It means no change to the alert state
                    // So we update the details column with any fresh changes to the alert output we might have.
                    $alert_log = dbFetchRow('SELECT alert_log.id, alert_log.details FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0
     ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule['id']));
                    $details = [];
                    if (!empty($alert_log['details'])) {
                         $details = json_decode(gzuncompress($alert_log['details']), true);
                    }
                    $details['contacts'] = AlertUtil::getContacts($qry);
                    $details['rule']     = $qry;
                    $details             = gzcompress(json_encode($details), 9);
                    dbUpdate(array('details' => $details), 'alert_log', 'id = ?', array($alert_log['id']));
                } else {
                    $extra = gzcompress(json_encode(array('contacts' => AlertUtil::getContacts($qry), 'rule'=>$qry)), 9);
                    if (dbInsert(['state' => 1, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'details' => $extra], 'alert_log')) {
                        if (is_null($current_state)) {
                            dbInsert(array('state' => 1, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1,'alerted' => 0), 'alerts');
                        } else {
                            dbUpdate(['state' => 1, 'open' => 1], 'alerts', 'device_id = ? && rule_id = ?', [$device_id, $rule['id']]);
                        }
                        c_echo(PHP_EOL . 'Status: %rALERT');
                    }
                }
            } else {
                if (!is_null($current_state) && $current_state == 0) {
                    c_echo('Status: %bNOCHG');
                } else {
                    if (dbInsert(['state' => 0, 'device_id' => $device_id, 'rule_id' => $rule['id']], 'alert_log')) {
                        if (is_null($current_state)) {
                            dbInsert(['state' => 0, 'device_id' => $device_id, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0], 'alerts');
                        } else {
                            dbUpdate(['state' => 0, 'open' => 1, 'note' => ''], 'alerts', 'device_id = ? && rule_id = ?', [$device_id, $rule['id']]);
                        }

                        c_echo(PHP_EOL . 'Status: %gOK');
                    }
                }
            }
            c_echo('%n' . PHP_EOL);
        }
    }
}
