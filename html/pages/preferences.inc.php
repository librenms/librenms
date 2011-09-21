<?php
echo("<div style='margin: 10px'>");
echo("<h3>User Preferences</h3>");

if ($_POST['action'] == "changepass")
{
  if (authenticate($_SESSION['username'],$_POST['old_pass']))
  {
    if ($_POST['new_pass'] == "" || $_POST['new_pass2'] == "")
    {
      $changepass_message = "Password must not be blank.";
    }
    elseif ($_POST['new_pass'] == $_POST['new_pass2'])
    {
      changepassword($_SESSION['username'],$_POST['new_pass']);
      $changepass_message = "Password Changed.";
    }
    else
    {
      $changepass_message = "Passwords don't match.";
    }
  } else {
    $changepass_message = "Incorrect password";
  }
}

include("includes/update-preferences-password.inc.php");

echo("<div style='width: 800px; background-color: #fff; padding:5px; margin-bottom:10px; float:left;'>");
echo("</div>");

echo("<div style='width: 300px; float: right;'>");
echo("<div style='background-color: #e5e5e5; border: solid #e5e5e5 10px; margin-bottom:10px;'>");

if (passwordscanchange($_SESSION['username']))
{
  echo("<div style='font-size: 18px; font-weight: bold; margin-bottom: 5px;'>Change Password</div>");
  echo($changepass_message);
  echo("<form method='post' action='preferences/'><input type=hidden name='action' value='changepass'>
        <table>
        <tr><td>Old Password</td><td><input type=password name=old_pass autocomplete='off'></input></td></tr>
        <tr><td>New Password</td><td><input type=password name=new_pass autocomplete='off'></input></td></tr>
        <tr><td>New Password</td><td><input type=password name=new_pass2 autocomplete='off'></input></td></tr>
        <tr><td></td><td align=right><input type=submit class=submit></td></tr></table></form>");
  echo("</div>");
}

echo("<div style='background-color: #e5e5e5; border: solid #e5e5e5 10px;  margin-bottom:10px;'>");
echo("<div style='font-size: 18px; font-weight: bold; margin-bottom: 5px;'>Device Permissions</div>");

if ($_SESSION['userlevel'] == '10') { echo("<strong class='blue'>Global Administrative Access</strong>"); }
if ($_SESSION['userlevel'] == '5')  { echo("<strong class='green'>Global Viewing Access</strong>"); }
if ($_SESSION['userlevel'] == '1')
{

  foreach (dbFetchRows("SELECT * FROM `devices_perms` AS P, `devices` AS D WHERE `user_id` = ? AND P.device_id = D.device_id", array($user_id)) as $perm)
  {
   #FIXME generatedevicelink?
    echo("<a href='device/device" . $perm['device_id'] . "'>" . $perm['hostname'] . "</a><br />");
    $dev_access = 1;
  }

  if (!$dev_access) { echo("No access!"); }
}

echo("</div>");
echo("</div>");
echo("</div>");

?>
