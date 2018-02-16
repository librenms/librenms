<?php

if (is_numeric($vars['id'])) {
    $router_utilization = dbFetchRow('SELECT * FROM router_utilization WHERE id = ?', array($vars['id']));

    if (is_numeric($router_utilization['device_id']) && ($auth || device_permitted($router_utilization['device_id']))) {
        $device = device_by_id_cache($router_utilization['device_id']);
        $rrd_filename = rrd_name($device['hostname'], array('router_utilization', $router_utilization['id'], $router_utilization['resource']));

        $title  = generate_device_link($device);
        $title .= ' :: Router Utilization :: '.htmlentities($router_utilization['feature']);
        $auth   = true;
    }
}
