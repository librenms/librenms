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
    $app->response->setStatus(400);
    $output = array("status" => "error", "message" => "API Token is invalid");
    echo json_encode($output);
    $app->stop();
  }
}

function get_graph_by_id()
{
  // This will return a graph for a given port by the port id
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $vars['id'] = $router['id'];
  $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($router['from']))
  {
    $vars['from'] = $router['from'];
  }
  if(!empty($router['to']))
  {
    $vars['to'] = $router['to'];
  }
  $vars['width'] = $router['width'] ?: 1075;
  $vars['height'] = $router['height'] ?: 300;
  $auth = "1";
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_graph_by_port()
{
  // This will return a graph for a given port by the ifName
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $device_id = $router['id'];
  $vars['port'] = $router['port'];
  $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($router['from']))
  {
    $vars['from'] = $router['from'];
  }
  if(!empty($router['to']))
  {
    $vars['to'] = $router['to'];
  }
  $vars['width'] = $router['width'] ?: 1075;
  $vars['height'] = $router['height'] ?: 300;
  $auth = "1";
  $vars['id'] = dbFetchCell("SELECT `P`.`port_id` FROM `ports` AS `P` WHERE `P`.`device_id`=? AND `P`.`ifName`=?", array($device_id,$vars['port']));
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_graph_by_port_hostname()
{
  // This will return a graph for a given port by the ifName
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];
  $vars['port'] = $router['port'];
  $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($router['from']))
  {
    $vars['from'] = $router['from'];
  }
  if(!empty($router['to']))
  {
    $vars['to'] = $router['to'];
  }
  $vars['width'] = $router['width'] ?: 1075;
  $vars['height'] = $router['height'] ?: 300;
  $auth = "1";
  $vars['id'] = dbFetchCell("SELECT `P`.`port_id` FROM `ports` AS `P` JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id` WHERE `D`.`hostname`=? AND `P`.`ifName`=?", array($hostname,$vars['port']));
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_port_stats_by_id()
{
  // This will return port stats based on port id
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $port_id = $router['id'];
  $stats = dbFetchRow("SELECT * FROM `ports` WHERE `port_id`=?", array($port_id));
  $output = array("status" => "ok", "port" => $stats); 
  $app->response->headers->set('Content-Type', 'application/json');
  echo json_encode($output);  
}

function get_port_stats_by_port()
{
  // This will return port stats based on ifName
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $device_id = $router['id'];
  $if_name = $router['port'];
  $stats = dbFetchRow("SELECT * FROM `ports` WHERE `device_id`=? AND `ifName`=?", array($device_id,$if_name));
  $output = array("status" => "ok", "port" => $stats);
  $app->response->headers->set('Content-Type', 'application/json');
  echo json_encode($output);
}

function get_graph_generic_by_deviceid()
{
  // This will return a graph type given a device id.
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $vars['device'] = $router['id'];
    $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($router['from']))
  {
    $vars['from'] = $router['from'];
  }
  if(!empty($router['to']))
  {
    $vars['to'] = $router['to'];
  }
  $vars['width'] = $router['width'] ?: 1075;
  $vars['height'] = $router['height'] ?: 300;
  $auth = "1";
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function get_graph_generic_by_hostname()
{
  // This will return a graph type given a device id.
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $hostname = $router['hostname'];
    $vars['type'] = $router['type'] ?: 'port_bits';
  if(!empty($router['from']))
  {
    $vars['from'] = $router['from'];
  }
  if(!empty($router['to']))
  {
    $vars['to'] = $router['to'];
  }
  $vars['width'] = $router['width'] ?: 1075;
  $vars['height'] = $router['height'] ?: 300;
  $auth = "1";
  $vars['device'] = dbFetchCell("SELECT `D`.`device_id` FROM `devices` AS `D` WHERE `D`.`hostname`=?", array($hostname));
  $app->response->headers->set('Content-Type', 'image/png');
  require("includes/graphs/graph.inc.php");
}

function list_devices()
{
  // This will return a list of devices
  global $config;
  $app = \Slim\Slim::getInstance();
  $router = $app->router()->getCurrentRoute()->getParams();
  $order = $router['order'];
  $type = $router['type'];
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
  foreach (dbFetchRows("SELECT * FROM `devices` WHERE $sql ORDER by $order") as $device)
  {
    $devices[] = $device;
  }
  $output = array("status" => "ok", "devices" => $devices);
  $app->response->headers->set('Content-Type', 'application/json');
  echo json_encode($output);
}

function add_device()
{
  // This will add a device using the data passed encoded with json
  global $config;
  $app = \Slim\Slim::getInstance();
  $data = json_decode(file_get_contents('php://input'), true);
  // Default status to error and change it if we need to.
  $status = "error";
  if(empty($data))
  {
    $message = "No information has been provided to add this new device";
  }
  elseif(empty($data["hostname"]))
  {
    $message = "Missing the device hostname";
  }
  $hostname = $data['hostname'];
  if ($data['port']) { $port = mres($data['port']); } else { $port = $config['snmp']['port']; }
  if ($data['transport']) { $transport = mres($data['transport']); } else { $transport = "udp"; }
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
    $message = "You haven't specified an SNMP version to use";
  }
  if(empty($message))
  {
    require_once("functions.php");
    $result = addHost($hostname, $snmpver, $port, $transport, 1);
    if($result)
    {
      $status = 'ok';
      $message = 'Device has been added successfully';
    }
    else
    {
      $messge = "Failed adding $hostname";
    }
  }

  $output = array("status" => $status, "message" => $message);
  $app->response->headers->set('Content-Type', 'application/json');
  echo json_encode($output);
}
