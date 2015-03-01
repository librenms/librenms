<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

$_SERVER['PATH_INFO'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['ORIG_PATH_INFO']);

function logErrors($errno, $errstr, $errfile, $errline) {
    global $php_debug;
    $php_debug[] = array('errno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline);
}

function catchFatal() {
    $last_error = error_get_last();
    if ($last_error['type'] == 1) {
        $log_error = array($last_error['type'],$last_error['message'],$last_error['file'],$last_error['line']);
        print_r($log_error);
    }
}

if (strpos($_SERVER['PATH_INFO'], "debug"))
{
  $debug = "1";
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
  set_error_handler('logErrors');
  register_shutdown_function('catchFatal');
} else {
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

// Set variables
$msg_box = array();

include("../includes/defaults.inc.php");
include("../config.php");
include_once("../includes/definitions.inc.php");
include("../includes/functions.php");
include("includes/functions.inc.php");
include('includes/plugins.inc.php');
Plugins::start();

// Check for install.inc.php
if (!file_exists('../config.php') && $_SERVER['PATH_INFO'] != '/install.php')
{
  // no config.php does so let's redirect to the install
  header('Location: /install.php');
  exit;
}

$runtime_start = utime();

ob_start();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

foreach ($_GET as $key=>$get_var)
{
  if (strstr($key, "opt"))
  {
    list($name, $value) = explode("|", $get_var);
    if (!isset($value)) { $value = "yes"; }
    $vars[$name] = $value;
  }
}

$segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

foreach ($segments as $pos => $segment)
{
  $segment = urldecode($segment);
  if ($pos == "0")
  {
    $vars['page'] = $segment;
  } else {
    list($name, $value) = explode("=", $segment);
    if ($value == "" || !isset($value))
    {
      $vars[$name] = yes;
    } else {
      $vars[$name] = $value;
    }
  }
}

foreach ($_GET as $name => $value)
{
  $vars[$name] = $value;
}

foreach ($_POST as $name => $value)
{
  $vars[$name] = $value;
}

include("includes/authenticate.inc.php");

if (strstr($_SERVER['REQUEST_URI'], 'widescreen=yes')) { $_SESSION['widescreen'] = 1; }
if (strstr($_SERVER['REQUEST_URI'], 'widescreen=no'))  { unset($_SESSION['widescreen']); }

# Load the settings for Multi-Tenancy.
if (isset($config['branding']) && is_array($config['branding']))
{
  if ($config['branding'][$_SERVER['SERVER_NAME']])
  {
    foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval)
    {
        eval("\$config['" . $confitem . "'] = \$confval;");
    }
  } else {
    foreach ($config['branding']['default'] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  }
}

# page_title_prefix is displayed, unless page_title is set
if (isset($config['page_title'])) { $config['page_title_prefix'] = $config['page_title']; }

?>
<!DOCTYPE HTML>
<html>
<head>
  <title><?php echo($config['page_title_suffix']); ?></title>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if ($config['page_refresh']) { echo('  <meta http-equiv="refresh" content="'.$config['page_refresh'].'" />' . "\n"); }
?>
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
  <link href="css/toastr.min.css" rel="stylesheet" type="text/css" />
  <link href="css/typeahead.js-bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
  <link href="css/tagmanager.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo($config['stylesheet']);  ?>" rel="stylesheet" type="text/css" />
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-hover-dropdown.min.js"></script>
  <script src="js/bootstrap-switch.min.js"></script>
  <script src="js/hogan-2.0.0.js"></script>
  <script src="js/jquery.cycle2.min.js"></script>
  <script src="js/moment.min.js"></script>
  <script src="js/bootstrap-datetimepicker.min.js"></script>
  <script src="js/typeahead.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script src="js/tagmanager.js"></script>
<?php
if ($config['favicon']) { echo('  <link rel="shortcut icon" href="'.$config['favicon'].'" />' . "\n"); }
?>
  <script type="text/javascript">

    <!-- Begin
    function popUp(URL)
    {
      day = new Date();
      id = day.getTime();
      eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=600');");
    }
    // End -->
  </script>
  <script type="text/javascript" src="js/overlib_mini.js"></script>
  <script type="text/javascript" src="js/toastr.min.js"></script>
</head>
<body>

<?php

if ((isset($vars['bare']) && $vars['bare'] != "yes") || !isset($vars['bare'])) {

  if ($_SESSION['authenticated'])
  {
    include("includes/print-menubar.php");
  }
}

?>
<br />
<div class="container-fluid">
<?php
if ($_SESSION['authenticated'])
{
?>
  <div class="row">
    <div class="col-md-12">
      &nbsp;<br /><br />
    </div>
  </div>
<?php
}
?>
  <div class="row">
    <div class="col-md-12">
<?php

// To help debug the new URLs :)
if (isset($devel) || isset($vars['devel']))
{
  echo("<pre>");
  print_r($_GET);
  print_r($vars);
  echo("</pre>");
}

if ($_SESSION['authenticated'])
{
  // Authenticated. Print a page.
  if (isset($vars['page']) && !strstr("..", $vars['page']) &&  is_file("pages/" . $vars['page'] . ".inc.php"))
  {
    include("pages/" . $vars['page'] . ".inc.php");
  } else {
    if (isset($config['front_page']) && is_file($config['front_page']))
    {
      include($config['front_page']);
    } else {
      include("pages/front/default.php");
    }
  }

} else {
  // Not Authenticated. Show status page if enabled
  if ( $config['public_status'] === true )
  {
    if (isset($vars['page']) && strstr("login", $vars['page']))
    {
      include("pages/logon.inc.php");
    } else {
      echo '<div id="public-status">';
      include("pages/public.inc.php");
      echo '</div>';
      echo '<div id="public-logon" style="display:none;">';
      echo '<div class="well"><h3>Logon<button class="btn btn-default" type="submit" style="float:right;" id="ToggleStatus">Status</button></h3></div>';
      include ("pages/logon.inc.php");
      echo '</div>';
    }
  }
  else
  {
    include("pages/logon.inc.php");
  }
}
?>
    </div>
  </div>
</div>
<?php

$runtime_end = utime(); $runtime = $runtime_end - $runtime_start;
$gentime = substr($runtime, 0, 5);

# FIXME - move this
if ($config['page_gen'])
{
  echo('  <br />MySQL: Cell    '.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,3).'s'.
                                 ' Row    '.($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,3).'s'.
                                 ' Rows   '.($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,3).'s'.
                                 ' Column '.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,3).'s');

  $fullsize = memory_get_usage();
  unset($cache);
  $cachesize = $fullsize - memory_get_usage();
  if ($cachesize < 0) { $cachesize = 0; } // Silly PHP!

  echo('  <br />Cached data in memory is '.formatStorage($cachesize).'. Page memory usage is '.formatStorage($fullsize).', peaked at '. formatStorage(memory_get_peak_usage()) .'.');
  echo('  <br />Generated in ' . $gentime . ' seconds.');
}

