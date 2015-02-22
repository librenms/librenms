#!/usr/bin/env php
<?php

///////////////////////////////////////////////////////////////////////////////////////
///
//      A small script to grab the DNS statistics from a PowerDNS server
//      Needed commands: php, pdns_control
///
//      Install:
//              Add the following to your snmpd.conf file:
//              extend powerdns /opt/librenms/scripts/powerdns.php
///
//      Version 1.0 By:
//              All In One - Dennis de Houx <info@all-in-one.be>
///
///////////////////////////////////////////////////////////////////////////////////////


// START SETTINGS ///

	$pdnscontrol	= "/usr/bin/pdns_control";

// END SETTINGS ///


///
// DO NOT EDIT BENETH THIS LINE
///
///////////////////////////////////////////////////////////////////////////////////////

	$cmd	= shell_exec($pdnscontrol." show \*");
	$vars	= array();
	$vars	= explode(',', $cmd);
	
	function doSNMP($vars) {
	    foreach ($vars as $item=>$value) {
		$value = trim($value);
		if (!empty($value)) {
		    echo $value."\n";
		}
	    }
	}
	
	function doSNMPv2($vars) {
	    $pdns	= array();
	    foreach ($vars as $item=>$value) {
		if (!empty($value)) {
		    $temp = explode('=', $value);
		    if (isset($temp[1])) {
			$pdns[$temp[0]] = $temp[1];
		    }
		}
	    }
            $var = array();
	    $var['corrupt-packets']		= (isset($pdns['corrupt-packets']) ? $pdns['corrupt-packets'] : "U");
	    $var['deferred-cache-inserts']	= (isset($pdns['deferred-cache-inserts']) ? $pdns['deferred-cache-inserts'] : "U");
	    $var['deferred-cache-lookup']	= (isset($pdns['deferred-cache-lookup']) ? $pdns['deferred-cache-lookup'] : "U");
	    $var['latency']			= (isset($pdns['latency']) ? $pdns['latency'] : "U");
	    $var['packetcache-hit']		= (isset($pdns['packetcache-hit']) ? $pdns['packetcache-hit'] : "U");
	    $var['packetcache-miss']		= (isset($pdns['packetcache-miss']) ? $pdns['packetcache-miss'] : "U");
	    $var['packetcache-size']		= (isset($pdns['packetcache-size']) ? $pdns['packetcache-size'] : "U");
	    $var['qsize-q']			= (isset($pdns['qsize-q']) ? $pdns['qsize-q'] : "U");
	    $var['query-cache-hit']		= (isset($pdns['query-cache-hit']) ? $pdns['query-cache-hit'] : "U");
	    $var['query-cache-miss']		= (isset($pdns['query-cache-miss']) ? $pdns['query-cache-miss'] : "U");
	    $var['recursing-answers']		= (isset($pdns['recursing-answers']) ? $pdns['recursing-answers'] : "U");
	    $var['recursing-questions']		= (isset($pdns['recursing-questions']) ? $pdns['recursing-questions'] : "U");
	    $var['servfail-packets']		= (isset($pdns['servfail-packets']) ? $pdns['servfail-packets'] : "U");
	    $var['tcp-answers']			= (isset($pdns['tcp-answers']) ? $pdns['tcp-answers'] : "U");
	    $var['tcp-queries']			= (isset($pdns['tcp-queries']) ? $pdns['tcp-queries'] : "U");
	    $var['timedout-packets']		= (isset($pdns['timedout-packets']) ? $pdns['timedout-packets'] : "U");
	    $var['udp-answers']			= (isset($pdns['udp-answers']) ? $pdns['udp-answers'] : "U");
	    $var['udp-queries']			= (isset($pdns['udp-queries']) ? $pdns['udp-queries'] : "U");
	    $var['udp4-answers']		= (isset($pdns['udp4-answers']) ? $pdns['udp4-answers'] : "U");
	    $var['udp4-queries']		= (isset($pdns['udp4-queries']) ? $pdns['udp4-queries'] : "U");
	    $var['udp6-answers']		= (isset($pdns['udp6-answers']) ? $pdns['udp6-answers'] : "U");
	    $var['udp6-queries']		= (isset($pdns['udp6-queries']) ? $pdns['udp6-queries'] : "U");
	    foreach ($var as $item=>$count) {
		echo $count."\n";
	    }
	}

	doSNMPv2($vars);

?>
