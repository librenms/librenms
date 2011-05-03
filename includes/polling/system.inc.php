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
  foreach (array_keys($snmpdata[0]) as $key) { $poll_device[$key] = $snmpdata[0][$key]; }

  $poll_device['sysDescr'] = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB");

  $poll_device['sysName'] = strtolower($poll_device['sysName']);

  $hrSystemUptime = snmp_get($device, "hrSystemUptime.0", "-Oqv", "HOST-RESOURCES-MIB");
  $sysObjectID = snmp_get($device, "sysObjectID.0", "-Oqvn");

  if ($hrSystemUptime != "" && !strpos($hrSystemUptime, "No") && ($device['os'] != "windows"))
  {
  echo("Using hrSystemUptime\n");
  $agent_uptime = $poll_device['uptime']; ## Move uptime into agent_uptime
    #HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
    $hrSystemUptime = str_replace("(", "", $hrSystemUptime);
    $hrSystemUptime = str_replace(")", "", $hrSystemUptime);
    list($days,$hours, $mins, $secs) = explode(":", $hrSystemUptime);
    list($secs, $microsecs) = explode(".", $secs);
    $hours = $hours + ($days * 24);
    $mins = $mins + ($hours * 60);
    $secs = $secs + ($mins * 60);
    $poll_device['uptime'] = $secs;
  } else {
    echo("Using Agent Uptime\n");
    #SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
    $sysUpTime = str_replace("(", "", $sysUpTime);
    $sysUpTime = str_replace(")", "", $sysUpTime);
    list($days, $hours, $mins, $secs) = explode(":", $sysUpTime);
    list($secs, $microsecs) = explode(".", $secs);
    $hours = $hours + ($days * 24);
    $mins = $mins + ($hours * 60);
    $secs = $secs + ($mins * 60);
    $poll_device['uptime'] = $secs;
  }

  if (is_numeric($poll_device['uptime']))
  {
    if ($poll_device['uptime'] < $device['uptime'])
    {
      notify($device,"Device rebooted: " . $device['hostname'],  "Device Rebooted : " . $device['hostname'] . " " . formatUptime($poll_device['uptime']) . " ago.");
      log_event('Device rebooted after '.formatUptime($device['uptime']), $device, 'reboot', $device['uptime']);
    }

    $uptime_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/uptime.rrd";

    if (!is_file($uptime_rrd))
    {
      rrdtool_create ($uptime_rrd, "DS:uptime:GAUGE:600:0:U RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797");
    }
    rrdtool_update($uptime_rrd, "N:".$poll_device['uptime']);

    $graphs['uptime'] = TRUE;

    echo("Uptime: ".formatUptime($poll_device['uptime'])."\n");

    $device['db_update'] .= ", `uptime` = '".mres($poll_device['uptime'])."'";
  }

  echo("Hardware: ".$poll_device['hardware']." Version: ".$poll_device['version']." Features: ".$poll_device['features']."\n");

  $poll_device['sysLocation'] = str_replace("\"","", $poll_device['sysLocation']);
  $poll_device['sysContact']  = str_replace("\"","", $poll_device['sysContact']);

  if ($poll_device['serial'] && $poll_device['serial'] != $device['serial'])
  {
    $device['db_update'] .= ", `serial` = '".mres($poll_device['serial'])."'";
    log_event("Serial -> ".$poll_device['serial'], $device, 'system');
  }

  if ($poll_device['sysContact'] && $poll_device['sysContact'] != $device['sysContact'])
  {
    $device['db_update'] .= ", `sysContact` = '".mres($poll_device['sysContact'])."'";
    log_event("Contact -> ".$poll_device['sysContact'], $device, 'system');
  }

  if ($poll_device['sysName'] && $poll_device['sysName'] != $device['sysName'])
  {
    $device['db_update'] .= ", `sysName` = '".mres($poll_device['sysName'])."'";
    log_event("sysName -> ".$poll_device['sysName'], $device, 'system');
  }

  if ($poll_device['sysDescr'] && $poll_device['sysDescr'] != $device['sysDescr'])
  {
    $device['db_update'] .= ", `sysDescr` = '".mres($poll_device['sysDescr'])."'";
    log_event("sysDescr -> ".$poll_device['sysDescr'], $device, 'system');
  }

  if ($poll_device['sysLocation'] && $device['location'] != $poll_device['sysLocation'])
  {
      if (!get_dev_attrib($device,'override_sysLocation_bool'))
      {
      $device['db_update'] .= ", `location` = '".mres($poll_device['sysLocation'])."'";
      }
      log_event("Location -> ".$poll_device['sysLocation'], $device, 'system');
  }

  if ($poll_device['version'] && $device['version'] != $poll_device['version'])
  {
    $device['db_update'] .= ", `version` = '".mres($poll_device['version'])."'";
    log_event("OS Version -> ".$poll_device['version'], $device, 'system');
  }

  if ($poll_device['features'] != $device['features'])
  {
    $device['db_update'] .= ", `features` = '".mres($poll_device['features'])."'";
    log_event("OS Features -> ".$poll_device['features'], $device, 'system');
  }

  if ($poll_device['hardware'] && $poll_device['hardware'] != $device['hardware'])
  {
    $device['db_update'] .= ", `hardware` = '".mres($poll_device['hardware'])."'";
    log_event("Hardware -> ".$poll_device['hardware'], $device, 'system');
  }

?>
