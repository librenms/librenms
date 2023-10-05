<?php

use App\Models\Port;

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'dhcp-stats',
];

// app data is only going to exist for this for extend 3+, so don't both displaying it otherwise
if (isset($app->data['pools'])) {
    print_optionbar_start();
    echo generate_link('General', $link_array);
    echo ' | ' . generate_link('Pools', $link_array, ['app_page'=>'pools']);
    echo ' | ' . generate_link('Leases', $link_array, ['app_page'=>'leases']);
    print_optionbar_end();
}

if (! isset($vars['app_page']) || ! isset($app->data['pools'])) {
    $graphs = [
        'dhcp-stats_stats' => 'Stats',
        'dhcp-stats_pools_percent' => 'Pools Percent',
        'dhcp-stats_pools_current' => 'Pools Current',
        'dhcp-stats_pools_max'     => 'Pools Max',
        'dhcp-stats_networks_percent' => 'Networks Percent',
        'dhcp-stats_networks_current' => 'Networks Current',
        'dhcp-stats_networks_max'     => 'Networks Max',
    ];
} elseif (isset($vars['app_page']) && $vars['app_page'] == 'pools') {
    $pools = $app->data['pools'] ?? [];
    print_optionbar_start();
    echo '<center><b>Pools</b></center>';
    $pool_table = [
        'headers' => [
            'CIDR',
            'First IP',
            'Last IP',
            'Max',
            'In Use',
            'Use%',
        ],
        'rows' => [],
    ];
    foreach ($pools as $key => $pool) {
        $pool_table['rows'][$key] = [
            $pool['cidr'],
            $pool['first_ip'],
            $pool['last_ip'],
            $pool['max'],
            $pool['cur'],
            $pool['percent'],
        ];
    }
    echo render_table($pool_table);
    print_optionbar_end();

    print_optionbar_start();
    echo '<center><b>Subnets Details</b></center>';
    print_optionbar_start();
    $pool_detail_table = [
        'headers'=>[
            'Key',
            'Value',
        ],
    ];
    foreach ($pools as $pool_key => $pool) {
        // re-init the rows the pools detail table
        unset($pool_detail_table['rows']);
        $pool_detail_table['rows'] = [];
        // display it this way as a CIDR may have more than one pool defined for it
        // especially true if both IPv4 and IPv6 are in use
        echo '<center><b>' . $pool['cidr'] . ', ' . $pool['first_ip'] . '-' . $pool['last_ip'] . '</b></center>';
        $option_row_int = 0;
        // remove these as they are stats and no options related info for the subnet
        unset($pool['cur']);
        unset($pool['max']);
        unset($pool['percent']);
        foreach ($pool as $pool_option => $option_value) {
            $pool_detail_table['rows'][$option_row_int] = [
                $pool_option,
                $option_value,
            ];
            $option_row_int++;
        }
        echo render_table($pool_detail_table);
    }
    print_optionbar_end();
    print_optionbar_end();

    $subnets = $app->data['networks'] ?? [];
    print_optionbar_start();
    echo '<center><b>Networks</b></center>';
    $subnets_table = [
        'headers' => [
            'Name',
            'Max',
            'In Use',
            'Use%',
            'Pools',
        ],
        'rows' => [],
    ];
    foreach ($subnets as $key => $subnet) {
        $subnets_table['rows'][$key] = [
            $subnet['network'],
            $subnet['max'],
            $subnet['cur'],
            $subnet['percent'],
            json_encode($subnet['pools']),
        ];
    }
    echo render_table($subnets_table);
    print_optionbar_end();
} elseif (isset($vars['app_page']) && $vars['app_page'] == 'leases') {
    $leases = $app->data['found_leases'] ?? [];
    $table_info = [
        'headers' => [
            'IP',
            'State',
            'HW Address',
            'Starts',
            'Ends',
            'Client Hostname',
            'Vendor',
        ],
        'rows' => [],
    ];
    foreach ($leases as $key => $lease) {
        // look and see if we know what that mac belongs to and if so create a link for the device and port
        $mac = $lease['hw_address'];
        $port = Port::with('device')->firstWhere(['ifPhysAddress' => str_replace(':', '', $mac)]);
        if (isset($port)) {
            $mac = $mac . ' (' .
                generate_device_link(['device_id'=>$port->device_id]) . ', ' .
                generate_port_link([
                    'label' => $port->label,
                    'port_id' => $port->port_id,
                    'ifName' => $port->ifName,
                    'device_id' => $port->device_id,
                ]) . ')';
        }

        if ($lease['client_hostname'] != '') {
            $lease['client_hostname'] = base64_decode($lease['client_hostname']);
        }
        if ($lease['vendor_class_identifier'] != '') {
            $lease['vendor_class_identifier'] = base64_decode($lease['vendor_class_identifier']);
        }
        $table_info['rows'][$key] = [
            $lease['ip'],
            $lease['state'],
            $mac,
            // display the time as UTC as that keeps things most simple
            date('Y-m-d\TH:i:s\Z', $lease['starts']),
            date('Y-m-d\TH:i:s\Z', $lease['ends']),
            $lease['client_hostname'],
            $lease['vendor_class_identifier'],
        ];
    }
    echo render_table($table_info);
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

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
