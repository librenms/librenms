#!/usr/bin/env php
<?php
/**
 * rrdstep.php
 *
 * LibreNMS Script to convert rrd files from default 300 step to user defined
 *
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
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

use LibreNMS\Config;

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

$options = getopt('h:');

$hostname = $options['h'] ?: '';

if (empty($hostname)) {
    echo "*********************************************************************\n";
    echo "We highly suggest that you back up your rrd files before running this\n";
    echo "*********************************************************************\n";
    echo "-h <hostname or device id or all>  Device to process the rrd file for\n";
    echo "\n";
    exit;
}

if ($hostname !== 'all') {
    $hostname = ! ctype_digit($hostname) ? $hostname : gethostbyid($hostname);
}

if (empty($hostname)) {
    echo "Invalid hostname or device id specified\n";
    exit;
}

$system_step = Config::get('rrd.step', 300);
$icmp_step = Config::get('ping_rrd_step', $system_step);
$system_heartbeat = Config::get('rrd.heartbeat', $system_step * 2);
$rrdtool = Config::get('rrdtool', 'rrdtool');
$tmp_path = Config::get('temp_dir', '/tmp');

if ($hostname === 'all') {
    $hostname = '*';
}
$files = glob(Rrd::dirFromHost($hostname) . '/*.rrd');

$converted = 0;
$skipped = 0;
$failed = 0;

foreach ($files as $file) {
    $random = $tmp_path . '/' . mt_rand() . '.xml';
    $rrd_file = basename($file, '.rrd');

    if ($rrd_file == 'ping-perf') {
        $step = $icmp_step;
        $heartbeat = $icmp_step * 2;
    } else {
        $step = $system_step;
        $heartbeat = $system_heartbeat;
    }

    $rrd_info = shell_exec("$rrdtool info $file");
    preg_match('/step = (\d+)/', $rrd_info, $step_matches);

    if ($step_matches[1] == $step) {
        preg_match_all('/minimal_heartbeat = (\d+)/', $rrd_info, $heartbeat_matches);
        try {
            foreach ($heartbeat_matches[1] as $ds_heartbeat) {
                if ($ds_heartbeat != $heartbeat) {
                    throw new Exception("Mismatched heartbeat. {$ds_heartbeat} != $heartbeat");
                }
            }
            // all heartbeats ok

            d_echo("Skipping $file, step is already $step.\n");
            $skipped++;
            continue;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    echo "Converting $file: ";
    $command = "$rrdtool dump $file > $random &&
        sed -i 's/<step>\([0-9]*\)/<step>$step/' $random &&
        sed -i 's/<minimal_heartbeat>\([0-9]*\)/<minimal_heartbeat>$heartbeat/' $random &&
        $rrdtool restore -f $random $file &&
        rm -f $random";
    exec($command, $output, $code);
    if ($code === 0) {
        echo "[OK]\n";
        $converted++;
    } else {
        echo "\033[FAIL]\n";
        $failed++;
    }
}

echo "Converted: $converted  Failed: $failed  Skipped: $skipped\n";
