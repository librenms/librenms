#!/usr/bin/env php
<?php

use LibreNMS\Config;
use LibreNMS\Proc;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;

global $device;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$options = getopt(
    'h:dnm:o:v:f:',
    array(
        'debug',
        'no-save',
        'prefer-new',
        'hostname:',
        'help',
        'module:',
        'os:',
        'variant:',
        'file:',
        'snmpsim',
    )
);

$init_modules = array('discovery', 'polling');
require $install_dir . '/includes/init.php';

$debug = (isset($options['d']) || isset($options['debug']));
$vdebug = $debug;


if (isset($options['snmpsim'])) {
    $snmpsim = new Snmpsim();
    $snmpsim->run();
    exit;
}

if (isset($options['h'])) {
    $hostname = $options['h'];
} elseif (isset($options['hostname'])) {
    $hostname = $options['hostname'];
}

$target_os = '';
if (isset($options['o'])) {
    $target_os = $options['o'];
} elseif (isset($options['os'])) {
    $target_os = $options['os'];
}

if (isset($hostname)) {
    if (is_numeric($hostname)) {
        $device = device_by_id_cache($hostname);
    } elseif (!empty($hostname)) {
        $device = device_by_name($hostname);
    }

    if (isset($device['os']) && $device['os'] != 'generic') {
        $target_os = $device['os'];
    } else {
        echo "OS (-o, --os) required because device is generic.\n";
        exit;
    }
}

if (isset($options['help']) || empty($target_os)) {
    echo "Script to extract test data from devices or update test data

Usage:
  You must specify a valid hostname or os.
  -h, --hostname    ID, IP, or hostname of the device to extract data from
                    If this is not given, the existing snmp data will be used
  -o, --os          Name of the OS to save test data for
  -v, --variant     The variant of the OS to use, usually the device model
  -m, --modules     The discovery/poller module(s) to collect data for, comma delimited
  -f, --file        File to save the database entries to.  Default is in tests/data/
  -d, --debug       Enable debug output
  -n, --prefer-new  Prefer new snmprec data over existing data
      --no-save     Don't save database entries, print them out instead
      --snmpsim     Just run snmpsimd.py for manual testing.
";
    exit;
}

if (isset($options['m'])) {
    $modules_input = $options['m'];
    $modules = explode(',', $modules_input);
} elseif (isset($options['modules'])) {
    $modules_input = $options['modules'];
    $modules = explode(',', $modules_input);
} else {
    $modules_input = 'all';
    $modules = array();
}

$variant = '';
if (isset($options['v'])) {
    $variant = $options['v'];
} elseif (isset($options['variant'])) {
    $variant = $options['variant'];
}

echo "OS: $target_os\n";
echo "Module: $modules_input\n";
if ($variant) {
    echo "Variant: $variant\n";
}
echo PHP_EOL;


$tester = new ModuleTestHelper($modules, $target_os, $variant);


// Capture snmp data
if ($device) {
    echo "Capturing Data: ";
    $prefer_new_snmprec = isset($options['n']) || isset($options['prefer-new']);
    $tester->captureFromDevice($device['device_id'], true, $prefer_new_snmprec);

    echo PHP_EOL;
}


// Now use the saved data to update the saved database data
$snmpsim = new Snmpsim();
$snmpsim->fork();
$snmpsim_ip = $snmpsim->getIp();
$snmpsim_port = $snmpsim->getPort();


$no_save = isset($options['no-save']);
$test_data = $tester->generateTestData($snmpsim, $no_save);

if ($no_save) {
    print_r($test_data);
}
