#!/usr/bin/env php
<?php

    ///////////////////////////////////////////////////////////////////////////////////////
    ///
    //      A small script to grab the realtime statistics from a ShoutCast server
    //      Needed commands: php
    ///
    //      Install:
    //              Edit the shoutcast.conf file
    //              Add a crontab (every 5 min) for:
    //              /opt/librenms/scripts/shoutcast.php makeCache
    //              Add the following to your snmpd.conf file:
    //              extend shoutcast /opt/librenms/scripts/shoutcast.php
    ///
    //      Version 1.1 By:
    //              All In One - Dennis de Houx <info@all-in-one.be>
    ///
    ///////////////////////////////////////////////////////////////////////////////////////


    // START SETTINGS ///

	$config		= "/opt/librenms/scripts/shoutcast.conf";
	$cache		= "/opt/librenms/scripts/shoutcast.cache";

    // END SETTINGS ///


    ///
    // DO NOT EDIT BENETH THIS LINE
    ///
    ///////////////////////////////////////////////////////////////////////////////////////

	/* Do NOT run this script through a web browser */
	if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR'])) {
	    die('<span style="color: #880000; text-weight: bold; font-size: 1.3em;">This script is only meant to run at the command line.</span>');
	}
	
	$cmd	= (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "");

	function get_data($host, $port) {
	    $fp		= @fsockopen($host, $port, &$errno, &$errstr, 5);
	    if(!$fp) { $connect = 0; }
	    if (!isset($connect)) {
		fputs($fp, "GET /7.html HTTP/1.0\r\n"
		    . "User-Agent: All In One - SHOUTcast Stats Parser"
		    . " (Mozilla Compatible)\r\n\r\n");
		while (!feof($fp)) {
		    $rawdata	= fgets($fp, 1024);
		}
		fclose($fp);
	    }
	    preg_match('/body>(.*)<\/body/', $rawdata, $matches);
	    $res	= explode(',', $matches[1], 7);
	    $res[7]	= $host;
	    $res[8]	= $port;
	    return $res;
	}
	
	function get_list($config) {
	    if (file_exists($config)) {
		$servers		= file($config);
		$data			= array();
		foreach ($servers as $item=>$server) {
		    list($host, $port)	= split(":", $server, 2);
		    array_push($data, get_data(trim($host), trim($port)));
		}
		return $data;
	    }
	}
	
	function doSNMPv2($vars) {
	    $res = array();
	    foreach ($vars as $items=>$server) {
                $var = array();
		$var['bitrate']		= (isset($server['5']) ? (($server['5'] / 8) * 1000) : "0");
		//$var['bitrate']		= (isset($server['5']) ? ($server['5'] * 1024) : "0");
		$var['traf_in']		= (isset($server['1']) ? ($var['bitrate'] * $server['1']) : "0");
		$var['traf_out']	= (isset($server['0']) ? ($var['bitrate'] * $server['0']) : "0");
		$var['current']		= (isset($server['0']) ? $server['0'] : "0");
		$var['status']		= (isset($server['1']) ? $server['1'] : "0");
		$var['peak']		= (isset($server['2']) ? $server['2'] : "0");
		$var['max']		= (isset($server['3']) ? $server['3'] : "0");
		$var['unique']		= (isset($server['4']) ? $server['4'] : "0");
		$host			= (isset($server['7']) ? $server['7'] : "unknown");
		$port			= (isset($server['8']) ? $server['8'] : "unknown");
		$tmp			= $host.":".$port;
		foreach ($var as $item=>$value) {
		    $tmp .= ";".$value;
		}
		array_push($res, $tmp);
	    }
	    return $res;
	}

	function makeCacheFile($data, $cache) {
	    $fp = fopen($cache, 'w');
	    foreach ($data as $item=>$value) {
		fwrite($fp, $value."\n");
	    }
	    fclose($fp);
	}
	
	function readCacheFile($cache) {
	    if (file_exists($cache)) {
		$data		= file($cache);
		foreach ($data as $item=>$value) {
		    echo trim($value)."\n";
		}
	    }
	}

	if ($cmd == "makeCache") {
	    $servers	= get_list($config);
	    $data	= doSNMPv2($servers);
	    makeCacheFile($data, $cache);
	} else {
	    readCacheFile($cache);
	}

?>
