<?php

global $config;

$disks=get_disks_with_smart($device, $app['app_id']);

print_optionbar_start();

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'smart',
);

echo generate_link('All Disks', $link_array);
echo '| disks:';

$disks_int=0;
while (isset($disks[$disks_int])) {
    $disk=$disks[$disks_int];
    $label=$disk;

    if ($vars['disk'] == $disk) {
        $label='>>'.$disk.'<<';
    }

    echo generate_link($label, $link_array, array('disk'=>$disk)).', ';

    $disks_int++;
}

print_optionbar_end();

if (isset($vars['disk'])) {
    $graphs = array(
        'smart_big5'=>'Big 5: Reallocated Sector Count(5), Reported Uncorrectable Errors(187), Command Timeout(188), Current Pending Sector Count(197), Offline Uncorrectable(198)',
        'smart_temp'=>'Temperature(C)(190), Air Temperature(C)(194)',
        'smart_ssd'=>'SSD: Wear Leveling Count(173), SSD Life Left(231), Media Wearout Indicator(233)',
        'smart_other'=>'Other: Spin Retry Count(10), Detected Uncorrentable Bad Blocks(183), End-to-End error(184), Reallocation Event Count(196), UltraDMA CRC Error Count(199)',
        'smart_tests_status'=>'Tallies various selftest statuses',
        'smart_tests_ran'=>'Tallies various selftest types',
    );
} else {
    $graphs = array(
        'smart_id5'=>'ID# 5, Reallocated Sectors Count',
        'smart_id10'=>'ID# 10, Spin Retry Count',
        'smart_id173'=>'ID# 173 SSD Wear Leveling Count',
        'smart_id183'=>'ID# 183, Detected Uncorrentable Bad Blocks',
        'smart_id184'=>'ID# 184, End-to-End error / IOEDC',
        'smart_id187'=>'ID# 187, Reported Uncorrectable Errors',
        'smart_id188'=>'ID# 188, Command Timeout',
        'smart_id190'=>'ID# 190, Airflow Temperature (C)',
        'smart_id194'=>'ID# 194, Temperature (C)',
        'smart_id196'=>'ID# 196, Reallocation Event Count',
        'smart_id197'=>'ID# 197, Current Pending Sector Count',
        'smart_id198'=>'ID# 198, Uncorrectable Sector Count or Offline Uncorrectable or Off-Line Scan Uncorrectable Sector Count',
        'smart_id199'=>'ID# 199, UltraDMA CRC Error Count',
        'smart_id231'=>'ID# 231, SSD Life Left',
        'smart_id233'=>'ID# 233, Media Wearout Indicator'
    );
}

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    if (isset($vars['disk'])) {
        $graph_array['disk']=$vars['disk'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
