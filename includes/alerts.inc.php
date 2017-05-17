<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/*
 * Alerts Tracking
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */


/**
 * Generate SQL from Rule
 * @param string $rule Rule to generate SQL for
 * @return string|boolean
 */
function GenSQL($rule)
{
    $rule = RunMacros($rule);
    if (empty($rule)) {
        //Cannot resolve Macros due to recursion. Rule is invalid.
        return false;
    }
    //Pretty-print rule to dissect easier
    $pretty = array('&&' => ' && ', '||' => ' || ');
    $rule = str_replace(array_keys($pretty), $pretty, $rule);
    $tmp = explode(" ", $rule);
    $tables = array();
    foreach ($tmp as $opt) {
        if (strstr($opt, '%') && strstr($opt, '.')) {
            $tmpp = explode(".", $opt, 2);
            $tmpp[0] = str_replace("%", "", $tmpp[0]);
            $tables[] = mres(str_replace("(", "", $tmpp[0]));
            $rule = str_replace($opt, $tmpp[0].'.'.$tmpp[1], $rule);
        }
    }
    $tables = array_keys(array_flip($tables));
    if (dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?', array($tables[0],'device_id')) != 1) {
        //Our first table has no valid glue, append the 'devices' table to it!
        array_unshift($tables, 'devices');
    }
    $x = sizeof($tables)-1;
    $i = 0;
    $join = "";
    while ($i < $x) {
        if (isset($tables[$i+1])) {
            $gtmp = ResolveGlues(array($tables[$i+1]), 'device_id');
            if ($gtmp === false) {
                //Cannot resolve glue-chain. Rule is invalid.
                return false;
            }
            $last = "";
            $qry = "";
            foreach ($gtmp as $glue) {
                if (empty($last)) {
                    list($tmp,$last) = explode('.', $glue);
                    $qry .= $glue.' = ';
                } else {
                    list($tmp,$new) = explode('.', $glue);
                    $qry .= $tmp.'.'.$last.' && '.$tmp.'.'.$new.' = ';
                    $last = $new;
                }
                if (!in_array($tmp, $tables)) {
                    $tables[] = $tmp;
                }
            }
            $join .= "( ".$qry.$tables[0].".device_id ) && ";
        }
        $i++;
    }
    $sql = "SELECT * FROM ".implode(",", $tables)." WHERE (".$join."".str_replace("(", "", $tables[0]).".device_id = ?) && (".str_replace(array("%","@","!~","~"), array("",".*","NOT REGEXP","REGEXP"), $rule).")";
    return $sql;
}

/**
 * Process Macros
 * @param string $rule Rule to process
 * @param int $x Recursion-Anchor
 * @return string|boolean
 */
function RunMacros($rule, $x = 1)
{
    global $config;
    krsort($config['alert']['macros']['rule']);
    foreach ($config['alert']['macros']['rule'] as $macro => $value) {
        if (!strstr($macro, " ")) {
            $rule = str_replace('%macros.'.$macro, '('.$value.')', $rule);
        }
    }
    if (strstr($rule, "%macros")) {
        if (++$x < 30) {
            $rule = RunMacros($rule, $x);
        } else {
            return false;
        }
    }
    return $rule;
}

/**
 * Get Alert-Rules for Devices
 * @param int $device Device-ID
 * @return array
 */
function GetRules($device)
{
    $groups = GetGroupsFromDevice($device);
    $params = array($device,$device);
    $where = "";
    foreach ($groups as $group) {
        $where .= " || alert_map.target = ?";
        $params[] = 'g'.$group;
    }
    return dbFetchRows('SELECT alert_rules.* FROM alert_rules LEFT JOIN alert_map ON alert_rules.id=alert_map.rule WHERE alert_rules.disabled = 0 && ( (alert_rules.device_id = -1 || alert_rules.device_id = ? ) || alert_map.target = ? '.$where.' )', $params);
}

/**
 * Check if device is under maintenance
 * @param int $device Device-ID
 * @return int
 */
