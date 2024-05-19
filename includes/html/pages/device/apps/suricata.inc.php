<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'suricata',
];

$app_data = $app->data;

print_optionbar_start();

// set the default graph set page for v2
if (!isset($vars['suricata_graph_set'])) {
    $vars['suricata_graph_set']='general';
}

// print the link to the totals
$total_label = isset($vars['sinstance'])
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
if (isset($vars['suricata_graph_set'])) {
    echo generate_link($total_label, $link_array, ['suricata_graph_set' => $vars['suricata_graph_set']]);
} else {
    echo generate_link($total_label, $link_array);
}

// print links to instances
echo ' | Instances: ';
$suricata_instances = $app->data['instances'] ?? [];
sort($suricata_instances);
foreach ($suricata_instances as $index => $sinstance) {
    $label = $vars['sinstance'] == $sinstance
        ? '<span class="pagemenu-selected">' . $sinstance . '</span>'
        : $sinstance;

    echo generate_link($label, $link_array, ['sinstance' => $sinstance, 'suricata_graph_set' => $vars['suricata_graph_set']]);

    if ($index < (count($suricata_instances) - 1)) {
        echo ', ';
    }
}

// print page information
// only present for v2
if ($app_data['version'] == 2) {
    print "<br>\nPages: ";
    $suricata_pages = ['general'=>'General', 'bypassed' => 'By Passed', 'errors' => 'Errors', 'memuse' => 'Memory Usage',
                     'detect' => 'Detect', 'filestore' => 'File Store', 'tcp' => 'TCP'];
    $page_count=0;
    foreach ($suricata_pages as $page => $page_description) {
        $label = $vars['suricata_graph_set'] == $page
            ? '<span class="pagemenu-selected">' . $page_description . '</span>'
            : $page_description;

        if (isset($vars['sinstance'])) {
            echo generate_link($label, $link_array, ['sinstance' => $vars['sinstance'], 'suricata_graph_set' => $page]);
        } else {
            echo generate_link($label, $link_array, ['suricata_graph_set' => $page]);
        }

        if ($page_count < (count($suricata_pages) - 1)) {
            echo ', ';
        }
        $page_count++;
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
} elseif ($app_data['version'] == 2) {
    if (!isset($vars['suricata_graph_set'])) {
        $vars['suricata_graph_set'] = 'general';
    }

    if (strcmp($vars['suricata_graph_set'], 'general') == 0) {
        if (!isset($vars['sinstance'])) {
            // v2 computes drop_percent for total only currently
            $graphs = [
                // capture__kernel_packets
                // decoder__ethernet
                // capture__kernel_drops
                //capture__kernel_ifdrops
                'suricata_v2_packets' => 'Packets',
                // flow__icmpv4
                // flow__icmpv6
                // flow__tcp
                // flow__udp
                'suricata_v2_flow_proto' => 'Flow Protocols',
                // decoder not events, decoder__bytes, decoder__avg_pkt_size
                // decoder__bytes
                // flow_bypassed__bytes
                'suricata_v2_bytes' => 'Bytes',
                // decoder_* protocol items
                'suricata_v2_dec_proto' => 'Decoder Protocol Packets',
                // decoder__avg_pkt_size
                'suricata_v2_avg_pkg_size' => 'Decoder Average Packet Size',
                // drop_percent
                'suricata_v2_drop_percent' => 'Drop Percentage',
                // capture__kernel_ifdrops, capture__kernel_drops
                'suricata_v2_pkt_drop' => 'Packet Drop',
                // error_delta
                'suricata_v2_error_delta' => 'Error Delta',
                // app_layer__flow__*
                'suricata_v2_app_layer__flows' => 'Application Layer Flows',
                // app_layer__tx__*
                'suricata_v2_app_layer__tx' => 'Application Layer Packets',
                // flow__end__*
                'suricata_v2_flow__end' => 'Flow Ends',
                // flow__emerg_mode_entered, flow__emerg_mode_over
                'suricata_v2_flow_emerg_mode' => 'Flow Emergency Mode',
                // uptime
                'suricata_v2_uptime' => 'Uptime',
            ];
        } else {
            $graphs = [
                // capture__kernel_packets
                'suricata_v2_packets' => 'Packets',
                // decoder not events, decoder__bytes, decoder__avg_pkt_size
                // decoder__avg_pkt_size
                // decoder__bytes
                // flow_bypassed__bytes
                'suricata_v2_bytes' => 'Bytes',
                // decoder_* protocol items
                'suricata_v2_dec_proto' => 'Decoder Protocol Packets',
                // decoder__avg_pkt_size
                'suricata_v2_avg_pkg_size' => 'Decoder Average Packet Size',
                // capture__kernel_ifdrops, capture__kernel_drops
                'suricata_v2_pkt_drop' => 'Packet Drop',
                // app_layer__flow__*
                'suricata_v2_app_layer__flows' => 'Application Layer Flows',
                // app_layer__tx__*
                'suricata_v2_app_layer__tx' => 'Application Layer Packets',
                // flow__end__*
                'suricata_v2_flow__end' => 'Flow Ends',
                // flow__emerg_mode_entered, flow__emerg_mode_over
                'suricata_v2_flow_emerg_mode' => 'Flow Emergency Mode',
                // uptime
                'suricata_v2_uptime' => 'Uptime',
            ];
        }
    } elseif (strcmp($vars['suricata_graph_set'], 'bypassed') == 0) {
        $graphs = [
            // flow_bypassed__closed
            'suricata_v2_flow_bypassed__closed' => 'Flow Bypassed Closed',
            // flow_bypassed__bytes
            'suricata_v2_flow_bypassed__bytes' => 'Flow Bypassed Bytes',
            // flow_bypassed__local_bytes
            'suricata_v2_flow_bypassed__local_bytes' => 'Flow Bypassed Local Bytes',
            // flow_bypassed__local_capture_pkts
            'suricata_v2_flow_bypassed__local_capture_pkts' => 'Flow Bypassed Local Capture Packets',
            // flow_bypassed__pkts
            'suricata_v2_flow_bypassed__pkts' => 'Flow Bypassed Packets',
            // flow_bypassed__local_pkts
            'suricata_v2_flow_bypassed__local_pkts' => 'Flow Bypassed Local Packets',
            // flow__end__state__local_bypassed
            'suricata_v2_flow__end__state__local_bypassed' => 'Flow End State Local Bypassed',
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'errors') == 0) {
        $graphs = [
            // drop_percent
            'suricata_v2_drop_percent' => 'Drop Percent',
            // capture__kernel_ifdrops, capture__kernel_drops
            'suricata_v2_pkt_drop' => 'Packet Drop',
            // error_delta
            'suricata_v2_error_delta' => 'Error Delta',
            // app_layer__error__*__alloc
            'suricata_v2_app_layer_error_alloc' => 'App Layer Error Alloc',
            // app_layer__error__*__gap
            'suricata_v2_app_layer_error_gap' => 'App Layer Error Gap',
            // app_layer__error__*__internal
            'suricata_v2_app_layer_error_internal' => 'App Layer Error Internal',
            // app_layer__error__*__parser
            'suricata_v2_app_layer_error_parser' => 'App Layer Error Parser',
            // file_store__fs_errors
            'suricata_v2_file_store__fs_errors' => 'File Store FS Errors',
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'memuse') == 0) {
        $graphs = [
            // flow__memuse
            // ftp__memuse
            // http__memuse
            // tcp__memuse
            // tcp__reassembly_memuse
            'suricata_v2_memuse' => 'Memory Usage',
            // flow__memcap
            // flow__memuse
            'suricata_v2_memuse_flow' => 'Flow Memory Usage',
            // ftp__memcap
            // ftp__memuse
            'suricata_v2_memuse_ftp' => 'FTP Memory Usage',
            // http__memcap
            // http__memuse
            'suricata_v2_memuse_http' => 'HTTP Memory Usage',
            // tcp__memuse
            // tcp__reassembly_memuse
            'suricata_v2_memuse_tcp' => 'TCP Memory Usage',
            // tcp__segment_memcap_drop
            // tcp__ssn_memcap_drop
            'suricata_v2_memuse_tcp_drop' => 'TCP Memcap Drop',
            // memcap_pressure
            // memcap_pressure_max
            'suricata_v2_memcap_pressure' => 'Memcap Pressure',
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'detect') == 0) {
        $graphs = [
            // detect__alert
            // detect__alerts_suppressed
            'suricata_v2_detect_alert_suppressed' => 'Detect Alert + Suppressed',
            // detect__alert
            'suricata_v2_detect_alert' => 'Detect Alert',
            // detect__alert_queue_overflow
            'suricata_v2_detect__alert_queue_overflow' => 'Detect Alert Queue Overflow',
            // detect__alerts_suppressed
            'suricata_v2_detect__alerts_suppressed' => 'Detect Alerts Suppressed',
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'filestore') == 0) {
        $graphs = [
            // file_store__fs_errors
            'suricata_v2_file_store__fs_errors' => 'File Store FS Errors',
            // file_store__open_files
            'suricata_v2_file_store__open_files' => 'File Store Open Files',
            // file_store__open_files_max_hit
            'suricata_v2_file_store__open_files_max_hit' => 'File Store Open Files, Max Hit',
        ];
    } elseif (strcmp($vars['suricata_graph_set'], 'tcp') == 0) {
        $graphs = [
            // tcp__ack_unseen_data
            'suricata_v2_tcp__ack_unseen_data' => 'TCP Ack Unseen Data',
            // tcp__active_sessions
            'suricata_v2_tcp__active_sessions' => 'TCP Active Sessions',
            // tcp__insert_data_normal_fail
            'suricata_v2_tcp__insert_data_normal_fail' => 'TCP Insert Data Normal Fail',
            // tcp__insert_data_overlap_fail
            'suricata_v2_tcp__insert_data_overlap_fail' => 'TCP Insert Data Overlap Fail',
            // tcp__invalid_checksum
            'suricata_v2_tcp__invalid_checksum' => 'TCP Invalid Checksum',
            // tcp__memuse
            'suricata_v2_tcp__memuse' => 'TCP Memuse',
            // tcp__midstream_pickups
            'suricata_v2_tcp__midstream_pickups' => 'TCP Midstream Pickups',
            // tcp__overlap
            'suricata_v2_tcp__overlap' => 'TCP Overlap',
            // tcp__overlap_diff_data
            'suricata_v2_tcp__overlap_diff_data' => 'TCP Overlap Diff Data',
            // tcp__pkt_on_wrong_thread
            'suricata_v2_tcp__pkt_on_wrong_thread' => 'TCP Pkt on Wrong Thread',
            // tcp__pseudo
            'suricata_v2_tcp__pseudo' => 'TCP Pseudo',
            // tcp__pseudo_failed
            'suricata_v2_tcp__pseudo_failed' => 'TCP Pseudo Failed',
            // tcp__reassembly_gap
            'suricata_v2_tcp__reassembly_gap' => 'TCP Reassembly Gap',
            // tcp__reassembly_memuse
            'suricata_v2_tcp__reassembly_memuse' => 'TCP Reassembly Memuse',
            // tcp__rst
            'suricata_v2_tcp__rst' => 'TCP Rst',
            // tcp__segment_from_cache
            'suricata_v2_tcp__segment_from_cache' => 'TCP Segment From Cache',
            // tcp__segment_from_pool
            'suricata_v2_tcp__segment_from_pool' => 'TCP Segment From Pool',
            // tcp__segment_memcap_drop
            'suricata_v2_tcp__segment_memcap_drop' => 'TCP Segment Memcap Drop',
            // tcp__sessions
            'suricata_v2_tcp__sessions' => 'TCP Sessions',
            // tcp__ssn_from_cache
            'suricata_v2_tcp__ssn_from_cache' => 'TCP SSN From Cache',
            // tcp__ssn_from_pool
            'suricata_v2_tcp__ssn_from_pool' => 'TCP SSN From Pool',
            // tcp__ssn_memcap_drop
            'suricata_v2_tcp__ssn_memcap_drop' => 'TCP SSN Memcap Drop',
            // tcp__stream_depth_reached
            'suricata_v2_tcp__stream_depth_reached' => 'TCP Stream Depth Reached',
            // tcp__syn
            'suricata_v2_tcp__syn' => 'TCP Syn',
            // tcp__synack
            'suricata_v2_tcp__synack' => 'TCP Syn ACK',
            // flow__end__tcp__*
            'suricata_v2_flow__end__end' => 'TCP Flow Ends',
        ];
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
