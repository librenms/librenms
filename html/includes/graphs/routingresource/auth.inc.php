<?php

if (is_numeric($vars['id'])) {
    $routing_resources = dbFetchRow('SELECT * FROM routing_resources WHERE resource_id = ?', array($vars['id']));

    if (is_numeric($routing_resources['device_id']) && ($auth || device_permitted($routing_resources['device_id']))) {
        $device = device_by_id_cache($routing_resources['device_id']);
        $rrd_filename = rrd_name($device['hostname'], array('routing_resources', $routing_resources['resource_id'], $routing_resources['resource']));

        $title  = generate_device_link($device);
        $title .= ' :: Router Utilization :: '.htmlentities($routing_resources['feature']);
        $auth   = true;
    }
}
