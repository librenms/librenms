#!/usr/bin/env php
<?php

include('../includes/console_colour.php');
include('../includes/console_table.php');
include("../includes/defaults.inc.php");
include("../config.php");
include_once("../includes/definitions.inc.php");
include("../includes/functions.php");
include("../html/includes/functions.inc.php");

$console_color = new Console_Color2();

$long_opts = array('list::','device-stats');
$options = getopt("l:d:",$long_opts);

$end = 0;
while($end == 0)
{
  passthru('clear');
  $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_RIGHT);
  foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
  {
    if (get_dev_attrib($device,'override_sysLocation_bool'))
    {
      $device['real_location'] = $device['location'];
      $device['location'] = get_dev_attrib($device,'override_sysLocation_string');
    }

    $devices['count']++;

    $cache['devices']['hostname'][$device['hostname']] = $device['device_id'];
    $cache['devices']['id'][$device['device_id']] = $device;

    $cache['device_types'][$device['type']]++;
  }

  // Include the required SQL queries to get our data
  require('../includes/db/status_count.inc.php');

  $tbl->addRow(array('Devices ('.$devices['count'].')',print $console_color->convert("%g".$devices['up']." Up%n"),print $console_color->convert("%r".$devices['down']." Down%n"),print $console_color->convert("%y".$devices['ignored']." Ignored%n"),print $console_color->convert("%p".$devices['disabled']." Disabled%n")));
  $tbl->addRow(array('Ports ('.$ports['count'].')',print $console_color->convert("%g".$ports['up']." Up%n"),print $console_color->convert("%r".$ports['down']." Down%n"),print $console_color->convert("%y".$ports['ignored']." Ignored%n"),print $console_color->convert("%p".$ports['shutdown']." Shutdown%n")));
  $tbl->addRow(array('Services ('.$services['count'].')',print $console_color->convert("%g".$services['up']." Up%n"),print $console_color->convert("%r".$services['down']." Down%n"),print $console_color->convert("%y".$services['ignored']." Ignored%n"),print $console_color->convert("%p".$services['disabled']." Shutdown%n")));

  echo $tbl->getTable();

  if($options['l'] == 'eventlog')
  {
    $tbl = new Console_Table();
    $tbl->setHeaders(array('Date time','Host','Message','Type','Reference'));
    if(is_numeric($options['d']))
    {
      $sql = "WHERE host='".$options['d']."'";
    }
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` AS E $sql ORDER BY `datetime` DESC LIMIT 20";
    foreach (dbFetchRows($query, $param) as $entry)
    {
      $tbl->addRow(array($entry['datetime'],gethostbyid($entry['host']),$entry['message'],$entry['type'],$entry['reference']));
    }
    echo $tbl->getTable();
  }
  elseif($options['l'] == 'syslog')
  {
    $tbl = new Console_Table();
    $tbl->setHeaders(array('Date time','Host','Program','Message','Level','Facility'));
    if(is_numeric($options['d']))
    {
      $sql = "WHERE device_id='".$options['d']."'";
    }
    $query = "SELECT *, DATE_FORMAT(timestamp, '%Y-%m-%d %T') AS date from syslog AS S $sql_query ORDER BY `timestamp` DESC LIMIT 20";
    foreach (dbFetchRows($query, $param) as $entry)
    {
      $tbl->addRow(array($entry['timestamp'],gethostbyid($entry['device_id']),$entry['program'],$entry['msg'],$entry['level'],$entry['facility']));
    }
    echo $tbl->getTable();
  }
  elseif($options['list'] == 'devices')
  {

    $tbl = new Console_Table();
    $tbl->setHeaders(array('Device ID','Device Hostname'));
    $query = "SELECT device_id,hostname FROM `devices` ORDER BY hostname";
    foreach (dbFetchRows($query, $sql_param) as $device)
    {
      $tbl->addRow(array($device['device_id'],$device['hostname']));
    }
    echo $tbl->getTable();
    exit;
  }
  elseif(isset($options['device-stats']))
  {
    $tbl = new  Console_Table();
    $tbl->setHeaders(array('Port name','Status','IPv4 Address','Speed In','Speed Out','Packets In','Packets Out','Speed','Duplex','Type','MAC Address','MTU'));
    foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($options['d'])) as $port)
    {
      if($port['ifOperStatus'] == 'up')
      {
        $port['in_rate'] = $port['ifInOctets_rate'] * 8;
        $port['out_rate'] = $port['ifOutOctets_rate'] * 8;
        $in_perc = @round($port['in_rate']/$port['ifSpeed']*100);
        $out_perc = @round($port['in_rate']/$port['ifSpeed']*100);
      }
      if ($port['ifSpeed']) { $port_speed = humanspeed($port['ifSpeed']); }
      if ($port[ifDuplex] != "unknown") { $port_duplex = $port['ifDuplex']; }
      if ($port['ifPhysAddress'] && $port['ifPhysAddress'] != "") { $port_mac = formatMac($port['ifPhysAddress']); }
      if ($port['ifMtu'] && $port['ifMtu'] != "") { $port_mtu = $port['ifMtu']; }
      $tbl->addRow(array($port['ifDescr'],$port['ifOperStatus'],'',formatRates($port['in_rate']),formatRates($port['out_rate']),format_bi($port['ifInUcastPkts_rate']).'pps',format_bi($port['ifOutUcastPkts_rate']).'pps',$port_speed,$port_duplex,'',$port_mac,$port_mtu));
    }
    echo $tbl->getTable();
  }
  else
  {
    echo $options['list'];
    echo("Usage of console-ui.php:

  -l      What log type we want to see:
            eventlog = Event log messages
            syslog = Syslog messages

  -d      Specify the device id to filter results

  --list   What to list
            devices = list devices and device id's

  --device-stats      Lists the port statistics for a given device

  Examples:
           #1 php console-ui.php -l eventlog -d 1
           #2 php console-ui.php --list=devices

");
  exit;
  }
  echo(print $console_color->convert("%rLast update at ". date("Y-m-d h:i:s")."%n\n\n"));
  sleep(5);
}

?>
