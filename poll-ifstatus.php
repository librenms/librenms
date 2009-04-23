#!/usr/bin/php
<?php

  //
  // Interface Status Poller
  //
  
  include("config.php");
  include("includes/functions.php");

  $interface_query = mysql_query("SELECT *, I.interface_id AS sqlid FROM `interfaces` AS I, `devices` AS D where I.device_id = D.device_id AND D.status = '1' AND I.device_id LIKE '%" . $argv[1] . "' ORDER BY I.device_id DESC");

  while ($interface = mysql_fetch_array($interface_query)) {
      $hostname = $interface['hostname'];
      $host = $interface['host'];
      $old_if = $interface['if'];
      $ifIndex = $interface['ifIndex'];
      $old_alias = $interface['name'];
      $id = $interface['sqlid'];
      $old_up = $interface['up'];
      $old_speed = $interface['speed'];
      $os = $interface['os'];
      $old_duplex = $interface['duplex'];
      $old_mac = $interface['mac'];
      $old_up_admin = $interface['up_admin'];
      $snmpver = $interface['snmpver'];
      $snmp_cmd = "snmpget -m IF-MIB -O qv -".$interface['snmpver']." -c ".$interface['community']." ".$interface['hostname'].":".$interface['port']." ifDescr.$ifIndex ifAdminStatus.$ifIndex ifOperStatus.$ifIndex ";
      $snmp_cmd .= "ifAlias.$ifIndex ifName.$ifIndex";
      $snmp_output = trim(shell_exec($snmp_cmd));
      list($ifDescr, $ifAdminStatus, $ifOperStatus, $ifAlias, $ifName) = explode("\n", $snmp_output);

      ## DUPLEX IS AT .1.3.6.1.4.1.9.5.1.4.1.1.10 <-- DUPLEX, K? BUT ONLY CATALYFAILS!

      $ifDescr = trim(str_replace("\"", "", $ifDescr));
      $name = $ifDescr;
      if ($ifDuplex == 3) { $ifDuplex = "half"; } elseif ($ifDuplex == 2) { $ifDuplex = "full"; } else { $ifDuplex = "unknown"; }
      $ifAlias = str_replace("No Such Object available on this agent at this OID", "", $ifAlias);
      $name = strtolower($name);

      $ifAlias = str_replace("No Such Object available on this agent at this OID", "", $ifAlias);
      $name = str_replace("no such object available on this agent at this oid", "", $name);
#      if ($ifAlias == " ") { $ifAlias = str_replace(" ", "", $ifAlias); }
      $ifAlias = trim(str_replace("\"", "", $ifAlias));
      $ifAlias = trim($ifAlias);
      if ($old_alias != $ifAlias || $old_up != $ifOperStatus || $old_up_admin != $ifAdminStatus || $old_duplex != $ifDuplex || $old_if == $name ) 

      unset($update); 
      unset($update_query); 
      unset($seperator);
      
      if ( $old_if != $name ) {
         $update = "`if` = '$name'"; 
         $seperator = ", "; 
      }
      if ( $old_alias != $ifAlias ) {
         $update .= $seperator . "`name` = \"$ifAlias\"";
      }
      if ( $old_up != $ifOperStatus ) {
         $update .= $seperator . "`up` = '$ifOperStatus'";
      }
      if ( $old_up_admin != $ifAdminStatus ) {
         $update .= $seperator . "`up_admin` = '$ifAdminStatus'";
      }
      if ( $old_duplex != $ifDuplex ) {
         $update .= $seperator . "`duplex` = '$ifDuplex'";
      }

      if ($update) {
	 $update_query  = "UPDATE `interfaces` SET ";
         $update_query .= $update;
         $update_query .= " WHERE `id` = '$id'";
         echo("Updating : $hostname $name\n$update_query\n\n");
         $update_result = mysql_query($update_query);
      } else {
         echo("Not Updating : $hostname $name ( $old_if )\n\n");
      }
  }
?>
