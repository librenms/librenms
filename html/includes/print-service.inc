<?php

if(!$samehost) {
  if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg = $list_colour_a; }
}

  $service_type = strtolower($service['service_type']);

  if($service[service_status] == '0') {$status = "<span class=red><b>$service_type</b></span>";
  } elseif ($service[service_status] == '1') {$status = "<span class=green><b>$service_type</b></span>";
  } elseif ($service[service_status] == '2') {$status = "<span class=grey><b>$service_type</b></span>";}

  $message = trim($service['service_message']);
  $message = str_replace("\n", "<br />", $message);

  $since = time() - $service['service_changed'];
  $since = formatUptime($since);

  if($service['service_checked']) {
    $checked = time() - $service['service_checked'];
    $checked = formatUptime($checked);
  } else { $checked = "Never"; }


  echo("
         <tr style=\"background-color: $bg; padding: 5px;\">");

if($device_id) {
  if(!$samehost) { 
    $device['device_id'] = $device_id;
    $device['hostname'] = $device_hostname;
    echo("<td valign=top width=250><span style='font-weight:bold;'>" . generatedevicelink($device) . "</span></td>");  
  } else { 
    echo("<td></td>");  
  }
}

echo("     
           <td valign=top class=list-bold>
             $status
           </td>
           <td valign=top width=175>
             $since
           </td>
           <td valign=top>
             <span class=box-desc>$message</span>
           </td>
         </tr>");

  $i++;

?>
