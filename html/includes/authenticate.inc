<?php 

@ini_set("session.gc_maxlifetime","0"); 

session_start();

if($_GET['logout']) {
  unset($_SESSION);
  session_destroy();
  header('Location: /');
  setcookie ("username", "", time() - 60*60*24*100, "/");
  setcookie ("password", "", time() - 60*60*24*100, "/");
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('$olduser', '".$_SERVER["REMOTE_ADDR"]."', 'logged out')");
  $auth_message = "Logged Out";
  unset ($_SESSION['authenticated']);

}

if($_POST['username'] && $_POST['password']){
  $_SESSION['username'] = mres($_POST['username']);
  $_SESSION['password'] = mres($_POST['password']);
}

if($_COOKIE['username'] && $_COOKIE['password']){
  $_SESSION['username'] = mres($_COOKIE['username']);
  $_SESSION['password'] = mres($_COOKIE['password']);
}


$encrypted = md5($_SESSION['password']);
$sql = "SELECT * FROM `users` WHERE `username`='".$_SESSION['username']."' AND `password`='".$encrypted."'";
$query = mysql_query($sql);
$row = @mysql_fetch_array($query);
if($row['username'] && $row['username'] == $_SESSION['username']) {
  $_SESSION['userlevel'] = $row['level'];
  $_SESSION['user_id'] = $row['user_id'];
  if(!$_SESSION['authenticated']) {
    $_SESSION['authenticated'] = true;
    mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'logged in')");
    header("Location: ".$_SERVER['REQUEST_URI']);
  }
  if(isset($_POST['remember'])){
    setcookie("username", $_SESSION['username'], time()+60*60*24*100, "/");
    setcookie("password", $_SESSION['password'], time()+60*60*24*100, "/");
  }
} else { 
  $auth_message = "Authentication Failed"; 
  unset ($_SESSION['authenticated']);
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'authentication failure')");
} 

?>
