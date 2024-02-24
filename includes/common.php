<?php
/*
 * LibreNMS - Common Functions
 *
 * Original Observium version by: Adam Armstrong, Tom Laermans
 * Copyright (c) 2009-2012 Adam Armstrong.
 *
 * Additions for LibreNMS by: Neil Lathwood, Paul Gear, Tim DuFrane
 * Copyright (c) 2014-2015 Neil Lathwood <http://www.lathwood.co.uk>
 * Copyright (c) 2014-2015 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\IP;
use LibreNMS\Util\Laravel;
use Symfony\Component\Process\Process;

/**
 * Execute and snmp command, filter debug output unless -v is specified
 *
 * @param  array  $command
 * @return null|string
 */
function external_exec($command)
{
    $device = DeviceCache::getPrimary();

    $proc = new Process($command);
    $proc->setTimeout(Config::get('snmp.exec_timeout', 1200));

    if (Debug::isEnabled() && ! Debug::isVerbose()) {
        $patterns = [
            '/-c\' \'[\S]+\'/',
            '/-u\' \'[\S]+\'/',
            '/-U\' \'[\S]+\'/',
            '/-A\' \'[\S]+\'/',
            '/-X\' \'[\S]+\'/',
            '/-P\' \'[\S]+\'/',
            '/-H\' \'[\S]+\'/',
            '/-y\' \'[\S]+\'/',
            '/(udp|udp6|tcp|tcp6):([^:]+):([\d]+)/',
        ];
        $replacements = [
            '-c\' \'COMMUNITY\'',
            '-u\' \'USER\'',
            '-U\' \'USER\'',
            '-A\' \'PASSWORD\'',
            '-X\' \'PASSWORD\'',
            '-P\' \'PASSWORD\'',
            '-H\' \'HOSTNAME\'',
            '-y\' \'KG_KEY\'',
            '\1:HOSTNAME:\3',
        ];

        $debug_command = preg_replace($patterns, $replacements, $proc->getCommandLine());
        c_echo('SNMP[%c' . $debug_command . "%n]\n");
    } elseif (Debug::isVerbose()) {
        c_echo('SNMP[%c' . $proc->getCommandLine() . "%n]\n");
    }

    $proc->run();
    $output = $proc->getOutput();

    if ($proc->getExitCode()) {
        if (Str::startsWith($proc->getErrorOutput(), 'Invalid authentication protocol specified')) {
            \App\Models\Eventlog::log('Unsupported SNMP authentication algorithm - ' . $proc->getExitCode(), optional($device)->device_id, 'poller', Severity::Error);
        } elseif (Str::startsWith($proc->getErrorOutput(), 'Invalid privacy protocol specified')) {
            \App\Models\Eventlog::log('Unsupported SNMP privacy algorithm - ' . $proc->getExitCode(), optional($device)->device_id, 'poller', Severity::Error);
        }
        d_echo('Exitcode: ' . $proc->getExitCode());
        d_echo($proc->getErrorOutput());
    }

    if (Debug::isEnabled() && ! Debug::isVerbose()) {
        $ip_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
        $debug_output = preg_replace($ip_regex, '*', $output);
        d_echo($debug_output . PHP_EOL);
    } elseif (Debug::isVerbose()) {
        d_echo($output . PHP_EOL);
    }
    d_echo($proc->getErrorOutput());

    return $output;
}

function shorthost($hostname, $len = 12)
{
    // IP addresses should not be shortened
    if (filter_var($hostname, FILTER_VALIDATE_IP)) {
        return $hostname;
    }
    $len = Config::get('shorthost_target_length', $len);

    $parts = explode('.', $hostname);
    $shorthost = $parts[0];
    $i = 1;
    while ($i < count($parts) && strlen($shorthost . '.' . $parts[$i]) < $len) {
        $shorthost = $shorthost . '.' . $parts[$i];
        $i++;
    }

    return $shorthost;
}

function print_error($text)
{
    if (Laravel::isCli()) {
        c_echo('%r' . $text . "%n\n");
    } else {
        echo '<div class="alert alert-danger"><i class="fa fa-fw fa-exclamation-circle" aria-hidden="true"></i> ' . $text . '</div>';
    }
}

