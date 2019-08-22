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
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\Git;
use LibreNMS\Util\IP;
use LibreNMS\Util\Laravel;

function generate_priority_label($priority)
{
    $map = array(
        "emerg"     => "label-danger",
        "alert"     => "label-danger",
        "crit"      => "label-danger",
        "err"       => "label-danger",
        "warning"   => "label-warning",
        "notice"    => "label-info",
        "info"      => "label-info",
        "debug"     => "label-default",
        ""          => "label-info",
    );

    $barColor = isset($map[$priority]) ? $map[$priority] : 'label-info';
    return '<span class="alert-status '.$barColor .'">&nbsp;</span>';
}

function generate_priority_status($priority)
{
    $map = array(
        "emerg"     => 2,
        "alert"     => 2,
        "crit"      => 2,
        "err"       => 2,
        "warning"   => 1,
        "notice"    => 0,
        "info"      => 0,
        "debug"     => 3,
        ""          => 0,
    );

    return isset($map[$priority]) ? $map[$priority] : 0;
}

function graylog_severity_label($severity)
{
    $map = array(
        "0" => "label-danger",
        "1" => "label-danger",
        "2" => "label-danger",
        "3" => "label-danger",
        "4" => "label-warning",
        "5" => "label-info",
        "6" => "label-info",
        "7" => "label-default",
        ""  => "label-info",
    );
    $barColor = isset($map[$severity]) ? $map[$severity] : 'label-info';
    return '<span class="alert-status '.$barColor .'" style="margin-right:8px;float:left;"></span>';
}

/**
 * Execute and snmp command, filter debug output unless -v is specified
 *
 * @param array $command
 * @return null|string
 */
function external_exec($command)
{
    global $debug, $vdebug;

    $proc = new \Symfony\Component\Process\Process($command);
    $proc->setTimeout(Config::get('snmp.exec_timeout', 1200));

    if ($debug && !$vdebug) {
        $patterns = [
            '/-c\' \'[\S]+\'/',
            '/-u\' \'[\S]+\'/',
            '/-U\' \'[\S]+\'/',
            '/-A\' \'[\S]+\'/',
            '/-X\' \'[\S]+\'/',
            '/-P\' \'[\S]+\'/',
            '/-H\' \'[\S]+\'/',
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
            '\1:HOSTNAME:\3',
        ];

        $debug_command = preg_replace($patterns, $replacements, $proc->getCommandLine());
        c_echo('SNMP[%c' . $debug_command . "%n]\n");
    } elseif ($vdebug) {
        c_echo('SNMP[%c'.$proc->getCommandLine()."%n]\n");
    }

    $proc->run();
    $output = $proc->getOutput();

    if ($debug && !$vdebug) {
        $ip_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
        $debug_output = preg_replace($ip_regex, '*', $output);
        d_echo($debug_output . PHP_EOL);
    } elseif ($vdebug) {
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

    $parts = explode(".", $hostname);
    $shorthost = $parts[0];
    $i = 1;
    while ($i < count($parts) && strlen($shorthost.'.'.$parts[$i]) < $len) {
        $shorthost = $shorthost.'.'.$parts[$i];
        $i++;
    }
    return ($shorthost);
}

function isCli()
{
    if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    } else {
        return false;
    }
}

function print_error($text)
{
    if (isCli()) {
        c_echo("%r".$text."%n\n");
    } else {
        echo('<div class="alert alert-danger"><i class="fa fa-fw fa-exclamation-circle" aria-hidden="true"></i> '.$text.'</div>');
    }
}

function print_message($text)
{
    if (isCli()) {
        c_echo("%g".$text."%n\n");
    } else {
        echo('<div class="alert alert-success"><i class="fa fa-fw fa-check-circle" aria-hidden="true"></i> '.$text.'</div>');
    }
}

function delete_port($int_id)
{
    $interface = dbFetchRow("SELECT * FROM `ports` AS P, `devices` AS D WHERE P.port_id = ? AND D.device_id = P.device_id", array($int_id));

    $interface_tables = array('ipv4_addresses', 'ipv4_mac', 'ipv6_addresses', 'juniAtmVp', 'mac_accounting', 'ospf_nbrs', 'ospf_ports', 'ports', 'ports_adsl', 'ports_perms', 'ports_statistics', 'ports_stp', 'ports_vlans', 'pseudowires');

    foreach ($interface_tables as $table) {
        dbDelete($table, "`port_id` =  ?", array($int_id));
    }

    dbDelete('links', "`local_port_id` = ? OR `remote_port_id` = ?", array($int_id, $int_id));
    dbDelete('ports_stack', "`port_id_low` = ? OR `port_id_high` = ?", array($int_id, $int_id));

    unlink(get_port_rrdfile_path($interface['hostname'], $interface['port_id']));
}

function get_sensor_rrd($device, $sensor)
{
    return rrd_name($device['hostname'], get_sensor_rrd_name($device, $sensor));
}

function get_sensor_rrd_name($device, $sensor)
{
    # For IPMI, sensors tend to change order, and there is no index, so we prefer to use the description as key here.
    if (Config::getOsSetting($device['os'], 'sensor_descr') || $sensor['poller_type'] == "ipmi") {
        return array('sensor', $sensor['sensor_class'], $sensor['sensor_type'], $sensor['sensor_descr']);
    } else {
        return array('sensor', $sensor['sensor_class'], $sensor['sensor_type'], $sensor['sensor_index']);
    }
}

function getPortRrdName($port_id, $suffix = '')
{
    if (!empty($suffix)) {
        $suffix = '-' . $suffix;
    }

    return "port-id$port_id$suffix";
}

