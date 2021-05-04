<?php
/*
 * RunAlerts.php
 *
 * Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * @package LibreNMS
 * @subpackage Alerts
 *
 * Modified by:
 * @author Heath Barnhart <barnhart@kanren.net>
 *
 */

namespace LibreNMS\Alert;

use App\Models\DevicePerf;
use LibreNMS\Config;
use LibreNMS\Enum\Alert;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;
use Log;

class RunAlerts
{
    /**
     * Populate variables
     * @param string  $txt  Text with variables
     * @param bool $wrap Wrap variable for text-usage (default: true)
     * @return string
     */
    public function populate($txt, $wrap = true)
    {
        preg_match_all('/%([\w\.]+)/', $txt, $m);
        foreach ($m[1] as $tmp) {
            $orig = $tmp;
            $rep = false;
            if ($tmp == 'key' || $tmp == 'value') {
                $rep = '$' . $tmp;
            } else {
                if (strstr($tmp, '.')) {
                    $tmp = explode('.', $tmp, 2);
                    $pre = '$' . $tmp[0];
                    $tmp = $tmp[1];
                } else {
                    $pre = '$obj';
                }

                $rep = $pre . "['" . str_replace('.', "']['", $tmp) . "']";
                if ($wrap) {
                    $rep = '{' . $rep . '}';
                }
            }

            $txt = str_replace('%' . $orig, $rep, $txt);
        }

        return $txt;
    }

    /**
     * Describe Alert
     * @param array $alert Alert-Result from DB
     * @return array|bool|string
     */
    public function describeAlert($alert)
    {
        $obj = [];
        $i = 0;
        $device = dbFetchRow('SELECT hostname, sysName, sysDescr, sysContact, os, type, ip, hardware, version, serial, features, purpose, notes, uptime, status, status_reason, locations.location FROM devices LEFT JOIN locations ON locations.id = devices.location_id WHERE device_id = ?', [$alert['device_id']]);
        $attribs = get_dev_attribs($alert['device_id']);

        $obj['hostname'] = $device['hostname'];
        $obj['sysName'] = $device['sysName'];
        $obj['sysDescr'] = $device['sysDescr'];
        $obj['sysContact'] = $device['sysContact'];
        $obj['os'] = $device['os'];
        $obj['type'] = $device['type'];
        $obj['ip'] = inet6_ntop($device['ip']);
        $obj['hardware'] = $device['hardware'];
        $obj['version'] = $device['version'];
        $obj['serial'] = $device['serial'];
        $obj['features'] = $device['features'];
        $obj['location'] = $device['location'];
        $obj['uptime'] = $device['uptime'];
        $obj['uptime_short'] = Time::formatInterval($device['uptime'], 'short');
        $obj['uptime_long'] = Time::formatInterval($device['uptime']);
        $obj['description'] = $device['purpose'];
        $obj['notes'] = $device['notes'];
        $obj['alert_notes'] = $alert['note'];
        $obj['device_id'] = $alert['device_id'];
        $obj['rule_id'] = $alert['rule_id'];
        $obj['id'] = $alert['id'];
        $obj['proc'] = $alert['proc'];
        $obj['status'] = $device['status'];
        $obj['status_reason'] = $device['status_reason'];
        if (can_ping_device($attribs)) {
            $ping_stats = DevicePerf::where('device_id', $alert['device_id'])->latest('timestamp')->first();
            $obj['ping_timestamp'] = $ping_stats->timestamp;
            $obj['ping_loss'] = $ping_stats->loss;
            $obj['ping_min'] = $ping_stats->min;
            $obj['ping_max'] = $ping_stats->max;
            $obj['ping_avg'] = $ping_stats->avg;
            $obj['debug'] = json_decode($ping_stats->debug, true);
        }
        $extra = $alert['details'];

        $tpl = new Template;
        $template = $tpl->getTemplate($obj);

        if ($alert['state'] >= AlertState::ACTIVE) {
            $obj['title'] = $template->title ?: 'Alert for device ' . $device['hostname'] . ' - ' . ($alert['name'] ? $alert['name'] : $alert['rule']);
            if ($alert['state'] == AlertState::ACKNOWLEDGED) {
                $obj['title'] .= ' got acknowledged';
            } elseif ($alert['state'] == AlertState::WORSE) {
                $obj['title'] .= ' got worse';
            } elseif ($alert['state'] == AlertState::BETTER) {
                $obj['title'] .= ' got better';
            }

            foreach ($extra['rule'] as $incident) {
                $i++;
                $obj['faults'][$i] = $incident;
                $obj['faults'][$i]['string'] = null;
                foreach ($incident as $k => $v) {
                    if (! empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                        $obj['faults'][$i]['string'] .= $k . ' = ' . $v . '; ';
                    }
                }
            }
            $obj['elapsed'] = $this->timeFormat(time() - strtotime($alert['time_logged']));
            if (! empty($extra['diff'])) {
                $obj['diff'] = $extra['diff'];
            }
        } elseif ($alert['state'] == AlertState::RECOVERED) {
            // Alert is now cleared
            $id = dbFetchRow('SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state != ? && alert_log.state != ? && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1', [AlertState::ACKNOWLEDGED, AlertState::RECOVERED, $alert['rule_id'], $alert['device_id'], $alert['id']]);
            if (empty($id['id'])) {
                return false;
            }

            $extra = [];
            if (! empty($id['details'])) {
                $extra = json_decode(gzuncompress($id['details']), true);
            }

            // Reset count to 0 so alerts will continue
            $extra['count'] = 0;
            dbUpdate(['details' => gzcompress(json_encode($id['details']), 9)], 'alert_log', 'id = ?', [$alert['id']]);

            $obj['title'] = $template->title_rec ?: 'Device ' . $device['hostname'] . ' recovered from ' . ($alert['name'] ? $alert['name'] : $alert['rule']);
            $obj['elapsed'] = $this->timeFormat(strtotime($alert['time_logged']) - strtotime($id['time_logged']));
            $obj['id'] = $id['id'];
            foreach ($extra['rule'] as $incident) {
                $i++;
                $obj['faults'][$i] = $incident;
                foreach ($incident as $k => $v) {
                    if (! empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                        $obj['faults'][$i]['string'] .= $k . ' => ' . $v . '; ';
                    }
                }
            }
        } else {
            return 'Unknown State';
        }//end if
        $obj['builder'] = $alert['builder'];
        $obj['uid'] = $alert['id'];
        $obj['alert_id'] = $alert['alert_id'];
        $obj['severity'] = $alert['severity'];
        $obj['rule'] = $alert['rule'];
        $obj['name'] = $alert['name'];
        $obj['timestamp'] = $alert['time_logged'];
        $obj['contacts'] = $extra['contacts'];
        $obj['state'] = $alert['state'];
        $obj['alerted'] = $alert['alerted'];
        $obj['template'] = $template;

        return $obj;
    }

