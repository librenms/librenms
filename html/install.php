<?php
session_start();
if( empty($_POST) && !empty($_SESSION) && !isset($_REQUEST['stage'])) {
    $_POST = $_SESSION;
} else {
    $_SESSION = $_POST;
}

$stage = $_POST['stage'];

// Before we do anything, if we see config.php, redirect back to the homepage.
if(file_exists('../config.php') && $stage != "6") {
    header("Location: /");
    exit;
}

// List of php modules we expect to see
$modules = array('gd','mysql','snmp','mcrypt');

$dbhost = @$_POST['dbhost'] ?: 'localhost';
$dbuser = @$_POST['dbuser'] ?: 'librenms';
$dbpass = @$_POST['dbpass'] ?: '';
$dbname = @$_POST['dbname'] ?: 'librenms';
$add_user = @$_POST['add_user'] ?: '';
$add_pass = @$_POST['add_pass'] ?: '';
$add_email = @$_POST['add_email'] ?: '';

if($stage == "4" || $stage == "3") {
    // Ok now let's set the db connection up
    $config['db_host']=$dbhost;
    $config['db_user']=$dbuser;
    $config['db_pass']=$dbpass;
    $config['db_name']=$dbname;
}

require '../includes/defaults.inc.php';
$config['db']['extension']='mysqli';
// Work out the install directory
$cur_dir = explode('/',__DIR__);
$check = end($cur_dir);
if( empty($check) ) {
    $install_dir = array_pop($cur_dir);
}
unset($check);
$install_dir = array_pop($cur_dir);
$install_dir = implode('/',$cur_dir);
$config['install_dir'] = $install_dir;
$config['log_dir'] = $install_dir.'/logs';
if($_POST['stage'] == "3" || $_POST['stage'] == "4") {
    require_once '../includes/definitions.inc.php';
}
require '../includes/functions.php';
require 'includes/functions.inc.php';

// Check we can connect to MySQL DB, if not, back to stage 1 :)
if($stage == 2 || $stage == 3) {
    $database_link = mysqli_connect('p:'.$dbhost,$dbuser,$dbpass,$dbname);
    if(mysqli_connect_error()) {
        $stage = 1;
        $msg = "Couldn't connect to the database, please check your details<br /> " . mysqli_connect_error();
    }
    elseif ($stage != 3) {
        if($_SESSION['build-ok'] == true) {
                    $stage = 3;
                    $msg = "It appears that the database is already setup so have moved onto stage $stage";
        }
    }
    $_SESSION['stage'] = $stage;
}
elseif($stage == "4") {
    // Now check we have a username, password and email before adding new user
    if(empty($add_user) || empty($add_pass) || empty($add_email)) {
        $stage = 3;
        $msg = "You haven't entered enough information to add the user account, please check below and re-try";
    }
}
elseif($stage == "6") {
    session_destroy();
    // If we get here then let's do some final checks.
    if(!file_exists("../config.php")) {
        // config.php file doesn't exist. go back to that stage
        $msg = "config.php still doesn't exist";
        $stage = "5";
    }
}

if(empty($stage)) {
    $stage = '0';
}

$total_stages = 6;
$stage_perc = $stage / $total_stages * 100;
$complete = 1;

?>

<!DOCTYPE HTML>
<html>
<head>
  <title><?php echo($config['page_title_prefix']); ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo($config['stylesheet']);  ?>" rel="stylesheet" type="text/css" />
  <link href="css/typeahead.js-bootstrap.css" rel="stylesheet" type="text/css" />
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-hover-dropdown.min.js"></script>
  <script src="js/typeahead.min.js"></script>
  <script src="js/hogan-2.0.0.js"></script>

</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <h2 class="text-center">Welcome to the <?php echo($config['project_name']); ?> install</h2>
      </div>
      <div class="col-md-3">
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <h4 class="text-center">Stage <?php echo $stage; ?> of <?php echo $total_stages; ?> complete</h2>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php

if(!empty($msg)) {

?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <div class="alert alert-danger"><?php echo $msg; ?></div>
      </div>
      <div class="col-md-3">
      </div>
    </div>

<?php
}
?>

    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <div class="progress progress-striped">
          <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $stage_perc; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $stage_perc; ?>%">
            <span class="sr-only"><?php echo $stage_perc; ?>% Complete</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
      </div>
    </div>

<?php

