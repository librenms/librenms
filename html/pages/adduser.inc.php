<?php

if ($_SESSION['userlevel'] < '10')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  echo("<h3>Add User</h3>");

  $pagetitle[] = "Add user";

  if (auth_usermanagement())
  {
    if ($_POST['action'] == "add")
    {
      if ($_POST['new_username'])
      {
        if (!user_exists($_POST['new_username']))
        {

          if (isset($_POST['can_modify_passwd'])) {
            $_POST['can_modify_passwd'] = 1;
          } else {
            $_POST['can_modify_passwd'] = 0;
          }

          // FIXME: missing email field here on the form
          if (adduser($_POST['new_username'], $_POST['new_password'], $_POST['new_level'], '', $_POST['realname'], $_POST['can_modify_passwd']))
          {
            echo("<span class=info>User " . $_POST['username'] . " added!</span>");
          }
        }
        else
        {
          echo('<div class="red">User with this name already exists!</div>');
        }
      }
      else
      {
        echo('<div class="red">Please enter a username!</div>');
      }
    }

    echo("<form method='post' action='adduser/' class='form-horizontal' role='form'> <input type='hidden' value='add' name='action'>");
    echo("
  <div class='form-group'>
    <label for='new_username' class='col-sm-2 control-label'>Username</label>
    <div class='col-sm-4'>
      <input name='new_username' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>");
  ?>
  <div class='form-group'>
    <label for='new_password' class='col-sm-2 control-label'>Password</label>
    <div class='col-sm-4'>
      <input name='new_password' id='new_password' type=password class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>

  <?php
    if ($_POST['action'] == "add" && !$_POST['new_password'])
    {
      echo("<span class=red>Please enter a password!</span><br />");
    }

echo("
  <div class='form-group'>");
    echo("<label for='new_realname' class='col-sm-2 control-label'>Realname</label>
    <div class='col-sm-4'>
      <input name='new_realname' class='form-control input-sm'>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>");
    echo("<div class='form-group'>
    <label for='new_level' class='col-sm-2 control-label'>Level</label>
    <div class='col-sm-4'>
      <select name='new_level' class='form-control input-sm'>
        <option value='1'>Normal User</option>
        <option value='5'>Global Read</option>
        <option value='10'>Administrator</option>
      </select>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>");
    echo("<div class='form-group'>
    <div class='col-sm-6'>
      <div class='checkbox'>
        <label>
          <input type='checkbox' checked='checked' name='can_modify_passwd'> Allow the user to change his password.
        </label>
      </div>
    </div>
    <div class='col-sm-6'>
    </div>
  </div>");
    echo("<button type='submit' class='btn btn-default'>Add</button>");
    echo("</form>");
  }
  else
  {
    echo('<span class="red">Auth module does not allow user management!</span><br />');
  }
}

?>
