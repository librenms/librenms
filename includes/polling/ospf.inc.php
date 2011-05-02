<?php

echo("OSPF: ");
echo("Processes: ");

$ospf_instance_count  = 0;
$ospf_port_count      = 0;
$ospf_area_count      = 0;
$ospf_neighbour_count = 0;

$ospf_oids_db = array('ospfRouterId', 'ospfAdminStat', 'ospfVersionNumber', 'ospfAreaBdrRtrStatus', 'ospfASBdrRtrStatus',
                      'ospfExternLsaCount', 'ospfExternLsaCksumSum', 'ospfTOSSupport', 'ospfOriginateNewLsas', 'ospfRxNewLsas',
                      'ospfExtLsdbLimit', 'ospfMulticastExtensions', 'ospfExitOverflowInterval', 'ospfDemandExtensions');

### Build array of existing entries
$query = mysql_query("SELECT * FROM `ospf_instances` WHERE `device_id` = '".$device['device_id']."'");
while ($entry = mysql_fetch_assoc($query))
{
  $ospf_instances_db[$entry['ospf_instance_id']] = $entry;
}

### Pull data from device
$ospf_instances_poll = snmpwalk_cache_oid($device, "OSPF-MIB::ospfGeneralGroup", array(), "OSPF-MIB");
foreach ($ospf_instances_poll as $ospf_instance_id => $ospf_entry)
{
  ### If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
  if (!isset($ospf_instances_db[$ospf_instance_id]))
  {
    $query = "INSERT INTO `ospf_instances` (`device_id`, `ospf_instance_id`) VALUES ('".$device['device_id']."','".$ospf_instance_id."')";
    echo($query);
    mysql_query($query);
    echo(mysql_error());
    echo("+");
    $entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_instances` WHERE `device_id` = '".$device['device_id']."' AND `ospf_instance_id` = '".$ospf_instance_id."'"));
    $ospf_instances_db[$entry['ospf_instance_id']] = $entry;
  }
}

if ($debug) {
  echo("\nPolled: ");
  print_r($ospf_instances_poll);
  echo("Database: ");
  print_r($ospf_instances_db);
  echo("\n");
}

### Loop array of entries and update 
if (is_array($ospf_instances_db))
{ 
  foreach ($ospf_instances_db as $ospf_instance_db)
  {
    $ospf_instance_poll = $ospf_instances_poll[$ospf_instance_db['ospf_instance_id']];
    foreach ($ospf_oids_db as $oid)
    { // Loop the OIDs
      if ($ospf_instance_db[$oid] != $ospf_instance_poll[$oid])
      { // If data has changed, build a query
        $ospf_instance_update .= ", `$oid` = '".mres($ospf_instance_poll[$oid])."'";
        #log_event("$oid -> ".$this_port[$oid], $device, 'ospf', $port['interface_id']); ## FIXME
      }
    }
    if ($ospf_instance_update) 
    {
      $query = "UPDATE `ospf_instances` SET `ospf_instance_id` = '".$ospf_instance_db['ospf_instance_id']."'".$ospf_instance_update." WHERE `device_id` = '".$device['device_id']."' AND `ospf_instance_id` = '".$ospf_instance_id."'";
      if ($debug) {echo($query);} ## Debug
      mysql_query($query);
      if ($debug) {echo(mysql_error());} ## Debug
      echo("U");
      unset($ospf_instance_update);
    } else {
      echo(".");
    }
    unset($ospf_instance_poll);
    unset($ospf_instance_db);
    $ospf_instance_count++;
  }
}

unset($ospf_instances_poll);
unset($ospf_instances_db);

echo(" Areas: ");

$ospf_area_oids = array('ospfAuthType','ospfImportAsExtern','ospfSpfRuns','ospfAreaBdrRtrCount','ospfAsBdrRtrCount','ospfAreaLsaCount','ospfAreaLsaCksumSum','ospfAreaSummary','ospfAreaStatus');

### Build array of existing entries
$query = mysql_query("SELECT * FROM `ospf_areas` WHERE `device_id` = '".$device['device_id']."'");
while ($entry = mysql_fetch_assoc($query))
{
  $ospf_areas_db[$entry['ospfAreaId']] = $entry;
}

### Pull data from device
$ospf_areas_poll = snmpwalk_cache_oid($device, "OSPF-MIB::ospfAreaEntry", array(), "OSPF-MIB");

foreach ($ospf_areas_poll as $ospf_area_id => $ospf_area)
{
  ### If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
  if (!isset($ospf_areas_db[$ospf_area_id]))
  {
    mysql_query("INSERT INTO `ospf_areas` (`device_id`, `ospfAreaId`) VALUES ('".$device['device_id']."','".$ospf_area_id."') ");
    echo("+");
    $entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_areas` WHERE `device_id` = '".$device['device_id']."' AND `ospfAreaId` = '".$ospf_area_id."'"));
    $ospf_areas_db[$entry['ospf_area_id']] = $entry;
  }
}

