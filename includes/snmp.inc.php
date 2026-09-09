<?php

/*
 * LibreNMS - SNMP Functions
 *
 * Original Observium code by: Adam Armstrong, Tom Laermans
 * Copyright (c) 2010-2012 Adam Armstrong.
 *
 * Additions for LibreNMS by Paul Gear
 * Copyright (c) 2014-2015 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use App\Events\SnmpQueryExecuted;
use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Mib;
use LibreNMS\Util\StringHelpers;

/**
 * Execute an SNMP CLI command and dispatch SnmpQueryExecuted event.
 *
 * @deprecated Please use SnmpQuery instead
 */
function snmp_exec(string $cmd, array|string|null $oids, array|string|null $options, ?array $device = null, array|string|null $mibs = null, ?string $mibdir = null): string
{
    $target = $device['overwrite_ip'] ?: $device['hostname'];
    $os = $device['os'] ?? 'generic';
    $oids = Arr::wrap($oids);

    $config = SnmpConfig::fromDeviceArray($device);

    $queryOptions = resolve(NetSnmpOptions::class)->parseCli($options);
    $queryOptions->context = $device['context_name'] ?? $queryOptions->context;
    $queryOptions->mibs = Mib::parseCliInput($mibs ?? '', $queryOptions->mibs);
    $queryOptions->mibDirs = Mib::directories($os, Mib::parseCliInput($mibdir ?? '', $queryOptions->mibDirs));
    if ($cmd === 'snmpwalk') {
        $queryOptions = $queryOptions->createPerWalkInstance($os, array_first($oids));
    }

    $snmp = resolve(SnmpBackendInterface::class);
    $response = match($cmd) {
        'snmpwalk' => $snmp->walk($target, array_first($oids), $config, $queryOptions),
        'snmpget' => $snmp->get($target, $oids, $config, $queryOptions),
        'snmpgetnext' => $snmp->next($target, $oids, $config, $queryOptions),
        default => throw new SnmpException('Unknown command: ' . $cmd),
    };

    event(new SnmpQueryExecuted(
        method: $cmd,
        oids: $oids,
        response: $response,
        cliCommand: $response->command,
        device: DeviceCache::get($device['device_id'] ?? DeviceCache::getPrimary()->device_id),
        context: $device['context_name'] ?? '',
        mibs: $queryOptions->mibs,
        mibDir: implode(':', $queryOptions->mibDirs),
    ));

    return $response->raw;
}

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmp_get_multi($device, $oids, $options = '-OQUs', $mib = null, $mibdir = null, $array = [])
{
    $measure = Measurement::start('snmpget');

    if (! is_array($oids)) {
        $oids = explode(' ', (string) $oids);
    }

    $data = trim(snmp_exec('snmpget', $oids, $options, $device, $mib, $mibdir));

    foreach (explode("\n", $data) as $entry) {
        if (! Str::contains($entry, ' =')) {
            if (! empty($entry) && isset($index, $oid)) {
                $array[$index][$oid] .= "\n$entry";
            }

            continue;
        }

        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value, "\" \n\r");
        [$oid, $index] = explode('.', $oid, 2);

        if (! Str::contains($value, 'at this OID')) {
            if (is_null($index)) {
                if (empty($oid)) {
                    continue; // no index or oid
                }
                $array[$oid] = $value;
            } else {
                $array[$index][$oid] = $value;
            }
        }
    }

    $measure->manager()->recordSnmp($measure->end());

    return $array;
}//end snmp_get_multi()

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmp_get_multi_oid($device, $oids, $options = '-OUQn', $mib = null, $mibdir = null)
{
    $measure = Measurement::start('snmpget');
    $oid_limit = get_device_oid_limit($device);

    if (! is_array($oids)) {
        $oids = explode(' ', (string) $oids);
    }

    $data = [];
    foreach (array_chunk($oids, $oid_limit) as $chunk) {
        $output = snmp_exec('snmpget', $chunk, $options, $device, $mib, $mibdir);
        $result = trim(str_replace('Wrong Type (should be OBJECT IDENTIFIER): ', '', $output));
        if ($result) {
            $data = array_merge($data, explode("\n", $result));
        }
    }

    $array = [];
    $oid = '';
    foreach ($data as $entry) {
        if (Str::contains($entry, '=')) {
            [$oid,$value] = explode('=', $entry, 2);
            $oid = trim($oid);
            $value = trim($value, "\\\" \n\r");
            if (! strstr($value, 'at this OID') && isset($oid)) {
                $array[$oid] = $value;
            }
        } else {
            if (isset($array[$oid])) {
                // if appending, add a line return
                $array[$oid] .= PHP_EOL . $entry;
            } else {
                $array[$oid] = $entry;
            }
        }
    }

    $measure->manager()->recordSnmp($measure->end());

    return $array;
}//end snmp_get_multi_oid()

