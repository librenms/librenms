<?php

$name = 'mojo_cape_submit';
$unit_text = 'Submissions';
$colours = 'psychedelic';
$descr = 'Sub Count';
$ds = 'sub_count';

if (isset($vars['slug'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'slugs___-___' . $vars['slug']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

require 'includes/html/graphs/generic_stats.inc.php';
