<?php 

session_start();

if($_GET['logout']) {
  $olduser = $_SESSION['username'];
  session_destroy();
  header('Location: /');
  setcookie ("username", "", time() - 3600);
  setcookie ("encrypted", "", time() - 3600);
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('$olduser', '".$_SERVER["REMOTE_ADDR"]."', 'logged out')");
  $auth_message = "Logged Out";
} else {
  if($_POST['username'] && $_POST['password']){
    $username = mres($_POST['username']);
    $password = mres($_POST['password']);
    $encrypted = md5($password);
    $sql = "SELECT * FROM `users` WHERE `username`='$username' AND `password`='$encrypted'";
    $query = mysql_query($sql);
    $row = @mysql_fetch_array($query);
    if($row['level']) {
      $_SESSION['userlevel'] = $row['level'];
      $_SESSION['user_id'] = $row['user_id'];
      $_SESSION['authenticated'] = true;
      $_SESSION['username'] = $username;
      mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('$username', '".$_SERVER["REMOTE_ADDR"]."', 'logged in')");
      header("Location: ".$_SERVER['REQUEST_URI']);
    } else { 
      $auth_message = "Authentication Failed"; 
      mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('$username', '".$_SERVER["REMOTE_ADDR"]."', 'authentication failure')");
    } 
  }
}

?>
