<?php


  echo('<table cellspacing="0" class="devicetable sortable" width="100%" border="0">');
  echo('<tr class="tablehead">
    <th></th>
    <th></th>
    <th class="paddedcell">Device</th>
    <th></th>
    <th class="paddedcell">Platform</th>
    <th class="paddedcell">Operating System</th>
    <th class="paddedcell">Uptime/Location</th>
  </tr>');

  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (device_permitted($device['device_id']))
    {
      if (!$location_filter || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
        || $device['location'] == $location_filter))
      {

        //include("includes/hostbox-basic.inc.php");
        includes("includes/hostbox-details.inc.php");
      }
    }
  }

  echo('</table>');
?>