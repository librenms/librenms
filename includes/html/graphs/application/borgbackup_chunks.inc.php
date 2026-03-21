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

if (isset($vars['chunks_logscale']) && $vars['chunks_logscale'] == 1) {
    $graph_params->logarithmic = true;
}

$metrics = ['total_chunks', 'total_unique_chunks'];

if (isset($vars['borgrepo'])) {
    $repo = $vars['borgrepo'];
    $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo);

    $rrd_list = [];
    foreach ($metrics as $m) {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'repos___' . $repo_key . '___' . $m]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr' => $m,
                'ds' => 'data',
            ];
        }
    }

    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
} else {
    $repos = array_keys($app->data['repos'] ?? []);
    sort($repos);

    $rrd_list = [];
    foreach ($repos as $repo) {
        $repo_key = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $repo);
        foreach ($metrics as $m) {
            $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'repos___' . $repo_key . '___' . $m]);
            if (Rrd::checkRrdExists($rrd_filename)) {
                $rrd_list[] = [
                    'filename' => $rrd_filename,
                    'descr' => $repo . ' - ' . $m,
                    'ds' => 'data',
                ];
            }
        }
    }

    require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
}
