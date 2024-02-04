<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'logsize',
];

$extra_vars = [
    'log_set',
    'log_file',
];

require 'includes/html/graphs/generic_stats_options_init.inc.php';

$no_minus_d = $app->data['no_minus_d'] ?? false;

print_optionbar_start();

echo generate_link('Basics', $link_array);
echo ' | Sets:';
$sets = $app->data['sets'] ?? [];
$sets_list = array_keys($sets);
sort($sets_list);
foreach ($sets_list as $index => $log_set) {
    $label = $vars['log_set'] == $log_set
        ? '<span class="pagemenu-selected">' . $log_set . '</span>'
        : $log_set;

    $new_link_extra_array = $link_extra_array;
    $new_link_extra_array['log_set'] = $log_set;
    unset($new_link_extra_array['log_file']);

    echo generate_link($label, $link_array, $new_link_extra_array) . "\n";

    if ($index < (count($sets_list) - 1)) {
        echo ', ';
    }
}

if (isset($vars['log_set']) && isset($sets[$vars['log_set']])) {
    $log_files = $sets[$vars['log_set']]['files'];
    $log_count = count($log_files);
    echo "<br>\nFiles Count: " . $log_count . "<br>\nFiles: \n";
    sort($log_files);

    foreach ($log_files as $index => $log_file) {
        $label = $vars['log_file'] == $log_file
            ? '<span class="pagemenu-selected">' . $log_file . '</span>'
            : $log_file;

        $new_link_extra_array = $link_extra_array;
        $new_link_extra_array['log_file'] = $log_file;

        echo generate_link($label, $link_array, $new_link_extra_array) . "\n";

        if ($index < (count($log_files) - 1)) {
            echo ', ';
        }
    }
}

require 'includes/html/graphs/generic_stats_options_line.inc.php';

print_optionbar_end();

if (isset($vars['log_file']) && isset($vars['log_set'])) {
    $graphs = [
        'logsize_size' => 'Log Size',
    ];
} elseif (isset($vars['log_set'])) {
    $graphs = [
        'logsize_size' => 'Set Size',
        'logsize_log_sizes' => 'Log Sizes, Top 12',
        'logsize_combined_stats' => 'Combined Log Stats',
        'logsize_max_size' => 'Max Log Size',
        'logsize_mean_size' => 'Mean Log Size',
        'logsize_median_size' => 'Median Log Size',
        'logsize_mode_size' => 'Mode Log Size',
        'logsize_min_size' => 'Min Log Size',
    ];
} else {
    $graphs = [
        'logsize_size' => 'Total Size',
        'logsize_set_sizes' => 'Set Sizes',
        'logsize_combined_stats' => 'Combined Set Stats',
        'logsize_max_size' => 'Max Set Size',
        'logsize_mean_size' => 'Mean Set Size',
        'logsize_median_size' => 'Median Set Size',
        'logsize_mode_size' => 'Mode Set Size',
        'logsize_min_size' => 'Min Set Size',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['log_set'])) {
        $graph_array['log_set'] = $vars['log_set'];
    }

    if (isset($vars['log_file'])) {
        $graph_array['log_file'] = $vars['log_file'];
    }

    $graph_array['gstats_no_hourly'] = $vars['gstats_no_hourly'];
    $graph_array['gstats_no_hourly_min'] = $vars['gstats_no_hourly_min'];
    $graph_array['gstats_no_hourly_max'] = $vars['gstats_no_hourly_max'];
    $graph_array['gstats_no_daily'] = $vars['gstats_no_daily'];
    $graph_array['gstats_no_daily_min'] = $vars['gstats_no_daily_min'];
    $graph_array['gstats_no_daily_max'] = $vars['gstats_no_daily_max'];
    $graph_array['gstats_no_weekly'] = $vars['gstats_no_weekly'];
    $graph_array['gstats_no_weekly_min'] = $vars['gstats_no_weekly_min'];
    $graph_array['gstats_no_weekly_max'] = $vars['gstats_no_weekly_max'];
    $graph_array['gstats_no_percentile'] = $vars['gstats_no_percentile'];
    $graph_array['gstats_no_percentile_x0'] = $vars['gstats_no_percentile_x0'];
    $graph_array['gstats_percentile_x0_val'] = $vars['gstats_percentile_x0_val'];
    $graph_array['gstats_no_percentile_x1'] = $vars['gstats_no_percentile_x1'];
    $graph_array['gstats_percentile_x1_val'] = $vars['gstats_percentile_x1_val'];

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
