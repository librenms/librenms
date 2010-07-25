<?

if(is_numeric($id)) {
  $device = device_by_id_cache($id);
}

$title = generatedevicelink($device);



?>