    /**
     * Format Elapsed Time
     * @param int $secs Seconds elapsed
     * @return string
     */
    public function timeFormat($secs)
    {
        $bit = [
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60,
        ];
        $ret = [];
        foreach ($bit as $k => $v) {
            if ($v > 0) {
                $ret[] = $v . $k;
            }
        }

        if (empty($ret)) {
            return 'none';
        }

        return join(' ', $ret);
    }

    public function clearStaleAlerts()
    {
        $sql = 'SELECT `alerts`.`id` AS `alert_id`, `devices`.`hostname` AS `hostname` FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE `alerts`.`state`!=' . AlertState::CLEAR . ' AND `devices`.`hostname` IS NULL';
        foreach (dbFetchRows($sql) as $alert) {
            if (empty($alert['hostname']) && isset($alert['alert_id'])) {
                dbDelete('alerts', '`id` = ?', [$alert['alert_id']]);
                echo "Stale-alert: #{$alert['alert_id']}" . PHP_EOL;
            }
        }
    }

    /**
     * Re-Validate Rule-Mappings
     * @param int $device_id Device-ID
     * @param int $rule   Rule-ID
     * @return bool
     */
    public function isRuleValid($device_id, $rule)
    {
        global $rulescache;
        if (empty($rulescache[$device_id]) || ! isset($rulescache[$device_id])) {
            foreach (AlertUtil::getRules($device_id) as $chk) {
                $rulescache[$device_id][$chk['id']] = true;
            }
        }

        if ($rulescache[$device_id][$rule] === true) {
            return true;
        }

        return false;
    }

