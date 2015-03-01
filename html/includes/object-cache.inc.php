<?php

// FIXME queries such as the one below should probably go into index.php?

// FIXME: This appears to keep a complete cache of device details in memory for every page load.
// It would be interesting to know where this is used.  It probably should have its own API.

foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
{
  if (get_dev_attrib($device,'override_sysLocation_bool'))
  {
    $device['real_location'] = $device['location'];
    $device['location'] = get_dev_attrib($device,'override_sysLocation_string');
  }

  $cache['devices']['hostname'][$device['hostname']] = $device['device_id'];
  $cache['devices']['id'][$device['device_id']] = $device;

  $cache['device_types'][$device['type']]++;
}

if($_SESSION['userlevel'] >= 5)
{
  $devices['count']     = dbFetchCell("SELECT COUNT(*) FROM devices");
  $devices['up']        = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `status` = '1' AND `ignore` = '0'  AND `disabled` = '0'");
  $devices['down']      = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `status` = '0' AND `ignore` = '0'  AND `disabled` = '0'");
  $devices['ignored']   = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `ignore` = '1' AND `disabled` = '0'");
  $devices['disabled']  = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `disabled` = '1'");

  $ports['count']       = dbFetchCell("SELECT COUNT(*) FROM ports WHERE `deleted` = '0'");
  $ports['up']          = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'");
  $ports['down']        = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'down' AND I.`ifAdminStatus` = 'up'");
  $ports['shutdown']    = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'");
  $ports['errored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')");
  $ports['ignored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')");

  $services['count']    = dbFetchCell("SELECT COUNT(*) FROM services");
  $services['up']       = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '1'");
  $services['down']     = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '0'");
  $services['ignored']  = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '1' AND `service_disabled` = '0'");
  $services['disabled'] = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_disabled` = '1'");
}
else
{
  $devices['count']     = dbFetchCell("SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id`", array($_SESSION['user_id']));
  $devices['up']        = dbFetchCell("SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`status` = '1' AND D.`ignore` = '0' AND D.`disabled` = '0'", array($_SESSION['user_id']));
  $devices['down']      = dbFetchCell("SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`status` = '0' AND D.`ignore` = '0' AND D.`disabled` = '0'", array($_SESSION['user_id']));
  $devices['ignored']   = dbFetchCell("SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`ignore` = '1' AND D.`disabled` = '0'", array($_SESSION['user_id']));
  $devices['disabled']  = dbFetchCell("SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`disabled` = '1'", array($_SESSION['user_id']));

  $ports['count']       = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id`", array($_SESSION['user_id']));
  $ports['up']          = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'", array($_SESSION['user_id']));
  $ports['down']        = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'down' AND I.`ifAdminStatus` = 'up'", array($_SESSION['user_id']));
  $ports['shutdown']    = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'", array($_SESSION['user_id']));
  $ports['errored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')", array($_SESSION['user_id']));
  $ports['ignored']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')", array($_SESSION['user_id']));

  $services['count']    = dbFetchCell("SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id`", array($_SESSION['user_id']));
  $services['up']       = dbFetchCell("SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '1'", array($_SESSION['user_id']));
  $services['down']     = dbFetchCell("SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '0'", array($_SESSION['user_id']));
  $services['ignored']  = dbFetchCell("SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '1' AND S.`service_disabled` = '0'", array($_SESSION['user_id']));
  $services['disabled'] = dbFetchCell("SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_disabled` = '1'", array($_SESSION['user_id']));
}

if ($devices['down'])  { $devices['bgcolour'] = "#ffcccc"; } else { $devices['bgcolour'] = "transparent"; }
if ($ports['down'])    { $ports['bgcolour'] = "#ffcccc"; } else { $ports['bgcolour'] = "#e5e5e5"; }
if ($services['down']) { $services['bgcolour'] = "#ffcccc"; } else { $services['bgcolour'] = "transparent"; }

?>
