#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "WHERE `interface_id` = '$argv[1]'"; }

$interface_query = mysql_query("SELECT * FROM `interfaces` $where ORDER BY interface_id DESC");
while ($interface = mysql_fetch_array($interface_query)) {

 unset($this);

 $device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE device_id = '" . $interface['device_id'] . "'"));
 if($device['status'] == '1') {

  unset($update);
  unset($update_query);
  unset($seperator);

  echo("Looking at " . $interface['ifDescr'] . " on " . $device['hostname'] . "\n");

  $snmp_cmd  = $config['snmpget'] . " -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ifName." . $interface['ifIndex'];
  $snmp_cmd .= " ifDescr." . $interface['ifIndex'] . " ifAdminStatus." . $interface['ifIndex'] . " ifOperStatus." . $interface['ifIndex'] . " ";
  $snmp_cmd .= "ifAlias." . $interface['ifIndex'] . " ifSpeed." . $interface['ifIndex'] . " 1.3.6.1.2.1.10.7.2.1." . $interface['ifIndex'];
  $snmp_cmd .= " ifType." . $interface['ifIndex'] . " ifMtu." . $interface['ifIndex'] . " ifPhysAddress." . $interface['ifIndex'];

  $snmp_output = trim(`$snmp_cmd`);
  $snmp_output = str_replace("No Such Object available on this agent at this OID", "", $snmp_output);
  $snmp_output = str_replace("No Such Instance currently exists at this OID", "", $snmp_output);
  $snmp_output = str_replace("\"", "", $snmp_output);

  if($device['os'] == "IOS") {

    $snmp_cmdb  = $config['snmpget'] . " -m +CISCO-VLAN-MEMBERSHIP-MIB:CISCO-VTP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
    $snmp_cmdb .= " .1.3.6.1.4.1.9.2.2.1.1.1." . $interface['ifIndex'];
    $snmp_cmdb .= " .1.3.6.1.4.1.9.9.68.1.2.2.1.2." . $interface['ifIndex'];
    $snmp_cmdb .= " .1.3.6.1.4.1.9.9.46.1.6.1.1.16." . $interface['ifIndex'];

    $snmp_outputb = trim(`$snmp_cmdb`);
    $snmp_outputb = str_replace("No Such Object available on this agent at this OID", "", $snmp_outputb);
    $snmp_outputb = str_replace("No Such Instance currently exists at this OID", "", $snmp_outputb);
    $snmp_outputb = str_replace("\"", "", $snmp_outputb);

    list($this['ifHardType'], $this['ifVlan'], $this['ifTrunk']) = explode("\n", $snmp_outputb);
    if($this['ifTrunk'] == "notApplicable" || $this['ifTrunk'] == "6") { unset($this['ifTrunk']); }
    if($this['ifVlan'] == "") { unset($this['ifVlan']); }

    if ( $interface['ifTrunk'] != $this['ifTrunk'] ) {
       $update .= $seperator . "`ifTrunk` = '" . $this['ifTrunk'] . "'";
       $seperator = ", ";
       mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'VLAN Trunk -> " . $this['ifTrunk'] . "')");
    }


    if ( $interface['ifVlan'] != $this['ifVlan']) {
       $update .= $seperator . "`ifVlan` = '" . $this['ifVlan'] . "'";
       echo($update);
       $seperator = ", ";
       mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'VLAN Vlan -> " . $this['ifVlan'] . "')");
    }

    if($this['ifTrunk']) { echo("Interface is a " . $this['ifTrunk'] . " trunk\n"); }
    if($this['ifVlan'])   { echo("Interface is a member of vlan " . $this['ifVlan'] . " \n"); }

  }

  list($ifName, $ifDescr, $ifAdminStatus, $ifOperStatus, $ifAlias, $ifSpeed, $ifDuplex, $ifType, $ifMtu, $ifPhysAddress) = explode("\n", $snmp_output);
  $ifDescr = trim(str_replace("\"", "", $ifDescr));
  if ($ifDuplex == 3) { $ifDuplex = "half"; } elseif ($ifDuplex == 2) { $ifDuplex = "full"; } else { $ifDuplex = "unknown"; }
  $ifDescr = strtolower($ifDescr);
  if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
  $ifAlias = trim(str_replace("\"", "", $ifAlias));
  $ifAlias = trim($ifAlias);

  echo("\n$ifName\n");
  $ifDescr = fixifname($ifDescr);

  $ifPhysAddress = strtolower(str_replace("\"", "", $ifPhysAddress));
  $ifPhysAddress = str_replace(" ", ":", $ifPhysAddress);

  if ( $interface['ifDescr'] != $ifDescr && $ifDescr != "" ) {
     $update .= $seperator . "`ifDescr` = '$ifDescr'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Name -> " . $ifDescr . "')");
  }
  if ( $interface['ifAlias'] != $ifAlias ) {
     $update .= $seperator . "`ifAlias` = '$ifAlias'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Desc  -> $ifAlias')");
  }
  if ( $interface['ifOperStatus'] != $ifOperStatus && $ifOperStatus != "" ) {
     $update .= $seperator . "`ifOperStatus` = '$ifOperStatus'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" . $interface['interface_id'] . "', NOW(), 'Interface went $ifOperStatus')");
  }
  if ( $interface['ifAdminStatus'] != $ifAdminStatus && $ifAdminStatus != "" ) {
     $update .= $seperator . "`ifAdminStatus` = '$ifAdminStatus'";
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

  if ( $interface['ifHardType'] != $this['ifHardType']) {
     $update .= $seperator . "`ifHardType` = '" . $this['ifHardType'] . "'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (`host`, `interface`, `datetime`, `message`) VALUES ('" . $interface['device_id'] . "', '" .$interface['interface_id'] . "', NOW(), 'HW Type -> " . $this['ifHardType']. "')");
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
     $update_query .= " WHERE `interface_id` = '" . $interface['interface_id'] . "'";
     $update_result = mysql_query($update_query);
  } else {
  }

 }
}

mysql_query("UPDATE interfaces set ifPhysAddress = '' WHERE ifPhysAddress = 'No Such Instance currently exists at this OID'");

?>

