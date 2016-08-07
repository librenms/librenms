#!/usr/bin/env php
<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@librenms.org>
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
 */

/**
 * SNMP Scan
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Discovery
 */

$ts = microtime(true);

chdir(dirname($argv[0]));

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';

if ($config['autodiscovery']['snmpscan'] == false) {
    echo 'SNMP-Scan disabled.'.PHP_EOL;
    exit(2);
}

require 'includes/functions.php';
require 'includes/discovery/functions.inc.php';

function perform_snmp_scan($net) {
    global $stats, $config, $quiet;
    echo 'Range: '.$net->network.'/'.$net->bitmask.PHP_EOL;
    $config['snmp']['timeout'] = 1;
    $config['snmp']['retries'] = 0;
    $config['fping_options']['retries']  = 0;
    $start = ip2long($net->network);
    $end   = ip2long($net->broadcast)-1;
    while ($start++ < $end) {
        $stats['count']++;
        $host = long2ip($start);
        $test = isPingable($host);
        if ($test['result'] === false) {
            echo '.';
            continue;
        }
        if (ip_exists($host)) {
            $stats['known']++;
            echo '*';
            continue;
        }
        foreach (array('udp','tcp') as $transport) {
            $result = addHost(gethostbyaddr($host), '', $config['snmp']['port'], $transport, $quiet, $config['distributed_poller_group'], 0);
            if (is_numeric($result)) {
                $stats['added']++;
                echo '+';
                break;
            } elseif (substr($result, 0, 12) === 'Already have') {
                $stats['known']++;
                echo '*';
                break;
            } elseif (substr($result, 0 , 14) === 'Could not ping') {
                echo '.';
                break;
            } elseif ($transport == 'tcp') {
                // tried both udp and tcp without success
                $stats['failed']++;
                echo '-';
            }
        }
    }
    echo PHP_EOL;
}

$opts  = getopt('r:d::l::h::');
$stats = array('count'=> 0, 'known'=>0, 'added'=>0, 'failed'=>0);
$start = false;
$debug = false;
$quiet = 1;
$net   = false;

if (isset($opts['h']) || (empty($opts) && (!isset($config['nets']) || empty($config['nets'])))) {
    echo 'Usage: '.$argv[0].' -r <CIDR_Range> [-d] [-l] [-h]'.PHP_EOL;
    echo '  -r CIDR_Range     CIDR noted IP-Range to scan'.PHP_EOL;
    echo '                    This argument is only requied if $config[\'nets\'] is not set'.PHP_EOL;
    echo '                    Example: 192.168.0.0/24'.PHP_EOL;
    echo '  -d                Enable Debug'.PHP_EOL;
    echo '  -l                Show Legend'.PHP_EOL;
    echo '  -h                Print this text'.PHP_EOL;
    exit(0);
}
if (isset($opts['d'])) {
    $debug = true;
    $quiet = 0;
}
if (isset($opts['l'])) {
    echo '   * = Known Device;   . = Unpingable Device;   + = Added Device;   - = Failed To Add Device;'.PHP_EOL;
}
if (isset($opts['r'])) {
    $net = Net_IPv4::parseAddress($opts['r']);
    if (ip2long($net->network) !== false) {
        perform_snmp_scan($net);
        echo 'Scanned '.$stats['count'].' IPs, Already known '.$stats['known'].' Devices, Added '.$stats['added'].' Devices, Failed to add '.$stats['failed'].' Devices.'.PHP_EOL;
        echo 'Runtime: '.(microtime(true)-$ts).' secs'.PHP_EOL;
    } else {
        echo 'Could not interpret supplied CIDR noted IP-Range: '.$opts['r'].PHP_EOL;
        exit(2);
    }
} elseif (isset($config['nets']) && !empty($config['nets'])) {
    if (!is_array($config['nets'])) {
        $config['nets'] = array( $config['nets'] );
    }
    foreach( $config['nets'] as $subnet ) {
        $net = Net_IPv4::parseAddress($subnet);
        perform_snmp_scan($net);
    }
    echo 'Scanned '.$stats['count'].' IPs, Already know '.$stats['known'].' Devices, Added '.$stats['added'].' Devices, Failed to add '.$stats['failed'].' Devices.'.PHP_EOL;
    echo 'Runtime: '.(microtime(true)-$ts).' secs'.PHP_EOL;
} else {
    echo 'Please either add a range argument with \'-r <CIDR_RANGE>\' or define $config[\'nets\'] in your config.php'.PHP_EOL;
    exit(2);
}

