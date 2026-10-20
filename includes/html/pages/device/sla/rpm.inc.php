<?php

// Juniper RPM metrics collected for every probe type in Junos::pollSlas()
$rpm_panels = [
    'Packet Loss' => 'device_sla_loss',
    'RTT Spread' => 'device_sla_spread',
    'Jitter' => 'device_sla_jitter_rtt',
];

foreach ($rpm_panels as $title => $graph_type) {
    echo '<div class="panel-heading"><h3 class="panel-title">' . $title . '</h3></div>';
    echo '<div class="panel-body">';
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['type'] = $graph_type;
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    echo '</div>';
}
