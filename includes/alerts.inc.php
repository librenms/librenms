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

include_once($config['install_dir'].'/includes/device-groups.inc.php');
include_once($config['install_dir'].'/html/includes/authentication/'.$config['auth_mechanism'].'.inc.php');

/**
 * Generate SQL from Rule
 * @param string $rule Rule to generate SQL for
 * @return string|boolean
 */
function GenSQL($rule) {
    $rule = RunMacros($rule);
    if( empty($rule) ) {
        //Cannot resolve Macros due to recursion. Rule is invalid.
        return false;
    }
    //Pretty-print rule to dissect easier
    $pretty = array('*'  => ' * ', '('  => ' ( ', ')'  => ' ) ', '/'  => ' / ', '&&' => ' && ', '||' => ' || ', 'DATE_SUB ( NOW (  )' => 'DATE_SUB( NOW()');
    $rule = str_replace(array_keys($pretty),$pretty,$rule);
    $tmp = explode(" ",$rule);
    $tables = array();
    foreach( $tmp as $opt ) {
        if( strstr($opt,'%') && strstr($opt,'.') ) {
            $tmpp = explode(".",$opt,2);
            $tmpp[0] = str_replace("%","",$tmpp[0]);
            $tables[] = mres(str_replace("(","",$tmpp[0]));
            $rule = str_replace($opt,$tmpp[0].'.'.$tmpp[1],$rule);
        }
    }
    $tables = array_keys(array_flip($tables));
    if( dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?',array($tables[0],'device_id')) != 1 ) {
        //Our first table has no valid glue, append the 'devices' table to it!
        array_unshift($tables, 'devices');
    }
    $x = sizeof($tables)-1;
    $i = 0;
    $join = "";
    while( $i < $x ) {
        if( isset($tables[$i+1]) ) {
            $gtmp = ResolveGlues(array($tables[$i+1]),'device_id');
            if( $gtmp === false ) {
                //Cannot resolve glue-chain. Rule is invalid.
                return false;
            }
            $last = "";
            $qry = "";
            foreach( $gtmp as $glue ) {
                if( empty($last) ) {
                    list($tmp,$last) = explode('.',$glue);
                    $qry .= $glue.' = ';
                }
                else {
                    list($tmp,$new) = explode('.',$glue);
                    $qry .= $tmp.'.'.$last.' && '.$tmp.'.'.$new.' = ';
                    $last = $new;
                }
                if( !in_array($tmp, $tables) ) {
                    $tables[] = $tmp;
                }
            }
            $join .= "( ".$qry.$tables[0].".device_id ) && ";
        }
        $i++;
    }
    $sql = "SELECT * FROM ".implode(",",$tables)." WHERE (".$join."".str_replace("(","",$tables[0]).".device_id = ?) && (".str_replace(array("%","@","!~","~"),array("",".*","NOT REGEXP","REGEXP"),$rule).")";
    return $sql;
}

/**
 * Create a glue-chain
 * @param array $tables Initial Tables to construct glue-chain
 * @param string $target Glue to find (usual device_id)
 * @param int $x Recursion Anchor
 * @param array $hist History of processed tables
 * @param array $last Glues on the fringe
 * @return string|boolean
 */