function print_message($text)
{
    if (Laravel::isCli()) {
        c_echo('%g' . $text . "%n\n");
    } else {
        echo '<div class="alert alert-success"><i class="fa fa-fw fa-check-circle" aria-hidden="true"></i> ' . $text . '</div>';
    }
}

function get_sensor_rrd($device, $sensor)
{
    return Rrd::name($device['hostname'], get_sensor_rrd_name($device, $sensor));
}

function get_sensor_rrd_name($device, $sensor)
{
    // For IPMI, sensors tend to change order, and there is no index, so we prefer to use the description as key here.
    if (Config::getOsSetting($device['os'], 'sensor_descr') || $sensor['poller_type'] == 'ipmi') {
        return ['sensor', $sensor['sensor_class'], $sensor['sensor_type'], $sensor['sensor_descr']];
    } else {
        return ['sensor', $sensor['sensor_class'], $sensor['sensor_type'], $sensor['sensor_index']];
    }
}

function get_port_rrdfile_path($hostname, $port_id, $suffix = '')
{
    return Rrd::name($hostname, Rrd::portName($port_id, $suffix));
}

function get_port_by_index_cache($device_id, $ifIndex)
{
    global $port_index_cache;

    if (isset($port_index_cache[$device_id][$ifIndex]) && is_array($port_index_cache[$device_id][$ifIndex])) {
        $port = $port_index_cache[$device_id][$ifIndex];
    } else {
        $port = get_port_by_ifIndex($device_id, $ifIndex);
        $port_index_cache[$device_id][$ifIndex] = $port;
    }

    return $port;
}

function get_port_by_ifIndex($device_id, $ifIndex)
{
    return dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device_id, $ifIndex]);
}

function get_entity_by_id_cache($type, $id)
{
    global $entity_cache;

    $table = $type == 'storage' ? $type : $type . 's';

    if (is_array($entity_cache[$type][$id])) {
        $entity = $entity_cache[$type][$id];
    } else {
        $entity = dbFetchRow('SELECT * FROM `' . $table . '` WHERE `' . $type . '_id` = ?', [$id]);
        $entity_cache[$type][$id] = $entity;
    }

    return $entity;
}

function get_port_by_id($port_id)
{
    if (is_numeric($port_id)) {
        $port = dbFetchRow('SELECT * FROM `ports` WHERE `port_id` = ?', [$port_id]);
        if (is_array($port)) {
            return $port;
        } else {
            return false;
        }
    }
}

function get_device_id_by_port_id($port_id)
{
    if (is_numeric($port_id)) {
        $device_id = dbFetchCell('SELECT `device_id` FROM `ports` WHERE `port_id` = ?', [$port_id]);
        if (is_numeric($device_id)) {
            return $device_id;
        } else {
            return false;
        }
    }
}

function ifclass($ifOperStatus, $ifAdminStatus)
{
    // fake a port model
    return \LibreNMS\Util\Url::portLinkDisplayClass((object) ['ifOperStatus' => $ifOperStatus, 'ifAdminStatus' => $ifAdminStatus]);
}

function device_by_name($name)
{
    return device_by_id_cache(getidbyname($name));
}

function accesspoint_by_id($ap_id, $refresh = '0')
{
    $ap = dbFetchRow('SELECT * FROM `access_points` WHERE `accesspoint_id` = ?', [$ap_id]);

    return $ap;
}

function device_by_id_cache($device_id, $refresh = false)
{
    $model = $refresh ? DeviceCache::refresh((int) $device_id) : DeviceCache::get((int) $device_id);

    $device = $model->toArray();
    $device['location'] = $model->location->location ?? null;
    $device['lat'] = $model->location->lat ?? null;
    $device['lng'] = $model->location->lng ?? null;

    return $device;
}

function truncate($substring, $max = 50, $rep = '...')
{
    if (strlen($substring) < 1) {
        $string = $rep;
    } else {
        $string = $substring;
    }
    $leave = $max - strlen($rep);
    if (strlen($string) > $max) {
        return substr_replace($string, $rep, $leave);
    } else {
        return $string;
    }
}

function gethostbyid($device_id)
{
    return DeviceCache::get((int) $device_id)->hostname;
}

