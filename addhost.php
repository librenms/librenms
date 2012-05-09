#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");

if (isset($argv[1]) && $argv[1])
{
  $host      = strtolower($argv[1]);
  $community = $argv[2];
  $snmpver   = strtolower($argv[3]);

  if (is_numeric($argv[4]))
  {
    $port = $argv[4];
  }
  else
  {
    $port = 161;
  }

  if (@!$argv[5])
  {
    $transport = 'udp';
  }
  else
  {
    $transport = $argv[5];
  }

  if ($community)
  {
    $config['snmp']['community'] = array($community);
  }

  if ($snmpver)
  {
    $snmpversions[] = $snmpver;
  }
  else
  {
    $snmpversions = array('v2c','v1');
  }

  while (!$device_id && count($snmpversions))
  {
    $snmpver = array_shift($snmpversions);
    $device_id = addHost($host, $snmpver, $port, $transport);
  }

  if ($device_id)
  {
    $device = device_by_id_cache($device_id);
    echo("Added device ".$device['hostname']." (".$device_id.")\n");
  }
} else {
  print Console_Color::convert("
Observium v".$config['version']." Add Host Tool

Usage: ./addhost.php <%Whostname%n> [community] [v1|v2c] [port] [" . join("|",$config['snmp']['transports']) . "]

%rRemember to run discovery for the host afterwards.%n

");
}

?>
