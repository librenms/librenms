<?php

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'wireguard',
];

if (isset($vars['interface'])) {
    $vars['interface'] = htmlspecialchars($vars['interface']);
}
if (isset($vars['client'])) {
    $vars['client'] = htmlspecialchars($vars['client']);
}

$interface_client_map = $app->data['mappings'] ?? [];
$returned_data = $app->data['data'] ?? [];

// put interfaces in order
ksort($interface_client_map);

print_optionbar_start();

$label =
    (! isset($vars['wg_page']) && ! isset($vars['interface']))
            ? '<span class="pagemenu-selected">All Interfaces</span>'
            : 'All Interfaces';
echo generate_link($label, $link_array);
if (count($returned_data) > 0) {
    echo ' | ';
    $label =
        $vars['wg_page'] == 'details'
        ? '<span class="pagemenu-selected">Details</span>'
        : 'Details';
    echo generate_link($label, $link_array, ['wg_page' => 'details']);
}
echo ' | Interfaces: ';

// generate interface links on the host application page
$i = 0;
foreach ($interface_client_map as $interface => $client_list) {
    $interface = htmlspecialchars($interface);

    $label =
        ($vars['interface'] == $interface && ! isset($vars['wg_page']))
            ? '<span class="pagemenu-selected">' . $interface . '</span>'
            : $interface;

    echo generate_link($label, $link_array, ['interface' => $interface]);

    echo '(';

    $label =
        ($vars['interface'] == $interface && $vars['wg_page'] == 'peer_bw')
            ? '<span class="pagemenu-selected">' . 'BW' . '</span>'
            : 'BW';

    echo generate_link($label, $link_array, ['interface' => $interface, 'wg_page' => 'peer_bw']);

    echo ', ';

    $label =
        ($vars['interface'] == $interface && $vars['wg_page'] == 'peer_last')
            ? '<span class="pagemenu-selected">' . 'Last' . '</span>'
            : 'Last';
    echo generate_link($label, $link_array, ['interface' => $interface, 'wg_page' => 'peer_last']);

    echo ')';

    if ($i < count(array_keys($interface_client_map)) - 1) {
        echo ', ';
    }
    $i++;
}

// if we have a interface specified and it exists, print a list of peers
if (isset($vars['interface']) && isset($interface_client_map[$vars['interface']])) {
    // order the peer information for the interface
    asort($interface_client_map[$vars['interface']]);

    $i = 0;
    echo '<br>Peers: ';
    foreach ($interface_client_map[$vars['interface']] as $peer_key => $peer) {
        $peer = htmlspecialchars($peer);

        $label =
            $vars['client'] == $peer
            ? '<span class="pagemenu-selected">' . $peer . '</span>'
            : $peer;
        echo generate_link($label, $link_array, ['interface' => $interface, 'client' => $peer]);

        if ($i < count(array_keys($interface_client_map[$vars['interface']])) - 1) {
            echo ', ';
        }

        $i++;
    }
}

