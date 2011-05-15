<?php

echo('<div style="margin: 10px;">');

if ($_SESSION['userlevel'] < '10') { include("includes/error-no-perm.inc.php"); } else
{
  echo("<h3>Delete User</h3>");

  if (auth_usermanagement())
  {
    if ($_GET['action'] == "del")
    {
      $delete_username = dbFetchCell("SELECT username FROM users WHERE user_id = ?", array($_GET['user_id']));

      if ($_GET['confirm'] == "yes")
      {
        dbDelete('devices_perms', "`user_id` =  ?", array($_GET['user_id']));
        if (deluser($_GET['user_id'])) { echo("<span class=info>User '$delete_username' deleted!</span>"); }
      }
      else
      {
        echo("<span class=alert>You have requested deletion of the user '$delete_username'. This action can not be reversed.<br /><a href='?page=deluser&action=del&user_id=" . $_GET['user_id'] . "&confirm=yes'>Click to confirm</a></span>");
      }
    }

    # FIXME v mysql query should be replaced by authmodule
    $userlist = dbFetchRows("SELECT * FROM `users`");
    foreach ($userlist as $userentry)
    {
      $i++;
      echo($i . ". " . $userentry['username'] . "
         <a href='?page=deluser&action=del&user_id=" . $userentry['user_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a><br/>");
    }
  }
  else
  {
    print_error("Authentication module does not allow user management!");
  }
}

echo("</div>");

?>

