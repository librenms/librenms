<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;

function authToken(\Slim\Route $route)
{
    global $permissions;

    if (Auth::check()) {
        $user = Auth::user();

        // Fake session so the standard auth/permissions checks work
        $_SESSION = [
            'username' => $user->username,
            'user_id' => $user->user_id,
            'userlevel' => $user->level
        ];
        $permissions = permissions_cache($user->user_id);

        return;
    }

    api_error(401, 'API Token is missing or invalid; please supply a valid token');
}

function api_success($result, $result_name, $message = null, $code = 200, $count = null, $extra = null)
{
    if (isset($result) && !isset($result_name)) {
        api_error(500, 'Result name not specified');
    }

    $app  = \Slim\Slim::getInstance();
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    $output = array('status'  => 'ok');

    if (isset($result)) {
        $output[$result_name] = $result;
    }
    if (isset($message) && $message != '') {
        $output['message'] = $message;
    }
    if (!isset($count) && is_array($result)) {
        $count = count($result);
    }
    if (isset($count)) {
        $output['count'] = $count;
    }
    if (isset($extra)) {
        $output = array_merge($output, $extra);
    }
    echo _json_encode($output);
    $app->stop();
} // end api_success()

function api_success_noresult($code, $message = null)
{
    api_success(null, null, $message, $code);
} // end api_success_noresult

function api_error($statusCode, $message)
{
    $app  = \Slim\Slim::getInstance();
    $app->response->setStatus($statusCode);
    $app->response->headers->set('Content-Type', 'application/json');
    $output = array(
        'status'  => 'error',
        'message' => $message
    );
    echo _json_encode($output);
    $app->stop();
} // end api_error()

function check_bill_permission($bill_id)
{
    if (!bill_permitted($bill_id)) {
        api_error(403, 'Insufficient permissions to access this bill');
    }
}

function check_device_permission($device_id)
{
    if (!device_permitted($device_id)) {
        api_error(403, 'Insufficient permissions to access this device');
    }
}

function check_port_permission($port_id, $device_id)
{
    if (!device_permitted($device_id) && !port_permitted($port_id, $device_id)) {
        api_error(403, 'Insufficient permissions to access this port');
    }
}

function check_is_admin()
{
    if (!LegacyAuth::user()->hasGlobalAdmin()) {
        api_error(403, 'Insufficient privileges');
    }
}

function check_is_read()
{
    if (!LegacyAuth::user()->hasGlobalRead()) {
        api_error(403, 'Insufficient privileges');
    }
}

function check_not_demo()
{
    global $config;
    if ($config['api_demo'] == 1) {
        api_error(500, 'This feature isn\'t available in the demo');
    }
}

function get_graph_by_port_hostname()
{
    // This will return a graph for a given port by the ifName
    global $config;
    $app          = \Slim\Slim::getInstance();
    $router       = $app->router()->getCurrentRoute()->getParams();
    $hostname     = $router['hostname'];
    $device_id    = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $vars         = array();
    $vars['port'] = urldecode($router['ifname']);
    $vars['type'] = $router['type'] ?: 'port_bits';
    $vars['output'] = $_GET['output'] ?: 'display';
    if (!empty($_GET['from'])) {
        $vars['from'] = $_GET['from'];
    }

    if (!empty($_GET['to'])) {
        $vars['to'] = $_GET['to'];
    }

    if ($_GET['ifDescr'] == true) {
        $port = 'ifDescr';
    } else {
        $port = 'ifName';
    }

    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;
    $auth           = '1';
    $vars['id']     = dbFetchCell("SELECT `P`.`port_id` FROM `ports` AS `P` JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=? AND `P`.`$port`=? AND `deleted` = 0 LIMIT 1", array($device_id, $vars['port']));

    check_port_permission($vars['id'], $device_id);
    $app->response->headers->set('Content-Type', get_image_type());
    rrdtool_initialize(false);
    include 'includes/graphs/graph.inc.php';
    rrdtool_close();
    if ($vars['output'] === 'base64') {
        api_success(['image' => $base64_output, 'content-type' => get_image_type()], 'image');
    }
}


function get_port_stats_by_port_hostname()
{
    // This will return port stats based on a devices hostname and ifName
    $app       = \Slim\Slim::getInstance();
    $router    = $app->router()->getCurrentRoute()->getParams();
    $hostname  = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $ifName    = urldecode($router['ifname']);
    $port     = dbFetchRow('SELECT * FROM `ports` WHERE `device_id`=? AND `ifName`=? AND `deleted` = 0', array($device_id, $ifName));

    check_port_permission($port['port_id'], $device_id);

    $in_rate = $port['ifInOctets_rate'] * 8;
    $out_rate = $port['ifOutOctets_rate'] * 8;
    $port['in_rate'] = formatRates($in_rate);
    $port['out_rate'] = formatRates($out_rate);
    $port['in_perc'] = number_format($in_rate / $port['ifSpeed'] * 100, 2, '.', '');
    $port['out_perc'] = number_format($out_rate / $port['ifSpeed'] * 100, 2, '.', '');
    $port['in_pps'] = format_bi($port['ifInUcastPkts_rate']);
    $port['out_pps'] = format_bi($port['ifOutUcastPkts_rate']);

    //only return requested columns
    if (isset($_GET['columns'])) {
        $cols = explode(",", $_GET['columns']);
        foreach (array_keys($port) as $c) {
            if (!in_array($c, $cols)) {
                unset($port[$c]);
            }
        }
    }

    api_success($port, 'port');
}


function get_graph_generic_by_hostname()
{
    // This will return a graph type given a device id.
    global $config;
    $app          = \Slim\Slim::getInstance();
    $router       = $app->router()->getCurrentRoute()->getParams();
    $hostname     = $router['hostname'];
    $sensor_id    = $router['sensor_id'] ?: null;
    $vars         = array();
    $vars['type'] = $router['type'] ?: 'device_uptime';
    $vars['output'] = $_GET['output'] ?: 'display';
    if (isset($sensor_id)) {
        $vars['id']   = $sensor_id;
        if (str_contains($vars['type'], '_wireless')) {
            $vars['type'] = str_replace('device_', '', $vars['type']);
        } else {
            // If this isn't a wireless graph we need to fix the name.
            $vars['type'] = str_replace('device_', 'sensor_', $vars['type']);
        }
    }

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $device = device_by_id_cache($device_id);

    check_device_permission($device_id);

    if (!empty($_GET['from'])) {
        $vars['from'] = $_GET['from'];
    }

    if (!empty($_GET['to'])) {
        $vars['to'] = $_GET['to'];
    }

    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;
    $auth           = '1';
    $vars['device'] = dbFetchCell('SELECT `D`.`device_id` FROM `devices` AS `D` WHERE `D`.`hostname`=?', array($hostname));
    $app->response->headers->set('Content-Type', get_image_type());
    rrdtool_initialize(false);
    include 'includes/graphs/graph.inc.php';
    rrdtool_close();

    if ($vars['output'] === 'base64') {
        api_success(['image' => $base64_output, 'content-type' => get_image_type()], 'image');
    }
}


function list_locations()
{
    check_is_read();

    $app           = \Slim\Slim::getInstance();
    $router        = $app->router()->getCurrentRoute()->getParams();

    $locations   = dbFetchRows("SELECT `locations`.* FROM `locations` WHERE `locations`.`location` IS NOT NULL");
    $total_locations = count($locations);
    if ($total_locations == 0) {
        api_error(404, 'Locations do not exist');
    }

    api_success($locations, 'locations');
}


function get_device()
{
    // return details of a single device
    $app = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);

    // find device matching the id
    $device = device_by_id_cache($device_id);
    if (!$device) {
        api_error(404, "Device $hostname does not exist");
    }

    check_device_permission($device_id);
    $host_id = get_vm_parent_id($device);
    if (is_numeric($host_id)) {
        $device = array_merge($device, array('parent_id' => $host_id));
    }
    api_success(array($device), 'devices');
}

