<?php

$component = new \LibreNMS\Component();
// Include ID filter so we get only one component
$components = $component->getComponents(null, array('type' => 'cisco-qfp', 'id' => $vars['id']));
// Get first key of the components array that is device ID
$device_id = key($components);

if ($components && isset($components[$device_id][$vars['id']]) && ($auth || device_permitted($device_id))) {
    $components = $components[$device_id][$vars['id']];
    $device = device_by_id_cache($device_id);
    switch ($subtype) {
        case 'util':
            $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'util', $components['entPhysicalIndex']));
            break;
        case 'memory':
            $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'memory', $components['entPhysicalIndex']));
            break;
        case 'packets':
        case 'throughput':
        default:
            $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'util', $components['entPhysicalIndex']));
            break;
    }

    $title        = generate_device_link($device);
    $title       .= ' :: ' . $components['name'];
    $auth = true;
}
