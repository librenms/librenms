<?php

$pagetitle[] = "Preferences";

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

echo("<div class='well'>");

if (passwordscanchange($_SESSION['username']))
{
  echo("<h3>Change Password</h3>");
  echo($changepass_message);
  echo("<form method='post' action='preferences/' class='form-horizontal' role='form'>
  <input type=hidden name='action' value='changepass'>
  <div class='form-group'>
    <label for='old_pass' class='col-sm-2 control-label'>Old Password</label>
    <div class='col-sm-4'>
      <input type=password name=old_pass autocomplete='off' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_pass' class='col-sm-2 control-label'>New Password</label>
    <div class='col-sm-4'>
      <input type=password name=new_pass autocomplete='off' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_pass2' class='col-sm-2 control-label'>New Password</label>
    <div class='col-sm-4'>
      <input type=password name=new_pass2 autocomplete='off' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <button type='submit' class='btn btn-default'>Submit</button>
</form>");
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

?>
