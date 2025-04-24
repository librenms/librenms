#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\HostUnreachableException;

$init_modules = [];
require __DIR__ . '/includes/init.php';

c_echo('%RWarning: addhost.php is deprecated!%n Use %9lnms device:add%n instead.' . PHP_EOL . PHP_EOL);

$options = getopt('Pbg:p:f::');

$device = new Device;

if (isset($options['g']) && $options['g'] >= 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $device->poller_group = $options['g'];
}

if (isset($options['f']) && $options['f'] == 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $force_add = true;
} else {
    $force_add = false;
}

$valid_assoc_modes = PortAssociationMode::getModes();
if (isset($options['p'])) {
    $device->port_association_mode = $options['p'];
    if (! in_array($device->port_association_mode, $valid_assoc_modes)) {
        echo "Invalid port association mode '" . $device->port_association_mode . "'\n";
        echo 'Valid modes: ' . join(', ', $valid_assoc_modes) . "\n";
        exit(1);
    }

    $cmd = array_shift($argv);
    array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

if (isset($options['P'])) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

if (isset($options['b'])) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

$transports_regex = implode('|', Config::get('snmp.transports'));
if (! empty($argv[1])) {
    $device->hostname = strtolower($argv[1]);
    $device->snmpver = strtolower($argv[3]);

    if (isset($options['P'])) {
        $device->snmp_disable = 1;
        $device->os = $argv[2] ?: 'ping';
        $device->hardware = $argv[3] ?: '';
    } elseif ($device->snmpver === 'v3') {
        $seclevel = $argv[2];

        // v3
        if ($seclevel === 'nanp' or $seclevel === 'any' or $seclevel === 'noAuthNoPriv') {
            $device->authlevel = 'noAuthNoPriv';
            $v3args = array_slice($argv, 4);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $device->port = $arg;
                } elseif (preg_match('/^(' . $transports_regex . ')$/', $arg)) {
                    $device->transport = $arg;
                } else {
                    // should add a sanity check of chars allowed in user
                    $device->authname = $arg;
                }
            }
        } elseif ($seclevel === 'anp' or $seclevel === 'authNoPriv') {
            $device->authlevel = 'authNoPriv';
            $v3args = array_slice($argv, 4);
            $device->authname = array_shift($v3args);
            $device->authpass = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $device->port = $arg;
                } elseif (preg_match('/^(' . $transports_regex . ')$/i', $arg)) {
                    $device->transport = $arg;
                } elseif (preg_match('/^(sha|md5)$/i', $arg)) {
                    $device->authalgo = $arg;
                } else {
                    echo 'Invalid argument: ' . $arg . "\n";
                    exit(1);
                }
            }
        } elseif ($seclevel === 'ap' or $seclevel === 'authPriv') {
            $device->authlevel = 'authPriv';
            $v3args = array_slice($argv, 4);
            $device->authname = array_shift($v3args);
            $device->authpass = array_shift($v3args);
            $device->cryptopass = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $device->port = $arg;
                } elseif (preg_match('/^(' . $transports_regex . ')$/i', $arg)) {
                    $device->transport = $arg;
                } elseif (preg_match('/^(sha|md5)$/i', $arg)) {
                    $device->authalgo = $arg;
                } elseif (preg_match('/^(aes|des)$/i', $arg)) {
                    $device->cryptoalgo = $arg;
                } else {
                    echo 'Invalid argument: ' . $arg . "\n";
                    exit(1);
                }
            }//end while
        }
    } else {
        // v2c or v1
        $v2args = array_slice($argv, 2);
        $device->community = $argv[2];

        while ($arg = array_shift($v2args)) {
            // parse all remaining args
            if (is_numeric($arg)) {
                $device->port = $arg;
            } elseif (preg_match('/(' . $transports_regex . ')/i', $arg)) {
                $device->transport = $arg;
            } elseif (preg_match('/^(v1|v2c)$/i', $arg)) {
                $device->snmpver = $arg;
            }
        }
    }//end if

    try {
        $result = (new ValidateDeviceAndCreate($device, $force_add, isset($options['b'])))->execute();

        echo "Added device $device->hostname ($device->device_id)\n";
        exit(0);
    } catch (HostUnreachableException $e) {
        print_error($e->getMessage());
        foreach ($e->getReasons() as $reason) {
            echo "  $reason\n";
        }
        exit(2);
    } catch (Exception $e) {
        print_error("$e");
        exit(3);
    }
} else {
    c_echo(
        "\n" . Config::get('project_name') . ' Add Host Tool

    Usage (SNMPv1/2c)    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname or IP%n> [community] [v1|v2c] [port] [' . $transports_regex . ']
    Usage (SNMPv3)       :
        Config Defaults  : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname or IP%n> any v3 [user] [port] [' . $transports_regex . ']
        No Auth, No Priv : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname or IP%n> nanp v3 [user] [port] [' . $transports_regex . ']
        Auth, No Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname or IP%n> anp v3 <user> <password> [md5|sha] [port] [' . $transports_regex . ']
        Auth,    Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname or IP%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|des] [port] [' . $transports_regex . ']
    Usage (ICMP only)    : ./addhost.php [-g <poller group>] [-f] -P <%Whostname or IP%n> [os] [hardware]

    -g <poller group> allows you to add a device to be pinned to a specific poller when using distributed polling. X can be any number associated with a poller group
    -f forces the device to be added by skipping the icmp and snmp check against the host.
    -p <port assoc mode> allow you to set a port association mode for this device. By default ports are associated by \'ifIndex\'.
        For Linux/Unix based devices \'ifName\' or \'ifDescr\' might be useful for a stable iface mapping.
        The default for this installation is \'' . Config::get('default_port_association_mode') . '\'
        Valid port assoc modes are: ' . join(', ', $valid_assoc_modes) . '
    -b Add the host with SNMP if it replies to it, otherwise only ICMP.
    -P Add the host with only ICMP, no SNMP or OS discovery.

    %rRemember to run discovery for the host afterwards.%n
'
    );
    exit(1);
}
