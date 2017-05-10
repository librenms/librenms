#!/usr/bin/env php
<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Alerts Cronjob
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$init_modules = array('alerts');
require __DIR__ . '/includes/init.php';

$options = getopt('d::');

set_lock('alerts');

if (isset($options['d'])) {
    echo "DEBUG!\n";
    $debug = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', 1);
} else {
    $debug = false;
    // ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    // ini_set('error_reporting', 0);
}

if (!defined('TEST') && $config['alert']['disable'] != 'true') {
    echo 'Start: '.date('r')."\r\n";
    echo "ClearStaleAlerts():" . PHP_EOL;
    ClearStaleAlerts();
    echo "RunFollowUp():\r\n";
    RunFollowUp();
    echo "RunAlerts():\r\n";
    RunAlerts();
    echo "RunAcks():\r\n";
    RunAcks();
    echo 'End  : '.date('r')."\r\n";
}

release_lock('alerts');

function ClearStaleAlerts()
{
    $sql = "SELECT `alerts`.`id` AS `alert_id`, `devices`.`hostname` AS `hostname` FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE `alerts`.`state`!=0 AND `devices`.`hostname` IS NULL";
    foreach (dbFetchRows($sql) as $alert) {
        if (empty($alert['hostname']) && isset($alert['alert_id'])) {
            dbDelete('alerts', '`id` = ?', array($alert['alert_id']));
            echo "Stale-alert: #{$alert['alert_id']}" . PHP_EOL;
        }
    }
}

/**
 * Re-Validate Rule-Mappings
 * @param integer $device Device-ID
 * @param integer $rule   Rule-ID
 * @return boolean
 */
function IsRuleValid($device, $rule)
{
    global $rulescache;
    if (empty($rulescache[$device]) || !isset($rulescache[$device])) {
        foreach (GetRules($device) as $chk) {
            $rulescache[$device][$chk['id']] = true;
        }
    }

    if ($rulescache[$device][$rule] === true) {
        return true;
    }

    return false;
}//end IsRuleValid()


/**
 * Issue Alert-Object
 * @param array $alert
 * @return boolean
 */
function IssueAlert($alert)
{
    global $config;
    if (dbFetchCell('SELECT attrib_value FROM devices_attribs WHERE attrib_type = "disable_notify" && device_id = ?', array($alert['device_id'])) == '1') {
        return true;
    }

    if ($config['alert']['fixed-contacts'] == false) {
        if (empty($alert['query'])) {
            $alert['query'] = GenSQL($alert['rule']);
        }
        $sql = $alert['query'];
        $qry = dbFetchRows($sql, array($alert['device_id']));
        $alert['details']['contacts'] = GetContacts($qry);
    }

    $obj = DescribeAlert($alert);
    if (is_array($obj)) {
        echo 'Issuing Alert-UID #'.$alert['id'].'/'.$alert['state'].': ';
        if (!empty($config['alert']['transports'])) {
            ExtTransports($obj);
        }

        echo "\r\n";
    }

    return true;
}//end IssueAlert()


/**
 * Issue ACK notification
 * @return void
 */
function RunAcks()
{
    foreach (dbFetchRows('SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts WHERE alerts.state = 2 && alerts.open = 1') as $alert) {
        $tmp   = array(
            $alert['rule_id'],
            $alert['device_id'],
        );
        $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($alert['device_id'], $alert['rule_id']));
        if (empty($alert['rule']) || !IsRuleValid($tmp[1], $tmp[0])) {
            // Alert-Rule does not exist anymore, let's remove the alert-state.
            echo 'Stale-Rule: #'.$tmp[0].'/'.$tmp[1]."\r\n";
            dbDelete('alerts', 'rule_id = ? && device_id = ?', array($tmp[0], $tmp[1]));
            continue;
        }

        $alert['details'] = json_decode(gzuncompress($alert['details']), true);
        $alert['state']   = 2;
        IssueAlert($alert);
        dbUpdate(array('open' => 0), 'alerts', 'rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
    }
}//end RunAcks()


