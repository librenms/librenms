<?php

use App\Models\Sensor;

/*
 * QFP count for cisco devices
 */
$qfp = 0;
if ($device['os_group'] == 'cisco') {
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], ['type'=> 'cisco-qfp']);
    $components = $components[$device['device_id']];
    $qfp = count($components);
}

if ($qfp) {
    $datas[] = 'qfp';
}

unset($datas);
$datas[] = 'overview';
$type_text['overview'] = 'Overview';

/*
 * Main Sensors
 */

$main_sensors = [
    'storage' => 'Storage',
    'ucd_diskio' => 'Disk I/O',
    'mempools' => 'Memory pools',
    'processors' => 'Processors',
];

foreach (array_keys($main_sensors) as $health) {
    if (dbFetchCell('select count(*) from ' . $health . ' WHERE device_id = ?', [$device['device_id']])) {
        $datas[] = $health;
        $type_text[$health] = $main_sensors[$health];
    }
}

    /*
     * Sensors
     */

    foreach (Sensor::getTypes() as $sensor) {
        if (Sensor::where('device_id', $device['device_id'])
                      ->where('sensor_class', $sensor)
                      ->count()) {
            $datas[] = $sensor;
            $type_text[$sensor] = Sensor::$text[$sensor];
        }
    }

    $type_text['qfp'] = 'QFP';

    $link_array = [
        'page'   => 'device',
        'device' => $device['device_id'],
        'tab'    => 'health',
    ];

    print_optionbar_start();

    echo "<span style='font-weight: bold;'>Health</span> &#187; ";

    if (! $vars['metric']) {
        $vars['metric'] = 'overview';
    }

    unset($sep);
    foreach ($datas as $type) {
        echo $sep;
        if ($vars['metric'] == $type) {
            echo '<span class="pagemenu-selected">';
        }

        echo generate_link($type_text[$type], $link_array, ['metric' => $type]);
        if ($vars['metric'] == $type) {
            echo '</span>';
        }

        $sep = ' | ';
    }

    print_optionbar_end();

    $metric = basename($vars['metric']);
    if (is_file("includes/html/pages/device/health/$metric.inc.php")) {
        include "includes/html/pages/device/health/$metric.inc.php";
    } else {
        foreach ($datas as $type) {
            if ($type != 'overview') {
                $graph_title = $type_text[$type];
                $graph_array['type'] = 'device_' . $type;
                include 'includes/html/print-device-graph.php';
            }
        }
    }

    $pagetitle[] = 'Health';
