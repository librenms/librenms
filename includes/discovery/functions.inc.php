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

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Models\Port;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\OS;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Number;
use LibreNMS\Util\UserFuncHelper;

/**
 * @param  string  $hostname
 * @param  array  $device
 * @param  string  $method  name of process discoverying this device
 * @param  array|null  $interface  Interface this device was discovered on
 * @return false|int
 *
 * @throws InvalidIpException
 */
function discover_new_device($hostname, $device, $method, $interface = null)
{
    Log::debug("discovering $hostname\n");

    if (IP::isValid($hostname)) {
        $ip = $hostname;
        if (! Config::get('discovery_by_ip', false)) {
            Log::debug('Discovery by IP disabled, skipping ' . $hostname);
            Eventlog::log("$method discovery of " . $hostname . ' failed - Discovery by IP disabled', $device['device_id'], 'discovery', Severity::Warning);

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
            Log::debug("name lookup of $hostname failed\n");
            Eventlog::log("$method discovery of " . $hostname . ' failed - Check name lookup', $device['device_id'], 'discovery', Severity::Error);

            return false;
        }
    } else {
        Log::debug("Discovery failed: '$hostname' is not a valid ip or dns name\n");

        return false;
    }

    Log::debug("ip lookup result: $ip\n");

    $hostname = rtrim($hostname, '.'); // remove trailing dot

    $ip = IP::parse($ip, true);
    if ($ip->inNetworks(Config::get('autodiscovery.nets-exclude'))) {
        Log::debug("$ip in an excluded network - skipping\n");

        return false;
    }

    if (! $ip->inNetworks(Config::get('nets'))) {
        Log::debug("$ip not in a matched network - skipping\n");

        return false;
    }

    try {
        $remote_device = new Device([
            'hostname' => $hostname,
            'poller_group' => $device['poller_group'],
        ]);
        $result = (new ValidateDeviceAndCreate($remote_device))->execute();

        if ($result) {
            echo '+[' . $remote_device->hostname . '(' . $remote_device->device_id . ')]';

            $extra_log = is_array($interface) ? ' (port ' . cleanPort($interface)['label'] . ') ' : '';
            Eventlog::log('Device ' . $remote_device->hostname . " ($ip) $extra_log autodiscovered through $method on " . $device['hostname'], $device['device_id'], 'discovery', Severity::Ok);

            return $remote_device->device_id;
        }

        Eventlog::log("$method discovery of " . $remote_device->hostname . " ($ip) failed - Check ping and SNMP access", $device['device_id'], 'discovery', Severity::Error);
    } catch (HostExistsException $e) {
        // already have this device
    } catch (Exception $e) {
        Eventlog::log("$method discovery of " . $hostname . " ($ip) failed - " . $e->getMessage(), $device['device_id'], 'discovery', Severity::Error);
    }

    return false;
}
//end discover_new_device()

/**
 * @param  array  $device  The device to poll
 * @param  bool  $force_module  Ignore device module overrides
 * @return bool if the device was discovered or skipped
 */