// if displaying peer information, display additional useful information
if (isset($vars['interface']) &&
    isset($interface_client_map[$vars['interface']]) &&
    isset($vars['client']) &&
    isset($returned_data[$vars['interface']][$vars['client']]) &&
    ! isset($vars['wg_page'])
) {
    $peer = $returned_data[$vars['interface']][$vars['client']];
    echo "\n<hr>Hostname: ";
    if (isset($peer['hostname'])) {
        $peer_dev = Device::firstWhere(['hostname' => $peer['hostname']]);
        if (isset($peer_dev)) {
            echo generate_device_link(['device_id' => $peer_dev->device_id], $name) . "<br>\n";
        } else {
            echo htmlspecialchars($peer['hostname']) . "<br>\n";
        }
    } else {
        echo "*unknown*<br>\n";
    }
    echo 'PubKey: ';
    if (is_null($peer['pubkey'])) {
        echo "*hidden*<br>\n";
    } else {
        echo htmlspecialchars($peer['pubkey']) . "<br>\n";
    }
    echo 'Interface: ';
    $port = Port::with('device')->firstWhere(['ifName' => $vars['interface'], 'device_id' => $device['device_id']]);
    if (isset($port)) {
        echo generate_port_link([
            'label' => $port->label,
            'port_id' => $port->port_id,
            'ifName' => $port->ifName,
            'device_id' => $port->device_id,
        ]) . "<br>\n";
    } else {
        echo htmlspecialchars($vars['interface']) . "<br>\n";
    }
    echo 'Endpoint Host: ';
    if (preg_match('/^[\:A-Fa-f0-9]+$/', $peer['endpoint_host'])) {
        $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $peer['endpoint_host']]);
    } elseif (preg_match('/^[\.0-9]+$/', $peer['endpoint_host'])) {
        $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $peer['endpoint_host']]);
    }
    if (isset($ip_info)) {
        $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
        echo $peer['endpoint_host'] . '(' . generate_device_link(['device_id' => $port->device_id]) . ', ' .
            generate_port_link([
                'label' => $port->label,
                'port_id' => $port->port_id,
                'ifName' => $port->ifName,
                'device_id' => $port->device_id,
            ]) . ")<br>\n";
    } else {
        echo htmlspecialchars($peer['endpoint_host']) . "<br>\n";
    }
    echo 'Endpoint Port: ' . htmlspecialchars($peer['endpoint_port']) . "<br>\n";
    echo 'Minutes Since Last Handshake: ' . htmlspecialchars($peer['minutes_since_last_handshake']) . "<br>\n";
    echo 'Allowed IPs: ';
    $allowed_ips = '';
    if (isset($peer['allowed_ips']) && ! is_null($peer['allowed_ips']) && is_array($peer['allowed_ips'])) {
        foreach ($peer['allowed_ips'] as $allowed_ips_key => $allowed_ip) {
            $ip_found = false;
            if (preg_match('/^[\:A-Fa-f0-9]+$/', $allowed_ip)) {
                $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $allowed_ip]);
                if (isset($ip_info)) {
                    $ip_found = true;
                }
            } elseif (preg_match('/^[\.0-9]+$/', $allowed_ip)) {
                $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $allowed_ip]);
                if (isset($ip_info)) {
                    $ip_found = true;
                }
            }
            if ($ip_found) {
                $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
                $ip_info_string = generate_device_link(['device_id' => $port->device_id], $allowed_ip) . '(' .
                    generate_port_link([
                        'label' => $port->label,
                        'port_id' => $port->port_id,
                        'ifName' => $port->ifName,
                        'device_id' => $port->device_id,
                    ]) . ')';
                if ($allowed_ips == '') {
                    $allowed_ips = $ip_info_string;
                } else {
                    $allowed_ips = $allowed_ips . ', ' . $ip_info_string;
                }
            } else {
                if ($allowed_ips == '') {
                    $allowed_ips = htmlspecialchars($allowed_ip);
                } else {
                    $allowed_ips = $allowed_ips . ', ' . htmlspecialchars($allowed_ip);
                }
            }
        }
    }
    echo $allowed_ips . "<br>\n";
}

print_optionbar_end();

