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

use App\Models\DevicePerf;
use LibreNMS\Alert\Template;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Alert\AlertUtil;

/**
 * @param $rule
 * @param $query_builder
 * @return bool|string
 */
function GenSQL($rule, $query_builder = false)
{
    if ($query_builder) {
        return QueryBuilderParser::fromJson($query_builder)->toSql();
    } else {
        return GenSQLOld($rule);
    }
}

/**
 * Generate SQL from Rule
 * @param string $rule Rule to generate SQL for
 * @return string|boolean
 */
function GenSQLOld($rule)
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
    if (strstr($rule, "%macros.")) {
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
 * @param int $device_id Device-ID
 * @return array
 */
function GetRules($device_id)
{
    $query = "SELECT DISTINCT a.* FROM alert_rules a
  LEFT JOIN alert_device_map d ON a.id=d.rule_id
  LEFT JOIN alert_group_map g ON a.id=g.rule_id
  LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id
  WHERE a.disabled = 0 AND ((d.device_id IS NULL AND g.group_id IS NULL) OR d.device_id=? OR dg.device_id=?)";

    $params = [$device_id, $device_id];
    return dbFetchRows($query, $params);
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
 * @param int $device_id Device-ID
 * @return void
 */
function RunRules($device_id)
{
    if (IsMaintenance($device_id) > 0) {
        echo "Under Maintenance, Skipping alerts.\r\n";
        return false;
    }
    foreach (GetRules($device_id) as $rule) {
        c_echo('Rule %p#'.$rule['id'].' (' . $rule['name'] . '):%n ');
        $extra = json_decode($rule['extra'], true);
        if (isset($extra['invert'])) {
            $inv = (bool) $extra['invert'];
        } else {
            $inv = false;
        }
        d_echo(PHP_EOL);
        if (empty($rule['query'])) {
            $rule['query'] = GenSQL($rule['rule'], $rule['builder']);
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
        } else { //( $s > 0 && $inv == false ) {
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
                $alert_log           = dbFetchRow('SELECT alert_log.id, alert_log.details FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule['id']));
                $details             = [];
                if (!empty($alert_log['details'])) {
                    $details = json_decode(gzuncompress($alert_log['details']), true);
                }
                $details['contacts'] = GetContacts($qry);
                $details['rule']     = $qry;
                $details             = gzcompress(json_encode($details), 9);
                dbUpdate(array('details' => $details), 'alert_log', 'id = ?', array($alert_log['id']));
            } else {
                $extra = gzcompress(json_encode(array('contacts' => GetContacts($qry), 'rule'=>$qry)), 9);
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

/**
 * Find contacts for alert
 * @param array $results Rule-Result
 * @return array
 */
function GetContacts($results)
{
    global $config, $authorizer;

    if (sizeof($results) == 0) {
        return array();
    }
    if ($config['alert']['default_only'] === true || $config['alerts']['email']['default_only'] === true) {
        return array(''.($config['alert']['default_mail'] ? $config['alert']['default_mail'] : $config['alerts']['email']['default']) => '');
    }
    $users = LegacyAuth::get()->getUserlist();
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
            $tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE port_id = ?", array($result["port_id"]));
            foreach ($tmpa as $tmp) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
        if (is_numeric($result["device_id"])) {
            if ($config['alert']['syscontact'] == true) {
                if (dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?", [$result["device_id"]])) {
                    $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?", array($result["device_id"]));
                } else {
                    $tmpa = dbFetchCell("SELECT sysContact FROM devices WHERE device_id = ?", array($result["device_id"]));
                }
                if (!empty($tmpa)) {
                    $contacts[$tmpa] = '';
                }
            }
            $tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE device_id = ?", array($result["device_id"]));
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
            $user['level'] = LegacyAuth::get()->getUserlevel($user['username']);
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

    if (!empty($tmp_contacts)) {
        // Validate contacts so we can fall back to default if configured.
        $mail = new PHPMailer();
        foreach ($tmp_contacts as $tmp_email => $tmp_name) {
            if ($mail->validateAddress($tmp_email) != true) {
                unset($tmp_contacts[$tmp_email]);
            }
        }
    }

    # Copy all email alerts to default contact if configured.
    if (!isset($tmp_contacts[$config['alert']['default_mail']]) && ($config['alert']['default_copy'])) {
        $tmp_contacts[$config['alert']['default_mail']] = '';
    }

    # Send email to default contact if no other contact found
    if ((count($tmp_contacts) == 0) && ($config['alert']['default_if_none']) && (!empty($config['alert']['default_mail']))) {
        $tmp_contacts[$config['alert']['default_mail']] = '';
    }

    return $tmp_contacts;
}

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
    @eval($code);
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
    $device      = dbFetchRow('SELECT hostname, sysName, sysDescr, sysContact, os, type, ip, hardware, version, purpose, notes, uptime, status, status_reason, locations.location FROM devices LEFT JOIN locations ON locations.id = devices.location_id WHERE device_id = ?', array($alert['device_id']));
    $attribs     = get_dev_attribs($alert['device_id']);

    $obj['hostname']      = $device['hostname'];
    $obj['sysName']       = $device['sysName'];
    $obj['sysDescr']      = $device['sysDescr'];
    $obj['sysContact']    = $device['sysContact'];
    $obj['os']            = $device['os'];
    $obj['type']          = $device['type'];
    $obj['ip']            = inet6_ntop($device['ip']);
    $obj['hardware']      = $device['hardware'];
    $obj['version']       = $device['version'];
    $obj['location']      = $device['location'];
    $obj['uptime']        = $device['uptime'];
    $obj['uptime_short']  = formatUptime($device['uptime'], 'short');
    $obj['uptime_long']   = formatUptime($device['uptime']);
    $obj['description']   = $device['purpose'];
    $obj['notes']         = $device['notes'];
    $obj['alert_notes']   = $alert['note'];
    $obj['device_id']     = $alert['device_id'];
    $obj['rule_id']       = $alert['rule_id'];
    $obj['status']        = $device['status'];
    $obj['status_reason'] = $device['status_reason'];
    if (can_ping_device($attribs)) {
        $ping_stats = DevicePerf::where('device_id', $alert['device_id'])->latest('timestamp')->first();
        $obj['ping_timestamp'] = $ping_stats->template;
        $obj['ping_loss']      = $ping_stats->loss;
        $obj['ping_min']       = $ping_stats->min;
        $obj['ping_max']       = $ping_stats->max;
        $obj['ping_avg']       = $ping_stats->avg;
        $obj['debug']          = json_decode($ping_stats->debug, true);
    }
    $extra               = $alert['details'];

    $tpl                 = new Template;
    $template            = $tpl->getTemplate($obj);

    if ($alert['state'] >= 1) {
        $obj['title'] = $template->title ?: 'Alert for device '.$device['hostname'].' - '.($alert['name'] ? $alert['name'] : $alert['rule']);
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
            $obj['faults'][$i]['string'] = null;
            foreach ($incident as $k => $v) {
                if (!empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                    $obj['faults'][$i]['string'] .= $k.' = '.$v.'; ';
                }
            }
        }
        $obj['elapsed'] = TimeFormat(time() - strtotime($alert['time_logged']));
        if (!empty($extra['diff'])) {
            $obj['diff'] = $extra['diff'];
        }
    } elseif ($alert['state'] == 0) {
        // Alert is now cleared
        $id = dbFetchRow('SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state != 2 && alert_log.state != 0 && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1', array($alert['rule_id'], $alert['device_id'], $alert['id']));
        if (empty($id['id'])) {
            return false;
        }

        $extra = [];
        if (!empty($id['details'])) {
            $extra = json_decode(gzuncompress($id['details']), true);
        }

        // Reset count to 0 so alerts will continue
        $extra['count'] = 0;
        dbUpdate(array('details' => gzcompress(json_encode($id['details']), 9)), 'alert_log', 'id = ?', array($alert['id']));

        $obj['title'] = $template->title_rec ?: 'Device '.$device['hostname'].' recovered from '.($alert['name'] ? $alert['name'] : $alert['rule']);
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
    $obj['builder']   = $alert['builder'];
    $obj['uid']       = $alert['id'];
    $obj['alert_id']  = $alert['alert_id'];
    $obj['severity']  = $alert['severity'];
    $obj['rule']      = $alert['rule'];
    $obj['name']      = $alert['name'];
    $obj['timestamp'] = $alert['time_logged'];
    $obj['contacts']  = $extra['contacts'];
    $obj['state']     = $alert['state'];
    $obj['template']  = $template;
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
 * @param integer $device_id Device-ID
 * @param integer $rule   Rule-ID
 * @return boolean
 */
function IsRuleValid($device_id, $rule)
{
    global $rulescache;
    if (empty($rulescache[$device_id]) || !isset($rulescache[$device_id])) {
        foreach (GetRules($device_id) as $chk) {
            $rulescache[$device_id][$chk['id']] = true;
        }
    }

    if ($rulescache[$device_id][$rule] === true) {
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
            $alert['query'] = GenSQL($alert['rule'], $alert['builder']);
        }
        $sql = $alert['query'];
        $qry = dbFetchRows($sql, array($alert['device_id']));
        $alert['details']['contacts'] = GetContacts($qry);
    }

    $obj = DescribeAlert($alert);
    if (is_array($obj)) {
        echo 'Issuing Alert-UID #'.$alert['id'].'/'.$alert['state'].':' . PHP_EOL;
        ExtTransports($obj);

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

    foreach (loadAlerts('alerts.state = 2 && alerts.open = 1') as $alert) {
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
    foreach (loadAlerts('alerts.state > 0 && alerts.open = 0') as $alert) {
        if ($alert['state'] != 2 || ($alert['info']['until_clear'] === false)) {
            $rextra = json_decode($alert['extra'], true);
            if ($rextra['invert']) {
                continue;
            }

            if (empty($alert['query'])) {
                $alert['query'] = GenSQL($alert['rule'], $alert['builder']);
            }
            $chk = dbFetchRows($alert['query'], array($alert['device_id']));
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
            $state = 0;
            if ($n > $o) {
                $ret .= ' Worsens';
                $state = 3;
                $alert['details']['diff'] = array_diff($chk, $alert['details']['rule']);
            } elseif ($n < $o) {
                $ret .= ' Betters';
                $state = 4;
                $alert['details']['diff'] = array_diff($alert['details']['rule'], $chk);
            }

            if ($state > 0 && $n > 0) {
                $alert['details']['rule'] = $chk;
                if (dbInsert(array(
                    'state' => $state,
                    'device_id' => $alert['device_id'],
                    'rule_id' => $alert['rule_id'],
                    'details' => gzcompress(json_encode($alert['details']), 9)
                ), 'alert_log')) {
                    dbUpdate(array('state' => $state, 'open' => 1, 'alerted' => 1), 'alerts', 'rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
                }

                echo $ret . ' (' . $o . '/' . $n . ")\r\n";
            }
        }
    }//end foreach
}//end RunFollowUp()

function loadAlerts($where)
{
    $alerts = [];
    foreach (dbFetchRows("SELECT alerts.id, alerts.device_id, alerts.rule_id, alerts.state, alerts.note, alerts.info FROM alerts WHERE $where") as $alert_status) {
        $alert = dbFetchRow(
            'SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name,alert_rules.builder FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1',
            array($alert_status['device_id'], $alert_status['rule_id'])
        );

        if (empty($alert['rule_id']) || !IsRuleValid($alert_status['device_id'], $alert_status['rule_id'])) {
            echo 'Stale-Rule: #' . $alert_status['rule_id'] . '/' . $alert_status['device_id'] . "\r\n";
            // Alert-Rule does not exist anymore, let's remove the alert-state.
            dbDelete('alerts', 'rule_id = ? && device_id = ?', [$alert_status['rule_id'], $alert_status['device_id']]);
        } else {
            $alert['alert_id'] = $alert_status['id'];
            $alert['state'] = $alert_status['state'];
            $alert['note'] = $alert_status['note'];
            if (!empty($alert['details'])) {
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
function RunAlerts()
{
    global $config;
    foreach (loadAlerts('alerts.state != 2 && alerts.open = 1') as $alert) {
        $noiss            = false;
        $noacc            = false;
        $updet            = false;
        $rextra           = json_decode($alert['extra'], true);
        if (!isset($rextra['recovery'])) {
            // backwards compatibility check
            $rextra['recovery'] = true;
        }

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

            if (in_array($alert['state'], [1,3,4]) && !empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count'])) {
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

        if (IsParentDown($alert['device_id'])) {
            $noiss = true;
            log_event('Skipped alerts because all parent devices are down', $alert['device_id'], 'alert', 1);
        }

        if ($alert['state'] == 0 && $rextra['recovery'] == false) {
            // Rule is set to not send a recovery alert
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
    $type  = new Template;

    // If alert transport mapping exists, override the default transports
    $transport_maps = AlertUtil::getAlertTransports($obj['alert_id']);

    if (!$transport_maps) {
        $transport_maps = AlertUtil::getDefaultAlertTransports();
        $legacy_transports = array_unique(array_map(function ($transports) {
            return $transports['transport_type'];
        }, $transport_maps));
        foreach ($config['alert']['transports'] as $transport => $opts) {
            if (in_array($transport, $legacy_transports)) {
                // If it is a default transport type, then the alert has already been sent out, so skip
                continue;
            }
            if (is_array($opts)) {
                $opts = array_filter($opts);
            }
            $class  = 'LibreNMS\\Alert\\Transport\\' . ucfirst($transport);
            if (($opts === true || !empty($opts)) && $opts != false && class_exists($class)) {
                $transport_maps[] = [
                    'transport_id' => null,
                    'transport_type' => $transport,
                    'opts' => $opts,
                    'legacy' => true,
                ];
            }
        }
        unset($legacy_transports);
    }

    foreach ($transport_maps as $item) {
        $class = 'LibreNMS\\Alert\\Transport\\'.ucfirst($item['transport_type']);
        //FIXME remove Deprecated noteice
        $dep_notice = 'DEPRECATION NOTICE: https://t.libren.ms/deprecation-alerting';
        if (class_exists($class)) {
            //FIXME remove Deprecated transport
            $transport_title = ($item['legacy'] === true) ? "Transport {$item['transport_type']} (%YTransport $dep_notice%n)" : "Transport {$item['transport_type']}";
            $obj['transport'] = $item['transport_type'];
            $obj['transport_name'] = $item['transport_name'];
            $obj['alert']     = new AlertData($obj);
            $obj['title']     = $type->getTitle($obj);
            $obj['alert']['title'] = $obj['title'];
            $obj['msg']       = $type->getBody($obj);
            //FIXME remove Deprecated template check
            if (preg_match('/{\/if}/', $type->getTemplate()->template)) {
                c_echo(" :: %YTemplate $dep_notice :: Please update your template " . $type->getTemplate()->name . "%n" . PHP_EOL);
            }
            c_echo(" :: $transport_title => ");
            $instance = new $class($item['transport_id']);
            $tmp = $instance->deliverAlert($obj, $item['opts']);
            AlertLog($tmp, $obj, $obj['transport']);
            unset($instance);
            echo PHP_EOL;
        }
    }

    if (count($transport_maps) === 0) {
        echo 'No configured transports';
    }
}//end ExtTransports()

// Log alert event
function AlertLog($result, $obj, $transport)
{
    $prefix = [
        0 => "recovery",
        1 => $obj['severity']." alert",
        2 => "acknowledgment"
    ];
    $prefix[3] = &$prefix[0];
    $prefix[4] = &$prefix[0];
    if ($result === true) {
        echo 'OK';
        log_event('Issued ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], 'alert', 1);
    } elseif ($result === false) {
        echo 'ERROR';
        log_event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], null, 5);
    } else {
        echo "ERROR: $result\r\n";
        log_event('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "' Error: " . $result, $obj['device_id'], 'error', 5);
    }
    return;
}//end AlertLog()


/**
 * Check if a device's all parent are down
 * Returns true if all parents are down
 * @param int $device Device-ID
 * @return bool
 */
function IsParentDown($device)
{
    $parent_count = dbFetchCell("SELECT count(*) from `device_relationships` WHERE `child_device_id` = ?", array($device));
    if (!$parent_count) {
        return false;
    }


    $down_parent_count = dbFetchCell("SELECT count(*) from devices as d LEFT JOIN devices_attribs as a ON d.device_id=a.device_id LEFT JOIN device_relationships as r ON d.device_id=r.parent_device_id WHERE d.status=0 AND d.ignore=0 AND d.disabled=0 AND r.child_device_id=? AND (d.status_reason='icmp' OR (a.attrib_type='override_icmp_disable' AND a.attrib_value=true))", array($device));
    if ($down_parent_count == $parent_count) {
        return true;
    }

    return false;
} //end IsParentDown()