/**
 * Simple snmpget, returns the output of the get or false if the get failed.
 *
 * @param  array  $device
 * @param  array|string  $oid
 * @param  array|string  $options
 * @param  string  $mib
 * @param  string  $mibdir
 * @return bool|string
 *
 * @deprecated Please use SnmpQuery instead
 */
function snmp_get($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $measure = Measurement::start('snmpget');

    if (strstr($oid, ' ')) {
        throw new Exception("snmp_get called for multiple OIDs: $oid");
    }

    $output = snmp_exec('snmpget', $oid, $options, $device, $mib, $mibdir);
    $output = str_replace('Wrong Type (should be OBJECT IDENTIFIER): ', '', $output);
    $data = trim($output, "\\\" \n\r");

    $measure->manager()->recordSnmp($measure->end());
    if (preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $data)) {
        return false;
    } elseif (preg_match('/Wrong Type(.*)should be/', $data)) {
        $data = preg_replace('/Wrong Type \(should be .*\): /', '', $data);

        return $data;
    } elseif ($data || $data === '0') {
        return $data;
    } else {
        return false;
    }
}//end snmp_get()

/**
 * Calls snmpgetnext.  Getnext returns the next oid after the specified oid.
 * For example instead of get sysName.0, you can getnext sysName to get the .0 value.
 *
 * @param  array  $device  Target device
 * @param  array|string  $oid  The oid to getnext
 * @param  array|string  $options  Options to pass to snmpgetnext (-Oqv for example)
 * @param  string  $mib  The MIB to use
 * @param  string  $mibdir  Optional mib directory to search
 * @return string|false the output or false if the data could not be fetched
 *
 * @deprecated Please use SnmpQuery instead
 */
