<?php

$psu_list = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'pwrstatd');

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'pwrstatd',
];

$sn_list = [];

foreach ($psu_list as $label) {
    $sn = $label;

    if ($vars['sn'] == $sn) {
        $label = '<span class="pagemenu-selected">' . $label . '</span>';
    }

    array_push($sn_list, generate_link($label, $link_array, ['sn' => $sn]));
}

printf('%s | PSUs: %s', generate_link('All PSUs', $link_array), implode(', ', $sn_list));

print_optionbar_end();

$graphs = [
    'pwrstatd_minutes' => 'Battery Runtime Remaining',
    'pwrstatd_percentage' => 'Percentage Readings',
    'pwrstatd_voltage' => 'Voltage Readings',
    'pwrstatd_wattage' => 'Power Readings',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['sn'])) {
        $graph_array['sn'] = $vars['sn'];
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
