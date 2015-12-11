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

require_once '../includes/functions.php';


function authToken(\Slim\Route $route) {
    $app   = \Slim\Slim::getInstance();
    $token = $app->request->headers->get('X-Auth-Token');
    if (isset($token) && !empty($token)) {
        $username = dbFetchCell('SELECT `U`.`username` FROM `api_tokens` AS AT JOIN `users` AS U ON `AT`.`user_id`=`U`.`user_id` WHERE `AT`.`token_hash`=?', array($token));
        if (!empty($username)) {
            $authenticated = true;
        }
        else {
            $authenticated = false;
        }
    }
    else {
        $authenticated = false;
    }

    if ($authenticated === false) {
        $app->response->setStatus(401);
        $output = array(
            'status'  => 'error',
            'message' => 'API Token is missing or invalid; please supply a valid token',
        );
        echo _json_encode($output);
        $app->stop();
    }

}



function get_graph_by_port_hostname() {
    // This will return a graph for a given port by the ifName
    global $config;
    $app          = \Slim\Slim::getInstance();
    $router       = $app->router()->getCurrentRoute()->getParams();
    $hostname     = $router['hostname'];
    $vars         = array();
    $vars['port'] = urldecode($router['ifname']);
    $vars['type'] = $router['type'] ?: 'port_bits';
    if (!empty($_GET['from'])) {
        $vars['from'] = $_GET['from'];
    }

    if (!empty($_GET['to'])) {
        $vars['to'] = $_GET['to'];
    }

    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;
    $auth           = '1';
    $vars['id']     = dbFetchCell('SELECT `P`.`port_id` FROM `ports` AS `P` JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id` WHERE `D`.`hostname`=? AND `P`.`ifName`=?', array($hostname, $vars['port']));
    $app->response->headers->set('Content-Type', 'image/png');
    include 'includes/graphs/graph.inc.php';

}


