<?php

global $config;

$raid_arrays=get_raid_drp_arrays($device['device_id']);

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'raid-drp',
);

print_optionbar_start();

echo generate_link('Overview', $link_array);
echo ' | Arrays:';

$raid_int=0;
while (isset($raid_arrays[$raid_int])) {
    $name=$pools[$raid_int];
    $label=$name;

    if ($vars['array'] == $name) {
        $label='>>'.$name.'<<';
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
        'raid-drp_status' => 'Array Status',
        'raid-drp_bbu' => 'Array BBU Status',
        'raid-drp_drives' => 'Array Drive Count',
    );
} else {
    $graphs = array(
        'raid-drp_array_stats' => 'Arrays',
        'raid-drp_bbu_stats' => 'BBUs',
        'raid-drp_drive_stats' => 'Drives',
    );
}

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    if (isset($vars['array'])) {
        $graph_array['array']=$vars['array'];
    }


    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    if (strcmp($key, 'raid-drp_status') == 0) {
        echo '3=Good, 2=Rebuilding, 1=Bad, 0=Unknown';
    } elseif (strcmp($key, 'raid-drp_bbu') == 0) {
        echo '5=Good, 4=charging, 3=failed, 2=notPrsent, 1=N/A, 0=Unknown';
    }
    echo '</div>';
    echo '</div>';
}
