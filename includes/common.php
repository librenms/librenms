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

function format_number_short($number, $sf) {
    // This formats a number so that we only send back three digits plus an optional decimal point.
    // Example: 723.42 -> 723    72.34 -> 72.3    2.23 -> 2.23

    list($whole, $decimal) = explode (".", $number);

    if (strlen($whole) >= $sf || !is_numeric($decimal)) {
        $number = $whole;
    }
    elseif(strlen($whole) < $sf) {
        $diff = $sf - strlen($whole);
        $number = $whole .".".substr($decimal, 0, $diff);
    }
    return $number;
}

function external_exec($command) {
    d_echo($command."\n");
    $output = shell_exec($command);
    d_echo($output."\n");

    return $output;
}

function shorthost($hostname, $len=12) {
    // IP addresses should not be shortened
    if (filter_var($hostname, FILTER_VALIDATE_IP))
        return $hostname;

    $parts = explode(".", $hostname);
    $shorthost = $parts[0];
    $i = 1;
    while ($i < count($parts) && strlen($shorthost.'.'.$parts[$i]) < $len) {
        $shorthost = $shorthost.'.'.$parts[$i];
        $i++;
    }
    return ($shorthost);
}

function isCli() {
    if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    }
    else {
        return false;
    }
}

function print_error($text) {
    global $console_color;
    if (isCli()) {
        print $console_color->convert("%r".$text."%n\n", false);
    }
    else {
        echo('<div class="alert alert-danger"><img src="images/16/exclamation.png" align="absmiddle"> '.$text.'</div>');
    }
}

function print_message($text) {
    if (isCli()) {
        print Console_Color2::convert("%g".$text."%n\n", false);
    }
    else {
        echo('<div class="alert alert-success"><img src="images/16/tick.png" align="absmiddle"> '.$text.'</div>');
    }
}

function delete_port($int_id) {
    global $config;

    $interface = dbFetchRow("SELECT * FROM `ports` AS P, `devices` AS D WHERE P.port_id = ? AND D.device_id = P.device_id", array($int_id));

    $interface_tables = array('adjacencies', 'ipaddr', 'ip6adjacencies', 'ip6addr', 'mac_accounting', 'bill_ports', 'pseudowires', 'ports');

    foreach ($interface_tables as $table) {
        dbDelete($table, "`port_id` =  ?", array($int_id));
    }

    dbDelete('links', "`local_port_id` =  ?", array($int_id));
    dbDelete('links', "`remote_port_id` =  ?", array($int_id));
    dbDelete('bill_ports', "`port_id` =  ?", array($int_id));

    unlink(trim($config['rrd_dir'])."/".trim($interface['hostname'])."/port-".$interface['ifIndex'].".rrd");
}

function sgn($int) {
    if ($int < 0) {
        return -1;
    }
    elseif ($int == 0) {
        return 0;
    }
    else {
        return 1;
    }
}

function get_sensor_rrd($device, $sensor) {
    global $config;

    # For IPMI, sensors tend to change order, and there is no index, so we prefer to use the description as key here.
    if ($config['os'][$device['os']]['sensor_descr'] || $sensor['poller_type'] == "ipmi") {
        $rrd_file = $config['rrd_dir']."/".$device['hostname']."/".safename("sensor-".$sensor['sensor_class']."-".$sensor['sensor_type']."-".$sensor['sensor_descr'] . ".rrd");
    }
    else {
        $rrd_file = $config['rrd_dir']."/".$device['hostname']."/".safename("sensor-".$sensor['sensor_class']."-".$sensor['sensor_type']."-".$sensor['sensor_index'] . ".rrd");
    }

    return($rrd_file);
}

function get_port_by_index_cache($device_id, $ifIndex) {
    global $port_index_cache;

    if (isset($port_index_cache[$device_id][$ifIndex]) && is_array($port_index_cache[$device_id][$ifIndex])) {
        $port = $port_index_cache[$device_id][$ifIndex];
    }
    else {
        $port = get_port_by_ifIndex($device_id, $ifIndex);
        $port_index_cache[$device_id][$ifIndex] = $port;
    }

    return $port;
}

