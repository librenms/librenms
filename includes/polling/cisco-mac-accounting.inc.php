<?php

$cip_oids = array('cipMacHCSwitchedBytes', 'cipMacHCSwitchedPkts');
echo("Caching OID: ");
foreach ($cip_oids as $oid)      { echo("$oid "); $array = snmp_cache_cip($oid, $device, $array, "CISCO-IP-STAT-MIB"); }
echo("\n");

$polled = time();

$mac_entries = 0;

$mac_accounting_query = mysql_query("SELECT * FROM `mac_accounting` as A, `interfaces` AS I where A.interface_id = I.interface_id AND I.device_id = '".$device['device_id']."'");
while ($acc = mysql_fetch_array($mac_accounting_query)) {
  
  $device_id = $acc['device_id'];
  $ifIndex = $acc['ifIndex'];
  $mac = $acc['mac'];
  if($array[$device_id][$ifIndex][$mac]) {

    $polled_period = $polled - $acc['poll_time'];

    $update .= "`poll_time` = '".$polled."'";
    $update .= ", `poll_prev` = '".$acc['poll_time']."'";
    $update .= ", `poll_period` = '".$polled_period."'";

    $mac_entries++;

    $b_in = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'];
    $b_out = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'];
    $p_in = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'];
    $p_out = $array[$device_id][$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'];

    $this_ma = &$array[$device_id][$ifIndex][$mac]; 

    /// Update metrics
    foreach ($cip_oids as $oid) {
      foreach(array('input','output') as $dir) {
        $oid_dir = $oid . "_" . $dir;
        $update .= ", `".$oid_dir."` = '".$this_ma[$oid][$dir]."'";
        $update .= ", `".$oid_dir."_prev` = '".$acc[$oid_dir]."'";
        $oid_prev = $oid_dir . "_prev";
        if($this_ma[$oid][$dir]) {
          $oid_diff = $this_ma[$oid][$dir] - $acc[$oid_dir];
          $oid_rate  = $oid_diff / $polled_period;
          $update .= ", `".$oid_dir."_rate` = '".$oid_rate."'";
          $update .= ", `".$oid_dir."_delta` = '".$oid_diff."'";
          if($debug) {echo("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs\n");}
        }
      }
    }

    if($debug) {echo("\n" . $acc['hostname']." ".$acc['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out ");}
    $rrdfile = $host_rrd . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");

    if(!is_file($rrdfile)) {
      $woo = shell_exec($config['rrdtool'] ." create $rrdfile \
        DS:IN:COUNTER:600:0:12500000000 \
        DS:OUT:COUNTER:600:0:12500000000 \
        DS:PIN:COUNTER:600:0:12500000000 \
        DS:POUT:COUNTER:600:0:12500000000 \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797 \
        RRA:MAX:0.5:1:600 \
        RRA:MAX:0.5:6:700 \
        RRA:MAX:0.5:24:775 \
        RRA:MAX:0.5:288:797");
    }
    $woo = "N:".($b_in+0).":".($b_out+0).":".($p_in+0).":".($p_out+0);
    $ret = rrdtool_update("$rrdfile", $woo);

      if ($update) { /// Do Updates
        $update_query  = "UPDATE `mac_accounting` SET ".$update." WHERE `ma_id` = '" . $acc['ma_id'] . "'";
        @mysql_query($update_query); $mysql++;
        if($debug) {echo("\nMYSQL : [ $update_query ]");}
      } /// End Updates
     unset($update_query); unset($update);

  }
}

if($mac_entries) { echo("$mac_entries mac accounting entries updated\n"); }

?>
