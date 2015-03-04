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

/**
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
 * @return string
 */
function GenSQL($rule) {
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
	$x = sizeof($tables);
	$i = 0;
	$join = "";
	while( $i < $x ) {
		if( isset($tables[$i+1]) ) {
			$join .= $tables[$i].".device_id = ".$tables[$i+1].".device_id && ";
		}
		$i++;
	}
	$sql = "SELECT * FROM ".implode(",",$tables)." WHERE (".$join."".str_replace("(","",$tables[0]).".device_id = ?) && (".str_replace(array("%","@","!~","~"),array("","%","NOT LIKE","LIKE"),$rule).")";
	return $sql;
}


/**
 * Run all rules for a device
 * @param int $device Device-ID
 * @return void
 */
function RunRules($device) {
	global $debug;
	$chk = dbFetchRow("SELECT id FROM alert_schedule WHERE alert_schedule.device_id = ? AND NOW() BETWEEN alert_schedule.start AND alert_schedule.end", array($device));
	if( $chk['id'] > 0 ) {
		return false;
	}
	foreach( dbFetchRows("SELECT * FROM alert_rules WHERE alert_rules.disabled = 0 && ( alert_rules.device_id = -1 || alert_rules.device_id = ? ) ORDER BY device_id,id",array($device)) as $rule ) {
		echo " #".$rule['id'].":";
		$inv = json_decode($rule['extra'],true);
		if( isset($inv['invert']) ) {
			$inv = (bool) $inv['invert'];
		} else {
			$inv = false;
		}
		$chk = dbFetchRow("SELECT state FROM alerts WHERE rule_id = ? && device_id = ? ORDER BY id DESC LIMIT 1", array($rule['id'], $device));
		$sql = GenSQL($rule['rule']);
		$qry = dbFetchRows($sql,array($device));
		$s = sizeof($qry);
		if( $s == 0 && $inv === false ) {
			$doalert = false;
		} elseif( $s > 0 && $inv === false ) {
			$doalert = true;
		} elseif( $s == 0 && $inv === true ) {
			$doalert = true;
		} else { //( $s > 0 && $inv == false ) {
			$doalert = false;
		}
		if( $doalert ) {
			if( $chk['state'] === "2" ) {
				echo " SKIP  ";
			} elseif( $chk['state'] >= "1" ) {
				echo " NOCHG ";
			} else {
				$extra = gzcompress(json_encode(array('contacts' => GetContacts($qry), 'rule'=>$qry)),9);
				if( dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'details' => $extra),'alert_log') ) {
					if( !dbUpdate(array('state' => 1, 'open' => 1),'alerts','device_id = ? && rule_id = ?', array($device,$rule['id'])) ) {
						dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1),'alerts');
					}
					echo " ALERT ";
				}
			}
		} else {
			if( $chk['state'] === "0" ) {
				echo " NOCHG ";
			} else {
				if( dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id']),'alert_log') ){
					if( !dbUpdate(array('state' => 0, 'open' => 1),'alerts','device_id = ? && rule_id = ?', array($device,$rule['id'])) ) {
						dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id'], 'open' => 1),'alerts');
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
	if( $config['alerts']['email']['default_only'] ) {
		return array($config['alerts']['email']['default'] => 'NOC');
	}
	$contacts = array();
	$uids = array();
	foreach( $results as $result ) {
		$tmp  = NULL;
		if( is_numeric($result["port_id"]) ) {
			$tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE access_level >= 0 AND port_id = ?",array($result["port_id"]));
			foreach( $tmpa as $tmp ) {
				$uids[$tmp['user_id']] = $tmp['user_id'];
			}
		}
		if( is_numeric($result["device_id"]) ) {
			$tmpa = dbFetchRow("SELECT sysContact FROM devices WHERE device_id = ?",array($result["device_id"]));
			$contacts[$tmpa["sysContact"]] = "NOC";
			$tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE access_level >= 0 AND device_id = ?", array($result["device_id"]));
			foreach( $tmpa as $tmp ) {
				$uids[$tmp['user_id']] = $tmp['user_id'];
			}
		}
	}
	if( $config["alert"]["globals"] ) {
		$tmpa = dbFetchRows("SELECT realname,email FROM users WHERE level >= 5 AND level < 10");
		foreach( $tmpa as $tmp ) {
			$contacts[$tmp['email']] = $tmp['realname'];
		}
	}
	if( $config["alert"]["admins"] ) {
		$tmpa = dbFetchRows("SELECT realname,email FROM users WHERE level = 10");
		foreach( $tmpa as $tmp ) {
			$contacts[$tmp['email']] = $tmp['realname'];
		}
	}
	if( is_array($uids) ) {
		foreach( $uids as $uid ) {
			$tmp = dbFetchRow("SELECT realname,email FROM users WHERE user_id = ?", array($uid));
			$contacts[$tmp['email']] = $tmp['realname'];
		}
	}
	return $contacts;
}
?>
