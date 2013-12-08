<?php

  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (device_permitted($device['device_id']))
    {
      if (!$location_filter || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
        || $device['location'] == $location_filter))
      {

        include("includes/hostbox.inc.php");
      }
    }
  }
?>