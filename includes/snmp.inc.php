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

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\Debug;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

function string_to_oid($string)
{
    $oid = strlen($string);
    for ($i = 0; $i != strlen($string); $i++) {
        $oid .= '.' . ord($string[$i]);
    }

    return $oid;
}//end string_to_oid()

function prep_snmp_setting($device, $setting)
{
    if (isset($device[$setting]) && is_numeric($device[$setting]) && $device[$setting] > 0) {
        return $device[$setting];
    }

    return Config::get("snmp.$setting");
}//end prep_snmp_setting()

/**
 * @param array $device
 * @return array will contain a list of mib dirs
 */
function get_mib_dir($device)
{
    $dirs = [];

    if (file_exists(Config::get('mib_dir') . '/' . $device['os'])) {
        $dirs[] = Config::get('mib_dir') . '/' . $device['os'];
    }

    if (isset($device['os_group'])) {
        if (file_exists(Config::get('mib_dir') . '/' . $device['os_group'])) {
            $dirs[] = Config::get('mib_dir') . '/' . $device['os_group'];
        }

        if ($group_mibdir = Config::get("os_groups.{$device['os_group']}.mib_dir")) {
            if (is_array($group_mibdir)) {
                foreach ($group_mibdir as $k => $dir) {
                    $dirs[] = Config::get('mib_dir') . '/' . $dir;
                }
            }
        }
    }

    if ($os_mibdir = Config::get("os.{$device['os']}.mib_dir")) {
        $dirs[] = Config::get('mib_dir') . '/' . $os_mibdir;
    }

    return $dirs;
}

/**
 * Generate the mib search directory argument for snmpcmd
 * If null return the default mib dir
 * If $mibdir is empty '', return an empty string
 *
 * @param string $mibdir should be the name of the directory within \LibreNMS\Config::get('mib_dir')
 * @param array|null $device
 * @return string The option string starting with -M
 */
function mibdir($mibdir = null, $device = null)
{
    $dirs = is_array($device) ? get_mib_dir($device) : [];

    $base = Config::get('mib_dir');
    $dirs[] = "$base/$mibdir";

    // make sure base directory is included first
    array_unshift($dirs, $base);

    // remove trailing /, remove empty dirs, and remove duplicates
    $dirs = array_unique(array_filter(array_map(function ($dir) {
        return rtrim($dir, '/');
    }, $dirs)));

    return implode(':', $dirs);
}//end mibdir()

/**
 * Generate an snmpget command
 *
 * @param array $device the we will be connecting to
 * @param array|string $oids the oids to fetch, separated by spaces
 * @param array|string $options extra snmp command options, usually this is output options
 * @param string $mib an additional mib to add to this command
 * @param string $mibdir a mib directory to search for mibs, usually prepended with +
 * @return array the fully assembled command, ready to run
 */
function gen_snmpget_cmd($device, $oids, $options = null, $mib = null, $mibdir = null)
{
    $snmpcmd = [Config::get('snmpget')];

    return gen_snmp_cmd($snmpcmd, $device, $oids, $options, $mib, $mibdir);
} // end gen_snmpget_cmd()

/**
 * Generate an snmpwalk command
 *
 * @param array $device the we will be connecting to
 * @param array|string $oids the oids to fetch, separated by spaces
 * @param array|string $options extra snmp command options, usually this is output options
 * @param string $mib an additional mib to add to this command
 * @param string $mibdir a mib directory to search for mibs, usually prepended with +
 * @return array the fully assembled command, ready to run
 */
function gen_snmpwalk_cmd($device, $oids, $options = null, $mib = null, $mibdir = null, $strIndexing = null)
{
    if ($device['snmpver'] == 'v1' || (isset($device['os']) && Config::getOsSetting($device['os'], 'nobulk'))) {
        $snmpcmd = [Config::get('snmpwalk')];
    } else {
        $snmpcmd = [Config::get('snmpbulkwalk')];
        $max_repeaters = get_device_max_repeaters($device);
        if ($max_repeaters > 0) {
            $snmpcmd[] = "-Cr$max_repeaters";
        }
    }

    return gen_snmp_cmd($snmpcmd, $device, $oids, $options, $mib, $mibdir, $strIndexing);
} //end gen_snmpwalk_cmd()

