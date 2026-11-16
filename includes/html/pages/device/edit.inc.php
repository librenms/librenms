<?php

use App\Facades\DeviceCache;

$no_refresh = true;

if (Gate::denies('update', DeviceCache::getPrimary())) {
    print_error('Insufficient Privileges');
} else {
    $section = basename((string) ($vars['section'] ?? 'edit'));

    echo Blade::render('<x-device.edit-tabs :device="$device" :tab="$tab" />', [
        'device' => DeviceCache::get((int) $device['device_id']),
        'tab' => $section,
    ]);

    if (is_file("includes/html/pages/device/edit/$section.inc.php")) {
        require "includes/html/pages/device/edit/$section.inc.php";
    }
}

$pagetitle[] = 'Settings';
