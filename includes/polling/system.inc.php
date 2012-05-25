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

  unset($poll_device);

  $snmpdata = snmp_get_multi($device, "sysUpTime.0 sysLocation.0 sysContact.0 sysName.0", "-OQUs", "SNMPv2-MIB");
  $poll_device = $snmpdata[0];

  $poll_device['sysDescr'] = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB");
  $poll_device['sysObjectID'] = snmp_get($device, "sysObjectID.0", "-Oqvn", "SNMPv2-MIB");
  $poll_device['sysName'] = strtolower($poll_device['sysName']);

  if (!empty($agent_data['uptime'])) { list($uptime) = explode(" ", $agent_data['uptime']); $uptime = round($uptime); echo("Using UNIX Agent Uptime ($uptime)\n");}

  if (empty($uptime))
  {
    $hrSystemUptime = snmp_get($device, "hrSystemUptime.0", "-Oqv", "HOST-RESOURCES-MIB");
    if (!empty($hrSystemUptime) && !strpos($hrSystemUptime, "No") && ($device['os'] != "windows"))
    {
      echo("Using hrSystemUptime (".$hrSystemUptime.")\n");
      $agent_uptime = $uptime; /// Move uptime into agent_uptime
      #HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
      $hrSystemUptime = str_replace("(", "", $hrSystemUptime);
      $hrSystemUptime = str_replace(")", "", $hrSystemUptime);
      list($days,$hours, $mins, $secs) = explode(":", $hrSystemUptime);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
    } else {
      echo("Using SNMP Agent Uptime (".$poll_device['sysUpTime'].")\n");
      #SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
      $poll_device['sysUpTime'] = str_replace("(", "", $poll_device['sysUpTime']);
      $poll_device['sysUpTime'] = str_replace(")", "", $poll_device['sysUpTime']);
      list($days, $hours, $mins, $secs) = explode(":", $poll_device['sysUpTime']);
      list($secs, $microsecs) = explode(".", $secs);
      $hours = $hours + ($days * 24);
      $mins = $mins + ($hours * 60);
      $secs = $secs + ($mins * 60);
      $uptime = $secs;
    }
  }

  // Use snmpEngineTime (68 year rollover) to cross-reference for false positives in device rebooting due to sysUpTime rollover issues
  $snmpEngineTime = (integer)snmp_get($device, "snmpEngineTime.0", "-OUqv", "SNMP-FRAMEWORK-MIB");
  if (!is_numeric($snmpEngineTime)) {
    $snmpEngineTime = 0;
  }

  if (is_numeric($uptime))
  {
    if ($uptime < $device['uptime'] && $uptime >= $snmpEngineTime)
    {
      notify($device,"Device rebooted: " . $device['hostname'],  "Device Rebooted : " . $device['hostname'] . " " . formatUptime($uptime) . " ago.");
      log_event('Device rebooted after '.formatUptime($device['uptime']), $device, 'reboot', $device['uptime']);
    }

    $uptime_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";

    if (!is_file($uptime_rrd))
    {
      rrdtool_create ($uptime_rrd, "DS:uptime:GAUGE:600:0:U ".$config['rrd_rra']);
    }
    rrdtool_update($uptime_rrd, "N:".$uptime);

    $graphs['uptime'] = TRUE;

    echo("Uptime: ".formatUptime($uptime)."\n");

    $update_array['uptime'] = $uptime;
  }

  $poll_device['sysLocation'] = str_replace("\"","", $poll_device['sysLocation']);

  /// Rewrite sysLocation if there is a mapping array (database too?)
  if (!empty($poll_device['sysLocation']) && is_array($config['location_map']))
  {
    $poll_device['sysLocation'] = rewrite_location($poll_device['sysLocation']);
  }

  $poll_device['sysContact']  = str_replace("\"","", $poll_device['sysContact']);

  if ($poll_device['sysLocation'] == "not set")
  {
    $poll_device['sysLocation'] = "";
  }

  if ($poll_device['sysContact'] == "not set")
  {
    $poll_device['sysContact'] = "";
  }

  if ($poll_device['sysContact'] && $poll_device['sysContact'] != $device['sysContact'])
  {
    $update_array['sysContact'] = $poll_device['sysContact'];
    log_event("Contact -> ".$poll_device['sysContact'], $device, 'system');
  }

  if ($poll_device['sysName'] && $poll_device['sysName'] != $device['sysName'])
  {
    $update_array['sysName'] = $poll_device['sysName'];
    log_event("sysName -> ".$poll_device['sysName'], $device, 'system');
  }

  if ($poll_device['sysDescr'] && $poll_device['sysDescr'] != $device['sysDescr'])
  {
    $update_array['sysDescr'] = $poll_device['sysDescr'];
    log_event("sysDescr -> ".$poll_device['sysDescr'], $device, 'system');
  }

  if ($poll_device['sysLocation'] && $device['location'] != $poll_device['sysLocation'])
  {
      if (!get_dev_attrib($device,'override_sysLocation_bool'))
      {
        $update_array['location'] = $poll_device['sysLocation'];
        log_event("Location -> ".$poll_device['sysLocation'], $device, 'system');
      }
  }

?>
