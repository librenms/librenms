#!/usr/bin/env php
<?php

use LibreNMS\Proc;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$options = getopt(
    'hdm:o:v:f:',
    array(
        'debug',
        'hostname:',
        'help',
        'module:',
        'os:',
        'variant:',
        'file:',
    )
);

$debug = (isset($options['d']) || isset($options['debug']));
$vdebug = $debug;

$init_modules = array('discovery');
require $install_dir . '/includes/init.php';


if (isset($options['o'])) {
    $target_os = $options['o'];
} elseif (isset($options['os'])) {
    $target_os = $options['os'];
}

if (isset($options['help']) || !isset($target_os)) {
    echo "Insert help here\n";
    exit;
}

$module = 'all';
if (isset($options['m'])) {
    $module = $options['m'];
} elseif (isset($options['module'])) {
    $module = $options['module'];
}

$variant = '';
if (isset($options['v'])) {
    $variant = '_' . $options['v'];
} elseif (isset($options['variant'])) {
    $variant = '_' . $options['variant'];
}

$output_file = $install_dir . "/tests/data/$target_os$variant.json";
if (isset($options['f'])) {
    $output_file = $options['f'];
} elseif(isset($options['file'])) {
    $output_file = $options['file'];
}

$ip = '127.1.6.1';
$snmpsim_log = "/tmp/snmpsimd.log";
$snmpsim_cmd = "snmpsimd.py --data-dir=./tests/snmpsim --agent-udpv4-endpoint=$ip:1161 --logging-method=file:$snmpsim_log";
echo "Starting snmpsimd...\n";
d_echo($snmpsim_cmd);
$proc_snmpsimd = new Proc($snmpsim_cmd);
echo "Logfile: $snmpsim_log\n";


// Remove existing device in case it didn't get removed previously
if ($existing_device = device_by_name($ip)) {
    delete_device($existing_device['device_id']);
}


try {
    // Add the test device
    $config['snmp']['community'] = array($target_os . $variant);
    $device_id = addHost($ip, 'v2c', 1161);
    echo "Added device: $device_id\n";

    // Populate the device variable
    $device = device_by_id_cache($device_id);

    // Run discovery
    discover_device($device, $module == 'all' ? array() : array('m' => $module));

    // Dump the discovered data
    $data = dump_module_data($device_id, $module);

    // Remove the test device
    echo delete_device($device_id)."\n";

    d_echo($data);

    // Save the data to the default test data location (or elsewhere if specified)
    file_put_contents($output_file, _json_encode($data));
    echo "Saved to $output_file\n";
    echo "Ready for testing!\n";
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit;
}
