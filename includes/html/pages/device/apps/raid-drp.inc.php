<?php

global $config;

$pools=get_raid_drp_arrays($device['device_id']);

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'zfs',
);

print_optionbar_start();

echo 'Arrays:';
$pool_int=0;
while (isset($pools[$pool_int])) {
    $pool=$pools[$pool_int];
    $label=$pool;

    if ($vars['pool'] == $pool) {
        $label='>>'.$pool.'<<';
    }

    $pool_int++;

    $append='';
    if (isset($pools[$pool_int])) {
        $append=', ';
    }

    echo generate_link($label, $link_array, array('pool'=>$pool)).$append;
}

print_optionbar_end();

if (!isset($vars['array'])) {
    $graphs = array(
        'raid_drp_status' => 'Array Status',
        'raid_drp_bbu' => 'Array BBU Status',
        'raid_drp_drives' => 'Array Drive Count',
    );
}

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    if (isset($vars['pool'])) {
        $graph_array['pool']=$vars['pool'];
    }


    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
