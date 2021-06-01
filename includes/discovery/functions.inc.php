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

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv6;

function discover_new_device($hostname, $device = [], $method = '', $interface = '')
{
    d_echo("discovering $hostname\n");

    if (IP::isValid($hostname)) {
        $ip = $hostname;
        if (! Config::get('discovery_by_ip', false)) {
            d_echo('Discovery by IP disabled, skipping ' . $hostname);
            log_event("$method discovery of " . $hostname . ' failed - Discovery by IP disabled', $device['device_id'], 'discovery', 4);

            return false;
        }
    } elseif (\LibreNMS\Util\Validate::hostname($hostname)) {
        if ($mydomain = Config::get('mydomain')) {
            $full_host = rtrim($hostname, '.') . '.' . $mydomain;
            if (isDomainResolves($full_host)) {
                $hostname = $full_host;
            }
        }

        $ip = gethostbyname($hostname);
        if ($ip == $hostname) {
            d_echo("name lookup of $hostname failed\n");
            log_event("$method discovery of " . $hostname . ' failed - Check name lookup', $device['device_id'], 'discovery', 5);

            return false;
        }
    } else {
        d_echo("Discovery failed: '$hostname' is not a valid ip or dns name\n");

        return false;
    }

    d_echo("ip lookup result: $ip\n");

    $hostname = rtrim($hostname, '.'); // remove trailing dot

    $ip = IP::parse($ip, true);
    if ($ip->inNetworks(Config::get('autodiscovery.nets-exclude'))) {
        d_echo("$ip in an excluded network - skipping\n");

        return false;
    }

    if (! $ip->inNetworks(Config::get('nets'))) {
        d_echo("$ip not in a matched network - skipping\n");

        return false;
    }

    try {
        $remote_device_id = addHost($hostname, '', '161', 'udp', Config::get('default_poller_group'));
        $remote_device = device_by_id_cache($remote_device_id, 1);
        echo '+[' . $remote_device['hostname'] . '(' . $remote_device['device_id'] . ')]';
        discover_device($remote_device);
        device_by_id_cache($remote_device_id, 1);

        if ($remote_device_id && is_array($device) && ! empty($method)) {
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

    return false;
}
//end discover_new_device()

/**
 * @param $device
 */
function load_discovery(&$device)
{
    $yaml_discovery = Config::get('install_dir') . '/includes/definitions/discovery/' . $device['os'] . '.yaml';
    if (file_exists($yaml_discovery)) {
        $device['dynamic_discovery'] = Symfony\Component\Yaml\Yaml::parse(
            file_get_contents($yaml_discovery)
        );
    } else {
        unset($device['dynamic_discovery']);
    }
}

/**
 * @param array $device The device to poll
 * @param bool $force_module Ignore device module overrides
 * @return bool if the device was discovered or skipped
 */
function discover_device(&$device, $force_module = false)
{
    if ($device['snmp_disable'] == '1') {
        return false;
    }

    global $valid;

    $valid = [];
    // Reset $valid array
    $attribs = DeviceCache::getPrimary()->getAttribs();
    $device['attribs'] = $attribs;

    $device_start = microtime(true);
    // Start counting device poll time
    echo $device['hostname'] . ' ' . $device['device_id'] . ' ' . $device['os'] . ' ';

    $response = device_is_up($device, true);

    if ($response['status'] !== '1') {
        return false;
    }

    $discovery_devices = Config::get('discovery_modules', []);
    $discovery_devices = ['core' => true] + $discovery_devices;

    foreach ($discovery_devices as $module => $module_status) {
        $os_module_status = Config::getOsSetting($device['os'], "discovery_modules.$module");
        d_echo('Modules status: Global' . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
        d_echo('OS' . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
        d_echo('Device' . (isset($attribs['discover_' . $module]) ? ($attribs['discover_' . $module] ? '+ ' : '- ') : '  '));
        if ($force_module === true ||
            ! empty($attribs['discover_' . $module]) ||
            ($os_module_status && ! isset($attribs['discover_' . $module])) ||
            ($module_status && ! isset($os_module_status) && ! isset($attribs['discover_' . $module]))
        ) {
            $module_start = microtime(true);
            $start_memory = memory_get_usage();
            echo "\n#### Load disco module $module ####\n";

            try {
                include "includes/discovery/$module.inc.php";
            } catch (Exception $e) {
                // isolate module exceptions so they don't disrupt the polling process
                echo $e->getTraceAsString() . PHP_EOL;
                c_echo("%rError in $module module.%n " . $e->getMessage() . PHP_EOL);
                logfile("Error in $module module. " . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
            }

            $module_time = microtime(true) - $module_start;
            $module_time = substr($module_time, 0, 5);
            $module_mem = (memory_get_usage() - $start_memory);
            printf("\n>> Runtime for discovery module '%s': %.4f seconds with %s bytes\n", $module, $module_time, $module_mem);
            printChangedStats();
            echo "#### Unload disco module $module ####\n\n";
        } elseif (isset($attribs['discover_' . $module]) && $attribs['discover_' . $module] == '0') {
            echo "Module [ $module ] disabled on host.\n\n";
        } elseif (isset($os_module_status) && $os_module_status == '0') {
            echo "Module [ $module ] disabled on os.\n\n";
        } else {
            echo "Module [ $module ] disabled globally.\n\n";
        }
    }

    $device_time = round(microtime(true) - $device_start, 3);

    dbUpdate(['last_discovered' => ['NOW()'], 'last_discovered_timetaken' => $device_time], 'devices', '`device_id` = ?', [$device['device_id']]);

    echo "Discovered in $device_time seconds\n";

    echo PHP_EOL;

    return true;
}
//end discover_device()

// Discover sensors
function discover_sensor(&$valid, $class, $device, $oid, $index, $type, $descr, $divisor = 1, $multiplier = 1, $low_limit = null, $low_warn_limit = null, $warn_limit = null, $high_limit = null, $current = null, $poller_type = 'snmp', $entPhysicalIndex = null, $entPhysicalIndex_measured = null, $user_func = null, $group = null)
{
    $guess_limits = Config::get('sensors.guess_limits', true);

    $low_limit = set_null($low_limit);
    $low_warn_limit = set_null($low_warn_limit);
    $warn_limit = set_null($warn_limit);
    $high_limit = set_null($high_limit);
    $current = cast_number($current);

    if (! is_numeric($divisor)) {
        $divisor = 1;
    }
    if (can_skip_sensor($device, $class, $descr)) {
        return false;
    }

    d_echo("Discover sensor: $oid, $index, $type, $descr, $poller_type, $divisor, $multiplier, $entPhysicalIndex, $current, (limits: LL: $low_limit, LW: $low_warn_limit, W: $warn_limit, H: $high_limit)\n");

    if (isset($warn_limit, $low_warn_limit) && $low_warn_limit > $warn_limit) {
        // Fix high/low thresholds (i.e. on negative numbers)
        [$warn_limit, $low_warn_limit] = [$low_warn_limit, $warn_limit];
    }

    if (dbFetchCell('SELECT COUNT(sensor_id) FROM `sensors` WHERE `poller_type`= ? AND `sensor_class` = ? AND `device_id` = ? AND sensor_type = ? AND `sensor_index` = ?', [$poller_type, $class, $device['device_id'], $type, (string) $index]) == '0') {
        if ($guess_limits && is_null($high_limit)) {
            $high_limit = sensor_limit($class, $current);
        }

        if ($guess_limits && is_null($low_limit)) {
            $low_limit = sensor_low_limit($class, $current);
        }

        if (! is_null($high_limit) && $low_limit > $high_limit) {
            // Fix high/low thresholds (i.e. on negative numbers)
            [$high_limit, $low_limit] = [$low_limit, $high_limit];
        }

        $insert = [
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
            'group' => $group,
        ];

        foreach ($insert as $key => $val_check) {
            if (! isset($val_check)) {
                unset($insert[$key]);
            }
        }

        $inserted = dbInsert($insert, 'sensors');

        d_echo("( $inserted inserted )\n");

        echo '+';
        log_event('Sensor Added: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr, $device, 'sensor', 3, $inserted);
    } else {
        $sensor_entry = dbFetchRow('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?', [$class, $device['device_id'], $type, (string) $index]);

        if (! isset($high_limit)) {
            if ($guess_limits && ! $sensor_entry['sensor_limit']) {
                // Calculate a reasonable limit
                $high_limit = sensor_limit($class, $current);
            } else {
                // Use existing limit
                $high_limit = $sensor_entry['sensor_limit'];
            }
        }

        if (! isset($low_limit)) {
            if ($guess_limits && ! $sensor_entry['sensor_limit_low']) {
                // Calculate a reasonable limit
                $low_limit = sensor_low_limit($class, $current);
            } else {
                // Use existing limit
                $low_limit = $sensor_entry['sensor_limit_low'];
            }
        }

        // Fix high/low thresholds (i.e. on negative numbers)
        if (isset($high_limit) && $low_limit > $high_limit) {
            [$high_limit, $low_limit] = [$low_limit, $high_limit];
        }

        if ($high_limit != $sensor_entry['sensor_limit'] && $sensor_entry['sensor_custom'] == 'No') {
            $update = ['sensor_limit' => ($high_limit == null ? ['NULL'] : $high_limit)];
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', [$sensor_entry['sensor_id']]);
            d_echo("( $updated updated )\n");

            echo 'H';
            log_event('Sensor High Limit Updated: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr . ' (' . $high_limit . ')', $device, 'sensor', 3, $sensor_entry['sensor_id']);
        }

        if ($sensor_entry['sensor_limit_low'] != $low_limit && $sensor_entry['sensor_custom'] == 'No') {
            $update = ['sensor_limit_low' => ($low_limit == null ? ['NULL'] : $low_limit)];
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', [$sensor_entry['sensor_id']]);
            d_echo("( $updated updated )\n");

            echo 'L';
            log_event('Sensor Low Limit Updated: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr . ' (' . $low_limit . ')', $device, 'sensor', 3, $sensor_entry['sensor_id']);
        }

        if ($warn_limit != $sensor_entry['sensor_limit_warn'] && $sensor_entry['sensor_custom'] == 'No') {
            $update = ['sensor_limit_warn' => ($warn_limit == null ? ['NULL'] : $warn_limit)];
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', [$sensor_entry['sensor_id']]);
            d_echo("( $updated updated )\n");

            echo 'WH';
            log_event('Sensor Warn High Limit Updated: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr . ' (' . $warn_limit . ')', $device, 'sensor', 3, $sensor_entry['sensor_id']);
        }

        if ($sensor_entry['sensor_limit_low_warn'] != $low_warn_limit && $sensor_entry['sensor_custom'] == 'No') {
            $update = ['sensor_limit_low_warn' => ($low_warn_limit == null ? ['NULL'] : $low_warn_limit)];
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', [$sensor_entry['sensor_id']]);
            d_echo("( $updated updated )\n");

            echo 'WL';
            log_event('Sensor Warn Low Limit Updated: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr . ' (' . $low_warn_limit . ')', $device, 'sensor', 3, $sensor_entry['sensor_id']);
        }

        if ($oid == $sensor_entry['sensor_oid'] &&
            $descr == $sensor_entry['sensor_descr'] &&
            $multiplier == $sensor_entry['sensor_multiplier'] &&
            $divisor == $sensor_entry['sensor_divisor'] &&
            $entPhysicalIndex_measured == $sensor_entry['entPhysicalIndex_measured'] &&
            $entPhysicalIndex == $sensor_entry['entPhysicalIndex'] &&
            $user_func == $sensor_entry['user_func'] &&
            $group == $sensor_entry['group']

        ) {
            echo '.';
        } else {
            $update = [
                'sensor_oid' => $oid,
                'sensor_descr' => $descr,
                'sensor_multiplier' => $multiplier,
                'sensor_divisor' => $divisor,
                'entPhysicalIndex' => $entPhysicalIndex,
                'entPhysicalIndex_measured' => $entPhysicalIndex_measured,
                'user_func' => $user_func,
                'group' => $group,
            ];
            $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', [$sensor_entry['sensor_id']]);
            echo 'U';
            log_event('Sensor Updated: ' . $class . ' ' . $type . ' ' . $index . ' ' . $descr, $device, 'sensor', 3, $sensor_entry['sensor_id']);
            d_echo("( $updated updated )\n");
        }
    }//end if
    $valid[$class][$type][$index] = 1;
}

//end discover_sensor()

function sensor_low_limit($class, $current)
{
    // matching an empty case executes code until a break is reached
    switch ($class) {
        case 'temperature':
            $limit = $current - 10;
            break;
        case 'voltage':
            $limit = $current * 0.85;
            break;
        case 'humidity':
            $limit = 30;
            break;
        case 'fanspeed':
            $limit = $current * 0.80;
            break;
        case 'power_factor':
            $limit = -1;
            break;
        case 'signal':
            $limit = -80;
            break;
        case 'airflow':
        case 'snr':
        case 'frequency':
        case 'pressure':
        case 'cooling':
            $limit = $current * 0.95;
            break;
        default:
            return null;
    }//end switch

    return round($limit, 11);
}

//end sensor_low_limit()

function sensor_limit($class, $current)
{
    // matching an empty case executes code until a break is reached
    switch ($class) {
        case 'temperature':
            $limit = $current + 20;
            break;
        case 'voltage':
            $limit = $current * 1.15;
            break;
        case 'humidity':
            $limit = 70;
            break;
        case 'fanspeed':
            $limit = $current * 1.80;
            break;
        case 'power_factor':
            $limit = 1;
            break;
        case 'signal':
            $limit = -30;
            break;
        case 'load':
            $limit = 80;
            break;
        case 'airflow':
        case 'snr':
        case 'frequency':
        case 'pressure':
        case 'cooling':
            $limit = $current * 1.05;
            break;
        default:
            return null;
    }//end switch

    return round($limit, 11);
}

//end sensor_limit()

function check_valid_sensors($device, $class, $valid, $poller_type = 'snmp')
{
    $entries = dbFetchRows('SELECT * FROM sensors AS S, devices AS D WHERE S.sensor_class=? AND S.device_id = D.device_id AND D.device_id = ? AND S.poller_type = ?', [$class, $device['device_id'], $poller_type]);

    if (count($entries)) {
        foreach ($entries as $entry) {
            $index = $entry['sensor_index'];
            $type = $entry['sensor_type'];
            $class = $entry['sensor_class'];
            d_echo($index . ' -> ' . $type . "\n");

            if (! $valid[$class][$type][$index]) {
                echo '-';
                if ($class == 'state') {
                    dbDelete('sensors_to_state_indexes', '`sensor_id` =  ?', [$entry['sensor_id']]);
                }
                dbDelete('sensors', '`sensor_id` =  ?', [$entry['sensor_id']]);
                log_event('Sensor Deleted: ' . $entry['sensor_class'] . ' ' . $entry['sensor_type'] . ' ' . $entry['sensor_index'] . ' ' . $entry['sensor_descr'], $device, 'sensor', 3, $entry['sensor_id']);
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

    if (dbFetchCell('SELECT COUNT(*) FROM `juniAtmVp` WHERE `port_id` = ? AND `vp_id` = ?', [$port_id, $vp_id]) == '0') {
        $inserted = dbInsert(['port_id' => $port_id, 'vp_id' => $vp_id, 'vp_descr' => $vp_descr], 'juniAtmVp');
        d_echo("( $inserted inserted )\n");

        // FIXME vv no $device!
        log_event('Juniper ATM VP Added: port ' . $port_id . ' vp ' . $vp_id . ' descr' . $vp_descr, $device, 'juniAtmVp', 3, $inserted);
    } else {
        echo '.';
    }

    $valid[$port_id][$vp_id] = 1;
}

//end discover_juniAtmVp()

function discover_link($local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $local_device_id, $remote_device_id)
{
    global $link_exists;

    d_echo("Discover link: $local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $remote_device_id\n");

    if (dbFetchCell(
        'SELECT COUNT(*) FROM `links` WHERE `remote_hostname` = ? AND `local_port_id` = ? AND `protocol` = ? AND `remote_port` = ?',
        [
            $remote_hostname,
            $local_port_id,
            $protocol,
            $remote_port,
        ]
    ) == '0') {
        $insert_data = [
            'local_port_id' => $local_port_id,
            'local_device_id' => $local_device_id,
            'protocol' => $protocol,
            'remote_hostname' => $remote_hostname,
            'remote_device_id' => (int) $remote_device_id,
            'remote_port' => $remote_port,
            'remote_platform' => $remote_platform,
            'remote_version' => $remote_version,
        ];

        if (! empty($remote_port_id)) {
            $insert_data['remote_port_id'] = (int) $remote_port_id;
        }

        $inserted = dbInsert($insert_data, 'links');

        echo '+';
        d_echo("( $inserted inserted )");
    } else {
        $sql = 'SELECT `id`,`local_device_id`,`remote_platform`,`remote_version`,`remote_device_id`,`remote_port_id` FROM `links`';
        $sql .= ' WHERE `remote_hostname` = ? AND `local_port_id` = ? AND `protocol` = ? AND `remote_port` = ?';
        $data = dbFetchRow($sql, [$remote_hostname, $local_port_id, $protocol, $remote_port]);

        $update_data = [
            'local_device_id' => $local_device_id,
            'remote_platform' => $remote_platform,
            'remote_version' => $remote_version,
            'remote_device_id' => (int) $remote_device_id,
            'remote_port_id' => (int) $remote_port_id,
        ];

        $id = $data['id'];
        unset($data['id']);
        if ($data == $update_data) {
            echo '.';
        } else {
            $updated = dbUpdate($update_data, 'links', '`id` = ?', [$id]);
            echo 'U';
            d_echo("( $updated updated )");
        }//end if
    }//end if
    $link_exists[$local_port_id][$remote_hostname][$remote_port] = 1;
}

//end discover_link()

function discover_storage(&$valid, $device, $index, $type, $mib, $descr, $size, $units, $used = null)
{
    if (ignore_storage($device['os'], $descr)) {
        return;
    }
    d_echo("Discover Storage: $index, $type, $mib, $descr, $size, $units, $used\n");

    if ($descr && $size > '0') {
        $storage = dbFetchRow('SELECT * FROM `storage` WHERE `storage_index` = ? AND `device_id` = ? AND `storage_mib` = ?', [$index, $device['device_id'], $mib]);
        if (empty($storage)) {
            if (Config::getOsSetting($device['os'], 'storage_perc_warn')) {
                $perc_warn = Config::getOsSetting($device['os'], 'storage_perc_warn');
            } else {
                $perc_warn = Config::get('storage_perc_warn', 60);
            }

            dbInsert(
                [
                    'device_id' => $device['device_id'],
                    'storage_descr' => $descr,
                    'storage_index' => $index,
                    'storage_mib' => $mib,
                    'storage_type' => $type,
                    'storage_units' => $units,
                    'storage_size' => $size,
                    'storage_used' => $used,
                    'storage_perc_warn' => $perc_warn,
                ],
                'storage'
            );

            echo '+';
        } else {
            $updated = dbUpdate(['storage_descr' => $descr, 'storage_type' => $type, 'storage_units' => $units, 'storage_size' => $size], 'storage', '`device_id` = ? AND `storage_index` = ? AND `storage_mib` = ?', [$device['device_id'], $index, $mib]);
            if ($updated) {
                echo 'U';
            } else {
                echo '.';
            }
        }//end if

        $valid[$mib][$index] = 1;
    }//end if
}

function discover_entity_physical(&$valid, $device, $entPhysicalIndex, $entPhysicalDescr, $entPhysicalClass, $entPhysicalName, $entPhysicalModelName, $entPhysicalSerialNum, $entPhysicalContainedIn, $entPhysicalMfgName, $entPhysicalParentRelPos, $entPhysicalVendorType, $entPhysicalHardwareRev, $entPhysicalFirmwareRev, $entPhysicalSoftwareRev, $entPhysicalIsFRU, $entPhysicalAlias, $entPhysicalAssetID, $ifIndex)
{
    d_echo("Discover Inventory Item: $entPhysicalIndex, $entPhysicalDescr, $entPhysicalClass, $entPhysicalName, $entPhysicalModelName, $entPhysicalSerialNum, $entPhysicalContainedIn, $entPhysicalMfgName, $entPhysicalParentRelPos, $entPhysicalVendorType, $entPhysicalHardwareRev, $entPhysicalFirmwareRev, $entPhysicalSoftwareRev, $entPhysicalIsFRU, $entPhysicalAlias, $entPhysicalAssetID, $ifIndex\n");

    if ($entPhysicalDescr || $entPhysicalName) {
        if (dbFetchCell('SELECT COUNT(entPhysical_id) FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?', [$device['device_id'], $entPhysicalIndex]) == '0') {
            $insert_data = [
                'device_id'               => $device['device_id'],
                'entPhysicalIndex'        => $entPhysicalIndex,
                'entPhysicalDescr'        => $entPhysicalDescr,
                'entPhysicalClass'        => $entPhysicalClass,
                'entPhysicalName'         => $entPhysicalName,
                'entPhysicalModelName'    => $entPhysicalModelName,
                'entPhysicalSerialNum'    => $entPhysicalSerialNum,
                'entPhysicalContainedIn'  => $entPhysicalContainedIn,
                'entPhysicalMfgName'      => $entPhysicalMfgName,
                'entPhysicalParentRelPos' => $entPhysicalParentRelPos,
                'entPhysicalVendorType'   => $entPhysicalVendorType,
                'entPhysicalHardwareRev'  => $entPhysicalHardwareRev,
                'entPhysicalFirmwareRev'  => $entPhysicalFirmwareRev,
                'entPhysicalSoftwareRev'  => $entPhysicalSoftwareRev,
                'entPhysicalIsFRU'        => $entPhysicalIsFRU,
                'entPhysicalAlias'        => $entPhysicalAlias,
                'entPhysicalAssetID'      => $entPhysicalAssetID,
            ];
            if (! empty($ifIndex)) {
                $insert_data['ifIndex'] = $ifIndex;
            }

            $inserted = dbInsert($insert_data, 'entPhysical');
            echo '+';
            log_event('Inventory Item added: index ' . $entPhysicalIndex . ' descr ' . $entPhysicalDescr, $device, 'entity-physical', 3, $inserted);
        } else {
            echo '.';
            $update_data = [
                'entPhysicalIndex'        => $entPhysicalIndex,
                'entPhysicalDescr'        => $entPhysicalDescr,
                'entPhysicalClass'        => $entPhysicalClass,
                'entPhysicalName'         => $entPhysicalName,
                'entPhysicalModelName'    => $entPhysicalModelName,
                'entPhysicalSerialNum'    => $entPhysicalSerialNum,
                'entPhysicalContainedIn'  => $entPhysicalContainedIn,
                'entPhysicalMfgName'      => $entPhysicalMfgName,
                'entPhysicalParentRelPos' => $entPhysicalParentRelPos,
                'entPhysicalVendorType'   => $entPhysicalVendorType,
                'entPhysicalHardwareRev'  => $entPhysicalHardwareRev,
                'entPhysicalFirmwareRev'  => $entPhysicalFirmwareRev,
                'entPhysicalSoftwareRev'  => $entPhysicalSoftwareRev,
                'entPhysicalIsFRU'        => $entPhysicalIsFRU,
                'entPhysicalAlias'        => $entPhysicalAlias,
                'entPhysicalAssetID'      => $entPhysicalAssetID,
                'ifIndex'                 => $ifIndex,
            ];
            dbUpdate($update_data, 'entPhysical', '`device_id`=? AND `entPhysicalIndex`=?', [$device['device_id'], $entPhysicalIndex]);
        }//end if
        $valid[$entPhysicalIndex] = 1;
    }//end if
}

//end discover_entity_physical()

function discover_process_ipv6(&$valid, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin, $context_name = '')
{
    global $device;

    if (! IPv6::isValid($ipv6_address, true)) {
        // ignore link-locals (coming from IPV6-MIB)
        return;
    }

    $ipv6 = new IPv6($ipv6_address);
    $ipv6_network = $ipv6->getNetwork($ipv6_prefixlen);
    $ipv6_compressed = $ipv6->compressed();

    if (dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifIndex` = ?', [$device['device_id'], $ifIndex]) != '0' && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && $ipv6_compressed != '::1') {
        $port_id = dbFetchCell('SELECT port_id FROM `ports` WHERE device_id = ? AND ifIndex = ?', [$device['device_id'], $ifIndex]);

        if (is_numeric($port_id)) {
            if (dbFetchCell('SELECT COUNT(*) FROM `ipv6_networks` WHERE `ipv6_network` = ?', [$ipv6_network]) < '1') {
                dbInsert(['ipv6_network' => $ipv6_network, 'context_name' => $context_name], 'ipv6_networks');
                echo 'N';
            } else {
                //Update Context
                dbUpdate(['context_name' => $device['context_name']], 'ipv6_networks', '`ipv6_network` = ?', [$ipv6_network]);
                echo 'n';
            }

            if ($context_name == null) {
                $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ? AND `context_name` IS NULL', [$ipv6_network]);
            } else {
                $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ? AND `context_name` = ?', [$ipv6_network, $context_name]);
            }
            if (dbFetchCell('SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ?', [$ipv6_address, $ipv6_prefixlen, $port_id]) == '0') {
                dbInsert([
                    'ipv6_address' => $ipv6_address,
                    'ipv6_compressed' => $ipv6_compressed,
                    'ipv6_prefixlen' => $ipv6_prefixlen,
                    'ipv6_origin' => $ipv6_origin,
                    'ipv6_network_id' => $ipv6_network_id,
                    'port_id' => $port_id,
                    'context_name' => $context_name,
                ], 'ipv6_addresses');
                echo '+';
            } elseif (dbFetchCell('SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ? AND `ipv6_network_id` = ""', [$ipv6_address, $ipv6_prefixlen, $port_id]) == '1') {
                // Update IPv6 network ID if not set
                if ($context_name == null) {
                    $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ? AND `context_name` IS NULL', [$ipv6_network]);
                } else {
                    $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ? AND `context_name` = ?', [$ipv6_network, $context_name]);
                }
                dbUpdate(['ipv6_network_id' => $ipv6_network_id], 'ipv6_addresses', '`ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ?', [$ipv6_address, $ipv6_prefixlen, $port_id]);
                echo 'u';
            } else {
                //Update Context
                dbUpdate(['context_name' => $device['context_name']], 'ipv6_addresses', '`ipv6_address` = ? AND `ipv6_prefixlen` = ? AND `port_id` = ?', [$ipv6_address, $ipv6_prefixlen, $port_id]);
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
    $fringe = array_merge(Config::get('bad_entity_sensor_regex', []), Config::getOsSetting($device['os'], 'bad_entity_sensor_regex', []));

    foreach ($fringe as $bad) {
        if (preg_match($bad . 'i', $string)) {
            d_echo("Ignored entity sensor: $bad : $string");

            return false;
        }
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
            if (Str::startsWith($device['hardware'], 'UPS2000')) {
                return 10;
            }

            return 100;
        }
    } elseif ($device['os'] == 'hpe-rtups') {
        if ($sensor_type == 'voltage' && ! Str::startsWith($oid, '.1.3.6.1.2.1.33.1.2.5.') && ! Str::startsWith($oid, '.1.3.6.1.2.1.33.1.3.3.1.3')) {
            return 1;
        }
    } elseif ($device['os'] == 'apc-mgeups') {
        if ($sensor_type == 'voltage') {
            return 10;
        }
    }

    // UPS-MIB Defaults

    if ($sensor_type == 'load') {
        return 1;
    }

    if ($sensor_type == 'voltage' && ! Str::startsWith($oid, '.1.3.6.1.2.1.33.1.2.5.')) {
        return 1;
    }

    if ($sensor_type == 'runtime') {
        if (Str::startsWith($oid, '.1.3.6.1.2.1.33.1.2.2.')) {
            return 60;
        }

        if (Str::startsWith($oid, '.1.3.6.1.2.1.33.1.2.3.')) {
            if ($device['os'] == 'routeros') {
                return 60;
            } else {
                return 1;
            }
        }
    }

    return 10;
}

/**
 * Should we ignore this storage device based on teh description? (usually the mount path or drive)
 *
 * @param string $os The OS of the device
 * @param string $descr The description of the storage
 * @return bool
 */
function ignore_storage($os, $descr)
{
    foreach (Config::getCombined($os, 'ignore_mount', []) as $im) {
        if ($im == $descr) {
            d_echo("ignored $descr (matched: $im)\n");

            return true;
        }
    }

    foreach (Config::getCombined($os, 'ignore_mount_string', []) as $ims) {
        if (Str::contains($descr, $ims)) {
            d_echo("ignored $descr (matched: $ims)\n");

            return true;
        }
    }

    foreach (Config::getCombined($os, 'ignore_mount_regexp', []) as $imr) {
        if (preg_match($imr, $descr)) {
            d_echo("ignored $descr (matched: $imr)\n");

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
function discovery_process(&$valid, $device, $sensor_class, $pre_cache)
{
    if (! empty($device['dynamic_discovery']['modules']['sensors'][$sensor_class]) && ! can_skip_sensor($device, $sensor_class, '')) {
        $sensor_options = [];
        if (isset($device['dynamic_discovery']['modules']['sensors'][$sensor_class]['options'])) {
            $sensor_options = $device['dynamic_discovery']['modules']['sensors'][$sensor_class]['options'];
        }

        d_echo("Dynamic Discovery ($sensor_class): ");
        d_echo($device['dynamic_discovery']['modules']['sensors'][$sensor_class]);

        foreach ($device['dynamic_discovery']['modules']['sensors'][$sensor_class]['data'] as $data) {
            $tmp_name = $data['oid'];
            $raw_data = (array) $pre_cache[$tmp_name];

            d_echo("Data $tmp_name: ");
            d_echo($raw_data);

            foreach ($raw_data as $index => $snmp_data) {
                $user_function = null;
                if (isset($data['user_func'])) {
                    $user_function = $data['user_func'];
                }
                // get the value for this sensor, check 'value' and 'oid', if state string, translate to a number
                $data['value'] = isset($data['value']) ? $data['value'] : $data['oid'];  // fallback to oid if value is not set

                $snmp_value = $snmp_data[$data['value']];
                if (! is_numeric($snmp_value)) {
                    if ($sensor_class === 'temperature') {
                        // For temp sensors, try and detect fahrenheit values
                        if (Str::endsWith($snmp_value, ['f', 'F'])) {
                            $user_function = 'fahrenheit_to_celsius';
                        }
                    }
                    preg_match('/-?\d*\.?\d+/', $snmp_value, $temp_response);
                    if (! empty($temp_response[0])) {
                        $snmp_value = $temp_response[0];
                    }
                }

                if (is_numeric($snmp_value)) {
                    $value = $snmp_value;
                } elseif ($sensor_class === 'state') {
                    // translate string states to values (poller does this as well)
                    $states = array_column($data['states'], 'value', 'descr');
                    $value = isset($states[$snmp_value]) ? $states[$snmp_value] : false;
                } else {
                    $value = false;
                }

                $skippedFromYaml = YamlDiscovery::canSkipItem($value, $index, $data, $sensor_options, $pre_cache);

                // Check if we have a "num_oid" value. If not, we'll try to compute it from textual OIDs with snmptranslate.
                if (empty($data['num_oid'])) {
                    try {
                        $data['num_oid'] = YamlDiscovery::computeNumericalOID($device, $data);
                    } catch (\Exception $e) {
                        d_echo('Error: We cannot find a numerical OID for ' . $data['value'] . '. Skipping this one...');
                        $skippedFromYaml = true;
                        // Because we don't have a num_oid, we have no way to add this sensor.
                    }
                }

                if ($skippedFromYaml === false && is_numeric($value)) {
                    d_echo("Sensor fetched value: $value\n");

                    $oid = str_replace('{{ $index }}', $index, $data['num_oid']);
                    // if index is a string, we need to convert it to OID
                    // strlen($index) as first number, and each letter converted to a number, separated by dots
                    $oid = str_replace('{{ $index_string }}', strlen($index) . '.' . implode('.', unpack('c*', $index)), $oid);

                    // process the description
                    $descr = YamlDiscovery::replaceValues('descr', $index, null, $data, $pre_cache);

                    // process the group
                    $group = YamlDiscovery::replaceValues('group', $index, null, $data, $pre_cache) ?: null;

                    $divisor = $data['divisor'] ?? ($sensor_options['divisor'] ?? 1);
                    $multiplier = $data['multiplier'] ?? ($sensor_options['multiplier'] ?? 1);

                    $limits = ['low_limit', 'low_warn_limit', 'warn_limit', 'high_limit'];
                    foreach ($limits as $limit) {
                        if (isset($data[$limit]) && is_numeric($data[$limit])) {
                            $$limit = $data[$limit];
                        } else {
                            $$limit = YamlDiscovery::getValueFromData($limit, $index, $data, $pre_cache, 'null');
                            if (is_numeric($$limit)) {
                                $$limit = ($$limit / $divisor) * $multiplier;
                            }
                            if (is_numeric($$limit) && isset($user_function) && is_callable($user_function)) {
                                $$limit = $user_function($$limit);
                            }
                        }
                    }

                    $sensor_name = $device['os'];

                    if ($sensor_class === 'state') {
                        $sensor_name = $data['state_name'] ?: $data['oid'];
                        create_state_index($sensor_name, $data['states']);
                    } else {
                        // We default to 1 for both divisors / multipliers so it should be safe to do the calculation using both.
                        $value = ($value / $divisor) * $multiplier;
                    }

                    echo "Cur $value, Low: $low_limit, Low Warn: $low_warn_limit, Warn: $warn_limit, High: $high_limit" . PHP_EOL;
                    $entPhysicalIndex = YamlDiscovery::replaceValues('entPhysicalIndex', $index, null, $data, $pre_cache) ?: null;
                    $entPhysicalIndex_measured = isset($data['entPhysicalIndex_measured']) ? $data['entPhysicalIndex_measured'] : null;

                    //user_func must be applied after divisor/multiplier
                    if (isset($user_function) && is_callable($user_function)) {
                        $value = $user_function($value);
                    }

                    $uindex = str_replace('{{ $index }}', $index, isset($data['index']) ? $data['index'] : $index);
                    discover_sensor($valid['sensor'], $sensor_class, $device, $oid, $uindex, $sensor_name, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, $user_function, $group);

                    if ($sensor_class === 'state') {
                        create_sensor_to_state_index($device, $sensor_name, $uindex);
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
function sensors($types, $device, $valid, $pre_cache = [])
{
    foreach ((array) $types as $sensor_class) {
        echo ucfirst($sensor_class) . ': ';
        $dir = Config::get('install_dir') . '/includes/discovery/sensors/' . $sensor_class . '/';

        if (isset($device['os_group']) && is_file($dir . $device['os_group'] . '.inc.php')) {
            include $dir . $device['os_group'] . '.inc.php';
        }
        if (is_file($dir . $device['os'] . '.inc.php')) {
            include $dir . $device['os'] . '.inc.php';
        }
        if (Config::getOsSetting($device['os'], 'rfc1628_compat', false)) {
            if (is_file($dir . '/rfc1628.inc.php')) {
                include $dir . '/rfc1628.inc.php';
            }
        }
        discovery_process($valid, $device, $sensor_class, $pre_cache);
        d_echo($valid['sensor'][$sensor_class] ?? []);
        check_valid_sensors($device, $sensor_class, $valid['sensor']);
        echo "\n";
    }
}

function build_bgp_peers($device, $data, $peer2)
{
    d_echo("Peers : $data\n");
    $remove = [
        'ARISTA-BGP4V2-MIB::aristaBgp4V2PeerRemoteAs.1.',
        'ALCATEL-IND1-BGP-MIB::alaBgpPeerAS.',
        'CISCO-BGP4-MIB::cbgpPeer2RemoteAs.',
        'BGP4-MIB::bgpPeerRemoteAs.',
        'HUAWEI-BGP-VPN-MIB::hwBgpPeerRemoteAs.',
        '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13.',
    ];
    $peers = trim(str_replace($remove, '', $data));

    $peerlist = [];
    $ver = '';
    foreach (explode("\n", $peers) as $peer) {
        $local_ip = null;
        if ($peer2 === true) {
            [$ver, $peer] = explode('.', $peer, 2);
        }
        [$peer_ip, $peer_as] = explode(' ', $peer);
        if ($device['os'] === 'junos') {
            $ver = '';
            $octets = count(explode('.', $peer_ip));
            if ($octets > 11) {
                // ipv6
                $peer_ip = (string) IP::parse(snmp2ipv6($peer_ip), true);
            } else {
                // ipv4
                $peer_ip = implode('.', array_slice(explode('.', $peer_ip), -4));
            }
        } else {
            if (strstr($peer_ip, ':')) {
                $peer_ip_snmp = preg_replace('/:/', ' ', $peer_ip);
                $peer_ip = preg_replace('/(\S+\s+\S+)\s/', '$1:', $peer_ip_snmp);
                $peer_ip = str_replace('"', '', str_replace(' ', '', $peer_ip));
            }
        }
        if ($peer && $peer_ip != '0.0.0.0') {
            if ($peer_as < 0) {
                //if ASN is negative -> overflow int32 -> original number is max(INT32) - min(INT32) + 1 + value
                $peer_as = 4294967296 + $peer_as;
            }
            d_echo("Found peer $peer_ip (AS$peer_as)\n");
            $peerlist[] = [
                'ip'      => $peer_ip,
                'as'      => $peer_as,
                'localip' => $local_ip ?: '0.0.0.0',
                'ver'     => $ver,
            ];
        }
    }

    return $peerlist;
}

function build_cbgp_peers($device, $peer, $af_data, $peer2)
{
    d_echo('afi data :: ');
    d_echo($af_data);

    $af_list = [];
    foreach ($af_data as $k => $v) {
        if ($peer2 === true) {
            [,$k] = explode('.', $k, 2);
        }

        d_echo("AFISAFI = $k\n");

        $afisafi_tmp = explode('.', $k);
        if ($device['os_group'] === 'vrp') {
            $vpninst_id = array_shift($afisafi_tmp);
            $afi = array_shift($afisafi_tmp);
            $safi = array_shift($afisafi_tmp);
            $peertype = array_shift($afisafi_tmp);
            $bgp_ip = implode('.', $afisafi_tmp);
        } elseif ($device['os'] == 'aos7') {
            $afi = 'ipv4';
            $safi = 'unicast';
            $bgp_ip = $k;
        } else {
            $safi = array_pop($afisafi_tmp);
            $afi = array_pop($afisafi_tmp);
            $bgp_ip = str_replace(".$afi.$safi", '', $k);
            if ($device['os_group'] === 'arista') {
                $bgp_ip = str_replace("$afi.", '', $bgp_ip);
            }
        }
        $bgp_ip = preg_replace('/:/', ' ', $bgp_ip);
        $bgp_ip = preg_replace('/(\S+\s+\S+)\s/', '$1:', $bgp_ip);
        $bgp_ip = str_replace('"', '', str_replace(' ', '', $bgp_ip));

        if ($afi && $safi && $bgp_ip == $peer['ip']) {
            $af_list[$bgp_ip][$afi][$safi] = 1;
            add_cbgp_peer($device, $peer, $afi, $safi);
        }
    }

    return $af_list;
}

function add_bgp_peer($device, $peer)
{
    if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ?', [$device['device_id'], $peer['ip']]) < '1') {
        $bgpPeers = [
            'device_id' => $device['device_id'],
            'bgpPeerIdentifier' => $peer['ip'],
            'bgpPeerRemoteAs' => $peer['as'],
            'context_name' => $device['context_name'],
            'astext' => $peer['astext'],
            'bgpPeerState' => 'idle',
            'bgpPeerAdminStatus' => 'stop',
            'bgpLocalAddr' => $peer['localip'] ?: '0.0.0.0',
            'bgpPeerRemoteAddr' => '0.0.0.0',
            'bgpPeerInUpdates' => 0,
            'bgpPeerOutUpdates' => 0,
            'bgpPeerInTotalMessages' => 0,
            'bgpPeerOutTotalMessages' => 0,
            'bgpPeerFsmEstablishedTime' => 0,
            'bgpPeerInUpdateElapsedTime' => 0,
        ];
        dbInsert($bgpPeers, 'bgpPeers');
        if (Config::get('autodiscovery.bgp')) {
            $name = gethostbyaddr($peer['ip']);
            discover_new_device($name, $device, 'BGP');
        }
        echo '+';
    } else {
        dbUpdate(['bgpPeerRemoteAs' => $peer['as'], 'astext' => $peer['astext']], 'bgpPeers', 'device_id=? AND bgpPeerIdentifier=?', [$device['device_id'], $peer['ip']]);
        echo '.';
    }
}

function add_cbgp_peer($device, $peer, $afi, $safi)
{
    if (dbFetchCell('SELECT COUNT(*) from `bgpPeers_cbgp` WHERE device_id = ? AND bgpPeerIdentifier = ? AND afi=? AND safi=?', [$device['device_id'], $peer['ip'], $afi, $safi]) == 0) {
        $cbgp = [
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
        ];
        dbInsert($cbgp, 'bgpPeers_cbgp');
    }
}

/**
 * check if we should skip this sensor from discovery
 * @param $device
 * @param string $sensor_class
 * @param string $sensor_descr
 * @return bool
 */
function can_skip_sensor($device, $sensor_class = '', $sensor_descr = '')
{
    if (! empty($sensor_class) && Config::getCombined($device['os'], "disabled_sensors.$sensor_class", false)) {
        return true;
    }
    foreach (Config::getCombined($device['os'], 'disabled_sensors_regex', []) as $skipRegex) {
        if (! empty($sensor_descr) && preg_match($skipRegex, $sensor_descr)) {
            return true;
        }
    }

    return false;
}

/**
 * check if we should skip this device from discovery
 * @param string $sysName
 * @param string $sysDescr
 * @param string $platform
 * @return bool
 */
function can_skip_discovery($sysName, $sysDescr = '', $platform = '')
{
    if ($sysName) {
        foreach ((array) Config::get('autodiscovery.xdp_exclude.sysname_regexp') as $needle) {
            if (preg_match($needle . 'i', $sysName)) {
                d_echo("$sysName - regexp '$needle' matches '$sysName' - skipping device discovery \n");

                return true;
            }
        }
    }

    if ($sysDescr) {
        foreach ((array) Config::get('autodiscovery.xdp_exclude.sysdesc_regexp') as $needle) {
            if (preg_match($needle . 'i', $sysDescr)) {
                d_echo("$sysName - regexp '$needle' matches '$sysDescr' - skipping device discovery \n");

                return true;
            }
        }
    }

    if ($platform) {
        foreach ((array) Config::get('autodiscovery.cdp_exclude.platform_regexp') as $needle) {
            if (preg_match($needle . 'i', $platform)) {
                d_echo("$sysName - regexp '$needle' matches '$platform' - skipping device discovery \n");

                return true;
            }
        }
    }

    return false;
}

/**
 * Try to find a device by sysName, hostname, ip, or mac_address
 * If a device cannot be found, returns 0
 *
 * @param string $name sysName or hostname
 * @param string $ip May be an IP or hex string
 * @param string $mac_address
 * @return int the device_id or 0
 */
function find_device_id($name = '', $ip = '', $mac_address = '')
{
    $where = [];
    $params = [];

    if ($name && \LibreNMS\Util\Validate::hostname($name)) {
        $where[] = '`hostname`=?';
        $params[] = $name;

        if ($mydomain = Config::get('mydomain')) {
            $where[] = '`hostname`=?';
            $params[] = "$name.$mydomain";

            $where[] = 'concat(`hostname`, \'.\', ?) =?';
            $params[] = "$mydomain";
            $params[] = "$name";
        }
    }

    if ($ip) {
        $where[] = '`hostname`=?';
        $params[] = $ip;

        try {
            $params[] = IP::fromHexString($ip)->packed();
            $where[] = '`ip`=?';
        } catch (InvalidIpException $e) {
            //
        }
    }

    if (! empty($where)) {
        $sql = 'SELECT `device_id` FROM `devices` WHERE ' . implode(' OR ', $where);
        if ($device_id = dbFetchCell($sql, $params)) {
            return (int) $device_id;
        }
    }

    if ($mac_address && $mac_address != '000000000000') {
        if ($device_id = dbFetchCell('SELECT `device_id` FROM `ports` WHERE `ifPhysAddress`=?', [$mac_address])) {
            return (int) $device_id;
        }
    }

    if ($name) {
        $where = [];
        $params = [];

        $where[] = '`sysName`=?';
        $params[] = $name;

        if ($mydomain = Config::get('mydomain')) {
            $where[] = '`sysName`=?';
            $params[] = "$name.$mydomain";

            $where[] = 'concat(`sysName`, \'.\', ?) =?';
            $params[] = "$mydomain";
            $params[] = "$name";
        }

        $sql = 'SELECT `device_id` FROM `devices` WHERE ' . implode(' OR ', $where) . ' LIMIT 2';
        $ids = dbFetchColumn($sql, $params);
        if (count($ids) == 1) {
            return (int) $ids[0];
        } elseif (count($ids) > 1) {
            d_echo("find_device_id: more than one device found with sysName '$name'.\n");
            // don't do anything, try other methods, if any
        }
    }

    return 0;
}

/**
 * Try to find a port by ifDescr, ifName, ifAlias, or MAC
 *
 * @param string $description matched against ifDescr, ifName, and ifAlias
 * @param string $identifier matched against ifDescr, ifName, and ifAlias
 * @param int $device_id restrict search to ports on a specific device
 * @param string $mac_address check against ifPhysAddress (should be in lowercase hexadecimal)
 * @return int
 */
function find_port_id($description, $identifier = '', $device_id = 0, $mac_address = null)
{
    if (! ($device_id || $mac_address)) {
        return 0;
    }

    $statements = [];
    $params = [];

    if ($device_id) {
        if ($description) {
            // order is important here, the standard says this is ifDescr, which some mfg confuse with ifName
            $statements[] = 'SELECT `port_id` FROM `ports` WHERE `device_id`=? AND (`ifDescr`=? OR `ifName`=?)';
            $params[] = $device_id;
            $params[] = $description;
            $params[] = $description;
        }

        if ($identifier) {
            if (is_numeric($identifier)) {
                $statements[] = 'SELECT `port_id` FROM `ports` WHERE `device_id`=? AND (`ifIndex`=? OR `ifAlias`=?)';
            } else {
                $statements[] = 'SELECT `port_id` FROM `ports` WHERE `device_id`=? AND (`ifDescr`=? OR `ifName`=?)';
            }
            $params[] = $device_id;
            $params[] = $identifier;
            $params[] = $identifier;
        }

        if ($description) {
            // we check ifAlias last because this is a user editable field, but some bad LLDP implementations use it
            $statements[] = 'SELECT `port_id` FROM `ports` WHERE `device_id`=? AND `ifAlias`=?';
            $params[] = $device_id;
            $params[] = $description;
        }
    }

    if ($mac_address) {
        $mac_statement = 'SELECT `port_id` FROM `ports` WHERE ';
        if ($device_id) {
            $mac_statement .= '`device_id`=? AND ';
            $params[] = $device_id;
        }
        $mac_statement .= '`ifPhysAddress`=?';

        $statements[] = $mac_statement;
        $params[] = $mac_address;
    }

    if (empty($statements)) {
        return 0;
    }

    $queries = implode(' UNION ', $statements);
    $sql = "SELECT * FROM ($queries LIMIT 1) p";

    return (int) dbFetchCell($sql, $params);
}
