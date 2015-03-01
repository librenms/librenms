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

if (!empty($argv[1]))
{
  $host      = strtolower($argv[1]);
  $community = $argv[2];
  $snmpver   = strtolower($argv[3]);

  $port = 161;
  $transport = 'udp';

  if ($snmpver === "v3")
  {
    $seclevel = $community;

    // These values are the same as in defaults.inc.php
    $v3 = array(
      'authlevel'  => "noAuthNoPriv",
      'authname'   => "root",
      'authpass'   => "",
      'authalgo'   => "MD5",
      'cryptopass' => "",
      'cryptoalgo' => "AES"
    );

    if ($seclevel === "nanp" or $seclevel === "any" or $seclevel === "noAuthNoPriv")
    {
      $v3['authlevel'] = "noAuthNoPriv";
      $v3args = array_slice($argv, 4);

      while ($arg = array_shift($v3args))
      {
        // parse all remaining args
        if (is_numeric($arg))
        {
          $port = $arg;
        }
        elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/', $arg))
        {
          $transport = $arg;
        }
        else
        {
          // should add a sanity check of chars allowed in user
          $user = $arg;
        }

      }

      if ($seclevel === "nanp")
        { array_push($config['snmp']['v3'], $v3); }

      $device_id = addHost($host, $snmpver, $port, $transport);

    }
    elseif ($seclevel === "anp" or $seclevel === "authNoPriv")
    {

      $v3['authlevel'] = "authNoPriv";
      $v3args = array_slice($argv, 4);
      $v3['authname'] = array_shift($v3args);
      $v3['authpass'] = array_shift($v3args);

      while ($arg = array_shift($v3args))
      {
        // parse all remaining args
        if (is_numeric($arg))
        {
          $port = $arg;
        }
        elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/i', $arg))
        {
          $transport = $arg;
        }
        elseif (preg_match ('/^(sha|md5)$/i', $arg))
        {
          $v3['authalgo'] = $arg;
        }
      }

      array_push($config['snmp']['v3'], $v3);
      $device_id = addHost($host, $snmpver, $port, $transport);

    }
    elseif ($seclevel === "ap" or $seclevel === "authPriv")
    {
      $v3['authlevel'] = "authPriv";
      $v3args = array_slice($argv, 4);
      $v3['authname'] = array_shift($v3args);
      $v3['authpass'] = array_shift($v3args);
      $v3['cryptopass'] = array_shift($v3args);

      while ($arg = array_shift($v3args))
      {
        // parse all remaining args
        if (is_numeric($arg))
        {
          $port = $arg;
        }
        elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/i', $arg))
        {
          $transport = $arg;
        }
        elseif (preg_match ('/^(sha|md5)$/i', $arg))
        {
          $v3['authalgo'] = $arg;
        }
        elseif (preg_match ('/^(aes|des)$/i', $arg))
        {
          $v3['cryptoalgo'] = $arg;
        }
      }

      array_push($config['snmp']['v3'], $v3);
      $device_id = addHost($host, $snmpver, $port, $transport);

    }
    else
    {
      // Error or do nothing ?
    }
  }
  else // v1 or v2c
  {
    $v2args = array_slice($argv, 2);

    while ($arg = array_shift($v2args))
    {
      // parse all remaining args
      if (is_numeric($arg))
      {
        $port = $arg;
      }
      elseif (preg_match ('/(' . implode("|",$config['snmp']['transports']) . ')/i', $arg))
      {
        $transport = $arg;
      }
      elseif (preg_match ('/^(v1|v2c)$/i', $arg))
      {
        $snmpver = $arg;
      }
    }

    if ($community)
    {
      $config['snmp']['community'] = array($community);
    }

    $device_id = addHost($host, $snmpver, $port, $transport);
  }

  if ($snmpver)
  {
    $snmpversions[] = $snmpver;
  }
  else
  {
    $snmpversions = array('v2c', 'v3', 'v1');
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
    exit;
  }
}

print $console_color->convert("\n" . $config['project_name_version']." Add Host Tool

Usage (SNMPv1/2c): ./addhost.php <%Whostname%n> [community] [v1|v2c] [port] [" . implode("|",$config['snmp']['transports']) . "]
Usage (SNMPv3)   :  Config Defaults : ./addhost.php <%Whostname%n> any v3 [user] [port] [" . implode("|",$config['snmp']['transports']) . "]
                   No Auth, No Priv : ./addhost.php <%Whostname%n> nanp v3 [user] [port] [" . implode("|",$config['snmp']['transports']) . "]
                      Auth, No Priv : ./addhost.php <%Whostname%n> anp v3 <user> <password> [md5|sha] [port] [" . implode("|",$config['snmp']['transports']) . "]
                      Auth,    Priv : ./addhost.php <%Whostname%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|dsa] [port] [" . implode("|",$config['snmp']['transports']) . "]
%rRemember to run discovery for the host afterwards.%n

");

?>
