<?php

use App\Facades\LibrenmsConfig;

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
