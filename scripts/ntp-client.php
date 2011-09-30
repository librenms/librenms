#!/usr/bin/env php
<?php

    ########################################################################################
    ##
    ##      A small script to grab the NTP Client statistics from a NTPD server
    ##      Needed commands: php, ntpd, ntpq
    ##
    ##      Install:
    ##              Add the following to your snmpd.conf file:
    ##              extend ntpclient /opt/observium/scripts/ntp-client.php
    ##
    ##      Version 1.0 By:
    ##              All In One - Dennis de Houx <info@all-in-one.be>
    ##
    ########################################################################################


    #### START SETTINGS ####

	$ntpq		= "/usr/sbin/ntpq";

    #### END SETTINGS ####


    ##
    ## DO NOT EDIT BENETH THIS LINE
    ##
    ########################################################################################

	$cmd	= shell_exec($ntpq." -c rv | grep '^offset'");
	$cmd2	= shell_exec($ntpq." -c rv | grep '^stability'");
	$vars	= array();
	$vars2	= array();
	$vars	= explode(',', $cmd);
	$vars2	= explode(',', $cmd2);
	
	function doSNMPv2($vars, $vars2) {
	    $ntp	= array();
	    foreach ($vars as $item=>$value) {
		if (!empty($value)) {
		    $temp = explode('=', $value);
		    if (isset($temp[1])) {
			$ntp[trim($temp[0])] = trim($temp[1]);
		    }
		}
	    }
	    foreach ($vars2 as $item=>$value) {
		if (!empty($value)) {
		    $temp = explode('=', $value);
		    if (isset($temp[1])) {
			$ntp[trim($temp[0])] = trim($temp[1]);
		    }
		}
	    }
	    $var['offset']			= (isset($ntp['offset']) ? $ntp['offset'] : "U");
	    $var['frequency']			= (isset($ntp['frequency']) ? $ntp['frequency'] : "U");
	    $var['jitter']			= (isset($ntp['jitter']) ? $ntp['jitter'] : "U");
	    $var['noise']			= (isset($ntp['noise']) ? $ntp['noise'] : "U");
	    $var['stability']			= (isset($ntp['stability']) ? $ntp['stability'] : "U");
	    foreach ($var as $item=>$count) {
		echo $count."\n";
	    }
	}

	doSNMPv2($vars, $vars2);

?>
