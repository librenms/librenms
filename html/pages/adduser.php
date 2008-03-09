<?

echo("<div style='margin: 10px;'>");

if($_SESSION['userlevel'] != '10') { echo("<span class=alert>You do not have then necessary permission to view this page!</alert>"); } else {

  echo("<h3>Add User</h3>");

  if($_POST['action'] == "add") {

    mysql_query("INSERT INTO `users` (`username`, `realname`, `password`, `level`) VALUES ('" . $_POST['new_username'] . "', '" . $_POST['new_realname'] . "', MD5('" . $_POST['new_password'] . "'), '" . $_POST['new_level'] . "')");

    if(mysql_affected_rows()) { echo("<span class=info>User " . $_GET['username'] . " added!</span>"); }

  }

  echo("<form method='post' action='?page=adduser'>
          <input type='hidden' value='add' name='action'>");

  echo("Username <input name='new_username'></input><br />");
  echo("Password <input name='new_password'></input><br />");

  echo("Realname <input name='new_realname'></input><br /><br />");

  echo("<select name='new_level'>
          <option value='1'>Normal User</option>
          <option value='5'>Global Read</option>
          <option value='10'>Administrator</option>
        </select><br /><br />");

  echo(" <input type='submit' Value='Add' >");

  echo("</form>");

}

echo("</div>");

?>

