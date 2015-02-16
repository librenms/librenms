<?php

if ($_SESSION['userlevel'] >= '10')
{
  echo("<table cellspacing=0 cellpadding=1 width=100%>");

  foreach (dbFetchRows("SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `authlog` ORDER BY `datetime` DESC LIMIT 0,250") as $entry)
  {
    if ($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

    echo("<tr style=\"background-color: $bg\">
     <td class=syslog width=160>
       " . $entry['datetime'] . "
     </td>
     <td class=list-bold width=125>
       ".$entry['user']."
     </td>
     <td class=syslog width=250>
       ".$entry['address']."
     </td>
     <td class=syslog width=150>
       ".$entry['result']."
     </td>
     <td></td>
   ");
  }

  $pagetitle[] = "Authlog";

  echo("</table>");
}
else
{
  include("includes/error-no-perm.inc.php");
}

?>
