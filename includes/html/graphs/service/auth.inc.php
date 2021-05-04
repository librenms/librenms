<?php

if (is_numeric($vars['id'])) {
    $service = dbFetchRow('SELECT * FROM services WHERE service_id = ?', [$vars['id']]);

    if (is_numeric($service['device_id']) && ($auth || device_permitted($service['device_id']))) {
        $device = device_by_id_cache($service['device_id']);

        // This doesn't quite work for all yet.
        $rrd_filename = Rrd::name($device['hostname'], ['service', $service['service_type'], $service['service_id']]);

        $title = generate_device_link($device);
        $title .= ' :: Service :: ' . htmlentities($service['service_type']) . ' - ' . htmlentities($service['service_desc']);
        $auth = true;
    }
}
