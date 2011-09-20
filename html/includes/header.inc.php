<div id="gumax-header">
  <div id="gumax-p-logo">
    <div id="p-logo">
      <a style="background-image: url('images/observium-logo.png');" accesskey="z" href=""></a>
    </div>
    <script type="text/javascript"> if (window.isMSIE55) fixalpha(); </script>
  </div>
  <!-- end of gumax-p-logo -->

  <!-- Login Tools -->
  <div id="gumax-p-login">

<?php

$toggle_url = preg_replace('/(\?|\&)widescreen=(yes|no)/', '', $_SERVER['REQUEST_URI']);
if (strstr($toggle_url,'?')) { $toggle_url .= '&amp;'; } else { $toggle_url .= '?'; }

if($_SESSION['widescreen'] === 1)
{
  echo('<a href="' . $toggle_url . 'widescreen=no" title="Switch to normal screen width layout">Normal width</a> | ');
} else {
  echo('<a href="' . $toggle_url . 'widescreen=yes" title="Switch to wide screen layout">Widescreen</a> | ');
}

if ($_SESSION['authenticated'])
{
  echo("Logged in as <b>".$_SESSION['username']."</b> (<a href='?logout=yes'>Logout</a>)");
} else {
  echo("Not logged in!");
}

if (Net_IPv6::checkIPv6($_SERVER['REMOTE_ADDR'])) {
  echo(' via <b>IPv6</b>');
} else {
  echo(' via <b>IPv4</b>');
}
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



