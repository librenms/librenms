<?php 

if($_GET['logout']) {
  session_start();
  session_destroy();
  header('Location: /');
  setcookie ("username", "", time() - 3600);
  setcookie ("encrypted", "", time() - 3600);
  echo("$_COOKIE[username]");

} else {

  session_start();  

  if($_POST['username'] && $_POST['password']){ 
    $_SESSION['username']=$_POST['username']; 
    $_SESSION['password']=$_POST['password'];   
    $_SESSION['encrypted'] = md5($_SESSION['password']);
  } elseif($_COOKIE['username'] && $_COOKIE['encrypted'] && !$_SESSION['authenticated']) {
    $_SESSION['username']=$_COOKIE['username'];
    $_SESSION['encrypted']=$_COOKIE['encrypted'];
  }

  $sql = "select username, level, user_id from users where username='" . $_SESSION['username'] . "' and password='" . $_SESSION['encrypted'] . "'"; 
  $row = mysql_fetch_row(mysql_query($sql));

  if ( $_SESSION['username'] != "" && $row[0] == $_SESSION['username'] ) {
    $_SESSION['userlevel'] = $row[1];
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = $row[2];
    setcookie("username", $username);
    setcookie("encrypted", $encrypted);
  }
}

?>
