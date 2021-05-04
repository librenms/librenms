<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$auth = false;
$start = microtime(true);

$init_modules = ['web', 'graphs', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    // check for unauthenticated graphs and set auth
    $auth = is_client_authorized($_SERVER['REMOTE_ADDR']);
    if (! $auth) {
        exit('Unauthorized');
    }
}

Debug::set(isset($_GET['debug']));

require \LibreNMS\Config::get('install_dir') . '/includes/html/graphs/graph.inc.php';

Datastore::terminate();

if (Debug::isEnabled()) {
    echo '<br />';
    printf('Runtime %.3fs', microtime(true) - $start);
    echo '<br />';
    printStats();
}
