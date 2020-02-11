<?php
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;

session_start();
$librenms_dir = realpath(__DIR__ . '/..');

if (empty($_POST) && !empty($_SESSION) && !isset($_REQUEST['stage'])) {
    $_POST = $_SESSION;
} elseif (!file_exists("{$librenms_dir}/config.php")) {
    $allowed_vars = array('stage','build-ok','dbhost','dbuser','dbpass','dbname','dbport','dbsocket','add_user','add_pass','add_email');
    foreach ($allowed_vars as $allowed) {
        if (isset($_POST[$allowed])) {
            $_SESSION[$allowed] = $_POST[$allowed];
        }
    }
}

$stage = isset($_POST['stage']) ? $_POST['stage'] : 0;

// Before we do anything, if we see config.php, redirect back to the homepage.
if (file_exists("{$librenms_dir}/config.php") && $stage != 6) {
    unset($_SESSION['stage']);
    header("Location: /");
    exit;
}

// do not use the DB in init, we'll bring it up ourselves
$init_modules = array('web', 'nodb');
require realpath(__DIR__ . '/..') . '/includes/init.php';

// List of php modules we expect to see
$modules = array('gd','mysqlnd', 'pdo_mysql');

$dbhost = @$_POST['dbhost'] ?: 'localhost';
$dbuser = @$_POST['dbuser'] ?: 'librenms';
$dbpass = @$_POST['dbpass'] ?: '';
$dbname = @$_POST['dbname'] ?: 'librenms';
$dbport = @$_POST['dbport'] ?: 3306;
if (empty($_POST['dbsocket'])) {
    $dbsocket = null;
} else {
    $dbhost = 'localhost';
    $dbsocket = $_POST['dbsocket'];
    $dbport = null;
}

$add_user = @$_POST['add_user'] ?: '';
$add_pass = @$_POST['add_pass'] ?: '';
$add_email = @$_POST['add_email'] ?: '';


// Check we can connect to MySQL DB, if not, back to stage 1 :)
if ($stage > 1) {
    try {
        if ($stage != 6) {
            dbConnect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbsocket);
            if (dbIsConnected() === false) {
                $msg = "We could not connect to your database, please check the details and try again";
                $stage = 1;
            }
        }
        if ($stage == 2 && $_SESSION['build-ok'] == true) {
            $stage = 3;
            $msg = "It appears that the database is already setup so have moved onto stage $stage";
        }
    } catch (\LibreNMS\Exceptions\DatabaseConnectException $e) {
        $stage = 1;
        $msg = "Couldn't connect to the database, please check your details<br /> " . $e->getMessage();
    }
    $_SESSION['stage'] = $stage;
}

session_write_close();

if ($stage == 4) {
    // Now check we have a username, password and email before adding new user
    if (empty($add_user) || empty($add_pass) || empty($add_email)) {
        $stage = 3;
        $msg = "You haven't entered enough information to add the user account, please check below and re-try";
    }
} elseif ($stage == 6) {
    // If we get here then let's do some final checks.
    if (!file_exists("{$librenms_dir}/config.php")) {
        // config.php file doesn't exist. go back to that stage
        $msg = "config.php still doesn't exist";
        $stage = 5;
    } else {
        // all done, remove all traces of the install session
        session_unset();
        session_destroy();
        setcookie(session_name(), '', 0, '/');
        session_regenerate_id(true);
    }
}

if (empty($stage)) {
    $stage = 0;
}

$total_stages = 6;
$stage_perc = $stage / $total_stages * 100;
$complete = 1;

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <title><?php echo(Config::get('page_title_prefix')); ?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo(Config::get('stylesheet')); ?>" rel="stylesheet" type="text/css"/>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-hover-dropdown.min.js"></script>
  <script src="js/hogan-2.0.0.js"></script>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
          <h2 class="text-center">Welcome to the <?php echo(Config::get('project_name')); ?> install</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <h4 class="text-center">Stage <?php echo $stage; ?> of <?php echo $total_stages; ?> complete</h4>
      </div>
    </div>
<?php

if (!empty($msg)) {
?>
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="alert alert-danger"><?php echo $msg; ?></div>
      </div>
    </div>

<?php
}
?>

    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div id="install-progress" class="progress progress-striped">
          <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $stage_perc; ?>"
               aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $stage_perc; ?>%">
            <span class="sr-only"><?php echo $stage_perc; ?>% Complete</span>
          </div>
        </div>
      </div>
    </div>

<?php