function list_devices()
{
    // This will return a list of devices
    $order = $_GET['order'];
    $type  = $_GET['type'];
    $query = mres($_GET['query']);
    $param = array();

    if (empty($order)) {
        $order = 'hostname';
    }

    if (stristr($order, ' desc') === false && stristr($order, ' asc') === false) {
        $order = 'd.`'.$order.'` ASC';
    }

    $select = " d.*, GROUP_CONCAT(dd.device_id) AS dependency_parent_id, GROUP_CONCAT(dd.hostname) AS dependency_parent_hostname, `lat`, `lng` ";
    $join = " LEFT JOIN `device_relationships` AS dr ON dr.`child_device_id` = d.`device_id` LEFT JOIN `devices` AS dd ON dr.`parent_device_id` = dd.`device_id` LEFT JOIN `locations` ON `locations`.`id` = `d`.`location_id`";

    if ($type == 'all' || empty($type)) {
        $sql = '1';
    } elseif ($type == 'active') {
        $sql = "`d`.`ignore`='0' AND `d`.`disabled`='0'";
    } elseif ($type == 'location') {
        $sql = "`locations`.`location` LIKE '%".$query."%'";
    } elseif ($type == 'ignored') {
        $sql = "`d`.`ignore`='1' AND `d`.`disabled`='0'";
    } elseif ($type == 'up') {
        $sql = "`d`.`status`='1' AND `d`.`ignore`='0' AND `d`.`disabled`='0'";
    } elseif ($type == 'down') {
        $sql = "`d`.`status`='0' AND `d`.`ignore`='0' AND `d`.`disabled`='0'";
    } elseif ($type == 'disabled') {
        $sql = "`d`.`disabled`='1'";
    } elseif ($type == 'os') {
        $sql = "`d`.`os`=?";
        $param[] = $query;
    } elseif ($type == 'mac') {
        $join .= " LEFT JOIN `ports` AS p ON d.`device_id` = p.`device_id` LEFT JOIN `ipv4_mac` AS m ON p.`port_id` = m.`port_id` ";
        $sql = "m.`mac_address`=?";
        $select .= ",p.* ";
        $param[] = $query;
    } elseif ($type == 'ipv4') {
        $join .= " LEFT JOIN `ports` AS p ON d.`device_id` = p.`device_id` LEFT JOIN `ipv4_addresses` AS a ON p.`port_id` = a.`port_id` ";
        $sql = "a.`ipv4_address`=?";
        $select .= ",p.* ";
        $param[] = $query;
    } elseif ($type == 'ipv6') {
        $join .= " LEFT JOIN `ports` AS p ON d.`device_id` = p.`device_id` LEFT JOIN `ipv6_addresses` AS a ON p.`port_id` = a.`port_id` ";
        $sql = "a.`ipv6_address`=? OR a.`ipv6_compressed`=?";
        $select .= ",p.* ";
        $param = array($query, $query);
    } else {
        $sql = '1';
    }


    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql .= " AND `d`.`device_id` IN (SELECT device_id FROM devices_perms WHERE user_id = ?)";
        $param[] = LegacyAuth::id();
    }
    $devices = array();
    $dev_query = "SELECT $select FROM `devices` AS d $join WHERE $sql GROUP BY d.`hostname` ORDER BY $order";
    foreach (dbFetchRows($dev_query, $param) as $device) {
        $host_id = get_vm_parent_id($device);
        $device['ip'] = inet6_ntop($device['ip']);
        if (is_numeric($host_id)) {
            $device['parent_id'] = $host_id;
        }
        $devices[] = $device;
    }

    api_success($devices, 'devices');
}


function add_device()
{
    check_is_admin();

    // This will add a device using the data passed encoded with json
    // FIXME: Execution flow through this function could be improved
    global $config;
    $data = json_decode(file_get_contents('php://input'), true);

    $additional = array();
    // keep scrutinizer from complaining about snmpver not being set for all execution paths
    $snmpver = 'v2c';
    if (empty($data)) {
        api_error(400, 'No information has been provided to add this new device');
    }
    if (empty($data['hostname'])) {
        api_error(400, 'Missing the device hostname');
    }

    $hostname     = $data['hostname'];
    $port         = $data['port'] ? mres($data['port']) : $config['snmp']['port'];
    $transport    = $data['transport'] ? mres($data['transport']) : 'udp';
    $poller_group = $data['poller_group'] ? mres($data['poller_group']) : 0;
    $force_add    = $data['force_add'] ? true : false;
    $snmp_disable = ($data['snmp_disable']);
    if ($snmp_disable) {
        $additional = array(
            'os'           => $data['os'] ? mres($data['os']) : 'ping',
            'hardware'     => $data['hardware'] ? mres($data['hardware']) : '',
            'snmp_disable' => 1,
        );
    } elseif ($data['version'] == 'v1' || $data['version'] == 'v2c') {
        if ($data['community']) {
            $config['snmp']['community'] = array($data['community']);
        }

        $snmpver = mres($data['version']);
    } elseif ($data['version'] == 'v3') {
        $v3 = array(
            'authlevel'  => mres($data['authlevel']),
            'authname'   => mres($data['authname']),
            'authpass'   => mres($data['authpass']),
            'authalgo'   => mres($data['authalgo']),
            'cryptopass' => mres($data['cryptopass']),
            'cryptoalgo' => mres($data['cryptoalgo']),
        );

        array_unshift($config['snmp']['v3'], $v3);
        $snmpver = 'v3';
    } else {
        api_error(400, 'You haven\'t specified an SNMP version to use');
    }
    try {
        $device_id = addHost($hostname, $snmpver, $port, $transport, $poller_group, $force_add, 'ifIndex', $additional);
    } catch (Exception $e) {
        api_error(500, $e->getMessage());
    }

    api_success_noresult(201, "Device $hostname ($device_id) has been added successfully");
}


function del_device()
{
    check_is_admin();

    // This will add a device using the data passed encoded with json
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    check_not_demo();
    if (empty($hostname)) {
        api_error(400, 'No hostname has been provided to delete');
    }

    // allow deleting by device_id or hostname
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $device    = null;
    if ($device_id) {
        // save the current details for returning to the client on successful delete
        $device = device_by_id_cache($device_id);
    }

    if (!$device) {
        api_error(404, "Device $hostname not found");
    }

    $response = delete_device($device_id);
    if (empty($response)) {
        // FIXME: Need to provide better diagnostics out of delete_device
        api_error(500, 'Device deletion failed');
    }

    // deletion succeeded - include old device details in response
    api_success(array($device), 'devices', $response);
}


function get_vlans()
{
    // This will list all vlans for a given device
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    if (empty($hostname)) {
        api_error(500, 'No hostname has been provided');
    }

    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $device    = null;
    if ($device_id) {
        // save the current details for returning to the client on successful delete
        $device = device_by_id_cache($device_id);
    }

    if (!$device) {
        api_error(404, "Device $hostname not found");
    }
    check_device_permission($device_id);

    $vlans       = dbFetchRows('SELECT vlan_vlan,vlan_domain,vlan_name,vlan_type,vlan_mtu FROM vlans WHERE `device_id` = ?', array($device_id));
    api_success($vlans, 'vlans');
}


function show_endpoints()
{
    global $config;
    $app    = \Slim\Slim::getInstance();
    $routes = $app->router()->getNamedRoutes();
    $output = array();
    foreach ($routes as $route) {
        $output[$route->getName()] = $config['base_url'].$route->getPattern();
    }

    $app->response->setStatus('200');
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}


function list_bgp()
{
    check_is_read();

    $app        = \Slim\Slim::getInstance();

    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'] ?: '';
    $asn        = $_GET['asn'] ?: '';
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        $sql        = ' AND `devices`.`device_id` = ?';
        $sql_params[] = $device_id;
    }
    if (!empty($asn)) {
        $sql = ' AND `devices`.`bgpLocalAs` = ?';
        $sql_params[] = $asn;
    }

    $bgp_sessions       = dbFetchRows("SELECT `bgpPeers`.* FROM `bgpPeers` LEFT JOIN `devices` ON `bgpPeers`.`device_id` = `devices`.`device_id` WHERE `bgpPeerState` IS NOT NULL AND `bgpPeerState` != '' $sql", $sql_params);
    $total_bgp_sessions = count($bgp_sessions);
    if (!is_numeric($total_bgp_sessions)) {
        api_error(500, 'Error retrieving bgpPeers');
    }

    api_success($bgp_sessions, 'bgp_sessions');
}


function get_bgp()
{
    check_is_read();

    $app        = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();

    $bgpPeerId        = $router['id'];
    if (!is_numeric($bgpPeerId)) {
        api_error(400, 'Invalid id has been provided');
    }

    $bgp_session       = dbFetchRows("SELECT * FROM `bgpPeers` WHERE `bgpPeerState` IS NOT NULL AND `bgpPeerState` != '' AND bgpPeer_id = ?", array($bgpPeerId));
    $bgp_session_count = count($bgp_session);
    if (!is_numeric($bgp_session_count)) {
        api_error(500, 'Error retrieving BGP peer');
    }
    if ($bgp_session_count == 0) {
        api_error(404, "BGP peer $bgpPeerId does not exist");
    }

    api_success($bgp_session, 'bgp_session');
}


