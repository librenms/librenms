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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$init_modules = array();
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
    $hostname = !ctype_digit($hostname) ? $hostname : gethostbyid($hostname);
}

if (empty($hostname)) {
    echo "Invalid hostname or device id specified\n";
    exit;
}

$step      = $config['rrd']['step'];
$heartbeat = $config['rrd']['heartbeat'];
$rrd_path  = $config['rrd_dir'];
$rrdtool   = $config['rrdtool'];
$tmp_path  = $config['temp_dir'];

if ($hostname === 'all') {
    $hostname = '*';
}
$files = glob($rrd_path . '/' . $hostname . '/*.rrd');

$run = readline("Are you sure you want to run this command [N/y]: ");
if (!($run == 'y' || $run == 'Y')) {
    echo "Exiting....." . PHP_EOL;
    exit;
}

foreach ($files as $file) {
    $random = $tmp_path.'/'.mt_rand() . '.xml';
    $tmp = explode('/', $file);
    $rrd_file = array_pop($tmp);
    echo "Converting $file: ";
    $command = "$rrdtool dump $file > $random && 
        sed -i 's/<step>\([0-9]*\)/<step>$step/' $random && 
        sed -i 's/<minimal_heartbeat>\([0-9]*\)/<minimal_heartbeat>$heartbeat/' $random &&
        $rrdtool restore -f $random $file &&
        rm -f $random";
    exec($command, $output, $code);
    if ($code === 0) {
        echo "[OK]\n";
    } else {
        echo "\033[FAIL]\n";
    }
}
