<?php

   $type = strtolower($device['os']);
   if(file_exists("images/os/$type" . ".gif")){ $image = "<img src='/images/os/$type.gif'>"; }

   echo("<table cellpadding=0 cellspacing=0 class=devicetable width=100%>");
   echo("
            <tr bgcolor='#e5e5e5'>
             <td width='40' align=center valign=middle>$image</td>
             <td width='250' style='font-weight: bold;'><a class='$class' href='?page=device&id=$device[id]'>$device[hostname]</a>
                <br />$device[hardware]</td>
             <td width='150'>$device[os] $device[version]<br />
             $device[features]</td>
             <td>$device[location]<br />
             Up " . formatUptime($device[uptime]) ."</td>
             <td>");

   if($_GET['page'] == 'device') {
     if($_GET['graphs'] == 'yes') {
       echo("<p class=interface><a href='?page=device&id=$_GET[id]'>
       <img src='/images/neu/hide-graphs.png' border=0 align=absmiddle> Hide Graphs</a></p>");
     } else {
       echo("<p class=interface><a href='?page=device&id=$_GET[id]&graphs=yes'>
       <img src='/images/neu/show-graphs.png' border=0 align=absmiddle> Show Graphs</a></p>");
     }
   }

echo("           </tr>");

   echo("</table>");

?>
