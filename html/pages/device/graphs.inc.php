<?php

$device_query = mysql_query("select * from devices WHERE `device_id` = '$_GET[id]'");
while ($device = mysql_fetch_array($device_query)) 
{
  $hostname = $device[hostname];
  $bg="#ffffff";

  echo('<div style="clear: both;">');

  if (is_file($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php")) {
    include($config['install_dir'] . "/html/pages/device/graphs/os-$os.inc.php");
  }

  if ($os_groups[$device['os']]) { $os_group = $os_groups[$device['os']]; }

  if (is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php")) {
    /// OS Specific
    include($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php");
  } elseif ($os_group && is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php")) {
    /// OS Group Specific
    include($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php");
  } else {
    echo("No graph definitions found for OS " . $device['os'] . "!");
  }

   if ($memgraph) {
     echo("<img src=\"$memgraph\"> <img src=\"$memgraphm\">");
   }
   if ($storagegraph) {
     echo("$storagegraph");
   }
   if ($loadgraph) {
     echo("<img src=\"$loadgraph\"> <img src=\"$loadgraphm\">");
   }
   if ($tempgraph) {
     echo("<img src=\"$tempgraph\"> <img src=\"$tempgraphm\">");
   }
   if ($mailsgraph) {
     echo("<img src=\"$mailsgraph\"> <img src=\"$mailsgraphm\">");
   }
   if ($mailerrorgraph) {
     echo("<img src=\"$mailerrorgraph\"> <img src=\"$mailerrorgraphm\">");
   }
   if ($couriergraph) {
     echo("<img src=\"$couriergraph\"> <img src=\"$couriergraphm\">");
   }
   if ($ahitsgraph) {
     echo("<img src=\"$ahitsgraph\"> <img src=\"$ahitsgraphm\">");
   }
   if ($abitsgraph) {
     echo("<img src=\"$abitsgraph\"> <img src=\"$abitsgraphm\">");
   }
   if ($usersgraph) {
     echo("<img src=\"$usersgraph\"> <img src=\"$usersgraphm\">");
   }
   if ($procsgraph) {
     echo("<img src=\"$procsgraph\"> <img src=\"$procsgraphm\">");
   }
   if ($uptimegraph) {
     echo("<img src=\"$uptimegraph\"> <img src=\"$uptimegraphm\">");
   }
   echo("</div>");
}

?>