function list_cbgp()
{
    $app        = \Slim\Slim::getInstance();
    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'] ?: '';
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        check_device_permission($device_id);
        $sql        = " AND `devices`.`device_id` = ?";
        $sql_params[] = $device_id;
    }
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql .= " AND `bgpPeers_cbgp`.`device_id` IN (SELECT device_id FROM devices_perms WHERE user_id = ?)";
        $sql_params[] = LegacyAuth::id();
    }

    $bgp_counters = array();
    foreach (dbFetchRows("SELECT `bgpPeers_cbgp`.* FROM `bgpPeers_cbgp` LEFT JOIN `devices` ON `bgpPeers_cbgp`.`device_id` = `devices`.`device_id` WHERE `bgpPeers_cbgp`.`device_id` IS NOT NULL $sql", $sql_params) as $bgp_counter) {
        $host_id = get_vm_parent_id($device);
        $device['ip'] = inet6_ntop($device['ip']);
        if (is_numeric($host_id)) {
            $device['parent_id'] = $host_id;
        }
        $bgp_counters[] = $bgp_counter;
    }
    $total_bgp_counters = count($bgp_counters);
    if ($total_bgp_counters == 0) {
        api_error(404, 'BGP counters does not exist');
    }

    api_success($bgp_counters, 'bgp_counters');
}


function list_ospf()
{
    check_is_read();

    $app        = \Slim\Slim::getInstance();
    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'];
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        $sql        = ' AND `device_id`=?';
        $sql_params = array($device_id);
    }

    $ospf_neighbours       = dbFetchRows("SELECT * FROM ospf_nbrs WHERE `ospfNbrState` IS NOT NULL AND `ospfNbrState` != '' $sql", $sql_params);
    $total_ospf_neighbours = count($ospf_neighbours);
    if (!is_numeric($total_ospf_neighbours)) {
        api_error(500, 'Error retrieving ospf_nbrs');
    }

    api_success($ospf_neighbours, 'ospf_neighbours');
}


function get_graph_by_portgroup()
{
    check_is_read();
    global $config;
    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $group  = $router['group'] ?: '';
    $id     = $router['id'] ?: '';
    $vars   = array();
    $vars['output'] = $_GET['output'] ?: 'display';
    if (!empty($_GET['from'])) {
        $vars['from'] = $_GET['from'];
    }

    if (!empty($_GET['to'])) {
        $vars['to'] = $_GET['to'];
    }

    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;
    $auth           = '1';
    $if_list        = '';
    $ports          = array();

    if (!empty($id)) {
        $if_list = $id;
    } else {
        $ports = get_ports_from_type(explode(',', $group));
    }
    if (empty($if_list)) {
        $seperator   = '';
        foreach ($ports as $port) {
            $if_list  .= $seperator.$port['port_id'];
            $seperator = ',';
        }
    }

    unset($seperator);
    $vars['type'] = 'multiport_bits_separate';
    $vars['id']   = $if_list;
    $app->response->headers->set('Content-Type', get_image_type());
    rrdtool_initialize(false);
    include 'includes/graphs/graph.inc.php';
    rrdtool_close();
    if ($vars['output'] === 'base64') {
        api_success(['image' => $base64_output, 'content-type' => get_image_type()], 'image');
    }
}


function get_components()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    // Do some filtering if the user requests.
    $options = array();
    // We need to specify the label as this is a LIKE query
    if (isset($_GET['label'])) {
        // set a label like filter
        $options['filter']['label'] = array('LIKE',$_GET['label']);
        unset($_GET['label']);
    }
    // Add the rest of the options with an equals query
    foreach ($_GET as $k => $v) {
        $options['filter'][$k] = array('=',$v);
    }

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    $COMPONENT = new LibreNMS\Component();
    $components = $COMPONENT->getComponents($device_id, $options);

    api_success($components[$device_id], 'components');
}


function add_components()
{
    check_is_admin();

    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $ctype = $router['type'];

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $COMPONENT = new LibreNMS\Component();
    $component = $COMPONENT->createComponent($device_id, $ctype);

    api_success($component, 'components');
}


function edit_components()
{
    check_is_admin();

    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $data = json_decode(file_get_contents('php://input'), true);

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $COMPONENT = new LibreNMS\Component();

    if (!$COMPONENT->setComponentPrefs($device_id, $data)) {
        api_error(500, 'Components could not be edited.');
    }

    api_success_noresult(200);
}


function delete_components()
{
    check_is_admin();

    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $cid = $router['component'];

    $COMPONENT = new LibreNMS\Component();
    if ($COMPONENT->deleteComponent($cid)) {
        api_success_noresult(200);
    } else {
        api_error(500, 'Components could not be deleted.');
    }
}


function get_graphs()
{
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    // FIXME: this has some overlap with html/pages/device/graphs.inc.php
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    $graphs    = array();
    $graphs[]  = array(
        'desc' => 'Poller Time',
        'name' => 'device_poller_perf',
    );
    $graphs[]  = array(
        'desc' => 'Ping Response',
        'name' => 'device_ping_perf',
    );
    foreach (dbFetchRows('SELECT * FROM device_graphs WHERE device_id = ? ORDER BY graph', array($device_id)) as $graph) {
        $desc     = $config['graph_types']['device'][$graph['graph']]['descr'];
        $graphs[] = array(
            'desc' => $desc,
            'name' => 'device_'.$graph['graph'],
        );
    }

    return api_success($graphs, 'graphs');
}

