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

require_once 'includes/defaults.inc.php';
require_once 'config.php';

$lock = false;
if (file_exists($config['install_dir'].'/.alerts.lock')) {
    $pids = explode("\n", trim(`ps -e | grep php | awk '{print $1}'`));
    $lpid = trim(file_get_contents($config['install_dir'].'/.alerts.lock'));
    if (in_array($lpid, $pids)) {
        $lock = true;
    }
}

if ($lock === true) {
    exit(1);
}
else {
    file_put_contents($config['install_dir'].'/.alerts.lock', getmypid());
}

require_once $config['install_dir'].'/includes/definitions.inc.php';
require_once $config['install_dir'].'/includes/functions.php';
require_once $config['install_dir'].'/includes/alerts.inc.php';

if (!defined('TEST') && $config['alert']['disable'] != 'true') {
    echo 'Start: '.date('r')."\r\n";
    echo "RunFollowUp():\r\n";
    RunFollowUp();
    echo "RunAlerts():\r\n";
    RunAlerts();
    echo "RunAcks():\r\n";
    RunAcks();
    echo 'End  : '.date('r')."\r\n";
}

unlink($config['install_dir'].'/.alerts.lock');


/**
 * Re-Validate Rule-Mappings
 * @param integer $device Device-ID
 * @param integer $rule   Rule-ID
 * @return boolean
 */