if ($debug) {
  echo("\nPolled: ");
  print_r($ospf_areas_poll);
  echo("Database: ");
  print_r($ospf_areas_db);
  echo("\n");
}


### Loop array of entries and update
if (is_array($ospf_areas_db))
{ 
  foreach ($ospf_areas_db as $ospf_area_db)
  {
    $ospf_area_poll = $ospf_areas_poll[$ospf_area_db['ospfAreaId']];
    foreach ($ospf_area_oids as $oid)
    { ## Loop the OIDs
      if ($ospf_area_db[$oid] != $ospf_area_poll[$oid])
      { ## If data has changed, build a query
        $ospf_area_update .= ", `$oid` = '".mres($ospf_area_poll[$oid])."'";
        #log_event("$oid -> ".$this_port[$oid], $device, 'interface', $port['interface_id']); ## FIXME
      }
    }
    if ($ospf_area_update)
    {
      $sql = "UPDATE `ospf_areas` SET `ospfAreaId` = '".$ospf_area_db['ospfAreaId']."'".$ospf_area_update." WHERE `device_id` = '".$device['device_id']."' AND `ospfAreaId` = '".$ospf_area_id."'";
      mysql_query($sql);
      if ($debug) { echo("$sql\n"); }
      echo("U");
      unset($ospf_area_update);
    } else {
      echo(".");
    }
    unset($ospf_area_poll);
    unset($ospf_area_db);
    $ospf_area_count++;
  }
}

unset($ospf_areas_db);
unset($ospf_areas_poll);


#$ospf_ports = snmpwalk_cache_oid($device, "OSPF-MIB::ospfIfEntry", array(), "OSPF-MIB");
#print_r($ospf_ports);

echo(" Ports: ");

$ospf_port_oids = array('ospfIfIpAddress','interface_id','ospfAddressLessIf','ospfIfAreaId','ospfIfType','ospfIfAdminStat','ospfIfRtrPriority','ospfIfTransitDelay','ospfIfRetransInterval','ospfIfHelloInterval','ospfIfRtrDeadInterval','ospfIfPollInterval','ospfIfState','ospfIfDesignatedRouter','ospfIfBackupDesignatedRouter','ospfIfEvents','ospfIfAuthKey','ospfIfStatus','ospfIfMulticastForwarding','ospfIfDemand','ospfIfAuthType');

### Build array of existing entries
$query = mysql_query("SELECT * FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."'");
while ($entry = mysql_fetch_assoc($query))
{
  $ospf_ports_db[$entry['ospf_port_id']] = $entry;
}

### Pull data from device
$ospf_ports_poll = snmpwalk_cache_oid($device, "OSPF-MIB::ospfIfEntry", array(), "OSPF-MIB");

foreach ($ospf_ports_poll as $ospf_port_id => $ospf_port)
{
  ### If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
  if (!isset($ospf_ports_db[$ospf_port_id]))
  {
    mysql_query("INSERT INTO `ospf_ports` (`device_id`, `ospf_port_id`) VALUES ('".$device['device_id']."','".$ospf_port_id."') ");
    echo("+");
    $entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."' AND `ospf_port_id` = '".$ospf_port_id."'"));
    $ospf_ports_db[$entry['ospf_port_id']] = $entry;
  }
}

if ($debug)
{
  echo("\nPolled: ");
  print_r($ospf_ports_poll);
  echo("Database: ");
  print_r($ospf_ports_db);
  echo("\n");
}

