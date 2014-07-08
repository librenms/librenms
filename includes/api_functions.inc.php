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
