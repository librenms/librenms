<?php

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';
require realpath(__DIR__ . '/..') . '/includes/generic_search.php';
if (!Auth::check()) {
    die('Unauthorized');
}

set_debug($_REQUEST['debug']);
$output = generic_search($_REQUEST['search'],$_REQUEST['type'],$_REQUEST['map']);

echo($output);
exit();