function list_available_health_graphs()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    if (isset($router['type'])) {
        list($dump, $type) = explode('_', $router['type']);
    }
    $sensor_id = $router['sensor_id'] ?: null;
    $graphs    = array();

    if (isset($type)) {
        if (isset($sensor_id)) {
              $graphs = dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_id` = ?', array($sensor_id));
        } else {
            foreach (dbFetchRows('SELECT `sensor_id`, `sensor_descr` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = ? AND `sensor_deleted` = 0', array($device_id, $type)) as $graph) {
                $graphs[] = array(
                    'sensor_id' => $graph['sensor_id'],
                    'desc'      => $graph['sensor_descr'],
                );
            }
        }
    } else {
        foreach (dbFetchRows('SELECT `sensor_class` FROM `sensors` WHERE `device_id` = ? AND `sensor_deleted` = 0 GROUP BY `sensor_class`', array($device_id)) as $graph) {
            $graphs[] = array(
                'desc' => ucfirst($graph['sensor_class']),
                'name' => 'device_'.$graph['sensor_class'],
            );
        }
        $device = \App\Models\Device::find($device_id);

        if ($device) {
            if ($device->processors()->count() > 0) {
                array_push($graphs, array(
                    'desc' => 'Processors',
                    'name' => 'device_processor'
                ));
            }

            if ($device->storage()->count() > 0) {
                array_push($graphs, array(
                    'desc' => 'Storage',
                    'name' => 'device_storage'
                ));
            }

            if ($device->mempools()->count() > 0) {
                array_push($graphs, array(
                    'desc' => 'Memory Pools',
                    'name' => 'device_mempool'
                ));
            }
        }
    }

    return api_success($graphs, 'graphs');
}

function list_available_wireless_graphs()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    if (isset($router['type'])) {
        list(, , $type) = explode('_', $router['type']);
    }
    $sensor_id = $router['sensor_id'] ?: null;
    $graphs    = array();

    if (isset($type)) {
        if (isset($sensor_id)) {
            $graphs = dbFetchRows('SELECT * FROM `wireless_sensors` WHERE `sensor_id` = ?', array($sensor_id));
        } else {
            foreach (dbFetchRows('SELECT `sensor_id`, `sensor_descr` FROM `wireless_sensors` WHERE `device_id` = ? AND `sensor_class` = ? AND `sensor_deleted` = 0', array($device_id, $type)) as $graph) {
                $graphs[] = array(
                    'sensor_id' => $graph['sensor_id'],
                    'desc'      => $graph['sensor_descr'],
                );
            }
        }
    } else {
        foreach (dbFetchRows('SELECT `sensor_class` FROM `wireless_sensors` WHERE `device_id` = ? AND `sensor_deleted` = 0 GROUP BY `sensor_class`', array($device_id)) as $graph) {
            $graphs[] = array(
                'desc' => ucfirst($graph['sensor_class']),
                'name' => 'device_wireless_'.$graph['sensor_class'],
            );
        }
    }

    return api_success($graphs, 'graphs');
}

function get_port_graphs()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
    } else {
        $columns = 'ifName';
    }
    validate_column_list($columns, 'ports');

    // use hostname as device_id if it's all digits
    $device_id   = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $sql = '';
    $params = array($device_id);
    if (!device_permitted($device_id)) {
        $sql = 'AND `port_id` IN (select `port_id` from `ports_perms` where `user_id` = ?)';
        array_push($params, LegacyAuth::id());
    }

    $ports       = dbFetchRows("SELECT $columns FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' $sql ORDER BY `ifIndex` ASC", $params);
    api_success($ports, 'ports');
}

function get_ip_addresses()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $ipv4 = array();
    $ipv6 = array();
    if (isset($router['hostname'])) {
        $hostname = $router['hostname'];
        // use hostname as device_id if it's all digits
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        check_device_permission($device_id);
        $ipv4   = dbFetchRows("SELECT `ipv4_addresses`.* FROM `ipv4_addresses` JOIN `ports` ON `ports`.`port_id`=`ipv4_addresses`.`port_id` WHERE `ports`.`device_id` = ? AND `deleted` = 0", array($device_id));
        $ipv6   = dbFetchRows("SELECT `ipv6_addresses`.* FROM `ipv6_addresses` JOIN `ports` ON `ports`.`port_id`=`ipv6_addresses`.`port_id` WHERE `ports`.`device_id` = ? AND `deleted` = 0", array($device_id));
        $ip_addresses_count = count(array_merge($ipv4, $ipv6));
        if ($ip_addresses_count == 0) {
            api_error(404, "Device $device_id does not have any IP addresses");
        }
    } elseif (isset($router['portid'])) {
        $port_id = urldecode($router['portid']);
        check_port_permission($port_id, null);
        $ipv4   = dbFetchRows("SELECT * FROM `ipv4_addresses` WHERE `port_id` = ?", array($port_id));
        $ipv6   = dbFetchRows("SELECT * FROM `ipv6_addresses` WHERE `port_id` = ?", array($port_id));
        $ip_addresses_count = count(array_merge($ipv4, $ipv6));
        if ($ip_addresses_count == 0) {
            api_error(404, "Port $port_id does not have any IP addresses");
        }
    } elseif (isset($router['id'])) {
        check_is_read();
        $network_id = $router['id'];
        $ipv4   = dbFetchRows("SELECT * FROM `ipv4_addresses` WHERE `ipv4_network_id` = ?", array($network_id));
        $ipv6   = dbFetchRows("SELECT * FROM `ipv6_addresses` WHERE `ipv6_network_id` = ?", array($network_id));
        $ip_addresses_count = count(array_merge($ipv4, $ipv6));
        if ($ip_addresses_count == 0) {
            api_error(404, "IP network $network_id does not exist or is empty");
        }
    }

    api_success(array_merge($ipv4, $ipv6), 'addresses');
}

function get_port_info()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $port_id  = urldecode($router['portid']);
    check_port_permission($port_id, null);

    // use hostname as device_id if it's all digits
    $port   = dbFetchRows("SELECT * FROM `ports` WHERE `port_id` = ? AND `deleted` = 0", array($port_id));
    api_success($port, 'port');
}

function get_all_ports()
{
    $app = \Slim\Slim::getInstance();
    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
    } else {
        $columns = 'port_id, ifName';
    }
    validate_column_list($columns, 'ports');
    $params = array();
    $sql = '';
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql = ' AND (device_id IN (SELECT device_id FROM devices_perms WHERE user_id = ?) OR port_id IN (SELECT port_id FROM ports_perms WHERE user_id = ?))';
        array_push($params, LegacyAuth::id());
        array_push($params, LegacyAuth::id());
    }
    $ports = dbFetchRows("SELECT $columns FROM `ports` WHERE `deleted` = 0 $sql", $params);

    api_success($ports, 'ports');
}

function get_port_stack()
{
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id      = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);

    if (isset($_GET['valid_mappings'])) {
        $mappings       = dbFetchRows("SELECT * FROM `ports_stack` WHERE (`device_id` = ? AND `ifStackStatus` = 'active' AND (`port_id_high` != '0' AND `port_id_low` != '0')) ORDER BY `port_id_high` ASC", array($device_id));
    } else {
        $mappings       = dbFetchRows("SELECT * FROM `ports_stack` WHERE `device_id` = ? AND `ifStackStatus` = 'active' ORDER BY `port_id_high` ASC", array($device_id));
    }

    api_success($mappings, 'mappings');
}

function list_alert_rules()
{
    check_is_read();
    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $sql    = '';
    $param  = array();
    if (isset($router['id']) && $router['id'] > 0) {
        $rule_id = mres($router['id']);
        $sql     = 'WHERE id=?';
        $param   = array($rule_id);
    }

    $rules       = dbFetchRows("SELECT * FROM `alert_rules` $sql", $param);
    api_success($rules, 'rules');
}


function list_alerts()
{
    check_is_read();
    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();

    $sql = "SELECT `D`.`hostname`, `A`.*, `R`.`severity` FROM `alerts` AS `A`, `devices` AS `D`, `alert_rules` AS `R` WHERE `D`.`device_id` = `A`.`device_id` AND `A`.`rule_id` = `R`.`id` AND `A`.`state` IN ";
    if (isset($_GET['state'])) {
        $param = explode(',', $_GET['state']);
    } else {
        $param = [1];
    }
    $sql .= dbGenPlaceholders(count($param));

    if (isset($router['id']) && $router['id'] > 0) {
        $param[] = $router['id'];
        $sql .= 'AND `A`.id=?';
    }

    $severity = $_GET['severity'];
    if (isset($severity)) {
        if (in_array($severity, ['ok', 'warning', 'critical'])) {
            $param[] = $severity;
            $sql .= ' AND `R`.severity=?';
        }
    }
    
    $order = $_GET['order'] ?: "timestamp desc";
    $sql .= ' ORDER BY A.'.$order;

    $alerts = dbFetchRows($sql, $param);
    api_success($alerts, 'alerts');
}


function add_edit_rule()
{
    check_is_admin();
    $app  = \Slim\Slim::getInstance();
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error()) {
        api_error(500, "We couldn't parse the provided json");
    }

    $rule_id = mres($data['rule_id']);
    $tmp_devices = (array)mres($data['devices']);
    $groups  = (array)$data['groups'];
    if (empty($tmp_devices) && !isset($rule_id)) {
        api_error(400, 'Missing the devices or global device (-1)');
    }

    $devices = [];
    foreach ($tmp_devices as $device) {
        if ($device == "-1") {
            continue;
        }
        $devices[] = ctype_digit($device) ? $device : getidbyname($device);
    }

    $builder = $data['builder'] ?: $data['rule'];
    if (empty($builder)) {
        api_error(400, 'Missing the alert builder rule');
    }

    $name = mres($data['name']);
    if (empty($name)) {
        api_error(400, 'Missing the alert rule name');
    }

    $severity = mres($data['severity']);
    $sevs     = array(
        'ok',
        'warning',
        'critical',
    );
    if (!in_array($severity, $sevs)) {
        api_error(400, 'Missing the severity');
    }

    $disabled = mres($data['disabled']);
    if ($disabled != '0' && $disabled != '1') {
        $disabled = 0;
    }

    $count     = mres($data['count']);
    $mute      = mres($data['mute']);
    $delay     = mres($data['delay']);
    $override_query = $data['override_query'];
    $adv_query = $data['adv_query'];
    $delay_sec = convert_delay($delay);
    if ($mute == 1) {
        $mute = true;
    } else {
        $mute = false;
    }

    $extra      = [
        'mute'  => $mute,
        'count' => $count,
        'delay' => $delay_sec,
        'options' =>
            [
                'override_query' => $override_query
            ],
    ];
    $extra_json = json_encode($extra);

    if ($override_query === 'on') {
        $query = $adv_query;
    } else {
        $query = QueryBuilderParser::fromJson($builder)->toSql();
        if (empty($query)) {
            api_error(500, "We couldn't parse your rule");
        }
    }

    if (!isset($rule_id)) {
        if (dbFetchCell('SELECT `name` FROM `alert_rules` WHERE `name`=?', array($name)) == $name) {
            api_error(500, 'Addition failed : Name has already been used');
        }
    } else {
        if (dbFetchCell("SELECT name FROM alert_rules WHERE name=? AND id !=? ", array($name, $rule_id)) == $name) {
            api_error(500, 'Update failed : Invalid rule id');
        }
    }

    if (is_numeric($rule_id)) {
        if (!(dbUpdate(array('name' => $name, 'builder' => $builder, 'query' => $query, 'severity' => $severity, 'disabled' => $disabled, 'extra' => $extra_json), 'alert_rules', 'id=?', array($rule_id)) >= 0)) {
            api_error(500, 'Failed to update existing alert rule');
        }
    } elseif (!$rule_id = dbInsert(array('name' => $name, 'builder' => $builder, 'query' => $query, 'severity' => $severity, 'disabled' => $disabled, 'extra' => $extra_json), 'alert_rules')) {
        api_error(500, 'Failed to create new alert rule');
    }

    dbSyncRelationship('alert_device_map', 'rule_id', $rule_id, 'device_id', $devices);
    dbSyncRelationship('alert_group_map', 'rule_id', $rule_id, 'group_id', $groups);
    api_success_noresult(200);
}


function delete_rule()
{
    check_is_admin();
    $app     = \Slim\Slim::getInstance();
    $router  = $app->router()->getCurrentRoute()->getParams();
    $rule_id = mres($router['id']);
    if (is_numeric($rule_id)) {
        if (dbDelete('alert_rules', '`id` =  ? LIMIT 1', array($rule_id))) {
            api_success_noresult(200, 'Alert rule has been removed');
        } else {
            api_success_noresult(200, 'No alert rule by that ID');
        }
    } else {
        api_error(400, 'Invalid rule id has been provided');
    }
}


function ack_alert()
{
    check_is_admin();

    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $alert_id = mres($router['id']);
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_numeric($alert_id)) {
        api_error(400, 'Invalid alert has been provided');
    }

    $alert = dbFetchRow('SELECT note, info FROM alerts WHERE id=?', [$alert_id]);
    $note  = $alert['note'];
    $info  = json_decode($alert['info'], true);
    if (!empty($note)) {
        $note .= PHP_EOL;
    }
    $note .= date(Config::get('dateformat.long')) . " - Ack (" . Auth::user()->username . ") {$data['note']}";
    $info['until_clear'] = $data['until_clear'];
    $info = json_encode($info);

    if (dbUpdate(['state' => 2, 'note' => $note, 'info' => $info], 'alerts', '`id` = ? LIMIT 1', [$alert_id])) {
        api_success_noresult(200, 'Alert has been acknowledged');
    } else {
        api_success_noresult(200, 'No Alert by that ID');
    }
}

function unmute_alert()
{
    check_is_admin();

    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $alert_id = mres($router['id']);
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_numeric($alert_id)) {
        api_error(400, 'Invalid alert has been provided');
    }

    $alert = dbFetchRow('SELECT note, info FROM alerts WHERE id=?', [$alert_id]);
    $note  = $alert['note'];
    $info  = json_decode($alert['info'], true);
    if (!empty($note)) {
        $note .= PHP_EOL;
    }
    $note .= date(Config::get('dateformat.long')) . " - Ack (" . Auth::user()->username . ") {$data['note']}";

    if (dbUpdate(['state' => 1, 'note' => $note], 'alerts', '`id` = ? LIMIT 1', [$alert_id])) {
        api_success_noresult(200, 'Alert has been unmuted');
    } else {
        api_success_noresult(200, 'No alert by that ID');
    }
}


function get_inventory()
{
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();

    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    $sql       = '';
    $params    = array();
    if (isset($_GET['entPhysicalClass']) && !empty($_GET['entPhysicalClass'])) {
        $sql     .= ' AND entPhysicalClass=?';
        $params[] = mres($_GET['entPhysicalClass']);
    }

    if (isset($_GET['entPhysicalContainedIn']) && !empty($_GET['entPhysicalContainedIn'])) {
        $sql     .= ' AND entPhysicalContainedIn=?';
        $params[] = mres($_GET['entPhysicalContainedIn']);
    } else {
        $sql .= ' AND entPhysicalContainedIn="0"';
    }

    if (!is_numeric($device_id)) {
        api_error(400, 'Invalid device provided');
    }
    $sql .= ' AND `device_id`=?';
    $params[] = $device_id;
    $inventory = dbFetchRows("SELECT * FROM `entPhysical` WHERE 1 $sql", $params);

    api_success($inventory, 'inventory');
}


function list_oxidized()
{
    check_is_read();
    global $config;
    $app = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();

    $hostname = $router['hostname'];
    $devices = array();
    $device_types = "'".implode("','", $config['oxidized']['ignore_types'])."'";
    $device_os    = "'".implode("','", $config['oxidized']['ignore_os'])."'";

    $sql = '';
    $params = array();
    if ($hostname) {
        $sql = " AND hostname = ?";
        $params = array($hostname);
    }

    foreach (dbFetchRows("SELECT hostname,sysname,sysDescr,hardware,os,locations.location,ip AS ip FROM `devices` LEFT JOIN locations ON devices.location_id = locations.id LEFT JOIN devices_attribs AS `DA` ON devices.device_id = DA.device_id AND `DA`.attrib_type='override_Oxidized_disable' WHERE `disabled`='0' AND `ignore` = 0 AND (DA.attrib_value = 'false' OR DA.attrib_value IS NULL) AND (`type` NOT IN ($device_types) AND `os` NOT IN ($device_os)) $sql", $params) as $device) {
        // Convert from packed value to human value
        $device['ip'] = inet6_ntop($device['ip']);

        // Pre-populate the group with the default
        if ($config['oxidized']['group_support'] === true && !empty($config['oxidized']['default_group'])) {
            $device['group'] = $config['oxidized']['default_group'];
        }
        foreach ($config['oxidized']['maps'] as $maps_column => $maps) {
            // Based on Oxidized group support we can apply groups by setting group_support to true
            if ($maps_column == "group" && (!isset($config['oxidized']['group_support']) or $config['oxidized']['group_support'] !== true)) {
                continue;
            }

            foreach ($maps as $field_type => $fields) {
                foreach ($fields as $field) {
                    if (isset($field['regex']) && preg_match($field['regex'].'i', $device[$field_type])) {
                        $device[$maps_column] = $field[$maps_column];
                        break;
                    } elseif (isset($field['match']) && $field['match'] == $device[$field_type]) {
                        $device[$maps_column] = $field[$maps_column];
                        break;
                    }
                }
            }
        }

        // We remap certain device OS' that have different names with Oxidized models
        $models = [
            'arista_eos' => 'eos',
            'vyos'       => 'vyatta',
        ];

        $device['os'] = str_replace(array_keys($models), array_values($models), $device['os']);

        unset($device['location']);
        unset($device['sysname']);
        unset($device['sysDescr']);
        unset($device['hardware']);
        $devices[] = $device;
    }

    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($devices);
}

function list_bills()
{
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();

    $bills = array();
    $bill_id = mres($router['bill_id']);
    $bill_ref = mres($_GET['ref']);
    $bill_custid = mres($_GET['custid']);
    $period = $_GET['period'];
    $param = array();

    if (!empty($bill_custid)) {
        $sql    .= '`bill_custid` = ?';
        $param[] = $bill_custid;
    } elseif (!empty($bill_ref)) {
        $sql    .= '`bill_ref` = ?';
        $param[] = $bill_ref;
    } elseif (is_numeric($bill_id)) {
        $sql    .= '`bill_id` = ?';
        $param[] = $bill_id;
    } else {
        $sql = '1';
    }
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql    .= ' AND `bill_id` IN (SELECT `bill_id` FROM `bill_perms` WHERE `user_id` = ?)';
        $param[] = LegacyAuth::id();
    }

    if ($period === 'previous') {
        $select = "SELECT bills.bill_name, bills.bill_notes, bill_history.*, bill_history.traf_total as total_data, bill_history.traf_in as total_data_in, bill_history.traf_out as total_data_out ";
        $query = 'FROM `bills`
            INNER JOIN (SELECT bill_id, MAX(bill_hist_id) AS bill_hist_id FROM bill_history WHERE bill_dateto < NOW() AND bill_dateto > subdate(NOW(), 40) GROUP BY bill_id) qLastBills ON bills.bill_id = qLastBills.bill_id
            INNER JOIN bill_history ON qLastBills.bill_hist_id = bill_history.bill_hist_id
    ';
    } else {
        $select = "SELECT bills.*,
            IF(bills.bill_type = 'CDR', bill_cdr, bill_quota) AS bill_allowed
        ";
        $query = "FROM `bills`\n";
    }

    foreach (dbFetchRows("$select $query WHERE $sql ORDER BY `bill_name`", $param) as $bill) {
        $rate_data    = $bill;
        $allowed = '';
        $used = '';
        $percent = '';
        $overuse = '';

        if ($bill['bill_type'] == "cdr") {
            $allowed = format_si($bill['bill_cdr'])."bps";
            $used    = format_si($rate_data['rate_95th'])."bps";
            $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100, 2);
            $overuse = $rate_data['rate_95th'] - $bill['bill_cdr'];
            $overuse = (($overuse <= 0) ? "-" : format_si($overuse));
        } elseif ($bill['bill_type'] == "quota") {
            $allowed = format_bytes_billing($bill['bill_quota']);
            $used    = format_bytes_billing($rate_data['total_data']);
            $percent = round(($rate_data['total_data'] / ($bill['bill_quota'])) * 100, 2);
            $overuse = $rate_data['total_data'] - $bill['bill_quota'];
            $overuse = (($overuse <= 0) ? "-" : format_bytes_billing($overuse));
        }
        $bill['allowed'] = $allowed;
        $bill['used'] = $used;
        $bill['percent'] = $percent;
        $bill['overuse'] = $overuse;

        $bill['ports'] = dbFetchRows("SELECT `D`.`device_id`,`P`.`port_id`,`P`.`ifName` FROM `bill_ports` AS `B`, `ports` AS `P`, `devices` AS `D` WHERE `B`.`bill_id` = ? AND `P`.`port_id` = `B`.`port_id` AND `D`.`device_id` = `P`.`device_id`", array($bill["bill_id"]));

        $bills[] = $bill;
    }
    api_success($bills, 'bills');
}

function get_bill_graph()
{
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = mres($router['bill_id']);
    $graph_type = $router['graph_type'];

    if (!LegacyAuth::user()->hasGlobalRead()) {
        check_bill_permission($bill_id);
    }

    if ($graph_type == 'monthly') {
        $graph_type = 'historicmonthly';
    }

    $vars = array();
    $vars['type'] = "bill_$graph_type";
    $vars['id'] = $bill_id;
    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;

    $app->response->headers->set('Content-Type', 'image/png');
    include 'includes/graphs/graph.inc.php';
}

function get_bill_graphdata()
{
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = mres($router['bill_id']);
    $graph_type = $router['graph_type'];

    if (!LegacyAuth::user()->hasGlobalRead()) {
        check_bill_permission($bill_id);
    }

    if ($graph_type == 'bits') {
        $from = (isset($_GET['from']) ? $_GET['from'] : time() - 60 * 60 * 24);
        $to   = (isset($_GET['to']) ? $_GET['to'] : time());
        $reducefactor = $_GET['reducefactor'];

        $graph_data = getBillingBitsGraphData($bill_id, $from, $to, $reducefactor);
    } elseif ($graph_type == 'monthly') {
        $graph_data = getHistoricTransferGraphData($bill_id);
    }

    if (!isset($graph_data)) {
        api_error(400, "Unsupported graph type $graph_type");
    } else {
        api_success($graph_data, 'graph_data');
    }
}

function get_bill_history()
{
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = mres($router['bill_id']);

    if (!LegacyAuth::user()->hasGlobalRead()) {
        check_bill_permission($bill_id);
    }

    $result = [];
    foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` DESC LIMIT 24', array($bill_id)) as $history) {
        $result[] = $history;
    }

    api_success($result, 'bill_history');
}