/**
 * Generate an snmp command
 *
 * @param array $cmd the snmp command to run, like snmpget plus any additional arguments in an array
 * @param array $device the we will be connecting to
 * @param array|string $oids the oids to fetch, separated by spaces
 * @param array|string $options extra snmp command options, usually this is output options
 * @param string $mib an additional mib to add to this command
 * @param string $mibdir a mib directory to search for mibs, usually prepended with +
 * @return array the fully assembled command, ready to run
 */
function gen_snmp_cmd($cmd, $device, $oids, $options = null, $mib = null, $mibdir = null, $strIndexing = null)
{
    if (! isset($device['transport'])) {
        $device['transport'] = 'udp';
    }

    $cmd = snmp_gen_auth($device, $cmd, $strIndexing);
    $cmd = $options ? array_merge($cmd, (array) $options) : $cmd;
    if ($mib) {
        array_push($cmd, '-m', $mib);
    }
    array_push($cmd, '-M', mibdir($mibdir, $device));

    $timeout = prep_snmp_setting($device, 'timeout');
    if ($timeout && $timeout !== 1) {
        array_push($cmd, '-t', $timeout);
    }

    $retries = prep_snmp_setting($device, 'retries');
    if ($retries && $retries !== 5) {
        array_push($cmd, '-r', $retries);
    }

    $pollertarget = Device::pollerTarget($device);
    $cmd[] = $device['transport'] . ':' . $pollertarget . ':' . $device['port'];
    $cmd = array_merge($cmd, (array) $oids);

    return $cmd;
} // end gen_snmp_cmd()