function get_port_by_ifIndex($device_id, $ifIndex) {
    return dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device_id, $ifIndex));
}

function get_all_devices($device, $type = "") {
    global $cache;
    $devices = array();

    // FIXME needs access control checks!
    // FIXME respect $type (server, network, etc) -- needs an array fill in topnav.

    if (isset($cache['devices']['hostname'])) {
        $devices = array_keys($cache['devices']['hostname']);
    }
    else {
        foreach (dbFetchRows("SELECT `hostname` FROM `devices`") as $data) {
            $devices[] = $data['hostname'];
        }
    }

    return $devices;
}

function port_by_id_cache($port_id) {
    return get_port_by_id_cache('port', $port_id);
}

function table_from_entity_type($type) {
    // Fuck you, english pluralisation.
    if ($type == "storage") {
        return $type;
    }
    else {
        return $type."s";
    }
}

function get_entity_by_id_cache($type, $id) {
    global $entity_cache;

    $table = table_from_entity_type($type);

    if (is_array($entity_cache[$type][$id])) {
        $entity = $entity_cache[$type][$id];
    }
    else {
        $entity = dbFetchRow("SELECT * FROM `".$table."` WHERE `".$type."_id` = ?", array($id));
        $entity_cache[$type][$id] = $entity;
    }
    return $entity;
}

function get_port_by_id($port_id) {
    if (is_numeric($port_id)) {
        $port = dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($port_id));
        if (is_array($port)) {
            return $port;
        }
        else {
            return FALSE;
        }
    }
}

function get_application_by_id($application_id) {
    if (is_numeric($application_id)) {
        $application = dbFetchRow("SELECT * FROM `applications` WHERE `app_id` = ?", array($application_id));
        if (is_array($application)) {
            return $application;
        }
        else {
            return FALSE;
        }
    }
}