function get_bill_history_graph()
{
    global $config;

    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = mres($router['bill_id']);
    $bill_hist_id = mres($router['bill_hist_id']);
    $graph_type = $router['graph_type'];

    if (!LegacyAuth::user()->hasGlobalRead()) {
        check_bill_permission($bill_id);
    }

    $vars = array();

    switch ($graph_type) {
        case 'bits':
            $graph_type = 'historicbits';
            $vars['reducefactor'] = $_GET['reducefactor'];
            break;

        case 'day':
        case 'hour':
            $vars['imgtype'] = $graph_type;
            $graph_type = 'historictransfer';
            break;

        default:
            api_error(400, "Unknown Graph Type $graph_type");
            break;
    }

    global $dur;        // Needed for callback within graph code
    $vars['type'] = "bill_$graph_type";
    $vars['id'] = $bill_id;
    $vars['bill_hist_id'] = $bill_hist_id;
    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;

    $app->response->headers->set('Content-Type', 'image/png');
    include 'includes/graphs/graph.inc.php';
}

function get_bill_history_graphdata()
{
    global $config;

    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = mres($router['bill_id']);
    $bill_hist_id = mres($router['bill_hist_id']);
    $graph_type = $router['graph_type'];

    if (!LegacyAuth::user()->hasGlobalRead()) {
        check_bill_permission($bill_id);
    }

    switch ($graph_type) {
        case 'bits':
            $reducefactor = $_GET['reducefactor'];

            $graph_data = getBillingHistoryBitsGraphData($bill_id, $bill_hist_id, $reducefactor);
            break;
        case 'day':
        case 'hour':
            $graph_data = getBillingBandwidthGraphData($bill_id, $bill_hist_id, null, null, $graph_type);
            break;
    }

    if (!isset($graph_data)) {
        api_error(400, "Unsupported graph type $graph_type");
    } else {
        api_success($graph_data, 'graph_data');
    }
}

