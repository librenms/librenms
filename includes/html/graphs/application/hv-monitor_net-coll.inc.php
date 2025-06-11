<?php

$name = 'hv-monitor';
$unit_text = 'Per Second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['vmif'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vmif', $vars['vm'], '__-__', $vars['vmif']]);
} elseif (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}
    $descr = 'Collisions';
    $ds = 'coll';


require 'includes/html/graphs/generic_stats.inc.php';