function discover_device(&$device, $force_module = false)
{
    DeviceCache::setPrimary($device['device_id']);
    App::forgetInstance('sensor-discovery');

    if ($device['snmp_disable'] == '1') {
        return true;
    }

    global $valid;

    $valid = [];

    // Start counting device poll time
    echo $device['hostname'] . ' ' . $device['device_id'] . ' ' . $device['os'] . ' ';

    $helper = new \LibreNMS\Polling\ConnectivityHelper(DeviceCache::getPrimary());

    if (! $helper->isUp()) {
        return false;
    }

    $discovery_modules = ['core' => true] + Config::get('discovery_modules', []);

    /** @var \App\Polling\Measure\MeasurementManager $measurements */
    $measurements = app(\App\Polling\Measure\MeasurementManager::class);
    $measurements->checkpoint(); // don't count previous stats

    foreach ($discovery_modules as $module => $module_status) {
        $os_module_status = Config::getOsSetting($device['os'], "discovery_modules.$module");
        $device_module_status = DeviceCache::getPrimary()->getAttrib('discover_' . $module);
        Log::debug('Modules status: Global' . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
        Log::debug('OS' . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
        Log::debug('Device' . ($device_module_status !== null ? ($device_module_status ? '+ ' : '- ') : '  '));
        if ($force_module === true ||
            $device_module_status ||
            ($os_module_status && $device_module_status === null) ||
            ($module_status && ! isset($os_module_status) && $device_module_status === null)
        ) {
            $module_start = microtime(true);
            $start_memory = memory_get_usage();
            echo "\n#### Load disco module $module ####\n";

            try {
                include "includes/discovery/$module.inc.php";
            } catch (Throwable $e) {
                // isolate module exceptions so they don't disrupt the polling process
                Log::error("%rError discovering $module module for {$device['hostname']}.%n $e", ['color' => true]);
                Eventlog::log("Error discovering $module module. Check log file for more details.", $device['device_id'], 'discovery', Severity::Error);
                report($e);

                // Re-throw exception if we're in CI
                if (getenv('CI') == true) {
                    throw $e;
                }
            }

            $module_time = microtime(true) - $module_start;
            $module_time = substr($module_time, 0, 5);
            $module_mem = (memory_get_usage() - $start_memory);
            printf("\n>> Runtime for discovery module '%s': %.4f seconds with %s bytes\n", $module, $module_time, $module_mem);
            $measurements->printChangedStats();
            echo "#### Unload disco module $module ####\n\n";
        } elseif ($device_module_status == '0') {
            echo "Module [ $module ] disabled on host.\n\n";
        } elseif (isset($os_module_status) && $os_module_status == '0') {
            echo "Module [ $module ] disabled on os.\n\n";
        } else {
            echo "Module [ $module ] disabled globally.\n\n";
        }
    }

    return true;
}
//end discover_device()

// Discover sensors
function discover_sensor($unused, $class, $device, $oid, $index, $type, $descr, $divisor = 1, $multiplier = 1, $low_limit = null, $low_warn_limit = null, $warn_limit = null, $high_limit = null, $current = null, $poller_type = 'snmp', $entPhysicalIndex = null, $entPhysicalIndex_measured = null, $user_func = null, $group = null, $rrd_type = 'GAUGE'): bool
{
    $low_limit = set_null($low_limit);
    $low_warn_limit = set_null($low_warn_limit);
    $warn_limit = set_null($warn_limit);
    $high_limit = set_null($high_limit);
    $current = Number::cast($current);

    if (! is_numeric($divisor)) {
        $divisor = 1;
    }

    app('sensor-discovery')->discover(new \App\Models\Sensor([
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
        'rrd_type' => $rrd_type,
    ]));

    return true;
}

function discover_juniAtmVp(&$valid, $device, $port_id, $vp_id, $vp_descr)
{
    Log::debug("Discover Juniper ATM VP: $port_id, $vp_id, $vp_descr\n");

    if (dbFetchCell('SELECT COUNT(*) FROM `juniAtmVp` WHERE `port_id` = ? AND `vp_id` = ?', [$port_id, $vp_id]) == '0') {
        $inserted = dbInsert(['port_id' => $port_id, 'vp_id' => $vp_id, 'vp_descr' => $vp_descr], 'juniAtmVp');
        Log::debug("( $inserted inserted )\n");

        // FIXME vv no $device!
        Eventlog::log('Juniper ATM VP Added: port ' . $port_id . ' vp ' . $vp_id . ' descr' . $vp_descr, $device, 'juniAtmVp', 3, $inserted);
    } else {
        echo '.';
    }

    $valid[$port_id][$vp_id] = 1;
}

//end discover_juniAtmVp()

function discover_link($local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $local_device_id, $remote_device_id)
{
    global $link_exists;

    Log::debug("Discover link: $local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $remote_device_id\n");

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
        Log::debug("( $inserted inserted )");
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
            Log::debug("( $updated updated )");
        }//end if
    }//end if
    $link_exists[$local_port_id][$remote_hostname][$remote_port] = 1;
}

//end discover_link()

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

    $port_id = Port::where([
        ['device_id', $device['device_id']],
        ['ifIndex', $ifIndex],
    ])->value('port_id');

    if ($port_id && $ipv6_prefixlen > '0' && $ipv6_prefixlen < '129' && $ipv6_compressed != '::1') {
        Log::debug('IPV6: Found port id: ' . $port_id);

        $ipv6netDB = Ipv6Network::updateOrCreate([
            'ipv6_network' => $ipv6_network,
        ], [
            'context_name' => $context_name,
        ]);

        if ($ipv6netDB->wasChanged()) {
            Log::debug('IPV6: Update DB ipv6_networks');
        }

        $ipv6_network_id = Ipv6Network::where('ipv6_network', $ipv6_network)->where('context_name', $context_name)->value('ipv6_network_id');

        if ($ipv6_network_id) {
            Log::debug('IPV6: Found network id: ' . $ipv6_network_id);

            $ipv6adrDB = Ipv6Address::updateOrCreate([
                'ipv6_address' => $ipv6_address,
                'ipv6_prefixlen' => $ipv6_prefixlen,
                'port_id' => $port_id,
            ], [
                'ipv6_compressed' => $ipv6_compressed,
                'ipv6_origin' => $ipv6_origin,
                'ipv6_network_id' => $ipv6_network_id,
                'context_name' => $context_name,
            ]);

            if ($ipv6adrDB->wasChanged()) {
                Log::debug('IPV6: Update DB ipv6_addresses');
            }

            $full_address = "$ipv6_address/$ipv6_prefixlen";
            $valid_address = $full_address . '-' . $port_id;
            $valid['ipv6'][$valid_address] = 1;
        }//endif network_id
    }//endif port_id && others
}//end discover_process_ipv6()

