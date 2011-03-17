<?php

if($_SESSION['userlevel'] >= 5)
{
  $devices['count'] = mysql_result(mysql_query("SELECT count(*) FROM devices"),0);
  $devices['up'] = mysql_result(mysql_query("SELECT count(*) FROM devices  WHERE status = '1' AND `ignore` = '0'"),0);
  $devices['down'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE status = '0' AND `ignore` = '0'"),0);
  $devices['ignored'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE `ignore` = '1'"),0);
  $devices['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE `disabled` = '1'"),0);

  $ports['count'] = mysql_result(mysql_query("SELECT count(*) FROM ports"),0);
  $ports['up'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D  WHERE I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'"),0);
  $ports['down'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"),0);
  $ports['shutdown'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"),0);
  $ports['ignored'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND ( I.ignore = '1' OR D.ignore = '1')"),0);
  $ports['errored'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND ( I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')"),0);

  $services['count'] = mysql_result(mysql_query("SELECT count(service_id) FROM services"),0);
  $services['up'] = mysql_result(mysql_query("SELECT count(service_id) FROM services  WHERE service_status = '1' AND service_ignore ='0'"),0);
  $services['down'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'"),0);
  $services['ignored'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_ignore = '1'"),0);
  $services['disabled'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_disabled = '1'"),0);
}
else
{
  $devices['count']       = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id"),0);
  $devices['up']          = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.status = '1' AND D.ignore = '0'"),0);
  $devices['down']        = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.status = '0' AND D.ignore = '0'"),0);
  $devices['disabled']    = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.ignore = '1'"),0);

  $ports['count']    = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id"),0);
  $ports['up']       = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifOperStatus = 'up'"),0);
  $ports['down']     = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up'"),0);
  $ports['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifAdminStatus = 'down'"),0);
  $ports['errored'] = mysql_result(mysql_query("SELECT count(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND (I.in_errors > '0' OR I.out_errors > '0')"),0);

  $services['count']      = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.device_id = D.device_id"),0);
  $services['up']         = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.device_id = D.device_id AND service_status = '1' AND service_ignore ='0'"),0);
  $services['down']       = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.device_id = D.device_id AND service_status = '0' AND service_ignore = '0'"),0);
  $services['disabled']   = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.device_id = D.device_id AND service_ignore = '1'"),0);
}

if ($devices['down'])  { $devices['bgcolour'] = "#ffcccc"; } else { $devices['bgcolour'] = "transparent"; }
if ($ports['down'])    { $ports['bgcolour'] = "#ffcccc"; } else { $ports['bgcolour'] = "#e5e5e5"; }
if ($services['down']) { $services['bgcolour'] = "#ffcccc"; } else { $services['bgcolour'] = "transparent"; }

?>

<table cellpadding="2" cellspacing="0" border="0">
  <tr style="background-color: <?php echo($devices[bgcolour]); ?>">
    <td width="5"></td>
    <td>Devices : </td>
    <td><?php echo($devices['count']) ?></td>
    <td> ( </td>
    <td style="text-align: right"><span class="green"> <?php echo($devices['up']) ?> up</span></td>
    <td style="text-align: right"><span class="red"> <?php echo($devices['down']) ?> down</span></td>
    <td style="text-align: right"><span class="black"> <?php echo($devices['ignored']) ?> ignored</span> </td>
    <td style="text-align: right"><span class="grey"> <?php echo($devices['disabled']) ?> disabled</span></td>
    <td> ) </td>
    <td width="5"></td>
  </tr>
  <tr style="background-color: <?php echo($ports['bgcolour']) ?>">
      <td width="5"></td><td>Ports : </td>
    <td><?php echo($ports['count']) ?></td>
    <td> ( </td>
    <td style="text-align: right"><span class="green"> <?php echo($ports['up']) ?> up </span></td>
    <td style="text-align: right"><span class="red"> <?php echo($ports['down']) ?> down </span></td>
    <td style="text-align: right"><span class="black"> <?php echo($ports['ignored']) ?> ignored </span></td>
    <td style="text-align: right"><span class="grey"> <?php echo($ports['shutdown']) ?> shutdown</span></td>
    <td> ) </td>
    <td width="5"></td>
  </tr>
<?php if ($config['show_services']) { ?>
  <tr style="background-color: <?php echo($services['bgcolour']) ?>">
    <td width="5"></td>
    <td>Services : </td>
    <td><?php echo($services['count']) ?></td>
    <td> ( </td>
    <td style="text-align: right"><span class="green"><?php echo($services['up']) ?> up</span></td>
    <td style="text-align: right"><span class="red"> <?php echo($services['down']) ?> down</span></td>
    <td style="text-align: right"><span class="black"> <?php echo($services['ignored']) ?> ignored</span> </td>
    <td style="text-align: right"><span class="grey"> <?php echo($services['disabled']) ?> disabled</span></td>
    <td> ) </td>
    <td width="5"></td>
  </tr>
<?php } ?>
</table>
