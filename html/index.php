<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage webinterface
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

if (empty($_SERVER['PATH_INFO'])) {
    if (strstr($_SERVER['SERVER_SOFTWARE'], "nginx") && isset($_SERVER['PATH_TRANSLATED']) && isset($_SERVER['ORIG_SCRIPT_FILENAME'])) {
            $_SERVER['PATH_INFO'] = str_replace($_SERVER['PATH_TRANSLATED'] . $_SERVER['PHP_SELF'], "", $_SERVER['ORIG_SCRIPT_FILENAME']);
    } else {
        $_SERVER['PATH_INFO'] = isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '';
    }
}

function logErrors($errno, $errstr, $errfile, $errline)
{
    global $php_debug;
    $php_debug[] = array('errno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline);
}

function catchFatal()
{
    $last_error = error_get_last();
    if ($last_error['type'] == 1) {
        $log_error = array($last_error['type'],$last_error['message'],$last_error['file'],$last_error['line']);
        print_r($log_error);
    }
}

if (strpos($_SERVER['REQUEST_URI'], "debug")) {
    $debug = true;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', E_ALL);
    set_error_handler('logErrors');
    register_shutdown_function('catchFatal');
    $sql_debug = array();
    $php_debug = array();
} else {
    $debug = false;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

// Set variables
$msg_box = array();
// Check for install.inc.php
if (!file_exists('../config.php') && $_SERVER['PATH_INFO'] != '/install.php') {
    // no config.php does so let's redirect to the install
    header('Location: install.php');
    exit;
}

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

$config['memcached']['ttl'] = $config['time']['now']+300;

LibreNMS\Plugins::start();

$runtime_start = microtime(true);

ob_start();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

if (strstr($_SERVER['REQUEST_URI'], 'widescreen=yes')) {
    $_SESSION['widescreen'] = 1;
}
if (strstr($_SERVER['REQUEST_URI'], 'widescreen=no')) {
    unset($_SESSION['widescreen']);
}

# Load the settings for Multi-Tenancy.
if (isset($config['branding']) && is_array($config['branding'])) {
    if ($config['branding'][$_SERVER['SERVER_NAME']]) {
        foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval) {
            eval("\$config['" . $confitem . "'] = \$confval;");
        }
    } else {
        foreach ($config['branding']['default'] as $confitem => $confval) {
            eval("\$config['" . $confitem . "'] = \$confval;");
        }
    }
}

# page_title_prefix is displayed, unless page_title is set
if (isset($config['page_title'])) {
    $config['page_title_prefix'] = $config['page_title'];
}

?>
<!DOCTYPE HTML>
<html>
<head>
  <title><?php echo($config['page_title_suffix']); ?></title>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if (empty($config['favicon'])) {
?>
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="images/manifest.json">
  <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="images/favicon.ico">
  <meta name="msapplication-config" content="images/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
<?php
} else {
    echo('  <link rel="shortcut icon" href="'.$config['favicon'].'" />' . "\n");
}
?>
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
  <link href="css/toastr.min.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery.bootgrid.min.css" rel="stylesheet" type="text/css" />
  <link href="css/tagmanager.css" rel="stylesheet" type="text/css" />
  <link href="css/mktree.css" rel="stylesheet" type="text/css" />
  <link href="css/vis.min.css" rel="stylesheet" type="text/css" />
  <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery.gridster.min.css" rel="stylesheet" type="text/css" />
  <link href="css/leaflet.css" rel="stylesheet" type="text/css" />
  <link href="css/MarkerCluster.css" rel="stylesheet" type="text/css" />
  <link href="css/MarkerCluster.Default.css" rel="stylesheet" type="text/css" />
  <link href="css/leaflet.awesome-markers.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo($config['stylesheet']);  ?>?ver=291727419" rel="stylesheet" type="text/css" />
  <link href="css/<?php echo $config['site_style']; ?>.css?ver=632417638" rel="stylesheet" type="text/css" />
<?php

foreach ((array)$config['webui']['custom_css'] as $custom_css) {
    echo '<link href="' . $custom_css . '" rel="stylesheet" type="text/css" />';
}

?>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-hover-dropdown.min.js"></script>
  <script src="js/bootstrap-switch.min.js"></script>
  <script src="js/hogan-2.0.0.js"></script>
  <script src="js/jquery.cycle2.min.js"></script>
  <script src="js/moment.min.js"></script>
  <script src="js/bootstrap-datetimepicker.min.js"></script>
  <script src="js/typeahead.bundle.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script src="js/tagmanager.js"></script>
  <script src="js/mktree.js"></script>
  <script src="js/jquery.bootgrid.min.js"></script>
  <script src="js/handlebars.min.js"></script>
  <script src="js/pace.min.js"></script>
    <?php
    if ($config['enable_lazy_load'] === true) {
    ?>
  <script src="js/jquery.lazyload.min.js"></script>
  <script src="js/lazyload.js"></script>
    <?php
    }
    ?>
  <script src="js/librenms.js"></script>
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

if (empty($_SESSION['screen_width']) && empty($_SESSION['screen_height'])) {
    echo "<script>updateResolution();</script>";
}

if ((isset($vars['bare']) && $vars['bare'] != "yes") || !isset($vars['bare'])) {
    if ($_SESSION['authenticated']) {
        require 'includes/print-menubar.php';
    }
} else {
    echo "<style>body { padding-top: 0px !important;
    padding-bottom: 0px !important; }</style>";
}

?>
<br />
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
<?php

