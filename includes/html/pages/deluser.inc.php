<?php

use LibreNMS\Authentication\LegacyAuth;

echo '<div style="margin: 10px;">';

if (! Auth::user()->isAdmin()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    echo '<h3>Delete User</h3>';

    $pagetitle[] = 'Delete user';

    if (LegacyAuth::get()->canManageUsers()) {
        if ($vars['action'] == 'del') {
            $id = (int) $vars['id'];
            $user = LegacyAuth::get()->getUser($id);

            if ($vars['confirm'] == 'yes') {
                if (LegacyAuth::get()->deleteUser($id) >= 0) {
                    print_message('<div class="infobox">User "' . $user['username'] . '" deleted!');
                } else {
                    print_error('Error deleting user "' . $user['username'] . '"!');
                }
            } else {
                print_error('You have requested deletion of the user "' . $user['username'] . '". This action can not be reversed.<br /><a class="btn btn-danger" href="deluser/action=del/id=' . $id . '/confirm=yes">Click to confirm</a>');
            }
        }

        // FIXME v mysql query should be replaced by authmodule
        $userlist = LegacyAuth::get()->getUserlist();

        echo '
            <form role="form" class="form-horizontal" method="GET" action="">
            ' . csrf_field() . '
            <input type="hidden" name="action" value="del">
            <div class="form-group">
            <label for="user_id" class="col-sm-2 control-label">Select User: </label>
            <div class="col-sm-6">
            <select id="user_id" name="id" class="form-control input-sm">
            ';

        foreach ($userlist as $userentry) {
            $i++;
            echo '<option value="' . $userentry['user_id'] . '">' . $userentry['username'] . '</option>';
        }

        echo '
            </select>
            </div>
            </div>
            <div class="form-group">
            <div class="col-sm-2">
            </div>
            <div class="col-sm-6">
            <button class="btn btn-danger btn-sm">Delete User</button>
            </div>
            </div>
            </form>
            ';
    } else {
        print_error('Authentication module does not allow user management!');
    }//end if
}//end if

echo '</div>';