function strgen($length = 16)
{
    $entropy = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e',
        'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J', 'k', 'K', 'l', 'L', 'm', 'M', 'n',
        'N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T', 'u', 'U', 'v', 'V', 'w',
        'W', 'x', 'X', 'y', 'Y', 'z', 'Z', ];
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $key = mt_rand(0, 61);
        $string .= $entropy[$key];
    }

    return $string;
}

function getifbyid($id)
{
    return dbFetchRow('SELECT * FROM `ports` WHERE `port_id` = ?', [$id]);
}

function getidbyname($hostname)
{
    return DeviceCache::getByHostname($hostname)->device_id;
}

function zeropad($num, $length = 2)
{
    return str_pad($num, $length, '0', STR_PAD_LEFT);
}

function set_dev_attrib($device, $attrib_type, $attrib_value)
{
    return DeviceCache::get((int) $device['device_id'])->setAttrib($attrib_type, $attrib_value);
}

function get_dev_attribs($device_id)
{
    return DeviceCache::get((int) $device_id)->getAttribs();
}

function get_dev_entity_state($device)
{
    $state = [];
    foreach (dbFetchRows('SELECT * FROM entPhysical_state WHERE `device_id` = ?', [$device]) as $entity) {
        $state['group'][$entity['group']][$entity['entPhysicalIndex']][$entity['subindex']][$entity['key']] = $entity['value'];
        $state['index'][$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] = $entity['value'];
    }

    return $state;
}

function get_dev_attrib($device, $attrib_type)
{
    return DeviceCache::get((int) $device['device_id'])->getAttrib($attrib_type);
}

function del_dev_attrib($device, $attrib_type)
{
    return DeviceCache::get((int) $device['device_id'])->forgetAttrib($attrib_type);
}

/**
 * Output using console color if possible
 * https://github.com/pear/Console_Color2/blob/master/examples/documentation
 *
 * @param  string  $string  the string to print with console color
 * @param  bool  $enabled  if set to false, this function does nothing
 */
function c_echo($string, $enabled = true)
{
    if (! $enabled) {
        return;
    }

    if (Laravel::isCli()) {
        global $console_color;
        if ($console_color) {
            echo $console_color->convert($string);
        } else {
            // limited functionality for validate.php
            $search = [
                '/%n/',
                '/%g/',
                '/%R/',
                '/%Y/',
                '/%B/',
                '/%((%)|.)/', // anything left over replace with empty string
            ];
            $replace = [
                "\e[0m",
                "\e[32m",
                "\e[1;31m",
                "\e[1;33m",
                "\e[1;34m",
                '',
            ];
            echo preg_replace($search, $replace, $string);
        }
    } else {
        echo preg_replace('/%((%)|.)/', '', $string);
    }
}

/*
 * @return true if client IP address is authorized to access graphs
 */
function is_client_authorized($clientip)
{
    if (Config::get('allow_unauth_graphs', false)) {
        d_echo("Unauthorized graphs allowed\n");

        return true;
    }

    foreach (Config::get('allow_unauth_graphs_cidr', []) as $range) {
        try {
            if (IP::parse($clientip)->inNetwork($range)) {
                d_echo("Unauthorized graphs allowed from $range\n");

                return true;
            }
        } catch (InvalidIpException $e) {
            d_echo("Client IP ($clientip) is invalid.\n");
        }
    }

    return false;
} // is_client_authorized

/*
 * @return an array of all graph subtypes for the given type
 */
function get_graph_subtypes($type, $device = null)
{
    $type = basename($type);
    $types = [];

    // find the subtypes defined in files
    if ($handle = opendir(Config::get('install_dir') . "/includes/html/graphs/$type/")) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && $file != 'auth.inc.php' && strstr($file, '.inc.php')) {
                $types[] = str_replace('.inc.php', '', $file);
            }
        }
        closedir($handle);
    }

    sort($types);

    return $types;
} // get_graph_subtypes

function generate_smokeping_file($device, $file = '')
{
    $smokeping = new \LibreNMS\Util\Smokeping(DeviceCache::get((int) $device['device_id']));

    return $smokeping->generateFileName($file);
}