function IsRuleValid($device, $rule) {
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
function IssueAlert($alert) {
    global $config;
    if (dbFetchCell('SELECT attrib_value FROM devices_attribs WHERE attrib_type = "disable_notify" && device_id = ?', array($alert['device_id'])) == '1') {
        return true;
    }

    if ($config['alert']['fixed-contacts'] == false) {
        $alert['details']['contacts'] = GetContacts($alert['details']['rule']);
    }

    $obj = DescribeAlert($alert);
    if (is_array($obj)) {
        echo 'Issuing Alert-UID #'.$alert['id'].'/'.$alert['state'].': ';
        $msg        = FormatAlertTpl($obj);
        $obj['msg'] = $msg;
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
function RunAcks() {
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
function RunFollowUp() {
    global $config;
    foreach (dbFetchRows('SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts WHERE alerts.state != 2 && alerts.state > 0 && alerts.open = 0') as $alert) {
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
        $rextra           = json_decode($alert['extra'], true);
        if ($rextra['invert']) {
            continue;
        }

        $chk   = dbFetchRows(GenSQL($alert['rule']), array($alert['device_id']));
        $o     = sizeof($alert['details']['rule']);
        $n     = sizeof($chk);
        $ret   = 'Alert #'.$alert['id'];
        $state = 0;
        if ($n > $o) {
            $ret  .= ' Worsens';
            $state = 3;
        }
        else if ($n < $o) {
            $ret  .= ' Betters';
            $state = 4;
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
function RunAlerts() {
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
                }
                else {
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
        }
        else {
            // This is the new way
            if (!empty($rextra['delay']) && (time() - strtotime($alert['time_logged']) + $config['alert']['tolerance_window']) < $rextra['delay']) {
                continue;
            }

            if (!empty($rextra['interval'])) {
                if (!empty($alert['details']['interval']) && (time() - $alert['details']['interval'] + $config['alert']['tolerance_window']) < $rextra['interval']) {
                    continue;
                }
                else {
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
function ExtTransports($obj) {
    global $config;
    $tmp = false;
    // To keep scrutinizer from naging because it doesnt understand eval
    foreach ($config['alert']['transports'] as $transport => $opts) {
        if (($opts === true || !empty($opts)) && $opts != false && file_exists($config['install_dir'].'/includes/alerts/transport.'.$transport.'.php')) {
            echo $transport.' => ';
            eval('$tmp = function($obj,$opts) { global $config; '.file_get_contents($config['install_dir'].'/includes/alerts/transport.'.$transport.'.php').' return false; };');
            $tmp = $tmp($obj,$opts);
            $prefix = array( 0=>"recovery", 1=>$obj['severity']." alert", 2=>"acknowledgment" );
            $prefix[3] = &$prefix[0];
            $prefix[4] = &$prefix[0];
            if ($tmp) {
                echo 'OK';
                log_event('Issued '.$prefix[$obj['state']]." for rule '".$obj['name']."' to transport '".$transport."'", $obj['device_id']);
            }
            else {
                echo 'ERROR';
                log_event('Could not issue '.$prefix[$obj['state']]." for rule '".$obj['name']."' to transport '".$transport."'", $obj['device_id']);
            }
        }

        echo '; ';
    }

}//end ExtTransports()


/**
 * Format Alert
 * @param array  $obj Alert-Array
 * @return string
 */
function FormatAlertTpl($obj) {
    $tpl    = $obj["template"];
    $msg    = '$ret .= "'.str_replace(array('{else}', '{/if}', '{/foreach}'), array('"; } else { $ret .= "', '"; } $ret .= "', '"; } $ret .= "'), addslashes($tpl)).'";';
    $parsed = $msg;
    $s      = strlen($msg);
    $x      = $pos = -1;
    $buff   = '';
    $if     = $for = false;
    while (++$x < $s) {
        if ($msg[$x] == '{' && $buff == '') {
            $buff .= $msg[$x];
        }
        else if ($buff == '{ ') {
            $buff = '';
        }
        else if ($buff != '') {
            $buff .= $msg[$x];
        }

        if ($buff == '{if') {
            $pos = $x;
            $if  = true;
        }
        else if ($buff == '{foreach') {
            $pos = $x;
            $for = true;
        }

        if ($pos != -1 && $msg[$x] == '}') {
            $orig = $buff;
            $buff = '';
            $pos  = -1;
            if ($if) {
                $if     = false;
                $o      = 3;
                $native = array(
                    '"; if( ',
                    ' ) { $ret .= "',
                );
            }
            else if ($for) {
                $for    = false;
                $o      = 8;
                $native = array(
                    '"; foreach( ',
                    ' as $key=>$value) { $ret .= "',
                );
            }
            else {
                continue;
            }

            $cond   = trim(populate(substr($orig, $o, -1), false));
            $native = $native[0].$cond.$native[1];
            $parsed = str_replace($orig, $native, $parsed);
            unset($cond, $o, $orig, $native);
        }//end if
    }//end while

    $parsed = populate($parsed);
    return RunJail($parsed, $obj);

}//end FormatAlertTpl()


/**
 * Describe Alert
 * @param array $alert Alert-Result from DB
 * @return array
 */
function DescribeAlert($alert) {
    $obj              = array();
    $i                = 0;
    $device           = dbFetchRow('SELECT hostname FROM devices WHERE device_id = ?', array($alert['device_id']));
    $tpl              = dbFetchRow('SELECT `template`,`title`,`title_rec` FROM `alert_templates` JOIN `alert_template_map` ON `alert_template_map`.`alert_templates_id`=`alert_templates`.`id` WHERE `alert_template_map`.`alert_rule_id`=?', array($alert['rule_id']));
    $default_tpl      = "%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}";
    $obj['hostname']  = $device['hostname'];
    $obj['device_id'] = $alert['device_id'];
    $extra            = $alert['details'];
    if (!isset($tpl['template'])) {
        $obj['template'] = $default_tpl;
    } else {
        $obj['template'] = $tpl['template'];
    }
    if ($alert['state'] >= 1) {
        if (!empty($tpl['title'])) {
	    $obj['title'] = $tpl['title'];
        }
        else {
            $obj['title'] = 'Alert for device '.$device['hostname'].' - '.($alert['name'] ? $alert['name'] : $alert['rule']);
        }
        if ($alert['state'] == 2) {
            $obj['title'] .= ' got acknowledged';
        }
        else if ($alert['state'] == 3) {
            $obj['title'] .= ' got worse';
        }
        else if ($alert['state'] == 4) {
            $obj['title'] .= ' got better';
        }

        foreach ($extra['rule'] as $incident) {
            $i++;
            $obj['faults'][$i] = $incident;
            foreach ($incident as $k => $v) {
                if (!empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                    $obj['faults'][$i]['string'] .= $k.' => '.$v.'; ';
                }
            }
        }
    }
    else if ($alert['state'] == 0) {
        $id = dbFetchRow('SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state != 2 && alert_log.state != 0 && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1', array($alert['rule_id'], $alert['device_id'], $alert['id']));
        if (empty($id['id'])) {
            return false;
        }

        $extra          = json_decode(gzuncompress($id['details']), true);
	if (!empty($tpl['title_rec'])) {
	    $obj['title'] = $tpl['title_rec'];
	}
	else {
            $obj['title']   = 'Device '.$device['hostname'].' recovered from '.($alert['name'] ? $alert['name'] : $alert['rule']);
        }
        $obj['elapsed'] = TimeFormat(strtotime($alert['time_logged']) - strtotime($id['time_logged']));
        $obj['id']      = $id['id'];
        $obj['faults']  = false;
    }
    else {
        return 'Unknown State';
    }//end if
    $obj['uid']       = $alert['id'];
    $obj['severity']  = $alert['severity'];
    $obj['rule']      = $alert['rule'];
    $obj['name']      = $alert['name'];
    $obj['timestamp'] = $alert['time_logged'];
    $obj['contacts']  = $extra['contacts'];
    $obj['state']     = $alert['state'];
    if (strstr($obj['title'],'%')) {
        $obj['title'] = RunJail('$ret = "'.populate(addslashes($obj['title'])).'";', $obj);
    }
    return $obj;

}//end DescribeAlert()


/**
 * Format Elapsed Time
 * @param integer $secs Seconds elapsed
 * @return string
 */
function TimeFormat($secs) {
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60,
    );
    $ret = array();
    foreach ($bit as $k => $v) {
        if ($v > 0) {
            $ret[] = $v.$k;
        }
    }

    if (empty($ret)) {
        return 'none';
    }

    return join(' ', $ret);

}//end TimeFormat()


/**
 * "Safely" run eval
 * @param string $code Code to run
 * @param array  $obj  Object with variables
 * @return string|mixed
 */
function RunJail($code, $obj) {
    $ret = '';
    eval($code);
    return $ret;

}//end RunJail()


/**
 * Populate variables
 * @param string  $txt  Text with variables
 * @param boolean $wrap Wrap variable for text-usage (default: true)
 * @return string
 */
function populate($txt, $wrap=true) {
    preg_match_all('/%([\w\.]+)/', $txt, $m);
    foreach ($m[1] as $tmp) {
        $orig = $tmp;
        $rep  = false;
        if ($tmp == 'key' || $tmp == 'value') {
            $rep = '$'.$tmp;
        }
        else {
            if (strstr($tmp, '.')) {
                $tmp = explode('.', $tmp, 2);
                $pre = '$'.$tmp[0];
                $tmp = $tmp[1];
            }
            else {
                $pre = '$obj';
            }

            $rep = $pre."['".str_replace('.', "']['", $tmp)."']";
            if ($wrap) {
                $rep = '{'.$rep.'}';
            }
        }

        $txt = str_replace('%'.$orig, $rep, $txt);
    }//end foreach

    return $txt;

}//end populate()
