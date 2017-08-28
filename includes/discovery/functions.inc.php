<?php

/*
 * LibreNMS Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv6;

function discover_new_device($hostname, $device = '', $method = '', $interface = '')
{
    global $config;

    if (!empty($config['mydomain'])) {
        $full_host = rtrim($hostname, '.') . '.' . $config['mydomain'];
        if (isDomainResolves($full_host)) {
            $hostname = $full_host;
        }
    }

    d_echo("discovering $hostname\n");

    $ip = gethostbyname($hostname);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
        // $ip isn't a valid IP so it must be a name.
        if ($ip == $hostname) {
            d_echo("name lookup of $hostname failed\n");
            log_event("$method discovery of " . $hostname . " failed - Check name lookup", $device['device_id'], 'discovery', 5);

            return false;
        }
    } elseif (filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === true || filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === true) {
        // gethostbyname returned a valid $ip, was $dst_host an IP?
        if ($config['discovery_by_ip'] === false) {
            d_echo('Discovery by IP disabled, skipping ' . $hostname);
            log_event("$method discovery of " . $hostname . " failed - Discovery by IP disabled", $device['device_id'], 'discovery', 4);

            return false;
        }
    }

    d_echo("ip lookup result: $ip\n");

    $hostname = rtrim($hostname, '.');
    // remove trailing dot
    if (match_network($config['autodiscovery']['nets-exclude'], $ip)) {
        d_echo("$ip in an excluded network - skipping\n");

        return false;
    }

    if (match_network($config['nets'], $ip)) {
        try {
            $remote_device_id = addHost($hostname, '', '161', 'udp', $config['distributed_poller_group']);
            $remote_device = device_by_id_cache($remote_device_id, 1);
            echo '+[' . $remote_device['hostname'] . '(' . $remote_device['device_id'] . ')]';
            discover_device($remote_device);
            device_by_id_cache($remote_device_id, 1);
            if ($remote_device_id && is_array($device) && !empty($method)) {
                $extra_log = '';
                $int = cleanPort($interface);
                if (is_array($int)) {
                    $extra_log = ' (port ' . $int['label'] . ') ';
                }

                log_event('Device ' . $remote_device['hostname'] . " ($ip) $extra_log autodiscovered through $method on " . $device['hostname'], $remote_device_id, 'discovery', 1);
            } else {
                log_event("$method discovery of " . $remote_device['hostname'] . " ($ip) failed - Check ping and SNMP access", $device['device_id'], 'discovery', 5);
            }

            return $remote_device_id;
        } catch (HostExistsException $e) {
            // already have this device
        } catch (Exception $e) {
            log_event("$method discovery of " . $hostname . " ($ip) failed - " . $e->getMessage(), $device['device_id'], 'discovery', 5);
        }
    } else {
        d_echo("$ip not in a matched network - skipping\n");
    }//end if
}
//end discover_new_device()

/**
 * @param $device
 */
function load_discovery(&$device)
{
    global $config;
    $yaml_discovery = $config['install_dir'] . '/includes/definitions/discovery/' . $device['os'] . '.yaml';
    if (file_exists($yaml_discovery)) {
        $device['dynamic_discovery'] = Symfony\Component\Yaml\Yaml::parse(
            file_get_contents($yaml_discovery)
        );
    }
    unset($yaml_discovery);
}

