<?php

   echo ("<p class=device-header><a href='?page=device&id=$device[id]'>$device[hostname]</a></p>");
   if($device[location] != "") { $locationhtml = $device[location]; }

   $type = strtolower($device['os']);
   if(file_exists("../images/os/$type" . ".gif")){ $image = "<img src='../images/os/$type.gif' align=absmiddle>"; }

   if ($device['os'] == "IOS") {
      echo ("<p class=box-desc>
             <div style='float: left; padding: 10px;'>$image</div>
             $device[hardware] <br /> $device[os] $device[version] <br />
             $device[features] <br />
             $locationhtml<br />
             Up " . formatUptime($device[uptime]) ."</p>");
   } else {
      echo ("<p class=box-desc>$image $device[os] $device[version] ($device[features])
             <br />$locationhtml <br />
             Up " . formatUptime($device[uptime]) ."</p>");
   }

   if($_GET['page'] == 'device') {
     if($_GET['graphs'] == 'yes') {
       echo("<p class=interface><a href='?page=device&id=$_GET[id]'><img src='/images/neu/hide-graphs.png' border=0 align=absmiddle> Hide Graphs</a></p>");
     } else {
       echo("<p class=interface><a href='?page=device&id=$_GET[id]&graphs=yes'><img src='/images/neu/show-graphs.png' border=0 align=absmiddle> Show Graphs</a></p>");
     }
   }

?>
