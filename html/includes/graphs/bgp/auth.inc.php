<?php

if(is_numeric($id))
{

  $data = mysql_fetch_assoc(mysql_query("SELECT * FROM bgpPeers WHERE bgpPeer_id = '".$id."'"));

  if(is_numeric($data['device_id']) && device_permitted($data['device_id']))
  {
    $device = device_by_id_cache($data['device_id']);

    $title  = generatedevicelink($device);
    $title .= " :: BGP :: " . htmlentities($data['bgp_peerid']);
    $auth = TRUE;
  }
}




?>
