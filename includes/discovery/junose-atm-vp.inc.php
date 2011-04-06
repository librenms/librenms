<?php

# We're discovering this MIB
# snmpwalk -v2c -c <community> <hostname> -M mibs/junose/ -m Juniper-UNI-ATM-MIB juniAtmVpStatsEntry

## JunOSe ATM vps
if ($device['os'] == "junose" && $config['enable_ports_junoseatmvp'])
{
  echo("JunOSe ATM vps : ");
  $vp_array = snmpwalk_cache_multi_oid($device, "juniAtmVpStatsInCells", $vp_array, "Juniper-UNI-ATM-MIB" , $config['install_dir']."/mibs/junose");
  $valid_vp = array();
  if ($debug) { print_r($vp_array); }

  if (is_array($vp_array))
  {
    foreach ($vp_array as $index => $entry)
    {
      list($ifIndex,$vp_id)= explode('.', $index);

      $interface_id = mysql_result(mysql_query("SELECT `interface_id` FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '".$ifIndex."'"),0);

      if (is_numeric($interface_id) && is_numeric($vp_id))
      {
        discover_juniAtmvp($valid_vp, $interface_id, $vp_id, NULL);
      }
    } ## End Foreach
  } ## End if array

  unset ($vp_array);

  ## Remove ATM vps which weren't redetected here

  $sql = "SELECT * FROM `ports` AS P, `juniAtmVp` AS J WHERE P.`device_id`  = '".$device['device_id']."' AND J.interface_id = P.interface_id";
  $query = mysql_query($sql);

  if ($debug) { print_r ($valid_vp); }

  while ($test = mysql_fetch_assoc($query))
  {
    $interface_id = $test['interface_id'];
    $vp_id = $test['vp_id'];
    if ($debug) { echo($interface_id . " -> " . $vp_id . "\n"); }
    if (!$valid_vp[$interface_id][$vp_id])
    {
      echo("-");
      mysql_query("DELETE FROM `juniAtmvp` WHERE `juniAtmVp` = '" . $test['juniAtmvp'] . "'");
    }

    unset($interface_id); unset($vp_id);
  }

  unset($valid_vp);
  echo("\n");
}

?>