<?php

echo('<div style="margin: 10px;">');

if ($_SESSION['userlevel'] < '10') { include("includes/error-no-perm.inc.php"); } else
{
  echo("<h3>Delete User</h3>");

  if (auth_usermanagement())
  {
    if ($vars['action'] == "del")
    {
      $delete_username = dbFetchCell("SELECT username FROM users WHERE user_id = ?", array($vars['id']));

      if ($vars['confirm'] == "yes")
      {
        if (deluser($delete_username))
        {
          echo('<div class="infobox">User "' . $delete_username . '" deleted!</div>');
        }
        else
        {
          echo('<div class="errorbox">Error deleting user "' . $delete_username . '"!</div>');
        }
      }
      else
      {
        echo('<div class="errorbox">You have requested deletion of the user "' . $delete_username . '". This action can not be reversed.<br /><a href="deluser/action=del/id=' . $vars['id'] . '/confirm=yes">Click to confirm</a></div>');
      }
    }

    # FIXME v mysql query should be replaced by authmodule
    $userlist = dbFetchRows("SELECT * FROM `users`");
    foreach ($userlist as $userentry)
    {
      $i++;
      echo($i . ". " . $userentry['username'] . "
         <a href='deluser/action=del/id=" . $userentry['user_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a><br/>");
    }
  }
  else
  {
    print_error("Authentication module does not allow user management!");
  }
}

echo("</div>");

?>

