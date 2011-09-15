<?php

/* Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */


function discover_new_device($hostname)
{
  global $config;

  if($config['autodiscovery']['xdp']) {
    if ( isDomainResolves($hostname . "." . $config['mydomain']) ) {
      $dst_host = $hostname . "." . $config['mydomain'];
    } else {
      $dst_host = $hostname;
    }
    $ip = gethostbyname($dst_host);

    if ( match_network($config['nets'], $ip) )
    {
      $remote_device_id = addHost ($dst_host, NULL, "v2c");
      if($remote_device_id) {
        $remote_device = device_by_id_cache($remote_device_id, 1);
        echo("+[".$remote_device['hostname']."(".$remote_device['device_id'].")]");
        discover_device($remote_device);
        $remote_device = device_by_id_cache($remote_device_id, 1);
        return $remote_device_id;
      }
    }
  } else {
    return FALSE;
  }
}


function discover_device($device, $options = NULL) 
{

  global $config;
  $valid = array(); ## Reset $valid array

  $attribs = get_dev_attribs($device['device_id']);

  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");

  if($device['os'] == 'generic') // verify if OS has changed from generic
  {
      $device['os']= getHostOS($device); 
      if($device['os'] != 'generic')
      {
          echo "Device os was updated to".$device['os']."!";
          dbUpdate(array('os' => $device['os']), 'devices', '`device_id` = ?', array($device['device_id']));
      }
  }

  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo("(".$device['os_group'].")");
  }

  echo("\n");

  ### If we've specified a module, use that, else walk the modules array
  if ($options['m'])
  {
    if (is_file("includes/discovery/".$options['m'].".inc.php"))
    {
      include("includes/discovery/".$options['m'].".inc.php");
    }
  } else {
    foreach($config['discovery_modules'] as $module => $module_status)
    {
      if ($attribs['discover_'.$module] || ( $module_status && !isset($attribs['discover_'.$module])))
      {
        include('includes/discovery/'.$module.'.inc.php');
      } elseif (isset($attribs['discover_'.$module]) && $attribs['discover_'.$module] == "0") {
        echo("Module [ $module ] disabled on host.\n");
      } else {
        echo("Module [ $module ] disabled globally.\n");
      }
    }
  }

  ### Set type to a predefined type for the OS if it's not already set

  if ($device['type'] == "unknown" || $device['type'] == "")
  {
    if ($config['os'][$device['os']]['type'])
    {
      $device['type'] = $config['os'][$device['os']]['type'];
    }
  }

  $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);

  dbUpdate(array('last_discovered' => array('NOW()'), 'type' => $device['type'], 'last_discovered_timetaken' => $device_time), 'devices', '`device_id` = ?', array($device['device_id']));

  echo("Discovered in $device_time seconds\n");

  global $discovered_devices;

  echo("\n"); $discovered_devices++;
}

?>
