#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "AND `device_id` = '$argv[1]'"; }

function snmp_cache_cip($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O snq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  echo("Caching: $oid\n");
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split(" ", $entry);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    $this_oid = substr($this_oid, 30);
    list($ifIndex,$dir,$a,$b,$c,$d,$e,$f) = explode(".", $this_oid);
    $h_a = zeropad(dechex($a));
    $h_b = zeropad(dechex($b));
    $h_c = zeropad(dechex($c));
    $h_d = zeropad(dechex($d));
    $h_e = zeropad(dechex($e));
    $h_f = zeropad(dechex($f));
    $mac = "$h_a$h_b$h_c$h_d$h_e$h_f";
    if($dir == "1") { $dir = "input"; } elseif($dir == "2") { $dir = "output"; }
    if($mac && $dir) {
      $array[$device_id][$ifIndex][$mac][$oid][$dir] = $this_value;
    }
  }
  return $array;
}

$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' AND `status` = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {
  echo("-> " . $device['hostname'] . "\n"); 
  $i++;
  $cip_oids = array('cipMacHCSwitchedBytes', 'cipMacHCSwitchedPkts');
  foreach ($cip_oids as $oid)      { $array = snmp_cache_cip($oid, $device, $array, "CISCO-IP-STAT-MIB"); }
  echo("\n");
}

$mac_accounting_query = mysql_query("SELECT * FROM `mac_accounting` as A, `interfaces` AS I, `devices` AS D where A.interface_id = I.interface_id AND I.device_id = D.device_id AND D.status = '1'");
while ($acc = mysql_fetch_array($mac_accounting_query)) {
  
  $device_id = $acc['device_id'];
  $ifIndex = $acc['ifIndex'];
  $mac = $acc['mac'];
  if($array[$device_id][$ifIndex][$mac]) {

  $b_in = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'];
  $b_out = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'];
  $p_in = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'];
  $p_out = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'];

  #echo($acc['hostname']." ".$acc['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out ");

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

   $woo = "N:".($b_in+0).":".($b_out+0);
   $ret = rrdtool_update("$rrdfile", $woo);

  $woo = "N:".($p_in+0).":".($p_out+0);
  $ret = rrdtool_update("$pkts_rrdfile", $woo);

  #echo(" R!\n");

  }

}

echo("$i devices polled");

?>
