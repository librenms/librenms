<?php

echo '<div style="margin: 10px;">';

if ($_SESSION['userlevel'] < 10 || $_SESSION['userlevel'] > 10) {
    include 'includes/error-no-perm.inc.php';
} else {
    echo '<h3>Delete User</h3>';

    $pagetitle[] = 'Delete user';

    if (auth_usermanagement()) {
        if ($vars['action'] == 'del') {
            $delete_username = dbFetchCell('SELECT username FROM users WHERE user_id = ?', array($vars['id']));

            if ($vars['confirm'] == 'yes') {
                if (deluser($delete_username)) {
                    print_message('<div class="infobox">User "'.$delete_username.'" deleted!');
                } else {
                    print_error('Error deleting user "'.$delete_username.'"!');
                }
            } else {
                print_error('You have requested deletion of the user "'.$delete_username.'". This action can not be reversed.<br /><a class="btn btn-danger" href="deluser/action=del/id='.$vars['id'].'/confirm=yes">Click to confirm</a>');
            }
        }

        // FIXME v mysql query should be replaced by authmodule
        $userlist = dbFetchRows('SELECT * FROM `users`');

        echo '
            <form role="form" class="form-horizontal" method="GET" action="">
            <input type="hidden" name="action" value="del">
            <div class="form-group">
            <label for="user_id" class="col-sm-2 control-label">Select User: </label>
            <div class="col-sm-6">
            <select id="user_id" name="id" class="form-control input-sm">
            ';

        foreach ($userlist as $userentry) {
            $i++;
            echo '<option value="'.$userentry['user_id'].'">'.$userentry['username'].'</option>';
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
