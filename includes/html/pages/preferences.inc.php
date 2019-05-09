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
}//end if
