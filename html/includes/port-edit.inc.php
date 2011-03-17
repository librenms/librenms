<?php

# enable/disable ports/interfaces on devices.

$device_id = intval($_POST['device']);
$rows_updated = 0;

foreach ($_POST as $key => $val)
{
  if (strncmp($key,"oldval_",7) == 0)
  {
    # Interface identifier passed as part of the field name

    $interface_id = intval(substr($key,7));

    $oldval = intval($val) ? 1 : 0;
    $newval = $_POST['ignore_'.$interface_id] ? 1 : 0;

    # As checkboxes are not posted when unset - we effectively need to do a diff to work
    # out a set->unset case.

    if ($oldval == $newval)
    {
      continue;
    }

    if (!mysql_query('UPDATE `ports` SET `ignore`='.$newval.' WHERE `device_id`='.$device_id.' AND `interface_id`='.$interface_id))
    {
      $n = -1;
    }
    else
    {
      $n = mysql_affected_rows();
    }

    if ($n <0)
    {
      $rows_updated = -1;
      break;
    }

    $rows_updated += $n;
  }
}

if ($rows_updated > 0)
{
  $update_message =  $rows_updated . " Device record updated.";
  $updated = 1;
} elseif ($rows_updated = '-1') {
  $update_message = "Device record unchanged. No update necessary.";
  $updated = -1;
} else {
  $update_message = "Device record update error.";
  $updated = 0;
}

?>