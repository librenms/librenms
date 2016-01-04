#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

chdir(dirname($argv[0]));

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

$options = getopt('g:f::');

if (isset($options['g']) && $options['g'] >= 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $poller_group = $options['g'];
}
else if ($config['distributed_poller'] === true) {
    $poller_group = $config['distributed_poller_group'];
}

if (isset($options['f']) && $options['f'] == 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $force_add = 1;
}

if (!empty($argv[1])) {
    $host      = strtolower($argv[1]);
    $community = $argv[2];
    $snmpver   = strtolower($argv[3]);

    $port      = 161;
    $transport = 'udp';

    if ($snmpver === 'v3') {
        $seclevel = $community;

        // These values are the same as in defaults.inc.php
        $v3 = array(
            'authlevel'  => 'noAuthNoPriv',
            'authname'   => 'root',
            'authpass'   => '',
            'authalgo'   => 'MD5',
            'cryptopass' => '',
            'cryptoalgo' => 'AES',
        );

        if ($seclevel === 'nanp' or $seclevel === 'any' or $seclevel === 'noAuthNoPriv') {
            $v3['authlevel'] = 'noAuthNoPriv';
            $v3args          = array_slice($argv, 4);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                }
                else if (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/', $arg)) {
                    $transport = $arg;
                }
                else {
                    // should add a sanity check of chars allowed in user
                    $user = $arg;
                }
            }

            if ($seclevel === 'nanp') {
                array_push($config['snmp']['v3'], $v3);
            }

            $device_id = addHost($host, $snmpver, $port, $transport, 0, $poller_group, $force_add);
        }
        else if ($seclevel === 'anp' or $seclevel === 'authNoPriv') {
            $v3['authlevel'] = 'authNoPriv';
            $v3args          = array_slice($argv, 4);
            $v3['authname']  = array_shift($v3args);
            $v3['authpass']  = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                }
                else if (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/i', $arg)) {
                    $transport = $arg;
                }
                else if (preg_match('/^(sha|md5)$/i', $arg)) {
                    $v3['authalgo'] = $arg;
                }
                else {
                    echo 'Invalid argument: '.$arg."\n";
                    return;
                }
            }

            array_push($config['snmp']['v3'], $v3);
            $device_id = addHost($host, $snmpver, $port, $transport, 0, $poller_group, $force_add);
        }
        else if ($seclevel === 'ap' or $seclevel === 'authPriv') {
            $v3['authlevel']  = 'authPriv';
            $v3args           = array_slice($argv, 4);
            $v3['authname']   = array_shift($v3args);
            $v3['authpass']   = array_shift($v3args);
            $v3['cryptopass'] = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                }
                else if (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/i', $arg)) {
                    $transport = $arg;
                }
                else if (preg_match('/^(sha|md5)$/i', $arg)) {
                    $v3['authalgo'] = $arg;
                }
                else if (preg_match('/^(aes|des)$/i', $arg)) {
                    $v3['cryptoalgo'] = $arg;
                }
                else {
                    echo 'Invalid argument: '.$arg."\n";
                    return;
                }
            }//end while

            array_push($config['snmp']['v3'], $v3);
            $device_id = addHost($host, $snmpver, $port, $transport, 0, $poller_group, $force_add);
        }
        else {
            // Error or do nothing ?
        }//end if
    }
    else {
        $v2args = array_slice($argv, 2);

        while ($arg = array_shift($v2args)) {
            // parse all remaining args
            if (is_numeric($arg)) {
                $port = $arg;
            }
            else if (preg_match('/('.implode('|', $config['snmp']['transports']).')/i', $arg)) {
                $transport = $arg;
            }
            else if (preg_match('/^(v1|v2c)$/i', $arg)) {
                $snmpver = $arg;
            }
        }

        if ($community) {
            $config['snmp']['community'] = array($community);
        }

        $device_id = addHost($host, $snmpver, $port, $transport, 0, $poller_group, $force_add);
    }//end if

    if ($snmpver) {
        $snmpversions[] = $snmpver;
    }
    else {
        $snmpversions = array(
            'v2c',
            'v3',
            'v1',
        );
    }

    while (!$device_id && count($snmpversions)) {
        $snmpver   = array_shift($snmpversions);
        $device_id = addHost($host, $snmpver, $port, $transport, 0, $poller_group, $force_add);
    }

    if ($device_id) {
        $device = device_by_id_cache($device_id);
        echo 'Added device '.$device['hostname'].' ('.$device_id.")\n";
        exit;
    }
}//end if

print $console_color->convert(
    "\n".$config['project_name_version'].' Add Host Tool

    Usage (SNMPv1/2c): ./addhost.php [-g <poller group>] [-f] <%Whostname%n> [community] [v1|v2c] [port] ['.implode('|', $config['snmp']['transports']).']
    Usage (SNMPv3)   :  Config Defaults : ./addhost.php [-g <poller group>] [-f]<%Whostname%n> any v3 [user] [port] ['.implode('|', $config['snmp']['transports']).']
    No Auth, No Priv : ./addhost.php [-g <poller group>] [-f]<%Whostname%n> nanp v3 [user] [port] ['.implode('|', $config['snmp']['transports']).']
       Auth, No Priv : ./addhost.php [-g <poller group>] [-f]<%Whostname%n> anp v3 <user> <password> [md5|sha] [port] ['.implode('|', $config['snmp']['transports']).']
       Auth,    Priv : ./addhost.php [-g <poller group>] [-f]<%Whostname%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|dsa] [port] ['.implode('|', $config['snmp']['transports']).']

        -g <poller group> allows you to add a device to be pinned to a specific poller when using distributed polling. X can be any number associated with a poller group
        -f forces the device to be added by skipping the icmp and snmp check against the host.

    %rRemember to run discovery for the host afterwards.%n
'
);
