<?php

$cip_oids = array('cipMacHCSwitchedBytes', 'cipMacHCSwitchedPkts');
echo("Caching OID: ");
$cip_array = array();

foreach ($cip_oids as $oid) 
{ 
  echo("$oid "); 
  $cip_array = snmpwalk_cache_cip($device, $oid, $cip_array, "CISCO-IP-STAT-MIB"); 
}

$polled = time();

$mac_entries = 0;

$mac_accounting_query = mysql_query("SELECT *, A.poll_time AS poll_time FROM `mac_accounting` as A, `ports` AS I where A.interface_id = I.interface_id AND I.device_id = '".$device['device_id']."'");

while ($acc = mysql_fetch_assoc($mac_accounting_query))
{
  $device_id = $acc['device_id'];
  $ifIndex = $acc['ifIndex'];
  $mac = $acc['mac'];

  $polled_period = $polled - $acc['poll_time'];

  if ($cip_array[$ifIndex][$mac])
  {
    $update .= "`poll_time` = '".$polled."'";
    $update .= ", `poll_prev` = '".$acc['poll_time']."'";
    $update .= ", `poll_period` = '".$polled_period."'";

    $mac_entries++;

    $b_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'];
    $b_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'];
    $p_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'];
    $p_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'];

    $this_ma = &$cip_array[$ifIndex][$mac]; 

    /// Update metrics
    foreach ($cip_oids as $oid)
    {
      foreach (array('input','output') as $dir)
      {
        $oid_dir = $oid . "_" . $dir;
        $update .= ", `".$oid_dir."` = '".$this_ma[$oid][$dir]."'";
        $update .= ", `".$oid_dir."_prev` = '".$acc[$oid_dir]."'";
        $oid_prev = $oid_dir . "_prev";
        if ($this_ma[$oid][$dir])
        {
          $oid_diff = $this_ma[$oid][$dir] - $acc[$oid_dir];
          $oid_rate  = $oid_diff / $polled_period;
          $update .= ", `".$oid_dir."_rate` = '".$oid_rate."'";
          $update .= ", `".$oid_dir."_delta` = '".$oid_diff."'";
          if ($debug) { echo("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs\n"); }
        }
      }
    }

    if ($debug) { echo("\n" . $acc['hostname']." ".$acc['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out "); }
    $rrdfile = $host_rrd . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");

    if (!is_file($rrdfile))
    {
      rrdtool_create($rrdfile,"DS:IN:COUNTER:600:0:12500000000 \
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

    if ($update)
    { /// Do Updates
      $update_query  = "UPDATE `mac_accounting` SET ".$update." WHERE `ma_id` = '" . $acc['ma_id'] . "'";
      @mysql_query($update_query);
      if ($debug) { echo("\nMYSQL : [ $update_query ]"); }
    } /// End Updates

    unset($update_query); unset($update);
  }
}

unset($cip_array);

if ($mac_entries) { echo(" $mac_entries MAC accounting entries\n"); }

echo("\n");

?>