<?php

$mOldER = ini_get('error_reporting');
$mOldLE = ini_get('log_errors');
$mOldDE = ini_get('display_errors');
$mOldDSE = ini_get('display_startup_errors');

ini_set('error_reporting', E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require '../../../../includes/defaults.inc.php';
require dirname(__FILE__) . '/config.php';
require '../../../../includes/definitions.inc.php';
require '../../../../includes/functions.php';

require_once dirname(__FILE__) . '/../system/MIBUpAutoload.php';
require_once dirname(__FILE__) . '/MIBUpTestCase.php';

MIBUpAutoload::register();
