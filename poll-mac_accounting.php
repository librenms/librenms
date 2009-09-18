#!/usr/bin/php
<?php
  
  include("config.php");
  include("includes/functions.php");
  
  $mac_accounting_query = mysql_query("SELECT * FROM `mac_accounting` as A, `interfaces` AS I, `devices` AS D where A.interface_id = I.interface_id AND I.device_id = D.device_id AND D.status = '1'");
  while ($acc = mysql_fetch_array($mac_accounting_query)) {

   echo("Polling :" . $acc['ip']. " " . $acc['ifDescr']. " " . $acc['mac'] . " " . $acc['hostname'] . " ");

   $mac = $acc['mac'];

   $oid = hexdec(substr($mac, 0, 2));
   $oid .= ".".hexdec(substr($mac, 2, 2));
   $oid .= ".".hexdec(substr($mac, 4, 2));
   $oid .= ".".hexdec(substr($mac, 6, 2));
   $oid .= ".".hexdec(substr($mac, 8, 2));
   $oid .= ".".hexdec(substr($mac, 10, 2));

   $snmp_cmd  = $config['snmpget'] . " -m CISCO-IP-STAT-MIB -O Uqnv -" . $acc['snmpver'] . " -c " . $acc['community'] . " " . $acc['hostname'];
   $snmp_cmd .= " cipMacSwitchedBytes.". $acc['ifIndex'] .".input." . $oid;
   $snmp_cmd .= " cipMacSwitchedBytes.". $acc['ifIndex'] .".output." . $oid;
   $snmp_cmd .= " cipMacSwitchedPkts.". $acc['ifIndex'] .".input." . $oid;
   $snmp_cmd .= " cipMacSwitchedPkts.". $acc['ifIndex'] .".output." . $oid;

   $snmp_output = trim(shell_exec($snmp_cmd));

   $snmp_output = preg_replace("[a-zA-Z\ ]", "", $snmp_output);

   list($in,$out,$pktin,$pktout) = explode("\n", $snmp_output);

   $acc_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting";
   if(!is_dir($acc_rrd)) { mkdir($acc_rrd); echo("Created directory : $acc_rrd\n"); }
   $old_rrdfile = $acc_rrd . "/" . $acc['ifIndex'] . "-" . $acc['ip'] . ".rrd";
   $rrdfile = $acc_rrd . "/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
   if(is_file($old_rrdfile) && !is_file($rrdfile)) { rename($old_rrdfile, $rrdfile); echo("Moved $old_rrdfile -> $rrdfile \n"); };
   $pkts_rrdfile = $acc_rrd . "/" . $acc['ifIndex'] . "-" . $acc['mac'] . "-pkts.rrd";

   if(!is_file($pkts_rrdfile)) {
     $woo = shell_exec($config['rrdtool'] ." create $pkts_rrdfile \
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

   $woo = "N:".($in+0).":".($out+0);
   $ret = rrdtool_update("$rrdfile", $woo);

  $woo = "N:".($pktin+0).":".($pktout+0);
  $ret = rrdtool_update("$pkts_rrdfile", $woo);

  $rates = interface_rates ($rrdfile);

  $pkt_rates = interface_rates ($pkts_rrdfile);

  $pkt_rate['in'] = round($pkt_rate['in'] / 8);
  $pkt_rate['out'] = round($pkt_rate['out'] / 8);

  mysql_query("UPDATE `mac_accounting` SET bps_in = '" . $rates['in'] . "', bps_out = '" . $rates['out'] . "', pps_in = '" . $pkt_rates['in'] . "', pps_out = '" . $pkt_rates['out'] . "' WHERE ma_id= '" . $acc['ma_id'] . "'");

  echo(formatRates($rates['in']) . " (" . $pkt_rates['in'] . "pps) in " . formatRates($rates['out']) . " (" . $pkt_rates['out'] . "pps) out \n");

  }
?>
