
<?php

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=140>
      ".$entry['humandate']."
    </td>");

  $if = mysql_fetch_array(mysql_query("SELECT * from interfaces AS I, devices as D where I.device_id = D.device_id AND I.interface_id = '".$entry['interface']."'"));

    echo("<td class=list-bold width=75>
      "  . generateiflink($if, makeshortif($if['ifDescr'])) . "
    </td>
    <td class=syslog>" . 
      htmlspecialchars(truncate($entry['message'], 45))
    . "</td>
    <td></td>
  </tr>");




?>