if ($stage == 0) {
?>

    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <h4 class="text-center">Pre-Install Checks</h4>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <table class="table table-condensed table-bordered">
          <tr>
            <th>Item</th>
            <th>Status</th>
            <th>Comments</th>
          </tr>
<?php

$complete = true;
foreach ($modules as $extension) {
    if (extension_loaded("$extension")) {
        $status = 'installed';
        $row_class = 'success';
    } else {
        $status = 'missing';
        $row_class = 'danger';
        $complete = false;
    }

    echo "<tr class='$row_class'><td>PHP module <strong>$extension</strong></td><td>$status</td><td></td></tr>";
}

if (is_writable(session_save_path() === '' ? '/tmp' : session_save_path())) {
    $status = 'yes';
    $row_class = 'success';
} else {
    $status = 'no';
    $row_class = 'danger';
    $complete = false;
}

echo "<tr class='$row_class'><td>Session directory writable</td><td>$status</td><td>";
if ($status == 'no') {
    echo session_save_path() . " is not writable";
    if (function_exists('posix_getgrgid')) {
        $group_info = posix_getgrgid(filegroup(session_save_path()));
        if ($group_info['gid'] !== 0) {  // don't suggest adding users to the root group
            $group = $group_info['name'];
            $user = get_current_user();
            echo ", suggested fix <strong>usermod -a -G $group $user</strong>";
        }
    }
}
echo "</td></tr>";
    
if (is_writable(Config::get('temp_dir'))) {
    $status = 'yes';
    $row_class = 'success';
} else {
    $status = 'no';
    $row_class = 'danger';
    $complete = false;
}

echo "<tr class='$row_class'><td>Temporary directory writable</td><td>$status</td><td>";
if ($status == 'no') {
    echo Config::get('temp_dir') . ' is not writable';
    if (function_exists('posix_getgrgid')) {
        $group_info = posix_getgrgid(filegroup(session_save_path()));
        if ($group_info['gid'] !== 0) {  // don't suggest adding users to the root group
            $group = $group_info['name'];
            $user = get_current_user();
            echo ", suggested fix <strong>chown $user:$group $php_temp_dir</strong>";
        }
    }
}
echo "</td></tr>";
?>
        </table>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <form class="form-inline" role="form" method="post">
            <?php echo csrf_field() ?>
          <input type="hidden" name="stage" value="1">
          <button type="submit" class="btn btn-success pull-right"
            <?php
            if (!$complete) {
                echo "disabled='disabled'";
            }
            ?>>Next Stage</button>
        </form>
      </div>
    </div>

<?php
} elseif ($stage == 1) {
?>

    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <form class="form-horizontal" role="form" method="post">
            <?php echo csrf_field() ?>
          <input type="hidden" name="stage" value="2">
          <div class="form-group">
            <label for="dbhost" class="col-sm-4" control-label">DB Host: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbhost" id="dbhost" value="<?php echo $dbhost; ?>" placeholder="Leave empty if using Unix-Socket">
            </div>
          </div>
          <div class="form-group">
            <label for="dbport" class="col-sm-4" control-label">DB Port: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbport" id="dbport" value="<?php echo $dbport; ?>" placeholder="Leave empty if using Unix-Socket">
            </div>
          </div>
          <div class="form-group">
            <label for="dbsocket" class="col-sm-4" control-label">DB Unix-Socket: </label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="dbsocket" id="dbsocket" value="<?php echo $dbsocket; ?>" placeholder="Leave empty if using Host">
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
          <button type="submit" class="btn btn-success pull-right">Next Stage</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>

<?php
} elseif ($stage == "2") {
?>
    <div class="row">
     <div class="col-md-3">
     </div>
     <div class="col-md-6">
         <h5 class="text-center">Importing MySQL DB - Do not close this page or interrupt the import</h5>
        <textarea readonly id="db-update" class="form-control" rows="20" placeholder="Please Wait..." style="resize:vertical;"></textarea>
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
            <?php echo csrf_field() ?>
          <input type="hidden" name="stage" value="3">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <input type="hidden" name="dbport" value="<?php echo $dbport; ?>">
          <input type="hidden" name="dbsocket" value="<?php echo $dbsocket; ?>">
          <input type="button" id="retry-btn" value="Retry" onClick="window.location.reload()" style="display: none;" class="btn btn-success">
          <button type="submit" id="add-user-btn" class="btn btn-success pull-right" disabled>Goto Add User</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
    <script type="text/javascript">
        var output = document.getElementById("db-update");
        xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax_output.php?id=db-update", true);
        xhr.onprogress = function (e) {
            output.innerHTML = e.currentTarget.responseText;
            output.scrollTop = output.scrollHeight - output.clientHeight; // scrolls the output area
            if (output.innerHTML.indexOf('Error!') !== -1) {
                // if error word in output, show the retry button
                $("#retry-btn").css("display", "");
            }
        };
        xhr.timeout = 90000; // if no response for 90s, allow the user to retry
        xhr.ontimeout = function (e) {
            $("#retry-btn").css("display", "");
        };
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log("Complete");
                    $('#add-user-btn').removeAttr('disabled');
                }
                $('#install-progress').removeClass('active')
            }
        };
        xhr.send();
        $('#install-progress').addClass('active')
    </script>
