<?php

global $config;

$raid_arrays=get_raid_drp_arrays($device['device_id']);

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'zfs',
);

print_optionbar_start();

echo 'Arrays:';
$raid_int=0;
while (isset($raid_arrays[$raid_int])) {
    $name=$pools[$raid_int];
    $label=$name;

    if ($vars['array'] == $name) {
        $label='>>'.$pool.'<<';
    }

    $raid_int++;

    $append='';
    if (isset($raid_arrays[$raid_int])) {
        $append=', ';
    }

    echo generate_link($label, $link_array, array('array'=>$name)).$append;
}

print_optionbar_end();

if (isset($vars['array'])) {
    $graphs = array(
        'raid_drp_status' => 'Array Status',
        'raid_drp_bbu' => 'Array BBU Status',
        'raid_drp_drives' => 'Array Drive Count',
    );
} else {
    $graphs = array(
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
