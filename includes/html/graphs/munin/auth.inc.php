<?php

use App\Models\MuninPlugin;

if (is_numeric($vars['plugin'])) {
    $mplug = MuninPlugin::find($vars['plugin']);
} else {
    $mplug = MuninPlugin::query()
        ->where('device_id', is_array($device) ? $device['device_id'] : $device?->device_id)
        ->where('mplug_type', $vars['plugin'])
        ->first();
}

if ($mplug && ($auth || device_permitted($mplug->device_id))) {
    $device = $mplug->device;
    $title = ' :: Plugin :: ' . $mplug->mplug_type . ' - ' . $mplug->mplug_title;
    $auth = true;
}
