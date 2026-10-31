<?php

if (is_numeric($vars['id'] ?? '')) {
    $cef = \App\Models\CefSwitching::find($vars['id']);

    if (is_numeric($cef?->device_id) && ($auth || device_permitted($cef->device_id))) {
        $device = DeviceCache::get($cef->device_id);

        $rrd_filename = Rrd::name($device->hostname, ['cefswitching', $cef->entPhysicalIndex, $cef->afi, $cef->cef_index]);

        $title = ' :: CEF Switching :: ' . $cef->cef_path . ' ' . $cef->afi;
        $auth = true;
    }
}