function get_sensor_by_id($sensor_id) {
    if (is_numeric($sensor_id)) {
        $sensor = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_id` = ?", array($sensor_id));
        if (is_array($sensor)) {
            return $sensor;
        }
        else {
            return FALSE;
        }
    }
}

function get_device_id_by_port_id($port_id) {
    if (is_numeric($port_id)) {
        $device_id = dbFetchCell("SELECT `device_id` FROM `ports` WHERE `port_id` = ?", array($port_id));
        if (is_numeric($device_id)) {
            return $device_id;
        }
        else {
            return FALSE;
        }
    }
}

function get_device_id_by_app_id($app_id) {
    if (is_numeric($app_id)) {
        $device_id = dbFetchCell("SELECT `device_id` FROM `applications` WHERE `app_id` = ?", array($app_id));
        if (is_numeric($device_id)) {
            return $device_id;
        }
        else {
            return FALSE;
        }
    }
}

function ifclass($ifOperStatus, $ifAdminStatus) {
    $ifclass = "interface-upup";
    if ($ifAdminStatus == "down") {
        $ifclass = "interface-admindown";
    }
    if ($ifAdminStatus == "up" && $ifOperStatus== "down") {
        $ifclass = "interface-updown";
    }
    if ($ifAdminStatus == "up" && $ifOperStatus== "up") {
        $ifclass = "interface-upup";
    }
    return $ifclass;
}

function device_by_name($name, $refresh = 0) {
    // FIXME - cache name > id too.
    return device_by_id_cache(getidbyname($name), $refresh);
}


function accesspoint_by_id($ap_id, $refresh = '0') {

    $ap = dbFetchRow("SELECT * FROM `access_points` WHERE `accesspoint_id` = ?", array($ap_id));

    return $ap;

}


function device_by_id_cache($device_id, $refresh = '0') {
    global $cache;

    if (!$refresh && isset($cache['devices']['id'][$device_id]) && is_array($cache['devices']['id'][$device_id])) {
        $device = $cache['devices']['id'][$device_id];
    }
    else {
        $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device_id));
        $cache['devices']['id'][$device_id] = $device;
    }
    return $device;
}

function truncate($substring, $max = 50, $rep = '...') {
    if (strlen($substring) < 1) {
        $string = $rep;
    }
    else {
        $string = $substring;
    }
    $leave = $max - strlen ($rep);
    if (strlen($string) > $max) {
        return substr_replace($string, $rep, $leave);
    }
    else {
        return $string;
    }
}

function mres($string) {
    // short function wrapper because the real one is stupidly long and ugly. aesthetics.
    global $config, $database_link;
    if ($config['db']['extension'] == 'mysqli') {
        return mysqli_real_escape_string($database_link,$string);
    }
    else {
        return mysql_real_escape_string($string);
    }
}

function getifhost($id) {
    return dbFetchCell("SELECT `device_id` from `ports` WHERE `port_id` = ?", array($id));
}

function gethostbyid($id) {
    global $cache;

    if (isset($cache['devices']['id'][$id]['hostname'])) {
        $hostname = $cache['devices']['id'][$id]['hostname'];
    }
    else {
        $hostname = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
    }

    return $hostname;
}

function strgen ($length = 16) {
    $entropy = array(0,1,2,3,4,5,6,7,8,9,'a','A','b','B','c','C','d','D','e',
        'E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n',
        'N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w',
        'W','x','X','y','Y','z','Z');
    $string = "";

    for ($i=0; $i<$length; $i++) {
        $key = mt_rand(0,61);
        $string .= $entropy[$key];
    }

    return $string;
}

function getpeerhost($id) {
    return dbFetchCell("SELECT `device_id` from `bgpPeers` WHERE `bgpPeer_id` = ?", array($id));
}

function getifindexbyid($id) {
    return dbFetchCell("SELECT `ifIndex` FROM `ports` WHERE `port_id` = ?", array($id));
}

function getifbyid($id) {
    return dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($id));
}

function getifdescrbyid($id) {
    return dbFetchCell("SELECT `ifDescr` FROM `ports` WHERE `port_id` = ?", array($id));
}

function getidbyname($hostname) {
    global $cache;

    if (isset($cache['devices']['hostname'][$hostname])) {
        $id = $cache['devices']['hostname'][$hostname];
    }
    else {
        $id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `hostname` = ?", array($hostname));
    }

    return $id;
}

function gethostosbyid($id) {
    global $cache;

    if (isset($cache['devices']['id'][$id]['os'])) {
        $os = $cache['devices']['id'][$id]['os'];
    }
    else {
        $os = dbFetchCell("SELECT `os` FROM `devices` WHERE `device_id` = ?", array($id));
    }

    return $os;
}

function safename($name) {
    return preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
}

function zeropad($num, $length = 2) {
    while (strlen($num) < $length) {
        $num = '0'.$num;
    }

    return $num;
}

function set_dev_attrib($device, $attrib_type, $attrib_value) {
    if (dbFetchCell("SELECT COUNT(*) FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ?", array($device['device_id'],$attrib_type))) {
        $return = dbUpdate(array('attrib_value' => $attrib_value), 'devices_attribs', 'device_id=? and attrib_type=?', array($device['device_id'], $attrib_type));
    }
    else {
        $return = dbInsert(array('device_id' => $device['device_id'], 'attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'devices_attribs');
    }
    return $return;
}

function get_dev_attribs($device) {
    $attribs = array();
    foreach (dbFetchRows("SELECT * FROM devices_attribs WHERE `device_id` = ?", array($device)) as $entry) {
        $attribs[$entry['attrib_type']] = $entry['attrib_value'];
    }
    return $attribs;
}

function get_dev_entity_state($device) {
    $state = array();
    foreach (dbFetchRows("SELECT * FROM entPhysical_state WHERE `device_id` = ?", array($device)) as $entity) {
        $state['group'][$entity['group']][$entity['entPhysicalIndex']][$entity['subindex']][$entity['key']] = $entity['value'];
        $state['index'][$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] = $entity['value'];
    }
    return $state;
}

function get_dev_attrib($device, $attrib_type, $attrib_value='') {
    $sql = '';
    $params = array($device['device_id'], $attrib_type);
    if (!empty($attrib_value)) {
        $sql = " AND `attrib_value`=?";
        array_push($params, $attrib_value);
    }
    if ($row = dbFetchRow("SELECT attrib_value FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ? $sql", $params)) {
        return $row['attrib_value'];
    }
    else {
        return NULL;
    }
}

function is_dev_attrib_enabled($device, $attrib, $default = true) {
    $val = get_dev_attrib($device, $attrib);
    if ($val != NULL) {
        // attribute is set
        return ($val != 0);
    }
    else {
        // attribute not set
        return $default;
    }
}

function del_dev_attrib($device, $attrib_type) {
    return dbDelete('devices_attribs', "`device_id` = ? AND `attrib_type` = ?", array($device['device_id'], $attrib_type));
}

function formatRates($value, $round = '2', $sf = '3') {
    $value = format_si($value, $round, $sf) . "bps";
    return $value;
}

function formatStorage($value, $round = '2', $sf = '3') {
    $value = format_bi($value, $round) . "B";
    return $value;
}

function format_si($value, $round = '2', $sf = '3') {
    $neg = 0;
    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }

    if ($value >= "0.1") {
        $sizes = Array('', 'k', 'M', 'G', 'T', 'P', 'E');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value >= 1000)); $i++) {
            $value = $value / 1000;
            $ext  = $sizes[$i];
        }
    }
    else {
        $sizes = Array('', 'm', 'u', 'n');
        $ext = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($value != 0) && ($value <= 0.1)); $i++) {
            $value = $value * 1000;
            $ext  = $sizes[$i];
        }
    }

    if ($neg == 1) {
        $value = $value * -1;
    }

        return format_number_short(round($value, $round),$sf).$ext;
}

function format_bi($value, $round = '2', $sf = '3'){
    if ($value < "0") {
        $neg = 1;
        $value = $value * -1;
    }
    $sizes = Array('', 'k', 'M', 'G', 'T', 'P', 'E');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value >= 1024)); $i++) {
        $value = $value / 1024;
        $ext  = $sizes[$i];
    }

    if ($neg) {
        $value = $value * -1;
    }

    return format_number_short(round($value, $round), $sf).$ext;
}

function format_number($value, $base = '1000', $round=2, $sf=3) {
    if ($base == '1000') {
        return format_si($value, $round, $sf);
    }
    else {
        return format_bi($value, $round, $sf);
    }
}

function is_valid_hostname($hostname) {
    // The Internet standards (Request for Comments) for protocols mandate that
    // component hostname labels may contain only the ASCII letters 'a' through 'z'
    // (in a case-insensitive manner), the digits '0' through '9', and the hyphen
    // ('-'). The original specification of hostnames in RFC 952, mandated that
    // labels could not start with a digit or with a hyphen, and must not end with
    // a hyphen. However, a subsequent specification (RFC 1123) permitted hostname
    // labels to start with digits. No other symbols, punctuation characters, or
    // white space are permitted. While a hostname may not contain other characters,
    // such as the underscore character (_), other DNS names may contain the underscore

    return ctype_alnum(str_replace('_','',str_replace('-','',str_replace('.','',$hostname))));
}

function add_service($device, $service, $descr, $service_ip, $service_param = "", $service_ignore = 0) {

    if (!is_array($device)) {
        $device = device_by_id_cache($device);
    }

    if (empty($service_ip)) {
        $service_ip = $device['hostname'];
    }

    $insert = array('device_id' => $device['device_id'], 'service_ip' => $service_ip, 'service_type' => $service,
        'service_changed' => array('UNIX_TIMESTAMP(NOW())'), 'service_desc' => $descr, 'service_param' => $service_param, 'service_ignore' => $service_ignore);

    return dbInsert($insert, 'services');
}

function edit_service($service, $descr, $service_ip, $service_param = "", $service_ignore = 0) {

    if (!is_numeric($service)) {
        return false;
    }

    $update = array('service_ip' => $service_ip,
        'service_changed' => array('UNIX_TIMESTAMP(NOW())'),
        'service_desc' => $descr,
        'service_param' => $service_param,
        'service_ignore' => $service_ignore);
    return dbUpdate($update, 'services', '`service_id`=?', array($service));

}


/*
 * convenience function - please use this instead of 'if ($debug) { echo ...; }'
 */
function d_echo($text, $no_debug_text = null) {
    global $debug;
    if ($debug) {
        if (is_array($text)) {
            print_r($text);
        }
        else {
            echo "$text";
        }
    }
    elseif ($no_debug_text) {
        echo "$no_debug_text";
    }
} // d_echo


/*
 * convenience function - please use this instead of 'if ($debug) { print_r ...; }'
 */
function d_print_r($var, $no_debug_text = null) {
    global $debug;
    if ($debug) {
        print_r($var);
    }
    elseif ($no_debug_text) {
        echo "$no_debug_text";
    }
} // d_print_r


/*
 * @return true if the given graph type is a dynamic MIB graph
 */
function is_mib_graph($type, $subtype) {
    global $config;
    return $config['graph_types'][$type][$subtype]['section'] == 'mib';
} // is_mib_graph


/*
 * @return true if client IP address is authorized to access graphs
 */
function is_client_authorized($clientip) {
    global $config;

    if (isset($config['allow_unauth_graphs']) && $config['allow_unauth_graphs']) {
        d_echo("Unauthorized graphs allowed\n");
        return true;
    }

    if (isset($config['allow_unauth_graphs_cidr'])) {
        foreach ($config['allow_unauth_graphs_cidr'] as $range) {
            if (Net_IPv4::ipInNetwork($clientip, $range)) {
                d_echo("Unauthorized graphs allowed from $range\n");
                return true;
            }
        }
    }

    return false;
} // is_client_authorized


/*
 * @return an array of all graph subtypes for the given type
 */
function get_graph_subtypes($type, $device = null)
{
    global $config;

    $types = array();

    // find the subtypes defined in files
    if ($handle = opendir($config['install_dir'] . "/html/includes/graphs/$type/")) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "auth.inc.php" && strstr($file, ".inc.php")) {
                $types[] = str_replace(".inc.php", "", $file);
            }
        }
        closedir($handle);
    }

    // find the MIB subtypes
    foreach ($config['graph_types'] as $type => $unused1) {
        foreach ($config['graph_types'][$type] as $subtype => $unused2) {
            if (is_mib_graph($type, $subtype)  &&  $device != null  &&  is_device_graph($device, $subtype)) {
                $types[] = $subtype;
            }
        }
    }

    sort($types);
    return $types;
} // get_graph_subtypes


function is_device_graph($device, $subtype)
{
    $query = 'SELECT COUNT(*) FROM `device_graphs` WHERE `device_id` = ? AND `graph` = ?';
    return dbFetchCell($query, array($device['device_id'], $subtype)) > 0;
} // is_device_graph


function get_smokeping_files($device) {
    global $config;
    $smokeping_files = array();
    if (isset($config['smokeping']['dir'])) {
        $smokeping_dir = generate_smokeping_file($device);
        if ($handle = opendir($smokeping_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (eregi('.rrd', $file)) {
                        if (eregi('~', $file)) {
                            list($target,$slave) = explode('~', str_replace('.rrd', '', $file));
                            $target = str_replace('_', '.', $target);
                            $smokeping_files['in'][$target][$slave] = $file;
                            $smokeping_files['out'][$slave][$target] = $file;
                        }
                        else {
                            $target = str_replace('.rrd', '', $file);
                            $target = str_replace('_', '.', $target);
                            $smokeping_files['in'][$target][$config['own_hostname']] = $file;
                            $smokeping_files['out'][$config['own_hostname']][$target] = $file;
                        }
                    }
                }
            }
        }
    }
    return $smokeping_files;
} // end get_smokeping_files


function generate_smokeping_file($device,$file='') {
    global $config;
    if ($config['smokeping']['integration'] === true) {
        return $config['smokeping']['dir'] .'/'. $device['type'] .'/' . $file;
    }
    else {
        return $config['smokeping']['dir'] . '/' . $file;
    }
} // generate_smokeping_file


/*
 * @return rounded value to 10th/100th/1000th depending on input (valid: 10, 100, 1000)
 */
function round_Nth($val = 0, $round_to) {
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
    if (!is_module_enabled('poller', 'mib')) {
        return false;
    }

    if (!is_dev_attrib_enabled($device, 'poll_mib')) {
        d_echo('MIB module disabled for '.$device['hostname']."\n");
        return false;
    }

    return true;
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
    // These are standard graphs we should have for all systems
    $graph_enable['poller']['poller_perf'] = 'device_poller_perf';
    if (can_ping_device($device) === true) {
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
    }
    else {
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
function can_ping_device($attribs) {
    global $config;
    if ($config['icmp_check'] === true && $attribs['override_icmp_disable'] != "true") {
        return true;
    }
    else {
        return false;
    }
} // end can_ping_device


/*
 * @return true if the requested module type & name is globally enabled
 */
function is_module_enabled($type, $module)
{
    global $config;
    if (isset($config[$type.'_modules'][$module])) {
        return $config[$type.'_modules'][$module] == 1;
    }
    else {
        return false;
    }
} // is_module_enabled


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


function print_mib_poller_disabled() {
    echo '<h4>MIB polling is not enabled</h4>
<p>
Set <tt>$config[\'poller_modules\'][\'mib\'] = 1;</tt> in <tt>config.php</tt> to enable.
</p>';
} // print_mib_poller_disabled


/**
 * Constructs the path to an RRD for the Ceph application
 * @param string $gtype The type of rrd we're looking for
 * @return string
**/
function ceph_rrd($gtype) {
    global $device;
    global $vars;
    global $config;

    if ($gtype == "osd") {
        $var = $vars['osd'];
    }
    else {
        $var = $vars['pool'];
    }

    $rrd = join('-', array('app', 'ceph', $vars['id'], $gtype, $var)).'.rrd';
    return join('/', array($config['rrd_dir'], $device['hostname'], $rrd));
} // ceph_rrd

/**
 * Parse location field for coordinates
 * @param string location The location field to look for coords in.
 * @return array Containing the lat and lng coords
**/
function parse_location($location) {
    preg_match('/(\[)(-?[0-9\. ]+),[ ]*(-?[0-9\. ]+)(\])/', $location, $tmp_loc);
    if (!empty($tmp_loc[2]) && !empty($tmp_loc[3])) {
        return array('lat' => $tmp_loc[2], 'lng' => $tmp_loc[3]);
    }
}//end parse_location()

/**
 * Returns version info
 * @return array
**/
function version_info($remote=true) {
    global $config;
    $output = array();
    if ($remote === true && $config['update_channel'] == 'master') {
        $api = curl_init();
        set_curl_proxy($api);
        curl_setopt($api, CURLOPT_USERAGENT,'LibreNMS');
        curl_setopt($api, CURLOPT_URL, $config['github_api'].'commits/master');
        curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
        $output['github'] = json_decode(curl_exec($api),true);
    }
    $output['local_sha']   = chop(`git rev-parse HEAD`);
    $output['db_schema']   = dbFetchCell('SELECT version FROM dbSchema');
    $output['php_ver']     = phpversion();
    $output['mysql_ver']   = dbFetchCell('SELECT version()');
    $output['rrdtool_ver'] = implode(' ', array_slice(explode(' ', shell_exec($config['rrdtool'].' --version |head -n1')), 1, 1));
    $output['netsnmp_ver'] = shell_exec($config['snmpget'].' --version 2>&1');

    return $output;

}//end version_info()

/**
* Convert a MySQL binary v4 (4-byte) or v6 (16-byte) IP address to a printable string.
* @param string $ip A binary string containing an IP address, as returned from MySQL's INET6_ATON function
* @return string Empty if not valid.
*/
// Fuction is from http://uk3.php.net/manual/en/function.inet-ntop.php
function inet6_ntop($ip) {
    $l = strlen($ip);
    if ($l == 4 or $l == 16) {
        return inet_ntop(pack('A' . $l, $ip));
    }
    return '';
}

/**
 * Convert IP to use sysName
 * @param array device
 * @param string ip address
 * @return string
**/
function ip_to_sysname($device,$ip) {
    global $config;
    if ($config['force_ip_to_sysname'] === true) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == true || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == true) {
            $ip = $device['sysName'];
        }
    }
    return $ip;
}//end ip_to_sysname