if (isset($vars['wg_page']) and $vars['wg_page'] == 'details') {
    $table_info = [
        'headers' => [
            'Name',
            'Interface',
            'PubKey',
            'Recv',
            'Sent',
            'Endpoint IP',
            'Port',
            'Last Handshake',
            'Allowed IPs',
        ],
        'rows' => [],
    ];
    ksort($returned_data);
    foreach ($returned_data as $returned_data_key => $interface) {
        ksort($interface);
        $port = Port::with('device')->firstWhere(['ifName' => $returned_data_key, 'device_id' => $device['device_id']]);
        if (isset($port)) {
            $interface_info_raw = true;
            $interface_info = generate_port_link([
                'label' => $port->label,
                'port_id' => $port->port_id,
                'ifName' => $port->ifName,
                'device_id' => $port->device_id,
            ]);
        } else {
            $interface_info_raw = false;
        }
        foreach ($interface as $interface_key => $peer) {
            $name = $interface_key;
            $name_raw = false;
            // see if the hostname resolves to a hostname of a device
            if (isset($peer['hostname']) && ! is_null($peer['hostname'])) {
                $peer_dev = Device::firstWhere(['hostname' => $peer['hostname']]);
                if (isset($peer_dev)) {
                    $name_raw = true;
                    $name = generate_device_link(['device_id' => $peer_dev->device_id], $name);
                }
            }
            // if this is null, it means the extend did not return it as that options is set to 0
            if (is_null($peer['pubkey'])) {
                $peer['pubkey'] = '*hidden*';
            }
            // ensure we have something set for endpoint host
            if (! isset($peer['endpoint_host']) || is_null($peer['endpoint_host'])) {
                $peer['endpoint_host'] = '';
            } else { // if we have data, see if we can resolve that to a machine for generating dev/if links
                $endpoint_raw = false;
                if (preg_match('/^[\:A-Fa-f0-9]+$/', $peer['endpoint_host'])) {
                    $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $peer['endpoint_host']]);
                } elseif (preg_match('/^[\.0-9]+$/', $peer['endpoint_host'])) {
                    $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $peer['endpoint_host']]);
                }
                if (isset($ip_info)) {
                    $endpoint_raw = true;
                    $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
                    $peer['endpoint_host'] = $peer['endpoint_host'] . '(' . generate_device_link(['device_id' => $port->device_id]) . ', ' .
                        generate_port_link([
                            'label' => $port->label,
                            'port_id' => $port->port_id,
                            'ifName' => $port->ifName,
                            'device_id' => $port->device_id,
                        ]) . ')';
                }
            }
            // ensure we have something set for the endpoint port
            if (! isset($peer['endpoint_port']) || is_null($peer['endpoint_port'])) {
                $peer['endpoint_port'] = '';
            }
            // build string of allowed IPs
            $allowed_ips = '';
            if (isset($peer['allowed_ips']) && ! is_null($peer['allowed_ips']) && is_array($peer['allowed_ips'])) {
                foreach ($peer['allowed_ips'] as $allowed_ips_key => $allowed_ip) {
                    $ip_found = false;
                    if (preg_match('/^[\:A-Fa-f0-9]+$/', $allowed_ip)) {
                        $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $allowed_ip]);
                        if (isset($ip_info)) {
                            $ip_found = true;
                        }
                    } elseif (preg_match('/^[\.0-9]+$/', $allowed_ip)) {
                        $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $allowed_ip]);
                        if (isset($ip_info)) {
                            $ip_found = true;
                        }
                    }
                    if ($ip_found) {
                        $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
                        $ip_info_string = generate_device_link(['device_id' => $port->device_id], $allowed_ip) . '(' .
                            generate_port_link([
                                'label' => $port->label,
                                'port_id' => $port->port_id,
                                'ifName' => $port->ifName,
                                'device_id' => $port->device_id,
                            ]) . ')';
                        if ($allowed_ips == '') {
                            $allowed_ips = $ip_info_string;
                        } else {
                            $allowed_ips = $allowed_ips . ', ' . $ip_info_string;
                        }
                    } else {
                        if ($allowed_ips == '') {
                            $allowed_ips = htmlspecialchars($allowed_ip);
                        } else {
                            $allowed_ips = $allowed_ips . ', ' . htmlspecialchars($allowed_ip);
                        }
                    }
                }
            }
            $peer['pubkey'] = generate_link(htmlspecialchars($peer['pubkey']), $link_array, ['interface' => $returned_data_key, 'client' => $interface_key]);
            $row = [
                ['data' => $name, 'raw' => $name_raw],
                ['data' => $interface_info, 'raw' => $interface_info_raw],
                ['data' => $peer['pubkey'], 'raw' => true],
                ['data' => $peer['bytes_rcvd']],
                ['data' => $peer['bytes_sent']],
                ['data' => $peer['endpoint_host'], 'raw' => $endpoint_raw],
                ['data' => $peer['endpoint_port']],
                ['data' => sprintf('%01.2f', $peer['minutes_since_last_handshake'])],
                ['data' => $allowed_ips, 'raw' => true],
            ];
            $table_info['rows'][] = $row;
        }
    }
    echo view('widgets/sortable_table', $table_info);
} elseif (! isset($vars['interface'])) {
    $graphs = [
        'total' => [
            'type' => 'wireguard_traffic',
            'description' => 'Total Wireguard Traffic',
        ],
    ];
} elseif (isset($vars['interface']) && ! isset($vars['client']) && $vars['wg_page'] == 'peer_bw') {
    $graphs = [
        'interface_total' => [
            'type' => 'wireguard_traffic',
            'description' => 'Total Wireguard Traffic, ' . $vars['interface'],
            'interface' => $vars['interface'],
        ],
    ];
    foreach ($interface_client_map[$vars['interface']] as $peer_key => $peer) {
        $graphs['peer_bw_' . $peer] = [
            'type' => 'wireguard_traffic',
            'description' => 'Peer Traffic, ' . $vars['interface'] . ' - ' . $peer,
            'interface' => $vars['interface'],
            'client' => $peer,
        ];
    }
} elseif (isset($vars['interface']) && ! isset($vars['client']) && $vars['wg_page'] == 'peer_last') {
    $graphs = [];
    foreach ($interface_client_map[$vars['interface']] as $peer_key => $peer) {
        $graphs['peer_last_' . $peer] = [
            'type' => 'wireguard_time',
            'description' => 'Peer Minutes Since Last Handshake , ' . $vars['interface'] . ' - ' . $peer,
            'interface' => $vars['interface'],
            'client' => $peer,
        ];
    }
} elseif (isset($vars['interface']) && ! isset($vars['client'])) {
    $graphs = [
        'interface_total' => [
            'type' => 'wireguard_traffic',
            'description' => 'Total Wireguard Traffic, ' . $vars['interface'],
            'interface' => $vars['interface'],
        ],
    ];
} elseif (isset($vars['interface']) && isset($vars['client'])) {
    $graphs = [
        'client_bw' => [
            'type' => 'wireguard_traffic',
            'description' => 'Peer Traffic, ' . $vars['interface'] . ' - ' . $vars['client'],
            'interface' => $vars['interface'],
            'client' => $vars['client'],
        ],
        'client_handshake' => [
            'type' => 'wireguard_time',
            'description' => 'Peer Minutes Since Last Handshake , ' . $vars['interface'] . ' - ' . $vars['client'],
            'interface' => $vars['interface'],
            'client' => $vars['client'],
        ],
    ];
}

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $graph_info['type'];
    if (! is_null($graph_info['interface'])) {
        $graph_array['interface'] = $graph_info['interface'];
    }
    if (! is_null($graph_info['client'])) {
        $graph_array['client'] = $graph_info['client'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_info['description'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
