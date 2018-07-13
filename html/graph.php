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

$init_modules = array('web', 'graphs');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug(isset($_GET['debug']));

rrdtool_initialize(false);

require $config['install_dir'] . '/html/includes/graphs/graph.inc.php';

rrdtool_close();

if ($debug) {
    echo '<br />';
    printf("Runtime %.3fs", microtime(true) - $start);
    echo '<br />';
    printStats();
}