/*
 * @return rounded value to 10th/100th/1000th depending on input (valid: 10, 100, 1000)
 */
function round_Nth($val, $round_to)
{
    if (($round_to == '10') || ($round_to == '100') || ($round_to == '1000')) {
        $diff = $val % $round_to;
        if ($diff >= ($round_to / 2)) {
            $ret = $val + ($round_to - $diff);
        } else {
            $ret = $val - $diff;
        }

        return $ret;
    }
} // end round_Nth

function is_customoid_graph($type, $subtype)
{
    if (! empty($subtype) && $type == 'customoid') {
        return true;
    }

    return false;
} // is_customoid_graph

/**
 * Parse location field for coordinates
 *
 * @param string location The location field to look for coords in.
 * @return array|bool Containing the lat and lng coords
 **/
function parse_location($location)
{
    preg_match('/\[(-?[0-9. ]+), *(-?[0-9. ]+)\]/', $location, $tmp_loc);
    if (is_numeric($tmp_loc[1]) && is_numeric($tmp_loc[2])) {
        return ['lat' => $tmp_loc[1], 'lng' => $tmp_loc[2]];
    }

    return false;
}//end parse_location()

/**
 * Convert a MySQL binary v4 (4-byte) or v6 (16-byte) IP address to a printable string.
 *
 * @param  string  $ip  A binary string containing an IP address, as returned from MySQL's INET6_ATON function
 * @return string Empty if not valid.
 */
// Fuction is from https://php.net/manual/en/function.inet-ntop.php
function inet6_ntop($ip)
{
    $l = strlen($ip);
    if ($l == 4 or $l == 16) {
        return inet_ntop(pack('A' . $l, $ip));
    }

    return '';
}

/**
 * If hostname is an ip, use return sysName
 *
 * @param  array  $device  (uses hostname and sysName fields)
 * @return string
 */
function format_hostname($device): string
{
    $hostname = $device['hostname'] ?? 'invalid hostname';
    $hostname_is_ip = IP::isValid($hostname);
    $sysName = empty($device['sysName']) ? $hostname : $device['sysName'];

    return \App\View\SimpleTemplate::parse(empty($device['display']) ? Config::get('device_display_default', '{{ $hostname }}') : $device['display'], [
        'hostname' => $hostname,
        'sysName' => $sysName,
        'sysName_fallback' => $hostname_is_ip ? $sysName : $hostname,
        'ip' => empty($device['overwrite_ip']) ? ($hostname_is_ip ? $device['hostname'] : $device['ip'] ?? '') : $device['overwrite_ip'],
    ]);
}

/**
 * Query all ports of the given device (by ID) and build port array and
 * port association maps for ifIndex, ifName, ifDescr. Query port stats
 * if told to do so, too.
 *
 * @param  int  $device_id  ID of device to query ports for
 * @param  bool  $with_statistics  Query port statistics, too. (optional, default false)
 * @return array
 */
function get_ports_mapped($device_id, $with_statistics = false)
{
    $ports = [];
    $maps = [
        'ifIndex' => [],
        'ifName' => [],
        'ifDescr' => [],
    ];

    if ($with_statistics) {
        /* ... including any related ports_statistics if requested */
        $query = 'SELECT *, `ports_statistics`.`port_id` AS `ports_statistics_port_id`, `ports`.`port_id` AS `port_id` FROM `ports` LEFT OUTER JOIN `ports_statistics` ON `ports`.`port_id` = `ports_statistics`.`port_id` WHERE `ports`.`device_id` = ? ORDER BY ports.port_id';
    } else {
        /* Query all information available for ports for this device ... */
        $query = 'SELECT * FROM `ports` WHERE `device_id` = ? ORDER BY port_id';
    }

    // Query known ports in order of discovery to make sure the latest
    // discoverd/polled port is in the mapping tables.
    foreach (dbFetchRows($query, [$device_id]) as $port) {
        // Store port information by ports port_id from DB
        $ports[$port['port_id']] = $port;

        // Build maps from ifIndex, ifName, ifDescr to port_id
        $maps['ifIndex'][$port['ifIndex']] = $port['port_id'];
        $maps['ifName'][$port['ifName']] = $port['port_id'];
        $maps['ifDescr'][$port['ifDescr']] = $port['port_id'];
    }

    return [
        'ports' => $ports,
        'maps' => $maps,
    ];
}

