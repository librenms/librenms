<?php

$disks = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'smart');

print_optionbar_start();

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'smart',
];

$drives = [];

foreach ($disks as $label) {
    $disk = $label;

    if ($vars['disk'] == $disk) {
        $label = sprintf('⚫ %s', $label);
    }

    array_push($drives, generate_link($label, $link_array, ['disk'=>$disk]));
}

printf('%s | drives: %s', generate_link('All Drives', $link_array), implode(', ', $drives));

print_optionbar_end();

if (isset($vars['disk'])) {
    $graphs = [
        'smart_big5'        => 'Reliability / Age',
        'smart_temp'        => 'Temperature',
        'smart_ssd'         => 'SSD-specific',
        'smart_other'       => 'Other',
        'smart_tests_status'=> 'S.M.A.R.T self-tests results',
        'smart_tests_ran'   => 'S.M.A.R.T self-tests run count',
        'smart_runtime'     => 'Power On Hours',
    ];
} else {
    $graphs = [
        'smart_id5'=>'ID# 5, Reallocated Sectors Count',
        'smart_id9'=>'ID# 9, Power On Hours',
        'smart_id10'=>'ID# 10, Spin Retry Count',
        'smart_id173'=>'ID# 173, SSD Wear Leveller Worst Case Erase Count',
        'smart_id177'=>'ID# 177, SSD Wear Leveling Count',
        'smart_id183'=>'ID# 183, Detected Uncorrectable Bad Blocks',
        'smart_id184'=>'ID# 184, End-to-End error / IOEDC',
        'smart_id187'=>'ID# 187, Reported Uncorrectable Errors',
        'smart_id188'=>'ID# 188, Command Timeout',
        'smart_id190'=>'ID# 190, Airflow Temperature (C)',
        'smart_id194'=>'ID# 194, Temperature (C)',
        'smart_id196'=>'ID# 196, Reallocation Event Count',
        'smart_id197'=>'ID# 197, Current Pending Sector Count',
        'smart_id198'=>'ID# 198, Uncorrectable Sector Count / Offline Uncorrectable / Off-Line Scan Uncorrectable Sector Count',
        'smart_id199'=>'ID# 199, UltraDMA CRC Error Count',
        'smart_id231'=>'ID# 231, SSD Life Left',
        'smart_id233'=>'ID# 233, Media Wearout Indicator',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['disk'])) {
        $graph_array['disk'] = $vars['disk'];
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
