<?php

$os = getHostOS($device);

if ($os != $device['os'])
{
  $sql = mysql_query("UPDATE `devices` SET `os` = '$os' WHERE `device_id` = '".$device['device_id']."'");
  echo("Changed OS! : $os\n");
  log_event("Device OS changed ".$device['os']." => $os", $device, 'system');
  $device['os'] = $os;
}

?>