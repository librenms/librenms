<?php

if (is_numeric($vars['id'] ?? '')) {
    $cef = \App\Models\CefSwitching::find($vars['id']);

    if (is_numeric($cef?->device_id) && ($auth || device_permitted($cef->device_id))) {
        $device = device_by_id_cache($cef->device_id);

        $rrd_filename = Rrd::name($device['hostname'], ['cefswitching', $cef->entPhysicalIndex, $cef->afi, $cef->cef_index]);

        $title = generate_device_link($device);
        $title .= ' :: CEF Switching :: ' . htmlentities($cef->cef_path . ' ' . $cef->afi);
        $auth = true;
    }
}
