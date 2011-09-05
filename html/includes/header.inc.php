<div id="gumax-header">
  <div id="gumax-p-logo">
    <div id="p-logo">
      <a style="background-image: url('images/observium-logo.png');" accesskey="z"></a>
    </div>
    <script type="text/javascript"> if (window.isMSIE55) fixalpha(); </script>
  </div>
  <!-- end of gumax-p-logo -->

  <!-- Login Tools -->
  <div id="gumax-p-login">

<?php
if($_SESSION['widescreen'] === 1){
  echo('<a href="/?widescreen=no">Switch to Normal Width</a>');
}else{
  echo('<a href="/?widescreen=yes">Switch to Widescreen</a>');
}
?>


<?php
if ($_SESSION['authenticated'])
{
  echo("Logged in as <b>".$_SESSION['username']."</b> (<a href='?logout=yes'>Logout</a>)");
} else {
  echo("Not logged in!");
}

if (Net_IPv6::checkIPv6($_SERVER['REMOTE_ADDR'])) { echo(" via <b>IPv6</b>"); } else { echo(" via <b>IPv4</b>"); }
?>

  </div>
  <div style="float: right;">


<?php
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'])
{
  include("includes/topnav.inc.php");
}
?>
  </div>
</div>

	<!-- ///// end of gumax-header ///// -->



