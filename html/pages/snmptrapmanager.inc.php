<?php namespace snmptrapmanager;

$mOldER = ini_get('error_reporting');
$mOldLE = ini_get('log_errors');
$mOldDE = ini_get('display_errors');
$mOldDSE = ini_get('display_startup_errors');

ini_set('error_reporting', E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once dirname(__FILE__) . '/../../includes/snmptrapmanager/system/MIBUpAutoload.php';
/*
$dir = dirname(__FILE__) . '/../../includes/snmptrapmanager/';
$dh  = opendir($dir);
$dir_list = array($dir);
while (false !== ($filename = readdir($dh))) {
    if($filename!="."&&$filename!=".."&&is_dir($dir.$filename))
        array_push($dir_list, $dir.$filename."/");
}
foreach ($dir_list as $dir) {
    foreach (glob($dir."*.php") as $filename)
        require_once $filename;
}
*/

MIBUpAutoload::register();

try {
        $oMIBUp = new MIBUpPlugin();
        $oMIBUp->processPlugin();
} catch (MIBUpException $ex) {
        echo 'Something went wrong with SNMP Trapmanager feature: ' . $ex->getMessage();
}

ini_set('error_reporting', $mOldER);
ini_set('log_errors', $mOldLE);
ini_set('display_errors', $mOldDE);
ini_set('display_startup_errors', $mOldDSE);
