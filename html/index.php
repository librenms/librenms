<?php

include("../includes/defaults.inc.php");
include("../config.php");
include("../includes/functions.php");
include("includes/functions.inc.php");

$runtime_start = utime();

ob_start();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

$_SERVER['PATH_INFO'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['ORIG_PATH_INFO']);

if (strpos($_SERVER['PATH_INFO'], "debug"))
{
  $debug = "1";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
} else {
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

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
    if (TRUE)  // do this to keep everything working whilst we fiddle --AS
    { // also, who wrote this? Please check php.net/switch ;) --TL
      if ($pos == "1")
      {
        $_GET['opta'] = $segment;
      }
      if ($pos == "2")
      {
        $_GET['optb'] = $segment;
      }
      if ($pos == "3")
      {
        $_GET['optc'] = $segment;
      }
      if ($pos == "4")
      {
        $_GET['optd'] = $segment;
      }
      if ($pos == "5")
      {
        $_GET['opte'] = $segment;
      }
      if ($pos == "6")
      {
        $_GET['optf'] = $segment;
      }
    }
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

$now = time();
$fourhour = time() - (4 * 60 * 60);
$day = time() - (24 * 60 * 60);
$twoday = time() - (2 * 24 * 60 * 60);
$week = time() - (7 * 24 * 60 * 60);
$month = time() - (31 * 24 * 60 * 60);
$year = time() - (365 * 24 * 60 * 60);

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
if ($config['page_title']) { $config['page_title_prefix'] = $config['page_title']; }

?>
<!DOCTYPE HTML>
<html>
<head>
  <title><?php echo($config['page_title_prefix']); ?></title>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
<?php
if ($config['page_refresh']) { echo('  <meta http-equiv="refresh" content="'.$config['page_refresh'].'" />' . "\n"); }
?>
  <link href="<?php echo($config['stylesheet']);  ?>" rel="stylesheet" type="text/css" />
  <link rel="shortcut icon" href="<?php echo($config['favicon']);  ?>" />
  <link rel="stylesheet" href="css/mktree.css" type="text/css" />
<?php
if ($_SESSION['widescreen']) { echo('<link rel="stylesheet" href="css/styles-wide.css" type="text/css" />'); }
?>
</head>
<body>
  <script type="text/javascript" src="js/mktree.js"></script>
  <script type="text/javascript" src="js/sorttable.js"></script>
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/jquery-checkbox.js"></script>
  <script type="text/javascript" src="js/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>
<?php /* html5.js below from http://html5shim.googlecode.com/svn/trunk/html5.js */ ?>
  <!--[if IE]><script src="js/html5.js"></script><![endif]-->
  <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="js/jqplot/excanvas.js"></script><![endif]-->
  <script language="javascript" type="text/javascript" src="js/jqplot/jquery.jqplot.min.js"></script>
  <link rel="stylesheet" type="text/css" href="js/jqplot/jquery.jqplot.min.css" />
  <script type="text/javascript" src="js/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
  <script type="text/javascript" src="js/jqplot/plugins/jqplot.donutRenderer.min.js"></script>
  <script type="text/javascript">
    <!--

    $(function () {
        $('.bubbleInfo').each(function () {
            var distance = 10;
            var time = 250;
            var hideDelay = 500;

            var hideDelayTimer = null;

            var beingShown = false;
            var shown = false;
            var trigger = $('.trigger', this);
            var info = $('.popup', this).css('opacity', 0);

            $([trigger.get(0), info.get(0)]).mouseover(function () {
                if (hideDelayTimer) clearTimeout(hideDelayTimer);
                if (beingShown || shown) {
                    // don't trigger the animation again
                    return;
                } else {
                    // reset position of info box
                    beingShown = true;

                    info.css({
                        top: -90,
                        left: -33,
                        display: 'block'
                    }).animate({
                        top: '-=' + distance + 'px',
                        opacity: 1
                    }, time, 'swing', function() {
                        beingShown = false;
                        shown = true;
                    });
                }

                return false;
            }).mouseout(function () {
                if (hideDelayTimer) clearTimeout(hideDelayTimer);
                hideDelayTimer = setTimeout(function () {
                    hideDelayTimer = null;
                    info.animate({
                        top: '-=' + distance + 'px',
                        opacity: 0
                    }, time, 'swing', function () {
                        shown = false;
                        info.css('display', 'none');
                    });

                }, hideDelay);

                return false;
            });
        });
    });

    //-->
    </script>
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
  <div id="container">

<?php

if (!$vars['bare'] == "yes") {

  include("includes/".$config['web_header']);

  if ($_SESSION['authenticated']) { include("includes/print-menubar.php"); } else { echo('<hr color="#444444" />'); }

}

?>
    <div class="clearer"></div>
    <div class="content-mat">
      <div id="content" style="min-height:650px; width:auto; display:block;">
        <div style="clear:both; height:6px; display:block;"></div>
<?php

### To help debug the new URLs :)
if ($devel || $vars['devel'])
{
  echo("<pre>");
  print_r($_GET);
  print_r($vars);
  echo("</pre>");
}

if ($_SESSION['authenticated'])
{
  ## Authenticated. Print a page.
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
  ## Not Authenticated. Print login.
  include("pages/logon.inc.php");

  exit;
}
?>
        </div>
      <div class="clearer"></div>
  </div>
<?php
$runtime_end = utime(); $runtime = $runtime_end - $runtime_start;
$gentime = substr($runtime, 0, 5);

echo('<br /> <br /> <br /> <br />  <div id="footer">' . (isset($config['footer']) ? $config['footer'] : ''));
echo('<br />Powered by <a href="http://www.observium.org" target="_blank">Observium ' . $config['version']);

echo('</a>. Copyright &copy; 2006-'. date("Y"). ' by Adam Armstrong. All rights reserved.');

if ($config['page_gen'])
{
    echo('<br />MySQL: Cell    '.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,3).'s'.
                      ' Row    '.($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,3).'s'.
                      ' Rows   '.($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,3).'s'.
                      ' Column '.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,3).'s');

    $fullsize = memory_get_usage();
    unset($cache);
    $cachesize = $fullsize - memory_get_usage();
    if ($cachesize < 0) { $cachesize = 0; } // Silly PHP!

    echo('<br />Cached data in memory is '.formatStorage($cachesize).'. Page memory usage is '.formatStorage($fullsize).', peaked at '. formatStorage(memory_get_peak_usage()) .'.');

    echo('<br />Generated in ' . $gentime . ' seconds.');
}
?>
      </div>
    </div>
    <script class="content_tooltips" type="text/javascript">
$(document).ready(function() { $('#content a[title]').qtip({ content: { text: false }, style: 'light' }); });

$('INPUT.auto-hint, TEXTAREA.auto-hint').focus(function() {
    if ($(this).val() == $(this).attr('title')) {
        $(this).val('');
        $(this).removeClass('auto-hint');
    }
});

    </script>

<?php
if (is_array($pagetitle))
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

  </body>
</html>