function snmp_getnext($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $measure = Measurement::start('snmpgetnext');

    $snmpcmd = [LibrenmsConfig::get('snmpgetnext', 'snmpgetnext')];
    $data = trim(snmp_exec('snmpgetnext', $oid, $options, $device, $mib, $mibdir), "\" \n\r");

    $measure->manager()->recordSnmp($measure->end());
    if (preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $data)) {
        return false;
    } elseif ($data || $data === '0') {
        return $data;
    }

    return false;
}

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmp_walk($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $measure = Measurement::start('snmpwalk');

    $data = trim(snmp_exec('snmpwalk', $oid, $options, $device, $mib, $mibdir));

    $data = str_replace('"', '', $data);
    $data = str_replace('End of MIB', '', $data);

    if (is_string($data) && preg_match('/No Such (Object|Instance)/i', $data)) {
        d_echo('Invalid snmp_walk() data = ' . print_r($data, true));
        $data = false;
    } elseif (preg_match('/Wrong Type(.*)should be/', $data)) {
        $data = preg_replace('/Wrong Type \(should be .*\): /', '', $data);
    } else {
        if (Str::endsWith($data, '(It is past the end of the MIB tree)')) {
            $no_more_pattern = '/.*No more variables left in this MIB View \(It is past the end of the MIB tree\)[\n]?/';
            $data = preg_replace($no_more_pattern, '', $data);
        }
    }

    $measure->manager()->recordSnmp($measure->end());

    return $data;
}//end snmp_walk()

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmpwalk_cache_oid($device, $oid, $array = [], $mib = null, $mibdir = null, $snmpflags = '-OQUs')
{
    $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);

    if (empty($data)) {
        return $array;
    }

    $inferValueEncoding = ! StringHelpers::isValidUtf8($data);

    foreach (explode("\n", (string) $data) as $entry) {
        if (! Str::contains($entry, ' =')) {
            if (! empty($entry) && isset($index, $oid)) {
                $array[$index][$oid] .= "\n$entry";
            }

            continue;
        }

        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value, "\" \\\n\r");
        if ($inferValueEncoding) {
            $value = StringHelpers::inferEncoding($value);
        }
        $index = '';
        if (Str::contains($oid, '.')) {
            [$oid, $index] = explode('.', $oid, 2);
        }

        if (! strstr($value, 'at this OID') && ! empty($oid)) {
            $array[$index][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_oid()

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmpwalk_cache_multi_oid($device, $oid, $array = [], $mib = null, $mibdir = null, $snmpflags = '-OQUs')
{
    global $cache;

    if (! (is_array($cache['snmp'][$device['device_id']] ?? null) && array_key_exists($oid, $cache['snmp'][$device['device_id']]))) {
        $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);

        if (! empty($data)) {
            foreach (explode("\n", (string) $data) as $entry) {
                if (! Str::contains($entry, ' =')) {
                    if (! empty($entry) && isset($index, $r_oid)) {
                        $array[$index][$r_oid] .= "\n$entry"; // multi-line value, append to previous entry
                    }

                    continue;
                }

                [$r_oid,$value] = explode('=', $entry, 2);
                $r_oid = trim($r_oid);
                $value = trim($value);
                $oid_parts = explode('.', $r_oid);
                $r_oid = array_shift($oid_parts);
                $index = array_shift($oid_parts);
                foreach ($oid_parts as $tmp_oid) {
                    $index .= '.' . $tmp_oid;
                }

                if (! strstr($value, 'at this OID') && isset($r_oid) && isset($index)) {
                    $array[$index][$r_oid] = $value;
                }
            }//end foreach
        }

        $cache['snmp'][$device['device_id']][$oid] = $array;
    }//end if

    return $cache['snmp'][$device['device_id']][$oid];
}//end snmpwalk_cache_multi_oid()

/**
 * Walk an snmp mib oid and group items together based on the index.
 * This is intended to be used with a string based oid.
 * Any extra index data past $depth will be added after the oidName to keep grouping consistent.
 *
 * Example:
 * snmpwalk_group($device, 'ifTable', 'IF-MIB');
 * [
 *   1 => [ 'ifIndex' => '1', 'ifDescr' => 'lo', 'ifMtu' => '65536', ...],
 *   2 => [ 'ifIndex' => '2', 'ifDescr' => 'enp0s25', 'ifMtu' => '1500', ...],
 * ]
 *
 * @param  array  $device  Target device
 * @param  string  $oid  The string based oid to walk
 * @param  string  $mib  The MIB to use
 * @param  int  $depth  how many indexes to group
 * @param  array  $array  optionally insert the entries into an existing array (helpful for grouping multiple walks)
 * @param  string  $mibdir  custom mib dir to search for mib
 * @param  mixed  $snmpFlags  flags to use for the snmp command
 * @return array grouped array of data
 *
 * @deprecated Please use SnmpQuery instead
 */
function snmpwalk_group($device, $oid, $mib = '', $depth = 1, $array = [], $mibdir = null, $snmpFlags = '-OQUsetX')
{
    $data = rtrim(snmp_exec('snmpwalk', $oid, $snmpFlags, $device, $mib, $mibdir));

    if (empty($data)) {
        return $array;
    }

    $line = strtok($data, "\n");
    while ($line !== false) {
        if (Str::contains($line, 'at this OID') || Str::contains($line, 'this MIB View')) {
            $line = strtok("\n");
            continue;
        }

        [$address, $value] = explode(' =', $line, 2);
        preg_match_all('/([^[\]]+)/', $address, $parts);
        $parts = $parts[1];
        array_splice($parts, $depth, 0, array_shift($parts)); // move the oid name to the correct depth

        $line = strtok("\n"); // get the next line and concatenate multi-line values
        while ($line !== false && ! Str::contains($line, '=')) {
            $value .= $line . PHP_EOL;
            $line = strtok("\n");
        }

        // merge the parts into an array, creating keys if they don't exist
        $tmp = &$array;
        foreach ($parts as $part) {
            // we don't want to remove dots inside quotes, only outside
            $key = trim(trim($part, '.'), '"');
            $tmp = &$tmp[$key];
        }
        $tmp = trim($value, "\" \n\r"); // assign the value as the leaf
    }

    return $array;
}

/**
 * @deprecated Please use SnmpQuery instead
 */
function snmpwalk_cache_twopart_oid($device, $oid, $array = [], $mib = 0, $mibdir = null, $snmpflags = '-OQUs')
{
    $data = trim(snmp_exec('snmpwalk', $oid, $snmpflags, $device, $mib, $mibdir));

    if (empty($data)) {
        return $array;
    }

    foreach (explode("\n", $data) as $entry) {
        if (! Str::contains($entry, ' =')) {
            if (! empty($entry) && isset($first, $second, $oid)) {
                $array[$first][$second][$oid] .= "\n$entry"; // multi-line value, append to previous entry
            }

            continue;
        }

        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        $value = str_replace('"', '', $value);
        $parts = explode('.', $oid);
        if (! strstr($value, 'at this OID') && count($parts) >= 3) {
            [$oid, $first, $second] = $parts;
            $array[$first][$second][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_twopart_oid()

/**
 * SNMPWalk_array_num - performs a numeric SNMPWalk and returns an array containing $count indexes
 * One Index:
 *  From: 1.3.6.1.4.1.9.9.166.1.15.1.1.27.18.655360 = 0
 *  To: $array['1.3.6.1.4.1.9.9.166.1.15.1.1.27.18']['655360'] = 0
 * Two Indexes:
 *  From: 1.3.6.1.4.1.9.9.166.1.15.1.1.27.18.655360 = 0
 *  To: $array['1.3.6.1.4.1.9.9.166.1.15.1.1.27']['18']['655360'] = 0
 * And so on...
 * Think snmpwalk_cache_*_oid but for numeric data.
 *
 * Why is this useful?
 * Some SNMP data contains a single index (eg. ifIndex in IF-MIB) and some is dual indexed
 * (eg. PolicyIndex/ObjectsIndex in CISCO-CLASS-BASED-QOS-MIB).
 * The resulting array allows us to easily access the top level index we want and iterate over the data from there.
 *
 * @param  $device
 * @param  $OID
 * @param  int  $indexes
 *
 * @internal param $string
 *
 * @return bool|array
 *
 * @deprecated Please use SnmpQuery instead
 */
function snmpwalk_array_num($device, $oid, $indexes = 1)
{
    $array = [];
    $string = snmp_walk($device, $oid, '-Osqn');

    if ($string === false) {
        // False means: No Such Object.
        return false;
    }
    if ($string == '') {
        // Empty means SNMP timeout or some such.
        return null;
    }

    // Let's turn the string into something we can work with.
    foreach (explode("\n", (string) $string) as $line) {
        if ($line[0] == '.') {
            // strip the leading . if it exists.
            $line = substr($line, 1);
        }
        [$key, $value] = explode(' ', $line, 2);
        $prop_id = explode('.', $key);
        $value = trim($value);

        // if we have requested more levels that exist, set to the max.
        if ($indexes > count($prop_id)) {
            $indexes = count($prop_id) - 1;
        }

        for ($i = 0; $i < $indexes; $i++) {
            // Pop the index off.
            $index = array_pop($prop_id);
            $value = [$index => $value];
        }

        // Rebuild our key
        $key = implode('.', $prop_id);

        // Add the entry to the master array
        $array = array_replace_recursive($array, [$key => $value]);
    }

    return $array;
}
