<?php

   if($bg == $list_colour_b) { $bg = $list_colour_a; } else { $bg = $list_colour_b; }
   if ($device['status'] == '0') { $class = "list-device-down"; } else { $class = "list-device"; }
   if ($device['ignore'] == '1') {
     $class = "list-device-ignored";
     if ($device['status'] == '1') { $class = "list-device-ignored-up"; }
   }

   $type = strtolower($device['os']);
   unset($image);

   $image = getImage($device['device_id']);

   $device['os_text'] = $os_text[$device[os]]; 

   echo("  <tr bgcolor='$bg' onmouseover=\"this.style.backgroundColor='#fdd';\" onmouseout=\"this.style.backgroundColor='$bg';\" 
             onclick=\"location.href='/device/".$device['device_id']."/'\" style='cursor: hand;'>
             <td width='40' align=center valign=middle>$image</td>
             <td width='300'><span style='font-weight: bold; font-size: 14px;'>" . generatedevicelink($device) . "</span>
             <br />$device[sysName]</td>
             <td width='200'>$device[os_text]<br />
             $device[version]</td>
             <td width='200'>$device[hardware]<br />
             $device[features]</td>
             <td>" . formatUptime($device[uptime]) . "
             <br />
             $device[location]</td>
             <td width=10>
             </td>
           </tr>");

?>
