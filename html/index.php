<?php 
  ini_set('allow_url_fopen', 0);

if($_GET[debug]) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
}

  include("../config.php"); 
  include("../includes/functions.php");  
  include("includes/authenticate.inc");
  $start = utime();

  $now = time();
  $day = time() - (24 * 60 * 60);
  $twoday = time() - (2 * 24 * 60 * 60);
  $week = time() - (7 * 24 * 60 * 60);
  $month = time() - (31 * 24 * 60 * 60);
  $year = time() - (365 * 24 * 60 * 60);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml2/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title><?php echo("$page_title"); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <!-- <meta http-equiv="refresh" content="300"> -->
  <link href="<?php  echo($stylesheet);  ?>" rel="stylesheet" type="text/css">
  <link rel="shortcut icon" href="<?php  echo($favicon);  ?>" />
</head>
<SCRIPT LANGUAGE="JavaScript">
<!-- Begin
function popUp(URL) {
  day = new Date();
  id = day.getTime();
  eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=600');"); 
}
// End -->
</script>
<script type="text/javascript" src="js/overlib.js"></script>
<body topmargin=0 leftmargin=0 rightmargin=0 bottommargin=0>
<div id="center">
<div id="top">
<table cellpadding=0 cellspacing=0 width=100%>
<tr>
<td align=left></td>
<td align=right>
  <? 
     if($_SESSION['authenticated']) { 
       echo("Logged in as <b>$_SESSION[username]</b> (<a href='?logout=yes'>Logout</a>)"); 
     } else { 
       echo("Not logged in!"); 
     } 
  ?>
 </td></tr>
</table>
</div>

<div id="header" style="border: 1px none #ccf;">
  <table width="100%" style="padding: 0px; margin:0px;">
    <tr>
      <td style="padding: 0px; margin:0px; border: none;">
        <div id=logo style="padding: 10px"><a href="/"><img src="<?php echo("$title_image"); ?>" border="0" /></a></div>
      </td>
      <td align=right style="margin-right: 10px;">
        <div id="topnav" style="float: right;">
 	  <?php if($_SESSION['authenticated']) {
	    include("includes/topnav.inc");		
	  } ?>
        </div>
      </td>
    </tr>
  </table>
</div>

<?php if($_SESSION['authenticated']) {include("includes/print-menubar.php");} else {echo("<hr colour=#444444 />");} ?>

<div class=clearer></div>


<div class="content-mat" style="border: 1px none #fcc;">
<div id="content" style="border: 1px none #ccc; min-height:650px;">
<div style="margin: 7px;"></div>
<?php
  if($_SESSION['authenticated']) {
    if($_GET['page'] && is_file("pages/" . $_GET['page'] . ".php")) {
      include("pages/" . $_GET['page'] . ".php");
    } else { 
      include("pages/default.php");
    }
  } else {
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
    echo("<br /> <center>Generated in $gentime seconds 
          <br /> <a href='http://www.project-observer.org'>Project Observer $observer_version</a> &copy; 2006-2007 Adam Armstrong
          <br /> Development Sponsored by <a href='http://www.vostron.net'>Vostron</a>.");
          
?>
</body>
</html>

