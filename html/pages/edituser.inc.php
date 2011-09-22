<?php

include("includes/javascript-interfacepicker.inc.php");

echo("<div style='margin: 10px;'>");

if ($_SESSION['userlevel'] != '10') { include("includes/error-no-perm.inc.php"); } else
{
  if ($vars['user_id'])
  {
    $user_data = dbFetchRow("SELECT * FROM users WHERE user_id = ?", array($vars['user_id']));
      echo("<p><h2>" . $user_data['realname'] . "</h2><a href='edituser/'>Change...</a></p>");
    // Perform actions if requested

    if ($vars['action'] == "deldevperm")
    {
      if (dbFetchCell("SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?", array($vars['device_id'] ,$vars['user_id'])))
      {
        dbDelete('devices_perms', "`device_id` =  ? AND `user_id` = ?", array($vars['device_id'], $vars['user_id']));
      }
    }
    if ($vars['action'] == "adddevperm")
    {
      if (!dbFetchCell("SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?", array($vars['device_id'] ,$vars['user_id'])))
      {
        dbInsert(array('device_id' => $vars['device_id'], 'user_id' => $vars['user_id']), 'devices_perms');
      }
    }
    if ($vars['action'] == "delifperm")
    {
      if (dbFetchCell("SELECT COUNT(*) FROM ports_perms WHERE `interface_id` = ? AND `user_id` = ?", array($vars['interface_id'], $vars['user_id'])))
      {
        dbDelete('ports_perms', "`interface_id` =  ? AND `user_id` = ?", array($vars['interface_id'], $vars['user_id']));
      }
    }
    if ($vars['action'] == "addifperm")
    {
      if (!dbFetchCell("SELECT COUNT(*) FROM ports_perms WHERE `interface_id` = ? AND `user_id` = ?", array($vars['interface_id'], $vars['user_id'])))
      {
        dbInsert(array('interface_id' => $vars['interface_id'], 'user_id' => $vars['user_id']), 'ports_perms');
      }
    }
    if ($vars['action'] == "delbillperm")
    {
      if (dbFetchCell("SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?", array($vars['bill_id'], $vars['user_id'])))
      {
        dbDelete('bill_perms', "`bill_id` =  ? AND `user_id` = ?", array($vars['bill_id'], $vars['user_id']));
      }
    }
    if ($vars['action'] == "addbillperm")
    {
      if (!dbFetchCell("SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?", array($vars['bill_id'], $vars['user_id'])))
      {
        dbInsert(array('bill_id' => $vars['bill_id'], 'user_id' => $vars['user_id']), 'bill_perms');
      }
    }

    echo("<table width=100%><tr><td valign=top width=33%>");

    // Display devices this users has access to
    echo("<h3>Device Access</h3>");

    $device_perms = dbFetchRows("SELECT * from devices_perms as P, devices as D WHERE `user_id` = ? AND D.device_id = P.device_id", array($vars['user_id']));
    foreach ($device_perms as $device_perm)
    {
      echo("<strong>" . $device_perm['hostname'] . " <a href='edituser/action=deldevperm/user_id=" . $vars['user_id'] . "/device_id=" . $device_perm['device_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a></strong><br />");
      $access_list[] = $device_perm['device_id'];
      $permdone = "yes";
    }

    if (!$permdone) { echo("None Configured"); }

    // Display devices this user doesn't have access to
    echo("<h4>Grant access to new device</h4>");
    echo("<form method='post' action=''>
            <input type='hidden' value='" . $vars['user_id'] . "' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='adddevperm' name='action'>
            <select name='device_id' class=selector>");

    $devices = dbFetchRows("SELECT * FROM `devices` ORDER BY hostname");
    foreach ($devices as $device)
    {
      unset($done);
      foreach ($access_list as $ac) { if ($ac == $device['device_id']) { $done = 1; } }
      if (!$done)
      {
        echo("<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>");
      }
    }

    echo("</select> <input type='submit' name='Submit' value='Add'></form>");

    echo("</td><td valign=top width=33%>");
    echo("<h3>Interface Access</h3>");

    $interface_perms = dbFetchRows("SELECT * from ports_perms as P, ports as I, devices as D WHERE `user_id` = ? AND I.interface_id = P.interface_id AND D.device_id = I.device_id", array($vars['user_id']));

    foreach ($interface_perms as $interface_perm)
    {
      echo("<table><tr><td><strong>".$interface_perm['hostname']." - ".$interface_perm['ifDescr']."</strong><br />".
                  "" . $interface_perm['ifAlias'] . "</td><td width=50>&nbsp;&nbsp;<a href='edituser/action=delifperm/user_id=" . $vars['user_id'] .
       "/interface_id=" . $interface_perm['interface_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a></td></tr></table>");
      $ipermdone = "yes";
    }

    if (!$ipermdone) { echo("None Configured"); }

    // Display devices this user doesn't have access to
    echo("<h4>Grant access to new interface</h4>");

    echo("<form action='' method='post'>
        <input type='hidden' value='" . $vars['user_id'] . "' name='user_id'>
        <input type='hidden' value='edituser' name='page'>
        <input type='hidden' value='addifperm' name='action'>
        <table><tr><td>Device: </td>
         <td><select id='device' class='selector' name='device' onchange='getInterfaceList(this)'>
          <option value=''>Select a device</option>");

    foreach ($devices as $device)
    {
      unset($done);
      foreach ($access_list as $ac) { if ($ac == $device['device_id']) { $done = 1; } }
      if (!$done) { echo("<option value='" . $device['device_id']  . "'>" . $device['hostname'] . "</option>"); }
    }

    echo("</select></td></tr><tr>
       <td>Interface: </td><td><select class=selector id='interface_id' name='interface_id'>
       </select></td>
       </tr><tr></table><input type='submit' name='Submit' value='Add'></form>");

    echo("</td><td valign=top width=33%>");
    echo("<h3>Bill Access</h3>");

    $bill_perms = dbFetchRows("SELECT * from bills AS B, bill_perms AS P WHERE P.user_id = ? AND P.bill_id = B.bill_id", array($vars['user_id']));

    foreach ($bill_perms as $bill_perm)
    {
      echo("<table><tr><td><strong>".$bill_perm['bill_name']."</strong></td><td width=50>&nbsp;&nbsp;<a href='edituser/action=delbillperm/user_id=" .
        $vars['user_id'] . "/bill_id=" . $bill_perm['bill_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a></td></tr></table>");
      $bill_access_list[] = $bill_perm['bill_id'];

      $bpermdone = "yes";
    }

    if (!$bpermdone) { echo("None Configured"); }

    // Display devices this user doesn't have access to
    echo("<h4>Grant access to new bill</h4>");
    echo("<form method='post' action=''>
            <input type='hidden' value='" . $vars['user_id'] . "' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='addbillperm' name='action'>
            <select name='bill_id' class=selector>");

    $bills = dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`");
    foreach ($bills as $bill)
    {
      unset($done);
      foreach ($bill_access_list as $ac) { if ($ac == $bill['bill_id']) { $done = 1; } }
      if (!$done)
      {
        echo("<option value='" . $bill['bill_id'] . "'>" . $bill['bill_name'] . "</option>");
      }
    }

    echo("</select> <input type='submit' name='Submit' value='Add'></form>");
    echo("</td></table>");

  } else {

    $user_list = get_userlist();

    echo("<h3>Select a user to edit</h3>");

    echo("<form method='post' action=''>
            <input type='hidden' value='edituser' name='page'>
            <select name='user_id'>");
    foreach($user_list as $user_entry)
    {
      echo("<option value='" . $user_entry['user_id']  . "'>" . $user_entry['username'] . "</option>");
    }
    echo("</select><input type='submit' name='Submit' value='Select'></form>");
  }

}

echo("</div>");

?>
