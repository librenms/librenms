<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'logsize',
];

print_optionbar_start();

echo generate_link('Basics', $link_array);
echo ' | Sets:';
$sets = $app->data['sets'] ?? [];
$sets_list=array_keys($sets);
sort($sets_list);
foreach ($sets_list as $index => $log_set) {
    $label = $vars['log_set'] == $log_set
        ? '<span class="pagemenu-selected">' . $log_set . '</span>'
        : $log_set;

    echo generate_link($label, $link_array, ['log_set' => $log_set]) . "\n";

    if ($index < (count($sets_list) - 1)) {
        echo ', ';
    }
}

if (isset($vars['log_set']) && isset($sets[$vars['log_set']]) ) {
    echo "<br>\n Files: \n";
    $log_files=$sets[$vars['log_set']]['files'];
    sort($log_files);

    foreach ($log_files as $index => $log_file) {
        $label = $vars['log_file'] == $log_file
            ? '<span class="pagemenu-selected">' . $log_file . '</span>'
            : $log_file;

        echo generate_link($label, $link_array, ['log_set' => $vars['log_set'], 'log_file'=>$log_file]) . "\n";

        if ($index < (count($log_files) - 1)) {
            echo ', ';
        }
    }
}

print_optionbar_end();

if (isset($vars['log_file']) && isset($vars['log_set'])){
    $graphs = [
        'logsize_size'=>'Log Size',
        'logsize_1d_size_diff'=>'Log Size Difference, -1 day',
        'logsize_1d_size_diffp'=>'Log Size Difference Percentage, -1 day',
        'logsize_2d_size_diff'=>'Log Size Difference, -2 days',
        'logsize_2d_size_diffp'=>'Log Size Difference Percentage, -2 days',
        'logsize_3d_size_diff'=>'Log Size Difference, -3 days',
        'logsize_3d_size_diffp'=>'Log Size Difference Percentage, -3 days',
        'logsize_4d_size_diff'=>'Log Size Difference, -4 days',
        'logsize_4d_size_diffp'=>'Log Size Difference Percentage, -4 days',
        'logsize_5d_size_diff'=>'Log Size Difference, -5 days',
        'logsize_5d_size_diffp'=>'Log Size Difference Percentage, -5 days',
        'logsize_6d_size_diff'=>'Log Size Difference, -6 days',
        'logsize_6d_size_diffp'=>'Log Size Difference Percentage, -6 days',
        'logsize_7d_size_diff'=>'Log Size Difference, -7 days',
        'logsize_7d_size_diffp'=>'Log Size Difference Percentage, -7 days',
    ];
}elseif(isset($vars['log_set'])){
    $graphs = [
        'logsize_size'=>'Set Size',
        'logsize_log_sizes'=>'Log Sizes',
        'logsize_max_size'=>'Max Log Size',
        'logsize_max_size_diff'=>'Max Size Difference',
        'logsize_max_size_diffp'=>'Max Size Difference, Percentage ',
        'logsize_min_size'=>'Min Log Size',
        'logsize_min_size_diff'=>'Min Size Difference',
        'logsize_min_size_diffp'=>'Min Size Difference, Percentage',
    ];
}else{
    $graphs = [
        'logsize_size'=>'Total Size',
        'logsize_set_sizes'=>'Set Sizes',
        'logsize_max_size'=>'Max Log Size',
        'logsize_max_size_diff'=>'Max Size Difference',
        'logsize_max_size_diffp'=>'Max Size Difference, Percentage ',
        'logsize_min_size'=>'Min Log Size',
        'logsize_min_size_diff'=>'Min Size Difference',
        'logsize_min_size_diffp'=>'Min Size Difference, Percentage',
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
