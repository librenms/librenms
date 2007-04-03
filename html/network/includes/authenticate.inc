<?php 

session_start();  

if($_GET['logout']) {
  session_destroy();
}

// convert username and password from _POST or _SESSION 
if($_SERVER['PHP_AUTH_PW']) {
  $_SESSION['username']=$_SERVER['PHP_AUTH_USER'];
  $_SESSION['password']=$_SERVER['PHP_AUTH_PW'];
} elseif($_POST){ 
  $_SESSION['username']=$_POST["username"]; 
  $_SESSION['password']=$_POST["password"];   
} 

// query for a user/pass match 
$password = $_SESSION['password'];
$encrypted = md5($password);

$sql = "select username, level from users where username='$_SESSION[username]' and password='$encrypted'"; 

$row = mysql_fetch_row(mysql_query($sql));

$result = $row[0];

$auth_level = $row[1];

if ( $_SESSION['username'] != "" && $result == $_SESSION['username'] ) {
  $auth_user = $result;
} else {
   header('WWW-Authenticate: Basic realm="Vostron Administration"');
   header('HTTP/1.0 401 Unauthorized');
   echo 'Authentication required.';
   exit;

}

?>