/**
 * Run Follow-Up alerts
 * @return void
 */
function RunFollowUp()
{
    global $config;
    foreach (dbFetchRows('SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts WHERE alerts.state != 2 && alerts.state > 0 && alerts.open = 0') as $alert) {
        $tmp   = array(
            $alert['rule_id'],
            $alert['device_id'],
        );
        $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule, alert_rules.query,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($alert['device_id'], $alert['rule_id']));
        if (empty($alert['rule']) || !IsRuleValid($tmp[1], $tmp[0])) {
            // Alert-Rule does not exist anymore, let's remove the alert-state.
            echo 'Stale-Rule: #'.$tmp[0].'/'.$tmp[1]."\r\n";
            dbDelete('alerts', 'rule_id = ? && device_id = ?', array($tmp[0], $tmp[1]));
            continue;
        }

        $alert['details'] = json_decode(gzuncompress($alert['details']), true);
        $rextra           = json_decode($alert['extra'], true);
        if ($rextra['invert']) {
            continue;
        }

        if (empty($alert['query'])) {
            $alert['query'] = GenSQL($alert['rule']);
        }
        $chk   = dbFetchRows($alert['query'], array($alert['device_id']));
        //make sure we can json_encode all the datas later
        $cnt = count($chk);
        for ($i = 0; $i < $cnt; $i++) {
            if (isset($chk[$i]['ip'])) {
                $chk[$i]['ip'] = inet6_ntop($chk[$i]['ip']);
            }
        }
        $o     = sizeof($alert['details']['rule']);
        $n     = sizeof($chk);
        $ret   = 'Alert #'.$alert['id'];
        $state = 0;
        if ($n > $o) {
            $ret  .= ' Worsens';
            $state = 3;
            $alert['details']['diff'] = array_diff($chk, $alert['details']['rule']);
        } elseif ($n < $o) {
            $ret  .= ' Betters';
            $state = 4;
            $alert['details']['diff'] = array_diff($alert['details']['rule'], $chk);
        }

        if ($state > 0 && $n > 0) {
            $alert['details']['rule'] = $chk;
            if (dbInsert(array('state' => $state, 'device_id' => $alert['device_id'], 'rule_id' => $alert['rule_id'], 'details' => gzcompress(json_encode($alert['details']), 9)), 'alert_log')) {
                dbUpdate(array('state' => $state, 'open' => 1, 'alerted' => 1), 'alerts', 'rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
            }

            echo $ret.' ('.$o.'/'.$n.")\r\n";
        }
    }//end foreach
}//end RunFollowUp()


/**
 * Run all alerts
 * @return void
 */