// To help debug the new URLs :)
if (isset($devel) || isset($vars['devel'])) {
    echo("<pre>");
    print_r($_GET);
    print_r($vars);
    echo("</pre>");
}

if ($_SESSION['authenticated']) {
    // Authenticated. Print a page.
    if (isset($vars['page']) && !strstr("..", $vars['page']) &&  is_file("pages/" . $vars['page'] . ".inc.php")) {
        require "pages/" . $vars['page'] . ".inc.php";
    } else {
        if (isset($config['front_page']) && is_file($config['front_page'])) {
            require $config['front_page'];
        } else {
            require 'pages/front/default.php';
        }
    }
} else {
    // Not Authenticated. Show status page if enabled
    if ($config['public_status'] === true) {
        if (isset($vars['page']) && strstr("login", $vars['page'])) {
            require 'pages/logon.inc.php';
        } else {
            echo '<div id="public-status">';
            require 'pages/public.inc.php';
            echo '</div>';
            echo '<div id="public-logon" style="display:none;">';
            echo '<div class="well"><h3>Logon<button class="btn btn-default" type="submit" style="float:right;" id="ToggleStatus">Status</button></h3></div>';
            require 'pages/logon.inc.php';
            echo '</div>';
        }
    } else {
        require 'pages/logon.inc.php';
    }
}
?>
    </div>
  </div>
</div>
<?php

$runtime_end = microtime(true);
$runtime = $runtime_end - $runtime_start;
$gentime = substr($runtime, 0, 5);

# FIXME - move this
if ($config['page_gen']) {
    echo '<br />';
    printStats();

    $fullsize = memory_get_usage();
    unset($cache);
    $cachesize = $fullsize - memory_get_usage();
    if ($cachesize < 0) {
        $cachesize = 0;
    } // Silly PHP!

    echo('  <br />Cached data in memory is '.formatStorage($cachesize).'. Page memory usage is '.formatStorage($fullsize).', peaked at '. formatStorage(memory_get_peak_usage()) .'.');
    echo('  <br />Generated in ' . $gentime . ' seconds.');
}

if (isset($pagetitle) && is_array($pagetitle)) {
    # if prefix is set, put it in front
    if ($config['page_title_prefix']) {
        array_unshift($pagetitle, $config['page_title_prefix']);
    }

    # if suffix is set, put it in the back
    if ($config['page_title_suffix']) {
        $pagetitle[] = $config['page_title_suffix'];
    }

    # create and set the title
    $title = join(" - ", $pagetitle);
    echo("<script type=\"text/javascript\">\ndocument.title = '$title';\n</script>");
}
?>

<?php
if ($config['enable_footer'] == 1 && (isset($vars['bare']) && $vars['bare'] != "yes")) {
?>
<nav class="navbar navbar-default <?php echo $navbar; ?> navbar-fixed-bottom">
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
<?php
echo('<h5>Powered by <a href="' . $config['project_home'] . '" target="_blank" rel="noopener" class="red">' . $config['project_name'].'</a>.</h5>');
?>
      </div>
    </div>
  </div>
</nav>
<?php
}

if (dbFetchCell("SELECT COUNT(`device_id`) FROM `devices` WHERE `last_polled` <= DATE_ADD(NOW(), INTERVAL - 15 minute) AND `ignore` = 0 AND `disabled` = 0 AND status = 1", array()) > 0) {
    $msg_box[] = array('type' => 'warning', 'message' => "<a href=\"poll-log/filter=unpolled/\">It appears as though you have some devices that haven't completed polling within the last 15 minutes, you may want to check that out :)</a>",'title' => 'Devices unpolled');
}

if (is_array($msg_box)) {
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

if (is_array($sql_debug) && is_array($php_debug) && $_SESSION['authenticated'] === true) {
    require_once "includes/print-debug.php";
}

if ($no_refresh !== true && $config['page_refresh'] != 0) {
    $refresh = $config['page_refresh'] * 1000;
    echo('<script type="text/javascript">
        $(document).ready(function() {

           $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
           var Countdown = {
               sec: '. $config['page_refresh'] .',

               Start: function() {
                   var cur = this;
                   this.interval = setInterval(function() {
                       $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
                       cur.sec -= 1;
                       display_time = cur.sec;
                       if (display_time == 0) {
                           location.reload();
                       }
                       if (display_time % 1 === 0 && display_time <= 300) {
                           $("#countdown_timer").html("<i class=\"fa fa-clock-o fa-fw fa-lg\"></i> Refresh in " + display_time);
                       }
                   }, 1000);
               },

               Pause: function() {
                   clearInterval(this.interval);
                   $("#countdown_timer_status").html("<i class=\"fa fa-play fa-fw fa-lg\"></i> Resume");
                   delete this.interval;
               },

               Resume: function() {
                   if (!this.interval) this.Start();
               }
           };

           Countdown.Start();

           $("#countdown_timer_status").click("", function(event) {
               event.preventDefault();
               if (Countdown.interval) {
                   Countdown.Pause();
               } else {
                   Countdown.Resume();
               }
           });

           $("#countdown_timer").click("", function(event) {
               event.preventDefault();
           });

        });
    </script>');
} else {
    echo('<script type="text/javascript">
    var no_refresh = ' . var_export((bool)$no_refresh, true) . ';
    $(document).ready(function() {
        $("#countdown_timer").html("Refresh disabled");
        $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i>");
        $("#countdown_timer_status").click("", function(event) {
            event.preventDefault();
        });
     });
</script>');
}

?>
</body>
</html>
