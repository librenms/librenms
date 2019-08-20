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

$init_modules = array('web', 'graphs', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

$auth = Auth::check() || is_client_authorized($_SERVER['REMOTE_ADDR']);

if (!$auth) {
    die('Unauthorized');
}

set_debug(isset($_GET['debug']));

rrdtool_initialize(false);

require \LibreNMS\Config::get('install_dir') . '/includes/html/graphs/graph.inc.php';

rrdtool_close();

if ($debug) {
    echo '<br />';
    printf("Runtime %.3fs", microtime(true) - $start);
    echo '<br />';
    printStats();
}