/**
 * create or update IPv4 Addresses and/or IPv4 Networks
 *
 * @param  pointer  $valid_v4
 * @param  array  $device
 * @param  int  $ifIndex
 * @param  string  $ipv4_address
 * @param  string  $mask
 * @param  string  $context_name
 * @return array
 *
 * @throws InvalidIpException
 */
function discover_process_ipv4(&$valid_v4, $device, int $ifIndex, $ipv4_address, $mask, $context_name = '')
{
    $cidr = IPv4::netmask2cidr($mask);
    try {
        $ipv4 = new IPv4($ipv4_address . '/' . $cidr);
    } catch (InvalidIpException $e) {
        Log::debug('Invalid data: ' . $ipv4_address);

        return;
    }
    $ipv4_network = $ipv4->getNetworkAddress() . '/' . $ipv4->cidr;

    if ($ipv4_address != '0.0.0.0' && $ifIndex > 0) {
        $port_id = \App\Facades\PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

        if (is_numeric($port_id)) {
            $dbIpv4Net = Ipv4Network::updateOrCreate([
                'ipv4_network' => $ipv4_network,
            ], [
                'context_name' => $device['context_name'],
            ]);

            if (! $dbIpv4Net->wasRecentlyCreated && $dbIpv4Net->wasChanged()) {
                Eventlog::log('IPv4 network ' . $ipv4_network . ' changed', $device['device_id'], 'ipv4', Severity::Warning);
                echo 'Nu';
            }
            if ($dbIpv4Net->wasRecentlyCreated) {
                Eventlog::log('IPv4 network ' . $ipv4_network . ' created', $device['device_id'], 'ipv4', Severity::Notice);
                echo 'N+';
            }

            $ipv4_network_id = Ipv4Network::where('ipv4_network', $ipv4_network)->value('ipv4_network_id');
            $dbIpv4Addr = Ipv4Address::updateOrCreate([
                'ipv4_address' => $ipv4_address,
                'ipv4_prefixlen' => $cidr,
                'ipv4_network_id' => $ipv4_network_id,
                'port_id' => $port_id,
            ], [
                'context_name' => $device['context_name'],
            ]);

            if (! $dbIpv4Addr->wasRecentlyCreated && $dbIpv4Addr->wasChanged()) {
                Eventlog::log('IPv4 address ' . $ipv4_address . '/' . $cidr . ' changed', $device['device_id'], 'ipv4', Severity::Warning);
                echo 'Au';
            }
            if ($dbIpv4Addr->wasRecentlyCreated) {
                Eventlog::log('IPv4 address ' . $ipv4_address . '/' . $cidr . ' created', $device['device_id'], 'ipv4', Severity::Notice);
                echo 'A+';
            }
            $full_address = $ipv4_address . '/' . $cidr . '|' . $ifIndex;
            $valid_v4[$full_address] = 1;
        } else {
            Log::debug('No port id found for ifindex: ' . $ifIndex . PHP_EOL);
        }
    }
}
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
            Log::debug("Ignored entity sensor: $bad : $string");

            return false;
        }
    }

    return true;
}

/**
 * Get the device divisor, account for device specific quirks
 * The default divisor is 10
 *
 * @param  array  $device  device array
 * @param  string  $os_version  firmware version poweralert quirks
 * @param  string  $sensor_type  the type of this sensor
 * @param  string  $oid  the OID of this sensor
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
            if ($device['os'] == 'routeros' && $device['version'] && version_compare($device['version'], '6.47', '<')) {
                return 60;
            }

            return 1;
        }
    }

    return 10;
}

/**
 * @param  OS  $os
 * @param  $sensor_type
 * @param  $pre_cache
 */
