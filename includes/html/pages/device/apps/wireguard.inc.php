<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'wireguard',
];

print_optionbar_start();

echo generate_link('All Interfaces', $link_array);
echo ' | Interfaces: ';

$interface_client_map = $app->data['mappings'] ?? [];

// generate interface links
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

// build the interface/client -> graph map
foreach ($interface_client_map as $interface => $client_list) {
    if (
        ! isset($vars['interface']) ||
        (isset($vars['interface']) && $interface == $vars['interface'])
    ) {
        foreach ($client_list as $client) {
            $interface_client_map[$interface][$client] = [
                'wireguard_traffic' => $interface . ' ' . $client . ' Traffic',
                'wireguard_time' => $interface .
                    ' ' .
                    $client .
                    ' Minutes Since Last Handshake',
            ];
        }
    }
}

// generate graphs on a per-interface, per-client basis
foreach ($interface_client_map as $interface => $client_list) {
    foreach ($client_list as $client => $graphs) {
        foreach ($graphs as $gtype => $gtext) {
            $graph_type = $gtype;
            $graph_array['height'] = '100';
            $graph_array['width'] = '215';
            $graph_array['to'] = time();
            $graph_array['id'] = $app['app_id'];
            $graph_array['type'] = 'application_' . $gtype;
            $graph_array['interface'] = $interface;
            $graph_array['client'] = $client;

            echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">' . $gtext . '</h3>
            </div>
            <div class="panel-body">
            <div class="row">';
            include 'includes/html/print-graphrow.inc.php';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
}
