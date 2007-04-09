<?php

   if ($device['status'] == '0') { $class = "list-device-down"; } else { $class = "list-device"; }
   if ($device['ignore'] == '1') {
     $class = "list-device-ignored";
     if ($device['status'] == '1') { $class = "list-device-ignored-up"; }
   }
   $type = strtolower($device['os']);
   unset($image);

   $image = getImage($device['device_id']);

   echo("
            <tr >
             <td width='40' align=center valign=middle>$image</td>
             <td valign=middle><span style='font-weight: bold; font-size: 20px;'>" . generatedevicelink($device) . "</span>
             <br />" . $device['purpose'] . "</td>
             <td></td>
           </tr>");

?>
