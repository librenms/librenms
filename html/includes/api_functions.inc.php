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

require_once("../includes/functions.php");

function authToken(\Slim\Route $route)
{
  $app = \Slim\Slim::getInstance();
  $token = $app->request->headers->get('X-Auth-Token');
  if(isset($token) && !empty($token))
  {
    $username = dbFetchCell("SELECT `U`.`username` FROM `api_tokens` AS AT JOIN `users` AS U ON `AT`.`user_id`=`U`.`user_id` WHERE `AT`.`token_hash`=?", array($token));
    if(!empty($username))
    {
      $authenticated = true;
    }
    else
    {
      $authenticated = false;
    }
  }
  else
  {
    $authenticated = false;
  }

  if($authenticated === false)
  {
    $app->response->setStatus(401);
    $output = array("status" => "error", "message" => "API Token is missing or invalid; please supply a valid token");
    echo _json_encode($output);
    $app->stop();
  }
}

function get_graph_by_port_hostname()
{
  // This will return a graph for a given port by the ifName
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];
  $vars = array();
  $vars['port'] = urldecode($router['ifname']);
  $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($_GET['from']))
  {
    $vars['from'] = $_GET['from'];
  }
  if(!empty($_GET['to']))
  {
    $vars['to'] = $_GET['to'];
  }
  $vars['width'] = $_GET['width'] ?: 1075;
  $vars['height'] = $_GET['height'] ?: 300;
  $auth = "1";
  $vars['id'] = dbFetchCell("SELECT `P`.`port_id` FROM `ports` AS `P` JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id` WHERE `D`.`hostname`=? AND `P`.`ifName`=?", array($hostname,$vars['port']));
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_port_stats_by_port_hostname()
{
  // This will return port stats based on a devices hostname and ifName
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $ifName = urldecode($router['ifname']);
  $stats = dbFetchRow("SELECT * FROM `ports` WHERE `ifName`=?", array($ifName));
  $output = array("status" => "ok", "port" => $stats);
  $app->response->headers->set('Content-Type', 'application/json');
  echo _json_encode($output);
}

function get_graph_generic_by_hostname()
{
  // This will return a graph type given a device id.
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];
  $vars = array();
  $vars['type'] = $router['type'] ?: 'device_uptime';
  if(!empty($_GET['from']))
  {
    $vars['from'] = $_GET['from'];
  }
  if(!empty($_GET['to']))
  {
    $vars['to'] = $_GET['to'];
  }
  $vars['width'] = $_GET['width'] ?: 1075;
  $vars['height'] = $_GET['height'] ?: 300;
  $auth = "1";
  $vars['device'] = dbFetchCell("SELECT `D`.`device_id` FROM `devices` AS `D` WHERE `D`.`hostname`=?", array($hostname));
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_device()
{
  // return details of a single device
  $app = \Slim\Slim::getInstance();
  $app->response->headers->set('Content-Type', 'application/json');
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];

  // use hostname as device_id if it's all digits
  $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);

  // find device matching the id
  $device = device_by_id_cache($device_id);
  if (!$device) {
    $app->response->setStatus(404);
    $output = array("status" => "error", "message" => "Device $hostname does not exist");
    echo _json_encode($output);
    $app->stop();
  }
  else {
    $output = array("status" => "ok", "devices" => array($device));
    echo _json_encode($output);
  }
}

function list_devices()
{
  // This will return a list of devices
  global $config;
  $app = \Slim\Slim::getInstance();
  $order = $_GET['order'];
  $type = $_GET['type'];
  if(empty($order))
  {
    $order = "hostname";
  }
  if(stristr($order,' desc') === FALSE && stristr($order, ' asc') === FALSE)
  {
    $order .= ' ASC';
  }
  if($type == 'all' || empty($type))
  {
    $sql = "1";
  }
  elseif($type == 'ignored')
  {
    $sql = "ignore='1' AND disabled='0'";
  }
  elseif($type == 'up')
  {
    $sql = "status='1' AND ignore='0' AND disabled='0'";
  }
  elseif($type == 'down')
  {
    $sql = "status='0' AND ignore='0' AND disabled='0'";
  }
  elseif($type == 'disabled')
  {
    $sql = "disabled='1'";
  }
  else
  {
    $sql = "1";
  }
  $devices = array();
  foreach (dbFetchRows("SELECT * FROM `devices` WHERE $sql ORDER by $order") as $device)
  {
    $devices[] = $device;
  }
  $output = array("status" => "ok", "devices" => $devices);
  $app->response->headers->set('Content-Type', 'application/json');
  echo _json_encode($output);
}

