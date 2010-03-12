
<?php

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=140>
      ".$entry['humandate']."
    </td>
    <td class=syslog>");

   $if = mysql_fetch_array(mysql_query("SELECT * from ports AS I, devices as D where I.device_id = D.device_id AND I.interface_id = '".$entry['interface']."'"));

    echo("<span class=list-bold>
      "  . generateiflink($if) . "
    </span>" . 
      htmlspecialchars($entry['message'])
    . "</td>
    <td></td>
  </tr>");




?>