    /**
     * Issue Alert-Object
     * @param array $alert
     * @return bool
     */
    public function issueAlert($alert)
    {
        if (Config::get('alert.fixed-contacts') == false) {
            if (empty($alert['query'])) {
                $alert['query'] = AlertDB::genSQL($alert['rule'], $alert['builder']);
            }
            $sql = $alert['query'];
            $qry = dbFetchRows($sql, [$alert['device_id']]);
            $alert['details']['contacts'] = AlertUtil::getContacts($qry);
        }

        $obj = $this->describeAlert($alert);
        if (is_array($obj)) {
            echo 'Issuing Alert-UID #' . $alert['id'] . '/' . $alert['state'] . ':' . PHP_EOL;
            $this->extTransports($obj);

            echo "\r\n";
        }

        return true;
    }

    /**
     * Issue ACK notification
     * @return void
     */
    public function runAcks()
    {
        foreach ($this->loadAlerts('alerts.state = ' . AlertState::ACKNOWLEDGED . ' && alerts.open = ' . AlertState::ACTIVE) as $alert) {
            $this->issueAlert($alert);
            dbUpdate(['open' => AlertState::CLEAR], 'alerts', 'rule_id = ? && device_id = ?', [$alert['rule_id'], $alert['device_id']]);
        }
    }

    /**
     * Run Follow-Up alerts
     * @return void
     */
    public function runFollowUp()
    {
        foreach ($this->loadAlerts('alerts.state > ' . AlertState::CLEAR . ' && alerts.open = 0') as $alert) {
            if ($alert['state'] != AlertState::ACKNOWLEDGED || ($alert['info']['until_clear'] === false)) {
                $rextra = json_decode($alert['extra'], true);
                if ($rextra['invert']) {
                    continue;
                }

                if (empty($alert['query'])) {
                    $alert['query'] = AlertDB::genSQL($alert['rule'], $alert['builder']);
                }
                $chk = dbFetchRows($alert['query'], [$alert['device_id']]);
                //make sure we can json_encode all the datas later
                $cnt = count($chk);
                for ($i = 0; $i < $cnt; $i++) {
                    if (isset($chk[$i]['ip'])) {
                        $chk[$i]['ip'] = inet6_ntop($chk[$i]['ip']);
                    }
                }
                $o = sizeof($alert['details']['rule']);
                $n = sizeof($chk);
                $ret = 'Alert #' . $alert['id'];
                $state = AlertState::CLEAR;
                if ($n > $o) {
                    $ret .= ' Worsens';
                    $state = AlertState::WORSE;
                    $alert['details']['diff'] = array_diff($chk, $alert['details']['rule']);
                } elseif ($n < $o) {
                    $ret .= ' Betters';
                    $state = AlertState::BETTER;
                    $alert['details']['diff'] = array_diff($alert['details']['rule'], $chk);
                }

                if ($state > AlertState::CLEAR && $n > 0) {
                    $alert['details']['rule'] = $chk;
                    if (dbInsert([
                        'state' => $state,
                        'device_id' => $alert['device_id'],
                        'rule_id' => $alert['rule_id'],
                        'details' => gzcompress(json_encode($alert['details']), 9),
                    ], 'alert_log')) {
                        dbUpdate(['state' => $state, 'open' => 1, 'alerted' => 1], 'alerts', 'rule_id = ? && device_id = ?', [$alert['rule_id'], $alert['device_id']]);
                    }

                    echo $ret . ' (' . $o . '/' . $n . ")\r\n";
                }
            }
        }
    }

