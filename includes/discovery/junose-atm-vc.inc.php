<?php


### We're discovering this MIB 
# snmpwalk -v2c -c <community> <hostname> -M mibs/junose/ -m Juniper-UNI-ATM-MIB juniAtmVpStatsEntry

## JunOSe ATM VCs
if($device['os'] == "junose" && $config['enable_ports_junoseatmvc'])
{
  echo("JunOSe ATM VCs : ");
  $vc_array = snmpwalk_cache_multi_oid($device, "juniAtmVpStatsInCells", $vc_array, "Juniper-UNI-ATM-MIB" , "+".$config['install_dir']."/mibs/junose");
  $valid_vc = array();
  if($debug) { print_r($vc_array); }

  if(is_array($vc_array[$device['device_id']])) {
    foreach($vc_array[$device['device_id']] as $index => $entry) {

        list($interface_id,$vp_id)= explode('.', $index);      

        if(is_numeric($interface_id) && is_numeric($vp_id)) {
          discover_juniAtmVp($valid_vc, $interface_id, $vp_id, NULL);
        }
    } ## End Foreach
  } ## End if array
} ## End JUNOS vc

  unset ($vc_array);

### Remove ATM VCs which weren't redetected here

$sql = "SELECT * FROM `ports` AS P, `juniAtmVp` AS J WHERE P.`device_id`  = '".$device['device_id']."' AND J.interface_id = P.interface_id";
$query = mysql_query($sql);

if($debug) { print_r ($valid_vc); }

while ($test = mysql_fetch_array($query)) {
  $interface_id = $test['interface_id'];
  $vc_id = $test['vc_id'];
  if($debug) { echo($interface_id . " -> " . $vc_id . "\n"); }
  if(!$valid_vc[$interface_id][$vc_id]) {
    echo("-");
    mysql_query("DELETE FROM `juniAtmVp` WHERE `juniAtmVp` = '" . $test['juniAtmVp'] . "'");
  }
  unset($interface_id); unset($vc_id);
}

unset($valid_vc);
echo("\n");

?>