function RunAlerts()
{
    global $config;
    foreach (dbFetchRows('SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts WHERE alerts.state != 2 && alerts.open = 1') as $alert) {
        $tmp   = array(
            $alert['rule_id'],
            $alert['device_id'],
        );
        $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($alert['device_id'], $alert['rule_id']));
        if (empty($alert['rule_id']) || !IsRuleValid($tmp[1], $tmp[0])) {
            echo 'Stale-Rule: #'.$tmp[0].'/'.$tmp[1]."\r\n";
            // Alert-Rule does not exist anymore, let's remove the alert-state.
            dbDelete('alerts', 'rule_id = ? && device_id = ?', array($tmp[0], $tmp[1]));
            continue;
        }

        $alert['details'] = json_decode(gzuncompress($alert['details']), true);
        $noiss            = false;
        $noacc            = false;
        $updet            = false;
        $rextra           = json_decode($alert['extra'], true);
        $chk              = dbFetchRow('SELECT alerts.alerted,devices.ignore,devices.disabled FROM alerts,devices WHERE alerts.device_id = ? && devices.device_id = alerts.device_id && alerts.rule_id = ?', array($alert['device_id'], $alert['rule_id']));
        if ($chk['alerted'] == $alert['state']) {
            $noiss = true;
        }

        if (!empty($rextra['count']) && empty($rextra['interval'])) {
            // This check below is for compat-reasons
            if (!empty($rextra['delay'])) {
                if ((time() - strtotime($alert['time_logged']) + $config['alert']['tolerance_window']) < $rextra['delay'] || (!empty($alert['details']['delay']) && (time() - $alert['details']['delay'] + $config['alert']['tolerance_window']) < $rextra['delay'])) {
                    continue;
                } else {
                    $alert['details']['delay'] = time();
                    $updet = true;
                }
            }

            if ($alert['state'] == 1 && !empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count'])) {
                if ($alert['details']['count'] < $rextra['count']) {
                    $noacc = true;
                }

                $updet = true;
                $noiss = false;
            }
        } else {
            // This is the new way
            if (!empty($rextra['delay']) && (time() - strtotime($alert['time_logged']) + $config['alert']['tolerance_window']) < $rextra['delay']) {
                continue;
            }

            if (!empty($rextra['interval'])) {
                if (!empty($alert['details']['interval']) && (time() - $alert['details']['interval'] + $config['alert']['tolerance_window']) < $rextra['interval']) {
                    continue;
                } else {
                    $alert['details']['interval'] = time();
                    $updet = true;
                }
            }

            if ($alert['state'] == 1 && !empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count'])) {
                if ($alert['details']['count'] < $rextra['count']) {
                    $noacc = true;
                }

                $updet = true;
                $noiss = false;
            }
        }//end if
        if ($chk['ignore'] == 1 || $chk['disabled'] == 1) {
            $noiss = true;
            $updet = false;
            $noacc = false;
        }

        if (IsMaintenance($alert['device_id']) > 0) {
            $noiss = true;
            $noacc = true;
        }

        if ($updet) {
            dbUpdate(array('details' => gzcompress(json_encode($alert['details']), 9)), 'alert_log', 'id = ?', array($alert['id']));
        }

        if (!empty($rextra['mute'])) {
            echo 'Muted Alert-UID #'.$alert['id']."\r\n";
            $noiss = true;
        }

        if (!$noiss) {
            IssueAlert($alert);
            dbUpdate(array('alerted' => $alert['state']), 'alerts', 'rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
        }

        if (!$noacc) {
            dbUpdate(array('open' => 0), 'alerts', 'rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
        }
    }//end foreach
}//end RunAlerts()


/**
 * Run external transports
 * @param array $obj Alert-Array
 * @return void
 */
function ExtTransports($obj)
{
    global $config;
    $tmp = false;
    // To keep scrutinizer from naging because it doesnt understand eval
    foreach ($config['alert']['transports'] as $transport => $opts) {
        if (is_array($opts)) {
            $opts = array_filter($opts);
        }
        if (($opts === true || !empty($opts)) && $opts != false && file_exists($config['install_dir'].'/includes/alerts/transport.'.$transport.'.php')) {
            $obj['transport'] = $transport;
            $msg        = FormatAlertTpl($obj);
            $obj['msg'] = $msg;
            echo $transport.' => ';
            eval('$tmp = function($obj,$opts) { global $config; '.file_get_contents($config['install_dir'].'/includes/alerts/transport.'.$transport.'.php').' return false; };');
            $tmp = $tmp($obj,$opts);
            $prefix = array( 0=>"recovery", 1=>$obj['severity']." alert", 2=>"acknowledgment" );
            $prefix[3] = &$prefix[0];
            $prefix[4] = &$prefix[0];
            if ($tmp === true) {
                echo 'OK';
                log_event('Issued ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], null, 1);
            } elseif ($tmp === false) {
                echo 'ERROR';
                log_event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], null, 5);
            } else {
                echo 'ERROR: '.$tmp."\r\n";
                log_event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "' Error: " . $tmp, $obj['device_id'], null, 5);
            }
        }

        echo '; ';
    }
}//end ExtTransports()
