<?php

echo("<div style='margin: 10px;'>");

if($_SESSION['userlevel'] != '10') { echo("<span class=alert>You do not have then necessary permission to view this page!</span>"); } else {

  echo("<h3>Delete User</h3>");

  if($_GET['action'] == "del") {

    $delete_username = mysql_result(mysql_query("SELECT username FROM users WHERE user_id = '" . $_GET['user_id'] . "'"),0);

    if($_GET['confirm'] == "yes") {

      mysql_query("DELETE FROM `devices_perms` WHERE `user_id` = '" . $_GET['user_id'] . "'");
      mysql_query("DELETE FROM `users` WHERE `user_id` = '" . $_GET['user_id'] . "'");

      if(mysql_affected_rows()) { echo("<span class=info>User '$delete_username' deleted!</span>"); }

    } else {

      echo("<span class=alert>You have requested deletion of the  user '$delete_username'. This action can not be reversed.<br /><a href='?page=deluser&action=del&user_id=" . $_GET['user_id'] . "&confirm=yes'>Click to confirm</a></span>");

    }

  }

  $userlist = mysql_query("SELECT * FROM `users`");

  while($userentry = mysql_fetch_array($userlist)) {
    $i++;
    echo($i . ". " . $userentry['username'] . "
         <a href='?page=deluser&action=del&user_id=" . $userentry['user_id'] . "'><img src='images/16/cross.png' align=absmiddle border=0></a><br/>");
  }

}

echo("</div>");

?>

