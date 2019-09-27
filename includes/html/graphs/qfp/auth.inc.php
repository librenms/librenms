<?php

use LibreNMS\Component;

$component = new Component();
// Include ID filter so we get only one component
$components = $component->getComponents(null, array('type' => 'cisco-qfp', 'id' => $vars['id']));
// Get first key of the components array that is device ID
$device_id = key($components);

if ($components && isset($components[$device_id][$vars['id']]) && ($auth || device_permitted($device_id))) {
    $components = $components[$device_id][$vars['id']];
    $device = device_by_id_cache($device_id);
    switch ($subtype) {
        case 'memory':
            $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'memory', $components['entPhysicalIndex']));
            break;
        case 'packets':
        case 'throughput':
        case 'util':
        case 'avgpktsize':
        default:
            $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'util', $components['entPhysicalIndex']));
            break;
    }

    $link_array = array(
        'page'   => 'device',
        'device' => $device['device_id'],
        'tab'    => 'health',
    );

    $title = generate_device_link($device);
    $title .= ' :: ' . generate_link("QFP", $link_array, array('metric' => 'qfp'));
    $title .= ' :: ' . $components['name'];
    $auth = true;
}
