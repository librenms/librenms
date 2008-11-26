<?php 

function check_auth($username, $password) {
  $encrypted = md5($password);
  $sql = "select username, level, user_id from users where username='$username' and password='$encrypted'";
  $query = mysql_query($sql);
  $row = mysql_fetch_row($query);

  if ($row[0] == $username) { return $row[1]; } else { return FALSE; }

}

if($_GET['logout']) {
  session_start();
  $olduser = $_SESSION['username'];
  session_destroy();
  header('Location: /');
  setcookie ("username", "", time() - 3600);
  setcookie ("encrypted", "", time() - 3600);
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('$olduser', '".$_SERVER["REMOTE_ADDR"]."', 'logged out')");
  $auth_message = "Logged Out";
} else {

  session_start();  

  if($_POST['username'] && $_POST['password']){
    $username = mres($_POST['username']);
    $password = mres($_POST['password']);
    $userlevel = check_auth($username,$password);
    if($userlevel) {
      $_SESSION['userlevel'] = $userlevel;
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
