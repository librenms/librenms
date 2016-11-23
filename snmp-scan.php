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

use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\HostUnreachablePingException;

chdir(__DIR__); // cwd to the directory containing this script

$ts = microtime(true);

$init_modules = array('discovery');
require __DIR__ . '/includes/init.php';

if ($config['autodiscovery']['snmpscan'] === false) {
    echo 'SNMP-Scan disabled.'.PHP_EOL;
    exit(2);
}

function perform_snmp_scan($net, $force_network, $force_broadcast)
{
    global $stats, $config, $debug, $vdebug;
    echo 'Range: '.$net->network.'/'.$net->bitmask.PHP_EOL;
    $config['snmp']['timeout'] = 1;
    $config['snmp']['retries'] = 0;
    $config['fping_options']['retries']  = 0;
    $start = ip2long($net->network);
    $end   = ip2long($net->broadcast)-1;

    if ($force_network === true) { //Force-scan network address
        d_echo("Forcing network address scan".PHP_EOL);
        $start = $start-1;
    }

    if ($force_broadcast === true) { //Force-scan broadcast address
        d_echo("Forcing broadcast address scan".PHP_EOL);
        $end = $end+1;
    }

    if ($net->bitmask === "31") { //Handle RFC3021 /31 prefixes
        $start = ip2long($net->network)-1;
        $end   = ip2long($net->broadcast);
        d_echo("RFC3021 network, hosts ".long2ip($start+1)." and ".long2ip($end).PHP_EOL.PHP_EOL);
    } elseif ($net->bitmask === "32") { //Handle single-host /32 prefixes
        $start = ip2long($net->network)-1;
        $end   = $start+1;
        d_echo("RFC3021 network, hosts ".long2ip($start+1)." and ".long2ip($end).PHP_EOL.PHP_EOL);
    } else {
        d_echo("Network:   ".($net->network).PHP_EOL);
        d_echo("Broadcast: ".($net->broadcast).PHP_EOL.PHP_EOL);
    }

    while ($start++ < $end) {
        $stats['count']++;
        $host = long2ip($start);

        if ($vdebug) {
            echo "Scanning: ".$host.PHP_EOL;
        }

        if (match_network($config['autodiscovery']['nets-exclude'], $host)) {
            if ($vdebug) {
                echo "Excluded by config.php".PHP_EOL.PHP_EOL;
            } else {
                echo '|';
            }
            continue;
        }
        $test = isPingable($host);
        if ($test['result'] === false) {
            if ($vdebug) {
                echo "Unpingable Device".PHP_EOL.PHP_EOL;
            } else {
                echo '.';
            }
            continue;
        }
        if (ip_exists($host)) {
            $stats['known']++;
            if ($vdebug) {
                echo "Known Device".PHP_EOL;
            } else {
                echo '*';
            }
            continue;
        }
        foreach (array('udp','tcp') as $transport) {
            try {
                addHost(gethostbyaddr($host), '', $config['snmp']['port'], $transport, $config['distributed_poller_group']);
                $stats['added']++;
                if ($vdebug) {
                    echo "Added Device".PHP_EOL.PHP_EOL;
                } else {
                    echo '+';
                }
                break;
            } catch (HostExistsException $e) {
                $stats['known']++;
                if ($vdebug) {
                    echo "Known Device".PHP_EOL.PHP_EOL;
                } else {
                    echo '*';
                }
                break;
            } catch (HostUnreachablePingException $e) {
                if ($vdebug) {
                    echo "Unpingable Device".PHP_EOL.PHP_EOL;
                } else {
                    echo '.';
                }
                break;
            } catch (HostUnreachableException $e) {
                if ($debug) {
                    print_error($e->getMessage() . " over $transport");
                    foreach ($e->getReasons() as $reason) {
                        echo "  $reason".PHP_EOL;
                    }
                }
                if ($transport === 'tcp') {
                    // tried both udp and tcp without success
                    $stats['failed']++;
                    if ($vdebug) {
                        echo "Failed to Add Device".PHP_EOL.PHP_EOL;
                    } else {
                        echo '-';
                    }
                }
            }
        }
    }
    echo PHP_EOL;
}

$opts  = getopt('r:d::v::n::b::l::h::');
$stats = array('count'=> 0, 'known'=>0, 'added'=>0, 'failed'=>0);
$start = false;
$debug = false;
$quiet = 1;
$net   = false;

if (isset($opts['h']) || (empty($opts) && (!isset($config['nets']) || empty($config['nets'])))) {
    echo 'Usage: '.$argv[0].' -r <CIDR_Range> [-d] [-l] [-h]'.PHP_EOL;
    echo '  -r CIDR_Range     CIDR noted IP-Range to scan'.PHP_EOL;
    echo '                    This argument is only required if $config[\'nets\'] is not set'.PHP_EOL;
    echo '                    Example: 192.168.0.0/24'.PHP_EOL;
    echo '                    Example: 192.168.0.0/31 will be treated as an RFC3021 p-t-p network'.PHP_EOL;
    echo '                                            with two addresses, 192.168.0.0 and 192.168.0.1'.PHP_EOL;
    echo '                    Example: 192.168.0.1/32 will be treated as a single host address'.PHP_EOL;
    echo '  -n                Force scan of network address'.PHP_EOL;
    echo '  -b                Force scan of broadcast address'.PHP_EOL;
    echo '  -d                Enable Debug'.PHP_EOL;
    echo '  -v                Enable verbose Debug'.PHP_EOL;
    echo '  -l                Show Legend'.PHP_EOL;
    echo '  -h                Print this text'.PHP_EOL;
    exit(0);
}
if (isset($opts['d']) || isset($opts['v'])) {
    if (isset($opts['v'])) {
        $vdebug = true;
    }
    $debug = true;
}
if (isset($opts['l'])) {
    echo '   * = Known Device;   . = Unpingable Device;   + = Added Device;   - = Failed To Add Device;  | = Excluded by config.php'.PHP_EOL;
}

if (isset($opts['n'])) {
    $force_network = true;
}

if (isset($opts['b'])) {
    $force_broadcast = true;
}

if (isset($opts['r'])) {
    $net = Net_IPv4::parseAddress($opts['r']);
    if (ip2long($net->network) !== false) {
        perform_snmp_scan($net, $force_network, $force_broadcast);
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
    foreach ($config['nets'] as $subnet) {
        $net = Net_IPv4::parseAddress($subnet);
        perform_snmp_scan($net, $force_network, $force_broadcast);
    }
    echo 'Scanned '.$stats['count'].' IPs, Already know '.$stats['known'].' Devices, Added '.$stats['added'].' Devices, Failed to add '.$stats['failed'].' Devices.'.PHP_EOL;
    echo 'Runtime: '.(microtime(true)-$ts).' secs'.PHP_EOL;
} else {
    echo 'Please either add a range argument with \'-r <CIDR_RANGE>\' or define $config[\'nets\'] in your config.php'.PHP_EOL;
    exit(2);
}
