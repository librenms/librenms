<?php

/* FIXME: dead file */

if ($device['os_group'] == "ios") {
  $portifIndex = array();
  $cmd = ($device['snmpver'] == 'v1' ? $config['snmpwalk'] : $config['snmpbulkwalk']) . " -M ".$config['mibdir']. " -CI -m CISCO-STACK-MIB -O q -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portIfIndex";
  #echo("$cmd");
  $portifIndex_output = trim(shell_exec($cmd));
  foreach (explode("\n", $portifIndex_output) as $entry){
    $entry = str_replace("CISCO-STACK-MIB::portIfIndex.", "", $entry);
    list($slotport, $ifIndex) = explode(" ", $entry);
    $portifIndex[$ifIndex] = $slotport;
  }
  if ($debug) { print_r($portifIndex); }
}

$interface_query = mysql_query("SELECT * FROM `ports` $where");
while ($interface = mysql_fetch_assoc($interface_query)) {

 if (!$device) { $device = mysql_fetch_assoc(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '" . $interface['device_id'] . "'")); }

 unset($ifAdminStatus, $ifOperStatus, $ifAlias, $ifDescr);

 $interface['hostname'] = $device['hostname'];
 $interface['device_id'] = $device['device_id'];

 if ($device['status'] == '1') {

   unset($update);
   unset($update_query);
   unset($seperator);

   echo("Looking at " . $interface['ifDescr'] . " on " . $device['hostname'] . "\n");

   $snmp_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m IF-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
   $snmp_cmd .= " ifAdminStatus." . $interface['ifIndex'] . " ifOperStatus." . $interface['ifIndex'] . " ifAlias." . $interface['ifIndex'] . " ifName." . $interface['ifIndex'];
   $snmp_cmd .= " ifDescr." . $interface['ifIndex'];

   $snmp_output = trim(shell_exec($snmp_cmd));
   $snmp_output = str_replace("No Such Object available on this agent at this OID", "", $snmp_output);
   $snmp_output = str_replace("No Such Instance currently exists at this OID", "", $snmp_output);
   $snmp_output = str_replace("\"", "", $snmp_output);

   list($ifAdminStatus, $ifOperStatus, $ifAlias, $ifName, $ifDescr) = explode("\n", $snmp_output);

   $ifAdminStatus = translate_ifAdminStatus ($ifAdminStatus);
   $ifOperStatus = translate_ifOperStatus ($ifOperStatus);

   if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
   $ifAlias = trim(str_replace("\"", "", $ifAlias));
   $ifAlias = trim($ifAlias);
   $ifDescr = trim(str_replace("\"", "", $ifDescr));
   $ifDescr = trim($ifDescr);

   $ifIndex = $interface['ifIndex'];
   if ($portifIndex[$ifIndex]) {
     if ($device['os'] == "CatOS") {
       $cmd = $config['snmpget'] . " -M ".$config['mibdir'] . " -m CISCO-STACK-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " portName." . $portifIndex[$ifIndex];
       $ifAlias = trim(shell_exec($cmd));
     }
   }

   if ($config['os'][$device[os]]['ifname']) { $ifDescr = $ifName; }

   $rrdfile = $host_rrd . "/" . safename($interface['ifIndex'] . ".rrd");

   if (!is_file($rrdfile)) {
     rrdtool_create($rrdfile,"DS:INOCTETS:COUNTER:600:0:12500000000 \
      DS:OUTOCTETS:COUNTER:600:0:12500000000 \
      DS:INERRORS:COUNTER:600:0:12500000000 \
      DS:OUTERRORS:COUNTER:600:0:12500000000 \
      DS:INUCASTPKTS:COUNTER:600:0:12500000000 \
      DS:OUTUCASTPKTS:COUNTER:600:0:12500000000 \
      DS:INNUCASTPKTS:COUNTER:600:0:12500000000 \
      DS:OUTNUCASTPKTS:COUNTER:600:0:12500000000 \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797 \
      RRA:MAX:0.5:1:600 \
      RRA:MAX:0.5:6:700 \
      RRA:MAX:0.5:24:775 \
      RRA:MAX:0.5:288:797");
   }

   if (file_exists("includes/polling/interface-" . $device['os'] . ".php") ) { include("includes/polling/interface-" . $device['os'] . ".php"); }

   if ($interface['ifDescr'] != $ifDescr && $ifDescr != "" ) {
     $update .= $seperator . "`ifDescr` = '$ifDescr'";
     $seperator = ", ";
     eventlog("ifDescr -> $ifDescr", $interface['device_id'], $interface['interface_id']);
   }

   if ($interface['ifName'] != $ifName && $ifName != "" ) {
     $update .= $seperator . "`ifName` = '$ifName'";
     $seperator = ", ";
     eventlog("ifName -> $ifName", $interface['device_id'], $interface['interface_id']);
   }

   if ($interface['ifAlias'] != $ifAlias && $ifAlias != "" ) {
     $update .= $seperator . "`ifAlias` = '".mres($ifAlias)."'";
     $seperator = ", ";
     eventlog("ifAlias -> $ifAlias", $interface['device_id'], $interface['interface_id']);
   }
   if ($interface['ifOperStatus'] != $ifOperStatus && $ifOperStatus != "" ) {
     $update .= $seperator . "`ifOperStatus` = '$ifOperStatus'";
     $seperator = ", ";
     eventlog("Interface went $ifOperStatus", $interface['device_id'], $interface['interface_id']);
   }
   if ($interface['ifAdminStatus'] != $ifAdminStatus && $ifAdminStatus != "" ) {
     $update .= $seperator . "`ifAdminStatus` = '$ifAdminStatus'";
     $seperator = ", ";
     if ($ifAdminStatus == "up") { $admin = "enabled"; } else { $admin = "disabled"; }
     eventlog("Interface $admin", $interface['device_id'], $interface['interface_id']);
   }

   if ($update) {
     $update_query  = "UPDATE `ports` SET ";
     $update_query .= $update;
     $update_query .= " WHERE `interface_id` = '" . $interface['interface_id'] . "'";
     #echo("Updating : " . $device['hostname'] . " $ifDescr\nSQL :$update_query\n\n");
     $update_result = mysql_query($update_query);
   } else {
#     echo("Not Updating : " . $device['hostname'] ." $ifDescr ( " . $interface['ifDescr'] . " )\n\n");
   }

   if ($ifOperStatus == "up") {

    $snmp_data_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m IF-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
    $snmp_data_cmd .= " ifHCInOctets." . $interface['ifIndex'] . " ifHCOutOctets." . $interface['ifIndex'] . " ifInErrors." . $interface['ifIndex'];
    $snmp_data_cmd .= " ifOutErrors." . $interface['ifIndex'] . " ifInUcastPkts." . $interface['ifIndex'] . " ifOutUcastPkts." . $interface['ifIndex'];
    $snmp_data_cmd .= " ifInNUcastPkts." . $interface['ifIndex'] . " ifOutNUcastPkts." . $interface['ifIndex'];

    $snmp_data = shell_exec($snmp_data_cmd);

    $snmp_data = str_replace("Wrong Type (should be Counter32): ","", $snmp_data);
    $snmp_data = str_replace("No Such Instance currently exists at this OID","", $snmp_data);
    list($ifHCInOctets, $ifHCOutOctets, $ifInErrors, $ifOutErrors, $ifInUcastPkts, $ifOutUcastPkts, $ifInNUcastPkts, $ifOutNUcastPkts) = explode("\n", $snmp_data);
    if ($ifHCInOctets == "" || strpos($ifHCInOctets, "No") !== FALSE ) {

      $octets_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m IF-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
      $octets_cmd .= " ifInOctets." . $interface['ifIndex'] . " ifOutOctets." . $interface['ifIndex'];
      $octets = shell_exec($octets_cmd);
      list ($ifHCInOctets, $ifHCOutOctets) = explode("\n", $octets);
    }
     $woo = "N:$ifHCInOctets:$ifHCOutOctets:$ifInErrors:$ifOutErrors:$ifInUcastPkts:$ifOutUcastPkts:$ifInNUcastPkts:$ifOutNUcastPkts";
     $ret = rrdtool_update("$rrdfile", $woo);

   } else {
     #echo("Interface " . $device['hostname'] . " " . $interface['ifDescr'] . " is down\n");
  }
 }

  $rates = interface_rates ($rrdfile);
  mysql_query("UPDATE `ports` SET in_rate = '" . $rates['in'] . "', out_rate = '" . $rates['out'] . "' WHERE interface_id= '" . $interface['interface_id'] . "'");

}

unset($portifIndex);

?>