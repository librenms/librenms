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

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");

RunAlerts();

/**
 * Run all alerts
 * @return void
 */
function RunAlerts() {
	global $config;
	$default_tpl = "%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: %rule\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}"; //FIXME: Put somewhere else?
	foreach( dbFetchRows("SELECT alerts.device_id, alerts.rule_id, alerts.state FROM alerts WHERE alerts.state != 2 && alerts.open = 1") as $alert ) {
		$alert = dbFetchRow("SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? ORDER BY alert_log.id DESC LIMIT 1",array($alert['device_id'],$alert['rule_id']));
		$alert['details'] = json_decode(gzuncompress($alert['details']),true);
		$noiss = false;
		$noacc = false;
		$updet = false;
		$rextra = json_decode($alert['extra'],true);
		$chk = dbFetchRow('SELECT alerted FROM alerts WHERE device_id = ? && rule_id = ?',array($alert['device_id'],$alert['rule_id']));
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
		if( $updet ) {
			dbUpdate(array('details' => gzcompress(json_encode($alert['details']),9)),'alert_log','id = ?',array($alert['id']));
		}
		if( !empty($rextra['muted']) ) {
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
	$tpl = addslashes($tpl);

	/**
	 * {if ..}..{else}..{/if}
	 */
	preg_match_all('/\\{if (.+)\\}(.+)\\{\\/if\\}/Uims',$tpl,$m);
	foreach( $m[1] as $k=>$if ) {
		$if = preg_replace('/%(\w+)/i','\$obj["$1"]', $if);
		$ret = "";
		$cond = "if( $if ) {\r\n".'$ret = "'.str_replace("{else}",'";'."\r\n} else {\r\n".'$ret = "',$m[2][$k]).'";'."\r\n}\r\n";
		eval($cond); //FIXME: Eval is Evil
		$tpl = str_replace($m[0][$k],$ret,$tpl);
	}

	/**
	 * {foreach %var}..{/foreach}
	 */
	preg_match_all('/\\{foreach (.+)\\}(.+)\\{\\/foreach\\}/Uims',$tpl,$m);
	foreach( $m[1] as $k=>$for ) {
		$for = preg_replace('/%(\w+)/i','\$obj["$1"]', $for);
		$ret = "";
		$loop = 'foreach( '.$for.' as $key=>$value ) { $ret .= "'.str_replace(array("%key","%value"),array('$key','$value'),$m[2][$k]).'"; }';
		eval($loop); //FIXME: Eval is Evil
		$tpl = str_replace($m[0][$k],$ret,$tpl);
	}

	/**
	 * Populate variables with data
	 */
	foreach( $obj as $k=>$v ) {
		$tpl = str_replace("%".$k, $v, $tpl);
	}
	return $tpl;
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
	if( $alert['state'] == 1 ) {
		$obj['title'] = 'Alert for device '.$device['hostname'].' Alert-ID #'.$alert['id'];
		foreach( $extra['rule'] as $incident ) {
			$i++;
			foreach( $incident as $k=>$v ) {
				if( !empty($v) && $k != 'device_id' && (stristr($k,'id') || stristr($k,'desc')) && substr_count($k,'_') <= 1 ) {
					$obj['faults'][$i] .= $k.' => '.$v."; ";
				}
			}
		}
	} elseif( $alert['state'] == 0 ) {
		$id = dbFetchRow("SELECT alert_log.id,alert_log.time_logged,alert_log.details FROM alert_log WHERE alert_log.state = 1 && alert_log.rule_id = ? && alert_log.device_id = ? && alert_log.id < ? ORDER BY id DESC LIMIT 1", array($alert['rule_id'],$alert['device_id'],$alert['id']));
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
?>
