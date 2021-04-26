<?php

if (is_numeric($vars['id']) && ($auth || application_permitted($vars['id']))) {
    $app = get_application_by_id($vars['id']);
    $device = device_by_id_cache($app['device_id']);
    if ($app['app_type'] != 'proxmox') {
        $title = generate_device_link($device);
        $title .= $graph_subtype;
    } else {
        $title = $vars['port'] . '@' . $vars['hostname'] . ' on ' . generate_device_link($device);
    }
    $auth = true;
}
