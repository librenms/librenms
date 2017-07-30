<?php
// $entry[0] => device name (as string)
// $entry[1] => OID
// $entry[2] => param:value string

///*
$sFile = dirname(__FILE__) . '/../../html/plugins/MIBUploader/system/MIBUpAutoload.php';
require_once $sFile;
MIBUpAutoload::register();
//*/

try {
	$oCtrl = MIBUpCtrl::load('Trap');
	$oCtrl->trap($device['device_id'], $entry[1], $entry[2]);
} catch (MIBUpException $ex) {
	$sErr = 'MIBUploader trap receive failure: ' . $ex->getMessage();
	logfile($sErr);
	log_event($sErr);
}

/*
logfile("-------------");
logfile("Entries: " . MIBUpUtils::vardump($entry));
*/