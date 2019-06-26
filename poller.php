#!/usr/bin/env php
<?php
/**
 * poller.php
 *
 * -Description-
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
 * @subpackage poller
 * @copyright  (C) 2006 - 2012 Adam Armstrong

 * Modified 4/17/19
 * @author Heath Barnhart <hbarnhart@kanren.net>
 */

use LibreNMS\Config;
use LibreNMS\Alert\AlertRules;

$init_modules = ['polling', 'alerts', 'laravel'];
require __DIR__ . '/includes/init.php';

$poller_start = microtime(true);
echo Config::get('base_url') . " Poller\n";

$options = getopt('h:m:i:n:r::d::v::a::f::q');

if (isset($options['h'])) {
    if ($options['h'] == 'odd') {
        $options['n'] = '1';
        $options['i'] = '2';
    } elseif ($options['h'] == 'even') {
        $options['n'] = '0';
        $options['i'] = '2';
    } elseif ($options['h'] == 'all') {
        $where = ' ';
        $doing = 'all';
    } elseif ($options['h']) {
        if (is_numeric($options['h'])) {
            $where = "AND `device_id` = " . $options['h'];
            $doing = $options['h'];
        } else {
            if (preg_match('/\*/', $options['h'])) {
                $where = "AND `hostname` LIKE '" . str_replace('*', '%', mres($options['h'])) . "'";
            } else {
                $where = "AND `hostname` = '" . mres($options['h']) . "'";
            }
            $doing = $options['h'];
        }
    }
}

if (isset($options['i']) && $options['i'] && isset($options['n'])) {
    $where = true;
    // FIXME
    $query = 'SELECT * FROM (SELECT @rownum :=0) r,
        (
            SELECT @rownum := @rownum +1 AS rownum, `devices`.*
            FROM `devices`
            WHERE `disabled` = 0
            ORDER BY `device_id` ASC
        ) temp
        WHERE MOD(temp.rownum, '.mres($options['i']).') = '.mres($options['n']).';';
    $doing = $options['n'].'/'.$options['i'];
}

if (empty($where)) {
    echo "-h <device id> | <device hostname wildcard>  Poll single device\n";
    echo "-h odd             Poll odd numbered devices  (same as -i 2 -n 0)\n";
    echo "-h even            Poll even numbered devices (same as -i 2 -n 1)\n";
    echo "-h all             Poll all devices\n\n";
    echo "-i <instances> -n <number>                   Poll as instance <number> of <instances>\n";
    echo "                   Instances start at 0. 0-3 for -n 4\n\n";
    echo "Debugging and testing options:\n";
    echo "-r                 Do not create or update RRDs\n";
    echo "-f                 Do not insert data into InfluxDB\n";
    echo "-p                 Do not insert data into Prometheus\n";
    echo "-d                 Enable debugging output\n";
    echo "-v                 Enable verbose debugging output\n";
    echo "-m                 Specify module(s) to be run. Comma separate modules, submodules may be added with /\n";
    echo "\n";
    echo "No polling type specified!\n";
    exit;
}

if (set_debug(isset($options['d'])) || isset($options['v'])) {
    $versions = version_info();
    echo <<<EOH
===================================
Version info:
Commit SHA: {$versions['local_sha']}
Commit Date: {$versions['local_date']}
DB Schema: {$versions['db_schema']}
PHP: {$versions['php_ver']}
MySQL: {$versions['mysql_ver']}
RRDTool: {$versions['rrdtool_ver']}
SNMP: {$versions['netsnmp_ver']}
==================================
EOH;

    echo "DEBUG!\n";
    if (isset($options['v'])) {
        $vdebug = true;
    }
    update_os_cache(true); // Force update of OS Cache
}

if (isset($options['r'])) {
    Config::set('norrd', true);
}

if (isset($options['f'])) {
    Config::set('noinfluxdb', true);
}

if (isset($options['p'])) {
    $prometheus = false;
}

if (isset($options['g'])) {
    Config::set('nographite', true);
}

if (Config::get('base_url') !== true && Config::get('influxdb.enable') === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

if (Config::get('base_url') !== true && Config::get('graphite.enable') === true) {
    $graphite = fsockopen(Config::get('graphite.host'), Config::get('graphite.port'));
    if ($graphite !== false) {
        echo "Connection made to " . Config::get('graphite.host') . " for Graphite support\n";
    } else {
        echo "Connection to " . Config::get('graphite.host') . " has failed, Graphite support disabled\n";
        Config::set('nographite', true);
    }
} else {
    $graphite = false;
}

// If we've specified modules with -m, use them
$module_override = parse_modules('poller', $options);

rrdtool_initialize();

echo "Starting polling run:\n\n";
$polled_devices = 0;
$unreachable_devices = 0;
if (!isset($query)) {
    $query = "SELECT * FROM `devices` WHERE `disabled` = 0 $where ORDER BY `device_id` ASC";
}

foreach (dbFetch($query) as $device) {
    if ($device['os_group'] == 'cisco') {
        $device['vrf_lite_cisco'] = dbFetchRows("SELECT * FROM `vrf_lite_cisco` WHERE `device_id` = " . $device['device_id']);
    } else {
        $device['vrf_lite_cisco'] = '';
    }

    if (!poll_device($device, $module_override)) {
        $unreachable_devices++;
    }

    echo "#### Start Alerts ####\n";
    $rules = new AlertRules();
    $rules->runRules($device['device_id']);
    echo "#### End Alerts ####\r\n";
    $polled_devices++;
}

$poller_end  = microtime(true);
$poller_run  = ($poller_end - $poller_start);
$poller_time = substr($poller_run, 0, 5);

if ($graphite !== false) {
    fclose($graphite);
}

if ($polled_devices) {
    dbInsert(array(
        'type' => 'poll',
        'doing' => $doing,
        'start' => $poller_start,
        'duration' => $poller_time,
        'devices' => $polled_devices,
        'poller' => Config::get('base_url')
    ), 'perf_times');
}

$string = $argv[0] . " $doing " . date(Config::get('dateformat.compact')) . " - $polled_devices devices polled in $poller_time secs";
d_echo("$string\n");

if (!isset($options['q'])) {
    printStats();
}

logfile($string);
rrdtool_close();

// Remove this for testing
// print_r(get_defined_vars());

if ($polled_devices === $unreachable_devices) {
    exit(6);
}

exit(0);
