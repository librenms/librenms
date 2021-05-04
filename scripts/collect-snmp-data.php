#!/usr/bin/env php
<?php

use Illuminate\Support\Str;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = ['discovery', 'polling'];
require $install_dir . '/includes/init.php';

$options = getopt(
    'h:m:no:v:f:d',
    [
        'hostname:',
        'modules:',
        'prefer-new',
        'os:',
        'variant:',
        'file:',
        'debug',
        'snmpsim',
        'full',
        'help',
    ]
);

if (isset($options['snmpsim'])) {
    $snmpsim = new Snmpsim();
    $snmpsim->run();
    exit;
}

if (isset($options['v'])) {
    $variant = $options['v'];
} elseif (isset($options['variant'])) {
    $variant = $options['variant'];
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
    } elseif (! empty($hostname)) {
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

if (isset($options['help']) || empty($target_os) || ! isset($variant)) {
    echo 'Script to collect snmp data from devices to be used for testing.
Snmp data is saved in tests/snmpsim.

Usage:
  You must specify an existing device to collect data from.

Required:
  -h, --hostname     ID, IP, or hostname of the device to collect data from
  -v, --variant      The variant of the OS to use, usually the device model

Optional:
  -m, --modules      The discovery/poller module(s) to collect data for, comma delimited
  -n, --prefer-new   Prefer new snmprec data over existing data
  -o, --os           Name of the OS to save test data for (only used if device is generic)
  -f, --file         Save data to file instead of the standard location
  -d, --debug        Enable debug output
      --snmpsim      Run snmpsimd.py using the collected data for manual testing.
      --full         Walk the whole device (default: only used OIDs)
                     Useful when adding device support when you don\'t have access to it directly,
                     or when discovery/poller causes errors when capturing normally.
                     Do NOT use this to submit test data!

Examples:
  ./collect-snmp-data.php -h 192.168.0.1 -v 2960x
  ./collect-snmp-data.php -h 127.0.0.1 -v freeradius -m applications
';
    exit;
}

Debug::set(isset($options['d']) || isset($options['debug']));

if (isset($options['m'])) {
    $modules_input = $options['m'];
    $modules = explode(',', $modules_input);
} elseif (isset($options['modules'])) {
    $modules_input = $options['modules'];
    $modules = explode(',', $modules_input);
} else {
    $modules_input = 'all';
    $modules = [];
}

if (Str::contains($variant, '_')) {
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
    $full = isset($options['full']);

    echo 'Capturing Data: ';
    \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
    $capture->captureFromDevice($device['device_id'], true, $prefer_new_snmprec, $full);
} catch (InvalidModuleException $e) {
    echo $e->getMessage() . PHP_EOL;
}
