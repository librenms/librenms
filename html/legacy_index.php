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

use LibreNMS\Config;

if (empty($_SERVER['PATH_INFO'])) {
    if (strstr($_SERVER['SERVER_SOFTWARE'], "nginx") && isset($_SERVER['PATH_TRANSLATED']) && isset($_SERVER['ORIG_SCRIPT_FILENAME'])) {
            $_SERVER['PATH_INFO'] = str_replace($_SERVER['PATH_TRANSLATED'] . $_SERVER['PHP_SELF'], "", $_SERVER['ORIG_SCRIPT_FILENAME']);
    } else {
        $_SERVER['PATH_INFO'] = isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '';
    }
}


// Set variables
$msg_box = array();

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug(str_contains($_SERVER['REQUEST_URI'], 'debug'));

LibreNMS\Plugins::start();

$runtime_start = microtime(true);

ob_start();

ini_set('allow_url_fopen', 0);

if (strstr($_SERVER['REQUEST_URI'], 'widescreen=yes')) {
    $_SESSION['widescreen'] = 1;
}
if (strstr($_SERVER['REQUEST_URI'], 'widescreen=no')) {
    unset($_SESSION['widescreen']);
}

# Load the settings for Multi-Tenancy.
if (isset($config['branding']) && is_array($config['branding'])) {
    if (isset($config['branding'][$_SERVER['SERVER_NAME']])) {
        $config = array_replace_recursive($config, $config['branding'][$_SERVER['SERVER_NAME']]);
    } else {
        $config = array_replace_recursive($config, $config['branding']['default']);
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
  <link href="css/L.Control.Locate.min.css" rel="stylesheet" type="text/css" />
  <link href="css/leaflet.awesome-markers.css" rel="stylesheet" type="text/css" />
  <link href="css/select2.min.css" rel="stylesheet" type="text/css" />
  <link href="css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="css/query-builder.default.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo($config['stylesheet']);  ?>?ver=20181128" rel="stylesheet" type="text/css" />
  <link href="css/<?php echo $config['site_style']; ?>.css?ver=632417642" rel="stylesheet" type="text/css" />
<?php

foreach ((array)$config['webui']['custom_css'] as $custom_css) {
    echo '<link href="' . $custom_css . '" rel="stylesheet" type="text/css" />';
}

?>
  <script src="js/polyfill.min.js"></script>
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
  <script src="js/qrcode.min.js"></script>
    <?php
    if ($config['enable_lazy_load'] === true) {
    ?>
  <script src="js/jquery.lazyload.min.js"></script>
  <script src="js/lazyload.js"></script>
    <?php
    }
    ?>
  <script src="js/select2.min.js"></script>
  <script src="js/librenms.js?ver=20181130"></script>
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
    if (Auth::check()) {
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

if (isset($vars['page']) && !strstr("..", $vars['page']) &&  is_file("pages/" . $vars['page'] . ".inc.php")) {
    require "pages/" . $vars['page'] . ".inc.php";
} else {
    if (isset($config['front_page']) && is_file($config['front_page'])) {
        require $config['front_page'];
    } else {
        require 'pages/front/default.php';
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

if (is_array($msg_box)) {
    echo "<script>
        toastr.options.timeout = 10;
        toastr.options.extendedTimeOut = 20;
        </script>
    ";

    foreach ($msg_box as $message) {
        Toastr::add($message['type'], $message['message'], $message['title']);
    }
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

echo Toastr::render();

?>
</body>
</html>
