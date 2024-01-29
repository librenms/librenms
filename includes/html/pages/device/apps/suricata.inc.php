<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'suricata',
];

$app_data = $app->data;

print_optionbar_start();

echo generate_link('Totals', $link_array);
echo '| Instances:';
$suricata_instances = $app->data['instances'] ?? [];
sort($suricata_instances);
foreach ($suricata_instances as $index => $sinstance) {
    $label = $vars['sinstance'] == $sinstance
        ? '<span class="pagemenu-selected">' . $sinstance . '</span>'
        : $sinstance;

    echo generate_link($label, $link_array, ['sinstance' => $sinstance]);

    if ($index < (count($suricata_instances) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

if ($app_data['version'] == 1) {
    $graphs = [
        'suricata_packets' => 'Packets',
        'suricata_bytes' => 'Bytes',
        'suricata_nasty_delta' => 'Drops or Errors Delta',
        'suricata_nasty_percent' => 'Drops or Errors Percent',
        'suricata_dec_proto' => 'Decoder Protocols',
        'suricata_flow_proto' => 'Flow Protocols',
        'suricata_app_flows' => 'App Layer Flows',
        'suricata_app_tx' => 'App Layer TX',
        'suricata_mem_use' => 'Memory Usage',
        'suricata_uptime' => 'Uptime',
        'suricata_alert' => 'Alert Status',
    ];
} elseif ($app_data['version'] == 1) {
    if (strcmp($vars['suricata_graph_set'], 'general') == 0) {
        $graphs = [
            // capture__kernel_packets
            'suricata_v2_packets' => 'Packets',
            // decoder not events, decoder__bytes, decoder__avg_pkt_size
            // decoder__avg_pkt_size
            // decoder__bytes
            // flow_bypassed__bytes
            'suricata_v2_bytes' => 'Bytes',
            // decoder__avg_pkt_size
            'suricata_v2_avg_pkg_size' => 'Decoder Average Packet Size',
            // drop_percent
            // capture__kernel_ifdrops, capture__kernel_drops
            // error_delta
            // app_layer__flow__*
            // app_layer__tx__*
            // flow__emerg_mode_entered, flow__emerg_mode_over
            // uptime
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'bypassed') == 0) {
        // flow_bypassed__bytes
        // flow_bypassed__closed
        // flow_bypassed__local_bytes
        // flow_bypassed__local_capture_pkts
        // flow_bypassed__local_pkts
        // flow_bypassed__pkts
        // flow__end__state__local_bypassed
        $graphs = [

        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'errors') == 0) {
        // drop_percent
        // capture__kernel_ifdrops, capture__kernel_drops
        // error_delta
        // app_layer__error__*__alloc
        // app_layer__error__*__gap
        // app_layer__error__*__internal
        // app_layer__error__*__parser
        // file_store__fs_errors
        $graphs = [

        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'memuse') == 0) {
        // memcap
        // memuse
        // memcap_pressure
        // memcap_pressure_max
        // tcp__memuse
        // tcp__reassembly_memuse
        // tcp__segment_memcap_drop
        // tcp__ssn_memcap_drop
        $graphs = [

        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'detect') == 0) {
        // detect__alert
        // detect__alert_queue_overflow
        // detect__alerts_suppressed
        $graphs = [

        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'filestore') == 0) {
        // file_store__fs_errors
        // file_store__open_files
        // file_store__open_files_max_hit
        $graphs = [

        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'tcp') == 0) {
        // tcp__ack_unseen_data": 2276,
        // tcp__active_sessions
        // tcp__insert_data_normal_fail
        // tcp__insert_data_overlap_fail
        // tcp__invalid_checksum
        // tcp__memuse
        // tcp__midstream_pickups
        // tcp__overlap
        // tcp__overlap_diff_data
        // tcp__pkt_on_wrong_thread
        // tcp__pseudo
        // tcp__pseudo_failed
        // tcp__reassembly_gap
        // tcp__reassembly_memuse
        // tcp__rst
        // tcp__segment_from_cache
        // tcp__segment_from_pool
        // tcp__segment_memcap_drop
        // tcp__sessions
        // tcp__ssn_from_cache
        // tcp__ssn_from_pool
        // tcp__ssn_memcap_drop
        // tcp__stream_depth_reached
        //tcp__syn
        // tcp__synack
    }
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['sinstance'])) {
        $graph_array['sinstance'] = $vars['sinstance'];
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
