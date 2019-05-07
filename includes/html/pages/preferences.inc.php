<?php

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Authentication\TwoFactor;

$no_refresh = true;

$pagetitle[] = 'Preferences';

echo '<h2>User Preferences</h2>';
echo '<hr>';

if (LegacyAuth::user()->isDemoUser()) {
    demo_account();
} else {
    if ($_POST['action'] == 'changepass') {
        if (LegacyAuth::get()->authenticate(['username' => LegacyAuth::user()->username, 'password' => $_POST['old_pass']])) {
            if ($_POST['new_pass'] == '' || $_POST['new_pass2'] == '') {
                $changepass_message = 'Password must not be blank.';
            } elseif ($_POST['new_pass'] == $_POST['new_pass2']) {
                LegacyAuth::get()->changePassword(LegacyAuth::user()->username, $_POST['new_pass']);
                $changepass_message = 'Password Changed.';
            } else {
                $changepass_message = "Passwords don't match.";
            }
        } else {
            $changepass_message = 'Incorrect password';
        }
    }
    if ($vars['action'] === 'changedash') {
        if (!empty($vars['dashboard'])) {
            set_user_pref('dashboard', (int)$vars['dashboard']);
            $updatedashboard_message = "User default dashboard updated";
        }
    }
    if ($vars['action'] === 'changenote') {
        set_user_pref('add_schedule_note_to_device', (bool)$vars['notetodevice']);
        if ($vars['notetodevice']) {
            $updatenote_message = "Schedule notes will now be added to device notes";
        } else {
            $updatenote_message = "Schedule notes will no longer be added to device notes";
        }
    }

}//end if

echo "<h3>Default Dashboard</h3>
<hr>
<div class='well'>";
if (!empty($updatedashboard_message)) {
    print_message($updatedashboard_message);
}
echo "
  <form method='post' action='preferences/' class='form-horizontal' role='form'>
    <div class='form-group'>
      <input type=hidden name='action' value='changedash'>
      <div class='form-group'>
        <label for='dashboard' class='col-sm-2 control-label'>Dashboard</label>
        <div class='col-sm-4'>
          <select class='form-control' name='dashboard'>";
foreach (get_dashboards() as $dash) {
    echo "
            <option value='".$dash['dashboard_id']."'".($dash['default'] ? ' selected' : '').">".display($dash['username']).':'.display($dash['dashboard_name'])."</option>";
}
echo '
          </select>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-4 col-sm-offset-2"><button type="submit" class="btn btn-default">Update Dashboard</button></div>
      </div>
    </div>
  </form>
</div>';


echo "<h3>Add schedule notes to devices notes</h3>
<hr>
<div class='well'>";
if (!empty($updatenote_message)) {
    print_message($updatenote_message);
}
echo "
  <form method='post' action='preferences/' class='form-horizontal' role='form'>
    <div class='form-group'>
      <input type=hidden name='action' value='changenote'>
      <div class='form-group'>
        <label for='dashboard' class='col-sm-3 control-label'>Add schedule notes to devices notes</label>
        <div class='col-sm-4'>
          <input id='notetodevice' type='checkbox' name='notetodevice' data-size='small' " . ((get_user_pref('add_schedule_note_to_device', false)) ? 'checked' : '') . ">
        </div>
      </div>
      <div class='form-group'>
          <div class='col-sm-4 col-sm-offset-3'>
              <button type='submit' class='btn btn-default'>Update preferences</button>
        </div>
        <div class='col-sm-6'></div>
      </div>
    </div>
  </form>
</div>";



echo "<h3>Device Permissions</h3>";
echo "<hr>";
echo '<div class="well">';
if (LegacyAuth::user()->hasGlobalAdmin()) {
    echo "<strong class='blue'>Global Administrative Access</strong>";
} elseif (LegacyAuth::user()->hasGlobalRead()) {
    echo "<strong class='green'>Global Viewing Access</strong>";
} else {
    foreach (dbFetchRows('SELECT * FROM `devices_perms` AS P, `devices` AS D WHERE `user_id` = ? AND P.device_id = D.device_id', array(LegacyAuth::id())) as $perm) {
    // FIXME generatedevicelink?
        echo "<a href='device/device=".$perm['device_id']."'>".$perm['hostname'].'</a><br />';
        $dev_access = 1;
    }

    if (!$dev_access) {
        echo 'No access!';
    }
}

echo '</div>';

echo "<script>$(\"[name='notetodevice']\").bootstrapSwitch('offColor','danger');</script>";
