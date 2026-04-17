<?php

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\WirelessSensor;

$no_refresh = true;

$link_array = ['page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'edit', ];

if (Gate::denies('update', Device::class)) {
    print_error('Insufficient Privileges');
} else {
    $panes['device'] = 'Device Settings';
    $panes['snmp'] = 'SNMP';
    if (! $device['snmp_disable']) {
        $panes['ports'] = 'Port Settings';
    }

    if (BgpPeer::where('device_id', $device['device_id'])->exists()) {
        $panes['routing'] = 'Routing';
    }

    if (count(\App\Facades\LibrenmsConfig::get("os.{$device['os']}.icons", []))) {
        $panes['icon'] = 'Icon';
    }

    if (! $device['snmp_disable']) {
        $panes['apps'] = 'Applications';
    }
    $panes['alert-rules'] = 'Alert Rules';
    if (! $device['snmp_disable']) {
        $panes['modules'] = 'Modules';
    }

    if (\App\Facades\LibrenmsConfig::get('show_services')) {
        $panes['services'] = 'Services';
    }

    $panes['ipmi'] = 'IPMI';

    if (Sensor::where('device_id', $device['device_id'])->where('sensor_deleted', 0)->exists()) {
        $panes['health'] = 'Health';
    }

    if (WirelessSensor::where('device_id', $device['device_id'])->where('sensor_deleted', 0)->exists()) {
        $panes['wireless-sensors'] = 'Wireless Sensors';
    }

    if (! $device['snmp_disable']) {
        $panes['storage'] = 'Storage';
        $panes['processors'] = 'Processors';
        $panes['mempools'] = 'Memory';
    }
    $panes['misc'] = 'Misc';

    $panes['component'] = 'Components';

    $panes['customoid'] = 'Custom OID';

    print_optionbar_start();

    $sep = '';
    foreach ($panes as $type => $text) {
        if (! isset($vars['section'])) {
            $vars['section'] = $type;
        }
        echo $sep;
        if ($vars['section'] == $type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo match ($type) {
            'device' => '<a href="' . route('device.edit', [$device['device_id']]) . "\">$text</a>",
            'misc' => '<a href="' . route('device.edit.misc', [$device['device_id']]) . "\">$text</a>",
            'health' => '<a href="' . route('device.edit.health', [$device['device_id']]) . "\">$text</a>",
            default => generate_link($text, $link_array, ['section' => $type]),
        };

        if ($vars['section'] == $type) {
            echo '</span>';
        }
        $sep = ' | ';
    }

    print_optionbar_end();

    $section = basename((string) $vars['section']);
    if (is_file("includes/html/pages/device/edit/$section.inc.php")) {
        require "includes/html/pages/device/edit/$section.inc.php";
    }
}

$pagetitle[] = 'Settings';
