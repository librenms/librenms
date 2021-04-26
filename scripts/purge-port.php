#!/usr/bin/env php
<?php
/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * libreNMS CLI utility to purge old ports.
 *
 * @author Maximilian Wilhelm <max@sdn.clinic>
 * @copyright 2016-2017 LibreNMS, Barbarossa
 * @license GPL
 */

use App\Models\Port;
use Illuminate\Database\Eloquent\ModelNotFoundException;

chdir(dirname($argv[0]));

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

$opt = getopt('p:f:');

// Single Port-id given on cmdline?
$port_id = null;
if ($opt['p']) {
    $port_id = $opt['p'];
}

// File with port-ids given on cmdline?
$port_id_file = null;
if ($opt['f']) {
    $port_id_file = $opt['f'];
}

if (! $port_id && ! $port_id_file || ($port_id && $port_id_file)) {
    echo $console_color->convert(\LibreNMS\Config::get('project_name') . ' Port purge tool
    -p <port_id>  Purge single port by it\'s port-id
    -f <file>     Purge a list of ports, read port-ids from <file>, one on each line.
                  A filename of - means reading from STDIN.
');
}

// Purge single port
if ($port_id) {
    try {
        Port::findOrFail($port_id)->delete();
    } catch (ModelNotFoundException $e) {
        echo "Port ID $port_id not found!\n";
    }
}

// Delete multiple ports
if ($port_id_file) {
    $fh = null;
    if ($port_id_file == '-') {
        $fh = STDIN;
    } else {
        $fh = fopen($port_id_file, 'r');
        if (! $fh) {
            echo 'Failed to open port-id list "' . $port_id_file . "\": \n";
            exit(1);
        }
    }

    while ($port_id = trim(fgets($fh))) {
        try {
            Port::findOrFail($port_id)->delete();
        } catch (ModelNotFoundException $e) {
            echo "Port ID $port_id not found!\n";
        }
    }

    if ($fh != STDIN) {
        fclose($fh);
    }
}