function IsMaintenance($device)
{
    $groups = GetGroupsFromDevice($device);
    $params = array($device);
    $where = "";
    foreach ($groups as $group) {
        $where .= " || alert_schedule_items.target = ?";
        $params[] = 'g'.$group;
    }
    return dbFetchCell('SELECT alert_schedule.schedule_id FROM alert_schedule LEFT JOIN alert_schedule_items ON alert_schedule.schedule_id=alert_schedule_items.schedule_id WHERE ( alert_schedule_items.target = ?'.$where.' ) && ((alert_schedule.recurring = 0 AND (NOW() BETWEEN alert_schedule.start AND alert_schedule.end)) OR (alert_schedule.recurring = 1 AND (alert_schedule.start_recurring_dt <= date_format(NOW(), \'%Y-%m-%d\') AND (end_recurring_dt >= date_format(NOW(), \'%Y-%m-%d\') OR end_recurring_dt is NULL OR end_recurring_dt = \'0000-00-00\' OR end_recurring_dt = \'\')) AND (date_format(now(), \'%H:%i:%s\') BETWEEN `start_recurring_hr` AND end_recurring_hr) AND (recurring_day LIKE CONCAT(\'%\',date_format(now(), \'%w\'),\'%\') OR recurring_day is null or recurring_day = \'\'))) LIMIT 1', $params);
}
/**
 * Run all rules for a device
 * @param int $device Device-ID
 * @return void
 */
function RunRules($device)
{
    if (IsMaintenance($device) > 0) {
        echo "Under Maintenance, Skipping alerts.\r\n";
        return false;
    }
    foreach (GetRules($device) as $rule) {
        c_echo('Rule %p#'.$rule['id'].' (' . $rule['name'] . '):%n ');
        $extra = json_decode($rule['extra'], true);
        if (isset($extra['invert'])) {
            $inv = (bool) $extra['invert'];
        } else {
            $inv = false;
        }
        d_echo(PHP_EOL);
        $chk   = dbFetchRow("SELECT state FROM alerts WHERE rule_id = ? && device_id = ? ORDER BY id DESC LIMIT 1", array($rule['id'], $device));
        if (empty($rule['query'])) {
            $rule['query'] = GenSQL($rule['rule']);
        }
        $sql = $rule['query'];
        $qry = dbFetchRows($sql, array($device));
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
        } else { //( $s > 0 && $inv == false ) {
            $doalert = false;
        }
        if ($doalert) {
            if ($chk['state'] === "2") {
                c_echo('Status: %ySKIP');
            } elseif ($chk['state'] >= "1") {
                c_echo('Status: %bNOCHG');
                // NOCHG here doesn't mean no change full stop. It means no change to the alert state
                // So we update the details column with any fresh changes to the alert output we might have.
                $alert_log           = dbFetchRow('SELECT alert_log.id, alert_log.details FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($device, $rule['id']));
                $details             = json_decode(gzuncompress($alert_log['details']), true);
                $details['contacts'] = GetContacts($qry);
                $details['rule']     = $qry;
                $details             = gzcompress(json_encode($details), 9);
                dbUpdate(array('details' => $details), 'alert_log', 'id = ?', array($alert_log['id']));
            } else {
                $extra = gzcompress(json_encode(array('contacts' => GetContacts($qry), 'rule'=>$qry)), 9);
                if (dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'details' => $extra), 'alert_log')) {
                    if (!dbUpdate(array('state' => 1, 'open' => 1), 'alerts', 'device_id = ? && rule_id = ?', array($device,$rule['id']))) {
                        dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1,'alerted' => 0), 'alerts');
                    }
                    c_echo(PHP_EOL . 'Status: %rALERT');
                }
            }
        } else {
            if ($chk['state'] === "0") {
                c_echo('Status: %bNOCHG');
            } else {
                if (dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id']), 'alert_log')) {
                    if (!dbUpdate(array('state' => 0, 'open' => 1), 'alerts', 'device_id = ? && rule_id = ?', array($device,$rule['id']))) {
                        dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0), 'alerts');
                    }
                    c_echo(PHP_EOL . 'Status: %gOK');
                }
            }
        }
        c_echo('%n' . PHP_EOL);
    }
}

/**
 * Find contacts for alert
 * @param array $results Rule-Result
 * @return array
 */
