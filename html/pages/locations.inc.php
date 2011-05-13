<?php
echo('<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">');

foreach (getlocations() as $location)
{
  if (!isset($bg) || $bg == "#ffffff") { $bg = "#eeeeee"; } else { $bg="#ffffff"; }

  if ($_SESSION['userlevel'] == '10')
  {
    $num = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = '" . $location . "'");
    $net = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = '" . $location . "' AND type = 'network'");
    $srv = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = '" . $location . "' AND type = 'server'");
    $fwl = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = '" . $location . "' AND type = 'firewall'");
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = '" . $location . "' AND status = '0'");
  } else {
    $num = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $location . "'");
    $net = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $location . "' AND D.type = 'network'");
    $srv = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $location . "' AND type = 'server'");
    $fwl = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND location = '" . $location . "' AND type = 'firewall'");
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices AS D, devices_perms AS P WHERE location = '" . $location . "' AND status = '0'");
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
