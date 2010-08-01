<?

if(is_numeric($id))
{
  $sql = mysql_query("SELECT * FROM `storage` WHERE `storage_id` = '".mres($id)."'");
  $storage = mysql_fetch_assoc($sql);

  if(is_numeric($storage['device_id']) && device_permitted($storage['device_id']))   
  {
    $device = device_by_id_cache($storage['device_id']);
    $rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storage-" . $storage['storage_mib'] . "-" . $storage['storage_descr'] . ".rrd");

    $title  = generatedevicelink($device);
    $title .= " :: Storage :: " . htmlentities($storage['storage_descr']);
    $auth = TRUE;
  }
}

?>
