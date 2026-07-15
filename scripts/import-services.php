#!/usr/bin/env php
<?php

/**
 * import-services.php
 *
 * Import services and ports from /etc/services (or custom file) into
 * the service_discovery_known_ports database configuration.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

use App\Facades\LibrenmsConfig;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = [];
require $install_dir . '/includes/init.php';

$options = getopt('f:oyh', ['file:', 'overwrite', 'yes', 'force', 'help']);

function print_help()
{
    echo "LibreNMS Services Discovery Port Import Utility\n";
    echo "Usage: php scripts/import-services.php [options]\n\n";
    echo "Options:\n";
    echo "  -f, --file <file>      Path to a custom services file with the port and service name separated by a space on each line (defaults to /etc/services)\n";
    echo "  -o, --overwrite        Overwrite the existing configuration in the DB (default is to merge)\n";
    echo "  -y, --yes, --force     Bypass the interactive confirmation prompt\n";
    echo "  -h, --help             Display this help message\n";
}

if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

$file = '/etc/services';
if (isset($options['f'])) {
    $file = $options['f'];
} elseif (isset($options['file'])) {
    $file = $options['file'];
}

$overwrite = isset($options['o']) || isset($options['overwrite']);
$force = isset($options['y']) || isset($options['yes']) || isset($options['force']);

if (!file_exists($file)) {
    fwrite(STDERR, "Error: File '$file' does not exist.\n");
    exit(1);
}
if (!is_readable($file)) {
    fwrite(STDERR, "Error: File '$file' is not readable.\n");
    exit(1);
}

function getServicesFromFile(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        fwrite(STDERR, "Error: Failed to read file '$path'.\n");
        exit(1);
    }

    $services = [];

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip blank lines and comments
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Remove inline comments
        $line = preg_replace('/\s*#.*/', '', $line);

        // Split on one or more whitespace characters
        $fields = preg_split('/\s+/', $line);
        if (count($fields) < 2) {
            continue;
        }

        $f0 = trim($fields[0]);
        $f1 = trim($fields[1]);

        $port = null;
        $name = null;
        $protocol = 'tcp'; // default to tcp if not specified

        if (strpos($f0, '/') !== false) {
            list($port, $protocol) = explode('/', $f0, 2);
            $name = $f1;
        } elseif (strpos($f1, '/') !== false) {
            list($port, $protocol) = explode('/', $f1, 2);
            $name = $f0;
        } else {
            // No '/' present. Check if first field is numeric
            if (is_numeric($f0)) {
                $port = $f0;
                $name = $f1;
            } else {
                continue;
            }
        }

        $port = trim($port);
        $protocol = trim(strtolower($protocol));
        $name = trim($name);

        if ($protocol === 'tcp' && is_numeric($port)) {
            $portVal = (int) $port;
            // Keep the first defined service name for a given port if duplicates exist
            if (!isset($services[$portVal])) {
                $services[$portVal] = $name;
            }
        }
    }
    return $services;
}

echo "Parsing services file: $file\n";
$new_ports = getServicesFromFile($file);
echo "Found " . count($new_ports) . " service(s) in the file.\n";

$current_ports = LibrenmsConfig::get('service_discovery_known_ports', []);
if (!is_array($current_ports)) {
    $current_ports = [];
}

if ($overwrite) {
    echo "Overwriting existing 'service_discovery_known_ports' configuration in DB.\n";
    $final_ports = $new_ports;
} else {
    echo "Merging with existing 'service_discovery_known_ports' configuration (" . count($current_ports) . " ports already configured).\n";
    // Merge new ports into current ports, keeping current ports on conflict
    $final_ports = $current_ports + $new_ports;
}

$added_count = 0;
foreach ($new_ports as $port => $name) {
    if (!isset($current_ports[$port])) {
        $added_count++;
    }
}

// Sort final array numerically by port key
ksort($final_ports, SORT_NUMERIC);

if (!$force) {
    if ($overwrite) {
        echo "WARNING: This will overwrite your entire service discovery ports configuration with " . count($final_ports) . " ports. Do you want to proceed? [y/N]: ";
    } else {
        echo "This will merge " . count($new_ports) . " ports from '$file' (adding $added_count new ports) into your configuration. Do you want to proceed? [y/N]: ";
    }
    $input = trim(fgets(STDIN));
    if (strtolower($input) !== 'y') {
        echo "Import aborted.\n";
        exit(0);
    }
}

if (LibrenmsConfig::persist('service_discovery_known_ports', $final_ports)) {
    echo "Successfully updated configuration in DB.\n";
    if ($overwrite) {
        echo "Configuration set to " . count($final_ports) . " ports from '$file'.\n";
    } else {
        echo "Merged configuration: " . count($current_ports) . " existing + $added_count new = " . count($final_ports) . " total ports.\n";
    }
} else {
    fwrite(STDERR, "Error: Failed to save the configuration to DB.\n");
    exit(1);
}
