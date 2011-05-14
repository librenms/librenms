<?php
echo('<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">');

foreach (getlocations() as $location)
{
  if (!isset($bg) || $bg == "#ffffff") { $bg = "#eeeeee"; } else { $bg="#ffffff"; }

  if ($_SESSION['userlevel'] == '10')
  {
    $num = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ?", array($location));
    $net = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'network'", array($location));
    $srv = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'server'", array($location));
    $fwl = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'firewall'", array($location));
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND status = '0'", array($location));
  } else {
    $num = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ?", array($_SESSION['user_id'], $location));
    $net = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND D.type = 'network'", array($_SESSION['user_id'], $location));
    $srv = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND type = 'server'", array($_SESSION['user_id'], $location));
    $fwl = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND type = 'firewall'", array($_SESSION['user_id'], $location));
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND status = '0'", array($_SESSION['user_id'], $location));
  }

  if ($hostalerts) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }

  if ($location != "")
  {
    echo('      <tr bgcolor="' . $bg . '">
             <td class="interface" width="300"><a class="list-bold" href="?page=devices&amp;location=' . urlencode($location) . '">' . $location . '</a></td>
             <td width="100">' . $alert . '</td>
             <td width="100">' . $num . ' devices</td>
             <td width="100">' . $net . ' network</td>
	     <td width="100">' . $srv . ' servers</td>
             <td width="100">' . $fwl . ' firewalls</td>
           </tr>
         ');

    $done = "yes";
  }
}

echo("</table>");

?>
