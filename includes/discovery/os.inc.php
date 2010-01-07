<?php

   $os = getHostOS($device);

   if($os != $device['os']) {
      $sql = mysql_query("UPDATE `devices` SET `os` = '$os' WHERE `device_id` = '".$device['device_id']."'");
      echo("Changed OS! : $os\n");
      eventlog("Device OS changed ".$device['os']." => $os", $device['device_id']);
      $device['os'] = $os;
   }

?>
