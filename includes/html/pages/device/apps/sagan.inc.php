<?php

$sagan_instances = get_sagan_instances($device['device_id']);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'sagan',
];

print_optionbar_start();

echo generate_link('Totals', $link_array);
echo '| Instances:';
$int_int = 0;
while (isset($sagan_instances[$int_int])) {
    $instance = $sagan_instances[$int_int];
    $label = $instance;

    if ($vars['instance'] == $instance) {
        $label = '<span class="pagemenu-selected">' . $instance . '</span>';
    }

    $int_int++;

    $append = '';
    if (isset($sagan_instances[$int_int])) {
        $append = ', ';
    }

    echo generate_link($label, $link_array, ['pool'=>$instance]) . $append;
}

print_optionbar_end();

$graphs = [
    'sagan_bytes'=>'Bytes',
    'sagan_eps'=>'Events Per Second',
    'sagan_total'=>'Recieved Log Entries',
    'sagan_drop'=>'Drop',
    'sagan_drop_percent'=>'Drop Percent',
    'sagan_f_total'=>'Flows Total',
    'sagan_f_dropped'=>'Flows Dropped',
    'sagan_f_drop_percent'=>'Flows Dropped Percent',
    'sagan_ignore'=>'Ignore',
    'sagan_bytes_ignored'=>'Bytes Ignored',
    'sagan_match'=>'Match',
    'sagan_max_bytes_log_line'=>'Max Bytes Log Line',
    'sagan_threshold'=>'Threshold',
    'sagan_uptime'=>'Uptime',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['pool'])) {
        $graph_array['pool'] = $vars['pool'];
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
