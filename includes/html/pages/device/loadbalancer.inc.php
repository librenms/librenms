<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'loadbalancer',
];

$type_text['loadbalancer_rservers'] = 'Rservers';       // Cisco ACE
$type_text['loadbalancer_vservers'] = 'Serverfarms';    // Cisco ACE
$type_text['netscaler_vsvr'] = 'VServers';              // Citrix Netscaler
$type_text['ltm_vs'] = 'LTM Virtual Servers';           // F5 BigIP
$type_text['ltm_pool'] = 'LTM Pools';                   // F5 BigIP
$type_text['ltm_bwc'] = 'LTM Bandwidth Controller';     // F5 BigIP
$type_text['gtm_wide'] = 'GTM Wide IPs';                // F5 BigIP
$type_text['gtm_pool'] = 'GTM Pools';                   // F5 BigIP

print_optionbar_start();

$pagetitle[] = 'Load Balancer';

echo "<span style='font-weight: bold;'>Load Balancer</span> &#187; ";

unset($sep);
foreach ($loadbalancer_tabs as $type) {
    if (! $vars['type']) {
        $vars['type'] = $type;
    }

    echo $sep;

    if ($vars['type'] == $type) {
        echo '<span class="pagemenu-selected">';
    }

    echo generate_link($type_text[$type] . ' (' . $device_loadbalancer_count[$type] . ')', $link_array, ['type' => $type]);
    if ($vars['type'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$type = basename($vars['type']);
if (is_file("includes/html/pages/device/loadbalancer/$type.inc.php")) {
    include "includes/html/pages/device/loadbalancer/$type.inc.php";
} else {
    foreach ($loadbalancer_tabs as $type) {
        if ($type != 'overview') {
            if (is_file('includes/html/pages/device/loadbalancer/overview/' . $type . '.inc.php')) {
                $g_i++;
                if (! is_integer($g_i / 2)) {
                    $row_colour = \LibreNMS\Config::get('list_colour.even');
                } else {
                    $row_colour = \LibreNMS\Config::get('list_colour.odd');
                }

                echo '<div style="background-color: ' . $row_colour . ';">';
                echo '<div style="padding:4px 0px 0px 8px;"><span class=graphhead>' . $type_text[$type] . '</span>';

                include 'includes/html/pages/device/loadbalancer/overview/' . $type . '.inc.php';

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
