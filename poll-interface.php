#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$interface_query = mysql_query("SELECT * FROM `interfaces`");
while ($interface = mysql_fetch_array($interface_query)) {

 $device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE id = '" . $interface['device_id'] . "'"));
 if($device['status'] == '1') {

  $snmp_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " ifName." . $interface['ifIndex'];
  $snmp_cmd .= " ifDescr." . $interface['ifIndex'] . " ifAdminStatus." . $interface['ifIndex'] . " ifOperStatus." . $interface['ifIndex'] . " ";
  $snmp_cmd .= "ifAlias." . $interface['ifIndex'] . " ifSpeed." . $interface['ifIndex'] . " 1.3.6.1.2.1.10.7.2.1." . $interface['ifIndex'];
  $snmp_cmd .= " ifType." . $interface['ifIndex'] . " ifMtu." . $interface['ifIndex'] . " ifPhysAddress." . $interface['ifIndex'];

  echo($snmp_cmd);

  $snmp_output = trim(`$snmp_cmd`);
  $snmp_output = str_replace("No Such Object available on this agent at this OID", "", $snmp_output);
  $snmp_output = str_replace("No Such Instance currently exists at this OID", "", $snmp_output);

  echo("Looking at " . $interface['ifDescr'] . " on " . $device['hostname'] . "\n");
  list($ifName, $ifDescr, $ifAdminStatus, $ifOperStatus, $ifAlias, $ifSpeed, $ifDuplex, $ifType, $ifMtu, $ifPhysAddress) = explode("\n", $snmp_output);
  $ifDescr = trim(str_replace("\"", "", $ifDescr));
  if ($ifDuplex == 3) { $ifDuplex = "half"; } elseif ($ifDuplex == 2) { $ifDuplex = "full"; } else { $ifDuplex = "unknown"; }
  $ifDescr = strtolower($ifDescr);
  if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
  $ifAlias = trim(str_replace("\"", "", $ifAlias));
  $ifAlias = trim($ifAlias);

  $ifPhysAddress = strtolower(str_replace("\"", "", $ifPhysAddress));
  $ifPhysAddress = str_replace(" ", ":", $ifPhysAddress);

  if($device['os'] == "IOS") {
    $locIfHardType_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " 1.3.6.1.4.1.9.2.2.1.1.1." . $interface['ifIndex'];
    $locIfHardType = trim(str_replace("\"", "", `$locIfHardType_cmd`));
  }

  $rrdfile = "rrd/" . $device['hostname'] . "." . $interface['ifIndex'] . ".rrd";
  if(!is_file($rrdfile)) {
    $woo = `rrdtool create $rrdfile \
      DS:INOCTETS:COUNTER:600:U:100000000000 \
      DS:OUTOCTETS:COUNTER:600:U:10000000000 \
      DS:INERRORS:COUNTER:600:U:10000000000 \
      DS:OUTERRORS:COUNTER:600:U:10000000000 \
      DS:INUCASTPKTS:COUNTER:600:U:10000000000 \
      DS:OUTUCASTPKTS:COUNTER:600:U:10000000000 \
      DS:INNUCASTPKTS:COUNTER:600:U:10000000000 \
      DS:OUTNUCASTPKTS:COUNTER:600:U:10000000000 \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797 \
      RRA:MAX:0.5:1:600 \
      RRA:MAX:0.5:6:700 \
      RRA:MAX:0.5:24:775 \
      RRA:MAX:0.5:288:797`;
  }

  unset($update);
  unset($update_query);
  unset($seperator);

  if ( $interface['ifDescr'] != $ifDescr && $ifDescr != "" ) {
     $update = "`if` = '$ifDescr'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Name -> " . $ifDescr . "')");
  }
  if ( $interface['ifAlias'] != $ifAlias ) {
     $update .= $seperator . "`name` = '$ifAlias'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Desc  -> $ifAlias')");
  }
  if ( $interface['ifOperStatus'] != $ifOperStatus && $ifOperStatus != "" ) {
     $update .= $seperator . "`up` = '$ifOperStatus'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Interface went $ifOperStatus')");
  }
  if ( $interface['ifAdminStatus'] != $ifAdminStatus && $ifAdminStatus != "" ) {
     $update .= $seperator . "`up_admin` = '$ifAdminStatus'";
     $seperator = ", ";
     if($ifAdminStatus == "up") { $admin = "enabled"; } else { $admin = "disabled"; }
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Interface $admin')");
  }
  if ( $interface['ifDuplex'] != $ifDuplex && $ifDuplex != "" ) {
     $update .= $seperator . "`ifDuplex` = '$ifDuplex'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Duplex -> $ifDuplex')");
  }
  if ( $interface['ifType'] != $ifType && $ifType != "" ) {
     $update .= $seperator . "`ifType` = '$ifType'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Type -> $ifType')");
  }
  if ( $interface['ifMtu'] != $ifMtu && $ifMtu != "" ) {
     $update .= $seperator . "`ifMtu` = '$ifMtu'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'MTU -> $ifMtu')");
  }
  if ( $interface['ifPhysAddress'] != $ifPhysAddress && $ifPhysAddress != "" ) {
     $update .= $seperator . "`ifPhysAddress` = '$ifPhysAddress'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'MAC -> $ifPhysAddress')");
  }

  if ( $interface['ifSpeed'] != $ifSpeed && $ifSpeed != "" ) {
     $update .= $seperator . "`ifSpeed` = '$ifSpeed'";
     $seperator = ", ";
     $prev = humanspeed($interface['ifSpeed']);
     $now = humanspeed($ifSpeed);
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Speed -> $now')");
  }

  if ($update) {
     $update_query  = "UPDATE `interfaces` SET ";
     $update_query .= $update;
     $update_query .= " WHERE `id` = '" . $interface['interface_id'] . "'";
     echo("Updating : " . $device['hostname'] . " $ifDescr\nSQL :$update_query\n\n");
     $update_result = mysql_query($update_query);
  } else {
     echo("Not Updating : " . $device['hostname'] ." $ifDescr ( " . $interface['ifDescr'] . " )\n\n");
  }

  if($ifOperStatus == "up") {
    $snmp_data_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
    $snmp_data_cmd .= " ifHCInOctets." . $interface['ifIndex'] . " ifHCOutOctets." . $interface['ifIndex'] . " ifInErrors." . $interface['ifIndex'];
    $snmp_data_cmd .= " ifOutErrors." . $interface['ifIndex'] . " ifInUcastPkts." . $interface['ifIndex'] . " ifOutUcastPkts." . $interface['ifIndex'];
    $snmp_data_cmd .= " ifInNUcastPkts." . $interface['ifIndex'] . " ifOutNUcastPkts." . $interface['ifIndex'];

    $snmp_data = `$snmp_data_cmd`;

    $snmp_data = str_replace("Wrong Type (should be Counter32): ","", $snmp_data);
    $snmp_data = str_replace("No Such Instance currently exists at this OID","", $snmp_data);
    list($ifHCInOctets, $ifHCOutOctets, $ifInErrors, $ifOutErrors, $ifInUcastPkts, $ifOutUcastPkts, $ifInNUcastPkts, $ifOutNUcastPkts) = explode("\n", $snmp_data);
    if($ifHCInOctets == "" || strpos($ifHCInOctets, "No") !== FALSE ) {
      
      $octets_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
      $octets_cmd .= " ifInOctets." . $interface['ifIndex'] . " ifOutOctets." . $interface['ifIndex']; 
      $octets = `$octets_cmd`;
      list ($ifHCInOctets, $ifHCOutOctets) = explode("\n", $octets);
    }
     $woo = "N:$ifHCInOctets:$ifHCOutOctets:$ifInErrors:$ifOutErrors:$ifInUcastPkts:$ifOutUcastPkts:$ifInNUcastPkts:$ifOutNUcastPkts";
     $ret = rrd_update("$rrdfile", $woo);
   } else {
     echo("Interface " . $device['hostname'] . " " . $interface['ifDescr'] . " is down\n");
  }
 }
}

mysql_query("UPDATE interfaces set ifPhysAddress = '' WHERE ifPhysAddress = 'No Such Instance currently exists at this OID'");

?>


