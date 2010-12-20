<?php

  $ipmi_hostname = mres($_POST['ipmi_hostname']);
  $ipmi_username = mres($_POST['ipmi_username']);
  $ipmi_password = mres($_POST['ipmi_password']);

  #FIXME needs more sanity checking! and better feedback

  if ($ipmi_hostname != '') { set_dev_attrib($device, 'ipmi_hostname', $ipmi_hostname); } else { del_dev_attrib($device, 'ipmi_hostname'); }
  if ($ipmi_username != '') { set_dev_attrib($device, 'ipmi_username', $ipmi_username); } else { del_dev_attrib($device, 'ipmi_username'); }
  if ($ipmi_password != '') { set_dev_attrib($device, 'ipmi_password', $ipmi_password); } else { del_dev_attrib($device, 'ipmi_password'); }

  $update_message = "Device IPMI data updated.";
  $updated = 1;

?>
