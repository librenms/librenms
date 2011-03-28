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
          # FIXME: missing email field here on the form
          if (adduser($_POST['new_username'], $_POST['new_password'], $_POST['new_level'], '', $_POST['realname']))
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

    echo("<form method='post' action='?page=adduser'> <input type='hidden' value='add' name='action'>");
    echo("Username <input style='margin: 1px;' name='new_username'></input><br />");
  ?>
  Password <input style='margin: 1px;' name='new_password' id='new_password' type=password  /><br />
  <?php
    if ($_POST['action'] == "add" && !$_POST['new_password'])
    {
      echo("<span class=red>Please enter a password!</span><br />");
    }
    echo("Realname <input style='margin: 1px;' name='new_realname'></input><br />");
  ?>
  <?php
    echo("Level <select style='margin: 5px;' name='new_level'>
          <option value='1'>Normal User</option>
          <option value='5'>Global Read</option>
          <option value='10'>Administrator</option>
        </select><br /><br />");

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