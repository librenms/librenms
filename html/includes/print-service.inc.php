<?php

if (!$samehost) {
    if ($bg == $list_colour_a) {
        $bg = $list_colour_b;
    }
    else {
        $bg = $list_colour_a;
    }
}

$service_type = strtolower($service['service_type']);

if ($service[service_status] == '0') {
    $status = "<span class=red><b>$service_type</b></span>";
}
else if ($service[service_status] == '1') {
    $status = "<span class=green><b>$service_type</b></span>";
}
else if ($service[service_status] == '2') {
    $status = "<span class=grey><b>$service_type</b></span>";
}

$message = trim($service['service_message']);
$message = str_replace("\n", '<br />', $message);

$desc = trim($service['service_desc']);
$desc = str_replace("\n", '<br />', $desc);

$since = (time() - $service['service_changed']);
$since = formatUptime($since);

if ($service['service_checked']) {
    $checked = (time() - $service['service_checked']);
    $checked = formatUptime($checked);
}
else {
    $checked = 'Never';
}

$mini_url = 'graph.php?id='.$service['service_id'].'&amp;type=service_availability&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=80&amp;height=20&amp;bg=efefef';

$popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$service['service_type'];
$popup .= "</div><img src=\'graph.php?id=".$service['service_id'].'&amp;type=service_availability&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now']."&amp;width=400&amp;height=125\'>";
$popup .= "', RIGHT".$config['overlib_defaults'].');" onmouseout="return nd();"';

echo "
       <tr>";

if ($device_id) {
    if (!$samehost) {
        echo "<td>".generate_device_link($device).'</span></td>';
    }
    else {
        echo '<td></td>';
    }
}

echo "
         <td>
           $status
         </td>
         <td>
           $since
         </td>
         <td>
           <span class=box-desc>$message</span>
         </td>
         <td>
           <span class=box-desc>$desc</span>
         </td>
       </tr>";

$i++;
