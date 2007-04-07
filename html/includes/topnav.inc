<?php

$devices['count'] = mysql_result(mysql_query("SELECT count(id) FROM devices"),0);
$devices['up'] = mysql_result(mysql_query("SELECT count(id) FROM devices  WHERE status = '1' AND `ignore` = '0'"),0);
$devices['down'] = mysql_result(mysql_query("SELECT count(id) FROM devices WHERE status = '0' AND `ignore` = '0'"),0);
$devices['disabled'] = mysql_result(mysql_query("SELECT count(id) FROM devices WHERE `ignore` = '1'"),0);

$interfaces['count'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces"),0);
$interfaces['up'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces  WHERE ifOperStatus = 'up'"),0);
$interfaces['down'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces WHERE ifOperStatus = 'down' AND ifAdminStatus = 'up'"),0);
$interfaces['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces WHERE ifAdminStatus = 'down'"),0);

$services['count'] = mysql_result(mysql_query("SELECT count(service_id) FROM services"),0);
$services['up'] = mysql_result(mysql_query("SELECT count(service_id) FROM services  WHERE service_status = '1' AND service_ignore ='0'"),0);
$services['down'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'"),0);
$services['disabled'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_ignore = '1'"),0);

if($devices['down']) { $devices['bgcolour'] = "#ffcccc"; } else { $devices['bgcolour'] = ""; }
if($interfaces['down']) { $interfaces['bgcolour'] = "#ffcccc"; } else { $interfaces['bgcolour'] = "#e5e5e5"; }
if($services['down']) { $services['bgcolour'] = "#ffcccc"; } else { $services['bgcolour'] = ""; }



?>


<table cellpadding=2 cellspacing=0 border=0>
    <?php
      echo("<tr bgcolor=$devices[bgcolour]><td width=5></td>
            <td>Devices : 
            </td><td>$devices[count] 
            </td><td> ( <span class=green>$devices[up] up</span>
            </td><td><span class=red>$devices[down] down</span>
            </td><td><span class=grey>$devices[disabled]</span> ignored ) 
            </td><td width=5></td>");
      echo("</tr><tr bgcolor=$interfaces[bgcolour]>");
      echo("<td width=5></td><td>Interfaces : 
            </td><td>$interfaces[count]
            </td><td> ( <span class=green> $interfaces[up] up </span>
            </td><td><span class=red> $interfaces[down] down </span>
            </td><td><span class=grey> $interfaces[disabled] </span> disabled ) 
            </td><td width=5></td>");
      echo("</tr><tr bgcolor=$services[bgcolour]>");
      echo("<td width=5></td><td>Services : 
	    </td><td>$services[count]
            </td><td> ( <span class=green>$services[up] up</span>
            </td><td><span class=red>$services[down] down</span>
            </td><td><span class=grey>$services[disabled]</span> ignored ) 
            </td><td width=5></td>");
      echo("</tr>");
    ?>
    </td>
  </tr>
  <tr>
    <td align=left>
    </td>
  </tr>
</table>