function delete_bill()
{
    check_is_admin();
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $bill_id = (int)$router['id'];

    if ($bill_id < 1) {
        api_error(400, 'Could not remove bill with id '.$bill_id.'. Invalid id');
    }

    $res = dbDelete('bills', '`bill_id` =  ? LIMIT 1', [ $bill_id ]);
    if ($res == 1) {
        dbDelete('bill_ports', '`bill_id` =  ? ', [ $bill_id ]);
        dbDelete('bill_data', '`bill_id` =  ? ', [ $bill_id ]);
        dbDelete('bill_history', '`bill_id` =  ? ', [ $bill_id ]);
        dbDelete('bill_history', '`bill_id` =  ? ', [ $bill_id ]);
        dbDelete('bill_perms', '`bill_id` =  ? ', [ $bill_id ]);
        api_success_noresult(200, 'Bill has been removed');
    }
    api_error(400, 'Could not remove bill with id '.$bill_id);
}

function check_bill_key_value($bill_key, $bill_value)
{
    $return_value = null;
    $bill_types = ['quota', 'cdr'];
    switch ($bill_key) {
        case "bill_type":
            if (in_array($bill_value, $bill_types)) {
                $return_value = mres($bill_value);
            } else {
                api_error(400, "Invalid value for $bill_key: $bill_value. Allowed: quota,cdr");
            }
            break;
        case "bill_cdr":
            if (is_numeric($bill_value)) {
                $return_value = mres($bill_value);
            } else {
                api_error(400, "Invalid value for $bill_key. Must be numeric.");
            }
            break;
        case "bill_day":
            if ($bill_value > 0 && $bill_value <= 31) {
                $return_value = mres($bill_value);
            } else {
                api_error(400, "Invalid value for $bill_key. range: 1-31");
            }
            break;
        case "bill_quota":
            if (is_numeric($bill_value)) {
                $return_value = mres($bill_value);
            } else {
                api_error(400, "Invalid value for $bill_key. Must be numeric");
            }
            break;
        default:
            $return_value = mres($bill_value);
            break;
    }

    return $return_value;
}

function create_edit_bill()
{
    check_is_admin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        api_error(500, 'Invalid JSON data');
    }
    //check ports
    $ports_add = null;
    if (array_key_exists('ports', $data)) {
        $ports_add = [];
        $ports = $data['ports'];
        foreach ($ports as $port_id) {
            $result = dbFetchRows('SELECT port_id FROM `ports` WHERE `port_id` = ?  LIMIT 1', [ $port_id ]);
            $result = $result[0];
            if (!is_array($result) || !array_key_exists('port_id', $result)) {
                api_error(500, 'Port ' . $port_id . ' does not exists');
            }
            $ports_add[] = $port_id;
        }
    }

    $bill = [];
    //find existing bill for update
    $bill_id = (int)$data['bill_id'];
    $bills = dbFetchRows("SELECT * FROM `bills` WHERE `bill_id` = $bill_id LIMIT 1");

    // update existing bill
    if (is_array($bills) && count($bills) == 1) {
        $bill = $bills[0];

        foreach ($data as $bill_key => $bill_value) {
                $bill[$bill_key] = check_bill_key_value($bill_key, $bill_value);
        }
        $update_data = [
            'bill_name' => $bill['bill_name'],
            'bill_type' => $bill['bill_type'],
            'bill_cdr' => $bill['bill_cdr'],
            'bill_day' => $bill['bill_day'],
            'bill_quota' => $bill['bill_quota'],
            'bill_custid' => $bill['bill_custid'],
            'bill_ref' => $bill['bill_ref'],
            'bill_notes' => $bill['bill_notes']
        ];
        $update = dbUpdate($update_data, 'bills', 'bill_id=?', array($bill_id));
        if ($update === false || $update < 0) {
            api_error(500, 'Failed to update existing bill');
        }
    } else {
        // create new bill
        if (array_key_exists('bill_id', $data)) {
            api_error(500, 'Argument bill_id is not allowed on bill create (auto assigned)');
        }

        $bill_keys = [
            'bill_name',
            'bill_type',
            'bill_cdr',
            'bill_day',
            'bill_quota',
            'bill_custid',
            'bill_ref',
            'bill_notes'
        ];

        if ($data['bill_type'] == 'quota') {
            $data['bill_cdr'] = 0;
        }
        if ($data['bill_type'] == 'cdr') {
            $data['bill_quota'] = 0;
        }

        $missing_keys = '';
        $missing = array_diff_key(array_flip($bill_keys), $data);
        if (count($missing) > 0) {
            foreach ($missing as $missing_key => $dummy) {
                $missing_keys .= " $missing_key";
            }
            api_error(500, 'Missing parameters: ' . $missing_keys);
        }

        foreach ($bill_keys as $bill_key) {
            $bill[$bill_key] = check_bill_key_value($bill_key, $data[$bill_key]);
        }

        $bill_id = dbInsert(
            [
            'bill_name' => $bill['bill_name'],
            'bill_type' => $bill['bill_type'],
            'bill_cdr' => $bill['bill_cdr'],
            'bill_day' => $bill['bill_day'],
            'bill_quota' => $bill['bill_quota'],
            'bill_custid' => $bill['bill_custid'],
            'bill_ref' => $bill['bill_ref'],
            'bill_notes' => $bill['bill_notes']
             ],
            'bills'
        );

        if ($bill_id === null) {
            api_error(500, 'Failed to create new bill');
        }
    }

    // set previously checked ports
    if (is_array($ports_add)) {
        dbDelete('bill_ports', "`bill_id` =  $bill_id");
        if (count($ports_add) > 0) {
            foreach ($ports_add as $port_id) {
                dbInsert([ 'bill_id' => $bill_id, 'port_id' => $port_id, 'bill_port_autoadded' => 0 ], 'bill_ports');
            }
        }
    }

    api_success($bill_id, 'bill_id');
}

