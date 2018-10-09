#!/usr/bin/env php
<?php

use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = array('discovery', 'polling');
require $install_dir . '/includes/init.php';

$options = getopt(
    'h:m:no:v:f:d',
    array(
        'hostname:',
        'modules:',
        'prefer-new',
        'os:',
        'variant:',
        'file:',
        'debug',
        'snmpsim',
        'help',
    )
);

if (isset($options['snmpsim'])) {
    $snmpsim = new Snmpsim();
    $snmpsim->run();
    exit;
}

// check for hostname
if (isset($options['h'])) {
    $hostname = $options['h'];
} elseif (isset($options['hostname'])) {
    $hostname = $options['hostname'];
}

if (isset($hostname)) {
    if (is_numeric($hostname)) {
        $device = device_by_id_cache($hostname);
    } elseif (!empty($hostname)) {
        $device = device_by_name($hostname);
    }

    if (isset($device['os']) && $device['os'] != 'generic') {
        $target_os = $device['os'];
    } elseif (isset($options['o'])) {
        $target_os = $options['o'];
    } elseif (isset($options['os'])) {
        $target_os = $options['os'];
    } else {
        echo "OS (-o, --os) required because device is generic.\n";
        exit;
    }
}

if (isset($options['help']) || empty($target_os)) {
    echo "Script to collect snmp data from devices to be used for testing.
Snmp data is saved in tests/snmpsim.

Usage:
  You must specify an existing device to collect data from.
Required
  -h, --hostname     ID, IP, or hostname of the device to collect data from
Optional:
  -m, --modules      The discovery/poller module(s) to collect data for, comma delimited
  -n, --prefer-new   Prefer new snmprec data over existing data
  -o, --os           Name of the OS to save test data for (only used if device is generic)
  -v, --variant      The variant of the OS to use, usually the device model
  -f, --file         Save data to file instead of the standard location
  -d, --debug        Enable debug output
      --snmpsim      Run snmpsimd.py using the collected data for manual testing.
";
    exit;
}

$debug = (isset($options['d']) || isset($options['debug']));

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

if (str_contains($variant, '_')) {
    exit("Variant name cannot contain an underscore (_).\n");
}

echo "OS: $target_os\n";
echo "Module(s): $modules_input\n";
if ($variant) {
    echo "Variant: $variant\n";
}
echo PHP_EOL;

try {
    $capture = new ModuleTestHelper($modules, $target_os, $variant);


    if (isset($options['f'])) {
        $capture->setSnmprecSavePath($options['f']);
    } elseif (isset($options['file'])) {
        $capture->setSnmprecSavePath($options['file']);
    }

    $prefer_new_snmprec = isset($options['n']) || isset($options['prefer-new']);


    echo "Capturing Data: ";
    update_os_cache(true); // Force update of OS Cache
    $capture->captureFromDevice($device['device_id'], true, $prefer_new_snmprec);
} catch (InvalidModuleException $e) {
    echo $e->getMessage() . PHP_EOL;
}
