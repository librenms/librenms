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
        if (LegacyAuth::get()->authenticate(LegacyAuth::user()->username, $_POST['old_pass'])) {
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

    include 'includes/update-preferences-password.inc.php';

    if (LegacyAuth::get()->canUpdatePasswords(LegacyAuth::user()->username)) {
        echo '<h3>Change Password</h3>';
        echo '<hr>';
        echo "<div class='well'>";
        echo $changepass_message;
        echo "<form method='post' action='preferences/' class='form-horizontal' role='form'>
  <input type=hidden name='action' value='changepass'>
  <div class='form-group'>
    <label for='old_pass' class='col-sm-2 control-label'>Current Password</label>
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
      <br>
  <center><button type='submit' class='btn btn-default'>Submit</button></center>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>

</form>";
        echo '</div>';
    }//end if

    if ($config['twofactor'] === true) {
        $twofactor = get_user_pref('twofactor');
        echo '<script src="js/jquery.qrcode.min.js"></script>';
        echo '<h3>Two-Factor Authentication</h3>';
        echo '<hr>';
        echo '<div class="well">';
        if (!empty($twofactor)) {
            $twofactor['text'] = "<div class='form-group'>
  <label for='twofactorkey' class='col-sm-2 control-label'>Secret Key</label>
  <div class='col-sm-4'>
    <input type='text' name='twofactorkey' autocomplete='off' disabled class='form-control input-sm' value='".$twofactor['key']."' />
  </div>
</div>";
            if ($twofactor['counter'] !== false) {
                $twofactor['uri']   = 'otpauth://hotp/'.LegacyAuth::user()->username.'?issuer=LibreNMS&counter='.$twofactor['counter'].'&secret='.$twofactor['key'];
                $twofactor['text'] .= "<div class='form-group'>
  <label for='twofactorcounter' class='col-sm-2 control-label'>Counter</label>
  <div class='col-sm-4'>
    <input type='text' name='twofactorcounter' autocomplete='off' disabled class='form-control input-sm' value='".$twofactor['counter']."' />
  </div>
</div>";
            } else {
                $twofactor['uri'] = 'otpauth://totp/'.LegacyAuth::user()->username.'?issuer=LibreNMS&secret='.$twofactor['key'];
            }

            echo '<div id="twofactorqrcontainer">
<div id="twofactorqr"></div>
<button class="btn btn-default" onclick="$(\'#twofactorkeycontainer\').show(); $(\'#twofactorqrcontainer\').hide();">Manual</button>
</div>';
            echo '<div id="twofactorkeycontainer">
<form id="twofactorkey" class="form-horizontal" role="form">'.$twofactor['text'].'</form>
<button class="btn btn-default" onclick="$(\'#twofactorkeycontainer\').hide(); $(\'#twofactorqrcontainer\').show();">QR</button>
</div>';
            echo '<script>$("#twofactorqr").qrcode({"text": "'.$twofactor['uri'].'"}); $("#twofactorkeycontainer").hide();</script>';
            echo '<br/><form method="post" class="form-horizontal" role="form" action="2fa/remove">
  <button class="btn btn-danger" type="submit">Disable TwoFactor</button>
</form>';
        } else {
                echo '<form method="post" class="form-horizontal" role="form" action="2fa/add">
  <div class="form-group">
    <label for="twofactortype" class="col-sm-2 control-label">TwoFactor Type</label>
    <div class="col-sm-4">
      <select name="twofactortype" class="select">
        <option value="time">Time Based (TOTP)</option>
        <option value="counter">Counter Based (HOTP)</option>
      </select>
    </div>
  </div>
  <div class="form-group">
      <div class="col-sm-4 col-sm-offset-1">
        <button class="btn btn-default" type="submit">Generate TwoFactor Secret Key</button>
      </div>
  </div>
</form>';
        }//end if
        echo '</div>';
    }//end if
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
echo "
          </select>
          <br>
          <center><button type='submit' class='btn btn-default'>Update Dashboard</button></center>
        </div>
        <div class='col-sm-6'></div>
      </div>
    </div>
  </form>
</div>";


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