function ResolveGlues($tables,$target,$x=0,$hist=array(),$last=array()) {
    if( sizeof($tables) == 1 && $x != 0 ) {
        if( dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?',array($tables[0],$target)) == 1 ) {
            return array_merge($last,array($tables[0].'.'.$target));
        }
        else {
            return false;
        }
    }
    else {
        $x++;
        if( $x > 30 ) {
            //Too much recursion. Abort.
            return false;
        }
        foreach( $tables as $table ) {
            $glues = dbFetchRows('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME LIKE "%\_id"',array($table));
            if( sizeof($glues) == 1 && $glues[0]['COLUMN_NAME'] != $target ) {
                //Search for new candidates to expand
                $ntables = array();
                list($tmp) = explode('_',$glues[0]['COLUMN_NAME'],2);
                $ntables[] = $tmp;
                $ntables[] = $tmp.'s';
                $tmp = dbFetchRows('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME LIKE "'.substr($table,0,-1).'_%" && TABLE_NAME != "'.$table.'"');
                foreach( $tmp as $expand ) {
                    $ntables[] = $expand['TABLE_NAME'];
                }
                $tmp = ResolveGlues($ntables,$target,$x++,array_merge($tables,$ntables),array_merge($last,array($table.'.'.$glues[0]['COLUMN_NAME'])));
                if( is_array($tmp) ) {
                    return $tmp;
                }
            }
            else {
                foreach( $glues as $glue ) {
                    if( $glue['COLUMN_NAME'] == $target ) {
                        return array_merge($last,array($table.'.'.$target));
                    }
                    else {
                        list($tmp) = explode('_',$glue['COLUMN_NAME']);
                        $tmp .= 's';
                        if( !in_array($tmp,$tables) && !in_array($tmp,$hist) ) {
                            //Expand table
                            $tmp = ResolveGlues(array($tmp),$target,$x++,array_merge($tables,array($tmp)),array_merge($last,array($table.'.'.$glue['COLUMN_NAME'])));
                            if( is_array($tmp) ) {
                                return $tmp;
                            }
                        }
                    }
                }
            }
        }
    }
    //You should never get here.
    return false;
}

/**
 * Process Macros
 * @param string $rule Rule to process
 * @param int $x Recursion-Anchor
 * @return string|boolean
 */
function RunMacros($rule,$x=1) {
    global $config;
    krsort($config['alert']['macros']['rule']);
    foreach( $config['alert']['macros']['rule'] as $macro=>$value ) {
        if( !strstr($macro," ") ) {
            $rule = str_replace('%macros.'.$macro,'('.$value.')',$rule);
        }
    }
    if( strstr($rule,"%macros") ) {
        if( ++$x < 30 ) {
            $rule = RunMacros($rule,$x);
        }
        else {
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
function GetRules($device) {
    $groups = GetGroupsFromDevice($device);
    $params = array($device,$device);
    $where = "";
    foreach( $groups as $group ) {
        $where .= " || alert_map.target = ?";
        $params[] = 'g'.$group;
    }
    return dbFetchRows('SELECT alert_rules.* FROM alert_rules LEFT JOIN alert_map ON alert_rules.id=alert_map.rule WHERE alert_rules.disabled = 0 && ( (alert_rules.device_id = -1 || alert_rules.device_id = ? ) || alert_map.target = ? '.$where.' )',$params);
}

/**
 * Check if device is under maintenance
 * @param int $device Device-ID
 * @return int
 */
function IsMaintenance( $device ) {
    $groups = GetGroupsFromDevice($device);
    $params = array($device);
    $where = "";
    foreach( $groups as $group ) {
        $where .= " || alert_schedule_items.target = ?";
        $params[] = 'g'.$group;
    }
    return dbFetchCell('SELECT DISTINCT(alert_schedule.schedule_id) FROM alert_schedule LEFT JOIN alert_schedule_items ON alert_schedule.schedule_id=alert_schedule_items.schedule_id WHERE ( alert_schedule_items.target = ?'.$where.' ) && NOW() BETWEEN alert_schedule.start AND alert_schedule.end LIMIT 1',$params);
}

/**
 * Run all rules for a device
 * @param int $device Device-ID
 * @return void
 */
function RunRules($device) {
    if( IsMaintenance($device) > 0 ) {
        echo "Under Maintenance, Skipping alerts.\r\n";
        return false;
    }
    foreach( GetRules($device) as $rule ) {
        echo " #".$rule['id'].":";
        $inv = json_decode($rule['extra'],true);
        if( isset($inv['invert']) ) {
            $inv = (bool) $inv['invert'];
        }
        else {
            $inv = false;
        }
        $chk = dbFetchRow("SELECT state FROM alerts WHERE rule_id = ? && device_id = ? ORDER BY id DESC LIMIT 1", array($rule['id'], $device));
        $sql = GenSQL($rule['rule']);
        $qry = dbFetchRows($sql,array($device));
        $s = sizeof($qry);
        if( $s == 0 && $inv === false ) {
            $doalert = false;
        }
        elseif( $s > 0 && $inv === false ) {
            $doalert = true;
        }
        elseif( $s == 0 && $inv === true ) {
            $doalert = true;
        }
        else { //( $s > 0 && $inv == false ) {
            $doalert = false;
        }
        if( $doalert ) {
            if( $chk['state'] === "2" ) {
                echo " SKIP  ";
            }
            elseif( $chk['state'] >= "1" ) {
                echo " NOCHG ";
            }
            else {
                $extra = gzcompress(json_encode(array('contacts' => GetContacts($qry), 'rule'=>$qry)),9);
                if( dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'details' => $extra),'alert_log') ) {
                    if( !dbUpdate(array('state' => 1, 'open' => 1),'alerts','device_id = ? && rule_id = ?', array($device,$rule['id'])) ) {
                        dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1,'alerted' => 0),'alerts');
                    }
                    echo " ALERT ";
                }
            }
        }
       else {
            if( $chk['state'] === "0" ) {
                echo " NOCHG ";
            }
            else {
                if( dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id']),'alert_log') ){
                    if( !dbUpdate(array('state' => 0, 'open' => 1),'alerts','device_id = ? && rule_id = ?', array($device,$rule['id'])) ) {
                        dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1, 'alerted' => 0),'alerts');
                    }
                    echo " OK    ";
                }
            }
        }
    }
}

