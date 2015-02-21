#!/usr/bin/env php
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
 * Alerts Cronjob
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

include_once("includes/defaults.inc.php");
include_once("config.php");

$lock = false;
if( file_exists($config['install_dir']."/.alerts.lock") ) {
	$pids = explode("\n", trim(`ps -e | grep php | awk '{print $1}'`));
	$lpid = trim(file_get_contents($config['install_dir']."/.alerts.lock"));
	if( in_array($lpid,$pids) ) {
		$lock = true;
	}
}

if( $lock === true ) {
	exit(1);
} else {
	file_put_contents($config['install_dir']."/.alerts.lock", getmypid());
}

include_once($config['install_dir']."/includes/definitions.inc.php");
include_once($config['install_dir']."/includes/functions.php");
include_once($config['install_dir']."/includes/alerts.inc.php");

if( !defined("TEST") ) {
	echo "Start: ".date('r')."\r\n";
	echo "RunFollowUp():\r\n";
	RunFollowUp();
	echo "RunAlerts():\r\n";
	RunAlerts();
	echo "End  : ".date('r')."\r\n";
}

unlink($config['install_dir']."/.alerts.lock");

/**
 * Run Follow-Up alerts
 * @return void
 */
function RunFollowUp() {
	global $config;
	foreach( dbFetchRows("SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts,alert_rules WHERE alerts.rule_id = alert_rules.id && alert_rules.disabled = 0 && alerts.state != 2 && alerts.open = 0") as $alert ) {
		$tmp = array($alert['rule_id'],$alert['device_id']);
		$alert = dbFetchRow("SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? ORDER BY alert_log.id DESC LIMIT 1",array($alert['device_id'],$alert['rule_id']));
		if( empty($alert['rule']) ) {
			// Alert-Rule does not exist anymore, let's remove the alert-state.
			dbDelete('alerts','rule_id = ? && device_id = ?',array($tmp[0],$tmp[1]));
			continue;
		}
		$alert['details'] = json_decode(gzuncompress($alert['details']),true);
		$rextra = json_decode($alert['extra'],true);
		if( $rextra['invert'] ) {
			continue;
		}
		$chk = dbFetchRows(GenSQL($alert['rule']),array($alert['device_id']));
		$o = sizeof($alert['details']['rule']);
		$n = sizeof($chk);
		$ret = "Alert #".$alert['id'];
		$state = 0;
		if( $n > $o ) {
			$ret .= " Worsens";
			$state = 3;
		} elseif( $n < $o ) {
			$ret .= " Betters";
			$state = 4;
		}
		if( $state > 0 ) {
			$alert['details']['rule'] = $chk;
			if( dbInsert(array('state' => $state, 'device_id' => $alert['device_id'], 'rule_id' => $alert['rule_id'], 'details' => gzcompress(json_encode($alert['details']),9)), 'alert_log') ) {
				dbUpdate(array('state' => $state, 'open' => 1, 'alerted' => 1),'alerts','rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
			}
			echo $ret." (".$o."/".$n.")\r\n";
		}
	}
}

/**
 * Run all alerts
 * @return void
 */
function RunAlerts() {
	global $config;
	$default_tpl = "%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}"; //FIXME: Put somewhere else?
	foreach( dbFetchRows("SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts,alert_rules WHERE alerts.rule_id = alert_rules.id && alert_rules.disabled = 0 && alerts.state != 2 && alerts.open = 1") as $alert ) {
		$tmp = array($alert['rule_id'],$alert['device_id']);
		$alert = dbFetchRow("SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? ORDER BY alert_log.id DESC LIMIT 1",array($alert['device_id'],$alert['rule_id']));
		if( empty($alert['rule_id']) ) {
			// Alert-Rule does not exist anymore, let's remove the alert-state.
			dbDelete('alerts','rule_id = ? && device_id = ?',array($tmp[0],$tmp[1]));
			continue;
		}
		$alert['details'] = json_decode(gzuncompress($alert['details']),true);
		$noiss = false;
		$noacc = false;
		$updet = false;
		$rextra = json_decode($alert['extra'],true);
		$chk = dbFetchRow('SELECT alerts.alerted,devices.ignore,devices.disabled FROM alerts,devices WHERE alerts.device_id = ? && devices.device_id = alerts.device_id && alerts.rule_id = ?',array($alert['device_id'],$alert['rule_id']));
		if( $chk['alerted'] == $alert['state'] ) {
			$noiss = true;
		}
		if( !empty($rextra['delay']) ) {
			if( (time()-strtotime($alert['time_logged'])) < $rextra['delay'] || (!empty($alert['details']['delay']) && (time()-$alert['details']['delay']) < $rextra['delay']) ) {
				continue;
			} else {
				$alert['details']['delay'] = time();
				$updet = true;
			}
		}
		if( $alert['state'] == 1 && !empty($rextra['count']) && ($rextra['count'] == -1 || $alert['details']['count']++ < $rextra['count']) ) {
			if( $alert['details']['count'] < $rextra['count'] ) {
				$noacc = true;
			}
			$updet = true;
			$noiss = false;
		}
		if( $chk['ignore'] == 1 || $chk['disabled'] == 1 ) {
			$noiss = true;
			$updet = false;
			$noacc = false;
		}
		if( $updet ) {
			dbUpdate(array('details' => gzcompress(json_encode($alert['details']),9)),'alert_log','id = ?',array($alert['id']));
		}
		if( !empty($rextra['mute']) ) {
			echo "Muted Alert-UID #".$alert['id']."\r\n";
			$noiss = true;
		}
		if( !$noiss ) {
			$obj = DescribeAlert($alert);
			if( is_array($obj) ) {
				$tpl = dbFetchRow('SELECT template FROM alert_templates WHERE rule_id LIKE "%,'.$alert['rule_id'].',%"');
				if( isset($tpl['template']) ) {
					$tpl = $tpl['template'];
				} else {
					$tpl = $default_tpl;
				}
				echo "Issuing Alert-UID #".$alert['id'].": ";
				$msg = FormatAlertTpl($tpl,$obj);
				$obj['msg'] = $msg;
				if( !empty($config['alert']['transports']) ) {
					ExtTransports($obj);
				}
				echo "\r\n";
				dbUpdate(array('alerted' => $alert['state']),'alerts','rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
			}
		}
		if( !$noacc ) {
			dbUpdate(array('open' => 0),'alerts','rule_id = ? && device_id = ?', array($alert['rule_id'], $alert['device_id']));
		}
	}
}

/**
 * Run external transports
 * @param array $obj Alert-Array
 * @return void
 */
function ExtTransports($obj) {
	global $config;
	$tmp = false; //To keep scrutinizer from naging because it doesnt understand eval
	foreach( $config['alert']['transports'] as $transport=>$opts ) {
		if( file_exists($config['install_dir']."/includes/alerts/transport.".$transport.".php") ) {
			echo $transport." => ";
			eval('$tmp = function($obj,$opts) { global $config; '.file_get_contents($config['install_dir']."/includes/alerts/transport.".$transport.".php").' };');
			$tmp = $tmp($obj,$opts);
			echo ($tmp ? "OK" : "ERROR")."; ";
		}
	}
}

/**
 * Format Alert
 * @param string $tpl Template
 * @param array  $obj Alert-Array
 * @return string
 */
function FormatAlertTpl($tpl,$obj) {
	$msg = '$ret .= "'.str_replace(array("{else}","{/if}","{/foreach}"),array('"; } else { $ret .= "','"; } $ret .= "','"; } $ret .= "'),addslashes($tpl)).'";';
	$parsed = $msg;
	$s = strlen($msg);
	$x = $pos = -1;
	$buff = "";
	$if = $for = false;
	while( ++$x < $s ) {
		if( $msg[$x] == "{" && $buff == "" ) {
			$buff .= $msg[$x];
		} elseif( $buff == "{ " ) {
			$buff = "";
		} elseif( $buff != "" ) {
			$buff .= $msg[$x];
		}
		if( $buff == "{if" ) {
			$pos = $x;
			$if  = true;
		} elseif( $buff == "{foreach" ) {
			$pos = $x;
			$for = true;
		}
		if( $pos != -1 && $msg[$x] == "}" ) {
			$orig   = $buff;
			$buff   = "";
			$pos    = -1;
			if( $if ) {
				$if     = false;
				$o      = 3;
				$native = array('"; if( ',' ) { $ret .= "');
			} elseif( $for ) {
				$for    = false;
				$o      = 8;
				$native = array('"; foreach( ',' as $key=>$value) { $ret .= "');
			} else {
				continue;
			}
			$cond   = trim(populate(substr($orig,$o,-1),false));
			$native = $native[0].$cond.$native[1];
			$parsed = str_replace($orig,$native,$parsed);
			unset($cond, $o, $orig, $native);
		}
	}
	$parsed = populate($parsed);
	return RunJail($parsed,$obj);
}

/**
 * Describe Alert
 * @param array $alert Alert-Result from DB
 * @return array
 */
function DescribeAlert($alert) {
	$obj = array();
	$i = 0;
	$device = dbFetchRow("SELECT hostname FROM devices WHERE device_id = ?",array($alert['device_id']));
	$obj['hostname'] = $device['hostname'];
	$extra = $alert['details'];
	if( $alert['state'] >= 1 ) {
		$obj['title'] = 'Alert for device '.$device['hostname'].' Alert-ID #'.$alert['id'];
		if( $alert['state'] == 3 ) {
			$obj['title'] .= " got worse";
		} elseif( $alert['state'] == 4 ) {
			$obj['title'] .= " got better";
		}
		foreach( $extra['rule'] as $incident ) {
			$i++;
			$obj['faults'][$i] = $incident;
			foreach( $incident as $k=>$v ) {
				if( !empty($v) && $k != 'device_id' && (stristr($k,'id') || stristr($k,'desc')) && substr_count($k,'_') <= 1 ) {
					$obj['faults'][$i]['string'] .= $k.' => '.$v."; ";
				}
			}
		}
	} elseif( $alert['state'] == 0 ) {
		$id = dbFetchRow("SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state != 2 && alert_log.state != 0 && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1", array($alert['rule_id'],$alert['device_id'],$alert['id']));
		if( empty($id['id']) ) {
			return false;
		}
		$extra = json_decode(gzuncompress($id['details']),true);
		$obj['title'] = 'Device '.$device['hostname'].' recovered from Alert-ID #'.$id['id'];
		$obj['elapsed'] = TimeFormat(strtotime($alert['time_logged'])-strtotime($id['time_logged']));
		$obj['id'] = $id['id'];
		$obj['faults'] = false;
	} else {
		return "Unknown State";
	}
	$obj['uid'] = $alert['id'];
	$obj['severity'] = $alert['severity'];
	$obj['rule'] = $alert['rule'];
	$obj['name'] = $alert['name'];
	$obj['timestamp'] = $alert['time_logged'];
	$obj['contacts'] = $extra['contacts'];
	$obj['state'] = $alert['state'];
	return $obj;
}

/**
 * Format Elapsed Time
 * @param int $secs Seconds elapsed
 * @return string
 */
function TimeFormat($secs){
	$bit = array(
		'y' => $secs / 31556926 % 12,
		'w' => $secs / 604800 % 52,
		'd' => $secs / 86400 % 7,
		'h' => $secs / 3600 % 24,
		'm' => $secs / 60 % 60,
		's' => $secs % 60
	);
	$ret = array();
	foreach($bit as $k => $v){
		if($v > 0) {
			$ret[] = $v . $k;
		}
	}
	if( empty($ret) ) {
		return "none";
	}
	return join(' ', $ret);
}

/**
 * "Safely" run eval
 * @param string $code Code to run
 * @param array $obj Object with variables
 * @return string|mixed
 */
function RunJail($code,$obj) {
	$ret = "";
	eval($code);
	return $ret;
}

/**
 * Populate variables
 * @param string $txt Text with variables
 * @param bool $wrap Wrap variable for text-usage (default: true)
 * @return string
 */
function populate($txt,$wrap=true) {
	preg_match_all('/%([\w\.]+)/', $txt, $m);
	foreach( $m[1] as $tmp ) {
		$orig = $tmp;
		$rep = false;
		if( $tmp == "key" || $tmp == "value" ) {
			$rep = '$'.$tmp;
		} else {
			if( strstr($tmp,'.') ) {
				$tmp = explode('.',$tmp,2);
				$pre = '$'.$tmp[0];
				$tmp = $tmp[1];
			} else {
				$pre = '$obj';
			}
			$rep = $pre."['".str_replace('.',"']['",$tmp)."']";
			if( $wrap ) {
				$rep = "{".$rep."}";
			}
		}
		$txt = str_replace("%".$orig,$rep,$txt);
	}
	return $txt;
}
?>