function get_port_rrdfile_path($hostname, $port_id, $suffix = '')
{
    return rrd_name($hostname, getPortRrdName($port_id, $suffix));
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
    return dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device_id, $ifIndex));
}

function get_all_devices()
{
    global $cache;
    $devices = array();

    // FIXME needs access control checks!
    // FIXME respect $type (server, network, etc) -- needs an array fill in topnav.

    if (isset($cache['devices']['hostname'])) {
        $devices = array_keys($cache['devices']);
    } else {
        $devices = dbFetchRows("SELECT * FROM `devices` ORDER BY hostname");
    }

    return $devices;
}

function table_from_entity_type($type)
{
    // Fuck you, english pluralisation.
    if ($type == "storage") {
        return $type;
    } else {
        return $type."s";
    }
}

function get_entity_by_id_cache($type, $id)
{
    global $entity_cache;

    $table = table_from_entity_type($type);

    if (is_array($entity_cache[$type][$id])) {
        $entity = $entity_cache[$type][$id];
    } else {
        $entity = dbFetchRow("SELECT * FROM `".$table."` WHERE `".$type."_id` = ?", array($id));
        $entity_cache[$type][$id] = $entity;
    }
    return $entity;
}

function get_port_by_id($port_id)
{
    if (is_numeric($port_id)) {
        $port = dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($port_id));
        if (is_array($port)) {
            return $port;
        } else {
            return false;
        }
    }
}

function get_application_by_id($application_id)
{
    if (is_numeric($application_id)) {
        $application = dbFetchRow("SELECT * FROM `applications` WHERE `app_id` = ?", array($application_id));
        if (is_array($application)) {
            return $application;
        } else {
            return false;
        }
    }
}

