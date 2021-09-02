<?php

$storage = dbFetchCell('select count(*) from storage WHERE device_id = ?', [$device['device_id']]);
$diskio = dbFetchRows('SELECT * FROM `ucd_diskio` WHERE device_id = ? ORDER BY diskio_descr', [$device['device_id']]);
$mempools = dbFetchCell('select count(*) from mempools WHERE device_id = ?', [$device['device_id']]);
$processor = dbFetchCell('select count(*) from processors WHERE device_id = ?', [$device['device_id']]);

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

$count = dbFetchCell("select count(*) from sensors WHERE sensor_class='count' AND device_id = ?", [$device['device_id']]);
$temperatures = dbFetchCell("select count(*) from sensors WHERE sensor_class='temperature' AND device_id = ?", [$device['device_id']]);
$humidity = dbFetchCell("select count(*) from sensors WHERE sensor_class='humidity' AND device_id = ?", [$device['device_id']]);
$fans = dbFetchCell("select count(*) from sensors WHERE sensor_class='fanspeed' AND device_id = ?", [$device['device_id']]);
$volts = dbFetchCell("select count(*) from sensors WHERE sensor_class='voltage' AND device_id = ?", [$device['device_id']]);
$current = dbFetchCell("select count(*) from sensors WHERE sensor_class='current' AND device_id = ?", [$device['device_id']]);
$freqs = dbFetchCell("select count(*) from sensors WHERE sensor_class='frequency' AND device_id = ?", [$device['device_id']]);
$runtime = dbFetchCell("select count(*) from sensors WHERE sensor_class='runtime' AND device_id = ?", [$device['device_id']]);
$power = dbFetchCell("select count(*) from sensors WHERE sensor_class='power' AND device_id = ?", [$device['device_id']]);
$power_consumed = dbFetchCell("select count(*) from sensors WHERE sensor_class='power_consumed' AND device_id = ?", [$device['device_id']]);
$power_factor = dbFetchCell("select count(*) from sensors WHERE sensor_class='power_factor' AND device_id = ?", [$device['device_id']]);
$dBm = dbFetchCell("select count(*) from sensors WHERE sensor_class='dBm' AND device_id = ?", [$device['device_id']]);
$states = dbFetchCell("select count(*) from sensors WHERE sensor_class='state' AND device_id = ?", [$device['device_id']]);
$charge = dbFetchCell("select count(*) from sensors WHERE sensor_class='charge' AND device_id = ?", [$device['device_id']]);
$load = dbFetchCell("select count(*) from sensors WHERE sensor_class='load' AND device_id = ?", [$device['device_id']]);
$loss = dbFetchCell("select count(*) from sensors WHERE sensor_class='loss' AND device_id = ?", [$device['device_id']]);
$signal = dbFetchCell("select count(*) from sensors WHERE sensor_class='signal' AND device_id = ?", [$device['device_id']]);
$airflow = dbFetchCell("select count(*) from sensors WHERE sensor_class='airflow' AND device_id = ?", [$device['device_id']]);
$snr = dbFetchCell("select count(*) from sensors WHERE sensor_class='snr' AND device_id = ?", [$device['device_id']]);
$pressure = dbFetchCell("select count(*) from sensors WHERE sensor_class='pressure' AND device_id = ?", [$device['device_id']]);
$cooling = dbFetchCell("select count(*) from sensors WHERE sensor_class='cooling' AND device_id = ?", [$device['device_id']]);
$delay = dbFetchCell("select count(*) from sensors WHERE sensor_class='delay' AND device_id = ?", [$device['device_id']]);
$quality_factor = dbFetchCell("select count(*) from sensors WHERE sensor_class='quality_factor' AND device_id = ?", [$device['device_id']]);
$chromatic_dispersion = dbFetchCell("select count(*) from sensors WHERE sensor_class='chromatic_dispersion' AND device_id = ?", [$device['device_id']]);
$ber = dbFetchCell("select count(*) from sensors WHERE sensor_class='ber' AND device_id = ?", [$device['device_id']]);
$eer = dbFetchCell("select count(*) from sensors WHERE sensor_class='eer' AND device_id = ?", [$device['device_id']]);
$waterflow = dbFetchCell("select count(*) from sensors WHERE sensor_class='waterflow' AND device_id = ?", [$device['device_id']]);
$percent = dbFetchCell("select count(*) from sensors WHERE sensor_class='percent' AND device_id = ?", [$device['device_id']]);
$tv_signal = dbFetchCell("select count(*) from sensors WHERE sensor_class='tv_signal' AND device_id = ?", [$device['device_id']]);