function GetContacts($results)
{
    global $config;
    if (sizeof($results) == 0) {
        return array();
    }
    if ($config['alert']['default_only'] === true || $config['alerts']['email']['default_only'] === true) {
        return array(''.($config['alert']['default_mail'] ? $config['alert']['default_mail'] : $config['alerts']['email']['default']) => 'NOC');
    }
    $users = get_userlist();
    $contacts = array();
    $uids = array();
    foreach ($results as $result) {
        $tmp  = null;
        if (is_numeric($result["bill_id"])) {
            $tmpa = dbFetchRows("SELECT user_id FROM bill_perms WHERE bill_id = ?", array($result["bill_id"]));
            foreach ($tmpa as $tmp) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
        if (is_numeric($result["port_id"])) {
            $tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE access_level >= 0 AND port_id = ?", array($result["port_id"]));
            foreach ($tmpa as $tmp) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
        if (is_numeric($result["device_id"])) {
            if ($config['alert']['syscontact'] == true) {
                if (dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?", array($result["device_id"])) === "1") {
                    $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?", array($result["device_id"]));
                } else {
                    $tmpa = dbFetchCell("SELECT sysContact FROM devices WHERE device_id = ?", array($result["device_id"]));
                }
                if (!empty($tmpa)) {
                    $contacts[$tmpa] = "NOC";
                }
            }
            $tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE access_level >= 0 AND device_id = ?", array($result["device_id"]));
            foreach ($tmpa as $tmp) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
    }
    foreach ($users as $user) {
        if (empty($user['email'])) {
            continue; // no email, skip this user
        }
        if (empty($user['realname'])) {
            $user['realname'] = $user['username'];
        }
        if (empty($user['level'])) {
            $user['level'] = get_userlevel($user['username']);
        }
        if ($config['alert']['globals'] && ( $user['level'] >= 5 && $user['level'] < 10 )) {
            $contacts[$user['email']] = $user['realname'];
        } elseif ($config['alert']['admins'] && $user['level'] == 10) {
            $contacts[$user['email']] = $user['realname'];
        } elseif ($config['alert']['users'] == true && in_array($user['user_id'], $uids)) {
            $contacts[$user['email']] = $user['realname'];
        }
    }

    $tmp_contacts = array();
    foreach ($contacts as $email => $name) {
        if (strstr($email, ',')) {
            $split_contacts = preg_split('/[,\s]+/', $email);
            foreach ($split_contacts as $split_email) {
                if (!empty($split_email)) {
                    $tmp_contacts[$split_email] = $name;
                }
            }
        } else {
            $tmp_contacts[$email] = $name;
        }
    }

    # Send email to default contact if no other contact found
    if ((count($tmp_contacts) == 0) && ($config['alert']['default_if_none']) && (!empty($config['alert']['default_mail']))) {
        $tmp_contacts[$config['alert']['default_mail']] = 'NOC';
    }

    return $tmp_contacts;
}


/**
 * Format Alert
 * @param array  $obj Alert-Array
 * @return string
 */
function FormatAlertTpl($obj)
{
    $tpl    = $obj["template"];
    $msg    = '$ret .= "'.str_replace(array('{else}', '{/if}', '{/foreach}'), array('"; } else { $ret .= "', '"; } $ret .= "', '"; } $ret .= "'), addslashes($tpl)).'";';
    $parsed = $msg;
    $s      = strlen($msg);
    $x      = $pos = -1;
    $buff   = '';
    $if     = $for = $calc = false;
    while (++$x < $s) {
        if ($msg[$x] == '{' && $buff == '') {
            $buff .= $msg[$x];
        } elseif ($buff == '{ ') {
            $buff = '';
        } elseif ($buff != '') {
            $buff .= $msg[$x];
        }

        if ($buff == '{if') {
            $pos = $x;
            $if  = true;
        } elseif ($buff == '{foreach') {
            $pos = $x;
            $for = true;
        } elseif ($buff == '{calc') {
            $pos  = $x;
            $calc = true;
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
            } elseif ($for) {
                $for    = false;
                $o      = 8;
                $native = array(
                    '"; foreach( ',
                    ' as $key=>$value) { $ret .= "',
                );
            } elseif ($calc) {
                $calc   = false;
                $o      = 5;
                $native = array(
                    '"; $ret .= (float) (0+(',
                    ')); $ret .= "',
                );
            } else {
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
 * Populate variables
 * @param string  $txt  Text with variables
 * @param boolean $wrap Wrap variable for text-usage (default: true)
 * @return string
 */
function populate($txt, $wrap = true)
{
    preg_match_all('/%([\w\.]+)/', $txt, $m);
    foreach ($m[1] as $tmp) {
        $orig = $tmp;
        $rep  = false;
        if ($tmp == 'key' || $tmp == 'value') {
            $rep = '$'.$tmp;
        } else {
            if (strstr($tmp, '.')) {
                $tmp = explode('.', $tmp, 2);
                $pre = '$'.$tmp[0];
                $tmp = $tmp[1];
            } else {
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

/**
 * "Safely" run eval
 * @param string $code Code to run
 * @param array  $obj  Object with variables
 * @return string|mixed
 */
function RunJail($code, $obj)
{
    $ret = '';
    eval($code);
    return $ret;
}//end RunJail()


/**
 * Describe Alert
 * @param array $alert Alert-Result from DB
 * @return array|boolean
 */
function DescribeAlert($alert)
{
    $obj         = array();
    $i           = 0;
    $device      = dbFetchRow('SELECT hostname, sysName, location, purpose, notes, uptime FROM devices WHERE device_id = ?', array($alert['device_id']));
    $tpl         = dbFetchRow('SELECT `template`,`title`,`title_rec` FROM `alert_templates` JOIN `alert_template_map` ON `alert_template_map`.`alert_templates_id`=`alert_templates`.`id` WHERE `alert_template_map`.`alert_rule_id`=?', array($alert['rule_id']));
    $default_tpl = "%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}";
    $obj['hostname']     = $device['hostname'];
    $obj['sysName']      = $device['sysName'];
    $obj['location']     = $device['location'];
    $obj['uptime']       = $device['uptime'];
    $obj['uptime_short'] = formatUptime($device['uptime'], 'short');
    $obj['uptime_long']  = formatUptime($device['uptime']);
    $obj['description']  = $device['purpose'];
    $obj['notes']        = $device['notes'];
    $obj['device_id']    = $alert['device_id'];
    $extra               = $alert['details'];
    if (!isset($tpl['template'])) {
        $obj['template'] = $default_tpl;
    } else {
        $obj['template'] = $tpl['template'];
    }
    if ($alert['state'] >= 1) {
        if (!empty($tpl['title'])) {
            $obj['title'] = $tpl['title'];
        } else {
            $obj['title'] = 'Alert for device '.$device['hostname'].' - '.($alert['name'] ? $alert['name'] : $alert['rule']);
        }
        if ($alert['state'] == 2) {
            $obj['title'] .= ' got acknowledged';
        } elseif ($alert['state'] == 3) {
            $obj['title'] .= ' got worse';
        } elseif ($alert['state'] == 4) {
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
        $obj['elapsed'] = TimeFormat(time() - strtotime($alert['time_logged']));
        if (!empty($extra['diff'])) {
            $obj['diff'] = $extra['diff'];
        }
    } elseif ($alert['state'] == 0) {
        $id = dbFetchRow('SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state != 2 && alert_log.state != 0 && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1', array($alert['rule_id'], $alert['device_id'], $alert['id']));
        if (empty($id['id'])) {
            return false;
        }

        $extra          = json_decode(gzuncompress($id['details']), true);
        if (!empty($tpl['title_rec'])) {
            $obj['title'] = $tpl['title_rec'];
        } else {
            $obj['title']   = 'Device '.$device['hostname'].' recovered from '.($alert['name'] ? $alert['name'] : $alert['rule']);
        }
        $obj['elapsed'] = TimeFormat(strtotime($alert['time_logged']) - strtotime($id['time_logged']));
        $obj['id']      = $id['id'];
        foreach ($extra['rule'] as $incident) {
            $i++;
            $obj['faults'][$i] = $incident;
            foreach ($incident as $k => $v) {
                if (!empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                    $obj['faults'][$i]['string'] .= $k.' => '.$v.'; ';
                }
            }
        }
    } else {
        return 'Unknown State';
    }//end if
    $obj['uid']       = $alert['id'];
    $obj['severity']  = $alert['severity'];
    $obj['rule']      = $alert['rule'];
    $obj['name']      = $alert['name'];
    $obj['timestamp'] = $alert['time_logged'];
    $obj['contacts']  = $extra['contacts'];
    $obj['state']     = $alert['state'];
    if (strstr($obj['title'], '%')) {
        $obj['title'] = RunJail('$ret = "'.populate(addslashes($obj['title'])).'";', $obj);
    }
    return $obj;
}//end DescribeAlert()

/**
 * Format Elapsed Time
 * @param integer $secs Seconds elapsed
 * @return string
 */
function TimeFormat($secs)
{
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
                echo "ERROR: $tmp\r\n";
                log_event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "' Error: " . $tmp, $obj['device_id'], null, 5);
            }
        }
        echo '; ';
    }
}//end ExtTransports()
