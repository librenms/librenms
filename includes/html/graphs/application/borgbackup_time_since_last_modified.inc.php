<?php

$name = 'borgbackup';
$ds = 'data';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$unitlen = 4;
$bigdescrlen = 18;
$smalldescrlen = 5;

if (isset($vars['borgrepo'])) {
    $repo = $vars['borgrepo'];
    $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo);
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'repos___' . $repo_key . '___time_since_last_modified']);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list = [
            [
                'filename' => $rrd_filename,
                'descr' => $repo,
                'ds' => $ds,
            ],
        ];
    }

    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
} else {
    $repos = array_keys($app->data['repos'] ?? []);
    sort($repos);

    $rrd_list = [];
    foreach ($repos as $repo) {
        $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo);
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'repos___' . $repo_key . '___time_since_last_modified']);

        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr' => $repo,
                'ds' => $ds,
            ];
        }
    }

    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
}
