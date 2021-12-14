#!/usr/bin/env php
<?php

use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;

global $device;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$options = getopt(
    'o:v:m:nf:dh',
    [
        'os:',
        'variant:',
        'modules:',
        'no-save',
        'file:',
        'debug',
        'snmpsim',
        'help',
    ]
);

$init_modules = ['discovery', 'polling'];
require $install_dir . '/includes/init.php';

Debug::setVerbose(
    Debug::set(isset($options['d']) || isset($options['debug']))
);

if (isset($options['snmpsim'])) {
    $snmpsim = new Snmpsim();
    $snmpsim->run();
    exit;
}

if (isset($options['h'])
    || isset($options['help'])
    || (isset($options['o']) || isset($options['os'])) && ! (isset($options['v']) || isset($options['variant']))
    || ! (isset($options['o']) || isset($options['os']) || isset($options['m']) || isset($options['modules']))
) {
    echo "Script to update test data. Database data is saved in tests/data.

Usage:
  You must specify a valid OS (and variant) and/or module(s).

Required:
  -o, --os           Name of the OS to save test data for
  -v, --variant      The variant of the OS to use, usually the device model

Optional:
  -m, --modules      The discovery/poller module(s) to collect data for, comma delimited
  -n, --no-save      Don't save database entries, print them out instead
  -f, --file         Save data to file instead of the standard location
  -d, --debug        Enable debug output
      --snmpsim      Run snmpsimd.py using the collected data for manual testing.

Examples:
  ./save-test-data.php -o ios -v 2960x
  ./save-test-data.php -o linux -v freeradius -m applications
";
    exit;
}

$os_name = false;
if (isset($options['o'])) {
    $os_name = $options['o'];
} elseif (isset($options['os'])) {
    $os_name = $options['os'];
}

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

$full_os_name = $os_name;
$variant = '';
if (isset($options['v'])) {
    $variant = $options['v'];
    $full_os_name = $os_name . '_' . $variant;
} elseif (isset($options['variant'])) {
    $variant = $options['variant'];
    $full_os_name = $os_name . '_' . $variant;
}

$os_list = [];

if ($os_name) {
    $os_list = [$full_os_name => [$os_name, $variant]];
} else {
    $os_list = ModuleTestHelper::findOsWithData($modules);
}

if (isset($options['f'])) {
    if (count($os_list) != 1) {
        echo "Failed to create test data, -f/--file option can be used with one os/variant combination.\n";
        echo 'Multiple combinations (' . count($os_list) . ") found.\n";
        exit(1);
    }
    $output_file = $options['f'];
}

// Now use the saved data to update the saved database data
$snmpsim = new Snmpsim();
$snmpsim->fork();
$snmpsim_ip = $snmpsim->getIp();
$snmpsim_port = $snmpsim->getPort();

if (! $snmpsim->isRunning()) {
    echo "Failed to start snmpsim, make sure it is installed, working, and there are no bad snmprec files.\n";
    echo "Run ./scripts/save-test-data.php --snmpsim to see the log output\n";
    exit(1);
}

try {
    $no_save = isset($options['n']) || isset($options['no-save']);
    foreach ($os_list as $full_os_name => $parts) {
        [$target_os, $target_variant] = $parts;
        echo "OS: $target_os\n";
        echo "Module: $modules_input\n";
        if ($target_variant) {
            echo "Variant: $target_variant\n";
        }
        echo PHP_EOL;

        \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
        $tester = new ModuleTestHelper($modules, $target_os, $target_variant);
        if (! $no_save && ! empty($output_file)) {
            $tester->setJsonSavePath($output_file);
        }
        $test_data = $tester->generateTestData($snmpsim, $no_save);

        if ($no_save) {
            print_r($test_data);
        }
    }
} catch (InvalidModuleException $e) {
    echo $e->getMessage() . PHP_EOL;
}

$snmpsim->stop();
