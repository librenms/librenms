    <div id="top" style="background: <?php echo($config['header_color']); ?>; height: 5px;">
    </div>

    <div id="header" style="border: 1px none #ccf;">
      <table width="100%" style="padding: 0px; margin:0px;">
        <tr>
          <td style="padding: 0px; margin:0px; border: none;">
            <div id="logo" style="padding: 5px 10px;"><a href="index.php"><img src="<?php echo($config['title_image']); ?>" alt="ObserverNMS Logo" border="0" /></a></div>
          </td>
          <td align="center"><?php

        $data = trim(shell_exec("cat " . $config['install_dir'] . "/rrd/version.txt"));

        list($major, $minor, $release) = explode(".", $data);
                if (strstr('-',$config['version'])) { list($cur, $tag) = explode("-", $config['version']); } else { $cur = $config['version']; }
                list($cur_major, $cur_minor, $cur_release) = explode(".", $cur);

                if($major > $cur_major) {
                  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif ($major == $cur_major && $minor > $cur_minor) {
                  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif ($major == $cur_major && $minor == $cur_minor && $release > $cur_release) {
                  echo("<a href='http://www.observernms.org'><span class=red>New Version! <br /> <b>$major.$minor.$release</b></span></a>");
                } elseif($major < $cur_major || ($major == $cur_major && $minor < $cur_minor) || ($major == $cur_major && $minor == $cur_minor && $release < $cur_release)) {
                }
?>
          </td>
          <td align="right" style="margin-right: 10px;">
            <div id="topnav" style="float: right;">

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
          </td>
        </tr>
      </table>


            </div>
          </td>
        </tr>
      </table>
    </div>