    public function loadAlerts($where)
    {
        $alerts = [];
        foreach (dbFetchRows("SELECT alerts.id, alerts.alerted, alerts.device_id, alerts.rule_id, alerts.state, alerts.note, alerts.info FROM alerts WHERE $where") as $alert_status) {
            $alert = dbFetchRow(
                'SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name,alert_rules.query,alert_rules.builder,alert_rules.proc FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1',
                [$alert_status['device_id'], $alert_status['rule_id']]
            );

            if (empty($alert['rule_id']) || ! $this->isRuleValid($alert_status['device_id'], $alert_status['rule_id'])) {
                echo 'Stale-Rule: #' . $alert_status['rule_id'] . '/' . $alert_status['device_id'] . "\r\n";
                // Alert-Rule does not exist anymore, let's remove the alert-state.
                dbDelete('alerts', 'rule_id = ? && device_id = ?', [$alert_status['rule_id'], $alert_status['device_id']]);
            } else {
                $alert['alert_id'] = $alert_status['id'];
                $alert['state'] = $alert_status['state'];
                $alert['alerted'] = $alert_status['alerted'];
                $alert['note'] = $alert_status['note'];
                if (! empty($alert['details'])) {
                    $alert['details'] = json_decode(gzuncompress($alert['details']), true);
                }
                $alert['info'] = json_decode($alert_status['info'], true);
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Run all alerts
     * @return void
     */
    public function runAlerts()
    {
        foreach ($this->loadAlerts('alerts.state != ' . AlertState::ACKNOWLEDGED . ' && alerts.open = 1') as $alert) {
            $noiss = false;
            $noacc = false;
            $updet = false;
            $rextra = json_decode($alert['extra'], true);
            if (! isset($rextra['recovery'])) {
                // backwards compatibility check
                $rextra['recovery'] = true;
            }

            $chk = dbFetchRow('SELECT alerts.alerted,devices.ignore,devices.disabled FROM alerts,devices WHERE alerts.device_id = ? && devices.device_id = alerts.device_id && alerts.rule_id = ?', [$alert['device_id'], $alert['rule_id']]);

            if ($chk['alerted'] == $alert['state']) {
                $noiss = true;
            }

            $tolerence_window = Config::get('alert.tolerance_window');
            if (! empty($rextra['count']) && empty($rextra['interval'])) {
                // This check below is for compat-reasons
                if (! empty($rextra['delay']) && $alert['state'] != AlertState::RECOVERED) {
                    if ((time() - strtotime($alert['time_logged']) + $tolerence_window) < $rextra['delay'] || (! empty($alert['details']['delay']) && (time() - $alert['details']['delay'] + $tolerence_window) < $rextra['delay'])) {
                        continue;
                    } else {
                        $alert['details']['delay'] = time();
                        $updet = true;
                    }
                }

                if ($alert['state'] == AlertState::ACTIVE && ! empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count'])) {
                    if ($alert['details']['count'] < $rextra['count']) {
                        $noacc = true;
                    }

                    $updet = true;
                    $noiss = false;
                }
            } else {
                // This is the new way
                if (! empty($rextra['delay']) && (time() - strtotime($alert['time_logged']) + $tolerence_window) < $rextra['delay'] && $alert['state'] != AlertState::RECOVERED) {
                    continue;
                }

                if (! empty($rextra['interval'])) {
                    if (! empty($alert['details']['interval']) && (time() - $alert['details']['interval'] + $tolerence_window) < $rextra['interval']) {
                        continue;
                    } else {
                        $alert['details']['interval'] = time();
                        $updet = true;
                    }
                }

                if (in_array($alert['state'], [AlertState::ACTIVE, AlertState::WORSE, AlertState::BETTER]) && ! empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count'])) {
                    if ($alert['details']['count'] < $rextra['count']) {
                        $noacc = true;
                    }

                    $updet = true;
                    $noiss = false;
                }
            }
            if ($chk['ignore'] == 1 || $chk['disabled'] == 1) {
                $noiss = true;
                $updet = false;
                $noacc = false;
            }

            if (AlertUtil::isMaintenance($alert['device_id'])) {
                $noiss = true;
                $noacc = true;
            }

            if ($updet) {
                dbUpdate(['details' => gzcompress(json_encode($alert['details']), 9)], 'alert_log', 'id = ?', [$alert['id']]);
            }

            if (! empty($rextra['mute'])) {
                echo 'Muted Alert-UID #' . $alert['id'] . "\r\n";
                $noiss = true;
            }

            if ($this->isParentDown($alert['device_id'])) {
                $noiss = true;
                Log::event('Skipped alerts because all parent devices are down', $alert['device_id'], 'alert', 1);
            }

            if ($alert['state'] == AlertState::RECOVERED && $rextra['recovery'] == false) {
                // Rule is set to not send a recovery alert
                $noiss = true;
            }

            if (! $noiss) {
                $this->issueAlert($alert);
                dbUpdate(['alerted' => $alert['state']], 'alerts', 'rule_id = ? && device_id = ?', [$alert['rule_id'], $alert['device_id']]);
            }

            if (! $noacc) {
                dbUpdate(['open' => 0], 'alerts', 'rule_id = ? && device_id = ?', [$alert['rule_id'], $alert['device_id']]);
            }
        }
    }

    /**
     * Run external transports
     * @param array $obj Alert-Array
     * @return void
     */
    public function extTransports($obj)
    {
        $type = new Template;

        // If alert transport mapping exists, override the default transports
        $transport_maps = AlertUtil::getAlertTransports($obj['alert_id']);

        if (! $transport_maps) {
            $transport_maps = AlertUtil::getDefaultAlertTransports();
        }

        // alerting for default contacts, etc
        if (Config::get('alert.transports.mail') === true && ! empty($obj['contacts'])) {
            $transport_maps[] = [
                'transport_id' => null,
                'transport_type' => 'mail',
                'opts' => $obj,
            ];
        }

        foreach ($transport_maps as $item) {
            $class = 'LibreNMS\\Alert\\Transport\\' . ucfirst($item['transport_type']);
            if (class_exists($class)) {
                //FIXME remove Deprecated transport
                $transport_title = "Transport {$item['transport_type']}";
                $obj['transport'] = $item['transport_type'];
                $obj['transport_name'] = $item['transport_name'];
                $obj['alert'] = new AlertData($obj);
                $obj['title'] = $type->getTitle($obj);
                $obj['alert']['title'] = $obj['title'];
                $obj['msg'] = $type->getBody($obj);
                c_echo(" :: $transport_title => ");
                try {
                    $instance = new $class($item['transport_id']);
                    $tmp = $instance->deliverAlert($obj, $item['opts']);
                    $this->alertLog($tmp, $obj, $obj['transport']);
                } catch (\Exception $e) {
                    $this->alertLog($e, $obj, $obj['transport']);
                }
                unset($instance);
                echo PHP_EOL;
            }
        }

        if (count($transport_maps) === 0) {
            echo 'No configured transports';
        }
    }

    // Log alert event
    public function alertLog($result, $obj, $transport)
    {
        $prefix = [
            AlertState::RECOVERED => 'recovery',
            AlertState::ACTIVE => $obj['severity'] . ' alert',
            AlertState::ACKNOWLEDGED => 'acknowledgment',
        ];
        $prefix[3] = &$prefix[0];
        $prefix[4] = &$prefix[0];

        if ($obj['state'] == AlertState::RECOVERED) {
            $severity = Alert::OK;
        } elseif ($obj['state'] == AlertState::ACTIVE) {
            $severity = Alert::SEVERITIES[$obj['severity']] ?? Alert::UNKNOWN;
        } elseif ($obj['state'] == AlertState::ACKNOWLEDGED) {
            $severity = Alert::NOTICE;
        } else {
            $severity = Alert::UNKNOWN;
        }

        if ($result === true) {
            echo 'OK';
            Log::event('Issued ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], 'alert', $severity);
        } elseif ($result === false) {
            echo 'ERROR';
            Log::event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], null, Alert::ERROR);
        } else {
            echo "ERROR: $result\r\n";
            Log::event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "' Error: " . $result, $obj['device_id'], 'error', Alert::ERROR);
        }
    }

    /**
     * Check if a device's all parent are down
     * Returns true if all parents are down
     * @param int $device Device-ID
     * @return bool
     */
    public function isParentDown($device)
    {
        $parent_count = dbFetchCell('SELECT count(*) from `device_relationships` WHERE `child_device_id` = ?', [$device]);
        if (! $parent_count) {
            return false;
        }

        $down_parent_count = dbFetchCell("SELECT count(*) from devices as d LEFT JOIN devices_attribs as a ON d.device_id=a.device_id LEFT JOIN device_relationships as r ON d.device_id=r.parent_device_id WHERE d.status=0 AND d.ignore=0 AND d.disabled=0 AND r.child_device_id=? AND (d.status_reason='icmp' OR (a.attrib_type='override_icmp_disable' AND a.attrib_value=true))", [$device]);
        if ($down_parent_count == $parent_count) {
            return true;
        }

        return false;
    }
}
