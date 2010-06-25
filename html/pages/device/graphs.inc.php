<?php

$device_query = mysql_query("select * from devices WHERE `device_id` = '$_GET[id]'");
while ($device = mysql_fetch_array($device_query)) 
{
  $hostname = $device[hostname];
  $bg="#ffffff";

  echo('<div style="clear: both;">');

  if ($config['os'][$device['os']]['group']) { $os_group = $config['os'][$device['os']]['group']; }

  if (is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php")) {
    /// OS Specific
    include($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php");
  } elseif ($os_group && is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php")) {
    /// OS Group Specific
    include($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php");
  } else {
    echo("No graph definitions found for OS " . $device['os'] . "!");
  }

   echo("</div>");
}

?>

