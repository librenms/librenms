<?php
ob_start();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

if (strpos($_SERVER['REQUEST_URI'], "debug"))
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

include("../includes/defaults.inc.php");
include("../config.php");
include("../includes/functions.php");
include("includes/functions.inc.php");

$start = utime();
$now = time();
$day = time() - (24 * 60 * 60);
$twoday = time() - (2 * 24 * 60 * 60);
$week = time() - (7 * 24 * 60 * 60);
$month = time() - (31 * 24 * 60 * 60);
$year = time() - (365 * 24 * 60 * 60);

# Load the settings for Multi-Tenancy.
if (is_array($config['branding']))
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title><?php echo($config['page_title']); ?></title>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
<?php
if ($config['page_refresh']) { echo("<meta http-equiv='refresh' content='".$config['page_refresh']."'>"); }
?>
  <link href="<?php echo($config['stylesheet']);  ?>" rel="stylesheet" type="text/css" />
  <link rel="shortcut icon" href="<?php echo($config['favicon']);  ?>" />
  <link rel="stylesheet" href="css/mktree.css" type="text/css" />
</head>
<body>
  <script type="text/javascript" src="js/mktree.js"></script>
  <script type="text/javascript" src="js/sorttable.js"></script>
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
  <script type="text/javascript" src="js/overlib.js"></script>
  <div id="center">

<?php

include("includes/authenticate.inc.php");
include("includes/".$config['web_header']);

if ($_SESSION['authenticated']) { include("includes/print-menubar.php"); } else { echo('<hr color="#444444" />'); }

?>
    <div class="clearer"></div>
    <div class="content-mat" style="border: 1px none #fcc;">
      <div id="content" style="border: 1px none #ccc; min-height:650px;">
        <div style="margin: 7px;"></div>
<?php
if ($_SESSION['authenticated'])
{
  ## Authenticated. Print a page.
  if (isset($_GET['page']) && !strstr("..", $_GET['page']) &&  is_file("pages/" . $_GET['page'] . ".inc.php"))
  {
    include("pages/" . $_GET['page'] . ".inc.php");
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
  </div>
<?php
$end = utime(); $run = $end - $start;
$gentime = substr($run, 0, 5);

echo('<br /> <br /> <br /> <br />  <div id="footer">' . (isset($config['footer']) ? $config['footer'] : ''));
echo('<br />Powered by <a href="http://www.observium.org" target="_blank">Observium ' . $config['version']);

if (file_exists('.svn/entries'))
{
  $svn = File('.svn/entries');
  echo('-SVN r' . trim($svn[3]));
  unset($svn);
}

echo('</a>. Copyright &copy; 2006-'. date("Y"). ' by Adam Armstrong. All rights reserved.');

if ($config['page_gen'])
{
    echo('<br />Generated in ' . $gentime . ' seconds.');
}

echo('</div>');
?>
</body>
</html>