### Loop array of entries and update
if (is_array($ospf_ports_db))
{
  foreach ($ospf_ports_db as $ospf_port_id => $ospf_port_db)
  {
    if (is_array($ospf_ports_poll[$ospf_port_db['ospf_port_id']]))
    {
      $ospf_port_poll = $ospf_ports_poll[$ospf_port_db['ospf_port_id']];

      if ($ospf_port_poll['ospfAddressLessIf']) 
      { 
        $ospf_port_poll['interface_id'] = @mysql_result(mysql_query("SELECT `interface_id` FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '".$ospf_port_poll['ospfAddressLessIf']."'"),0); 
      } else {
        $ospf_port_poll['interface_id'] = @mysql_result(mysql_query("SELECT A.`interface_id` FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = '".$ospf_port_poll['ospfIfIpAddress']."' AND I.interface_id = A.interface_id AND I.device_id = '".$device['device_id']."'"),0);        
      }

      foreach ($ospf_port_oids as $oid)
      { // Loop the OIDs
        if ($ospf_port_db[$oid] != $ospf_port_poll[$oid])
        { // If data has changed, build a query
          $ospf_port_update .= ", `$oid` = '".mres($ospf_port_poll[$oid])."'";
          #log_event("$oid -> ".$this_port[$oid], $device, 'ospf', $port['interface_id']); ## FIXME
        }
      }
      if ($ospf_port_update)
      {
        $query = "UPDATE `ospf_ports` SET `ospf_port_id` = '".$ospf_port_db['ospf_port_id']."'".$ospf_port_update." WHERE `device_id` = '".$device['device_id']."' AND `ospf_port_id` = '".$ospf_port_id."'";
        mysql_query($query);
        if ($debug) { echo("$query"); }
        echo("U");
        unset($ospf_port_update);
      } else {
        echo(".");
      }
      unset($ospf_port_poll);
      unset($ospf_port_db);
      $ospf_port_count++;
    } else {
      mysql_query("DELETE FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."' AND `ospf_port_id` = '".$ospf_port_db['ospf_port_id']."'");
      echo("-");
    }
  }
}

#OSPF-MIB::ospfNbrIpAddr.172.22.203.98.0 172.22.203.98
#OSPF-MIB::ospfNbrAddressLessIndex.172.22.203.98.0 0
#OSPF-MIB::ospfNbrRtrId.172.22.203.98.0 172.22.203.128
#OSPF-MIB::ospfNbrOptions.172.22.203.98.0 2
#OSPF-MIB::ospfNbrPriority.172.22.203.98.0 0
#OSPF-MIB::ospfNbrState.172.22.203.98.0 full
#OSPF-MIB::ospfNbrEvents.172.22.203.98.0 6
#OSPF-MIB::ospfNbrLsRetransQLen.172.22.203.98.0 1
#OSPF-MIB::ospfNbmaNbrStatus.172.22.203.98.0 active
#OSPF-MIB::ospfNbmaNbrPermanence.172.22.203.98.0 dynamic
#OSPF-MIB::ospfNbrHelloSuppressed.172.22.203.98.0 false

echo(' Neighbours: ');

$ospf_nbr_oids_db  = array('ospfNbrIpAddr', 'ospfNbrAddressLessIndex', 'ospfNbrRtrId', 'ospfNbrOptions', 'ospfNbrPriority', 'ospfNbrState', 'ospfNbrEvents', 'ospfNbrLsRetransQLen', 'ospfNbmaNbrStatus', 'ospfNbmaNbrPermanence', 'ospfNbrHelloSuppressed');
$ospf_nbr_oids_rrd = array();
$ospf_nbr_oids = array_merge($ospf_nbr_oids_db, $ospf_nbr_oids_rrd);

### Build array of existing entries
$nbr_query = mysql_query("SELECT * FROM `ospf_nbrs` WHERE `device_id` = '".$device['device_id']."'");
while ($nbr_entry = mysql_fetch_assoc($nbr_query))
{
  $ospf_nbrs_db[$nbr_entry['ospf_nbr_id']] = $nbr_entry;
}

### Pull data from device
$ospf_nbrs_poll = snmpwalk_cache_oid($device, "OSPF-MIB::ospfNbrEntry", array(), "OSPF-MIB");

foreach ($ospf_nbrs_poll as $ospf_nbr_id => $ospf_nbr)
{
  ### If the entry doesn't already exist in the prebuilt array, insert into the database and put into the array
  if (!isset($ospf_nbrs_db[$ospf_nbr_id]))
  {
    mysql_query("INSERT INTO `ospf_nbrs` (`device_id`, `ospf_nbr_id`) VALUES ('".$device['device_id']."','".$ospf_nbr_id."') ");
    echo("+");
    $entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_nbrs` WHERE `device_id` = '".$device['device_id']."' AND `ospf_nbr_id` = '".$ospf_nbr_id."'"));
    $ospf_nbrs_db[$entry['ospf_nbr_id']] = $entry;
  }
}

if ($debug)
{
  echo("\nPolled: ");
  print_r($ospf_nbrs_poll);
  echo("Database: ");
  print_r($ospf_nbrs_db);
  echo("\n");
}

### Loop array of entries and update
if (is_array($ospf_nbrs_db))
{
  foreach ($ospf_nbrs_db as $ospf_nbr_id => $ospf_nbr_db)
  {
    if (is_array($ospf_nbrs_poll[$ospf_nbr_db['ospf_nbr_id']]))
    {
      $ospf_nbr_poll = $ospf_nbrs_poll[$ospf_nbr_db['ospf_nbr_id']];

      $ospf_nbr_poll['interface_id'] = @mysql_result(mysql_query("SELECT A.`interface_id` FROM ipv4_addresses AS A, nbrs AS I WHERE A.ipv4_address = '".$ospf_nbr_poll['ospfNbrIpAddr']."' AND I.interface_id = A.interface_id AND I.device_id = '".$device['device_id']."'"),0);

      if ($ospf_nbr_db['interface_id'] != $ospf_nbr_poll['interface_id'])
      {
        $ospf_nbr_update = " ";
      }

      foreach ($ospf_nbr_oids as $oid)
      { // Loop the OIDs
        echo($ospf_nbr_db[$oid]."|".$ospf_nbr_poll[$oid]."\n");
        if ($ospf_nbr_db[$oid] != $ospf_nbr_poll[$oid])
        { // If data has changed, build a query
          $ospf_nbr_update .= ", `$oid` = '".mres($ospf_nbr_poll[$oid])."'";
          #log_event("$oid -> ".$this_nbr[$oid], $device, 'ospf', $nbr['interface_id']); ## FIXME
        }
      }
      if ($ospf_nbr_update)
      {
        $query = "UPDATE `ospf_nbrs` SET `interface_id` = '".$ospf_nbr_poll['interface_id']."'".$ospf_nbr_update." WHERE `device_id` = '".$device['device_id']."' AND `ospf_nbr_id` = '".$ospf_nbr_id."'";
        mysql_query($query);
        if ($debug) { echo("$query"); }
        echo("U");
        unset($ospf_nbr_update);
      } else {
        echo(".");
      }

      unset($ospf_nbr_poll);
      unset($ospf_nbr_db);
      $ospf_nbr_count++;
    } else {
      mysql_query("DELETE FROM `ospf_nbrs` WHERE `device_id` = '".$device['device_id']."' AND `ospf_nbr_id` = '".$ospf_nbr_db['ospf_nbr_id']."'");
      echo("-");
    }
  }
}

## Create device-wide statistics RRD

$filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("ospf-statistics.rrd");

if (!is_file($filename))
{
  rrdtool_create($filename, "--step 300 \
          DS:instances:GAUGE:600:0:1000000 \
          DS:areas:GAUGE:600:0:1000000 \
          DS:ports:GAUGE:600:0:1000000 \
          DS:neighbours:GAUGE:600:0:1000000 \
          RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 \
          RRA:MIN:0.5:1:600     RRA:MIN:0.5:6:700     RRA:MIN:0.5:24:775     RRA:MIN:0.5:288:797 \
          RRA:MAX:0.5:1:600     RRA:MAX:0.5:6:700     RRA:MAX:0.5:24:775     RRA:MAX:0.5:288:797 \
          RRA:LAST:0.5:1:600    RRA:LAST:0.5:6:700    RRA:LAST:0.5:24:775    RRA:LAST:0.5:288:797");
}

$rrd_update  = "N:".$ospf_instance_count.":".$ospf_area_count.":".$ospf_port_count.":".$ospf_neighbour_count;
$ret = rrdtool_update("$filename", $rrd_update);

unset($ospf_ports_db);
unset($ospf_ports_poll);

echo("\n");

?>