function get_sensor_by_id($sensor_id)
{
    if (is_numeric($sensor_id)) {
        $sensor = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_id` = ?", array($sensor_id));
        if (is_array($sensor)) {
            return $sensor;
        } else {
            return false;
        }
    }
}

function get_device_id_by_port_id($port_id)
{
    if (is_numeric($port_id)) {
        $device_id = dbFetchCell("SELECT `device_id` FROM `ports` WHERE `port_id` = ?", array($port_id));
        if (is_numeric($device_id)) {
            return $device_id;
        } else {
            return false;
        }
    }
}

function get_device_id_by_app_id($app_id)
{
    if (is_numeric($app_id)) {
        $device_id = dbFetchCell("SELECT `device_id` FROM `applications` WHERE `app_id` = ?", array($app_id));
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

function device_by_name($name, $refresh = 0)
{
    // FIXME - cache name > id too.
    return device_by_id_cache(getidbyname($name), $refresh);
}


function accesspoint_by_id($ap_id, $refresh = '0')
{

    $ap = dbFetchRow("SELECT * FROM `access_points` WHERE `accesspoint_id` = ?", array($ap_id));

    return $ap;
}


function device_by_id_cache($device_id, $refresh = false)
{
    global $cache;

    if (!$refresh && isset($cache['devices']['id'][$device_id]) && is_array($cache['devices']['id'][$device_id])) {
        $device = $cache['devices']['id'][$device_id];
    } else {
        $device = dbFetchRow("SELECT `devices`.*, `location`, `lat`, `lng` FROM `devices` LEFT JOIN locations ON `devices`.location_id=`locations`.`id` WHERE `device_id` = ?", [$device_id]);
        $device['attribs'] = get_dev_attribs($device['device_id']);
        load_os($device);

        //order vrf_lite_cisco with context, this will help to get the vrf_name and instance_name all the time
        $vrfs_lite_cisco = dbFetchRows("SELECT * FROM `vrf_lite_cisco` WHERE `device_id` = ?", array($device_id));
        if (!empty($vrfs_lite_cisco)) {
            $device['vrf_lite_cisco'] = array();
            foreach ($vrfs_lite_cisco as $vrf) {
                $device['vrf_lite_cisco'][$vrf['context_name']] = $vrf;
            }
        }

        if (!empty($device['ip'])) {
            $device['ip'] = inet6_ntop($device['ip']);
        }
        $cache['devices']['id'][$device_id] = $device;
    }
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

function mres($string)
{
    return $string; // FIXME bleh
    // short function wrapper because the real one is stupidly long and ugly. aesthetics.
    global $database_link;
    return mysqli_real_escape_string($database_link, $string);
}

function getifhost($id)
{
    return dbFetchCell("SELECT `device_id` from `ports` WHERE `port_id` = ?", array($id));
}

function gethostbyid($id)
{
    global $cache;

    if (isset($cache['devices']['id'][$id]['hostname'])) {
        $hostname = $cache['devices']['id'][$id]['hostname'];
    } else {
        $hostname = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
    }

    return $hostname;
}

function strgen($length = 16)
{
    $entropy = array(0,1,2,3,4,5,6,7,8,9,'a','A','b','B','c','C','d','D','e',
        'E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n',
        'N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w',
        'W','x','X','y','Y','z','Z');
    $string = "";

    for ($i=0; $i<$length; $i++) {
        $key = mt_rand(0, 61);
        $string .= $entropy[$key];
    }

    return $string;
}

function getpeerhost($id)
{
    return dbFetchCell("SELECT `device_id` from `bgpPeers` WHERE `bgpPeer_id` = ?", array($id));
}

function getifindexbyid($id)
{
    return dbFetchCell("SELECT `ifIndex` FROM `ports` WHERE `port_id` = ?", array($id));
}

function getifbyid($id)
{
    return dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($id));
}

function getifdescrbyid($id)
{
    return dbFetchCell("SELECT `ifDescr` FROM `ports` WHERE `port_id` = ?", array($id));
}

function getidbyname($hostname)
{
    global $cache;

    if (isset($cache['devices']['hostname'][$hostname])) {
        $id = $cache['devices']['hostname'][$hostname];
    } else {
        $id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `hostname` = ?", array($hostname));
    }

    return $id;
}

function gethostosbyid($id)
{
    global $cache;

    if (isset($cache['devices']['id'][$id]['os'])) {
        $os = $cache['devices']['id'][$id]['os'];
    } else {
        $os = dbFetchCell("SELECT `os` FROM `devices` WHERE `device_id` = ?", array($id));
    }

    return $os;
}

function safename($name)
{
    return preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
}

/**
 * Function format the rrdtool description text correctly.
 * @param $descr
 * @return mixed
 */
function safedescr($descr)
{
    return preg_replace('/[^a-zA-Z0-9,._\-\/\ ]/', ' ', $descr);
}

function zeropad($num, $length = 2)
{
    return str_pad($num, $length, '0', STR_PAD_LEFT);
}

function set_dev_attrib($device, $attrib_type, $attrib_value)
{
    if (dbFetchCell("SELECT COUNT(*) FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ?", array($device['device_id'],$attrib_type))) {
        $return = dbUpdate(array('attrib_value' => $attrib_value), 'devices_attribs', 'device_id=? and attrib_type=?', array($device['device_id'], $attrib_type));
    } else {
        $return = dbInsert(array('device_id' => $device['device_id'], 'attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'devices_attribs');
    }
    return $return;
}

function get_dev_attribs($device)
{
    $attribs = array();
    foreach (dbFetchRows("SELECT * FROM devices_attribs WHERE `device_id` = ?", array($device)) as $entry) {
        $attribs[$entry['attrib_type']] = $entry['attrib_value'];
    }
    return $attribs;
}

function get_dev_entity_state($device)
{
    $state = array();
    foreach (dbFetchRows("SELECT * FROM entPhysical_state WHERE `device_id` = ?", array($device)) as $entity) {
        $state['group'][$entity['group']][$entity['entPhysicalIndex']][$entity['subindex']][$entity['key']] = $entity['value'];
        $state['index'][$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] = $entity['value'];
    }
    return $state;
}

function get_dev_attrib($device, $attrib_type, $attrib_value = '')
{
    $sql = '';
    $params = array($device['device_id'], $attrib_type);
    if (!empty($attrib_value)) {
        $sql = " AND `attrib_value`=?";
        array_push($params, $attrib_value);
    }
    if ($row = dbFetchRow("SELECT attrib_value FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ? $sql", $params)) {
        return $row['attrib_value'];
    } else {
        return null;
    }
}

function is_dev_attrib_enabled($device, $attrib, $default = true)
{
    $val = get_dev_attrib($device, $attrib);
    if ($val != null) {
        // attribute is set
        return ($val != 0);
    } else {
        // attribute not set
        return $default;
    }
}

function del_dev_attrib($device, $attrib_type)
{
    return dbDelete('devices_attribs', "`device_id` = ? AND `attrib_type` = ?", array($device['device_id'], $attrib_type));
}

function formatRates($value, $round = '2', $sf = '3')
{
    $value = format_si($value, $round, $sf) . "bps";
    return $value;
}

function formatStorage($value, $round = '2', $sf = '3')
{
    $value = format_bi($value, $round) . "B";
    return $value;
}

function format_si($value, $round = '2', $sf = '3')
{
    $neg = 0;
    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }

    if ($value >= "0.1") {
        $sizes = array('', 'k', 'M', 'G', 'T', 'P', 'E');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value >= 1000)); $i++) {
            $value = $value / 1000;
            $ext  = $sizes[$i];
        }
    } else {
        $sizes = array('', 'm', 'u', 'n', 'p');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value != 0) && ($value <= 0.1)); $i++) {
            $value = $value * 1000;
            $ext  = $sizes[$i];
        }
    }

    if ($neg == 1) {
        $value = $value * -1;
    }

    return (number_format(round($value, $round), $sf, '.', '')+0)." ".$ext;
}

function format_bi($value, $round = '2', $sf = '3')
{
    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }
    $sizes = array('', 'k', 'M', 'G', 'T', 'P', 'E');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value >= 1024)); $i++) {
        $value = $value / 1024;
        $ext  = $sizes[$i];
    }

    if ($neg) {
        $value = $value * -1;
    }
    return (number_format(round($value, $round), $sf, '.', '')+0)." ".$ext;
}

function format_number($value, $base = '1000', $round = 2, $sf = 3)
{
    if ($base == '1000') {
        return format_si($value, $round, $sf);
    } else {
        return format_bi($value, $round, $sf);
    }
}

function is_valid_hostname($hostname)
{
    return \LibreNMS\Util\Validate::hostname($hostname);
}

/*
 * convenience function - please use this instead of 'if ($debug) { echo ...; }'
 */
if (!function_exists('d_echo')) {
    //TODO remove this after installs have updated, leaving it for for transition
    function d_echo($text, $no_debug_text = null)
    {
        global $debug;

        if (Laravel::isBooted()) {
            \Log::debug(is_string($text) ? rtrim($text) : $text);
        } elseif ($debug) {
            print_r($text);
        }

        if (!$debug && $no_debug_text) {
            echo "$no_debug_text";
        }
    }
}

/**
 * Output using console color if possible
 * https://github.com/pear/Console_Color2/blob/master/examples/documentation
 *
 * @param string $string the string to print with console color
 * @param bool $enabled if set to false, this function does nothing
 */
function c_echo($string, $enabled = true)
{
    if (!$enabled) {
        return;
    }

    if (isCli()) {
        global $console_color;
        if ($console_color) {
            echo $console_color->convert($string);
        } else {
            // limited functionality for validate.php
            $search = array(
                '/%n/',
                '/%g/',
                '/%R/',
                '/%Y/',
                '/%B/',
                '/%((%)|.)/' // anything left over replace with empty string
            );
            $replace = array(
                "\e[0m",
                "\e[32m",
                "\e[1;31m",
                "\e[1;33m",
                "\e[1;34m",
                ""
            );
            echo preg_replace($search, $replace, $string);
        }
    } else {
        echo preg_replace('/%((%)|.)/', '', $string);
    }
}


/*
 * @return true if the given graph type is a dynamic MIB graph
 */
function is_mib_graph($type, $subtype)
{
    return \LibreNMS\Util\Graph::isMibGraph($type, $subtype);
} // is_mib_graph


/*
 * @return true if client IP address is authorized to access graphs
 */
function is_client_authorized($clientip)
{
    if (Config::get('allow_unauth_graphs', false)) {
        d_echo("Unauthorized graphs allowed\n");
        return true;
    }

    foreach (Config::get('allow_unauth_graphs_cidr', array()) as $range) {
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
    $types = array();

    // find the subtypes defined in files
    if ($handle = opendir(Config::get('install_dir') . "/includes/html/graphs/$type/")) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "auth.inc.php" && strstr($file, ".inc.php")) {
                $types[] = str_replace(".inc.php", "", $file);
            }
        }
        closedir($handle);
    }

    if ($device != null) {
        // find the MIB subtypes
        $graphs = get_device_graphs($device);

        foreach (Config::get('graph_types') as $type => $unused1) {
            foreach (Config::get("graph_types.$type") as $subtype => $unused2) {
                if (is_mib_graph($type, $subtype) && in_array($subtype, $graphs)) {
                    $types[] = $subtype;
                }
            }
        }
    }

    sort($types);
    return $types;
} // get_graph_subtypes

function get_device_graphs($device)
{
    $query = 'SELECT `graph` FROM `device_graphs` WHERE `device_id` = ?';
    return dbFetchColumn($query, array($device['device_id']));
}

function get_smokeping_files($device)
{
    $smokeping_files = array();
    if (Config::has('smokeping.dir')) {
        $smokeping_dir = generate_smokeping_file($device);
        if ($handle = opendir($smokeping_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (stripos($file, '.rrd') !== false) {
                        if (strpos($file, '~') !== false) {
                            list($target,$slave) = explode('~', str_replace('.rrd', '', $file));
                            $target = str_replace('_', '.', $target);
                            $smokeping_files['in'][$target][$slave] = $file;
                            $smokeping_files['out'][$slave][$target] = $file;
                        } else {
                            $target = str_replace('.rrd', '', $file);
                            $target = str_replace('_', '.', $target);
                            $smokeping_files['in'][$target][Config::get('own_hostname')] = $file;
                            $smokeping_files['out'][Config::get('own_hostname')][$target] = $file;
                        }
                    }
                }
            }
        }
    }
    return $smokeping_files;
} // end get_smokeping_files


function generate_smokeping_file($device, $file = '')
{
    if (Config::get('smokeping.integration') === true) {
        return Config::get('smokeping.dir') . '/' . $device['type'] . '/' . $file;
    } else {
        return Config::get('smokeping.dir') . '/' . $file;
    }
} // generate_smokeping_file


/*
 * @return rounded value to 10th/100th/1000th depending on input (valid: 10, 100, 1000)
 */
function round_Nth($val, $round_to)
{
    if (($round_to == "10") || ($round_to == "100") || ($round_to == "1000")) {
        $diff = $val % $round_to;
        if ($diff >= ($round_to / 2)) {
            $ret = $val + ($round_to-$diff);
        } else {
            $ret = $val - $diff;
        }
        return $ret;
    }
} // end round_Nth


/*
 * @return true if this device should be polled with MIB-based discovery
 */
function is_mib_poller_enabled($device)
{
    $val = get_dev_attrib($device, 'poll_mib');
    if ($val == null) {
        return Config::get("poller_modules.mib", false);
    }
    return $val;
} // is_mib_poller_enabled


/*
 * FIXME: Dummy implementation
 */
function count_mib_mempools($device)
{
    if (is_mib_poller_enabled($device) && $device['os'] == 'ruckuswireless') {
        return 1;
    }
    return 0;
} // count_mib_mempools


/*
 * FIXME: Dummy implementation
 */
function count_mib_processors($device)
{
    if (is_mib_poller_enabled($device) && $device['os'] == 'ruckuswireless') {
        return 1;
    }
    return 0;
} // count_mib_processors


function count_mib_health($device)
{
    return count_mib_mempools($device) + count_mib_processors($device);
} // count_mib_health


function get_mibval($device, $oid)
{
    $sql = 'SELECT * FROM `device_oids` WHERE `device_id` = ? AND `oid` = ?';
    return dbFetchRow($sql, array($device['device_id'], $oid));
} // get_mibval


/*
 * FIXME: Dummy implementation - needs an abstraction for each device
 */
function get_mib_mempools($device)
{
    $mempools = array();
    if (is_mib_poller_enabled($device) && $device['os'] == 'ruckuswireless') {
        $mempool = array();
        $mibvals = get_mibval($device, '.1.3.6.1.4.1.25053.1.2.1.1.1.15.14.0');
        $mempool['mempool_descr'] = $mibvals['object_type'];
        $mempool['mempool_id'] = 0;
        $mempool['mempool_total'] = 100;
        $mempool['mempool_used'] = $mibvals['numvalue'];
        $mempool['mempool_free'] = 100 - $mibvals['numvalue'];
        $mempool['percentage'] = true;
        $mempools[] = $mempool;
    }
    return $mempools;
} // get_mib_mempools


/*
 * FIXME: Dummy implementation - needs an abstraction for each device
 */
function get_mib_processors($device)
{
    $processors = array();
    if (is_mib_poller_enabled($device) && $device['os'] == 'ruckuswireless') {
        $proc = array();
        $mibvals = get_mibval($device, '.1.3.6.1.4.1.25053.1.2.1.1.1.15.13.0');
        $proc['processor_descr'] = $mibvals['object_type'];
        $proc['processor_id'] = 0;
        $proc['processor_usage'] = $mibvals['numvalue'];
        $processors[] = $proc;
    }
    return $processors;
} // get_mib_processors


/*
 * FIXME: Dummy implementation - needs an abstraction for each device
 * @return true if there is a custom graph defined for this type, subtype, and device
 */
function is_custom_graph($type, $subtype, $device)
{
    if (is_mib_poller_enabled($device) && $device['os'] == 'ruckuswireless' && $type == 'device') {
        switch ($subtype) {
            case 'cpumem':
            case 'mempool':
            case 'processor':
                return true;
        }
    }
    return false;
} // is_custom_graph


/*
 * FIXME: Dummy implementation
 * Set section/graph entries in $graph_enable for graphs specific to $os.
 */
function enable_os_graphs($os, &$graph_enable)
{
    /*
    foreach (dbFetchRows("SELECT * FROM graph_conditions WHERE graph_type = 'device' AND condition_name = 'os' AND condition_value = ?", array($os)) as $graph) {
        $graph_enable[$graph['graph_section']][$graph['graph_subtype']] = "device_".$graph['graph_subtype'];
    }
    */
} // enable_os_graphs


/*
 * For each os-based or global graph relevant to $device, set its section/graph entry in $graph_enable.
 */
function enable_graphs($device, &$graph_enable)
{
    // These are standard graphs we should have for (almost) all systems
    $graph_enable['poller']['poller_perf']         = 'device_poller_perf';
    if (!$device['snmp_disable']) {
        $graph_enable['poller']['poller_modules_perf'] = 'device_poller_modules_perf';
    }
    if (get_dev_attrib($device, "override_icmp_disable") != "true" && can_ping_device($device) === true) {
        $graph_enable['poller']['ping_perf'] = 'device_ping_perf';
    }

    enable_os_graphs($device['os'], $graph_enable);
} // enable_graphs


//
// maintain a simple cache of objects
//

function object_add_cache($section, $obj)
{
    global $object_cache;
    $object_cache[$section][$obj] = true;
} // object_add_cache


function object_is_cached($section, $obj)
{
    global $object_cache;
    if (array_key_exists($obj, $object_cache)) {
        return $object_cache[$section][$obj];
    } else {
        return false;
    }
} // object_is_cached


/**
 * Checks if config allows us to ping this device
 * $attribs contains an array of all of this devices
 * attributes
 * @param array $attribs Device attributes
 * @return bool
 **/
function can_ping_device($attribs)
{
    if (Config::get('icmp_check') && !(isset($attribs['override_icmp_disable']) && $attribs['override_icmp_disable'] == "true")) {
        return true;
    } else {
        return false;
    }
} // end can_ping_device


/*
 * @return true if every string in $arr begins with $str
 */
function begins_with($str, $arr)
{
    foreach ($arr as $s) {
        $pos = strpos($s, $str);
        if ($pos === false || $pos > 0) {
            return false;
        }
    }
    return true;
} // begins_with


/*
 * @return the longest starting portion of $str that matches everything in $arr
 */
function longest_matching_prefix($str, $arr)
{
    $len = strlen($str);
    while ($len > 0) {
        $prefix = substr($str, 0, $len);
        if (begins_with($prefix, $arr)) {
            return $prefix;
        }
        $len -= 1;
    }
    return '';
} // longest_matching_prefix


function search_phrase_column($c)
{
    global $searchPhrase;
    return "$c LIKE '%$searchPhrase%'";
} // search_phrase_column


function print_mib_poller_disabled()
{
    echo '<h4>MIB polling is not enabled</h4>
<p>
Set \'poller_modules.mib\' in your config or enable for this device specifically to enable.
</p>';
} // print_mib_poller_disabled


/**
 * Constructs the path to an RRD for the Ceph application
 * @param string $gtype The type of rrd we're looking for
 * @return string
 **/
function ceph_rrd($gtype)
{
    global $device;
    global $vars;

    if ($gtype == "osd") {
        $var = $vars['osd'];
    } else {
        $var = $vars['pool'];
    }

    return rrd_name($device['hostname'], array('app', 'ceph', $vars['id'], $gtype, $var));
} // ceph_rrd

/**
 * Parse location field for coordinates
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
 * Returns version info
 * @param bool $remote fetch remote version info from github
 * @return array
 */
function version_info($remote = false)
{
    $version = \LibreNMS\Util\Version::get();
    $output = [
        'local_ver' => $version->local(),
    ];
    if (Git::repoPresent() && Git::binaryExists()) {
        if ($remote === true && Config::get('update_channel') == 'master') {
            $api = curl_init();
            set_curl_proxy($api);
            curl_setopt($api, CURLOPT_USERAGENT, 'LibreNMS');
            curl_setopt($api, CURLOPT_URL, Config::get('github_api') . 'commits/master');
            curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($api, CURLOPT_TIMEOUT, 5);
            curl_setopt($api, CURLOPT_TIMEOUT_MS, 5000);
            curl_setopt($api, CURLOPT_CONNECTTIMEOUT, 5);
            $output['github'] = json_decode(curl_exec($api), true);
        }
        list($local_sha, $local_date) = explode('|', rtrim(`git show --pretty='%H|%ct' -s HEAD`));
        $output['local_sha']    = $local_sha;
        $output['local_date']   = $local_date;
        $output['local_branch'] = rtrim(`git rev-parse --abbrev-ref HEAD`);
    }
    $output['db_schema']   = vsprintf('%s (%s)', $version->database());
    $output['php_ver']     = phpversion();
    $output['mysql_ver']   = dbIsConnected() ? dbFetchCell('SELECT version()') : '?';
    $output['rrdtool_ver'] = str_replace('1.7.01.7.0', '1.7.0', implode(' ', array_slice(explode(' ', shell_exec(
        Config::get('rrdtool', 'rrdtool') . ' --version |head -n1'
    )), 1, 1)));
    $output['netsnmp_ver'] = str_replace('version: ', '', rtrim(shell_exec(
        Config::get('snmpget', 'snmpget') . ' -V 2>&1'
    )));

    return $output;
}//end version_info()

/**
 * Convert a MySQL binary v4 (4-byte) or v6 (16-byte) IP address to a printable string.
 * @param string $ip A binary string containing an IP address, as returned from MySQL's INET6_ATON function
 * @return string Empty if not valid.
 */
// Fuction is from http://uk3.php.net/manual/en/function.inet-ntop.php
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
 * @param array $device (uses hostname and sysName fields)
 * @param string $hostname
 * @return string
 */
function format_hostname($device, $hostname = null)
{
    if (empty($hostname)) {
        $hostname = $device['hostname'];
    }

    if (Config::get('force_hostname_to_sysname') && !empty($device['sysName'])) {
        if (is_valid_hostname($hostname) && !IP::isValid($hostname)) {
            return $device['sysName'];
        }
    }

    if (Config::get('force_ip_to_sysname') && !empty($device['sysName'])) {
        if (IP::isValid($hostname)) {
            return $device['sysName'];
        }
    }

    return $hostname;
}

/**
 * Return valid port association modes
 * @return array
 */
function get_port_assoc_modes()
{
    return [
        1 => 'ifIndex',
        2 => 'ifName',
        3 => 'ifDescr',
        4 => 'ifAlias',
    ];
}

/**
 * Get DB id of given port association mode name
 * @param string $port_assoc_mode
 * @return int
 */
function get_port_assoc_mode_id($port_assoc_mode)
{
    $modes = array_flip(get_port_assoc_modes());

    return isset($modes[$port_assoc_mode]) ? $modes[$port_assoc_mode] : false;
}

/**
 * Get name of given port association_mode ID
 * @param int $port_assoc_mode_id Port association mode ID
 * @return bool
 */
function get_port_assoc_mode_name($port_assoc_mode_id)
{
    $modes = get_port_assoc_modes();

    return isset($modes[$port_assoc_mode_id]) ? $modes[$port_assoc_mode_id] : false;
}

/**
 * Query all ports of the given device (by ID) and build port array and
 * port association maps for ifIndex, ifName, ifDescr. Query port stats
 * if told to do so, too.
 * @param int $device_id ID of device to query ports for
 * @param bool $with_statistics Query port statistics, too. (optional, default false)
 * @return array
 */
function get_ports_mapped($device_id, $with_statistics = false)
{
    $ports = array();
    $maps = array(
        'ifIndex' => array(),
        'ifName'  => array(),
        'ifDescr' => array(),
    );

    if ($with_statistics) {
        /* ... including any related ports_statistics if requested */
        $query = 'SELECT *, `ports_statistics`.`port_id` AS `ports_statistics_port_id`, `ports`.`port_id` AS `port_id` FROM `ports` LEFT OUTER JOIN `ports_statistics` ON `ports`.`port_id` = `ports_statistics`.`port_id` WHERE `ports`.`device_id` = ? ORDER BY ports.port_id';
    } else {
        /* Query all information available for ports for this device ... */
        $query = 'SELECT * FROM `ports` WHERE `device_id` = ? ORDER BY port_id';
    }

    // Query known ports in order of discovery to make sure the latest
    // discoverd/polled port is in the mapping tables.
    foreach (dbFetchRows($query, array ($device_id)) as $port) {
        // Store port information by ports port_id from DB
        $ports[$port['port_id']] = $port;

        // Build maps from ifIndex, ifName, ifDescr to port_id
        $maps['ifIndex'][$port['ifIndex']] = $port['port_id'];
        $maps['ifName'][$port['ifName']]   = $port['port_id'];
        $maps['ifDescr'][$port['ifDescr']] = $port['port_id'];
    }

    return array(
        'ports' => $ports,
        'maps'  => $maps,
    );
}

/**
 * Calculate port_id of given port using given devices port information and port association mode
 * @param array $ports_mapped Port information of device queried by get_ports_mapped()
 * @param array $port Port information as fetched from DB
 * @param string $port_association_mode Port association mode to use for mapping
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
    $maps  = $ports_mapped['maps'];

    if (in_array($port_association_mode, array ('ifIndex', 'ifName', 'ifDescr', 'ifAlias'))) {
        $port_id = $maps[$port_association_mode][$port[$port_association_mode]];
    }

    return $port_id;
}

/**
 * Create a glue-chain
 * @param array $tables Initial Tables to construct glue-chain
 * @param string $target Glue to find (usual device_id)
 * @param int $x Recursion Anchor
 * @param array $hist History of processed tables
 * @param array $last Glues on the fringe
 * @return array|false
 */
function ResolveGlues($tables, $target, $x = 0, $hist = array(), $last = array())
{
    if (sizeof($tables) == 1 && $x != 0) {
        if (dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?', array($tables[0],$target)) == 1) {
            return array_merge($last, array($tables[0].'.'.$target));
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
                return array_merge($last, array(
                    'state_translations.state_index_id',
                    'sensors_to_state_indexes.sensor_id',
                    "sensors.$target",
                ));
            } elseif ($table == 'application_metrics' && $target == 'device_id') {
                return array_merge($last, array(
                    'application_metrics.app_id',
                    "applications.$target",
                ));
            } elseif ($table == 'locations' && $target == 'device_id') {
                return array_merge($last, [
                    'locations.id',
                    'devices.device_id.location_id'
                ]);
            }

            $glues = dbFetchRows('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME LIKE "%\_id"', array($table));
            if (sizeof($glues) == 1 && $glues[0]['COLUMN_NAME'] != $target) {
                //Search for new candidates to expand
                $ntables = array();
                list($tmp) = explode('_', $glues[0]['COLUMN_NAME'], 2);
                $ntables[] = $tmp;
                $ntables[] = $tmp.'s';
                $tmp = dbFetchRows('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME LIKE "'.substr($table, 0, -1).'_%" && TABLE_NAME != "'.$table.'"');
                foreach ($tmp as $expand) {
                    $ntables[] = $expand['TABLE_NAME'];
                }
                $tmp = ResolveGlues($ntables, $target, $x++, array_merge($tables, $ntables), array_merge($last, array($table.'.'.$glues[0]['COLUMN_NAME'])));
                if (is_array($tmp)) {
                    return $tmp;
                }
            } else {
                foreach ($glues as $glue) {
                    if ($glue['COLUMN_NAME'] == $target) {
                        return array_merge($last, array($table.'.'.$target));
                    } else {
                        list($tmp) = explode('_', $glue['COLUMN_NAME']);
                        $tmp .= 's';
                        if (!in_array($tmp, $tables) && !in_array($tmp, $hist)) {
                            //Expand table
                            $tmp = ResolveGlues(array($tmp), $target, $x++, array_merge($tables, array($tmp)), array_merge($last, array($table.'.'.$glue['COLUMN_NAME'])));
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

if (!function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    function str_contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string $haystack
 * @param  string|array $needles
 * @return bool
 */
function str_i_contains($haystack, $needles)
{
    foreach ((array)$needles as $needle) {
        if ($needle != '' && stripos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle))) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Print a list of items up to a max amount
 * If over that number, a line will print the total items
 *
 * @param array $list
 * @param string $format format as consumed by printf()
 * @param int $max the max amount of items to print, default 10
 */
function print_list($list, $format, $max = 10)
{
    if (is_array(current($list))) {
        $list = array_map(function ($item) {
            return implode(' ', $item);
        }, $list);
    }

    foreach (array_slice($list, 0, $max) as $item) {
        printf($format, $item);
    }

    $extra = count($list) - $max;
    if ($extra > 0) {
        printf($format, " and $extra more...");
    }
}

/**
 * @param $value
 * @param bool $strip_tags
 * @return string
 */
function clean($value, $strip_tags = true)
{
    if ($strip_tags === true) {
        return strip_tags(mres($value));
    } else {
        return mres($value);
    }
}

/**
 * @param $value
 * @param array $purifier_config (key, value pair)
 * @return string
 */
function display($value, $purifier_config = [])
{
    return \LibreNMS\Util\Clean::html($value, $purifier_config);
}

/**
 * Load the os definition for the device and set type and os_group
 * $device['os'] must be set
 *
 * @param array $device
 */
function load_os(&$device)
{
    if (!isset($device['os'])) {
        d_echo("No OS to load\n");
        return;
    }

    if (!Config::get("os.{$device['os']}.definition_loaded")) {
        $tmp_os = Symfony\Component\Yaml\Yaml::parse(
            file_get_contents(Config::get('install_dir') . '/includes/definitions/' . $device['os'] . '.yaml')
        );

        Config::set("os.{$device['os']}", array_replace_recursive($tmp_os, Config::get("os.{$device['os']}", [])));
    }

    // Set type to a predefined type for the OS if it's not already set
    $loaded_os_type = Config::get("os.{$device['os']}.type");
    if ((!isset($device['attribs']['override_device_type']) && $device['attribs']['override_device_type'] != 1) && $loaded_os_type != $device['type']) {
        log_event('Device type changed ' . $device['type'] . ' => ' . $loaded_os_type, $device, 'system', 3);
        $device['type'] = $loaded_os_type;
        dbUpdate(['type' => $loaded_os_type], 'devices', 'device_id=?', [$device['device_id']]);
        d_echo("Device type changed to $loaded_os_type!\n");
    }

    if ($os_group = Config::get("os.{$device['os']}.group")) {
        $device['os_group'] = $os_group;
    } else {
        unset($device['os_group']);
    }

    Config::set("os.{$device['os']}.definition_loaded", true);
}

/**
 * Load all OS, optionally load just the OS used by existing devices
 * Default cache time is 1 day. Controlled by os_def_cache_time.
 *
 * @param bool $existing Only load OS that have existing OS in the database
 * @param bool $cached Load os definitions from the cache file
 */
function load_all_os($existing = false, $cached = true)
{
    $install_dir = Config::get('install_dir');
    $cache_file = $install_dir . '/cache/os_defs.cache';

    if ($cached && is_file($cache_file) && (time() - filemtime($cache_file) < Config::get('os_def_cache_time'))) {
        // Cached
        $os_defs = unserialize(file_get_contents($cache_file));

        if ($existing) {
            // remove unneeded os
            $os_defs = array_diff_key($os_defs, dbFetchColumn('SELECT DISTINCT(`os`) FROM `devices`'));
        }

        Config::set('os', array_replace_recursive($os_defs, Config::get('os')));
    } else {
        // load from yaml
        if ($existing) {
            $os_list = array_map(function ($os) use ($install_dir) {
                return $install_dir . '/includes/definitions/' . $os . '.yaml';
            }, dbFetchColumn('SELECT DISTINCT(`os`) FROM `devices`'));
        } else {
            $os_list = glob($install_dir . '/includes/definitions/*.yaml');
        }

        foreach ($os_list as $file) {
            if (is_readable($file)) {
                $tmp = Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

                Config::set("os.{$tmp['os']}", array_replace_recursive($tmp, Config::get("os.{$tmp['os']}", [])));
            }
        }
    }
}

/**
 * * Update the OS cache file cache/os_defs.cache
 * @param bool $force
 * @return bool true if the cache was updated
 */
function update_os_cache($force = false)
{
    $install_dir = Config::get('install_dir');
    $cache_file = "$install_dir/cache/os_defs.cache";
    $cache_keep_time = Config::get('os_def_cache_time', 86400) - 7200; // 2hr buffer

    if ($force === true || !is_file($cache_file) || time() - filemtime($cache_file) > $cache_keep_time) {
        d_echo('Updating os_def.cache... ');

        // remove previously cached os settings and replace with user settings
        $config = ['os' => []]; // local $config variable, not global
        include "$install_dir/config.php";
        Config::set('os', $config['os']);

        // load the os defs fresh from cache (merges with existing OS settings)
        load_all_os(false, false);

        file_put_contents($cache_file, serialize(Config::get('os')));
        d_echo("Done\n");
        return true;
    }

    return false;
}

/**
 * Converts fahrenheit to celsius (with 2 decimal places)
 * if $scale is not fahrenheit, it assumes celsius and  returns the value
 *
 * @param float $value
 * @param string $scale fahrenheit or celsius
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
 * Converts uW to dBm
 * $value must be positive
 *
 */
function uw_to_dbm($value)
{
    return 10 * log10($value / 1000);
}

/**
 * @param $value
 * @param null $default
 * @param int $min
 * @return null
 */
function set_null($value, $default = null, $min = null)
{
    if (!is_numeric($value)) {
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
    if (!is_numeric($value) ||
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

    return dbFetchCell("SELECT `device_id` FROM `vminfo` WHERE `vmwVmDisplayName` = ? OR `vmwVmDisplayName` = ?", [$device['hostname'], $device['hostname'] . '.' . Config::get('mydomain')]);
}

/**
 * Fetch a user preference from the database
 * Do not use strict comparison as results could be strings
 *
 * @param string $name preference name
 * @param mixed $default value to return if the preference is not set
 * @param int $user_id for this user_id otherwise, the currently logged in user
 * @return mixed value of this preference
 */
function get_user_pref($name, $default = null, $user_id = null)
{
    global $user_prefs;

    if (is_array($user_prefs) && array_key_exists($name, $user_prefs)) {
        return $user_prefs[$name];
    }

    if (is_null($user_id)) {
        $user_id = Auth::id();
    }

    $pref = dbFetchCell(
        'SELECT `value` FROM `users_prefs` WHERE `user_id`=? AND `pref`=?',
        array($user_id, $name)
    );

    if (!is_null($pref)) {
        $pref = json_decode($pref, true);
        $user_prefs[$name] = $pref;
        return $pref;
    }

    return $default;
}

/**
 * Set a user preference value
 *
 * @param string $name preference name
 * @param mixed $value value of this preference
 * @param int $user_id for this user_id otherwise, the currently logged in user
 * @return bool whether the setting was changed or not
 */
function set_user_pref($name, $value, $user_id = null)
{
    global $user_prefs;
    if (is_null($user_id)) {
        $user_id = Auth::id();
    }

    $pref = array(
        'user_id' => $user_id,
        'pref' => $name,
        'value' => json_encode($value),
    );

    if (dbFetchCell('SELECT count(*) FROM `users_prefs` WHERE `user_id`=? AND `pref`=?', array($user_id, $name))) {
        $update = array('value' => json_encode($value));
        $params = array($user_id, $name);

        $result = dbUpdate($update, 'users_prefs', '`user_id`=? AND `pref`=?', $params) > 0;
    } else {
        $result = dbInsert($pref, 'users_prefs') !== null;
    }

    if ($result) {
        $user_prefs[$name] = $value;
    }

    return $result;
}

/**
 * Generate a class name from a lowercase string containing - or _
 * Remove - and _ and camel case words
 *
 * @param string $name The string to convert to a class name
 * @param string $namespace namespace to prepend to the name for example: LibreNMS\
 * @return string  Class name
 */
function str_to_class($name, $namespace = null)
{
    $pre_format = str_replace(array('-', '_'), ' ', $name);
    $class = str_replace(' ', '', ucwords(strtolower($pre_format)));
    return $namespace . $class;
}

/**
 * Checks file permissions against a minimum permissions mask.
 * This only check that bits are enabled, not disabled.
 * The mask is in the same format as posix permissions. For example, 600 means user read and write.
 *
 * @param string $file the name of the file to check
 * @param $mask
 * @return bool
 */
function check_file_permissions($file, $mask)
{
    $perms = fileperms($file);
    $mask = octdec($mask);

    return ($perms & $mask) === $mask;
}

/**
 * Index an array by a column
 *
 * @param array $array
 * @param string|int $column
 * @return array
 */
function array_by_column($array, $column)
{
    return array_combine(array_column($array, $column), $array);
}
