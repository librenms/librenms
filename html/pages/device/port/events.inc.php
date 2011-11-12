<?php

$entries = dbFetchRows("SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` WHERE `host` = ? AND `type` = 'interface' AND `reference` = '".$port['interface_id']."' ORDER BY `datetime` DESC LIMIT 0,250", array($device['device_id']));
echo('<table cellspacing="0" cellpadding="2" width="100%">');

foreach ($entries as $entry)
{
  include("includes/print-event.inc.php");
}

echo('</table>');

$pagetitle[] = "Events";


?>
