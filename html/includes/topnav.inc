<?php

if($_SESSION['userlevel'] >= '5') {

$devices['count'] = mysql_result(mysql_query("SELECT count(*) FROM devices"),0);
$devices['up'] = mysql_result(mysql_query("SELECT count(*) FROM devices  WHERE status = '1' AND `ignore` = '0'"),0);
$devices['down'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE status = '0' AND `ignore` = '0'"),0);
$devices['ignored'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE `ignore` = '1'"),0);
$devices['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM devices WHERE `disabled` = '1'"),0);

$interfaces['count'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces"),0);
$interfaces['up'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D  WHERE I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'"),0);
$interfaces['down'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D WHERE I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"),0);
$interfaces['shutdown'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D WHERE I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"),0);
$interfaces['ignored'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D WHERE D.device_id = I.device_id AND ( I.ignore = '1' OR D.ignore = '1')"),0);
$interfaces['errored'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D WHERE D.device_id = I.device_id AND ( I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')"),0);


$services['count'] = mysql_result(mysql_query("SELECT count(service_id) FROM services"),0);
$services['up'] = mysql_result(mysql_query("SELECT count(service_id) FROM services  WHERE service_status = '1' AND service_ignore ='0'"),0);
$services['down'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'"),0);
$services['ignored'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_ignore = '1'"),0);
$services['disabled'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_disabled = '1'"),0);

} else {

  $devices['count']       = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id"),0);
  $devices['up']          = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.status = '1' AND D.ignore = '0'"),0);
  $devices['down']        = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.status = '0' AND D.ignore = '0'"),0);
  $devices['disabled']    = mysql_result(mysql_query("SELECT count(D.device_id) FROM devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND D.ignore = '1'"),0);

  $interfaces['count']    = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id"),0);
  $interfaces['up']       = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifOperStatus = 'up'"),0);
  $interfaces['down']     = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up'"),0);
  $interfaces['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND ifAdminStatus = 'down'"),0);
  $interfaces['errored'] = mysql_result(mysql_query("SELECT count(*) FROM interfaces AS I, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND I.device_id = D.device_id AND (I.in_errors > '0' OR I.out_errors > '0')"),0);

  $services['count']      = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.service_host = D.device_id"),0);
  $services['up']         = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.service_host = D.device_id AND service_status = '1' AND service_ignore ='0'"),0);
  $services['down']       = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.service_host = D.device_id AND service_status = '0' AND service_ignore = '0'"),0);
  $services['disabled']   = mysql_result(mysql_query("SELECT count(service_id) FROM services AS S, devices AS D, devices_perms AS P WHERE P.user_id = '" . $_SESSION['user_id'] . "' AND P.device_id = D.device_id AND S.service_host = D.device_id AND service_ignore = '1'"),0);

}

if($devices['down']) { $devices['bgcolour'] = "#ffcccc"; } else { $devices['bgcolour'] = ""; }
if($interfaces['down']) { $interfaces['bgcolour'] = "#ffcccc"; } else { $interfaces['bgcolour'] = "#e5e5e5"; }
if($services['down']) { $services['bgcolour'] = "#ffcccc"; } else { $services['bgcolour'] = ""; }

?>


<table cellpadding="2" cellspacing="0" border="0">
<tr bgcolor="<?php echo $devices[bgcolour] ?>"><td width="5"></td>
            <td>Devices : 
            </td><td><?php echo $devices['count'] ?>
            </td><td> ( <span class=green> <?php echo $devices['up'] ?> up</span>
            </td><td><span class="red"> <?php echo $devices['down'] ?> down</span>
            </td><td><span class="black"> <?php echo $devices['ignored'] ?> ignored</span> 
            </td><td><span class="grey"> <?php echo $devices['disabled'] ?> disabled</span> ) 

            </td><td width=5></td>
      </tr><tr bgcolor="<?php echo $interfaces['bgcolour'] ?>">
      <td width="5"></td><td>Interfaces : 
            </td><td><?php echo $interfaces['count'] ?>
            </td><td> ( <span class=green> <?php echo $interfaces['up'] ?> up </span>
            </td><td><span class="red"> <?php echo $interfaces['down'] ?> down </span>
            </td><td><span class="black"> <?php echo $interfaces['ignored'] ?> ignored </span>
            </td><td><span class="grey"> <?php echo $interfaces['shutdown'] ?> shutdown</span> ) 
            </td><td width=5></td>
      </tr><tr bgcolor="<?php echo $services['bgcolour'] ?>">
      <td width="5"></td><td>Services : 
	    </td><td><?php echo $services['count'] ?>
            </td><td> ( <span class=green><?php echo $services['up'] ?> up</span>
            </td><td><span class="red"> <?php echo $services['down'] ?> down</span>
            </td><td><span class="black"> <?php echo $services['ignored'] ?> ignored</span> 
            </td><td><span class="grey"> <?php echo $services['disabled'] ?> disabled</span> ) 
            </td><td width="5"></td>
      </tr>
    </td>
  </tr>
  <tr>
</table>
