<?php

include('../includes/console_colour.php');
include('../includes/console_table.php');
include("../includes/defaults.inc.php");
include("../config.php");
include_once("../includes/definitions.inc.php");
include("../includes/functions.php");
include("../html/includes/functions.inc.php");

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

  $devices['up']        = dbFetchCell("SELECT COUNT(*) FROM devices  WHERE status = '1' AND `ignore` = '0'  AND `disabled` = '0'");
  $devices['down']      = dbFetchCell("SELECT COUNT(*) FROM devices WHERE status = '0' AND `ignore` = '0'  AND `disabled` = '0'");
  $devices['ignored']   = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `ignore` = '1'  AND `disabled` = '0'");
  $devices['disabled']  = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `disabled` = '1'");

  $ports['count']       = dbFetchCell("SELECT COUNT(*) FROM ports WHERE deleted = '0'");
  $ports['up']          = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.deleted = '0' AND I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'");
  $ports['down']        = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.deleted = '0' AND I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'");
  $ports['shutdown']    = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.deleted = '0' AND I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'
");
  $ports['ignored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.deleted = '0' AND D.device_id = I.device_id AND (I.ignore = '1' OR D.ignore = '1')");
  $ports['errored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.deleted = '0' AND D.device_id = I.device_id AND (I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')");

  $services['count']    = dbFetchCell("SELECT COUNT(service_id) FROM services");
  $services['up']       = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_status = '1' AND service_ignore ='0'");
  $services['down']     = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'");
  $services['ignored']  = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_ignore = '1'");
  $services['disabled'] = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_disabled = '1'");

  $tbl->addRow(array('Devices ('.$devices['count'].')',Console_Color::convert("%g".$devices['up']." Up%n"),Console_Color::convert("%r".$devices['down']." Down%n"),Console_Color::convert("%y".$devices['ignored']." Ignored%n"),Console_Color::convert("%p".$devices['disabled']." Disabled%n")));
  $tbl->addRow(array('Ports ('.$ports['count'].')',Console_Color::convert("%g".$ports['up']." Up%n"),Console_Color::convert("%r".$ports['down']." Down%n"),Console_Color::convert("%y".$ports['ignored']." Ignored%n"),Console_Color::convert("%p".$ports['shutdown']." Shutdown%n")));
  $tbl->addRow(array('Services ('.$services['count'].')',Console_Color::convert("%g".$services['up']." Up%n"),Console_Color::convert("%r".$services['down']." Down%n"),Console_Color::convert("%y".$services['ignored']." Ignored%n"),Console_Color::convert("%p".$services['disabled']." Shutdown%n")));

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
  echo(Console_Color::convert("%rLast update at ". date("Y-m-d h:i:s")."%n\n\n"));
  sleep(5);
}

?>
