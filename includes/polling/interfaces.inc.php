<?

$interface_query = mysql_query("SELECT * FROM `interfaces` $where");
while ($interface = mysql_fetch_array($interface_query)) {

 if(!$device) { $device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE device_id = '" . $interface['device_id'] . "'")); }

 unset($ifAdminStatus, $ifOperStatus, $ifAlias, $ifDescr);

 $interface['hostname'] = $device['hostname'];
 $interface['device_id'] = $device['device_id'];

 if($device['status'] == '1') {

   unset($update);
   unset($update_query);
   unset($seperator);

   echo("Looking at " . $interface['ifDescr'] . " on " . $device['hostname'] . "\n");

   $snmp_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
   $snmp_cmd .= " ifAdminStatus." . $interface['ifIndex'] . " ifOperStatus." . $interface['ifIndex'] . " ifAlias." . $interface['ifIndex'];

   $snmp_output = trim(`$snmp_cmd`);
   $snmp_output = str_replace("No Such Object available on this agent at this OID", "", $snmp_output);
   $snmp_output = str_replace("No Such Instance currently exists at this OID", "", $snmp_output);
   $snmp_output = str_replace("\"", "", $snmp_output);

   list($ifAdminStatus, $ifOperStatus, $ifAlias) = explode("\n", $snmp_output);

   if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
   $ifAlias = trim(str_replace("\"", "", $ifAlias));
   $ifAlias = trim($ifAlias);

   $old_rrdfile = "rrd/" . $device['hostname'] . "." . $interface['ifIndex'] . ".rrd";
   $rrdfile = $host_rrd . "/" . $interface['ifIndex'] . ".rrd"; 

   if(is_file($old_rrdfile) && !is_file($rrdfile)) { rename($old_rrdfile, $rrdfile); echo("Moving $old_rrdfile to $rrdfile");  }

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


   if( file_exists("includes/polling/interface-" . $device['os'] . ".php") ) { include("includes/polling/interface-" . $device['os'] . ".php"); }


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

   if ($update) {
     $update_query  = "UPDATE `interfaces` SET ";
     $update_query .= $update;
     $update_query .= " WHERE `interface_id` = '" . $interface['interface_id'] . "'";
     echo("Updating : " . $device['hostname'] . " $ifDescr\nSQL :$update_query\n\n");
     $update_result = mysql_query($update_query);
   } else {
#     echo("Not Updating : " . $device['hostname'] ." $ifDescr ( " . $interface['ifDescr'] . " )\n\n");
   }

   if($ifOperStatus == "up") {

    $snmp_data_cmd  = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
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
     $ret = rrdtool_update("$rrdfile", $woo);
   } else {
     echo("Interface " . $device['hostname'] . " " . $interface['ifDescr'] . " is down\n");
  }
 }

  $rates = interface_rates ($interface);
  mysql_query("UPDATE `interfaces` SET in_rate = '" . $rates['in'] . "', out_rate = '" . $rates['out'] . "' WHERE interface_id= '" . $interface['interface_id'] . "'");

}

?>

