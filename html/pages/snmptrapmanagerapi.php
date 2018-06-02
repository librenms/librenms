<?php

$mOldER = ini_get('error_reporting');
$mOldLE = ini_get('log_errors');
$mOldDE = ini_get('display_errors');
$mOldDSE = ini_get('display_startup_errors');

ini_set('error_reporting', E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

#$init_modules = array('polling', 'alerts');
#require DIR . '/includes/init.php';

require_once dirname(__FILE__) . '/../../includes/defaults.inc.php';
require_once dirname(__FILE__) . '/../../config.php';
require_once dirname(__FILE__) . '/../../includes/definitions.inc.php';
require_once dirname(__FILE__) . '/../../includes/functions.inc.php';
require_once dirname(__FILE__) . '/../../includes/functions.php';
//require_once dirname(__FILE__) . '/../../includes/authenticate.inc.php';
require_once dirname(__FILE__) . '/../../includes/vars.inc.php';

require_once dirname(__FILE__) . '/../../includes/snmptrapmanager/MIBUpAutoload.php';

MIBUpAutoload::register();

try {
        $oMIBUp = new MIBUpPlugin();
        $oMIBUp->processAPI();
} catch (MIBUpException $ex) {
        echo 'Something went wrong with SNMPTrapmanager Feature: ' . $ex->getMessage();
}

ini_set('error_reporting', $mOldER);
ini_set('log_errors', $mOldLE);
ini_set('display_errors', $mOldDE);
ini_set('display_startup_errors', $mOldDSE);