function discovery_process($os, $sensor_class, $pre_cache)
{
    $discovery = $os->getDiscovery('sensors');
    $device = $os->getDeviceArray();

    if (! empty($discovery[$sensor_class]) && ! app('sensor-discovery')->canSkip(new \App\Models\Sensor(['sensor_class' => $sensor_class]))) {
        $sensor_options = [];
        if (isset($discovery[$sensor_class]['options'])) {
            $sensor_options = $discovery[$sensor_class]['options'];
        }

        Log::debug("Dynamic Discovery ($sensor_class): ");
        Log::debug($discovery[$sensor_class]);

        foreach ($discovery[$sensor_class]['data'] as $data) {
            $tmp_name = $data['oid'];

            if (! isset($pre_cache[$tmp_name])) {
                continue;
            }

            $raw_data = (array) $pre_cache[$tmp_name];

            Log::debug("Data $tmp_name: ");
            Log::debug($raw_data);
            $count = 0;

            foreach ($raw_data as $index => $snmp_data) {
                $count++;
                $user_function = null;
                if (isset($data['user_func'])) {
                    $user_function = $data['user_func'];
                }
                // get the value for this sensor, check 'value' and 'oid', if state string, translate to a number
                $data['value'] = isset($data['value']) ? $data['value'] : $data['oid'];  // fallback to oid if value is not set

                $snmp_value = $snmp_data[$data['value']] ?? '';
                if (! is_numeric($snmp_value)) {
                    if ($sensor_class === 'temperature') {
                        // For temp sensors, try and detect fahrenheit values
                        if (is_string($snmp_value) && Str::endsWith($snmp_value, ['f', 'F'])) {
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
                        $data['num_oid'] = YamlDiscovery::computeNumericalOID($os, $data);
                    } catch (\Exception $e) {
                        Log::debug('Error: We cannot find a numerical OID for ' . $data['value'] . '. Skipping this one...');
                        $skippedFromYaml = true;
                        // Because we don't have a num_oid, we have no way to add this sensor.
                    }
                }

                if ($skippedFromYaml === false && is_numeric($value)) {
                    Log::debug("Sensor fetched value: $value\n");

                    // process the oid (num_oid will contain index or str2num replacement calls)
                    $oid = trim(YamlDiscovery::replaceValues('num_oid', $index, null, $data, []));

                    // process the description
                    $descr = trim(YamlDiscovery::replaceValues('descr', $index, null, $data, $pre_cache));

                    // process the group
                    $group = trim(YamlDiscovery::replaceValues('group', $index, null, $data, $pre_cache)) ?: null;

                    // process the divisor - cannot be 0
                    if (isset($data['divisor'])) {
                        $divisor = (int) YamlDiscovery::replaceValues('divisor', $index, $count, $data, $pre_cache);
                    } elseif (isset($sensor_options['divisor'])) {
                        $divisor = (int) $sensor_options['divisor'];
                    } else {
                        $divisor = 1;
                    }
                    if ($divisor == 0) {
                        Log::warning('Divisor is not a nonzero number, defaulting to 1');
                        $divisor = 1;
                    }

                    // process the multiplier - zero is valid
                    if (isset($data['multiplier'])) {
                        $multiplier = YamlDiscovery::replaceValues('multiplier', $index, $count, $data, $pre_cache);
                    } elseif (isset($sensor_options['multiplier'])) {
                        $multipler = $sensor_options['multiplier'];
                    } else {
                        $multiplier = 1;
                    }
                    if (is_numeric($multiplier)) {
                        $multiplier = (int) $multiplier;
                    } else {
                        Log::warning('Multiplier $multiplier is not a valid number, defaulting to 1');
                        $multiplier = 1;
                    }

                    // process the limits
                    $limits = ['low_limit', 'low_warn_limit', 'warn_limit', 'high_limit'];
                    foreach ($limits as $limit) {
                        if (isset($data[$limit]) && is_numeric($data[$limit])) {
                            $$limit = $data[$limit];
                        } else {
                            $$limit = YamlDiscovery::getValueFromData($limit, $index, $data, $pre_cache, 'null');
                            if (is_numeric($$limit)) {
                                $$limit = ($$limit / $divisor) * $multiplier;
                            }
                            if (is_numeric($$limit) && isset($user_function)) {
                                if (is_callable($user_function)) {
                                    $$limit = $user_function($$limit);
                                } else {
                                    $$limit = (new UserFuncHelper($$limit))->{$user_function}();
                                }
                            }
                        }
                    }

                    $sensor_name = $device['os'];

                    if ($sensor_class === 'state') {
                        $sensor_name = $data['state_name'] ?? $data['oid'];
                        create_state_index($sensor_name, $data['states']);
                    } else {
                        // We default to 1 for both divisors / multipliers so it should be safe to do the calculation using both.
                        $value = ($value / $divisor) * $multiplier;
                    }

                    $entPhysicalIndex = YamlDiscovery::replaceValues('entPhysicalIndex', $index, null, $data, $pre_cache) ?: null;
                    $entPhysicalIndex_measured = isset($data['entPhysicalIndex_measured']) ? $data['entPhysicalIndex_measured'] : null;

                    //user_func must be applied after divisor/multiplier
                    if (isset($user_function)) {
                        if (is_callable($user_function)) {
                            $value = $user_function($value);
                        } else {
                            $value = (new UserFuncHelper($value, $snmp_data[$data['value']], $data))->{$user_function}();
                        }
                    }

                    $uindex = $index;
                    if (isset($data['index'])) {
                        if (Str::contains($data['index'], '{{')) {
                            $uindex = trim(YamlDiscovery::replaceValues('index', $index, null, $data, $pre_cache));
                        } else {
                            $uindex = $data['index'];
                        }
                    }

                    discover_sensor(null, $sensor_class, $device, $oid, $uindex, $sensor_name, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, $user_function, $group, $data['rrd_type'] ?? 'GAUGE');
                }
            }
        }
    }
}

/**
 * @param  $types
 * @param  OS  $os
 * @param  array  $pre_cache
 */
function sensors($types, $os, $pre_cache = [])
{
    $device = &$os->getDeviceArray();
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
        discovery_process($os, $sensor_class, $pre_cache);
        app('sensor-discovery')->sync(sensor_class: $sensor_class, poller_type: 'snmp');
        echo "\n";
    }
}

