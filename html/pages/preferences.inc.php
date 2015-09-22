<?php

$no_refresh = true;

$pagetitle[] = 'Preferences';

echo '<h2>User Preferences</h2>';
echo '<hr>';

if ($_SESSION['userlevel'] == 11) {
    demo_account();
}
else {
    if ($_POST['action'] == 'changepass') {
        if (authenticate($_SESSION['username'], $_POST['old_pass'])) {
            if ($_POST['new_pass'] == '' || $_POST['new_pass2'] == '') {
                $changepass_message = 'Password must not be blank.';
            }
            else if ($_POST['new_pass'] == $_POST['new_pass2']) {
                changepassword($_SESSION['username'], $_POST['new_pass']);
                $changepass_message = 'Password Changed.';
            }
            else {
                $changepass_message = "Passwords don't match.";
            }
        }
        else {
            $changepass_message = 'Incorrect password';
        }
    }

    include 'includes/update-preferences-password.inc.php';

    

    if (passwordscanchange($_SESSION['username'])) {
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
        if ($_POST['twofactorremove'] == 1) {
            include_once $config['install_dir'].'/html/includes/authentication/twofactor.lib.php';
            if (!isset($_POST['twofactor'])) {
                echo '<div class="well"><form class="form-horizontal" role="form" action="" method="post" name="twofactorform">';
                echo '<input type="hidden" name="twofactorremove" value="1" />';
                echo twofactor_form(false);
                echo '</form></div>';
            }
            else {
                $twofactor = dbFetchRow('SELECT twofactor FROM users WHERE username = ?', array($_SESSION['username']));
                if (empty($twofactor['twofactor'])) {
                    echo '<div class="alert alert-danger">Error: How did you even get here?!</div><script>window.location = "preferences/";</script>';
                }
                else {
                    $twofactor = json_decode($twofactor['twofactor'], true);
                }

                if (verify_hotp($twofactor['key'], $_POST['twofactor'], $twofactor['counter'])) {
                    if (!dbUpdate(array('twofactor' => ''), 'users', 'username = ?', array($_SESSION['username']))) {
                        echo '<div class="alert alert-danger">Error while disabling TwoFactor.</div>';
                    }
                    else {
                        echo '<div class="alert alert-success">TwoFactor Disabled.</div>';
                    }
                }
                else {
                    session_destroy();
                    echo '<div class="alert alert-danger">Error: Supplied TwoFactor Token is wrong, you\'ve been logged out.</div><script>window.location = "' . $config['base_url'] . '";</script>';
                }
            }//end if
        }
        else {
            $twofactor = dbFetchRow('SELECT twofactor FROM users WHERE username = ?', array($_SESSION['username']));
            echo '<script src="js/jquery.qrcode.min.js"></script>';
            echo '<div class="well"><h3>Two-Factor Authentication</h3>';
            if (!empty($twofactor['twofactor'])) {
                $twofactor         = json_decode($twofactor['twofactor'], true);
                $twofactor['text'] = "<div class='form-group'>
  <label for='twofactorkey' class='col-sm-2 control-label'>Secret Key</label>
  <div class='col-sm-4'>
    <input type='text' name='twofactorkey' autocomplete='off' disabled class='form-control input-sm' value='".$twofactor['key']."' />
  </div>
</div>";
                if ($twofactor['counter'] !== false) {
                    $twofactor['uri']   = 'otpauth://hotp/'.$_SESSION['username'].'?issuer=LibreNMS&counter='.$twofactor['counter'].'&secret='.$twofactor['key'];
                    $twofactor['text'] .= "<div class='form-group'>
  <label for='twofactorcounter' class='col-sm-2 control-label'>Counter</label>
  <div class='col-sm-4'>
    <input type='text' name='twofactorcounter' autocomplete='off' disabled class='form-control input-sm' value='".$twofactor['counter']."' />
  </div>
</div>";
                }
                else {
                    $twofactor['uri'] = 'otpauth://totp/'.$_SESSION['username'].'?issuer=LibreNMS&secret='.$twofactor['key'];
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
                echo '<br/><form method="post" class="form-horizontal" role="form">
  <input type="hidden" name="twofactorremove" value="1" />
  <button class="btn btn-danger" type="submit">Disable TwoFactor</button>
</form>';
            }
            else {
                if (isset($_POST['gentwofactorkey']) && isset($_POST['twofactortype'])) {
                    include_once $config['install_dir'].'/html/includes/authentication/twofactor.lib.php';
                    $chk = dbFetchRow('SELECT twofactor FROM users WHERE username = ?', array($_SESSION['username']));
                    if (empty($chk['twofactor'])) {
                        $twofactor = array('key' => twofactor_genkey());
                        if ($_POST['twofactortype'] == 'counter') {
                            $twofactor['counter'] = 1;
                        }
                        else {
                            $twofactor['counter'] = false;
                        }

                        if (!dbUpdate(array('twofactor' => json_encode($twofactor)), 'users', 'username = ?', array($_SESSION['username']))) {
                            echo '<div class="alert alert-danger">Error inserting TwoFactor details. Please try again later and contact Administrator if error persists.</div>';
                        }
                        else {
                            echo '<div class="alert alert-success">Added TwoFactor credentials. Please reload page.</div><script>window.location = "preferences/";</script>';
                        }
                    }
                    else {
                        echo '<div class="alert alert-danger">TwoFactor credentials already exists.</div>';
                    }
                }
                else {
                    echo '<form method="post" class="form-horizontal" role="form">
  <input type="hidden" name="gentwofactorkey" value="1" />
  <div class="form-group">
    <label for="twofactortype" class="col-sm-2 control-label">TwoFactor Type</label>
    <div class="col-sm-4">
      <select name="twofactortype">
        <option value=""></option>
        <option value="counter">Counter Based (HOTP)</option>
        <option value="time">Time Based (TOTP)</option>
      </select>
    </div>
  </div>
  <button class="btn btn-default" type="submit">Generate TwoFactor Secret Key</button>
</form>';
                }//end if
            }//end if
            echo '</div>';
        }//end if
    }//end if
}//end if


echo "<h3>Device Permissions</h3>";
echo "<hr>";
echo "<div style='background-color: #e5e5e5; border: solid #e5e5e5 10px;  margin-bottom:10px;'>";
if ($_SESSION['userlevel'] == '10') {
    echo "<strong class='blue'>Global Administrative Access</strong>";
}

if ($_SESSION['userlevel'] == '5') {
    echo "<strong class='green'>Global Viewing Access</strong>";
}

if ($_SESSION['userlevel'] == '1') {
    foreach (dbFetchRows('SELECT * FROM `devices_perms` AS P, `devices` AS D WHERE `user_id` = ? AND P.device_id = D.device_id', array($_SESSION['user_id'])) as $perm) {
    // FIXME generatedevicelink?
        echo "<a href='device/device=".$perm['device_id']."'>".$perm['hostname'].'</a><br />';
        $dev_access = 1;
    }

    if (!$dev_access) {
        echo 'No access!';
    }
}

echo '</div>';
