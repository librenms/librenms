<?php

if ($_GET['opta'] == "expunge" && $_SESSION['userlevel'] >= '10') { mysql_query("TRUNCATE TABLE `eventlog`"); }

if ($_SESSION['userlevel'] >= '5')
{
  $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,250";
} else {
  $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host = P.device_id AND P.user_id = ? ORDER BY `datetime` DESC LIMIT 0,250";
  $param = array($_SESSION['user_id']);
}

echo('<table cellspacing="0" cellpadding="1" width="100%">');

foreach (dbFetchRows($query, $param) as $entry)
{
  include("includes/print-event.inc.php");
}

echo("</table>");

?>