function snmp_get_multi($device, $oids, $options = '-OQUs', $mib = null, $mibdir = null, $array = [])
{
    $time_start = microtime(true);

    if (! is_array($oids)) {
        $oids = explode(' ', $oids);
    }

    $cmd = gen_snmpget_cmd($device, $oids, $options, $mib, $mibdir);
    $data = trim(external_exec($cmd));

    foreach (explode("\n", $data) as $entry) {
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

    recordSnmpStatistic('snmpget', $time_start);

    return $array;
}//end snmp_get_multi()

function snmp_get_multi_oid($device, $oids, $options = '-OUQn', $mib = null, $mibdir = null)
{
    $time_start = microtime(true);
    $oid_limit = get_device_oid_limit($device);

    if (! is_array($oids)) {
        $oids = explode(' ', $oids);
    }

    $data = [];
    foreach (array_chunk($oids, $oid_limit) as $chunk) {
        $output = external_exec(gen_snmpget_cmd($device, $chunk, $options, $mib, $mibdir));
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

    recordSnmpStatistic('snmpget', $time_start);

    return $array;
}//end snmp_get_multi_oid()

/**
 * Simple snmpget, returns the output of the get or false if the get failed.
 *
 * @param array $device
 * @param array|string $oid
 * @param array|string $options
 * @param string $mib
 * @param string $mibdir
 * @return bool|string
 */
function snmp_get($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $time_start = microtime(true);

    if (strstr($oid, ' ')) {
        throw new Exception("snmp_get called for multiple OIDs: $oid");
    }

    $output = external_exec(gen_snmpget_cmd($device, $oid, $options, $mib, $mibdir));
    $output = str_replace('Wrong Type (should be OBJECT IDENTIFIER): ', '', $output);
    $data = trim($output, "\\\" \n\r");

    recordSnmpStatistic('snmpget', $time_start);
    if (preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $data)) {
        return false;
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
 * @param array $device Target device
 * @param array|string $oid The oid to getnext
 * @param array|string $options Options to pass to snmpgetnext (-Oqv for example)
 * @param string $mib The MIB to use
 * @param string $mibdir Optional mib directory to search
 * @return string|false the output or false if the data could not be fetched
 */
function snmp_getnext($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $time_start = microtime(true);

    $snmpcmd = [Config::get('snmpgetnext', 'snmpgetnext')];
    $cmd = gen_snmp_cmd($snmpcmd, $device, $oid, $options, $mib, $mibdir);
    $data = trim(external_exec($cmd), "\" \n\r");

    recordSnmpStatistic('snmpgetnext', $time_start);
    if (preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $data)) {
        return false;
    } elseif ($data || $data === '0') {
        return $data;
    }

    return false;
}

/**
 * Calls snmpgetnext for multiple OIDs.  Getnext returns the next oid after the specified oid.
 * For example instead of get sysName.0, you can getnext sysName to get the .0 value.
 *
 * @param array $device Target device
 * @param array $oids The oids to getnext
 * @param string $options Options to pass to snmpgetnext (-OQUs for example)
 * @param string $mib The MIB to use
 * @param string $mibdir Optional mib directory to search
 * @return array|false the output or false if the data could not be fetched
 */
function snmp_getnext_multi($device, $oids, $options = '-OQUs', $mib = null, $mibdir = null, $array = [])
{
    $time_start = microtime(true);
    if (! is_array($oids)) {
        $oids = explode(' ', $oids);
    }
    $snmpcmd = [Config::get('snmpgetnext', 'snmpgetnext')];
    $cmd = gen_snmp_cmd($snmpcmd, $device, $oids, $options, $mib, $mibdir);
    $data = trim(external_exec($cmd), "\" \n\r");

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value, "\" \n\r");
        [$oid, $index] = explode('.', $oid, 2);
        if (! Str::contains($value, 'at this OID')) {
            if (empty($oid)) {
                continue; // no index or oid
            } else {
                $array[$oid] = $value;
            }
        }
    }
    recordSnmpStatistic('snmpgetnext', $time_start);

    return $array;
}//end snmp_getnext_multi()

/**
 * @param $device
 * @return bool
 */
function snmp_check($device)
{
    $time_start = microtime(true);

    try {
        $oid = '.1.3.6.1.2.1.1.2.0';
        $cmd = gen_snmpget_cmd($device, $oid, '-Oqvn');
        $proc = new \Symfony\Component\Process\Process($cmd);
        $proc->run();
        $code = $proc->getExitCode();
        Log::debug("SNMP Check response code: $code");
    } catch (ProcessTimedOutException $e) {
        Log::debug("Device didn't respond to snmpget before {$e->getExceededTimeout()}s timeout");
    }

    recordSnmpStatistic('snmpget', $time_start);

    if ($code === 0) {
        return true;
    }

    return false;
}//end snmp_check()

function snmp_walk($device, $oid, $options = null, $mib = null, $mibdir = null)
{
    $time_start = microtime(true);

    $cmd = gen_snmpwalk_cmd($device, $oid, $options, $mib, $mibdir);
    $data = trim(external_exec($cmd));

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

    recordSnmpStatistic('snmpwalk', $time_start);

    return $data;
}//end snmp_walk()

