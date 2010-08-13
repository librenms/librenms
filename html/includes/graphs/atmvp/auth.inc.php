<?php


if($_GET['id'] && is_numeric($_GET['id'])) { $atm_vp_id = $_GET['id']; }

$sql =  "SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D";
$sql .= " WHERE J.juniAtmVp_id = '".$atm_vp_id."' AND I.interface_id = J.interface_id AND I.device_id = D.device_id";

$query = mysql_query($sql);
$vp = mysql_fetch_array($query);

if(port_permitted($vp['interface_id'])) {

  $port   = $vp;
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  ".generate_port_link($port);
  $title .= " :: VP ".$vp['vp_id'];
  $auth = TRUE;
  $rrd_filename = $config['rrd_dir'] . "/" . $vp['hostname'] . "/" . safename("vp-" . $vp['ifIndex'] . "-".$vp['vp_id'].".rrd");

}

?>
