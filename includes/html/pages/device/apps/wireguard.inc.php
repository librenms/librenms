<?php

/**
 * Builds a graph array and outputs the graph.
 *
 * @param  string  $gtype
 * @param  string  $app_id
 * @param  null|string  $interface
 * @param  null|string  $client
 * @param  string  $gtext
 */
function wireguard_graph_printer($gtype, $app_id, $interface, $client, $gtext)
{
    $graph_type = $gtype;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app_id;
    $graph_array['type'] = 'application_' . $gtype;
    if (! is_null($interface)) {
        $graph_array['interface'] = $interface;
    }
    if (! is_null($client)) {
        $graph_array['client'] = $client;
    }
    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' .
        $gtext .
        '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'wireguard',
];

$interface_client_map = $app->data['mappings'] ?? [];
$graph_map = [
    'interface' => [
        'clients' => [],
        'total' => [],
    ],
    'total' => [],
];

print_optionbar_start();

echo generate_link('All Interfaces', $link_array);
echo ' | Interfaces: ';

// generate interface links on the host application page
$i = 0;
foreach ($interface_client_map as $interface => $client_list) {
    $label =
        $vars['interface'] == $interface
            ? '<span class="pagemenu-selected">' . $interface . '</span>'
            : $interface;

    echo generate_link($label, $link_array, ['interface' => $interface]);

    if ($i < count(array_keys($interface_client_map)) - 1) {
        echo ', ';
    }
    $i++;
}

print_optionbar_end();

// generates the global wireguard graph mapping
if (! isset($vars['interface'])) {
    $graph_map['total'] = [
        'wireguard_traffic' => 'Wireguard Total Traffic',
    ];
}

foreach ($interface_client_map as $interface => $client_list) {
    if (
        ! isset($vars['interface']) ||
        (isset($vars['interface']) && $interface == $vars['interface'])
    ) {
        // generates the interface graph mapping
        $graph_map['interface']['total'][$interface] = [
            'wireguard_traffic' => $interface . ' ' . 'Total Traffic',
        ];
        foreach ($client_list as $client) {
            // generates the interface+client graph mapping
            $graph_map['interface']['clients'][$interface][$client] = [
                'wireguard_traffic' => $interface . ' ' . $client . ' Traffic',
                'wireguard_time' => $interface .
                    ' ' .
                    $client .
                    ' Minutes Since Last Handshake',
            ];
        }
    }
}

// print graphs
foreach ($graph_map as $category => $category_map) {
    foreach ($category_map as $subcategory => $subcategory_map) {
        if ($category === 'total') {
            // print graphs for global wireguard metrics
            wireguard_graph_printer(
                $subcategory,
                $app['app_id'],
                null,
                null,
                $subcategory_map
            );
        } elseif ($category === 'interface') {
            foreach ($subcategory_map as $interface => $interface_map) {
                foreach ($interface_map as $client => $client_map) {
                    if ($subcategory === 'total') {
                        // print graphs for wireguard interface metrics
                        wireguard_graph_printer(
                            $client,
                            $app['app_id'],
                            $interface,
                            null,
                            $client_map
                        );
                    } elseif ($subcategory === 'clients') {
                        foreach ($client_map as $gtype => $gtext) {
                            // print graphs for wireguard interface+client metrics
                            wireguard_graph_printer(
                                $gtype,
                                $app['app_id'],
                                $interface,
                                $client,
                                $gtext
                            );
                        }
                    }
                }
            }
        }
    }
}
