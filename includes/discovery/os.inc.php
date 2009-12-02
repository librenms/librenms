<?php

   $os = getHostOS($device);

   if($os != $device['os']) {
      $sql = mysql_query("UPDATE `devices` SET `os` = '$os' WHERE `device_id` = '".$device['device_id']."'");
      echo("Changed OS! : $os\n");
      $eventlog = mysql_query("INSERT INTO eventlog (host, interface, datetime, message) VALUES ('" . $device['device_id'] . "', NULL, NOW(), 'Device OS changed ".$device['os']." => $os')");
      $device['os'] = $os;
   }

?>
