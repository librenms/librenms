<?php

$no_refresh = true;

require 'includes/javascript-interfacepicker.inc.php';

echo "<div style='margin: 10px;'>";

$pagetitle[] = 'Edit user';

if ($_SESSION['userlevel'] != '10') {
    include 'includes/error-no-perm.inc.php';
}
else {
    if ($vars['user_id'] && !$vars['edit']) {
        $user_data = dbFetchRow('SELECT * FROM users WHERE user_id = ?', array($vars['user_id']));
        echo '<p><h2>'.$user_data['realname']."</h2><a href='edituser/'>Change...</a></p>";
        // Perform actions if requested
        if ($vars['action'] == 'deldevperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?', array($vars['device_id'], $vars['user_id']))) {
                dbDelete('devices_perms', '`device_id` =  ? AND `user_id` = ?', array($vars['device_id'], $vars['user_id']));
            }
        }

        if ($vars['action'] == 'adddevperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM devices_perms WHERE `device_id` = ? AND `user_id` = ?', array($vars['device_id'], $vars['user_id']))) {
                dbInsert(array('device_id' => $vars['device_id'], 'user_id' => $vars['user_id']), 'devices_perms');
            }
        }

        if ($vars['action'] == 'delifperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM ports_perms WHERE `port_id` = ? AND `user_id` = ?', array($vars['port_id'], $vars['user_id']))) {
                dbDelete('ports_perms', '`port_id` =  ? AND `user_id` = ?', array($vars['port_id'], $vars['user_id']));
            }
        }

        if ($vars['action'] == 'addifperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM ports_perms WHERE `port_id` = ? AND `user_id` = ?', array($vars['port_id'], $vars['user_id']))) {
                dbInsert(array('port_id' => $vars['port_id'], 'user_id' => $vars['user_id'], 'access_level' => 0), 'ports_perms');
            }
        }

        if ($vars['action'] == 'delbillperm') {
            if (dbFetchCell('SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?', array($vars['bill_id'], $vars['user_id']))) {
                dbDelete('bill_perms', '`bill_id` =  ? AND `user_id` = ?', array($vars['bill_id'], $vars['user_id']));
            }
        }

        if ($vars['action'] == 'addbillperm') {
            if (!dbFetchCell('SELECT COUNT(*) FROM bill_perms WHERE `bill_id` = ? AND `user_id` = ?', array($vars['bill_id'], $vars['user_id']))) {
                dbInsert(array('bill_id' => $vars['bill_id'], 'user_id' => $vars['user_id']), 'bill_perms');
            }
        }

        echo '<div class="row">
           <div class="col-md-4">';

        // Display devices this users has access to
        echo '<h3>Device Access</h3>';

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
              <tr>
                <th>Device</th>
                <th>Action</th>
              </tr>";

        $device_perms = dbFetchRows('SELECT * from devices_perms as P, devices as D WHERE `user_id` = ? AND D.device_id = P.device_id', array($vars['user_id']));
        foreach ($device_perms as $device_perm) {
            echo '<tr><td><strong>'.$device_perm['hostname']."</td><td> <a href='edituser/action=deldevperm/user_id=".$vars['user_id'].'/device_id='.$device_perm['device_id']."'><img src='images/16/cross.png' align=absmiddle border=0></a></strong></td></tr>";
            $access_list[] = $device_perm['device_id'];
            $permdone      = 'yes';
        }

        echo '</table>
          </div>';

        if (!$permdone) {
            echo 'None Configured';
        }

        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new device</h4>';
        echo "<form class='form-inline' role='form' method='post' action=''>
            <input type='hidden' value='".$vars['user_id']."' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='adddevperm' name='action'>
            <div class='form-group'>
              <label class='sr-only' for='device_id'>Device</label>
              <select name='device_id' id='device_id' class='form-control'>";

        $devices = dbFetchRows('SELECT * FROM `devices` ORDER BY hostname');
        foreach ($devices as $device) {
            unset($done);
            foreach ($access_list as $ac) {
                if ($ac == $device['device_id']) {
                    $done = 1;
                }
            }

            if (!$done) {
                echo "<option value='".$device['device_id']."'>".$device['hostname'].'</option>';
            }
        }

        echo "</select>
           </div>
           <button type='submit' class='btn btn-default' name='Submit'>Add</button></form>";

        echo "</div>
          <div class='col-md-4'>";
        echo '<h3>Interface Access</h3>';

        $interface_perms = dbFetchRows('SELECT * from ports_perms as P, ports as I, devices as D WHERE `user_id` = ? AND I.port_id = P.port_id AND D.device_id = I.device_id', array($vars['user_id']));

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
              <tr>
                <th>Interface name</th>
                <th>Action</th>
              </tr>";
        foreach ($interface_perms as $interface_perm) {
            echo '<tr>
              <td>
                <strong>'.$interface_perm['hostname'].' - '.$interface_perm['ifDescr'].'</strong>'.''.$interface_perm['ifAlias']."
              </td>
              <td>
                &nbsp;&nbsp;<a href='edituser/action=delifperm/user_id=".$vars['user_id'].'/port_id='.$interface_perm['port_id']."'><img src='images/16/cross.png' align=absmiddle border=0></a>
              </td>
            </tr>";
            $ipermdone = 'yes';
        }

        echo '</table>
          </div>';

        if (!$ipermdone) {
            echo 'None Configured';
        }

        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new interface</h4>';

        echo "<form action='' method='post' class='form-horizontal' role='form'>
        <input type='hidden' value='".$vars['user_id']."' name='user_id'>
        <input type='hidden' value='edituser' name='page'>
        <input type='hidden' value='addifperm' name='action'>
        <div class='form-group'>
          <label for='device' class='col-sm-2 control-label'>Device: </label>
          <div class='col-sm-10'>
            <select id='device' class='form-control' name='device' onchange='getInterfaceList(this)'>
          <option value=''>Select a device</option>";

        foreach ($devices as $device) {
            unset($done);
            foreach ($access_list as $ac) {
                if ($ac == $device['device_id']) {
                    $done = 1;
                }
            }

            if (!$done) {
                echo "<option value='".$device['device_id']."'>".$device['hostname'].'</option>';
            }
        }

        echo "</select>
          </div>
          </div>
          <div class='form-group'>
            <label for='port_id' class='col-sm-2 control-label'>Interface: </label>
            <div class='col-sm-10'>
              <select class='form-control' id='port_id' name='port_id'>
              </select>
            </div>
         </div>
         <div class='form-group'>
           <div class='col-sm-12'>
             <button type='submit' class='btn btn-default' name='Submit' value='Add'>Add</button>
           </div>
         </div>
       </form>";

        echo "</div>
          <div class='col-md-4'>";
        echo '<h3>Bill Access</h3>';

        $bill_perms = dbFetchRows('SELECT * from bills AS B, bill_perms AS P WHERE P.user_id = ? AND P.bill_id = B.bill_id', array($vars['user_id']));

        echo "<div class='panel panel-default panel-condensed'>
            <table class='table table-hover table-condensed table-striped'>
            <tr>
              <th>Bill name</th>
              <th>Action</th>
            </tr>";

        foreach ($bill_perms as $bill_perm) {
            echo '<tr>
              <td>
                <strong>'.$bill_perm['bill_name']."</strong></td><td width=50>&nbsp;&nbsp;<a href='edituser/action=delbillperm/user_id=".$vars['user_id'].'/bill_id='.$bill_perm['bill_id']."'><img src='images/16/cross.png' align=absmiddle border=0></a>
              </td>
            </tr>";
            $bill_access_list[] = $bill_perm['bill_id'];

            $bpermdone = 'yes';
        }

        echo '</table>
          </div>';

        if (!$bpermdone) {
            echo 'None Configured';
        }

        // Display devices this user doesn't have access to
        echo '<h4>Grant access to new bill</h4>';
        echo "<form method='post' action='' class='form-inline' role='form'>
            <input type='hidden' value='".$vars['user_id']."' name='user_id'>
            <input type='hidden' value='edituser' name='page'>
            <input type='hidden' value='addbillperm' name='action'>
            <div class='form-group'>
              <label class='sr-only' for='bill_id'>Bill</label>
              <select name='bill_id' class='form-control' id='bill_id'>";

        $bills = dbFetchRows('SELECT * FROM `bills` ORDER BY `bill_name`');
        foreach ($bills as $bill) {
            unset($done);
            foreach ($bill_access_list as $ac) {
                if ($ac == $bill['bill_id']) {
                    $done = 1;
                }
            }

            if (!$done) {
                echo "<option value='".$bill['bill_id']."'>".$bill['bill_name'].'</option>';
            }
        }

        echo "</select>
          </div>
          <button type='submit' class='btn btn-default' name='Submit' value='Add'>Add</button>
        </form>
        </div>";
    }
    else if ($vars['user_id'] && $vars['edit']) {
        if ($_SESSION['userlevel'] == 11) {
            demo_account();
        }
        else {
            if (!empty($vars['new_level'])) {
                if ($vars['can_modify_passwd'] == 'on') {
                    $vars['can_modify_passwd'] = '1';
                }

                update_user($vars['user_id'], $vars['new_realname'], $vars['new_level'], $vars['can_modify_passwd'], $vars['new_email']);
                print_message('User has been updated');
                if (!empty($vars['new_pass1']) && $vars['new_pass1'] == $vars['new_pass2'] && passwordscanchange($vars['cur_username'])) {
                    if (changepassword($vars['cur_username'],$vars['new_pass1']) == 1) {
                        print_message("User password has been updated");
                }
                else {
                    print_error("Password couldn't be updated");
                }
            }
            elseif (!empty($vars['new_pass1']) && $vars['new_pass1'] != $vars['new_pass2']) {
                print_error("The supplied passwords didn't match so weren't updated");
            }
        }

            if (can_update_users() == '1') {
                $users_details = get_user($vars['user_id']);
                if (!empty($users_details)) {
                    if (empty($vars['new_realname'])) {
                        $vars['new_realname'] = $users_details['realname'];
                    }

                    if (empty($vars['new_level'])) {
                        $vars['new_level'] = $users_details['level'];
                    }

                    if (empty($vars['can_modify_passwd'])) {
                        $vars['can_modify_passwd'] = $users_details['can_modify_passwd'];
                    }
                    else if ($vars['can_modify_passwd'] == 'on') {
                        $vars['can_modify_passwd'] = '1';
                    }

                    if (empty($vars['new_email'])) {
                        $vars['new_email'] = $users_details['email'];
                    }

                    if ($config['twofactor']) {
                        if ($vars['twofactorremove']) {
                            if (dbUpdate(array('twofactor' => ''), users, 'user_id = ?', array($vars['user_id']))) {
                                echo "<div class='alert alert-success'>TwoFactor credentials removed.</div>";
                            }
                            else {
                                echo "<div class='alert alert-danger'>Couldnt remove user's TwoFactor credentials.</div>";
                            }
                        }

                        if ($vars['twofactorunlock']) {
                            $twofactor          = dbFetchRow('SELECT twofactor FROM users WHERE user_id = ?', array($vars['user_id']));
                            $twofactor          = json_decode($twofactor['twofactor'], true);
                            $twofactor['fails'] = 0;
                            if (dbUpdate(array('twofactor' => json_encode($twofactor)), users, 'user_id = ?', array($vars['user_id']))) {
                                echo "<div class='alert alert-success'>User unlocked.</div>";
                            }
                            else {
                                echo "<div class='alert alert-danger'>Couldnt reset user's TwoFactor failures.</div>";
                            }
                        }
                    }

                    if (!empty($vars['dashboard'])) {
                        dbUpdate(array('dashboard'=>$vars['dashboard']),'users','user_id = ?',array($vars['user_id']));
                    }

                    echo "<form class='form-horizontal' role='form' method='post' action=''>
  <input type='hidden' name='user_id' value='".$vars['user_id']."'>
  <input type='hidden' name='cur_username' value='" . $users_details['username'] . "'>
  <input type='hidden' name='edit' value='yes'>
  <div class='form-group'>
    <label for='new_realname' class='col-sm-2 control-label'>Realname</label>
    <div class='col-sm-4'>
      <input name='new_realname' class='form-control input-sm' value='".$vars['new_realname']."'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_email' class='col-sm-2 control-label'>Email</label>
    <div class='col-sm-4'>
      <input name='new_email' class='form-control input-sm' value='".$vars['new_email']."'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <div class='form-group'>
    <label for='new_level' class='col-sm-2 control-label'>Level</label>
    <div class='col-sm-4'>
      <select name='new_level' class='form-control input-sm'>
        <option value='1'";
                    if ($vars['new_level'] == '1') {
                        echo 'selected';
                    } echo ">Normal User</option>
        <option value='5'";
                    if ($vars['new_level'] == '5') {
                        echo 'selected';
                    } echo ">Global Read</option>
        <option value='10'";
                    if ($vars['new_level'] == '10') {
                        echo 'selected';
                    } echo ">Administrator</option>
        <option value='11'";
                    if ($vars['new_level'] == '11') {
                        echo 'selected';
                    } echo ">Demo account</option>
      </select>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>";

if (passwordscanchange($users_details['username'])) {
    echo "
        <div class='form-group'>
            <label for='new_pass1' class='col-sm-2 control-label'>Password</label>
            <div class='col-sm-4'>
                <input type='password' name='new_pass1' class='form-control input-sm' value='". $vars['new_pass1'] ."'>
            </div>
        </div>
        <div class='form-group'>
            <label for='new_pass2' class='col-sm-2 control-label'>Confirm Password</label>
            <div class='col-sm-4'>
                <input type='password' name='new_pass2' class='form-control input-sm' value='". $vars['new_pass2'] ."'>
            </div>
        </div>
        ";
}
    echo "
       <div class='form-group'>
           <label for='dashboard' class='col-sm-2 control-label'>Dashboard</label>
           <div class='col-sm-4'><select class='form-control' name='dashboard'>";
    $defdash = dbFetchCell("SELECT dashboard FROM users WHERE user_id = ?",array($vars['user_id']));
    foreach(dbFetchRows("SELECT dashboards.*,users.username FROM `dashboards` INNER JOIN `users` ON users.user_id = dashboards.user_id WHERE (dashboards.access > 0 && dashboards.user_id != ?) || dashboards.user_id = ?",array($vars['user_id'],$vars['user_id'])) as $dash) {
        echo "<option value='".$dash['dashboard_id']."'".($defdash == $dash['dashboard_id'] ? ' selected' : '').">".$dash['username'].':'.$dash['dashboard_name']."</option>";
    }
    echo "</select>
           </div>
       </div>
       ";

  echo "<div class='form-group'>
    <div class='col-sm-6'>
      <div class='checkbox'>
        <label>
          <input type='checkbox' ";
                    if ($vars['can_modify_passwd'] == '1') {
                        echo "checked='checked'";
                    } echo " name='can_modify_passwd'> Allow the user to change his password.
        </label>
      </div>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>
  <button type='submit' class='btn btn-default'>Update User</button>
  </form>";
                    if ($config['twofactor']) {
                        echo "<br/><div class='well'><h3>Two-Factor Authentication</h3>";
                        $twofactor = dbFetchRow('SELECT twofactor FROM users WHERE user_id = ?', array($vars['user_id']));
                        $twofactor = json_decode($twofactor['twofactor'], true);
                        if ($twofactor['fails'] >= 3 && (!$config['twofactor_lock'] || (time() - $twofactor['last']) < $config['twofactor_lock'])) {
                            echo "<form class='form-horizontal' role='form' method='post' action=''>
  <input type='hidden' name='user_id' value='".$vars['user_id']."'>
  <input type='hidden' name='edit' value='yes'>
  <div class='form-group'>
    <label for='twofactorunlock' class='col-sm-2 control-label'>User exceeded failures</label>
    <input type='hidden' name='twofactorunlock' value='1'>
    <button type='submit' class='btn btn-default'>Unlock</button>
  </div>
</form>";
                        }

                        if ($twofactor['key']) {
                            echo "<form class='form-horizontal' role='form' method='post' action=''>
  <input type='hidden' name='user_id' value='".$vars['user_id']."'>
  <input type='hidden' name='edit' value='yes'>
  <input type='hidden' name='twofactorremove' value='1'>
  <button type='submit' class='btn btn-danger'>Disable TwoFactor</button>
</form>
</div>";
                        }
                        else {
                            echo '<p>No TwoFactor key generated for this user, Nothing to do.</p>';
                        }
                    }//end if
                }
                else {
                    echo print_error('Error getting user details');
                }//end if
            }
            else {
                echo print_error("Authentication method doesn't support updating users");
            }//end if
        }//end if
    }
    else {
        $user_list = get_userlist();

        echo '<h3>Select a user to edit</h3>';

        echo "<form method='post' action='' class='form-horizontal' role='form'>
            <input type='hidden' value='edituser' name='page'>
              <div class='form-group'>
                <label for='user_id' class='col-sm-2 control-label'>User</label>
                <div class='col-sm-4'>
                  <select name='user_id' class='form-control input-sm'>";
        foreach ($user_list as $user_entry) {
            echo "<option value='".$user_entry['user_id']."'>".$user_entry['username'].'</option>';
        }

        echo "</select>
    </div>
    </div>
    <div class='form-group'>
      <div class='col-sm-offset-2 col-sm-3'>
        <button type='submit' name='Submit' class='btn btn-default'>Edit Permissions</button> / <button type='submit' name='edit' value='user' class='btn btn-default'>Edit User</button>
      </div>
    </div>
  </form>";
    }//end if
}//end if

echo '</div>';