/**
 * Calculate port_id of given port using given devices port information and port association mode
 *
 * @param  array  $ports_mapped  Port information of device queried by get_ports_mapped()
 * @param  array  $port  Port information as fetched from DB
 * @param  string  $port_association_mode  Port association mode to use for mapping
 * @return int port_id (or Null)
 */
function get_port_id($ports_mapped, $port, $port_association_mode)
{
    // Get port_id according to port_association_mode used for this device
    $port_id = null;

    /*
     * Information an all ports is available through $ports_mapped['ports']
     * This might come in handy sometime in the future to add you nifty new
     * port mapping schema:
     *
     * $ports = $ports_mapped['ports'];
    */
    $maps = $ports_mapped['maps'];

    if (in_array($port_association_mode, ['ifIndex', 'ifName', 'ifDescr', 'ifAlias'])) {
        $port_id = $maps[$port_association_mode][$port[$port_association_mode]] ?? null;
    }

    return $port_id;
}

/**
 * Create a glue-chain
 *
 * @param  array  $tables  Initial Tables to construct glue-chain
 * @param  string  $target  Glue to find (usual device_id)
 * @param  int  $x  Recursion Anchor
 * @param  array  $hist  History of processed tables
 * @param  array  $last  Glues on the fringe
 * @return array|false
 */
