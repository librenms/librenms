<?

if(is_numeric($_GET['id']) && interfacepermitted($_GET['id'])) {
  $port = get_port_by_id($_GET['id']);
  $device = device_by_id_cache($port['device_id']);

  $title  = generatedevicelink($device);
  $title .= " :: Port  ".generateiflink($port);

  $auth = TRUE;

}

?>
