#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

$interface_query = mysql_query("SELECT *, I.id AS sqlid FROM `interfaces` AS I, `devices` AS D where I.host = D.id AND D.id LIKE '%" . $argv[1]. "' AND D.status = '1'");
while ($interface = mysql_fetch_array($interface_query)) {

  $hostname = $interface['hostname'];
  $host = $interface['host'];
  $old_if = $interface['if'];
  $ifIndex = $interface['ifIndex'];
  $old_alias = $interface['name'];
  $id = $interface['sqlid'];
  $old_up = $interface['up'];
  $old_speed = $interface['ifSpeed'];
  $old_duplex = $interface['ifDuplex'];
  $old_physaddress = $interface['ifPhysAddress'];
  $old_type = $interface['ifType'];
  $old_mtu = $interface['ifMtu'];
  $old_up_admin = $interface['up_admin'];
  $community = $interface['community'];  
  $os = $interface['os'];
  $snmp_cmd  = "snmpget -O qv -v2c -c $community $hostname ifName.$ifIndex ifDescr.$ifIndex ifAdminStatus.$ifIndex ifOperStatus.$ifIndex ";
  $snmp_cmd .= "ifAlias.$ifIndex ifSpeed.$ifIndex 1.3.6.1.2.1.10.7.2.1.$ifIndex ifType.$ifIndex ifMtu.$ifIndex ifPhysAddress.$ifIndex";
  $snmp_output = `$snmp_cmd`;
  $snmp_output = trim($snmp_output);
  $snmp_output = str_replace("No Such Object available on this agent at this OID", "", $snmp_output);
  $snmp_output = str_replace("No Such Instance currently exists at this OID", "", $snmp_output);

  $ifPhysAddress = strtolower(str_replace("\"", "", $ifPhysAddress));
  $ifPhysAddress = str_replace(" ", ":", $ifPhysAddress);
  echo("Looking at $old_if on $hostname \n");
  list($ifName, $ifDescr, $ifAdminStatus, $ifOperStatus, $ifAlias, $ifSpeed, $ifDuplex, $ifType, $ifMtu, $ifPhysAddress) = explode("\n", $snmp_output);
  $ifDescr = trim(str_replace("\"", "", $ifDescr));
  if ($ifDuplex == 3) { $ifDuplex = "half"; } elseif ($ifDuplex == 2) { $ifDuplex = "full"; } else { $ifDuplex = "unknown"; }
  $ifDescr = strtolower($ifDescr);
  if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
  $ifAlias = trim(str_replace("\"", "", $ifAlias));
  $ifAlias = trim($ifAlias);

  $rrdfile = "rrd/" . $hostname . ".". $ifIndex . ".rrd";
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

  if ( $old_if != $ifDescr && $ifDescr != "" ) {
     $update = "`if` = '$ifDescr'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Name: $old_if -> $ifDescr')");
  }

  if ( $old_alias != $ifAlias ) {
     $update .= $seperator . "`name` = \"$ifAlias\"";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Desc: $old_alias -> $ifAlias')");
  }
  if ( $old_up != $ifOperStatus && $ifOperStatus != "" ) {
     $update .= $seperator . "`up` = '$ifOperStatus'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Interface went $ifOperStatus')");
  }
  if ( $old_up_admin != $ifAdminStatus && $ifAdminStatus != "" ) {
     $update .= $seperator . "`up_admin` = '$ifAdminStatus'";
     $seperator = ", ";
     if($ifAdminStatus == "up") { $admin = "enabled"; } else { $admin = "disabled"; }
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Interface $admin')");
  }
  if ( $old_duplex != $ifDuplex && $ifDuplex != "" ) {
     $update .= $seperator . "`ifDuplex` = '$ifDuplex'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Duplex changed to $ifDuplex')");
  }
  if ( $old_type != $ifType && $ifType != "" ) {
     $update .= $seperator . "`ifType` = '$ifType'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Type changed to $ifType')");
  }
  if ( $old_mtu != $ifMtu && $ifMtu != "" ) {
     $update .= $seperator . "`ifMtu` = '$ifMtu'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'MTU changed to $ifMtu')");
  }
  if ( $old_physaddress != $ifPhysAddress && $ifPhysAddress != "" ) {
     $update .= $seperator . "`ifPhysAddress` = '$ifPhysAddress'";
     $seperator = ", ";
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'MAC changed to $ifPhysAddress')");
  }

  if ( $old_speed != $ifSpeed && $ifSpeed != "" ) {
     $update .= $seperator . "`ifSpeed` = '$ifSpeed'";
     $seperator = ", ";
     $prev = humanspeed($old_speed); 
     $now = humanspeed($ifSpeed);
     mysql_query("INSERT INTO eventlog (host, interface, datetime, message) values ($interface[host], $interface[sqlid], NOW(), 'Speed changed from $prev -> $now')");
  }

  if ($update) {
     $update_query  = "UPDATE `interfaces` SET ";
     $update_query .= $update;
     $update_query .= " WHERE `id` = '$id'";
     echo("Updating : $hostname $ifDescr\n$update_query\n\n");
     $update_result = mysql_query($update_query);
  } else {
     echo("Not Updating : $hostname $ifDescr ( $old_if )\n\n");
  }

  if($ifOperStatus == "up") {
    $snmp_data = `snmpget -O qv -v2c -c $community $hostname ifHCInOctets.$ifIndex ifHCOutOctets.$ifIndex ifInErrors.$ifIndex ifOutErrors.$ifIndex \
                 ifInUcastPkts.$ifIndex ifOutUcastPkts.$ifIndex ifInNUcastPkts.$ifIndex ifOutNUcastPkts.$ifIndex`;
    $snmp_data = str_replace("Wrong Type (should be Counter32): ","", $snmp_data);
    $snmp_data = str_replace("No Such Instance currently exists at this OID","", $snmp_data);
    list($ifHCInOctets, $ifHCOutOctets, $ifInErrors, $ifOutErrors, $ifInUcastPkts, $ifOutUcastPkts, $ifInNUcastPkts, $ifOutNUcastPkts) = explode("\n", $snmp_data);
    if($ifHCInOctets == "" || strpos($ifHCInOctets, "No") !== FALSE ) {
      $fixit = `snmpget -O qv -v2c -c $community $hostname ifInOctets.$ifIndex ifOutOctets.$ifIndex`;
      list ($ifHCInOctets, $ifHCOutOctets) = explode("\n", $fixit);
    }
     $woo = "N:$ifHCInOctets:$ifHCOutOctets:$ifInErrors:$ifOutErrors:$ifInUcastPkts:$ifOutUcastPkts:$ifInNUcastPkts:$ifOutNUcastPkts";
     $ret = rrd_update("$rrdfile", $woo);
   } else {
     echo("Interface $hostname $old_if is down\n");
  }
}

mysql_query("UPDATE interfaces set ifPhysAddress = '' WHERE ifPhysAddress = 'No Such Instance currently exists at this OID'");

?>
