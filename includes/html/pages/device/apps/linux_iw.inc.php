<?php

/**
 * Builds a graph array and outputs the graph.
 *
 * @param  string  $gtype
 * @param  string  $app_id
 * @param  null|string  $interface
 * @param  string  $gtext
 */
function linux_iw_graph_printer($gtype, $app_id, $interface, $cap, $gtext)
{
    $graph_type = $gtype;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app_id;
    $graph_array['type'] = 'application_' . $gtype;
    $graph_array['interface'] = $interface;
    if (! is_null($cap)) {
        $graph_array['cap'] = $cap;
    }

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

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'linux_iw',
];

print_optionbar_start();

echo generate_link('All Interfaces', $link_array);
echo ' | Interfaces: ';

// Data pulled and saved during polling.
$intf_to_mac_mappings = $app->data['intf_to_mac_mappings'] ?? [];
$mac_to_name_mappings = $app->data['mac_to_name_mappings'] ?? [];
$intf_to_ssid_mappings = $app->data['intf_to_ssid_mappings'] ?? [];
$intf_to_type_mappings = $app->data['intf_to_type_mappings'] ?? [];

// Generate interface links.
$i = 0;
foreach ($intf_to_mac_mappings as $interface => $cap_data) {
    $label =
        $vars['interface'] == $interface
            ? '<span class="pagemenu-selected">' . $interface . '</span>'
            : $interface;

    echo generate_link($label, $link_array, ['interface' => $interface]);

    if ($i < count(array_keys($intf_to_mac_mappings)) - 1) {
        echo ', ';
    }
    $i++;
}

print_optionbar_end();

$cap_graphs = [];
$interface_graphs = [];

// Build the interface+client/AP graph maps.
foreach ($intf_to_mac_mappings as $interface => $cap_data) {
    if (isset($vars['interface']) && $interface != $vars['interface']) {
        continue;
    }

    // Map an SSID name to the interface if it exists.
    $ssid = ' ';
    if (isset($intf_to_ssid_mappings[$interface])) {
        $ssid .= '(SSID: ' . $intf_to_ssid_mappings[$interface] . ') ';
    }

    // Change graph names between client and AP depending on polling.
    $cap_type = ' ';
    if (isset($intf_to_type_mappings[$interface])) {
        if ($intf_to_type_mappings[$interface] == 'AP') {
            $cap_type .= 'Client ';
        } elseif ($intf_to_type_mappings[$interface] == 'managed') {
            $cap_type .= 'AP ';
        }
    }

    // Build the interface graph maps.
    $interface_graphs[$interface] = [
        'linux_iw_channel_freq' => $interface . $ssid . 'Channel Frequencies',
        'linux_iw_channel_power' => $interface . $ssid . 'Channel Power Levels',
        'linux_iw_channel_time' => $interface . $ssid . 'Channel Timings',
    ];

    $cap_graphs[$interface] = [];
    foreach ($cap_data as $cap => $expiration_data) {
        // Map a friendly name to the client/AP if it exists.
        $friendly_name = ' ';
        if (isset($mac_to_name_mappings[$cap])) {
            $friendly_name .= '(' . $mac_to_name_mappings[$cap] . ') ';
        }

        // Build the client/AP graph maps.
        $cap_graphs[$interface][$cap] = [
            'linux_iw_cap_bitrate' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'Bitrates',
            'linux_iw_cap_byte' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'Bytes',
            'linux_iw_cap_misc' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'Misc Intervals',
            'linux_iw_cap_packet_other' => $interface . $ssid . $cap . $friendly_name_type . $cap_type . 'Other Packets',
            'linux_iw_cap_packet_rxtx' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'RX/TX Packets',
            'linux_iw_cap_power' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'Power Levels',
            'linux_iw_cap_time' => $interface . $ssid . $cap . $friendly_name . $cap_type . 'Timings',
        ];
    }
}

// Generate graphs on a per-interface, per-client/AP basis.
foreach ($interface_graphs as $interface => $intf_graph_list) {
    // First, generate interface/channel graphs.
    foreach ($intf_graph_list as $gtype => $gtext) {
        linux_iw_graph_printer($gtype, $app['app_id'], $interface, null, $gtext);
    }
    // Second, generate client/AP graphs.
    foreach ($cap_graphs[$interface] as $cap => $cap_graph_list) {
        foreach ($cap_graph_list as $gtype => $gtext) {
            linux_iw_graph_printer($gtype, $app['app_id'], $interface, $cap, $gtext);
        }
    }
}
