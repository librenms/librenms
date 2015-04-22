#!/usr/bin/env php
<?php

    ///////////////////////////////////////////////////////////////////////////////////////
    ///
    //      A small script to grab the MailScanner statistics from a MailScanner server
    //      Needed commands: php, MailScanner, WatchMailLog, perl
    ///
    //      Install:
    //              Add the WatchMailLog Daemon to the rc.local so its start on server boot
    //              Run the WatchMailLog Daemon to start grabbing statistics from log files
    //              Add the following to your snmpd.conf file:
    //              extend mailwatch /opt/librenms/scripts/mailwatch.php
    ///
    //      Version 1.0 By:
    //              All In One - Dennis de Houx <info@all-in-one.be>
    ///
    ///////////////////////////////////////////////////////////////////////////////////////


    // START SETTINGS ///

	$mailstats	= "/opt/librenms/scripts/watchmaillog/watchmaillog_counters";

    // END SETTINGS ///


    ///
    // DO NOT EDIT BENETH THIS LINE
    ///
    ///////////////////////////////////////////////////////////////////////////////////////

	function doSNMPv2($vars) {
	    $stats	= array();
	    if (file_exists($vars)) {
		$data		= file($vars);
		foreach ($data as $item=>$value) {
		    if (!empty($value)) {
			$temp = explode(':', trim($value));
			if (isset($temp[1])) {
			    $stats[$temp[0]] = $temp[1];
			}
		    }
		}
	    }
            $var = array();
	    $var['mess_recv']		= (isset($stats['mess_recv']) ? $stats['mess_recv'] : "U");
	    $var['mess_rejected']	= (isset($stats['mess_rejected']) ? $stats['mess_rejected'] : "U");
	    $var['mess_relay']		= (isset($stats['mess_relay']) ? $stats['mess_relay'] : "U");
	    $var['mess_sent']		= (isset($stats['mess_sent']) ? $stats['mess_sent'] : "U");
	    $var['mess_waiting']	= (isset($stats['mess_waiting']) ? $stats['mess_waiting'] : "U");
	    $var['spam']		= (isset($stats['spam']) ? $stats['spam'] : "U");
	    $var['virus']		= (isset($stats['virus']) ? $stats['virus'] : "U");
	    foreach ($var as $item=>$count) {
		echo $count."\n";
	    }
	}
	
	function clearStats($mailstats) {
	    if (file_exists($mailstats)) {
		$fp	= fopen($mailstats, 'w');
		fwrite($fp, "mess_recv:0\n");
		fwrite($fp, "mess_rejected:0\n");
		fwrite($fp, "mess_relay:0\n");
		fwrite($fp, "mess_sent:0\n");
		fwrite($fp, "mess_waiting:0\n");
		fwrite($fp, "spam:0\n");
		fwrite($fp, "virus:0\n");
		fclose($fp);
	    }
	}

	doSNMPv2($mailstats);
	//clearStats($mailstats);

?>
