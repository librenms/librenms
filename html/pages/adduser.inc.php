<?php

echo("<div style='margin: 10px;'>");

if ($_SESSION['userlevel'] < '10')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  echo("<h3>Add User</h3>");

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

          # FIXME: missing email field here on the form
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

    echo("<form method='post' action='adduser/'> <input type='hidden' value='add' name='action'>");
    echo("Username <input style='margin: 1px;' name='new_username'></input><br />");
  ?>
  Password <input style='margin: 1px;' name='new_password' id='new_password' type=password  /><br />
  <?php
    if ($_POST['action'] == "add" && !$_POST['new_password'])
    {
      echo("<span class=red>Please enter a password!</span><br />");
    }
    echo("Realname <input style='margin: 1px;' name='new_realname'></input><br />");
    echo("Level <select style='margin: 5px;' name='new_level'>
          <option value='1'>Normal User</option>
          <option value='5'>Global Read</option>
          <option value='10'>Administrator</option>
        </select><br />");
    echo("<input type='checkbox' checked='checked' style='margin: 1px;' name='can_modify_passwd'></input> Allow the user to change his password.<br /><br />");
    echo(" <input type='submit' Value='Add' >");
    echo("</form>");
  }
  else
  {
    echo('<span class="red">Auth module does not allow user management!</span><br />');
  }
}

echo("</div>");

?>