if (isset($pagetitle) && is_array($pagetitle))
{
  # if prefix is set, put it in front
  if ($config['page_title_prefix']) { array_unshift($pagetitle,$config['page_title_prefix']); }

  # if suffix is set, put it in the back
  if ($config['page_title_suffix']) { $pagetitle[] = $config['page_title_suffix']; }

  # create and set the title
  $title = join(" - ",$pagetitle);
  echo("<script type=\"text/javascript\">\ndocument.title = '$title';\n</script>");
}
?>

<?php
if($config['enable_footer'] == 1) {
?>
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
<?php
echo('<em>        Powered by <a href="/about/" target="_blank">' . $config['project_name'].'</a>.</em><br/>');
?>
      </div>
    </div>
  </div>
</footer>
<?php
}

if(dbFetchCell("SELECT COUNT(`device_id`) FROM `devices` WHERE `last_polled` <= DATE_ADD(NOW(), INTERVAL - 15 minute) AND `ignore` = 0 AND `disabled` = 0 AND status = 1",array()) > 0) {
    $msg_box[] = array('type' => 'warning', 'message' => "It appears as though you have some devices that haven't completed polling within the last 15 minutes, you may want to check that out :)",'title' => 'Devices unpolled');
}

if(is_array($msg_box)) {
  echo("<script>
toastr.options.timeout = 10;
toastr.options.extendedTimeOut = 20;
");
  foreach ($msg_box as $message) {
    $message['type'] = mres($message['type']);
    $message['message'] = mres($message['message']);
    $message['title'] = mres($message['title']);
    echo "toastr.".$message['type']."('".$message['message']."','".$message['title']."');\n";
  }
  echo("</script>");
}

if (is_array($sql_debug) && is_array($php_debug)) {

    include_once "includes/print-debug.php";

}

?>

</body>
</html>
