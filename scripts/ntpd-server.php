#!/usr/bin/env php
<?php

    ///////////////////////////////////////////////////////////////////////////////////////
    ///
    //      A small script to grab the NTPD Server statistics from a NTPD server
    //      Needed commands: php, ntpd, ntpq, ntpdc
    ///
    //      Install:
    //              Add the following to your snmpd.conf file:
    //              extend ntpdserver /opt/librenms/scripts/ntpd-server.php
    ///
    //      Version 1.1 By:
    //              All In One - Dennis de Houx <info@all-in-one.be>
    ///
    ///////////////////////////////////////////////////////////////////////////////////////


    // START SETTINGS ///

	$ntpq		= "/usr/sbin/ntpq";
	$ntpdc		= "/usr/sbin/ntpdc";
	
	# Change this to true if you have clk_jitter, sys_jitter in the ntpq -c rv output
	$newstats_style	= false;

    // END SETTINGS ///


    ///
    // DO NOT EDIT BENETH THIS LINE
    ///
    ///////////////////////////////////////////////////////////////////////////////////////

	$cmd	= shell_exec($ntpq." -c rv");
	$cmd2	= shell_exec($ntpdc." -c iostats");
	$vars	= array();
	$vars2	= array();
	$vars	= explode(',', $cmd);
	$vars2	= eregi_replace(' ', '', $cmd2);
	$vars2	= explode("\n", $vars2);
	
	function doSNMPv2($vars, $vars2, $newstats_style) {
	    $ntpd	= array();
	    foreach ($vars as $item=>$value) {
		if (!empty($value)) {
		    $temp = explode('=', $value);
		    if (isset($temp[1])) {
			$ntpd[trim($temp[0])] = trim($temp[1]);
		    }
		}
	    }
	    foreach ($vars2 as $item=>$value) {
		if (!empty($value)) {
		    $temp = explode(':', $value);
		    if (isset($temp[1])) {
			$ntpd[trim($temp[0])] = trim($temp[1]);
		    }
		}
	    }
            $var = array();
	    $var['stratum']			= (isset($ntpd['stratum']) ? $ntpd['stratum'] : "U");
	    $var['offset']			= (isset($ntpd['offset']) ? $ntpd['offset'] : "U");
	    $var['frequency']			= (isset($ntpd['frequency']) ? $ntpd['frequency'] : "U");
	    if ($newstats_style) {
		$var['jitter']			= (isset($ntpd['clk_jitter']) ? $ntpd['clk_jitter'] : "U");
		$var['noise']			= (isset($ntpd['sys_jitter']) ? $ntpd['sys_jitter'] : "U");
		$var['stability']		= (isset($ntpd['clk_wander']) ? $ntpd['clk_wander'] : "U");
	    } else {
		$var['jitter']			= (isset($ntpd['jitter']) ? $ntpd['jitter'] : "U");
		$var['noise']			= (isset($ntpd['noise']) ? $ntpd['noise'] : "U");
		$var['stability']		= (isset($ntpd['stability']) ? $ntpd['stability'] : "U");
	    }
	    $var['uptime']			= (isset($ntpd['timesincereset']) ? $ntpd['timesincereset'] : "U");
	    $var['buffer_recv']			= (isset($ntpd['receivebuffers']) ? $ntpd['receivebuffers'] : "U");
	    $var['buffer_free']			= (isset($ntpd['freereceivebuffers']) ? $ntpd['freereceivebuffers'] : "U");
	    $var['buffer_used']			= (isset($ntpd['usedreceivebuffers']) ? $ntpd['usedreceivebuffers'] : "U");
	    $var['packets_drop']		= (isset($ntpd['droppedpackets']) ? $ntpd['droppedpackets'] : "U");
	    $var['packets_ignore']		= (isset($ntpd['ignoredpackets']) ? $ntpd['ignoredpackets'] : "U");
	    $var['packets_recv']		= (isset($ntpd['receivedpackets']) ? $ntpd['receivedpackets'] : "U");
	    $var['packets_sent']		= (isset($ntpd['packetssent']) ? $ntpd['packetssent'] : "U");
	    foreach ($var as $item=>$count) {
		echo $count."\n";
	    }
	}

	doSNMPv2($vars, $vars2, $newstats_style);

?>
