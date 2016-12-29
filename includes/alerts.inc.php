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
    $pretty = array('*'  => '*', '('  => ' ( ', ')'  => ' ) ', '/'  => '/', '&&' => ' && ', '||' => ' || ', 'DATE_SUB ( NOW (  )' => 'DATE_SUB( NOW()');
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
    return dbFetchCell('SELECT alert_schedule.schedule_id FROM alert_schedule LEFT JOIN alert_schedule_items ON alert_schedule.schedule_id=alert_schedule_items.schedule_id WHERE ( alert_schedule_items.target = ?'.$where.' ) && NOW() BETWEEN alert_schedule.start AND alert_schedule.end LIMIT 1', $params);
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
        $inv = json_decode($rule['extra'], true);
        if (isset($inv['invert'])) {
            $inv = (bool) $inv['invert'];
        } else {
            $inv = false;
        }
        d_echo(PHP_EOL);
        $chk = dbFetchRow("SELECT state FROM alerts WHERE rule_id = ? && device_id = ? ORDER BY id DESC LIMIT 1", array($rule['id'], $device));
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
            continue;
        } elseif (empty($user['realname'])) {
            $user['realname'] = $user['username'];
        }
        $user['level'] = get_userlevel($user['username']);
        if ($config["alert"]["globals"] && ( $user['level'] >= 5 && $user['level'] < 10 )) {
            $contacts[$user['email']] = $user['realname'];
        } elseif ($config["alert"]["admins"] && $user['level'] == 10) {
            $contacts[$user['email']] = $user['realname'];
        } elseif (in_array($user['user_id'], $uids)) {
            $contacts[$user['email']] = $user['realname'];
        }
    }

    $tmp_contacts = array();
    foreach ($contacts as $email => $name) {
        if (strstr($email, ',')) {
            $split_contacts = preg_split("/[,\s]+/", $email);
            foreach ($split_contacts as $split_email) {
                if (!empty($split_email)) {
                    $tmp_contacts[$split_email] = $name;
                }
            }
        } else {
            $tmp_contacts[$email] = $name;
        }
    }

    return $tmp_contacts;
}