function discover_device(&$device, $options = null)
{
    global $config, $valid;

    $valid = array();
    // Reset $valid array
    $attribs = get_dev_attribs($device['device_id']);
    $device['attribs'] = $attribs;
    $device['snmp_max_repeaters'] = $attribs['snmp_max_repeaters'];

    $device_start = microtime(true);
    // Start counting device poll time
    echo $device['hostname'] . ' ' . $device['device_id'] . ' ' . $device['os'] . ' ';

    $response = device_is_up($device, true);

    if ($response['status'] !== '1') {
        return;
    }

    if ($device['os'] == 'generic') {
        // verify if OS has changed from generic
        $device['os'] = getHostOS($device);

        if ($device['os'] != 'generic') {
            echo "\nDevice os was updated to " . $device['os'] . '!';
            dbUpdate(array('os' => $device['os']), 'devices', '`device_id` = ?', array($device['device_id']));
        }
    }

    load_os($device);
    load_discovery($device);
    if (is_array($config['os'][$device['os']]['register_mibs'])) {
        register_mibs($device, $config['os'][$device['os']]['register_mibs'], 'includes/discovery/os/' . $device['os'] . '.inc.php');
    }

    echo "\n";

    // If we've specified modules, use them, else walk the modules array
    $force_module = false;
    if ($options['m']) {
        $config['discovery_modules'] = array();
        foreach (explode(',', $options['m']) as $module) {
            if (is_file("includes/discovery/$module.inc.php")) {
                $config['discovery_modules'][$module] = 1;
                $force_module = true;
            }
        }
    }
    foreach ($config['discovery_modules'] as $module => $module_status) {
        $os_module_status = $config['os'][$device['os']]['discovery_modules'][$module];
        d_echo("Modules status: Global" . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
        d_echo("OS" . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
        d_echo("Device" . (isset($attribs['discover_' . $module]) ? ($attribs['discover_' . $module] ? '+ ' : '- ') : '  '));
        if ($force_module === true ||
            $attribs['discover_' . $module] ||
            ($os_module_status && !isset($attribs['discover_' . $module])) ||
            ($module_status && !isset($os_module_status) && !isset($attribs['discover_' . $module]))
        ) {
            $module_start = microtime(true);
            $start_memory = memory_get_usage();
            echo "\n#### Load disco module $module ####\n";
            include "includes/discovery/$module.inc.php";
            $module_time = microtime(true) - $module_start;
            $module_time = substr($module_time, 0, 5);
            $module_mem = (memory_get_usage() - $start_memory);
            printf("\n>> Runtime for discovery module '%s': %.4f seconds with %s bytes\n", $module, $module_time, $module_mem);
            echo "#### Unload disco module $module ####\n\n";
        } elseif (isset($attribs['discover_' . $module]) && $attribs['discover_' . $module] == '0') {
            echo "Module [ $module ] disabled on host.\n\n";
        } elseif (isset($os_module_status) && $os_module_status == '0') {
            echo "Module [ $module ] disabled on os.\n\n";
        } else {
            echo "Module [ $module ] disabled globally.\n\n";
        }
    }

    if (is_mib_poller_enabled($device)) {
        $devicemib = array($device['sysObjectID'] => 'all');
        register_mibs($device, $devicemib, "includes/discovery/functions.inc.php");
    }

    $device_time  = round(microtime(true) - $device_start, 3);

    dbUpdate(array('last_discovered' => array('NOW()'), 'last_discovered_timetaken' => $device_time), 'devices', '`device_id` = ?', array($device['device_id']));

    echo "Discovered in $device_time seconds\n";

    global $discovered_devices;

    echo "\n";
    $discovered_devices++;
}
//end discover_device()

// Discover sensors
function discover_sensor(&$valid, $class, $device, $oid, $index, $type, $descr, $divisor = 1, $multiplier = 1, $low_limit = null, $low_warn_limit = null, $warn_limit = null, $high_limit = null, $current = null, $poller_type = 'snmp', $entPhysicalIndex = null, $entPhysicalIndex_measured = null, $user_func = null, $entity_link_type = null, $entity_link_index = 0)
{
    $low_limit      = set_null($low_limit);
    $low_warn_limit = set_null($low_warn_limit);
    $warn_limit     = set_null($warn_limit);
    $high_limit     = set_null($high_limit);

    if (!is_numeric($divisor)) {
        $divisor  = 1;
    }

    d_echo("Discover sensor: $oid, $index, $type, $descr, $poller_type, $divisor, $multiplier, $entPhysicalIndex, $current\n");

    if (is_null($low_warn_limit) && !is_null($warn_limit)) {
        // Warn limits only make sense when we have both a high and a low limit
        $low_warn_limit = null;
        $warn_limit = null;
    } elseif (!is_null($warn_limit) && $low_warn_limit > $warn_limit) {
        // Fix high/low thresholds (i.e. on negative numbers)
        list($warn_limit, $low_warn_limit) = array($low_warn_limit, $warn_limit);
    }

    if (dbFetchCell('SELECT COUNT(sensor_id) FROM `sensors` WHERE `poller_type`= ? AND `sensor_class` = ? AND `device_id` = ? AND sensor_type = ? AND `sensor_index` = ?', array($poller_type, $class, $device['device_id'], $type, $index)) == '0') {
        if (is_null($high_limit)) {
            $high_limit = sensor_limit($class, $current);
        }

        if (is_null($low_limit)) {
            $low_limit = sensor_low_limit($class, $current);
        }

        if (!is_null($high_limit) && $low_limit > $high_limit) {
            // Fix high/low thresholds (i.e. on negative numbers)
            list($high_limit, $low_limit) = array($low_limit, $high_limit);
        }

        $insert = array(
            'poller_type' => $poller_type,
            'sensor_class' => $class,
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid,
            'sensor_index' => $index,
            'sensor_type' => $type,
            'sensor_descr' => $descr,
            'sensor_divisor' => $divisor,
            'sensor_multiplier' => $multiplier,
            'sensor_limit' => $high_limit,
            'sensor_limit_warn' => $warn_limit,
            'sensor_limit_low' => $low_limit,
            'sensor_limit_low_warn' => $low_warn_limit,
            'sensor_current' => $current,
            'entPhysicalIndex' => $entPhysicalIndex,
            'entPhysicalIndex_measured' => $entPhysicalIndex_measured,
            'user_func' => $user_func,
            'entity_link_type' => $entity_link_type,
            'entity_link_index' => $entity_link_index,
        );

        foreach ($insert as $key => $val_check) {
            if (!isset($val_check)) {
                unset($insert[$key]);
            }
        }

        $inserted = dbInsert($insert, 'sensors');

        d_echo("( $inserted inserted )\n");

        echo '+';
        log_event('Sensor Added: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr), $device, 'sensor', 3, $inserted);
    } else {
        $sensor_entry = dbFetchRow('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?', array($class, $device['device_id'], $type, $index));

        if (!isset($high_limit)) {
            if (!$sensor_entry['sensor_limit']) {
                // Calculate a reasonable limit
                $high_limit = sensor_limit($class, $current);
            } else {
                // Use existing limit
                $high_limit = $sensor_entry['sensor_limit'];
            }
        }

        if (!isset($low_limit)) {
            if (!$sensor_entry['sensor_limit_low']) {
                // Calculate a reasonable limit
                $low_limit = sensor_low_limit($class, $current);
            } else {
                // Use existing limit
                $low_limit = $sensor_entry['sensor_limit_low'];
            }
        }

        // Fix high/low thresholds (i.e. on negative numbers)
        if ($low_limit > $high_limit) {
            list($high_limit, $low_limit) = array($low_limit, $high_limit);
        }

        if ($high_limit != $sensor_entry['sensor_limit'] && $sensor_entry['sensor_custom'] == 'No') {
            $update = array('sensor_limit' => ($high_limit == null ? array('NULL') : $high_limit));
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
            d_echo("( $updated updated )\n");

            echo 'H';
            log_event('Sensor High Limit Updated: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr) . ' (' . $high_limit . ')', $device, 'sensor', 3, $sensor_id);
        }

        if ($sensor_entry['sensor_limit_low'] != $low_limit && $sensor_entry['sensor_custom'] == 'No') {
            $update = array('sensor_limit_low' => ($low_limit == null ? array('NULL') : $low_limit));
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
            d_echo("( $updated updated )\n");

            echo 'L';
            log_event('Sensor Low Limit Updated: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr) . ' (' . $low_limit . ')', $device, 'sensor', 3, $sensor_id);
        }

        if ($warn_limit != $sensor_entry['sensor_limit_warn'] && $sensor_entry['sensor_custom'] == 'No') {
            $update = array('sensor_limit_warn' => ($warn_limit == null ? array('NULL') : $warn_limit));
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
            d_echo("( $updated updated )\n");

            echo 'WH';
            log_event('Sensor Warn High Limit Updated: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr) . ' (' . $warn_limit . ')', $device, 'sensor', 3, $sensor_id);
        }

        if ($sensor_entry['sensor_limit_low_warn'] != $low_warn_limit && $sensor_entry['sensor_custom'] == 'No') {
            $update = array('sensor_limit_low_warn' => ($low_warn_limit == null ? array('NULL') : $low_warn_limit));
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
            d_echo("( $updated updated )\n");

            echo 'WL';
            log_event('Sensor Warn Low Limit Updated: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr) . ' (' . $low_warn_limit . ')', $device, 'sensor', 3, $sensor_id);
        }

        if ($oid == $sensor_entry['sensor_oid'] &&
            $descr == $sensor_entry['sensor_descr'] &&
            $multiplier == $sensor_entry['sensor_multiplier'] &&
            $divisor == $sensor_entry['sensor_divisor'] &&
            $entPhysicalIndex_measured == $sensor_entry['entPhysicalIndex_measured'] &&
            $entPhysicalIndex == $sensor_entry['entPhysicalIndex'] &&
            $user_func == $sensor_entry['user_func'] &&
            $entity_link_type == $sensor_entry['entity_link_type'] &&
            $entity_link_index == $sensor_entry['entity_link_index']

        ) {
            echo '.';
        } else {
            $update = array(
                'sensor_oid' => $oid,
                'sensor_descr' => $descr,
                'sensor_multiplier' => $multiplier,
                'sensor_divisor' => $divisor,
                'entPhysicalIndex' => $entPhysicalIndex,
                'entPhysicalIndex_measured' => $entPhysicalIndex_measured,
                'user_func' => $user_func,
                'entity_link_type' => $entity_link_type,
                'entity_link_index' => $entity_link_index,
            );
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
            echo 'U';
            log_event('Sensor Updated: ' . mres($class) . ' ' . mres($type) . ' ' . mres($index) . ' ' . mres($descr), $device, 'sensor', 3, $sensor_id);
            d_echo("( $updated updated )\n");
        }
    }//end if
    $valid[$class][$type][$index] = 1;
}

//end discover_sensor()

function sensor_low_limit($class, $current)
{
    $limit = null;

    switch ($class) {
        case 'temperature':
            $limit = ($current * 0.7);
            break;

        case 'voltage':
            if ($current < 0) {
                $limit = ($current * (1 + (sgn($current) * 0.15)));
            } else {
                $limit = ($current * (1 - (sgn($current) * 0.15)));
            }
            break;

        case 'humidity':
            $limit = '30';
            break;

        case 'frequency':
            $limit = ($current * 0.95);
            break;

        case 'current':
            $limit = null;
            break;

        case 'fanspeed':
            $limit = ($current * 0.80);
            break;

        case 'power':
            $limit = null;
            break;

        case 'signal':
            $limit = -80;
            break;
        case 'airflow':
        case 'dbm':
        case 'snr':
            $limit = ($current * 0.95);
            break;
    }//end switch

    return $limit;
}

//end sensor_low_limit()

function sensor_limit($class, $current)
{
    $limit = null;

    switch ($class) {
        case 'temperature':
            $limit = ($current * 1.60);
            break;

        case 'voltage':
            if ($current < 0) {
                $limit = ($current * (1 - (sgn($current) * 0.15)));
            } else {
                $limit = ($current * (1 + (sgn($current) * 0.15)));
            }
            break;

        case 'humidity':
            $limit = '70';
            break;

        case 'frequency':
            $limit = ($current * 1.05);
            break;

        case 'current':
            $limit = ($current * 1.50);
            break;

        case 'fanspeed':
            $limit = ($current * 1.80);
            break;

        case 'power':
            $limit = ($current * 1.50);
            break;

        case 'signal':
            $limit = -30;
            break;

        case 'load':
            $limit = 80;
            break;
        case 'airflow':
        case 'dbm':
        case 'snr':
            $limit = ($current * 1.05);
            break;
    }//end switch

    return $limit;
}

//end sensor_limit()

function check_valid_sensors($device, $class, $valid, $poller_type = 'snmp')
{
    $entries = dbFetchRows('SELECT * FROM sensors AS S, devices AS D WHERE S.sensor_class=? AND S.device_id = D.device_id AND D.device_id = ? AND S.poller_type = ?', array($class, $device['device_id'], $poller_type));

    if (count($entries)) {
        foreach ($entries as $entry) {
            $index = $entry['sensor_index'];
            $type = $entry['sensor_type'];
            $class = $entry['sensor_class'];
            d_echo($index . ' -> ' . $type . "\n");

            if (!$valid[$class][$type][$index]) {
                echo '-';
                if ($class == 'state') {
                    dbDelete('sensors_to_state_indexes', '`sensor_id` =  ?', array($entry['sensor_id']));
                }
                dbDelete('sensors', '`sensor_id` =  ?', array($entry['sensor_id']));
                log_event('Sensor Deleted: ' . $entry['sensor_class'] . ' ' . $entry['sensor_type'] . ' ' . $entry['sensor_index'] . ' ' . $entry['sensor_descr'], $device, 'sensor', 3, $sensor_id);
            }

            unset($oid);
            unset($type);
        }
    }
}

//end check_valid_sensors()

function discover_juniAtmVp(&$valid, $device, $port_id, $vp_id, $vp_descr)
{
    d_echo("Discover Juniper ATM VP: $port_id, $vp_id, $vp_descr\n");

    if (dbFetchCell('SELECT COUNT(*) FROM `juniAtmVp` WHERE `port_id` = ? AND `vp_id` = ?', array($port_id, $vp_id)) == '0') {
        $inserted = dbInsert(array('port_id' => $port_id, 'vp_id' => $vp_id, 'vp_descr' => $vp_descr), 'juniAtmVp');
        d_echo("( $inserted inserted )\n");

        // FIXME vv no $device!
        log_event('Juniper ATM VP Added: port ' . mres($port_id) . ' vp ' . mres($vp_id) . ' descr' . mres($vp_descr), $device, 'juniAtmVp', 3, $inserted);
    } else {
        echo '.';
    }

    $valid[$port_id][$vp_id] = 1;
}

//end discover_juniAtmVp()

function discover_link($local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $local_device_id, $remote_device_id)
{
    global $link_exists;

    d_echo("Discover link: $local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version\n");

    if (dbFetchCell(
        'SELECT COUNT(*) FROM `links` WHERE `remote_hostname` = ? AND `local_port_id` = ? AND `protocol` = ? AND `remote_port` = ?',
        array(
                $remote_hostname,
                $local_port_id,
                $protocol,
                $remote_port,
                    )
    ) == '0') {
        $insert_data = array(
            'local_port_id' => $local_port_id,
            'local_device_id' => $local_device_id,
            'protocol' => $protocol,
            'remote_hostname' => $remote_hostname,
            'remote_device_id' => $remote_device_id,
            'remote_port' => $remote_port,
            'remote_platform' => $remote_platform,
            'remote_version' => $remote_version,
        );

        if (!empty($remote_port_id)) {
            $insert_data['remote_port_id'] = $remote_port_id;
        }

        $inserted = dbInsert($insert_data, 'links');

        echo '+';
        d_echo("( $inserted inserted )");
    } else {
        $data = dbFetchRow('SELECT * FROM `links` WHERE `remote_hostname` = ? AND `local_port_id` = ? AND `protocol` = ? AND `remote_port` = ?', array($remote_hostname, $local_port_id, $protocol, $remote_port));
        if ($data['remote_port_id'] == $remote_port_id && $data['remote_platform'] == $remote_platform && $remote_version == $remote_version && $data['local_device_id'] > 0 && $data['remote_device_id'] > 0) {
            echo '.';
        } else {
            $update_data = array(
                'remote_platform' => $remote_platform,
                'remote_version' => $remote_version,
                'local_device_id' => $local_device_id,
                'remote_device_id' => $remote_device_id,
            );

            if (!empty($remote_port_id)) {
                $update_data['remote_port_id'] = $remote_port_id;
            }

            $updated = dbUpdate($update_data, 'links', '`id` = ?', array($data['id']));
            echo 'U';
            d_echo("( $updated updated )");
        }//end if
    }//end if
    $link_exists[$local_port_id][$remote_hostname][$remote_port] = 1;
}

//end discover_link()

function discover_storage(&$valid, $device, $index, $type, $mib, $descr, $size, $units, $used = null)
{
    d_echo("Discover Storage: $index, $type, $mib, $descr, $size, $units, $used\n");

    if ($descr && $size > '0') {
        $storage = dbFetchRow('SELECT * FROM `storage` WHERE `storage_index` = ? AND `device_id` = ? AND `storage_mib` = ?', array($index, $device['device_id'], $mib));
        if ($storage === false || !count($storage)) {
            $insert = dbInsert(
                array(
                    'device_id' => $device['device_id'],
                    'storage_descr' => $descr,
                    'storage_index' => $index,
                    'storage_mib' => $mib,
                    'storage_type' => $type,
                    'storage_units' => $units,
                    'storage_size' => $size,
                    'storage_used' => $used,
                ),
                'storage'
            );

            echo '+';
        } else {
            $updated = dbUpdate(array('storage_descr' => $descr, 'storage_type' => $type, 'storage_units' => $units, 'storage_size' => $size), 'storage', '`device_id` = ? AND `storage_index` = ? AND `storage_mib` = ?', array($device['device_id'], $index, $mib));
            if ($updated) {
                echo 'U';
            } else {
                echo '.';
            }
        }//end if

        $valid[$mib][$index] = 1;
    }//end if
}

//end discover_storage()

function discover_processor(&$valid, $device, $oid, $index, $type, $descr, $precision = '1', $current = null, $entPhysicalIndex = null, $hrDeviceIndex = null)
{
    d_echo("Discover Processor: $oid, $index, $type, $descr, $precision, $current, $entPhysicalIndex, $hrDeviceIndex\n");

    if ($descr) {
        $descr = trim(str_replace('"', '', $descr));
        if (dbFetchCell('SELECT COUNT(processor_id) FROM `processors` WHERE `processor_index` = ? AND `device_id` = ? AND `processor_type` = ?', array($index, $device['device_id'], $type)) == '0') {
            $insert_data = array(
                'device_id' => $device['device_id'],
                'processor_descr' => $descr,
                'processor_index' => $index,
                'processor_oid' => $oid,
                'processor_usage' => $current,
                'processor_type' => $type,
                'processor_precision' => $precision,
            );
            if (!empty($hrDeviceIndex)) {
                $insert_data['hrDeviceIndex'] = $hrDeviceIndex;
            }

            if (!empty($entPhysicalIndex)) {
                $insert_data['entPhysicalIndex'] = $entPhysicalIndex;
            }

            $inserted = dbInsert($insert_data, 'processors');
            echo '+';
            log_event('Processor added: type ' . mres($type) . ' index ' . mres($index) . ' descr ' . mres($descr), $device, 'processor', 3, $inserted);
        } else {
            echo '.';
            $update_data = array(
                'processor_descr' => $descr,
                'processor_oid' => $oid,
                'processor_usage' => $current,
                'processor_precision' => $precision,
            );
            dbUpdate($update_data, 'processors', '`device_id`=? AND `processor_index`=? AND `processor_type`=?', array($device['device_id'], $index, $type));
        }//end if
        $valid[$type][$index] = 1;
    }//end if
}

//end discover_processor()

function discover_mempool(&$valid, $device, $index, $type, $descr, $precision = '1', $entPhysicalIndex = null, $hrDeviceIndex = null)
{
    d_echo("Discover Mempool: $index, $type, $descr, $precision, $entPhysicalIndex, $hrDeviceIndex\n");

    // FIXME implement the mempool_perc, mempool_used, etc.
    if ($descr) {
        if (dbFetchCell('SELECT COUNT(mempool_id) FROM `mempools` WHERE `mempool_index` = ? AND `device_id` = ? AND `mempool_type` = ?', array($index, $device['device_id'], $type)) == '0') {
            $insert_data = array(
                'device_id' => $device['device_id'],
                'mempool_descr' => $descr,
                'mempool_index' => $index,
                'mempool_type' => $type,
                'mempool_precision' => $precision,
                'mempool_perc' => 0,
                'mempool_used' => 0,
                'mempool_free' => 0,
                'mempool_total' => 0,
            );

            if (is_numeric($entPhysicalIndex)) {
                $insert_data['entPhysicalIndex'] = $entPhysicalIndex;
            }

            if (is_numeric($hrDeviceIndex)) {
                $insert_data['hrDeviceIndex'] = $hrDeviceIndex;
            }

            $inserted = dbInsert($insert_data, 'mempools');
            echo '+';
            log_event('Memory pool added: type ' . mres($type) . ' index ' . mres($index) . ' descr ' . mres($descr), $device, 'mempool', 3, $inserted);
        } else {
            echo '.';
            $update_data = array(
                'mempool_descr' => $descr,
            );

            if (!empty($entPhysicalIndex)) {
                $update_data['entPhysicalIndex'] = $entPhysicalIndex;
            }

            if (!empty($hrDeviceIndex)) {
                $update_data['hrDeviceIndex'] = $hrDeviceIndex;
            }

            dbUpdate($update_data, 'mempools', 'device_id=? AND mempool_index=? AND mempool_type=?', array($device['device_id'], $index, $type));
        }//end if
        $valid[$type][$index] = 1;
    }//end if
}

//end discover_mempool()

function discover_toner(&$valid, $device, $oid, $index, $type, $descr, $capacity_oid = null, $capacity = null, $current = null)
{
    d_echo("Discover Toner: $oid, $index, $type, $descr, $capacity_oid, $capacity, $current\n");

    if (dbFetchCell('SELECT COUNT(toner_id) FROM `toner` WHERE device_id = ? AND toner_type = ? AND `toner_index` = ? AND `toner_oid` =?', array($device['device_id'], $type, $index, $oid)) == '0') {
        $inserted = dbInsert(array('device_id' => $device['device_id'], 'toner_oid' => $oid, 'toner_capacity_oid' => $capacity_oid, 'toner_index' => $index, 'toner_type' => $type, 'toner_descr' => $descr, 'toner_capacity' => $capacity, 'toner_current' => $current), 'toner');
        echo '+';
        log_event('Toner added: type ' . mres($type) . ' index ' . mres($index) . ' descr ' . mres($descr), $device, 'toner', 3, $inserted);
    } else {
        $toner_entry = dbFetchRow('SELECT * FROM `toner` WHERE `device_id` = ? AND `toner_type` = ? AND `toner_index` =?', array($device['device_id'], $type, $index));
        if ($oid == $toner_entry['toner_oid'] && $descr == $toner_entry['toner_descr'] && $capacity == $toner_entry['toner_capacity'] && $capacity_oid == $toner_entry['toner_capacity_oid']) {
            echo '.';
        } else {
            dbUpdate(array('toner_descr' => $descr, 'toner_oid' => $oid, 'toner_capacity_oid' => $capacity_oid, 'toner_capacity' => $capacity), 'toner', 'device_id=? AND toner_type=? AND `toner_index`=?', array($device['device_id'], $type, $index));
            echo 'U';
        }
    }

    $valid[$type][$oid] = 1;
}

//end discover_toner()

function discover_process_ipv6(&$valid, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin, $context_name = '')
{
    global $device;

    if (!IPv6::isValid($ipv6_address, true)) {
        // ignore link-locals (coming from IPV6-MIB)
        return;
    }

    $ipv6 = new IPv6($ipv6_address);
    $ipv6_network = $ipv6->getNetwork($ipv6_prefixlen);
    $ipv6_compressed = $ipv6->compressed();

    if (dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifIndex` = ?', array($device['device_id'], $ifIndex)) != '0' && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && $ipv6_compressed != '::1') {
        $port_id = dbFetchCell('SELECT port_id FROM `ports` WHERE device_id = ? AND ifIndex = ?', array($device['device_id'], $ifIndex));

        if (is_numeric($port_id)) {
            if (dbFetchCell('SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = ?', array($ipv6_network)) < '1') {
                dbInsert(array('ipv6_network' => $ipv6_network, 'context_name' => $context_name), 'ipv6_networks');
                echo 'N';
            } else {
                //Update Context
                dbUpdate(array('context_name' => $device['context_name']), 'ipv6_networks', '`ipv6_network` = ?', array($ipv6_network));
                echo 'n';
            }

            $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ? AND `context_name` = ?', array($ipv6_network, $context_name));

            if (dbFetchCell('SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ?', array($ipv6_address, $ipv6_prefixlen, $port_id)) == '0') {
                dbInsert(array(
                    'ipv6_address' => $ipv6_address,
                    'ipv6_compressed' => $ipv6_compressed,
                    'ipv6_prefixlen' => $ipv6_prefixlen,
                    'ipv6_origin' => $ipv6_origin,
                    'ipv6_network_id' => $ipv6_network_id,
                    'port_id' => $port_id,
                    'context_name' => $context_name
                ), 'ipv6_addresses');
                echo '+';
            } else {
                //Update Context
                dbUpdate(array('context_name' => $device['context_name']), 'ipv6_addresses', '`ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ?', array($ipv6_address, $ipv6_prefixlen, $port_id));
                echo '.';
            }

            $full_address = "$ipv6_address/$ipv6_prefixlen";
            $valid_address = $full_address . '-' . $port_id;
            $valid['ipv6'][$valid_address] = 1;
        }
    }//end if
}//end discover_process_ipv6()

/*
 * Check entity sensors to be excluded
 *
 * @param string value to check
 * @param array device
 *
 * @return bool true if sensor is valid
 *              false if sensor is invalid
*/
function check_entity_sensor($string, $device)
{
    global $config;
    $valid  = true;
    $string = strtolower($string);
    if (is_array($config['bad_entity_sensor_regex'])) {
        $fringe = $config['bad_entity_sensor_regex'];
        if (is_array($config['os'][$device['os']]['bad_entity_sensor_regex'])) {
            $fringe = array_merge($config['bad_entity_sensor_regex'], $config['os'][$device['os']]['bad_entity_sensor_regex']);
        }
        foreach ($fringe as $bad) {
            if (preg_match($bad . "i", $string)) {
                $valid = false;
                d_echo("Ignored entity sensor: $bad : $string");
            }
        }
    }
    return $valid;
}


/**
 * Helper function to improve readability
 * Can't use mib based polling, because the snmp implentation and mibs are terrible
 *
 * @param (device) array - device array
 * @param (sensor) array(id, oid, type, descr, descr_oid, min, max, divisor)
 */
function avtech_add_sensor($device, $sensor)
{
    global $valid;

    // set the id, must be unique
    if (isset($sensor['id'])) {
        $id = $sensor['id'];
    } else {
        d_echo('Error: No id set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor id: ' . $id . "\n");


    // set the sensor oid
    if ($sensor['oid']) {
        $oid = $sensor['oid'];
    } else {
        d_echo('Error: No oid set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor oid: ' . $oid . "\n");

    // get the sensor value
    $value = snmp_get($device, $oid, '-OvQ');
    // if the sensor doesn't exist abort
    if ($value === false || ($type == 'temperature' && $value == 0)) {
        //issue unfortunately some non-existant sensors return 0
        d_echo('Error: sensor returned no data, skipping' . "\n");
        return false;
    }
    d_echo('Sensor value: ' . $value . "\n");

    // get the type
    $type = $sensor['type'] ? $sensor['type'] : 'temperature';
    d_echo('Sensor type: ' . $type . "\n");

    $type_name = $device['os'];
    if ($type == 'switch') {
        // set up state sensor
        $type_name .= ucfirst($type);
        $type = 'state';
        $state_index_id = create_state_index($type_name);

        //Create State Translation
        if (isset($state_index_id)) {
            $states = array(
                 array($state_index_id,'Off',1,0,-1),
                 array($state_index_id,'On',1,1,0),
             );
            foreach ($states as $value) {
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],
                    'state_generic_value' => $value[4]
                );
                dbInsert($insert, 'state_translations');
            }
        }
    }

    // set the description
    if ($sensor['descr_oid']) {
        $descr = trim(snmp_get($device, $sensor['descr_oid'], '-OvQ'), '"');
    } elseif ($sensor['descr']) {
        $descr = $sensor['descr'];
    } else {
        d_echo('Error: No description set for this sensor' . "\n");
        return false;
    }
    d_echo('Sensor description: ' . $descr . "\n");

    // set divisor
    if ($sensor['divisor']) {
        $divisor = $sensor['divisor'];
    } elseif ($type == 'temperature') {
        $divisor = 100;
    } else {
        $divisor = 1;
    }
    d_echo('Sensor divisor: ' . $divisor . "\n");


    // set min for alarm
    if ($sensor['min_oid']) {
        $min = snmp_get($device, $sensor['min_oid'], '-OvQ') / $divisor;
    } else {
        $min = null;
    }
    d_echo('Sensor alarm min: ' . $min . "\n");

    // set max for alarm
    if ($sensor['max_oid']) {
        $max = snmp_get($device, $sensor['max_oid'], '-OvQ') / $divisor;
    } else {
        $max = null;
    }
    d_echo('Sensor alarm max: ' . $max . "\n");

    // add the sensor
    discover_sensor($valid['sensor'], $type, $device, $oid, $id, $type_name, $descr, $divisor, '1', $min, null, null, $max, $value/$divisor);

    if ($type == 'state') {
        create_sensor_to_state_index($device, $type_name, $id);
    }

    return true;
}


/**
 * Get the device divisor, account for device specific quirks
 * The default divisor is 10
 *
 * @param array $device device array
 * @param string $os_version firmware version poweralert quirks
 * @param string $sensor_type the type of this sensor
 * @param string $oid the OID of this sensor
 * @return int
 */
function get_device_divisor($device, $os_version, $sensor_type, $oid)
{
    if ($device['os'] == 'poweralert') {
        if ($sensor_type == 'current' || $sensor_type == 'frequency') {
            if (version_compare($os_version, '12.06.0068', '>=')) {
                return 10;
            } elseif (version_compare($os_version, '12.04.0055', '=')) {
                return 10;
            } elseif (version_compare($os_version, '12.04.0056', '>=')) {
                return 1;
            }
        } elseif ($sensor_type == 'load') {
            if (version_compare($os_version, '12.06.0064', '=')) {
                return 10;
            } else {
                return 1;
            }
        }
    } elseif ($device['os'] == 'huaweiups') {
        if ($sensor_type == 'frequency') {
            return 100;
        }
    } elseif ($device['os'] == 'hpe-rtups') {
        if ($sensor_type == 'voltage' && !starts_with($oid, '.1.3.6.1.2.1.33.1.2.5.') && !starts_with($oid, '.1.3.6.1.2.1.33.1.3.3.1.3')) {
            return 1;
        }
    }

    // UPS-MIB Defaults

    if ($sensor_type == 'load') {
        return 1;
    }

    if ($sensor_type == 'voltage' && !starts_with($oid, '.1.3.6.1.2.1.33.1.2.5.')) {
        return 1;
    }

    return 10;
}

/**
 * @param int $raw_capacity The value return from snmp
 * @return int normalized capacity value
 */
function get_toner_capacity($raw_capacity)
{
    // unknown or unrestricted capacity, assume 100
    if (empty($raw_capacity) || $raw_capacity < 0) {
        return 100;
    }
    return $raw_capacity;
}

/**
 * @param $descr
 * @return int
 */
function ignore_storage($descr)
{
    global $config;
    $deny = 0;
    foreach ($config['ignore_mount'] as $bi) {
        if ($bi == $descr) {
            $deny = 1;
            d_echo("$bi == $descr \n");
        }
    }

    foreach ($config['ignore_mount_string'] as $bi) {
        if (strpos($descr, $bi) !== false) {
            $deny = 1;
            d_echo("strpos: $descr, $bi \n");
        }
    }

    foreach ($config['ignore_mount_regexp'] as $bi) {
        if (preg_match($bi, $descr) > '0') {
            $deny = 1;
            d_echo("preg_match $bi, $descr \n");
        }
    }

    return $deny;
}

/**
 * @param $value
 * @param $data
 * @param $group
 * @return bool
 */
function can_skip_sensor($value, $data, $group)
{
    $skip_values = array_replace((array)$group['skip_values'], (array)$data['skip_values']);
    foreach ($skip_values as $skip_value) {
        if ($value == $skip_value) {
            return true;
        }
    }

    $skip_value_lt = array_replace((array)$group['skip_value_lt'], (array)$data['skip_value_lt']);
    foreach ($skip_value_lt as $skip_value) {
        if ($value < $skip_value) {
            return true;
        }
    }

    $skip_value_gt = array_reduce((array)$group['skip_value_gt'], (array)$data['skip_value_gt']);
    foreach ($skip_value_gt as $skip_value) {
        if ($value > $skip_value) {
            return true;
        }
    }

    return false;
}

/**
 * @param $valid
 * @param $device
 * @param $sensor_type
 * @param $pre_cache
 */
function discovery_process(&$valid, $device, $sensor_type, $pre_cache)
{
    if ($device['dynamic_discovery']['modules']['sensors'][$sensor_type]) {
        $sensor_options = array();
        if (isset($device['dynamic_discovery']['modules']['sensors'][$sensor_type]['options'])) {
            $sensor_options = $device['dynamic_discovery']['modules']['sensors'][$sensor_type]['options'];
        }
        foreach ($device['dynamic_discovery']['modules']['sensors'][$sensor_type]['data'] as $data) {
            $tmp_name = $data['oid'];
            $raw_data = $pre_cache[$tmp_name];
            $cached_data = $pre_cache['__cached'] ?: array();
            foreach ($raw_data as $index => $snmp_data) {
                $value = is_numeric($snmp_data[$data['value']]) ? $snmp_data[$data['value']] : (is_numeric($snmp_data[$data['oid']]) ? $snmp_data[$data['oid']]: false);
                if (can_skip_sensor($value, $data, $sensor_options) === false && is_numeric($value)) {
                    $oid = $data['num_oid'] . $index;
                    if (isset($snmp_data[$data['descr']])) {
                        $descr = $snmp_data[$data['descr']];
                    } else {
                        $descr = str_replace('{{ $index }}', $index, $data['descr']);
                        preg_match_all('/({{ [\$a-zA-Z0-9]+ }})/', $descr, $tmp_var, PREG_PATTERN_ORDER);
                        $tmp_vars = $tmp_var[0];
                        foreach ($tmp_vars as $k => $tmp_var) {
                            $tmp_var = preg_replace('/({{ | }}|\$)/', '', $tmp_var);
                            if ($snmp_data[$tmp_var]) {
                                $descr = str_replace("{{ \$$tmp_var }}", $snmp_data[$tmp_var], $descr);
                            }
                            if ($cached_data[$index][$tmp_var]) {
                                $descr = str_replace("{{ \$$tmp_var }}", $cached_data[$index][$tmp_var], $descr);
                            }
                        }
                    }
                    $divisor = $data['divisor'] ?: ($sensor_options['divisor'] ?: 1);
                    $multiplier = $data['multiplier'] ?: ($sensor_options['multiplier'] ?: 1);
                    $low_limit = is_numeric($data['low_limit']) ? $data['low_limit'] : ($snmp_data[$data['low_limit']] ?: 'null');
                    $low_warn_limit = is_numeric($data['low_warn_limit']) ? $data['low_warn_limit'] : ($snmp_data[$data['low_warn_limit']] ?: 'null');
                    $warn_limit = is_numeric($data['warn_limit']) ? $data['warn_limit'] : ($snmp_data[$data['warn_limit']] ?: 'null');
                    $high_limit = is_numeric($data['high_limit']) ? $data['high_limit'] : ($snmp_data[$data['high_limit']] ?: 'null');
                    $state_name = '';
                    if ($sensor_type !== 'state') {
                        if (is_numeric($divisor)) {
                            $value = $value / $divisor;
                        }
                        if (is_numeric($multiplier)) {
                            $value = $value * $multiplier;
                        }
                    } else {
                        $state_name = $data['state_name'] ?: $data['oid'];
                        $state_index_id = create_state_index($state_name);
                        if ($state_index_id != null) {
                            foreach ($data['states'] as $state) {
                                $insert = array(
                                    'state_index_id' => $state_index_id,
                                    'state_descr' => $state['descr'],
                                    'state_draw_graph' => $state['graph'],
                                    'state_value' => $state['value'],
                                    'state_generic_value' => $state['generic']
                                );
                                dbInsert($insert, 'state_translations');
                            }
                        }
                    }
                    $tmp_index = $data['index'] ?: $index;
                    $uindex = str_replace('{{ $index }}', $index, $tmp_index);
                    if ($sensor_type === 'state') {
                        discover_sensor($valid['sensor'], $sensor_type, $device, $oid, $uindex, $state_name, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $value);
                        create_sensor_to_state_index($device, $state_name, $uindex);
                    } else {
                        discover_sensor($valid['sensor'], $sensor_type, $device, $oid, $uindex, $device['os'], $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $value);
                    }
                }
            }
        }
    }
}

/**
 * @param $types
 * @param $device
 * @param array $pre_cache
 */
function sensors($types, $device, $valid, $pre_cache = array())
{
    global $config;
    foreach ((array)$types as $sensor_type) {
        echo ucfirst($sensor_type) . ': ';
        $dir = $config['install_dir'] . '/includes/discovery/sensors/' . $sensor_type .'/';

        if (is_file($dir . $device['os_group'] . '.inc.php')) {
            include $dir . $device['os_group'] . '.inc.php';
        }
        if (is_file($dir . $device['os'] . '.inc.php')) {
            include $dir . $device['os'] . '.inc.php';
        }
        if (isset($config['os'][$device['os']]['rfc1628_compat']) && $config['os'][$device['os']]['rfc1628_compat']) {
            if (is_file($dir  . '/rfc1628.inc.php')) {
                include $dir . '/rfc1628.inc.php';
            }
        }
        discovery_process($valid, $device, $sensor_type, $pre_cache);
        d_echo($valid['sensor'][$sensor_type]);
        check_valid_sensors($device, $sensor_type, $valid['sensor']);
        echo "\n";
    }
}

function build_bgp_peers($device, $data, $peer2)
{
    d_echo("Peers : $data\n");
    $peers = trim(str_replace('ARISTA-BGP4V2-MIB::aristaBgp4V2PeerRemoteAs.1.', '', $data));
    $peers = trim(str_replace('CISCO-BGP4-MIB::cbgpPeer2RemoteAs.', '', $peers));
    $peers = trim(str_replace('BGP4-MIB::bgpPeerRemoteAs.', '', $peers));
    $peers  = trim(str_replace('.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13.', '', $peers));
    $peerlist = array();
    $ver = '';
    foreach (explode("\n", $peers) as $peer) {
        if ($peer2 === true) {
            list($ver, $peer) = explode('.', $peer, 2);
        }
        list($peer_ip, $peer_as) = explode(' ', $peer);
        if ($device['os'] === 'junos') {
            $ver = '';
            $octets = count(explode(".", $peer_ip));
            if ($octets > 11) {
                // ipv6
                $peer_ip = (string)IP::parse(snmp2ipv6(implode('.', array_slice(explode('.', $peer_ip), (count(explode('.', $peer_ip)) - 16)))), true);
            } else {
                // ipv4
                $peer_ip = implode('.', array_slice(explode('.', $peer_ip), (count(explode('.', $peer_ip)) - 4)));
            }
        } else {
            if (strstr($peer_ip, ':')) {
                $peer_ip_snmp = preg_replace('/:/', ' ', $peer_ip);
                $peer_ip = preg_replace('/(\S+\s+\S+)\s/', '$1:', $peer_ip_snmp);
                $peer_ip = str_replace('"', '', str_replace(' ', '', $peer_ip));
            }
        }
        if ($peer && $peer_ip != '0.0.0.0') {
            d_echo("Found peer $peer_ip (AS$peer_as)\n");
            $peerlist[] = array(
                'ip'  => $peer_ip,
                'as'  => $peer_as,
                'ver' => $ver,
            );
        }
    }
    return $peerlist;
}

function build_cbgp_peers($device, $peer, $af_data, $peer2)
{

    d_echo('afi data :: ');
    d_echo($af_data);

    $af_list = array();
    foreach ($af_data as $k => $v) {
        if ($peer2 === true) {
            list(,$k) = explode('.', $k, 2);
        }

        d_echo("AFISAFI = $k\n");

        $afisafi_tmp = explode('.', $k);
        $safi        = array_pop($afisafi_tmp);
        $afi         = array_pop($afisafi_tmp);
        $bgp_ip      = str_replace(".$afi.$safi", '', $k);
        if ($device['os_group'] === 'arista') {
            $bgp_ip      = str_replace("$afi.", '', $bgp_ip);
        }
        $bgp_ip      = preg_replace('/:/', ' ', $bgp_ip);
        $bgp_ip      = preg_replace('/(\S+\s+\S+)\s/', '$1:', $bgp_ip);
        $bgp_ip      = str_replace('"', '', str_replace(' ', '', $bgp_ip));

        if ($afi && $safi && $bgp_ip == $peer['ip']) {
            $af_list[$bgp_ip][$afi][$safi] = 1;
            add_cbgp_peer($device, $peer, $afi, $safi);
        }
    }
    return $af_list;
}

function add_bgp_peer($device, $peer)
{
    global $config;
    if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ?', array($device['device_id'], $peer['ip'])) < '1') {
        $bgpPeers = array(
            'device_id' => $device['device_id'],
            'bgpPeerIdentifier' => $peer['ip'],
            'bgpPeerRemoteAs' => $peer['as'],
            'context_name' => $device['context_name'],
            'astext' => $peer['astext'],
            'bgpPeerState' => 'idle',
            'bgpPeerAdminStatus' => 'stop',
            'bgpLocalAddr' => '0.0.0.0',
            'bgpPeerRemoteAddr' => '0.0.0.0',
            'bgpPeerInUpdates' => 0,
            'bgpPeerOutUpdates' => 0,
            'bgpPeerInTotalMessages' => 0,
            'bgpPeerOutTotalMessages' => 0,
            'bgpPeerFsmEstablishedTime' => 0,
            'bgpPeerInUpdateElapsedTime' => 0,
        );
        dbInsert($bgpPeers, 'bgpPeers');
        if ($config['autodiscovery']['bgp'] === true) {
            $name             = gethostbyaddr($peer['ip']);
            discover_new_device($name, $device, 'BGP');
        }
        echo '+';
    } else {
        dbUpdate(array('bgpPeerRemoteAs' => $peer['as'], 'astext' => mres($peer['astext'])), 'bgpPeers', 'device_id=? AND bgpPeerIdentifier=?', array($device['device_id'], $peer['ip']));
        echo '.';
    }
}

function add_cbgp_peer($device, $peer, $afi, $safi)
{
    if (dbFetchCell('SELECT COUNT(*) from `bgpPeers_cbgp` WHERE device_id = ? AND bgpPeerIdentifier = ? AND afi=? AND safi=?', array($device['device_id'], $peer['ip'], $afi, $safi)) == 0) {
        $cbgp = array(
            'device_id' => $device['device_id'],
            'bgpPeerIdentifier' => $peer['ip'],
            'afi' => $afi,
            'safi' => $safi,
            'context_name' => $device['context_name'],
            'AcceptedPrefixes' => 0,
            'DeniedPrefixes' => 0,
            'PrefixAdminLimit' => 0,
            'PrefixThreshold' => 0,
            'PrefixClearThreshold' => 0,
            'AdvertisedPrefixes' => 0,
            'SuppressedPrefixes' => 0,
            'WithdrawnPrefixes' => 0,
            'AcceptedPrefixes_delta' => 0,
            'AcceptedPrefixes_prev' => 0,
            'DeniedPrefixes_delta' => 0,
            'DeniedPrefixes_prev' => 0,
            'AdvertisedPrefixes_delta' => 0,
            'AdvertisedPrefixes_prev' => 0,
            'SuppressedPrefixes_delta' => 0,
            'SuppressedPrefixes_prev' => 0,
            'WithdrawnPrefixes_delta' => 0,
            'WithdrawnPrefixes_prev' => 0,
        );
        dbInsert($cbgp, 'bgpPeers_cbgp');
    }
}
