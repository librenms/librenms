<?php

if($device['uptime'] < '86400') { $uptime_colour = $warn_colour_a; }

echo("
<table width=100% cellspacing=0 cellpadding=0>
  <tr><td class=tablehead>Operating System</td><td>$device[os] $device[version] ($device[features])</td></tr>
  <tr><td class=tablehead>Hardware        </td><td>$device[hardware]</td></tr>
  <tr><td class=tablehead>Uptime          </td><td bgcolor=$uptime_colour>" . formatuptime($device[uptime]) . "</td></tr>
</table>
");


?>