function update_device()
{
    check_is_admin();
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $data = json_decode(file_get_contents('php://input'), true);
    $bad_fields = array('device_id','hostname');
    if (empty($data['field'])) {
        api_error(400, 'Device field to patch has not been supplied');
    } elseif (in_array($data['field'], $bad_fields)) {
        api_error(500, 'Device field is not allowed to be updated');
    }

    if (is_array($data['field']) && is_array($data['data'])) {
        foreach ($data['field'] as $tmp_field) {
            if (in_array($tmp_field, $bad_fields)) {
                api_error(500, 'Device field is not allowed to be updated');
            }
        }
        if (count($data['field']) == count($data['data'])) {
            for ($x=0; $x<count($data['field']); $x++) {
                $update[mres($data['field'][$x])] = mres($data['data'][$x]);
            }
            if (dbUpdate($update, 'devices', '`device_id`=?', array($device_id)) >= 0) {
                api_success_noresult(200, 'Device fields have been updated');
            } else {
                api_error(500, 'Device fields failed to be updated');
            }
        } else {
            api_error(500, 'Device fields failed to be updated as the number of fields ('.count($data['field']).') does not match the supplied data ('.count($data['data']).')');
        }
    } elseif (dbUpdate(array(mres($data['field']) => mres($data['data'])), 'devices', '`device_id`=?', array($device_id)) >= 0) {
        api_success_noresult(200, 'Device ' . mres($data['field']) . ' field has been updated');
    } else {
        api_error(500, 'Device ' . mres($data['field']) . ' field failed to be updated');
    }
}

function rename_device()
{
    check_is_admin();
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $new_hostname = $router['new_hostname'];
    $new_device = getidbyname($new_hostname);

    if (empty($new_hostname)) {
        api_error(500, 'Missing new hostname');
    } elseif ($new_device) {
        api_error(500, 'Device failed to rename, new hostname already exists');
    } else {
        if (renamehost($device_id, $new_hostname, 'api') == '') {
            api_success_noresult(200, 'Device has been renamed');
        } else {
            api_error(500, 'Device failed to be renamed');
        }
    }
}

function get_device_groups()
{
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $status   = 'error';
    $code     = 404;
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        $groups = GetGroupsFromDevice($device_id, 1);
    } else {
        $groups = GetDeviceGroups();
    }
    if (empty($groups)) {
        api_error(404, 'No device groups found');
    }

    api_success($groups, 'groups', 'Found ' . count($groups) . ' device groups');
}

function get_devices_by_group()
{
    check_is_read();
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $name     = urldecode($router['name']);
    $devices  = array();
    $full     = $_GET['full'];
    if (empty($name)) {
        api_error(400, 'No device group name provided');
    }
    $group_id = dbFetchCell("SELECT `id` FROM `device_groups` WHERE `name`=?", array($name));
    $devices = GetDevicesFromGroup($group_id, true, $full);
    if (empty($devices)) {
        api_error(404, 'No devices found in group ' . $name);
    }

    api_success($devices, 'devices');
}


function list_vrf()
{
    $app        = \Slim\Slim::getInstance();
    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'];
    $vrfname    = $_GET['vrfname'];
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        check_device_permission($device_id);
        $sql        = " AND `devices`.`device_id`=?";
        $sql_params = array($device_id);
    }
    if (!empty($vrfname)) {
        $sql        = "  AND `vrfs`.`vrf_name`=?";
        $sql_params = array($vrfname);
    }
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql .= " AND `vrfs`.`device_id` IN (SELECT device_id FROM devices_perms WHERE user_id = ?)";
        $sql_params[] = LegacyAuth::id();
    }

    $vrfs       = array();
    foreach (dbFetchRows("SELECT `vrfs`.* FROM `vrfs` LEFT JOIN `devices` ON `vrfs`.`device_id` = `devices`.`device_id` WHERE `vrfs`.`vrf_name` IS NOT NULL $sql", $sql_params) as $vrf) {
        $host_id = get_vm_parent_id($device);
        $device['ip'] = inet6_ntop($device['ip']);
        if (is_numeric($host_id)) {
            $device['parent_id'] = $host_id;
        }
        $vrfs[] = $vrf;
    }
    $total_vrfs = count($vrfs);
    if ($total_vrfs == 0) {
        api_error(404, 'VRFs do not exist');
    }

    api_success($vrfs, 'vrfs');
}


function get_vrf()
{
    check_is_read();

    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $vrfId  = $router['id'];
    if (!is_numeric($vrfId)) {
        api_error(400, 'Invalid id has been provided');
    }

    $vrf       = dbFetchRows("SELECT * FROM `vrfs` WHERE `vrf_id` IS NOT NULL AND `vrf_id` = ?", array($vrfId));
    $vrf_count = count($vrf);
    if ($vrf_count == 0) {
        api_error(404, "VRF $vrfId does not exist");
    }

    api_success($vrf, 'vrf');
}


function list_ipsec()
{
    check_is_read();
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (!is_numeric($device_id)) {
        api_error(400, "No valid hostname or device ID provided");
    }

    $ipsec  = dbFetchRows("SELECT `D`.`hostname`, `I`.* FROM `ipsec_tunnels` AS `I`, `devices` AS `D` WHERE `I`.`device_id`=? AND `D`.`device_id` = `I`.`device_id`", array($device_id));
    api_success($ipsec, 'ipsec');
}


function list_vlans()
{
    $app      = \Slim\Slim::getInstance();
    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'] ?: '';
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        check_device_permission($device_id);
        $sql        = " AND `devices`.`device_id` = ?";
        $sql_params[] = $device_id;
    }
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql .= " AND `vlans`.`device_id` IN (SELECT device_id FROM devices_perms WHERE user_id = ?)";
        $sql_params[] = LegacyAuth::id();
    }

    $vlans       = array();
    foreach (dbFetchRows("SELECT `vlans`.* FROM `vlans` LEFT JOIN `devices` ON `vlans`.`device_id` = `devices`.`device_id` WHERE `vlans`.`vlan_vlan` IS NOT NULL $sql", $sql_params) as $vlan) {
        $host_id = get_vm_parent_id($device);
        $device['ip'] = inet6_ntop($device['ip']);
        if (is_numeric($host_id)) {
            $device['parent_id'] = $host_id;
        }
        $vlans[] = $vlan;
    }
    $vlans_count = count($vlans);
    if ($vlans_count == 0) {
        api_error(404, 'VLANs do not exist');
    }

    api_success($vlans, 'vlans');
}


function list_links()
{
    $app        = \Slim\Slim::getInstance();
    $router     = $app->router()->getCurrentRoute()->getParams();
    $sql        = '';
    $sql_params = array();
    $hostname   = $router['hostname'];
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        check_device_permission($device_id);
        $sql        = " AND `links`.`local_device_id`=?";
        $sql_params = array($device_id);
    }
    if (!LegacyAuth::user()->hasGlobalRead()) {
        $sql .= " AND `links`.`local_device_id` IN (SELECT device_id FROM devices_perms WHERE user_id = ?)";
        $sql_params[] = LegacyAuth::id();
    }

    $links       = array();
    foreach (dbFetchRows("SELECT `links`.* FROM `links` LEFT JOIN `devices` ON `links`.`local_device_id` = `devices`.`device_id` WHERE `links`.`id` IS NOT NULL $sql", $sql_params) as $link) {
        $host_id = get_vm_parent_id($device);
        $device['ip'] = inet6_ntop($device['ip']);
        if (is_numeric($host_id)) {
            $device['parent_id'] = $host_id;
        }
        $links[] = $link;
    }
    $total_links = count($links);
    if ($total_links == 0) {
        api_error(404, 'Links do not exist');
    }

    api_success($links, 'links');
}


