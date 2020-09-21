<?php

use LibreNMS\Authentication\LegacyAuth;

$no_refresh = true;

if (! Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/error-no-perm.inc.php';
} elseif (Auth::user()->isDemo()) {
    demo_account();
} else {
    echo '<h3>Add User</h3>';
    echo '<hr>';

    $pagetitle[] = 'Add user';

    if (LegacyAuth::get()->canManageUsers()) {
        if ($_POST['action'] == 'add') {
            if ($_POST['new_username']) {
                if (! LegacyAuth::get()->userExists($_POST['new_username'])) {
                    if (isset($_POST['can_modify_passwd'])) {
                        $_POST['can_modify_passwd'] = 1;
                    } else {
                        $_POST['can_modify_passwd'] = 0;
                    }

                    // FIXME: missing email field here on the form
                    if (LegacyAuth::get()->addUser($_POST['new_username'], $_POST['new_password'], $_POST['new_level'], $_POST['new_email'], $_POST['new_realname'], $_POST['can_modify_passwd'])) {
                        echo '<span class=info>User ' . $_POST['new_username'] . ' added!</span>';
                    }
                } else {
                    echo '<div class="red">User with this name already exists!</div>';
                }
            } else {
                echo '<div class="red">Please enter a username!</div>';
            }//end if
        }//end if
        echo "<form method='post' action='adduser/' class='form-horizontal' role='form'> <input type='hidden' value='add' name='action'>";
        echo csrf_field();
        echo "
  <div class='form-group'>
    <label for='new_username' class='col-sm-2 control-label'>Username</label>
    <div class='col-sm-4'>
      <input name='new_username' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>"; ?>
    <div class='form-group'>
    <label for='new_password' class='col-sm-2 control-label'>Password</label>
    <div class='col-sm-4'>
      <input name='new_password' id='new_password' type=password class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
    </div>

        <?php
        if ($_POST['action'] == 'add' && ! $_POST['new_password']) {
            echo '<span class=red>Please enter a password!</span><br />';
        }

        echo "
  <div class='form-group'>";
        echo "<label for='new_realname' class='col-sm-2 control-label'>Realname</label>
    <div class='col-sm-4'>
      <input name='new_realname' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_email' class='col-sm-2 control-label'>Email</label>
    <div class='col-sm-4'>
      <input name='new_email' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_level' class='col-sm-2 control-label'>Level</label>
    <div class='col-sm-4'>
      <select name='new_level' class='form-control input-sm'>
        <option value='1'>Normal User</option>
        <option value='5'>Global Read</option>
        <option value='10'>Administrator</option>
        <option value='11'>Demo account</option>
      </select>
      <div class='checkbox'>
        <label>
          <input type='checkbox' checked='checked' name='can_modify_passwd'> Allow the user to change their password.
        </label>
      </div>
      <hr>
      <center><button type='submit' class='btn btn-default'>Add User</button></center>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>";
        echo "<div class='form-group'>
    <div class='col-sm-6'>
      
    </div>
    <div class='col-sm-6'>
    </div>
  </div>";

        echo '</form>';
    } else {
        echo '<span class="red">Auth module does not allow user management!</span><br />';
    }//end if
}//end if
