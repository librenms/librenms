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
    $password = $_SESSION['password'];
    $encrypted = md5($password);
  } elseif($_COOKIE['username'] && $_COOKIE['encrypted'] && !$_SESSION['authenticated']) {
    $_SESSION['username']=$_COOKIE['username'];
    $encrypted=$_COOKIE['encrypted'];
  }

  $sql = "select username, level from users where username='$_SESSION[username]' and password='$encrypted'"; 
  $row = mysql_fetch_row(mysql_query($sql));

  if ( $_SESSION['username'] != "" && $row[0] == $_SESSION['username'] ) {
    $_SESSION['userlevel'] = $row[1];
    $_SESSION['authenticated'] = true;
    setcookie("username", $username);
    setcookie("encrypted", $encrypted);
  }
}

?>