function get_link()
{
    check_is_read();

    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $linkId  = $router['id'];
    if (!is_numeric($linkId)) {
        api_error(400, 'Invalid id has been provided');
    }

    $link       = dbFetchRows("SELECT * FROM `links` WHERE `id` IS NOT NULL AND `id` = ?", array($linkId));
    $link_count = count($link);
    if ($link_count == 0) {
        api_error(404, "Link $linkId does not exist");
    }

    api_success($link, 'link');
}


function list_ip_addresses()
{
    check_is_read();

    $app            = \Slim\Slim::getInstance();
    $router         = $app->router()->getCurrentRoute()->getParams();
    $ipv4_addresses = array();
    $ipv6_addresses = array();

    $ipv4_addresses   = dbFetchRows("SELECT * FROM `ipv4_addresses`");
    $ipv6_addresses   = dbFetchRows("SELECT * FROM `ipv6_addresses`");
    $ip_addresses_count = count(array_merge($ipv4_addresses, $ipv6_addresses));
    if ($ip_addresses_count == 0) {
        api_error(404, 'IP addresses do not exist');
    }

    api_success(array_merge($ipv4_addresses, $ipv6_addresses), 'ip_addresses');
}


function list_ip_networks()
{
    check_is_read();

    $app           = \Slim\Slim::getInstance();
    $router        = $app->router()->getCurrentRoute()->getParams();
    $ipv4_networks = array();
    $ipv6_networks = array();

    $ipv4_networks   = dbFetchRows("SELECT * FROM `ipv4_networks`");
    $ipv6_networks   = dbFetchRows("SELECT * FROM `ipv6_networks`");
    $ip_networks_count = count(array_merge($ipv4_networks, $ipv6_networks));
    if ($ip_networks_count == 0) {
        api_error(404, 'IP networks do not exist');
    }

    api_success(array_merge($ipv4_networks, $ipv6_networks), 'ip_networks');
}


function list_arp()
{
    check_is_read();
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $ip       = $router['ip'];
    $hostname = mres($_GET['device']);
    $total    = 0;
    if (empty($ip)) {
        api_error(400, "No valid IP provided");
    } elseif ($ip === "all" && empty($hostname)) {
        api_error(400, "Device argument is required when requesting all entries");
    }

    if ($ip === "all") {
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        $arp = dbFetchRows("SELECT `ipv4_mac`.* FROM `ipv4_mac` LEFT JOIN `ports` ON `ipv4_mac`.`port_id` = `ports`.`port_id` WHERE `ports`.`device_id` = ?", array($device_id));
    } elseif (str_contains($ip, '/')) {
        list($net, $cidr) = explode('/', $ip, 2);
        $arp = dbFetchRows(
            'SELECT * FROM `ipv4_mac` WHERE (inet_aton(`ipv4_address`) & ?) = ?',
            array(cidr2long($cidr), ip2long($net))
        );
    } else {
        $arp = dbFetchRows("SELECT * FROM `ipv4_mac` WHERE `ipv4_address`=?", array($ip));
    }
    api_success($arp, 'arp');
}

function list_services()
{
    check_is_read();
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $services = array();
    $where    = array();
    $params   = array();

    // Filter by State
    if (isset($_GET['state'])) {
        $where[] = '`service_status`=?';
        $params[] = $_GET['state'];
        $where[] = "`service_disabled`='0'";
        $where[] = "`service_ignore`='0'";

        if (!is_numeric($_GET['state'])) {
            api_error(400, "No valid service state provided, valid option is 0=Ok, 1=Warning, 2=Critical");
        }
    }

    //Filter by Type
    if (isset($_GET['type'])) {
        $where[] = '`service_type` LIKE ?';
        $params[] = $_GET['type'];
    }

    //GET by Host
    if (isset($router['hostname'])) {
        $hostname = $router['hostname'];
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        $where[] = '`device_id` = ?';
        $params[] = $device_id;

        if (!is_numeric($device_id)) {
            api_error(500, "No valid hostname or device id provided");
        }
    }

    $query = 'SELECT * FROM `services`';

    if (!empty($where)) {
        $query .= ' WHERE ' . implode(' AND ', $where);
    }
    $query .= ' ORDER BY `service_ip`';
    $services = array(dbFetchRows($query, $params)); // double array for backwards compat :(

    api_success($services, 'services');
}

function list_logs()
{
    check_is_read();
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $type = $app->router()->getCurrentRoute()->getName();
    $hostname = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if ($type === 'list_eventlog') {
        $table = 'eventlog';
        $timestamp = 'datetime';
    } elseif ($type === 'list_syslog') {
        $table = 'syslog';
        $timestamp = 'timestamp';
    } elseif ($type === 'list_alertlog') {
        $table = 'alert_log';
        $timestamp = 'time_logged';
    } elseif ($type === 'list_authlog') {
        $table = 'authlog';
        $timestamp = 'datetime';
    } else {
        $table = 'eventlog';
        $timestamp = 'datetime';
    }

    $start = mres($_GET['start']) ?: 0;
    $limit = mres($_GET['limit']) ?: 50;
    $from = mres($_GET['from']);
    $to = mres($_GET['to']);

    $count_query = 'SELECT COUNT(*)';
    $full_query = "SELECT `devices`.`hostname`, `devices`.`sysName`, `$table`.*";

    $param = array();
    $query = " FROM $table LEFT JOIN `devices` ON `$table`.`device_id`=`devices`.`device_id` WHERE 1";

    if (is_numeric($device_id)) {
        $query .= " AND `devices`.`device_id` = ?";
        $param[] = $device_id;
    }

    if ($from) {
        $query .= " AND $timestamp >= ?";
        $param[] = $from;
    }

    if ($to) {
        $query .= " AND $timestamp <= ?";
        $param[] = $to;
    }

    $count_query = $count_query . $query;
    $count = dbFetchCell($count_query, $param);
    $full_query = $full_query . $query . " ORDER BY $timestamp ASC LIMIT $start,$limit";
    $logs = dbFetchRows($full_query, $param);

    api_success($logs, 'logs', null, 200, null, array('total' => $count));
}

function validate_column_list($columns, $tableName)
{
    global $config;

    $column_names = explode(',', $columns);
    $db_schema = Symfony\Component\Yaml\Yaml::parse(file_get_contents($config['install_dir'] . '/misc/db_schema.yaml'));
    $valid_columns = array_column($db_schema[$tableName]['Columns'], 'Field');
    $invalid_columns = array_diff(array_map('trim', $column_names), $valid_columns);

    if (count($invalid_columns) > 0) {
        $output = array(
            'status'  => 'error',
            'message' => 'Invalid columns: ' . join(',', $invalid_columns),
        );
        $app = \Slim\Slim::getInstance();
        $app->response->setStatus(400);     // Bad request
        $app->response->headers->set('Content-Type', 'application/json');
        echo _json_encode($output);
        $app->stop();
    }
}

function add_service_for_host()
{
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    check_device_permission($device_id);
    $data = json_decode(file_get_contents('php://input'), true);
    $missing_fields = array();

    // Check if some required fields are empty
    if (empty($data['type'])) {
        $missing_fields[] = 'type';
    }
    if (empty($data['ip'])) {
        $missing_fields[] = 'ip';
    }

    // Print error if required fields are missing
    if (!empty($missing_fields)) {
        api_error(400, sprintf("Service field%s %s missing: %s.", ((sizeof($missing_fields)>1)?'s':''), ((sizeof($missing_fields)>1)?'are':'is'), implode(', ', $missing_fields)));
    }
    if (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
        api_error(400, 'service_ip is not a valid IP address.');
    }

    // Check if service type exists
    if (!in_array($data['type'], list_available_services())) {
        api_error(400, "The service " . $data['type'] . " does not exist.\n Available service types: " . implode(', ', list_available_services()));
    }

    // Get parameters
    $service_type = $data['type'];
    $service_ip   = $data['ip'];
    $service_desc = $data['desc'] ? mres($data['desc']) : '';
    $service_param = $data['param'] ? mres($data['param']) : '';
    $service_ignore = $data['ignore'] ? true : false; // Default false

    // Set the service
    $service_id = add_service($device_id, $service_type, $service_desc, $service_ip, $service_param, (int)$service_ignore);
    if ($service_id != false) {
        api_success_noresult(201, "Service $service_type has been added to device $hostname (#$service_id)");
    } else {
        api_error(500, 'Failed to add the service');
    }
}

/**
 * Display Librenms Instance Info
 */
function server_info()
{
    $versions = version_info();
    api_success([
        $versions
    ], 'system');
}
