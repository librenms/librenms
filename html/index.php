<?php
  ob_start();
  
  ini_set('allow_url_fopen', 0);
  ini_set('display_errors', 0);

#$debug=1;
if($debug) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
}

  include("../config.php"); 
  include("../includes/functions.php");  
  include("includes/functions.inc.php");
  include("includes/authenticate.inc");
  $start = utime();

  $now = time();
  $day = time() - (24 * 60 * 60);
  $twoday = time() - (2 * 24 * 60 * 60);
  $week = time() - (7 * 24 * 60 * 60);
  $month = time() - (31 * 24 * 60 * 60);
  $year = time() - (365 * 24 * 60 * 60);
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
  if($config['page_refresh']) { echo("<meta http-equiv='refresh' content='".$config['page_refresh']."'>"); }
?>
  <link href="<?php  echo($config['stylesheet']);  ?>" rel="stylesheet" type="text/css" />
  <link rel="shortcut icon" href="<?php  echo($config['favicon']);  ?>" />
  <link rel="stylesheet" href="css/mktree.css" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">
<script type="text/javascript" src="js/mktree.js"></script>
<script type="text/javascript">
<!-- Begin
function popUp(URL) {
  day = new Date();
  id = day.getTime();
  eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=600');"); 
}
// End -->
</script>
<script type="text/javascript" src="js/overlib.js"></script>
<div id="center">
<div id="top" style="background: <?php echo($config['header_color']); ?>;">
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="left"></td>
<td align="right">
  <?php
     if($_SESSION['authenticated']) { 
       echo("Logged in as <b>".$_SESSION['username']."</b> (<a href='?logout=yes'>Logout</a>)"); 
     } else { 
       echo("Not logged in!"); 
     } 
      if( Net_IPv6::checkIPv6($_SERVER['REMOTE_ADDR'])) { echo(" via <b>IPv6</b>"); } else { echo(" via <b>IPv4</b>"); }
  ?>
 </td></tr>
</table>
</div>

<div id="header" style="border: 1px none #ccf;">
  <table width="100%" style="padding: 0px; margin:0px;">
    <tr>
      <td style="padding: 0px; margin:0px; border: none;">
        <div id="logo" style="padding: 10px"><a href="index.php"><img src="<?php echo($config['title_image']); ?>" alt="Logo" border="0" /></a></div>
      </td>
      <td align="center"><?php
        
        $data = trim(shell_exec("cat " . $config['install_dir'] . "/rrd/version.txt"));
              
        list($major, $minor, $release) = explode(".", $data);
                list($cur, $tag) = explode("-", $config['version']);
                list($cur_major, $cur_minor, $cur_release) = explode(".", $cur);

                if($major > $cur_major) {
                  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif ($major == $cur_major && $minor > $cur_minor) {
		  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif ($major == $cur_major && $minor == $cur_minor && $release > $cur_release) {
		  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif($major < $cur_major || ($major == $cur_major && $minor < $cur_minor) || ($major == $cur_major && $minor == $cur_minor && $release < $cur_release)) {
                }

      ?></td>
      <td align="right" style="margin-right: 10px;">
        <div id="topnav" style="float: right;">
 	  <?php if($_SESSION['authenticated']) {
	    include("includes/topnav.inc");		
	  } ?>
        </div>
      </td>
    </tr>
  </table>
</div>


<?php if($_SESSION['authenticated']) {include("includes/print-menubar.php");} else {echo('<hr color="#444444" />');} ?>

<div class="clearer"></div>

<div class="content-mat" style="border: 1px none #fcc;">
<div id="content" style="border: 1px none #ccc; min-height:650px;">
<div style="margin: 7px;"></div>
<?php
  if($_SESSION['authenticated']) {
    include("includes/warn-deleted-interfaces.inc.php");
    ## Authenticated. Print a page.
    if($_GET['page'] && !strstr("..", $_GET['page']) &&  is_file("pages/" . $_GET['page'] . ".php")) {
      include("pages/" . $_GET['page'] . ".php");
    } else { 
      if($config['front_page']) {
        include($config['front_page']);
      } else {
        include("pages/front/default.php");
      }
    }

  } else {
    ## Not Authenticated. Print login.
    include("pages/logon.inc");
    exit;
  } 
?>
                </div>
                <div class="clearer"></div>
            </div>
        </div>
 </div>
<?php
    $end = utime(); $run = $end - $start;
    $gentime = substr($run, 0, 5);
    echo('<br /> <div id="footer">Generated in ' . $gentime . ' seconds 
          <br /> <a href="http://www.observernms.org">Observer ' . $config['version'] . '</a> &copy; 2006-2010 Adam Armstrong
          <br />This work is licensed under the <a href="http://www.gnu.org/licenses/gpl-3.0-standalone.html">GPL, version 3</a>.</div>');
	            
?>


</body>
</html>