function ResolveGlues($tables, $target, $x = 0, $hist = [], $last = [])
{
    if (sizeof($tables) == 1 && $x != 0) {
        if (dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?', [$tables[0], $target]) == 1) {
            return array_merge($last, [$tables[0] . '.' . $target]);
        } else {
            return false;
        }
    } else {
        $x++;
        if ($x > 30) {
            //Too much recursion. Abort.
            return false;
        }
        foreach ($tables as $table) {
            if ($table == 'state_translations' && ($target == 'device_id' || $target == 'sensor_id')) {
                // workaround for state_translations
                return array_merge($last, [
                    'state_translations.state_index_id',
                    'sensors_to_state_indexes.sensor_id',
                    "sensors.$target",
                ]);
            } elseif ($table == 'application_metrics' && $target == 'device_id') {
                return array_merge($last, [
                    'application_metrics.app_id',
                    "applications.$target",
                ]);
            } elseif ($table == 'locations' && $target == 'device_id') {
                return array_merge($last, [
                    'locations.id',
                    'devices.device_id.location_id',
                ]);
            }

            $glues = dbFetchRows('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME LIKE "%\_id"', [$table]);
            if (sizeof($glues) == 1 && $glues[0]['COLUMN_NAME'] != $target) {
                //Search for new candidates to expand
                $ntables = [];
                [$tmp] = explode('_', $glues[0]['COLUMN_NAME'], 2);
                $ntables[] = $tmp;
                $ntables[] = $tmp . 's';
                $tmp = dbFetchRows('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME LIKE "' . substr($table, 0, -1) . '_%" && TABLE_NAME != "' . $table . '"');
                foreach ($tmp as $expand) {
                    $ntables[] = $expand['TABLE_NAME'];
                }
                $tmp = ResolveGlues($ntables, $target, $x++, array_merge($tables, $ntables), array_merge($last, [$table . '.' . $glues[0]['COLUMN_NAME']]));
                if (is_array($tmp)) {
                    return $tmp;
                }
            } else {
                foreach ($glues as $glue) {
                    if ($glue['COLUMN_NAME'] == $target) {
                        return array_merge($last, [$table . '.' . $target]);
                    } else {
                        [$tmp] = explode('_', $glue['COLUMN_NAME']);
                        $tmp .= 's';
                        if (! in_array($tmp, $tables) && ! in_array($tmp, $hist)) {
                            //Expand table
                            $tmp = ResolveGlues([$tmp], $target, $x++, array_merge($tables, [$tmp]), array_merge($last, [$table . '.' . $glue['COLUMN_NAME']]));
                            if (is_array($tmp)) {
                                return $tmp;
                            }
                        }
                    }
                }
            }
        }
    }

    //You should never get here.
    return false;
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function str_i_contains($haystack, $needles)
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && stripos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Get alert_rules sql filter by minimal severity
 *
 * @param  string|int  $min_severity
 * @param  string  $alert_rules_name
 * @return string
 */
function get_sql_filter_min_severity($min_severity, $alert_rules_name)
{
    $alert_severities = [
        // alert_rules.status is enum('ok','warning','critical')
        'ok' => 1,
        'warning' => 2,
        'critical' => 3,
        'ok only' => 4,
        'warning only' => 5,
        'critical only' => 6,
    ];
    if (is_numeric($min_severity)) {
        $min_severity_id = $min_severity;
    } elseif (! empty($min_severity)) {
        $min_severity_id = $alert_severities[$min_severity];
    }
    if (isset($min_severity_id)) {
        return " AND `$alert_rules_name`.`severity` " . ($min_severity_id > 3 ? '' : '>') . '= ' . ($min_severity_id > 3 ? $min_severity_id - 3 : $min_severity_id);
    }

    return '';
}

/**
 * Converts fahrenheit to celsius (with 2 decimal places)
 * if $scale is not fahrenheit, it assumes celsius and  returns the value
 *
 * @param  float  $value
 * @param  string  $scale  fahrenheit or celsius
 * @return string (containing a float)
 */
function fahrenheit_to_celsius($value, $scale = 'fahrenheit')
{
    if ($scale === 'fahrenheit') {
        $value = ($value - 32) / 1.8;
    }

    return sprintf('%.02f', $value);
}

/**
 * Converts celsius to fahrenheit (with 2 decimal places)
 * if $scale is not celsius, it assumes celsius and  returns the value
 *
 * @param  float  $value
 * @param  string  $scale  fahrenheit or celsius
 * @return string (containing a float)
 */
function celsius_to_fahrenheit($value, $scale = 'celsius')
{
    if ($scale === 'celsius') {
        $value = ($value * 1.8) + 32;
    }

    return sprintf('%.02f', $value);
}

/**
 * Converts kelvin to fahrenheit (with 2 decimal places)
 * if $scale is not celsius, it assumes celsius and  returns the value
 *
 * @param  float  $value
 * @param  string  $scale  fahrenheit or celsius
 * @return string (containing a float)
 */
function kelvin_to_celsius($value, $scale = 'celsius')
{
    if ($scale === 'celsius') {
        $value = $value - 273.15;
    }

    return sprintf('%.02f', $value);
}

/**
 * Converts string to float
 */
function string_to_float($value)
{
    return sprintf('%.02f', $value);
}

/**
 * Converts uW to dBm
 * $value must be positive
 */
function uw_to_dbm($value)
{
    return $value == 0 ? -60 : 10 * log10($value / 1000);
}

/**
 * Converts mW to dBm
 * $value must be positive
 */
function mw_to_dbm($value)
{
    return $value == 0 ? -60 : 10 * log10($value);
}

/**
 * @param  $value
 * @param  null  $default
 * @param  int  $min
 * @return null
 */
function set_null($value, $default = null, $min = null)
{
    if (! is_numeric($value)) {
        return $default;
    } elseif (is_nan($value)) {
        return $default;
    } elseif (is_infinite($value)) {
        return $default;
    } elseif (isset($min) && $value < $min) {
        return $default;
    }

    return $value;
}
/*
 * @param $value
 * @param int $default
 * @return int
 */
function set_numeric($value, $default = 0)
{
    if (! is_numeric($value) ||
        is_nan($value) ||
        is_infinite($value)
    ) {
        $value = $default;
    }

    return $value;
}

function get_vm_parent_id($device)
{
    if (empty($device['hostname'])) {
        return false;
    }

    return dbFetchCell('SELECT `device_id` FROM `vminfo` WHERE `vmwVmDisplayName` = ? OR `vmwVmDisplayName` = ?', [$device['hostname'], $device['hostname'] . '.' . Config::get('mydomain')]);
}

/**
 * Index an array by a column
 *
 * @param  array  $array
 * @param  string|int  $column
 * @return array
 */
function array_by_column($array, $column)
{
    return array_combine(array_column($array, $column), $array);
}
