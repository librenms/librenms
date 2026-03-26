<?php

$name = 'mojo_cape_submit';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$descr = 'Size Sum';
$ds = 'Size Sum';

if (isset($vars['slug'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'slugs___-___' . $vars['slug']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

require 'includes/html/graphs/generic_stats.inc.php';