function snmpwalk_cache_cip($device, $oid, $array = [], $mib = 0)
{
    $cmd = gen_snmpwalk_cmd($device, $oid, '-OsnQ', $mib);
    $data = trim(external_exec($cmd));

    // echo("Caching: $oid\n");
    foreach (explode("\n", $data) as $entry) {
        [$this_oid, $this_value] = preg_split('/=/', $entry);
        $this_oid = trim($this_oid);
        $this_value = trim($this_value);
        $this_oid = substr($this_oid, 30);
        [$ifIndex, $dir, $a, $b, $c, $d, $e, $f] = explode('.', $this_oid);
        $h_a = zeropad(dechex($a));
        $h_b = zeropad(dechex($b));
        $h_c = zeropad(dechex($c));
        $h_d = zeropad(dechex($d));
        $h_e = zeropad(dechex($e));
        $h_f = zeropad(dechex($f));
        $mac = "$h_a$h_b$h_c$h_d$h_e$h_f";

        if ($dir == '1') {
            $dir = 'input';
        } elseif ($dir == '2') {
            $dir = 'output';
        }

        if ($mac && $dir) {
            $array[$ifIndex][$mac][$oid][$dir] = $this_value;
        }
    }//end foreach

    return $array;
}//end snmpwalk_cache_cip()

function snmp_cache_ifIndex($device)
{
    // FIXME: this is not yet using our own snmp_*
    $cmd = gen_snmpwalk_cmd($device, 'ifIndex', '-OQs', 'IF-MIB');
    $data = trim(external_exec($cmd));

    $array = [];
    foreach (explode("\n", $data) as $entry) {
        [$this_oid, $this_value] = preg_split('/=/', $entry);
        [$this_oid, $this_index] = explode('.', $this_oid, 2);
        $this_index = trim($this_index);
        $this_oid = trim($this_oid);
        $this_value = trim($this_value);
        if (! strstr($this_value, 'at this OID') && $this_index) {
            $array[] = $this_value;
        }
    }

    return $array;
}//end snmp_cache_ifIndex()

