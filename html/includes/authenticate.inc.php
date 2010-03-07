<?php 

@ini_set("session.gc_maxlifetime","0"); 

session_start();
if(isset($_GET['logout']) && $_SESSION['authenticated']) {
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('" . $_SESSION['username'] . "', '".$_SERVER["REMOTE_ADDR"]."', 'logged out')");
  unset($_SESSION);
  session_destroy();
  header('Location: /');
  setcookie ("username", "", time() - 60*60*24*100, "/");
  setcookie ("password", "", time() - 60*60*24*100, "/");
  $auth_message = "Logged Out";
}

if(isset($_POST['username']) && isset($_POST['password'])){
  $_SESSION['username'] = mres($_POST['username']);
  $_SESSION['password'] = mres($_POST['password']);
}

if(isset($_COOKIE['username']) && isset($_COOKIE['password'])){
  $_SESSION['username'] = mres($_COOKIE['username']);
  $_SESSION['password'] = mres($_COOKIE['password']);
}

if (!isset($config['auth_mechanism']))
{
  $config['auth_mechanism'] = "mysql";
}

if (file_exists('includes/authentication/' . $config['auth_mechanism'] . '.inc.php'))
{
  include('includes/authentication/' . $config['auth_mechanism'] . '.inc.php');
}
else
{
  # FIXME use standard error message box?
  echo "ERROR: no valid auth_mechanism defined.";
  exit();
}

$auth_success = 0;

if (isset($_SESSION['username']))
{
  if (authenticate($_SESSION['username'],$_SESSION['password']))
  {
    $_SESSION['userlevel'] = get_userlevel($_SESSION['username']);
    $_SESSION['user_id'] = get_userid($_SESSION['username']);
    if(!$_SESSION['authenticated']) 
    {
      $_SESSION['authenticated'] = true;
      mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'logged in')");
      header("Location: ".$_SERVER['REQUEST_URI']);
    }
    if(isset($_POST['remember'])) 
    {
      setcookie("username", $_SESSION['username'], time()+60*60*24*100, "/");
      setcookie("password", $_SESSION['password'], time()+60*60*24*100, "/");
    }
  } 
  elseif (isset($_SESSION['username'])) 
  { 
    $auth_message = "Authentication Failed"; 
    unset ($_SESSION['authenticated']);
    mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'authentication failure')");
  }
} 
?>