/**
 * Find contacts for alert
 * @param array $results Rule-Result
 * @return array
 */
function GetContacts($results) {
    global $config;
    if( sizeof($results) == 0 ) {
        return array();
    }
    if( $config['alert']['default_only'] == true || $config['alerts']['email']['default_only'] == true ) {
        return array(''.($config['alert']['default_mail'] ? $config['alert']['default_mail'] : $config['alerts']['email']['default']) => 'NOC');
    }
    $users = get_userlist();
    $contacts = array();
    $uids = array();
    foreach( $results as $result ) {
        $tmp  = NULL;
        if( is_numeric($result["bill_id"]) ) {
            $tmpa = dbFetchRows("SELECT user_id FROM bill_perms WHERE bill_id = ?",array($result["bill_id"]));
            foreach( $tmpa as $tmp ) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
        if( is_numeric($result["port_id"]) ) {
            $tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE access_level >= 0 AND port_id = ?",array($result["port_id"]));
            foreach( $tmpa as $tmp ) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
        if( is_numeric($result["device_id"]) ) {
            if( $config['alert']['syscontact'] == true ) {
                if( dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?",array($result["device_id"])) === "1" ) {
                    $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?",array($result["device_id"]));
                }
                else {
                    $tmpa = dbFetchCell("SELECT sysContact FROM devices WHERE device_id = ?",array($result["device_id"]));
                }
                $contacts[$tmpa] = "NOC";
            }
            $tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE access_level >= 0 AND device_id = ?", array($result["device_id"]));
            foreach( $tmpa as $tmp ) {
                $uids[$tmp['user_id']] = $tmp['user_id'];
            }
        }
    }
    foreach( $users as $user ) {
        if( empty($user['email']) ) {
            continue;
        }
        elseif( empty($user['realname']) ) {
            $user['realname'] = $user['username'];
        }
        $user['level'] = get_userlevel($user['username']);
        if( $config["alert"]["globals"] && ( $user['level'] >= 5 && $user['level'] < 10 ) ) {
            $contacts[$user['email']] = $user['realname'];
        }
        elseif( $config["alert"]["admins"] && $user['level'] == 10 ) {
            $contacts[$user['email']] = $user['realname'];
        }
        elseif( in_array($user['user_id'],$uids) ) {
            $contacts[$user['email']] = $user['realname'];
        }
    }
    return $contacts;
}
