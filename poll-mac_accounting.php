#!/usr/local/bin/php
<?php
  
  include("config.php");
  include("includes/functions.php");
  
  $mac_accounting_query = mysql_query("SELECT * FROM `mac_accounting` as A, `interfaces` AS I, `devices` AS D where A.interface_id = I.interface_id AND I.device_id = D.device_id AND D.status = '1'");
  while ($acc = mysql_fetch_array($mac_accounting_query)) {

   echo("Polling :" . $acc['peer_ip']. " " . $acc['ifDescr']. " " . $acc['hostname'] . " ");

   $snmp_cmd  = $config['snmpget'] . " -m CISCO-IP-STAT-MIB -O Uqnv -" . $acc['snmpver'] . " -c " . $acc['community'] . " " . $acc['hostname'];
   $snmp_cmd .= " " . $acc['in_oid'] . " " . $acc['out_oid'];

   $snmp_output = trim(`$snmp_cmd`);

   list($in,$out) = explode("\n", $snmp_output);

   $acc_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting";
   if(!is_dir($acc_rrd)) { mkdir($acc_rrd); echo("Created directory : $acc_rrd\n"); }
   $rrdfile = $acc_rrd . "/" . $acc['ifIndex'] . "-" . $acc['peer_ip'] . ".rrd";

   if(!is_file($rrdfile)) {
     $woo = shell_exec($config['rrdtool'] ." create $rrdfile \
       DS:IN:COUNTER:600:0:12500000000 \
       DS:OUT:COUNTER:600:0:12500000000 \
       RRA:AVERAGE:0.5:1:600 \
       RRA:AVERAGE:0.5:6:700 \
       RRA:AVERAGE:0.5:24:775 \
       RRA:AVERAGE:0.5:288:797 \
       RRA:MAX:0.5:1:600 \
       RRA:MAX:0.5:6:700 \
       RRA:MAX:0.5:24:775 \
       RRA:MAX:0.5:288:797");
   }

   $woo = "N:$in:$out";
   $ret = rrdtool_update("$rrdfile", $woo);

  $rates = interface_rates ($rrdfile);
  mysql_query("UPDATE `mac_accounting` SET bps_in = '" . $rates['in'] . "', bps_out = '" . $rates['out'] . "' WHERE ma_id= '" . $acc['ma_id'] . "'");

  echo(formatRates($rates['in']) . " in " . formatRates($rates['out']) . " out \n");

  }
?>
