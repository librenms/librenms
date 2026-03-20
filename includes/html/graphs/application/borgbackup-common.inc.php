<?php

$name = 'borgbackup';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$unitlen = 0;
$bigdescrlen = 18;
$smalldescrlen = 15;

$rrdVar = $metric;

if (isset($vars['borgrepo'])) {
    $name_part = 'repos___' . $vars['borgrepo'] . '___' . $rrdVar;
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $name_part]);

    require 'includes/html/graphs/generic_stats.inc.php';
} else {
    $repos = array_keys($app->data['repos'] ?? []);
    sort($repos);

    $int = 0;
    $rrd_list = [];
    while (isset($repos[$int])) {
        $repo = $repos[$int];
        $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo);
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'repos___' . $repo_key . '___' . $rrdVar]);

        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr' => $repo,
                'ds' => 'data',
            ];
        }
        $int++;
    }

    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
}
