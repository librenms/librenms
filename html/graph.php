<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage graphing
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */


$start = microtime(true);

if (isset($_GET['debug'])) {
    $debug = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', E_ALL);
} else {
    $debug = false;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

$init_modules = array('web', 'graphs');
require realpath(__DIR__ . '/..') . '/includes/init.php';

rrdtool_initialize(false);

require $config['install_dir'] . '/html/includes/graphs/graph.inc.php';

rrdtool_close();

if ($debug) {
    echo '<br />';
    printf("Runtime %.3fs", microtime(true) - $start);
    echo '<br />';
    printStats();
}