function add_device()
{
  // This will add a device using the data passed encoded with json
  // FIXME: Execution flow through this function could be improved
  global $config;
  $app = \Slim\Slim::getInstance();
  $data = json_decode(file_get_contents('php://input'), true);
  // Default status & code to error and change it if we need to.
  $status = "error";
  $code = 500;
  // keep scrutinizer from complaining about snmpver not being set for all execution paths
  $snmpver = "v2c";
  if(empty($data))
  {
    $message = "No information has been provided to add this new device";
  }
  elseif(empty($data["hostname"]))
  {
    $message = "Missing the device hostname";
  }
  $hostname = $data['hostname'];
  $port = $data['port'] ? mres($data['port']) : $config['snmp']['port'];
  $transport = $data['transport'] ? mres($data['transport']) : "udp";
  if($data['version'] == "v1" || $data['version'] == "v2c")
  {
    if ($data['community'])
    {
      $config['snmp']['community'] = array($data['community']);
    }
    $snmpver = mres($data['version']);
  }
  elseif($data['version'] == 'v3')
  {
    $v3 = array (
      'authlevel' => mres($data['authlevel']),
      'authname' => mres($data['authname']),
      'authpass' => mres($data['authpass']),
      'authalgo' => mres($data['authalgo']),
      'cryptopass' => mres($data['cryptopass']),
      'cryptoalgo' => mres($data['cryptoalgo']),
    );

    array_push($config['snmp']['v3'], $v3);
    $snmpver = "v3";
  }
  else
  {
    $code = 400;
    $status = "error";
    $message = "You haven't specified an SNMP version to use";
  }
  if(empty($message))
  {
    $result = addHost($hostname, $snmpver, $port, $transport, 1);
    if($result)
    {
      $code = 201;
      $status = "ok";
      $message = "Device $hostname has been added successfully";
    }
    else
    {
      $message = "Failed adding $hostname";
    }
  }

  $app->response->setStatus($code);
  $output = array("status" => $status, "message" => $message);
  $app->response->headers->set('Content-Type', 'application/json');
  echo _json_encode($output);
}


function del_device()
{
  // This will add a device using the data passed encoded with json
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];
  // Default status to error and change it if we need to.
  $status = "error";
  $code = 500;
  if(empty($hostname))
  {
    $message = "No hostname has been provided to delete";
    $output = array("status" => $status, "message" => $message);
  }
  else
  {

    // allow deleting by device_id or hostname
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    $device = null;
    if ($device_id) {
      // save the current details for returning to the client on successful delete
      $device = device_by_id_cache($device_id);
    }
    if ($device) {
      $response = delete_device($device_id);
      if(empty($response)) {
	// FIXME: Need to provide better diagnostics out of delete_device
	$output = array("status" => $status, "message" => "Device deletion failed");
      }
      else {
	// deletion succeeded - include old device details in response
	$code = 200;
	$status = "ok";
	$output = array("status" => $status, "message" => $response, "devices" => array($device));
      }
    }
    else {
      // no device matching the name
      $code = 404;
      $output = array("status" => $status, "message" => "Device $hostname not found");
    }
  }

  $app->response->setStatus($code);
  $app->response->headers->set('Content-Type', 'application/json');
  echo _json_encode($output);
}

function get_vlans() {
    // This will list all vlans for a given device
    global $config;
    $app = \Slim\Slim::getInstance();
    $router = $app->router()->getCurrentRoute()->getParams();
    $hostname = $router['hostname'];
    $code = 500;
    if(empty($hostname)) {
        $output = $output = array("status" => "error", "message" => "No hostname has been provided");
    } else {
        require_once("../includes/functions.php");
        $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
        $device = null;
        if ($device_id) {
            // save the current details for returning to the client on successful delete
            $device = device_by_id_cache($device_id);
        }
        if ($device) {
            $vlans = dbFetchRows("SELECT vlan_vlan,vlan_domain,vlan_name,vlan_type,vlan_mtu FROM vlans WHERE `device_id` = ?", array($device_id));
            $total_vlans = count($vlans);
            $code = 200;
            $output = array("status" => "ok", "count" => $total_vlans, "vlans" => $vlans);
        } else {
            $code = 404;
            $output = array("status" => "error", "Device $hostname not found");
        }
    }
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}

function show_endpoints() {
    global $config;
    $app = \Slim\Slim::getInstance();
    $routes = $app->router()->getNamedRoutes(); 
    $output = array();
    foreach($routes as $route) { 
        $output[$route->getName()] = $config['base_url'].$route->getPattern(); 
    }
    $app->response->setStatus('200');
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}

function list_bgp() {
    global $config;
    $app = \Slim\Slim::getInstance();
    $code = 500;
    $status = 'error';
    $message = 'Error retrieving bgpPeers';
    $sql = '';
    $sql_params = array();
    $hostname = $_GET['hostname'];
    $device_id = ctype_digit($hostname) ? $hostname : getidbyname($hostname);
    if(is_numeric($device_id)) {
        $sql = " AND `device_id`=?";
        $sql_params = array($device_id);
    }
    $bgp_sessions = dbFetchRows("SELECT * FROM bgpPeers WHERE `bgpPeerState` IS NOT NULL AND `bgpPeerState` != '' $sql", $sql_params);
    $total_bgp_sessions = count($bgp_sessions);
    if(is_numeric($total_bgp_sessions)) {
        $code = 200;
        $status = 'ok';
        $message = '';
    }
    $output = array("status" => "$status", "err-msg" => $message, "count" => $total_bgp_sessions, "bgp_sessions" => $bgp_sessions);
    $app->response->setStatus($code);
    $app->response->headers->set('Content-Type', 'application/json');
    echo _json_encode($output);
}

function get_graph_by_group() {
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $group = $router['group'];
  $vars = array();
  if(!empty($_GET['from']))
  {
    $vars['from'] = $_GET['from'];
  }
  if(!empty($_GET['to']))
  {
    $vars['to'] = $_GET['to'];
  }
  $vars['width'] = $_GET['width'] ?: 1075;
  $vars['height'] = $_GET['height'] ?: 300;
  $auth = "1";
  $type_where = " (";
  foreach (explode(",", $group) as $type)
  {
    $type_where .= " $or `port_descr_type` = ?";
    $or = "OR";
    $type_param[] = $type;
  }

  $type_where .= ") ";
  $ports = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);
  foreach ($ports as $port)
  {
    $if_list .= $seperator . $port['port_id'];
    $seperator = ",";
  }
  unset($seperator);
  $vars['type'] = "multiport_bits_separate";
  $vars['id'] = $if_list;
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}