function get_port_stats_by_port_hostname() {
    // This will return port stats based on a devices hostname and ifName
    global $config;
    $app       = \Slim\Slim::getInstance();
    $router    = $app->router()->getCurrentRoute()->getParams();
    $hostname  = $router['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $ifName    = urldecode($router['ifname']);
    $stats     = dbFetchRow('SELECT * FROM `ports` WHERE `device_id`=? AND `ifName`=?', array($device_id, $ifName));
    $output    = array(
        'status' => 'ok',
        'port'   => $stats,
    );
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function get_graph_generic_by_hostname() {
    // This will return a graph type given a device id.
    global $config;
    $app          = \Slim\Slim::getInstance();
    $router       = $app->router()->getCurrentRoute()->getParams();
    $hostname     = $router['hostname'];
    $vars         = array();
    $vars['type'] = $router['type'] ?: 'device_uptime';
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
    $app->response->headers->set('Content-Type', 'image/png');
    include 'includes/graphs/graph.inc.php';

}


function get_device() {
    // return details of a single device
    $app = \Slim\Slim::getInstance();
    $app->response->headers->set('Content-Type', 'application/json');
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);

    // find device matching the id
    $device = device_by_id_cache($device_id);
    if (!$device) {
        $app->response->setStatus(404);
        $output = array(
            'status'  => 'error',
            'message' => "Device $hostname does not exist",
        );
        echo _json_encode($output);
        $app->stop();
    }
    else {
        $output = array(
            'status'  => 'ok',
            'devices' => array($device),
        );
        echo _json_encode($output);
    }

}


function list_devices() {
    // This will return a list of devices
    global $config;
    $app   = \Slim\Slim::getInstance();
    $order = $_GET['order'];
    $type  = $_GET['type'];
    $query = mres($_GET['query']);
    $param = array();
    $join = '';
    if (empty($order)) {
        $order = 'hostname';
    }

    if (stristr($order, ' desc') === false && stristr($order, ' asc') === false) {
        $order = '`'.$order.'` ASC';
    }

    if ($type == 'all' || empty($type)) {
        $sql = '1';
    }
    elseif ($type == 'ignored') {
        $sql = "`ignore`='1' AND `disabled`='0'";
    }
    elseif ($type == 'up') {
        $sql = "`status`='1' AND `ignore`='0' AND `disabled`='0'";
    }
    elseif ($type == 'down') {
        $sql = "`status`='0' AND `ignore`='0' AND `disabled`='0'";
    }
    elseif ($type == 'disabled') {
        $sql = "`disabled`='1'";
    }
    elseif ($type == 'mac') {
        $join = " LEFT JOIN `ports` ON `devices`.`device_id`=`ports`.`device_id` LEFT JOIN `ipv4_mac` ON `ports`.`port_id`=`ipv4_mac`.`port_id` ";
        $sql = "`ipv4_mac`.`mac_address`=?";
        $param[] = $query;
    }
    elseif ($type == 'ipv4') {
        $join = " LEFT JOIN `ports` ON `devices`.`device_id`=`ports`.`device_id` LEFT JOIN `ipv4_addresses` ON `ports`.`port_id`=`ipv4_addresses`.`port_id` ";
        $sql = "`ipv4_addresses`.`ipv4_address`=?";
        $param[] = $query;
    }
    elseif ($type == 'ipv6') {
        $join = " LEFT JOIN `ports` ON `devices`.`device_id`=`ports`.`device_id` LEFT JOIN `ipv6_addresses` ON `ports`.`port_id`=`ipv6_addresses`.`port_id` ";
        $sql = "`ipv6_addresses`.`ipv6_address`=? OR `ipv6_addresses`.`ipv6_compressed`=?";
        $param = array($query,$query);
    }
    else {
        $sql = '1';
    }
    $devices = array();
    foreach (dbFetchRows("SELECT * FROM `devices` $join WHERE $sql ORDER by $order", $param) as $device) {
        $devices[] = $device;
    }

    $count = count($devices);

    $output = array(
        'status'  => 'ok',
        'count'   => $count,
        'devices' => $devices,
    );
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function add_device() {
    // This will add a device using the data passed encoded with json
    // FIXME: Execution flow through this function could be improved
    global $config;
    $app  = \Slim\Slim::getInstance();
    $data = json_decode(file_get_contents('php://input'), true);
    // Default status & code to error and change it if we need to.
    $status = 'error';
    $code   = 500;
    // keep scrutinizer from complaining about snmpver not being set for all execution paths
    $snmpver = 'v2c';
    if (empty($data)) {
        $message = 'No information has been provided to add this new device';
    }

    elseif (empty($data['hostname'])) {
        $message = 'Missing the device hostname';
    }

    $hostname     = $data['hostname'];
    $port         = $data['port'] ? mres($data['port']) : $config['snmp']['port'];
    $transport    = $data['transport'] ? mres($data['transport']) : 'udp';
    $poller_group = $data['poller_group'] ? mres($data['poller_group']) : 0;
    $force_add    = $data['force_add'] ? mres($data['force_add']) : 0;
    if ($data['version'] == 'v1' || $data['version'] == 'v2c') {
        if ($data['community']) {
            $config['snmp']['community'] = array($data['community']);
        }

        $snmpver = mres($data['version']);
    }
    elseif ($data['version'] == 'v3') {
        $v3 = array(
            'authlevel'  => mres($data['authlevel']),
            'authname'   => mres($data['authname']),
            'authpass'   => mres($data['authpass']),
            'authalgo'   => mres($data['authalgo']),
            'cryptopass' => mres($data['cryptopass']),
            'cryptoalgo' => mres($data['cryptoalgo']),
        );

        array_push($config['snmp']['v3'], $v3);
        $snmpver = 'v3';
    }
    else {
        $code    = 400;
        $status  = 'error';
        $message = "You haven't specified an SNMP version to use";
    }
    if (empty($message)) {
        $result = addHost($hostname, $snmpver, $port, $transport, 1, $poller_group, $force_add);
        if ($result) {
            $code    = 201;
            $status  = 'ok';
            $message = "Device $hostname has been added successfully";
        }
        else {
            $message = "Failed adding $hostname";
        }
    }

    $app->response->setStatus($code);
    $output = array(
        'status'  => $status,
        'message' => $message,
    );
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function del_device() {
    // This will add a device using the data passed encoded with json
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    // Default status to error and change it if we need to.
    $status = 'error';
    $code   = 500;
    if (empty($hostname) || $config['api_demo'] == 1) {
        $message = 'No hostname has been provided to delete';
        if ($config['api_demo'] == 1) {
            $message = "This feature isn\'t available in the demo";
        }

        $output = array(
            'status'  => $status,
            'message' => $message,
        );
    }

    else {
        // allow deleting by device_id or hostname
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        $device    = null;
        if ($device_id) {
            // save the current details for returning to the client on successful delete
            $device = device_by_id_cache($device_id);
        }

        if ($device) {
            $response = delete_device($device_id);
            if (empty($response)) {
                // FIXME: Need to provide better diagnostics out of delete_device
                $output = array(
                    'status'  => $status,
                    'message' => 'Device deletion failed',
                );
            }
            else {
                // deletion succeeded - include old device details in response
                $code   = 200;
                $status = 'ok';
                $output = array(
                    'status'  => $status,
                    'message' => $response,
                    'devices' => array($device),
                );
            }
        }
        else {
            // no device matching the name
            $code   = 404;
            $output = array(
                'status'  => $status,
                'message' => "Device $hostname not found",
            );
        }
    }

    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function get_vlans() {
    // This will list all vlans for a given device
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $code     = 500;
    if (empty($hostname)) {
        $output = $output = array(
            'status'  => 'error',
            'message' => 'No hostname has been provided',
        );
    }
    else {
        include_once '../includes/functions.php';
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        $device    = null;
        if ($device_id) {
            // save the current details for returning to the client on successful delete
            $device = device_by_id_cache($device_id);
        }

        if ($device) {
            $vlans       = dbFetchRows('SELECT vlan_vlan,vlan_domain,vlan_name,vlan_type,vlan_mtu FROM vlans WHERE `device_id` = ?', array($device_id));
            $total_vlans = count($vlans);
            $code        = 200;
            $output      = array(
                'status' => 'ok',
                'count'  => $total_vlans,
                'vlans'  => $vlans,
            );
        }

        else {
            $code   = 404;
            $output = array(
                'status' => 'error', "Device $hostname not found"
            );
        }
    }

    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function show_endpoints() {
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


function list_bgp() {
    global $config;
    $app        = \Slim\Slim::getInstance();
    $code       = 500;
    $status     = 'error';
    $message    = 'Error retrieving bgpPeers';
    $sql        = '';
    $sql_params = array();
    $hostname   = $_GET['hostname'];
    $device_id  = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if (is_numeric($device_id)) {
        $sql        = ' AND `device_id`=?';
        $sql_params = array($device_id);
    }

    $bgp_sessions       = dbFetchRows("SELECT * FROM bgpPeers WHERE `bgpPeerState` IS NOT NULL AND `bgpPeerState` != '' $sql", $sql_params);
    $total_bgp_sessions = count($bgp_sessions);
    if (is_numeric($total_bgp_sessions)) {
        $code    = 200;
        $status  = 'ok';
        $message = '';
    }

    $output = array(
        'status'       => "$status",
        'err-msg'      => $message,
        'count'        => $total_bgp_sessions,
        'bgp_sessions' => $bgp_sessions,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function get_graph_by_portgroup() {
    global $config;
    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $group  = $router['group'];
    $vars   = array();
    if (!empty($_GET['from'])) {
        $vars['from'] = $_GET['from'];
    }

    if (!empty($_GET['to'])) {
        $vars['to'] = $_GET['to'];
    }

    $vars['width']  = $_GET['width'] ?: 1075;
    $vars['height'] = $_GET['height'] ?: 300;
    $auth           = '1';
    $type_where     = ' (';
    $or             = '';
    $type_param     = array();
    foreach (explode(',', $group) as $type) {
        $type_where  .= " $or `port_descr_type` = ?";
        $or           = 'OR';
        $type_param[] = $type;
    }

    $type_where .= ') ';
    $if_list     = '';
    $seperator   = '';
    $ports       = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);
    foreach ($ports as $port) {
        $if_list  .= $seperator.$port['port_id'];
        $seperator = ',';
    }

    unset($seperator);
    $vars['type'] = 'multiport_bits_separate';
    $vars['id']   = $if_list;
    $app->response->headers->set('Content-Type', 'image/png');
    include 'includes/graphs/graph.inc.php';

}


function get_graphs() {
    global $config;
    $code     = 200;
    $status   = 'ok';
    $message  = '';
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];

    // FIXME: this has some overlap with html/pages/device/graphs.inc.php
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
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

    $total_graphs = count($graphs);
    $output       = array(
        'status'  => "$status",
        'err-msg' => $message,
        'count'   => $total_graphs,
        'graphs'  => $graphs,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function get_port_graphs() {
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
    }
    else {
        $columns = 'ifName';
    }

    // use hostname as device_id if it's all digits
    $device_id   = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $ports       = dbFetchRows("SELECT $columns FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `ifIndex` ASC", array($device_id));
    $total_ports = count($ports);
    $output      = array(
        'status'  => 'ok',
        'err-msg' => '',
        'count'   => $total_ports,
        'ports'   => $ports,
    );
    $app->response->setStatus('200');
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}

function list_alert_rules() {
    global $config;
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
    $total_rules = count($rules);
    $output      = array(
        'status'  => 'ok',
        'err-msg' => '',
        'count'   => $total_rules,
        'rules'   => $rules,
    );
    $app->response->setStatus('200');
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function list_alerts() {
    global $config;
    $app    = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    if (isset($_GET['state'])) {
        $param = array(mres($_GET['state']));
    }
    else {
        $param = array('1');
    }

    $sql = '';
    if (isset($router['id']) && $router['id'] > 0) {
        $alert_id = mres($router['id']);
        $sql      = 'AND id=?';
        array_push($param, $alert_id);
    }

    $alerts       = dbFetchRows("SELECT `D`.`hostname`, `A`.* FROM `alerts` AS `A`, `devices` AS `D` WHERE `D`.`device_id` = `A`.`device_id` AND `A`.`state` IN (?) $sql", $param);
    $total_alerts = count($alerts);
    $output       = array(
        'status'  => 'ok',
        'err-msg' => '',
        'count'   => $total_alerts,
        'alerts'  => $alerts,
    );
    $app->response->setStatus('200');
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function add_edit_rule() {
    global $config;
    $app  = \Slim\Slim::getInstance();
    $data = json_decode(file_get_contents('php://input'), true);

    $status  = 'error';
    $message = '';
    $code    = 500;

    $rule_id = mres($data['rule_id']);

    $device_id = mres($data['device_id']);
    if (empty($device_id) && !isset($rule_id)) {
        $message = 'Missing the device id or global device id (-1)';
    }
    elseif ($device_id == 0) {
        $device_id = '-1';
    }

    $rule = $data['rule'];
    if (empty($rule)) {
        $message = 'Missing the alert rule';
    }

    $name = mres($data['name']);
    if (empty($name)) {
        $message = 'Missing the alert rule name';
    }

    $severity = mres($data['severity']);
    $sevs     = array(
        'ok',
        'warning',
        'critical',
    );
    if (!in_array($severity, $sevs)) {
        $message = 'Missing the severity';
    }

    $disabled = mres($data['disabled']);
    if ($disabled != '0' && $disabled != '1') {
        $disabled = 0;
    }

    $count     = mres($data['count']);
    $mute      = mres($data['mute']);
    $delay     = mres($data['delay']);
    $delay_sec = convert_delay($delay);
    if ($mute == 1) {
        $mute = true;
    }
    else {
        $mute = false;
    }

    $extra      = array(
        'mute'  => $mute,
        'count' => $count,
        'delay' => $delay_sec,
    );
    $extra_json = json_encode($extra);

    if(!isset($rule_id)) {
        if (dbFetchCell('SELECT `name` FROM `alert_rules` WHERE `name`=?', array($name)) == $name) {
            $message = 'Addition failed : Name has already been used';
        }
    }
    else {
        if(dbFetchCell("SELECT name FROM alert_rules WHERE name=? AND id !=? ", array($name, $rule_id)) == $name) {
            $message = 'Edition failed : Name has already been used';
        }
    }

    if (empty($message)) {
        if (is_numeric($rule_id)) {
            if (dbUpdate(array('name' => $name, 'rule' => $rule, 'severity' => $severity, 'disabled' => $disabled, 'extra' => $extra_json), 'alert_rules', 'id=?', array($rule_id)) >= 0) {
                $status = 'ok';
                $code   = 200;
            }

            else {
                $message = 'Failed to update existing alert rule';
            }
        }
        elseif (dbInsert(array('name' => $name, 'device_id' => $device_id, 'rule' => $rule, 'severity' => $severity, 'disabled' => $disabled, 'extra' => $extra_json), 'alert_rules')) {
            $status = 'ok';
            $code   = 200;
        }
        else {
            $message = 'Failed to create new alert rule';
        }
    }

    $output = array(
        'status'  => $status,
        'err-msg' => $message,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function delete_rule() {
    global $config;
    $app     = \Slim\Slim::getInstance();
    $router  = $app->router()->getCurrentRoute()->getParams();
    $rule_id = mres($router['id']);
    $status  = 'error';
    $err_msg = '';
    $message = '';
    $code    = 500;
    if (is_numeric($rule_id)) {
        $status = 'ok';
        $code   = 200;
        if (dbDelete('alert_rules', '`id` =  ? LIMIT 1', array($rule_id))) {
            $message = 'Alert rule has been removed';
        }
        else {
            $message = 'No alert rule by that ID';
        }
    }

    else {
        $err_msg = 'Invalid rule id has been provided';
    }

    $output = array(
        'status'  => $status,
        'err-msg' => $err_msg,
        'message' => $message,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function ack_alert() {
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $alert_id = mres($router['id']);
    $status   = 'error';
    $err_msg  = '';
    $message  = '';
    $code     = 500;
    if (is_numeric($alert_id)) {
        $status = 'ok';
        $code   = 200;
        if (dbUpdate(array('state' => 2), 'alerts', '`id` = ? LIMIT 1', array($alert_id))) {
            $message = 'Alert has been ackgnowledged';
        }
        else {
            $message = 'No alert by that ID';
        }
    }
    else {
        $err_msg = 'Invalid alert has been provided';
    }

    $output = array(
        'status'  => $status,
        'err-msg' => $err_msg,
        'message' => $message,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}

function unmute_alert() {
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $alert_id = mres($router['id']);
    $status   = 'error';
    $err_msg  = '';
    $message  = '';
    $code     = 500;
    if (is_numeric($alert_id)) {
        $status = 'ok';
        $code   = 200;
        if (dbUpdate(array('state' => 1), 'alerts', '`id` = ? LIMIT 1', array($alert_id))) {
            $message = 'Alert has been unmuted';
        }
        else {
            $message = 'No alert by that ID';
        }
    }
    else {
        $err_msg = 'Invalid alert has been provided';
    }

    $output = array(
        'status'  => $status,
        'err-msg' => $err_msg,
        'message' => $message,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}


function get_inventory() {
    global $config;
    $app      = \Slim\Slim::getInstance();
    $router   = $app->router()->getCurrentRoute()->getParams();
    $status   = 'error';
    $err_msg  = '';
    $code     = 500;
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $sql       = '';
    $params    = array();
    if (isset($_GET['entPhysicalClass']) && !empty($_GET['entPhysicalClass'])) {
        $sql     .= ' AND entPhysicalClass=?';
        $params[] = mres($_GET['entPhysicalClass']);
    }

    if (isset($_GET['entPhysicalContainedIn']) && !empty($_GET['entPhysicalContainedIn'])) {
        $sql     .= ' AND entPhysicalContainedIn=?';
        $params[] = mres($_GET['entPhysicalContainedIn']);
    }
    else {
        $sql .= ' AND entPhysicalContainedIn="0"';
    }

    if (!is_numeric($device_id)) {
        $err_msg   = 'Invalid device provided';
        $total_inv = 0;
        $inventory = array();
    }

    else {
        $inventory = dbFetchRows("SELECT * FROM `entPhysical` WHERE 1 $sql", $params);
        $code      = 200;
        $status    = 'ok';
        $total_inv = count($inventory);
    }

    $output = array(
        'status'    => $status,
        'err-msg'   => $err_msg,
        'count'     => $total_inv,
        'inventory' => $inventory,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);

}


function list_oxidized() {
    global $config;
    $app = \Slim\Slim::getInstance();
    $app->response->headers->set('Content-Type', 'application/json');

    $devices = array();
    $device_types = "'".implode("','", $config['oxidized']['ignore_types'])."'";
    $device_os    = "'".implode("','", $config['oxidized']['ignore_os'])."'";
    foreach (dbFetchRows("SELECT hostname,os FROM `devices` LEFT JOIN devices_attribs AS `DA` ON devices.device_id = DA.device_id AND `DA`.attrib_type='override_Oxidized_disable' WHERE `status`='1' AND (DA.attrib_value = 'false' OR DA.attrib_value IS NULL) AND (`type` NOT IN ($device_types) AND `os` NOT IN ($device_os))") as $device) {
        $devices[] = $device;
    }

    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($devices);

}

function list_bills() {
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $status = 'ok';
    $err_msg = '';
    $message = '';
    $code = 200;
    $bills = array();
    $bill_id = mres($router['bill_id']);
    $bill_ref = mres($_GET['ref']);
    $bill_custid = mres($_GET['custid']);
    if (!empty($bill_custid)) {
        $sql   = '`bill_custid` = ?';
        $param = array($bill_custid);
    }
    elseif (!empty($bill_ref)) {
        $sql   = '`bill_ref` = ?';
        $param = array($bill_ref);
    }
    elseif (is_numeric($bill_id)) {
        $sql   = '`bills`.`bill_id` = ?';
        $param = array($bill_id);
    }
    else {
        $sql   = '';
        $param = array();
    }

    if (count($param) >= 1) {
        $sql = "WHERE $sql";
    }

    foreach (dbFetchRows("SELECT `bills`.*,COUNT(port_id) AS `ports_total` FROM `bills` LEFT JOIN `bill_ports` ON `bill_ports`.`bill_id`=`bills`.`bill_id` $sql GROUP BY `bill_name`,`bill_ref` ORDER BY `bill_name`",$param) as $bill) {
        $rate_data    = $bill;
        $allowed = '';
        $used = '';
        $percent = '';
        $overuse = '';

        if ($bill['bill_type'] == "cdr") {
            $allowed = format_si($bill['bill_cdr'])."bps";
            $used    = format_si($rate_data['rate_95th'])."bps";
            $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
            $overuse = $rate_data['rate_95th'] - $bill['bill_cdr'];
            $overuse = (($overuse <= 0) ? "-" : format_si($overuse));
        }
        elseif ($bill['bill_type'] == "quota") {
            $allowed = format_bytes_billing($bill['bill_quota']);
            $used    = format_bytes_billing($rate_data['total_data']);
            $percent = round(($rate_data['total_data'] / ($bill['bill_quota'])) * 100,2);
            $overuse = $rate_data['total_data'] - $bill['bill_quota'];
            $overuse = (($overuse <= 0) ? "-" : format_bytes_billing($overuse));
        }
        $bill['allowed'] = $allowed;
        $bill['used'] = $used;
        $bill['percent'] = $percent;
        $bill['overuse'] = $overuse;
        $bills[] = $bill;
    }
    $count = count($bills);
    $output = array(
        'status' => $status,
        'message' => $message,
        'err-msg' => $err_msg,
        'count' => $count,
        'bills' => $bills
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}

function update_device() {
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $status   = 'error';
    $code     = 500;
    $hostname = $router['hostname'];
    // use hostname as device_id if it's all digits
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $data = json_decode(file_get_contents('php://input'), true);
    $bad_fields = array('id','hostname');
    if (empty($data['field'])) {
        $message = 'Device field to patch has not been supplied';
    }
    elseif (in_array($data['field'], $bad_fields)) {
        $message = 'Device field is not allowed to be updated';
    }
    else {
        if (dbUpdate(array(mres($data['field']) => mres($data['data'])), 'devices', '`device_id`=?', array($device_id)) >= 0) {
            $status = 'ok';
            $message = 'Device ' . mres($data['field']) . ' field has been updated';
            $code = 200;
        }
        else {
            $message = 'Device ' . mres($data['field']) . ' field failed to be updated';
        }
    }
    $output = array(
        'status'  => $status,
        'message' => $message,
    );
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}
