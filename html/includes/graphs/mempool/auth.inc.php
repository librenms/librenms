<?php

if(is_numeric($id))
{
  $sql = mysql_query("SELECT * FROM `mempools` AS C, `devices` AS D where C.`mempool_id` = '".mres($id)."' AND C.device_id = D.device_id");
  $mempool = mysql_fetch_assoc($sql);

  if(is_numeric($mempool['device_id']) && device_permitted($mempool['device_id']))   
  {
    $device = device_by_id_cache($mempool['device_id']);
    $rrd_filename = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("mempool-".$mempool['mempool_type']."-".$mempool['mempool_index'].".rrd");
    $title  = generatedevicelink($device);
    $title .= " :: Memory Pool :: " . htmlentities($mempool['mempool_descr']);
    $auth = TRUE;
  }
}

?>
