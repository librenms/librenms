<?php

if($_SESSION['userlevel'] >= '5') {
  $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,250";
} else {
  $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host = P.device_id AND P.user_id = " . $_SESSION['user_id'] . " ORDER BY `datetime` DESC LIMIT 0,250";
}

$data = mysql_query($query);


echo('<table cellspacing="0" cellpadding="1" width="100%">');

while($entry = mysql_fetch_array($data)) {
  include("includes/print-event.inc");
}

echo("</table>");

?>
