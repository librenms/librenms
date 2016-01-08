<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphing
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */


$start = microtime(true);

require_once 'Net/IPv4.php';

if (isset($_GET['debug'])) {
    $debug = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', E_ALL);
}
else {
    $debug = false;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

require_once '../includes/defaults.inc.php';
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once '../includes/common.php';
require_once '../includes/console_colour.php';
require_once '../includes/dbFacile.php';
require_once '../includes/rewrites.php';
require_once 'includes/functions.inc.php';
require_once '../includes/rrdtool.inc.php';
require_once 'includes/authenticate.inc.php';

require 'includes/graphs/graph.inc.php';

$console_color = new Console_Color2();

$end = microtime(true);
$run = ($end - $start);


d_echo('<br />Runtime '.$run.' secs');
d_echo('<br />MySQL: Cell    '.($db_stats['fetchcell'] + 0).'/'.round(($db_stats['fetchcell_sec'] + 0), 3).'s'.' Row    '.($db_stats['fetchrow'] + 0).'/'.round(($db_stats['fetchrow_sec'] + 0), 3).'s'.' Rows   '.($db_stats['fetchrows'] + 0).'/'.round(($db_stats['fetchrows_sec'] + 0), 3).'s'.' Column '.($db_stats['fetchcol'] + 0).'/'.round(($db_stats['fetchcol_sec'] + 0), 3).'s');
