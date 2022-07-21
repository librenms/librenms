<?php

$suricata_instances =  json_decode($app['data'], true)['instances'];

sort($suricata_instances);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'suricata',
];

print_optionbar_start();

echo generate_link('Totals', $link_array);
echo '| Instances:';
$int_int = 0;
while (isset($suricata_instances[$int_int])) {
    $instance = $suricata_instances[$int_int];
    $label = $instance;

    if ($vars['instance'] == $instance) {
        $label = '<span class="pagemenu-selected">' . $instance . '</span>';
    }

    $int_int++;

    $append = '';
    if (isset($suricata_instances[$int_int])) {
        $append = ', ';
    }

    echo generate_link($label, $link_array, ['pool'=>$instance]) . $append;
}

print_optionbar_end();

$graphs = [
    'suricata_packets'=>'Packets',
    'suricata_bytes'=>'Bytes',
    'suricata_nasty_delta'=>'Drops or Errors Delta',
    'suricata_nasty_percent'=>'Drops or Errors Percent',
    'suricata_dec_proto'=>'Decoder Protocols',
    'suricata_flow_proto'=>'Flow Protocols',
    'suricata_app_flows'=>'App Layer Flows',
    'suricata_app_tx'=>'App Layer TX',
    'suricata_mem_use'=>'Memory Usage',
    'suricata_uptime'=>'Uptime',
    'suricata_alert'=>'Alert Status',
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
