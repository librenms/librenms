<?

echo("<div style='margin: 10px;'>");

if($_SESSION['userlevel'] != '10') { 
  include("includes/error-no-perm.inc.php");
} else {

  echo("<h3>Add User</h3>");

  if($_POST['action'] == "add") {
    if($_POST['new_username'] && $_POST['new_password'] && !mysql_result(mysql_query("SELECT * FROM users WHERE username = '".$_POST['new_username']."'"),0) ) {
      mysql_query("INSERT INTO `users` (`username`, `realname`, `password`, `level`) VALUES ('" . mres($_POST['new_username']) . "', '" . mres($_POST['new_realname']) . "', MD5('" . mres($_POST['new_password']) . "'), '" . mres($_POST['new_level']) . "')");
      if(mysql_affected_rows()) { echo("<span class=info>User " . $_POST['username'] . " added!</span>"); }
    }
  }

  echo("<form method='post' action='?page=adduser'>
          <input type='hidden' value='add' name='action'>");

  echo("Username <input style='margin: 1px;' name='new_username'></input><br />");
  if($_POST['action'] == "add" && !$_POST['new_username']) {
    echo("<div class=red>Please enter a username!</div>");
  } elseif( mysql_result(mysql_query("SELECT * FROM users WHERE username = '".$_POST['new_username']."'"),0)) {
    echo("<span class=red>User with this name already exists!</span><br />");
  }
  ?>
  Password <input style='margin: 1px;' name='new_password' id='new_password' type=password  /><br />
  <?php
  if($_POST['action'] == "add" && !$_POST['new_password']) {
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

echo("</div>");

?>

