<?php

if ($_SESSION['userlevel'] == '10')
{
  $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `authlog` ORDER BY `datetime` DESC LIMIT 0,250";
  $data = mysql_query($query);

  echo("<table cellspacing=0 cellpadding=1 width=100%>");

  while ($entry = mysql_fetch_array($data))
  {
    if ($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

    echo("<tr style=\"background-color: $bg\">
     <td class=syslog width=160>
       " . $entry['datetime'] . "
     </td>
     <td class=list-bold width=125>
       ".$entry['user']."
     </td>
     <td class=syslog width=150>
       ".$entry['address']."
     </td>
     <td class=syslog width=150>
       ".$entry['result']."
     </td>
     <td></td>
   ");
  }

  echo("</table>");
}

?>