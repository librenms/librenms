<?php

// MYSQL Check - FIXME
// 1 UPDATE

$os = getHostOS($device);

if ($os != $device['os'])
{
  $sql = dbUpdate(array('`os`' => $os), 'devices', 'device_id=?',array($device['device_id']));
  echo("Changed OS! : $os\n");
  log_event("Device OS changed ".$device['os']." => $os", $device, 'system');
  $device['os'] = $os;
}

?>
