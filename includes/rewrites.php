<?php

use App\Facades\LibrenmsConfig;

function rewrite_entity_descr($descr)
{
    if (is_null($descr)) {
        return '';
    }
    $descr = str_replace('Distributed Forwarding Card', 'DFC', $descr);
    $descr = preg_replace('/7600 Series SPA Interface Processor-/', '7600 SIP-', $descr);
    $descr = preg_replace('/Rev\.\ [0-9\.]+\ /', '', $descr);
    $descr = preg_replace('/12000 Series Performance Route Processor/', '12000 PRP', $descr);
    $descr = preg_replace('/^12000/', '', $descr);
    $descr = preg_replace('/Gigabit Ethernet/', 'GigE', $descr);
    $descr = preg_replace('/^ASR1000\ /', '', $descr);
    $descr = str_replace('Routing Processor', 'RP', $descr);
    $descr = str_replace('Route Processor', 'RP', $descr);
    $descr = str_replace('Switching Processor', 'SP', $descr);
    $descr = str_replace('Sub-Module', 'Module ', $descr);
    $descr = str_replace('DFC Card', 'DFC', $descr);
    $descr = str_replace('Centralized Forwarding Card', 'CFC', $descr);
    $descr = str_replace('Power Supply Module', 'PSU ', $descr);
    $descr = str_replace('/Voltage Sensor/', 'Voltage', $descr);
    $descr = str_replace('Sensor', '', $descr);
    $descr = str_replace('PMOD', 'PSU', $descr);
    $descr = preg_replace('/^temperatures /', '', $descr);
    $descr = preg_replace('/^voltages /', '', $descr);
    $descr = str_replace('PowerSupply', 'PSU ', $descr);

    return trim($descr);
}

/**
 * Clean port values for html display
 * Add label to the port array (usually one of ifAlias, ifName, ifDescr)
 *
 * @param  array  $interface
 * @param  null|array  $device
 * @return mixed
 */
function cleanPort($interface, $device = null)
{
    if (! $interface) {
        return $interface;
    }

    $interface['ifAlias'] = htmlentities($interface['ifAlias'] ?? '');
    $interface['ifName'] = htmlentities($interface['ifName'] ?? '');
    $interface['ifDescr'] = htmlentities($interface['ifDescr'] ?? '');

    if (! $device) {
        $device = device_by_id_cache($interface['device_id']);
    }

    // set "label"
    $interface['label'] = $interface['ifDescr'];
    if (isset($device['os'])) {
        $os = strtolower($device['os']);
        if (LibrenmsConfig::get("os.$os.ifname")) {
            $interface['label'] = $interface['ifName'];

            if ($interface['ifName'] == '') {
                $interface['label'] = $interface['ifDescr'];
            }
        } elseif (LibrenmsConfig::get("os.$os.ifalias")) {
            $interface['label'] = $interface['ifAlias'];
        } elseif (LibrenmsConfig::get("os.$os.ifindex")) {
            $interface['label'] = $interface['label'] . ' ' . $interface['ifIndex'];
        }

        if ($os == 'speedtouch') {
            [$interface['label']] = explode('thomson', $interface['label']);
        }
    }

    if (is_array(LibrenmsConfig::get('rewrite_if'))) {
        foreach (LibrenmsConfig::get('rewrite_if') as $src => $val) {
            if (stristr((string) $interface['label'], (string) $src)) {
                $interface['label'] = $val;
            }
        }
    }

    if (is_array(LibrenmsConfig::get('rewrite_if_regexp'))) {
        foreach (LibrenmsConfig::get('rewrite_if_regexp') as $reg => $val) {
            if (preg_match($reg . 'i', (string) $interface['label'])) {
                $interface['label'] = preg_replace($reg . 'i', $val, (string) $interface['label']);
            }
        }
    }

    return $interface;
}

function short_hrDeviceDescr($dev)
{
    $rewrite_hrDevice = [
        'GenuineIntel:' => '',
        'AuthenticAMD:' => '',
        'Intel(R)' => '',
        'CPU' => '',
        '(R)' => '',
        '  ' => ' ',
    ];

    $dev = str_replace(array_keys($rewrite_hrDevice), array_values($rewrite_hrDevice), $dev);
    $dev = preg_replace('/\ +/', ' ', $dev);
    $dev = trim($dev);

    return $dev;
}

/**
 * @param  $state
 * @return int|void
 */
function apc_relay_state($state)
{
    switch ($state) {
        case 'immediateCloseEMS':
        case 'immediateOnEMS':
            return 1;
            break;
        case 'immediateOpenEMS':
        case 'immediateOffEMS':
            return 2;
            break;
    }
}
