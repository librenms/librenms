#!/usr/bin/env php
<?php
/* Copyright (C) 2014  <singh@devilcode.org>
 * Modified and Relicensed by <f0o@devilcode.org> under the expressed
 * permission by the Copyright-Holder <singh@devilcode.org>.
 * 
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

chdir(dirname($argv[0]));
require_once("includes/defaults.inc.php");
require_once("config.php");
require_once("includes/definitions.inc.php");
require_once("includes/functions.php");
require_once("includes/discovery/functions.inc.php");
error_reporting(E_ERROR);

class ircbot {
	private $data     = "";
	private $authd    = array();
	private $debug    = false;
	private $server   = "";
	private $port     = "";
	private $ssl      = false;
	private $pass     = "";
	private $nick     = "LibreNMS";
	private $chan     = array();
	private $commands = array("auth", "quit", "listdevices", "device", "port", "down", "version", "status", "log", "help", "reload", "join");
	private $external = array();
	private $tick     = 62500;
	
	public function __construct() {
		global $config, $database_link;
		$this->log("Setting up IRC-Bot..");
		if( is_resource($database_link) ) {
			$this->sql = $database_link;
		}
		$this->j = 2;
		$this->config = $config;
		$this->debug = $this->config['irc_debug'];
		$this->config['irc_authtime'] = $this->config['irc_authtime'] ? $this->config['irc_authtime'] : 3;
		$this->max_retry = $this->config['irc_maxretry'];
		$this->server = $this->config['irc_host'];
		if($this->config['irc_port'][0] == "+") {
			$this->ssl  = true;
			$this->port = substr($this->config['irc_port'], 1);
		} else {
			$this->port = $this->config['irc_port'];
		}
		if($this->config['irc_nick']) {
			$this->nick = $this->config['irc_nick'];
		}
		if($this->config['irc_chan']) {
			if(is_array($this->config['irc_chan'])) {
				$this->chan = $this->config['irc_chan'];
			} elseif(strstr($this->config['irc_chan'],",")) {
				$this->chan = explode(",",$this->config['irc_chan']);
			} else {
				$this->chan = array($this->config['irc_chan']);
			}
		}
		if($this->config['irc_alert_chan']) {
			if(strstr($this->config['irc_alert_chan'],",")) {
				$this->config['irc_alert_chan'] = explode(",",$this->config['irc_alert_chan']);
			} else {
				$this->config['irc_alert_chan'] = array($this->config['irc_alert_chan']);
			}
		}
		if($this->config['irc_pass']) {
			$this->pass = $this->config['irc_pass'];
		}
		$this->load_external();
		$this->log("Starting IRC-Bot..");
		$this->init();
	}
	
	private function load_external() {
		if(!$this->config['irc_external']) {
			return true;
		}
		$this->log("Caching external commands...");
		if(!is_array($this->config['irc_external'])) {
			$this->config['irc_external'] = explode(",",$this->config['irc_external']);
		}
		foreach( $this->config['irc_external'] as $ext ) {
			if( ($this->external[$ext] = file_get_contents("includes/ircbot/".$ext.".inc.php")) == "" ) {
				unset($this->external[$ext]);
			}
		}
		return $this->log("Cached ".sizeof($this->external)." commands.");
	}
	
	private function init() {
		if($this->config['irc_alert']) {
			$this->connect_alert();
		}
		$this->connect();
		$this->log("Connected");
		if($this->pass) {
			fwrite($this->socket['irc'], "PASS ".$this->pass."\n\r");
		}
		$this->doAuth();
		while( true ) {
			foreach($this->socket as $n=>$socket) {
				if(!is_resource($socket)) {
					$this->log("Socket '$n' closed. Restarting.");
					break 2;
				}
			}
			$this->getData();
			if($this->config['irc_alert']) {
				$this->alertData();
			}
			usleep($this->tick);
		}
		return $this->init();
	}
	
	private function connect_alert() {
		$f = $this->config['install_dir']."/.ircbot.alert";
		if( ( file_exists($f) && filetype($f) != "fifo" && !unlink($f) ) || ( !file_exists($f) && !posix_mkfifo($f,0644) ) ) {
			$this->log("Error - Cannot create Alert-File");
			return false;
		}
		if( ($this->socket['alert'] = fopen($f,'r+')) ) {
			$this->log("Opened Alert-File");
			stream_set_blocking($this->socket['alert'], false);
			return true;
		}
		$this->log("Error - Cannot open Alert-File");
		return false;
	}
	
	private function read($buff) {
		$r = fread($this->socket[$buff],64);
		$this->buff[$buff] .= $r;
		$r = strlen($r);
		if(strstr($this->buff[$buff],"\n")) {
			$tmp = explode("\n",$this->buff[$buff],2);
			$this->buff[$buff] = substr($this->buff[$buff], strlen($tmp[0])+1);
			if( $this->debug ) {
				$this->log("Returning buffer '$buff': '".trim($tmp[0])."'");
			}
			return $tmp[0];
		}
		if( $this->debug && $r > 0 ) {
			$this->log("Expanding buffer '$buff' = '".trim($this->buff[$buff])."'");
		}
		return false;
	}
	
	private function alertData() {
		if( ($alert = $this->read("alert")) !== false ) {
			$alert = json_decode($alert,true);
			if( !is_array($alert) ) {
				return false;
			}
			if( $this->config['irc_alert_chan'] ) {
				foreach( $this->config['irc_alert_chan'] as $chan ) {
					$this->irc_raw("PRIVMSG ".$chan." :".trim($alert['title'])." - Rule: ".trim($alert['name'] ? $alert['name'] : $alert['rule']).(sizeof($alert['faults']) > 0 ? " - Faults:" : ""));
					foreach( $alert['faults'] as $k=>$v ) {
						$this->irc_raw("PRIVMSG ".$chan." :#".$k." ".$v['string']);
					}
				}
			} else {
				foreach( $this->authd as $nick=>$data ) {
					if( $data['expire'] >= time() ) {
						$this->irc_raw("PRIVMSG ".$nick." :".trim($alert['title'])." - Rule: ".trim($alert['name'] ? $alert['name'] : $alert['rule']).(sizeof($alert['faults']) > 0 ? " - Faults".(sizeof($alert['faults']) > 3 ? " (showing first 3 out of ".sizeof($alert['faults'])." )" : "" ).":" : ""));
						foreach( $alert['faults'] as $k=>$v ) {
							$this->irc_raw("PRIVMSG ".$nick." :#".$k." ".$v['string']);
							if( $k >= 3 ) {
								break;
							}
						}
					}
				}
			}
		}
	}
	
	private function getData() {
		if( ($data = $this->read("irc")) !== false ) {
			$this->data = $data;
			$ex = explode(' ', $this->data);
			if($ex[0] == "PING") {
				return $this->irc_raw("PONG ".$ex[1]);
			}
			if( $ex[1] == 376 || ($ex[1] == "MODE" && $ex[2] == $this->nick) ) {
				if($this->j == 2) {
					$this->joinChan();
					$this->j=0;
				}
			}
			$this->command = str_replace(array(chr(10), chr(13)), '', $ex[3]);
			if(strstr($this->command,":.")) {
				$this->handleCommand();
			}
		}
	}
	
	private function joinChan($chan=false) {
		if( $chan ) {
			$this->chan[] = $chan;
		}
		foreach($this->chan as $chan) {
			$this->irc_raw("JOIN ".$chan);
		}
		return true;
	}
	
	private function handleCommand() {
		$this->command = str_replace(":.","",$this->command);
		$tmp=explode(":.".$this->command." ",$this->data);
		$this->user = $this->get_user();
		if( $this->isAuthd() || trim($this->command) == "auth" ) {
			$this->proceedCommand(str_replace("\n","",trim($this->command)),trim($tmp[1]));
		}
		$this->authd[$this->getUser($this->data)] = $this->user;
		return false;
	}
	
	private function proceedCommand($command,$params) {
		$command = strtolower($command);
		if(in_array($command,$this->commands)) {
			$this->chkdb();
			$this->log($command." ( '".$params."' )");
			return $this->{"_".$command}($params);
		} elseif($this->external[$command]) {
			$this->chkdb();
			$this->log($command." ( '".$params."' ) [Ext]");
			return eval($this->external[$command]);
		}
		return false;
	}
	
	private function respond($msg) {
		$chan = $this->getChan($this->data);
		return $this->sendMessage($msg,strstr($chan,"#") ? $chan : $this->getUser($this->data));
	}
	
	private function getChan($param){
		$data = explode("PRIVMSG ",$this->data,3);
		$data = explode(" ",$data[1],2);
		return $data[0];
	}
	
	private function getUser($param) {
		$arrData = explode("!",$param,2);
		return str_replace(":","",$arrData[0]);
	}
	
	private function connect($try) {
		if($try > $this->max_retry){
			$this->log("Failed too many connection attempts, aborting");
			return die();
		}
		$this->log("Trying to connect to ".$this->server.":".$this->port.($this->ssl?" (SSL)":""));
		if($this->socket['irc']) {
			fclose($this->socket['irc']);
		}
		if($this->ssl) {
			$server = 'ssl://'.$this->server;
		} else {
			$server = $this->server;
		}
		$this->socket['irc'] = fsockopen($server, $this->port);
		if(!is_resource($this->socket['irc'])) {
			return $this->connect($try+1);
		} else {
			stream_set_blocking($this->socket['irc'], false);
			return true;
		}
	}
	
	private function doAuth() {
		if( $this->irc_raw("USER ".$this->nick." 0 ".$this->nick." :".$this->nick) && $this->irc_raw("NICK ".$this->nick) ) {
				return true;
		}
		return false;
	}
	
	private function sendMessage($message,$chan) {
		if( $this->debug ) {
			$this->log("Sending 'PRIVMSG ".trim($chan)." :".trim($message)."'");
		}
		return $this->irc_raw("PRIVMSG ".trim($chan)." :".trim($message));
	}
	
	private function log($msg) {
		echo "[".date("r")."] ".trim($msg)."\n";
		return true;
	}
	
	private function chkdb() {
		if( !is_resource($this->sql) ) {
			if( ($this->sql = mysql_connect($this->config['db_host'],$this->config['db_user'],$this->config['db_pass'])) != false && mysql_select_db($this->config['db_name'])) {
				return true;
			} else {
				$this->log("Cannot connect to MySQL");
				return die();
			}
		} else {
			return true;
		}
	}
	
	private function isAuthd() {
		if( $this->user['expire'] >= time() ) {
			$this->user['expire'] = time()+($this->config['irc_authtime']*3600);
			return true;
		} else {
			return false;
		}
	}
	
	private function get_user() {
		return $this->authd[$this->getUser($this->data)];
	}
	
	private function irc_raw($params) {
		return fputs($this->socket['irc'],$params."\r\n");
	}
	
	private function _auth($params) {
		$params = explode(" ",$params,2);
		if( strlen($params[0]) == 64 ) {
			if( $this->tokens[$this->getUser($this->data)] == $params[0] ) {
				$this->user['expire'] = time()+($this->config['irc_authtime']*3600);
				$tmp = dbFetchRow("SELECT level FROM users WHERE user_id = ?", array($this->user['id']));
				$this->user['level'] = $tmp['level'];
				if( $this->user['level'] < 5 ) {
					foreach( dbFetchRows("SELECT device_id FROM devices_perms WHERE user_id = ?", array($this->user['id'])) as $tmp ) {
						$this->user['devices'][] = $tmp['device_id'];
					}
					foreach( dbFetchRows("SELECT port_id FROM ports_perms WHERE user_id = ?", array($this->user['id'])) as $tmp ) {
						$this->user['ports'][] = $tmp['port_id'];
					}
				}
				return $this->respond("Authenticated.");
			} else {
				return $this->respond("Nope.");
			}
		} else {
			$user = dbFetchRow("SELECT `user_id`,`username`,`email` FROM `users` WHERE `username` = ?", array(mres($params[0])));
			if($user['email'] && $user['username'] == $params[0]) {
				$token = hash("gost",openssl_random_pseudo_bytes(1024));
				$this->tokens[$this->getUser($this->data)] = $token;
				$this->user['name'] = $params[0];
				$this->user['id'] = $user['user_id'];
				if( $this->debug ) {
					$this->log("Auth for '".$params[0]."', ID: '".$user['user_id']."', Token: '".$token."', Mail: '".$user['email']."'");
				}
				if(send_mail($user['email'], "LibreNMS IRC-Bot Authtoken", "Your Authtoken for the IRC-Bot:\r\n\r\n".$token."\r\n\r\n") === true) {
					return $this->respond("Token sent!");
				} else {
					return $this->respond("Sorry, seems like mail doesnt like us.");
				}
			} else {
				return $this->respond("Who are you again?");
			}
		}
		return false;
	}
	
	private function _reload() {
		if( $this->user['level'] == 10 ) {
			global $config;
			include("includes/defaults.inc.php");
			include("config.php");
			$this->respond("Reloading configuration & defaults");
			if( $config != $this->config ) {
				return $this->__construct();
			}
		} else {
			return $this->respond("Permission denied.");
		}
	}
	
	private function _join($params) {
		if( $this->user['level'] == 10 ) {
			return $this->joinChan($params);
		} else {
			return $this->respond("Permission denied.");
		}
	}
	
	private function _quit($params) {
		if( $this->user['level'] == 10 ) {
			return die();
		} else {
			return $this->respond("Permission denied.");
		}
	}
	
	private function _help($params) {
		foreach($this->commands as $cmd) {
			$msg .= ", ".$cmd;
		}
		$msg = substr($msg,2);
		return $this->respond("Available commands: $msg");
	}
	
	private function _version($params) {
		return $this->respond($this->config['project_name_version'].", PHP: ".PHP_VERSION);
	}
	
	private function _log($params) {
		$num = 1;
		if( $params > 1 ) {
			$num = $params;
		}
		if( $this->user['level'] < 5 ) {
			$tmp = dbFetchRows("SELECT `event_id`,`host`,`datetime`,`message`,`type` FROM `eventlog` WHERE `host` IN (".implode(",",$this->user['devices']).") ORDER BY `event_id` DESC LIMIT ".mres($num));
		} else
			$tmp = dbFetchRows("SELECT `event_id`,`host`,`datetime`,`message`,`type` FROM `eventlog` ORDER BY `event_id` DESC LIMIT ".mres($num));
		foreach( $tmp as $device) {
			$hostid = dbFetchRow("SELECT `hostname` FROM `devices` WHERE `device_id` = ".$device['host']);
			$this->respond($device['event_id'] ." ". $hostid['hostname'] ." ". $device['datetime'] ." ". $device['message'] ." ". $device['type']);
		}
		if(!$hostid) {
			$this->respond("Nothing to see, maybe a bug?");
		}
		return true;
	}
	
	private function _down($params) {
		if( $this->user['level'] < 5 ) {
			$tmp = dbFetchRows("SELECT `hostname` FROM `devices` WHERE status=0 AND `device_id` IN (".implode(",",$this->user['devices']).")");
		} else {
			$tmp = dbFetchRows("SELECT `hostname` FROM `devices` WHERE status=0");
		}
		foreach( $tmp as $db ) {
			if($db['hostname']) {
				$msg .= ", ".$db['hostname'];
			}
		}
		$msg = substr($msg,2);
		$msg = $msg ? $msg : "Nothing to show :)";
		return $this->respond($msg);
	}
	
	private function _device($params) {
		$params   = explode(" ",$params);
		$hostname = $params[0];
		$device   = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($hostname));
		if(!$device) {
			return $this->respond("Error: Bad or Missing hostname, use .listdevices to show all devices.");
		}
		if($this->user['level'] < 5 && !in_array($device['device_id'],$this->user['devices'])) {
			return $this->respond("Error: Permission denied.");
		}
		$status  = $device['status']   ? "Up ".formatUptime($device['uptime']) : "Down";
		$status .= $device['ignore']   ? "*Ignored*"  : "";
		$status .= $device['disabled'] ? "*Disabled*" : "";
		return $this->respond($device['os']." ".$device['version']." ".$device['features']." ".$status);
	}
	
	private function _port($params) {
		$params   = explode(" ",$params);
		$hostname = $params[0];
		$ifname   = $params[1];
		if( !$hostname || !$ifname ) {
			return $this->respond("Error: Missing hostname or ifname.");
		}
		$device   = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($hostname));
		$port     = dbFetchRow("SELECT * FROM `ports` WHERE `ifName` = ? OR `ifDescr` = ? AND device_id = ?", array($ifname, $ifname, $device['device_id']));
		if($this->user['level'] < 5 && !in_array($port['port_id'],$this->user['ports']) && !in_array($device['device_id'],$this->user['devices'])) {
			return $this->respond("Error: Permission denied.");
		}
		$bps_in   = formatRates($port['ifInOctets_rate']);
		$bps_out  = formatRates($port['ifOutOctets_rate']);
		$pps_in   = format_bi($port['ifInUcastPkts_rate']);
		$pps_out  = format_bi($port['ifOutUcastPkts_rate']);
		return $this->respond($port['ifAdminStatus']."/".$port['ifOperStatus']." ".$bps_in. " > bps > ".$bps_out." | ".$pps_in."pps > PPS > ".$pps_out."pps");
	}
	
	private function _listdevices($params) {
		if( $this->user['level'] < 5 ) {
			$tmp = dbFetchRows("SELECT `hostname` FROM `devices` WHERE `device_id` IN (".implode(",",$this->user['devices']).")");
		} else {
			$tmp = dbFetchRows("SELECT `hostname` FROM `devices`");
		}
		foreach( $tmp as $device ) {
			$msg .= ", ".$device['hostname'];
		}
		$msg = substr($msg,2);
		$msg = $msg ? $msg : "Nothing to show..?";
		return $this->respond($msg);
	}
	
	private function _status($params) {
		$params     = explode(" ",$params);
		$statustype = $params[0];
		if( $this->user['level'] < 5 ) {
			$d_w = " WHERE device_id IN (".implode(",",$this->user['devices']).")";
			$d_a = " AND   device_id IN (".implode(",",$this->user['devices']).")";
			$p_w = " WHERE  port_id IN (".implode(",",$this->user['ports']).") OR device_id IN (".implode(",",$this->user['devices']).")";
			$p_a = " AND (I.port_id IN (".implode(",",$this->user['ports']).") OR I.device_id IN (".implode(",",$this->user['devices'])."))";
		}
		switch($statustype) {
			case "devices":
			case "device":
			case "dev":
				$devcount = array_pop(dbFetchRow("SELECT count(*) FROM devices".$d_w));
				$devup = array_pop(dbFetchRow("SELECT count(*) FROM devices  WHERE status = '1' AND `ignore` = '0'".$d_a));
				$devdown = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE status = '0' AND `ignore` = '0'".$d_a));
				$devign = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE `ignore` = '1'".$d_a));
				$devdis = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE `disabled` = '1'".$d_a));
				$msg = "Devices: " .$devcount . " (" .$devup . " up, " .$devdown . " down, " .$devign . " ignored, " .$devdis . " disabled" . ")";
			break;
			case "ports":
			case "port":
			case "prt":
				$prtcount = array_pop(dbFetchRow("SELECT count(*) FROM ports".$p_w));
				$prtup = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D  WHERE I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'".$p_a));
				$prtdown = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'".$p_a));
				$prtsht = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'".$p_a));
				$prtign = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '1' OR D.ignore = '1')".$p_a));
				$prterr = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')".$p_a));
				$msg = "Ports: " .$prtcount . " (" .$prtup . " up, " .$prtdown . " down, " .$prtign . " ignored, " .$prtsht . " shutdown" . ")";
			break;
			case "services":
			case "service":
			case "srv":
				$srvcount = array_pop(dbFetchRow("SELECT count(service_id) FROM services".$d_w));
				$srvup = array_pop(dbFetchRow("SELECT count(service_id) FROM services  WHERE service_status = '1' AND service_ignore ='0'".$d_a));
				$srvdown = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'".$d_a));
				$srvign = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_ignore = '1'".$d_a));
				$srvdis = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_disabled = '1'".$d_a));
				$msg = "Services: " .$srvcount . " (" .$srvup . " up, " .$srvdown . " down, " .$srvign . " ignored, " .$srvdis . " disabled" . ")";
			break;
			default:
				$msg = "Error: STATUS requires one of the following: <devices|device|dev>|<ports|port|prt>|<services|service|src>";
			break;
		}
		return $this->respond($msg);
	}
}
$irc = new ircbot();
?>
