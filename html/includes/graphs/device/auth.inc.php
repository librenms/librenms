<?

if(is_numeric($id)) {
  $device = device_by_id_cache($id);
}

#if(!device_permitted($device['device_id'])) { echo("Not Permitted"); exit; }

$title = generatedevicelink($device);

?>