function snmpwalk_cache_oid($device, $oid, $array, $mib = null, $mibdir = null, $snmpflags = '-OQUs')
{
    $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);
    foreach (explode("\n", $data) as $entry) {
        if (! Str::contains($entry, ' =') && ! empty($entry) && isset($index, $oid)) {
            $array[$index][$oid] .= "\n$entry";
            continue;
        }

        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value, "\" \\\n\r");
        [$oid, $index] = explode('.', $oid, 2);
        if (! strstr($value, 'at this OID') && ! empty($oid)) {
            $array[$index][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_oid()

function snmpwalk_cache_numerical_oid($device, $oid, $array, $mib = null, $mibdir = null, $snmpflags = '-OQUsn')
{
    $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);
    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        [$index,] = explode('.', strrev($oid), 2);
        if (! strstr($value, 'at this OID') && isset($oid) && isset($index)) {
            $array[$index][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_oid()

function snmpwalk_cache_long_oid($device, $oid, $noid, $array, $mib = null, $mibdir = null, $snmpflags = '-OQnU')
{
    $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);

    if (empty($data)) {
        return $array;
    }

    foreach (explode("\n", $data) as $entry) {
        [$tmp_oid,$value] = explode('=', $entry, 2);
        $tmp_oid = trim($tmp_oid);
        $value = trim($value);
        $tmp_index = str_replace($noid, '', $tmp_oid);
        $index = md5($tmp_index);
        if (! empty($index) && ! empty($oid)) {
            $array[$index][$oid] = $value;
            if (empty($array[$index]['orig'])) {
                $array[$index]['orig'] = $tmp_index;
            }
        }
    }

    return $array;
}//end snmpwalk_cache_oid()

/**
 * Just like snmpwalk_cache_oid except that it returns the numerical oid as the index
 * this is useful when the oid is indexed by the mac address and snmpwalk would
 * return periods (.) for non-printable numbers, thus making many different indexes appear
 * to be the same.
 *
 * @param array $device
 * @param string $oid
 * @param array $array Pass an array to add the cache to, useful for multiple calls
 * @param string $mib
 * @param string $mibdir
 * @return bool|array
 */
function snmpwalk_cache_oid_num($device, $oid, $array, $mib = null, $mibdir = null)
{
    return snmpwalk_cache_oid($device, $oid, $array, $mib, $mibdir, $snmpflags = '-OQUn');
}//end snmpwalk_cache_oid_num()

function snmpwalk_cache_multi_oid($device, $oid, $array, $mib = null, $mibdir = null, $snmpflags = '-OQUs')
{
    global $cache;

    if (! (is_array($cache['snmp'][$device['device_id']]) && array_key_exists($oid, $cache['snmp'][$device['device_id']]))) {
        $data = snmp_walk($device, $oid, $snmpflags, $mib, $mibdir);
        foreach (explode("\n", $data) as $entry) {
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

        $cache['snmp'][$device['device_id']][$oid] = $array;
    }//end if

    return $cache['snmp'][$device['device_id']][$oid];
}//end snmpwalk_cache_multi_oid()

function snmpwalk_cache_double_oid($device, $oid, $array, $mib = null, $mibdir = null)
{
    $data = snmp_walk($device, $oid, '-OQUs', $mib, $mibdir);

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        [$oid, $first, $second] = explode('.', $oid);
        if (! strstr($value, 'at this OID') && isset($oid) && isset($first) && isset($second)) {
            $double = $first . '.' . $second;
            $array[$double][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_double_oid()

function snmpwalk_cache_index($device, $oid, $array, $mib = null, $mibdir = null)
{
    $data = snmp_walk($device, $oid, '-OQUs', $mib, $mibdir);

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        [$oid, $first] = explode('.', $oid);
        if (! strstr($value, 'at this OID') && isset($oid) && isset($first)) {
            $array[$oid][$first] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_double_oid()

function snmpwalk_cache_triple_oid($device, $oid, $array, $mib = null, $mibdir = null)
{
    $data = snmp_walk($device, $oid, '-OQUs', $mib, $mibdir);

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        [$oid, $first, $second, $third] = explode('.', $oid);
        if (! strstr($value, 'at this OID') && isset($oid) && isset($first) && isset($second)) {
            $index = $first . '.' . $second . '.' . $third;
            $array[$index][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_triple_oid()

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
 * @param array $device Target device
 * @param string $oid The string based oid to walk
 * @param string $mib The MIB to use
 * @param int $depth how many indexes to group
 * @param array $array optionally insert the entries into an existing array (helpful for grouping multiple walks)
 * @param string $mibdir custom mib dir to search for mib
 * @return array grouped array of data
 */
function snmpwalk_group($device, $oid, $mib = '', $depth = 1, $array = [], $mibdir = null, $strIndexing = null)
{
    d_echo("communityStringIndexing $strIndexing\n");
    $cmd = gen_snmpwalk_cmd($device, $oid, '-OQUsetX', $mib, $mibdir, $strIndexing);
    $data = rtrim(external_exec($cmd));

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

function snmpwalk_cache_twopart_oid($device, $oid, $array, $mib = 0, $mibdir = null, $snmpflags = '-OQUs')
{
    $cmd = gen_snmpwalk_cmd($device, $oid, $snmpflags, $mib, $mibdir);
    $data = trim(external_exec($cmd));

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        $value = str_replace('"', '', $value);
        [$oid, $first, $second] = explode('.', $oid);
        if (! strstr($value, 'at this OID') && isset($oid) && isset($first) && isset($second)) {
            $array[$first][$second][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_twopart_oid()

function snmpwalk_cache_threepart_oid($device, $oid, $array, $mib = 0)
{
    $cmd = gen_snmpwalk_cmd($device, $oid, '-OQUs', $mib);
    $data = trim(external_exec($cmd));

    foreach (explode("\n", $data) as $entry) {
        [$oid,$value] = explode('=', $entry, 2);
        $oid = trim($oid);
        $value = trim($value);
        $value = str_replace('"', '', $value);
        [$oid, $first, $second, $third] = explode('.', $oid);

        if (Debug::isEnabled()) {
            echo "$entry || $oid || $first || $second || $third\n";
        }

        if (! strstr($value, 'at this OID') && isset($oid) && isset($first) && isset($second) && isset($third)) {
            $array[$first][$second][$third][$oid] = $value;
        }
    }

    return $array;
}//end snmpwalk_cache_threepart_oid()

/**
 * generate snmp auth arguments
 * @param array $device
 * @param array $cmd
 * @return array
 */
function snmp_gen_auth(&$device, $cmd = [], $strIndexing = null)
{
    if ($device['snmpver'] === 'v3') {
        array_push($cmd, '-v3', '-l', $device['authlevel']);
        array_push($cmd, '-n', isset($device['context_name']) ? $device['context_name'] : '');

        $authlevel = strtolower($device['authlevel']);
        if ($authlevel === 'noauthnopriv') {
            // We have to provide a username anyway (see Net-SNMP doc)
            array_push($cmd, '-u', ! empty($device['authname']) ? $device['authname'] : 'root');
        } elseif ($authlevel === 'authnopriv') {
            array_push($cmd, '-a', $device['authalgo']);
            array_push($cmd, '-A', $device['authpass']);
            array_push($cmd, '-u', $device['authname']);
        } elseif ($authlevel === 'authpriv') {
            array_push($cmd, '-a', $device['authalgo']);
            array_push($cmd, '-A', $device['authpass']);
            array_push($cmd, '-u', $device['authname']);
            array_push($cmd, '-x', $device['cryptoalgo']);
            array_push($cmd, '-X', $device['cryptopass']);
        } else {
            d_echo('DEBUG: ' . $device['snmpver'] . " : Unsupported SNMPv3 AuthLevel (wtf have you done ?)\n");
        }
    } elseif ($device['snmpver'] === 'v2c' || $device['snmpver'] === 'v1') {
        array_push($cmd, '-' . $device['snmpver'], '-c', $device['community'] . ($strIndexing != null ? '@' . $strIndexing : null));
    } else {
        d_echo('DEBUG: ' . $device['snmpver'] . " : Unsupported SNMP Version (shouldn't be possible to get here)\n");
    }

    return $cmd;
}//end snmp_gen_auth()

/**
 * SNMP translate between numeric and textual oids
 *
 * Default options for a numeric oid is -Os
 * Default options for a textual oid is -On
 * You may override these by setting $options (an empty string for no options)
 *
 * @param string $oid
 * @param string $mib
 * @param string $mibdir the mib directory (relative to the LibreNMS mibs directory)
 * @param array|string $options Options to pass to snmptranslate
 * @param array|null $device
 * @return string
 */
function snmp_translate($oid, $mib = 'ALL', $mibdir = null, $options = null, $device = null)
{
    $cmd = [Config::get('snmptranslate', 'snmptranslate'), '-M', mibdir($mibdir, $device), '-m', $mib];

    if (oid_is_numeric($oid)) {
        $default_options = ['-Os', '-Pu'];
    } else {
        if ($mib != 'ALL' && ! Str::contains($oid, '::')) {
            $oid = "$mib::$oid";
        }
        $default_options = ['-On', '-Pu'];
    }
    $options = is_null($options) ? $default_options : $options;
    $cmd = array_merge($cmd, (array) $options);
    $cmd[] = $oid;

    return trim(external_exec($cmd));
}

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
 * @param $device
 * @param $OID
 * @param int $indexes
 * @internal param $string
 * @return bool|array
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
    foreach (explode("\n", $string) as $line) {
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

/**
 * @param $device
 * @return bool
 */
function get_device_max_repeaters($device)
{
    return $device['attribs']['snmp_max_repeaters'] ??
        Config::getOsSetting($device['os'], 'snmp.max_repeaters', Config::get('snmp.max_repeaters', false));
}

/**
 * Check if a given oid is numeric.
 *
 * @param string $oid
 * @return bool
 */
function oid_is_numeric($oid)
{
    return \LibreNMS\Device\YamlDiscovery::oidIsNumeric($oid);
}