<?php
} elseif ($stage == "5") {
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
\$config\['db_port'\] = '$dbport';
\$config\['db_user'\] = '$dbuser';
\$config\['db_pass'\] = '$dbpass';
\$config\['db_name'\] = '$dbname';
\$config\['db_socket'\] = '$dbsocket';

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
\$config['user'] = 'librenms';

### Locations - it is recommended to keep the default
#\$config\['install_dir'\]  = "$install_dir";

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

# Update configuration
#\$config\['update_channel'\] = 'release';  # uncomment to follow the monthly release channel
#\$config\['update'\] = 0;  # uncomment to completely disable updates
EOD;

if (!file_exists("{$librenms_dir}/config.php")) {
    $conf = fopen("config.php", 'w');
    if ($conf != false) {
        if (fwrite($conf, "<?php\n") === false) {
            echo("<div class='alert alert-danger'>We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)</div>");
            echo("<pre>&lt;?php\n".stripslashes($config_file)."</pre>");
        } else {
            $config_file = stripslashes($config_file);
            fwrite($conf, $config_file);
            echo("<div class='alert alert-success'>The config file has been created</div>");
        }
    } else {
        echo("<div class='alert alert-danger'>We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)</div>");
        echo("<pre>&lt;?php\n".stripslashes($config_file)."</pre>");
    }
}
?>
      <form class="form-horizontal" role="form" method="post">
        <?php echo csrf_field() ?>
        <input type="hidden" name="stage" value="6">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <input type="hidden" name="dbsocket" value="<?php echo $dbsocket; ?>">
        <button type="submit" class="btn btn-success pull-right">Finish install</button>
      </form>
<?php

?>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
} elseif ($stage == "3") {
?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <form class="form-horizontal" role="form" method="post">
            <?php echo csrf_field() ?>
          <input type="hidden" name="stage" value="4">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <input type="hidden" name="dbsocket" value="<?php echo $dbsocket; ?>">
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
          <button type="submit" class="btn btn-success pull-right">Add User</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
} elseif ($stage == "4") {
    $proceed = 1;
?>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
<?php
if (LegacyAuth::get()->canManageUsers()) {
    if (!LegacyAuth::get()->userExists($add_user)) {
        if (LegacyAuth::get()->addUser($add_user, $add_pass, '10', $add_email)) {
            echo("<div class='alert alert-success'>User has been added successfully</div>");
            $proceed = 0;
        } else {
            echo("<div class='alert alert-danger'>User hasn't been added, please try again</div>");
        }
    } else {
        echo("<div class='alert alert-danger'>User $add_user already exists!</div>");
    }
} else {
    echo("<div class='alert alert-danger'>Auth module isn't loaded</div>");
}

?>
        <form class="form-horizontal" role="form" method="post">
            <?php echo csrf_field() ?>
          <input type="hidden" name="stage" value="5">
          <input type="hidden" name="dbhost" value="<?php echo $dbhost; ?>">
          <input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>">
          <input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>">
          <input type="hidden" name="dbname" value="<?php echo $dbname; ?>">
          <input type="hidden" name="dbsocket" value="<?php echo $dbsocket; ?>">
          <button type="submit" class="btn btn-success pull-right"
            <?php
            if ($proceed == "1") {
                echo "disabled='disabled'";
            }
            ?>>Generate Config</button>
        </form>
      </div>
      <div class="col-md-3">
      </div>
    </div>
<?php
} elseif ($stage == "6") {
?>
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="alert alert-danger">
                <p>You haven't quite finished yet!</p>
                <p>First, you need to <a href="validate/">validate your install and fix any issues.</a></p>
            </div>
        </div>
    </div>
    <div class="row">
      <div class="col-md-3">
      </div>
      <div class="col-md-6">
        <div class="alert alert-success">
            <p>Thank you for setting up LibreNMS.</p>
            <p>It would be great if you would consider contributing to our statistics, you can do this on the <a href="about/">About LibreNMS Page</a> and check the box under Statistics.</p>
      </div>
      <div class="col-md-3">
      </div>
<?php
}
?>

  </div>
</body>
</html>