unset($datas);
$datas[] = 'overview';
if ($processor) {
    $datas[] = 'processor';
}

if ($qfp) {
    $datas[] = 'qfp';
}

if ($mempools) {
    $datas[] = 'mempool';
}

if ($storage) {
    $datas[] = 'storage';
}

if ($diskio) {
    $datas[] = 'diskio';
}

if ($charge) {
    $datas[] = 'charge';
}

if ($temperatures) {
    $datas[] = 'temperature';
}

if ($humidity) {
    $datas[] = 'humidity';
}

if ($fans) {
    $datas[] = 'fanspeed';
}

if ($volts) {
    $datas[] = 'voltage';
}

if ($freqs) {
    $datas[] = 'frequency';
}

if ($runtime) {
    $datas[] = 'runtime';
}

if ($current) {
    $datas[] = 'current';
}

if ($power) {
    $datas[] = 'power';
}

if ($power_consumed) {
    $datas[] = 'power_consumed';
}

if ($power_factor) {
    $datas[] = 'power_factor';
}

if ($dBm) {
    $datas[] = 'dbm';
}

if ($states) {
    $datas[] = 'state';
}

if ($count) {
    $datas[] = 'count';
}

if ($load) {
    $datas[] = 'load';
}

if ($signal) {
    $datas[] = 'signal';
}

if ($tv_signal) {
    $datas[] = 'tv_signal';
}

if ($airflow) {
    $datas[] = 'airflow';
}

if ($snr) {
    $datas[] = 'snr';
}

if ($pressure) {
    $datas[] = 'pressure';
}

if ($cooling) {
    $datas[] = 'cooling';
}

if ($delay) {
    $datas[] = 'delay';
}

if ($quality_factor) {
    $datas[] = 'quality_factor';
}

if ($chromatic_dispersion) {
    $datas[] = 'chromatic_dispersion';
}

if ($ber) {
    $datas[] = 'ber';
}

if ($eer) {
    $datas[] = 'eer';
}

if ($waterflow) {
    $datas[] = 'waterflow';
}

if ($loss) {
    $datas[] = 'loss';
}

if ($percent) {
    $datas[] = 'percent';
}

$type_text['overview'] = 'Overview';
$type_text['charge'] = 'Battery Charge';
$type_text['temperature'] = 'Temperature';
$type_text['humidity'] = 'Humidity';
$type_text['mempool'] = 'Memory';
$type_text['storage'] = 'Disk Usage';
$type_text['diskio'] = 'Disk I/O';
$type_text['processor'] = 'Processor';
$type_text['voltage'] = 'Voltage';
$type_text['fanspeed'] = 'Fanspeed';
$type_text['frequency'] = 'Frequency';
$type_text['runtime'] = 'Runtime remaining';
$type_text['current'] = 'Current';
$type_text['power'] = 'Power';
$type_text['power_consumed'] = 'Power Consumed';
$type_text['power_factor'] = 'Power Factor';
$type_text['dbm'] = 'dBm';
$type_text['state'] = 'State';
$type_text['count'] = 'Count';
$type_text['load'] = 'Load';
$type_text['signal'] = 'Signal';
$type_text['tv_signal'] = 'TV signal';
$type_text['airflow'] = 'Airflow';
$type_text['snr'] = 'SNR';
$type_text['pressure'] = 'Pressure';
$type_text['cooling'] = 'Cooling';
$type_text['delay'] = 'Delay';
$type_text['quality_factor'] = 'Quality factor';
$type_text['chromatic_dispersion'] = 'Chromatic Dispersion';
$type_text['ber'] = 'Bit Error Rate';
$type_text['eer'] = 'Energy Efficiency Ratio';
$type_text['waterflow'] = 'Water Flow Rate';
$type_text['loss'] = 'Loss';
$type_text['qfp'] = 'QFP';
$type_text['percent'] = 'Percent';

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
