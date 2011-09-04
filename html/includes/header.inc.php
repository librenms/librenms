    <div id="top" style="background: <?php echo($config['header_color']); ?>;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td align="left"></td>
          <td align="right">

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
          </td>
        </tr>
      </table>
    </div>

    <div id="header" style="border: 1px none #ccf;">
      <table width="100%" style="padding: 0px; margin:0px;">
        <tr>
          <td style="padding: 0px; margin:0px; border: none;">
            <div id="logo" style="padding: 10px"><a href="index.php"><img src="<?php echo($config['title_image']); ?>" alt="Logo" border="0" /></a></div>
          </td>
          <td align="right" style="margin-right: 10px;">
            <div id="topnav" style="float: right;">
<?php
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'])
{
  include("includes/topnav.inc.php");
}
?>
            </div>
          </td>
        </tr>
      </table>
    </div>