if($stage == 0) {

?>

    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <h5 class="text-center">Checking PHP module support</h5>
      </div>
      <div class="col-md-3">
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <table class="table table-condensed table-bordered">
          <tr>
            <th>Module</th>
            <th>Installed</th>
            <th>Comments</th>
          </tr>
<?php

    foreach ($modules as $extension) {
        if (extension_loaded("$extension")) {
            $ext_loaded = 'yes';
            $row_class = 'success';
        }
        else {
            $ext_loaded = 'no';
            $row_class = 'danger';
            $complete = 0;
        }

        echo("   <tr class='$row_class'>
            <td>$extension</td>
            <td>$ext_loaded</td>");
        if($ext_loaded == 'no') {
            echo("<td>apt-get install php5-$extension / yum install php-$extension</td>");
        }
        else {
            echo("<td></td>");
        }
        echo("</tr>");
    }

    // Check for pear install
    @include_once 'System.php';

    if(class_exists('System') === true) {
        $ext_loaded = 'yes';
        $row_class = 'success';
    }
    else {
        $ext_loaded = 'no';
        $row_class = 'danger';
    }

    echo("     <tr class='$row_class'>
        <td>pear</td>
        <td>$ext_loaded</td>");
    if($ext_loaded == 'no') {
        echo("<td>apt-get install php5-$extension / yum install php-$extension</td>");
    }
    else {
        echo("<td></td>");
    }
    echo("</tr>");
?>
        </table>
      </div>
      <div class="col-md-3">
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <form class="form-inline" role="form" method="post">
          <input type="hidden" name="stage" value="1">
          <button type="submit" class="btn btn-success" <?php if($complete == '0') echo "disabled='disabled'"; ?>>Next Stage</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>

<?php
}
elseif($stage == 1) {

?>

    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <form class="form-horizontal" role="form" method="post">
          <input type="hidden" name="stage" value="2">
          <div class="form-group">
            <label for="dbhost" class="col-sm-4" control-label">DB Host: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbhost" id="dbhost" value="<?php echo $dbhost; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="dbuser" class="col-sm-4" control-label">DB User: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbuser" id="dbuser" value="<?php echo $dbuser; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="dbpass" class="col-sm-4" control-label">DB Pass: </label>
            <div class="col-sm-8">
              <input type="password" class="form-control" name="dbpass" id="dbpass" value="<?php echo $dbpass; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="dbname" class="col-sm-4" control-label">DB Name: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbname" id="dbname" value="<?php echo $dbname; ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-success">Next Stage</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>

<?php

}
elseif($stage == "2") {
?>
    <div class="row">
     <div class="col-md-3">
     </div>
     <div class="col-md-6">
         <h5 class="text-center">Importing MySQL DB - Do not close this page or interrupt the import</h5>
<?php
// Ok now let's set the db connection up
    $config['db_host']=$dbhost;
    $config['db_user']=$dbuser;
    $config['db_pass']=$dbpass;
    $config['db_name']=$dbname;
    $config['db']['extension']='mysqli';
    $sql_file = '../build.sql';
    $_SESSION['last'] = time();
    ob_end_flush();
    ob_start();
    if ($_SESSION['offset'] < 100 && $_REQUEST['offset'] < 94) {
        require '../build-base.php';
    }
    else {
        require '../includes/sql-schema/update.php';
    }
    $_SESSION['out'] .= ob_get_clean();
    ob_end_clean();
    ob_start();
    echo $GLOBALS['refresh'];
    echo "<pre>".trim($_SESSION['out'])."</pre>";
?>
     </div>
     <div class="col-md-3">
     </div>
    </div>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        If you don't see any errors or messages above then the database setup has been successful.<br />
        <form class="form-horizontal" role="form" method="post">
          <input type="hidden" name="stage" value="3">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <button type="submit" class="btn btn-success">Goto Add User</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
}
elseif($stage == "5") {

?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
<?php

// Create the config file we will write or display
$config_file = <<<"EOD"
## Have a look in defaults.inc.php for examples of settings you can set here. DO NOT EDIT defaults.inc.php!

### Database config
\$config\['db_host'\] = '$dbhost';
\$config\['db_user'\] = '$dbuser';
\$config\['db_pass'\] = '$dbpass';
\$config\['db_name'\] = '$dbname';
\$config\['db'\]\['extension'\] = "mysqli";// mysql or mysqli

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
\$config['user'] = 'librenms';

### Memcached config - We use this to store realtime usage
\$config\['memcached'\]\['enable'\]  = FALSE;
\$config\['memcached'\]\['host'\]    = "localhost";
\$config\['memcached'\]\['port'\]    = 11211;

### Locations - it is recommended to keep the default
\$config\['install_dir'\]  = "$install_dir";

### This should *only* be set if you want to *force* a particular hostname/port
### It will prevent the web interface being usable form any other hostname
#\$config\['base_url'\]        = "http://librenms.company.com";

### Enable this to use rrdcached. Be sure rrd_dir is within the rrdcached dir
### and that your web server has permission to talk to rrdcached.
#\$config\['rrdcached'\]    = "unix:/var/run/rrdcached.sock";

### Default community
\$config\['snmp'\]\['community'\] = array("public");

### Authentication Model
\$config\['auth_mechanism'\] = "mysql"; # default, other options: ldap, http-auth
#\$config\['http_auth_guest'\] = "guest"; # remember to configure this user if you use http-auth

### List of RFC1918 networks to allow scanning-based discovery
#\$config\['nets'\]\[\] = "10.0.0.0/8";
#\$config\['nets'\]\[\] = "172.16.0.0/12";
#\$config\['nets'\]\[\] = "192.168.0.0/16";

# following is necessary for poller-wrapper
# poller-wrapper is released public domain
\$config\['poller-wrapper'\]\['alerter'\] = FALSE;
# Uncomment the next line to disable daily updates
#\$config\['update'\] = 0;
EOD;

if(!file_exists("../config.php")) {
    $conf = fopen("../config.php", 'w');
    if ($conf != false) {
        if(fwrite($conf, "<?php\n") === FALSE) {
            echo("<div class='alert alert-danger'>We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)</div>");
            echo("<pre>&lt;?php\n".stripslashes($config_file)."</pre>");
        }
        else {
            $config_file = stripslashes($config_file);
            fwrite($conf,$config_file);
            echo("<div class='alert alert-success'>The config file has been created</div>");
        }
    }
    else {
        echo("<div class='alert alert-danger'>We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)</div>");
        echo("<pre>&lt;?php\n".stripslashes($config_file)."</pre>");
    }
}
?>
      <form class="form-horizontal" role="form" method="post">
        <input type="hidden" name="stage" value="6">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
        <button type="submit" class="btn btn-success">Finish install</button>
      </form>
<?php

?>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php

}
elseif($stage == "3") {
?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <form class="form-horizontal" role="form" method="post">
          <input type="hidden" name="stage" value="4">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <div class="form-group">
            <label for="add_user" class="col-sm-4 control-label">Username</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="add_user" id="add_user" value="<?php echo $add_user; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="add_pass" class="col-sm-4 control-label">Password</label>
            <div class="col-sm-8">
              <input type="password" class="form-control" name="add_pass" id="add_pass" value="<?php echo $add_pass; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="add_email" class="col-sm-4 control-label">Email</label>
            <div class="col-sm-8">
              <input type="email" class="form-control" name="add_email" id="add_email" value="<?php echo $add_email; ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-success">Add User</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
}
elseif($stage == "4") {
    $proceed = 1;
?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
<?php
    require 'includes/authenticate.inc.php';
    if (auth_usermanagement()) {
        if (!user_exists($add_user)) {
            if (adduser($add_user,$add_pass,'10',$add_email)) {
                echo("<div class='alert alert-success'>User has been added successfully</div>");
                $proceed = 0;
            }
            else {
                echo("<div class='alert alert-danger'>User hasn't been added, please try again</div>");
            }
        }
        else {
            echo("<div class='alert alert-danger'>User $add_user already exists!</div>");
        }
    }
    else {
        echo("<div class='alert alert-danger'>Auth module isn't loaded</div>");
    }

?>
        <form class="form-horizontal" role="form" method="post">
          <input type="hidden" name="stage" value="5">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <button type="submit" class="btn btn-success" <?php if($proceed == "1") echo "disabled='disabled'"; ?>>Generate Config</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
}
elseif($stage == "6") {
?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <div class="alert alert-success">Thank you for setting up LibreNMS, you can now click <a href="/">here to login to your new install.</a></div>
      </div>
      <div class="col-md-3">
      </div>
<?php
}
?>

  </div>
</body>
</html>
