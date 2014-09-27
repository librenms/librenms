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

if (!defined('JSON_UNESCAPED_SLASHES'))
    define('JSON_UNESCAPED_SLASHES', 64);
if (!defined('JSON_PRETTY_PRINT'))
    define('JSON_PRETTY_PRINT', 128);
if (!defined('JSON_UNESCAPED_UNICODE'))
    define('JSON_UNESCAPED_UNICODE', 256);

function _json_encode($data, $options = 448)
{
    if (version_compare(PHP_VERSION, '5.4', '>='))
    {
        return json_encode($data, $options);
    }

    return _json_format(json_encode($data), $options);
}

function _pretty_print_json($json)
{
    return _json_format($json, JSON_PRETTY_PRINT);
}

function _json_format($json, $options = 448)
{
    $prettyPrint = (bool) ($options & JSON_PRETTY_PRINT);
    $unescapeUnicode = (bool) ($options & JSON_UNESCAPED_UNICODE);
    $unescapeSlashes = (bool) ($options & JSON_UNESCAPED_SLASHES);

    if (!$prettyPrint && !$unescapeUnicode && !$unescapeSlashes)
    {
        return $json;
    }

    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $outOfQuotes = true;
    $buffer = '';
    $noescape = true;

    for ($i = 0; $i < $strLen; $i++)
    {
        // Grab the next character in the string
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ('"' === $char && $noescape)
        {
            $outOfQuotes = !$outOfQuotes;
        }

        if (!$outOfQuotes)
        {
            $buffer .= $char;
            $noescape = '\\' === $char ? !$noescape : true;
            continue;
        }
        elseif ('' !== $buffer)
        {
            if ($unescapeSlashes)
            {
                $buffer = str_replace('\\/', '/', $buffer);
            }

            if ($unescapeUnicode && function_exists('mb_convert_encoding'))
            {
                // http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
                $buffer = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                    function ($match)
                    {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $buffer);
            } 

            $result .= $buffer . $char;
            $buffer = '';
            continue;
        }
        elseif(false !== strpos(" \t\r\n", $char))
        {
            continue;
        }

        if (':' === $char)
        {
            // Add a space after the : character
            $char .= ' ';
        }
        elseif (('}' === $char || ']' === $char))
        {
            $pos--;
            $prevChar = substr($json, $i - 1, 1);

            if ('{' !== $prevChar && '[' !== $prevChar)
            {
                // If this character is the end of an element,
                // output a new line and indent the next line
                $result .= $newLine;
                for ($j = 0; $j < $pos; $j++)
                {
                    $result .= $indentStr;
                }
            }
            else
            {
                // Collapse empty {} and []
                $result = rtrim($result) . "\n\n" . $indentStr;
            }
        }

        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line
        if (',' === $char || '{' === $char || '[' === $char)
        {
            $result .= $newLine;

            if ('{' === $char || '[' === $char)
            {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++)
            {
                $result .= $indentStr;
            }
        }
    }
    // If buffer not empty after formating we have an unclosed quote
    if (strlen($buffer) > 0)
    {
        //json is incorrectly formatted
        $result = false;
    }

    return $result;
}

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
    $app->response->setStatus(403);
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
  $vars['port'] = $router['ifname'];
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
  $ifName = $router['ifname'];
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
    require_once("../includes/functions.php");
    $result = addHost($hostname, $snmpver, $port, $transport, 1);
    if($result)
    {
      $status = 'ok';
      $message = 'Device has been added successfully';
    }
    else
    {
      $message = "Failed adding $hostname";
    }
  }

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
  if(empty($hostname))
  {
    $message = "No hostname has been provided to delete this device";
  }
  elseif(empty($hostname))
  {
    $message = "Missing the device hostname";
  }
  if(empty($message))
  {
    require_once("../includes/functions.php");
    $device_id = get_device_id($hostname);
    $response = delete_device($device_id);
    if(empty($response))
    {
      $message = "Device couldn't be deleted";
    }
    else
    {
      $message = $response;
      $status = "ok";
    }
  }
  $output = array("status" => $status, "message" => $message);
  $app->response->headers->set('Content-Type', 'application/json');
  echo _json_encode($output);
}