function build_bgp_peers($device, $data, $peer2)
{
    Log::debug("Peers : $data\n");
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
    foreach ($peers ? explode("\n", $peers) : [] as $peer) {
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
            Log::debug("Found peer $peer_ip (AS$peer_as)\n");
            $peerlist[] = [
                'ip' => $peer_ip,
                'as' => $peer_as,
                'localip' => $local_ip ?: '0.0.0.0',
                'ver' => $ver,
            ];
        }
    }

    return $peerlist;
}

function build_cbgp_peers($device, $peer, $af_data, $peer2)
{
    Log::debug('afi data :: ');
    Log::debug($af_data);

    $af_list = [];
    foreach ($af_data as $k => $v) {
        if ($peer2 === true) {
            [,$k] = explode('.', $k, 2);
        }

        Log::debug("AFISAFI = $k\n");

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
 * check if we should skip this device from discovery
 *
 * @param  string  $sysName
 * @param  string  $sysDescr
 * @param  string  $platform
 * @return bool
 */
function can_skip_discovery($sysName, $sysDescr = '', $platform = '')
{
    if ($sysName) {
        foreach ((array) Config::get('autodiscovery.xdp_exclude.sysname_regexp') as $needle) {
            if (preg_match($needle . 'i', $sysName)) {
                Log::debug("$sysName - regexp '$needle' matches '$sysName' - skipping device discovery \n");

                return true;
            }
        }
    }

    if ($sysDescr) {
        foreach ((array) Config::get('autodiscovery.xdp_exclude.sysdesc_regexp') as $needle) {
            if (preg_match($needle . 'i', $sysDescr)) {
                Log::debug("$sysName - regexp '$needle' matches '$sysDescr' - skipping device discovery \n");

                return true;
            }
        }
    }

    if ($platform) {
        foreach ((array) Config::get('autodiscovery.cdp_exclude.platform_regexp') as $needle) {
            if (preg_match($needle . 'i', $platform)) {
                Log::debug("$sysName - regexp '$needle' matches '$platform' - skipping device discovery \n");

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
 * @param  string  $name  sysName or hostname
 * @param  string  $ip  May be an IP or hex string
 * @param  string  $mac_address
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
            Log::debug("find_device_id: more than one device found with sysName '$name'.\n");
            // don't do anything, try other methods, if any
        }
    }

    return 0;
}

/**
 * Try to find a port by ifDescr, ifName, ifAlias, or MAC
 *
 * @param  string  $description  matched against ifDescr, ifName, and ifAlias
 * @param  string  $identifier  matched against ifDescr, ifName, and ifAlias
 * @param  int  $device_id  restrict search to ports on a specific device
 * @param  string  $mac_address  check against ifPhysAddress (should be in lowercase hexadecimal)
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
