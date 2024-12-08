<?php

$name = 'mojo_cape_submit';
$unit_text = 'Changed Hashes';
$colours = 'psychedelic';
$descr = 'Change Count';
$ds = 'hash_changed';

if (isset($vars['slug'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'slugs___-___' . $vars['slug']]);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
