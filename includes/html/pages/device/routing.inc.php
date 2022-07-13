<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'routing',
];

// $type_text['overview'] = "Overview";
$type_text['ipsec_tunnels'] = 'IPSEC Tunnels';

// Cisco ACE
$type_text['loadbalancer_rservers'] = 'Rservers';
$type_text['loadbalancer_vservers'] = 'Serverfarms';

// Citrix Netscaler
$type_text['netscaler_vsvr'] = 'VServers';

$type_text['bgp'] = 'BGP';
$type_text['cef'] = 'CEF';
$type_text['ospf'] = 'OSPF';
$type_text['isis'] = 'ISIS';
$type_text['vrf'] = 'VRFs';
$type_text['routes'] = 'Routing Table';
$type_text['cisco-otv'] = 'OTV';
$type_text['mpls'] = 'MPLS';

print_optionbar_start();

$pagetitle[] = 'Routing';

echo "<span style='font-weight: bold;'>Routing</span> &#187; ";

unset($sep);
foreach ($routing_tabs as $type => $type_count) {
    if (! $vars['proto']) {
        $vars['proto'] = $type;
    }

    echo $sep;

    if ($vars['proto'] == $type) {
        echo '<span class="pagemenu-selected">';
    }

    echo generate_link($type_text[$type] . ' (' . $type_count . ')', $link_array, ['proto' => $type]);
    if ($vars['proto'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$protocol = basename($vars['proto']);
if (is_file("includes/html/pages/device/routing/$protocol.inc.php")) {
    include "includes/html/pages/device/routing/$protocol.inc.php";
} else {
    foreach ($routing_tabs as $type => $type_count) {
        if ($type != 'overview') {
            if (is_file("includes/html/pages/device/routing/overview/$type.inc.php")) {
                $g_i++;
                if (! is_integer($g_i / 2)) {
                    $row_colour = \LibreNMS\Config::get('list_colour.even');
                } else {
                    $row_colour = \LibreNMS\Config::get('list_colour.odd');
                }

                echo '<div style="background-color: ' . $row_colour . ';">';
                echo '<div style="padding:4px 0px 0px 8px;"><span class=graphhead>' . $type_text[$type] . '</span>';

                include "includes/html/pages/device/routing/overview/$type.inc.php";

                echo '</div>';
                echo '</div>';
            } else {
                $graph_title = $type_text[$type];
                $graph_type = 'device_' . $type;

                include 'includes/html/print-device-graph.php';
            }//end if
        }//end if
    }//end foreach
}//end if
