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

if(isset($_SERVER['REMOTE_USER'])) {
  $_SESSION['username'] = mres($_SERVER['REMOTE_USER']);
  // we don't set a password here because we're using HTTP AUTH
}

$auth_success = 0;

if (isset($_SESSION['username']))
{
  if ($config['auth_mechanism'] == "mysql" || $config['auth_mechanism'] == "http-auth" || !$config['auth_mechanism'])
  {
       $sql = "SELECT username FROM `users` WHERE `username`='".$_SESSION['username'];
       if ($config['auth_mechanism'] != "http-auth") 
       {
           $encrypted = md5($_SESSION['password']);
           $sql .=       "' AND `password`='".$encrypted."';";
       } else { 
           $sql .= "';"; 
       }
       $query = mysql_query($sql);
    $row = @mysql_fetch_array($query);
    if($row['username'] && $row['username'] == $_SESSION['username']) {
      $auth_success = 1;
       } else {
         $_SESSION['username'] = $config['http_auth_guest'];
         $auth_success = 1;
       }
  }
  else if ($config['auth_mechanism'] == "ldap")
  {
    $ds=@ldap_connect($config['auth_ldap_server'],$config['auth_ldap_port']);
    if ($ds)
    {
      if (ldap_bind($ds, $config['auth_ldap_prefix'] . $_SESSION['username'] . $config['auth_ldap_suffix'], $_SESSION['password']))
      {
        if (!$config['auth_ldap_group'])
        {
          $auth_success = 1;
        }
        else
        { 
          if (ldap_compare($ds,$config['auth_ldap_group'],'memberUid',$_SESSION['username']))
          {
            $auth_success = 1;
          }
        }
      }
    }
  }
  else
  {
    echo "ERROR: no valid auth_mechanism defined.";
    exit();
  }
}

if ($auth_success) {
  $sql = "SELECT * FROM `users` WHERE `username`='".$_SESSION['username']."'";
  $query = mysql_query($sql);
  $row = @mysql_fetch_array($query);
  $_SESSION['userlevel'] = $row['level'];
  $_SESSION['user_id'] = $row['user_id'];
  if(!$_SESSION['authenticated']) {
    $_SESSION['authenticated'] = true;
    mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'logged in')");
    header("Location: ".$_SERVER['REQUEST_URI']);
  }
  if(isset($_POST['remember'])) {
    setcookie("username", $_SESSION['username'], time()+60*60*24*100, "/");
    setcookie("password", $_SESSION['password'], time()+60*60*24*100, "/");
  }
} 
elseif (isset($_SESSION['username'])) { 
  $auth_message = "Authentication Failed"; 
  unset ($_SESSION['authenticated']);
  mysql_query("INSERT INTO authlog (`user`,`address`,`result`) VALUES ('".$_SESSION['username']."', '".$_SERVER["REMOTE_ADDR"]."', 'authentication failure')");
} 
?>
