<?php

$name = 'http_access_log_combined';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

$logs = $app->data['logs'];

$descr_len = 12;

if (! isset($vars['log_stat'])) {
    d_echo('$vars["log_stat"] undef');

    return;
} elseif ($vars['log_stat'] == 'bytes'
          || $vars['log_stat'] == 'bytes_max'
          || $vars['log_stat'] == 'bytes_mean'
          || $vars['log_stat'] == 'bytes_median'
          || $vars['log_stat'] == 'bytes_min'
          || $vars['log_stat'] == 'bytes_mode'
          || $vars['log_stat'] == 'bytes_range'
          || $vars['log_stat'] == 'size'
          || $vars['log_stat'] == 'error_size') {
    $unit_text = $vars['log_stat'] . ' Bytes';
} else {
    $unit_text = $vars['log_stat'] . ' Count';
}

$rrd_list = [];
foreach ($logs as $log) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'logs___' . $log . '___' . $vars['log_stat']]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $log_len = strlen($log);
        if ($descr_len < $log_len) {
            $descr_len = $log_len;
        }

        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $log,
            'ds' => 'data',
        ];
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
