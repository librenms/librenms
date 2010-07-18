<?php

#echo("<pre>");
#print_r($interface);
#echo("</pre>");

#  This file prints a table row for each interface 
 
  $interface['device_id'] = $device['device_id'];
  $interface['hostname'] = $device['hostname'];

  $if_id = $interface['interface_id'];

  $interface = ifLabel($interface);

  if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }


  if($interface['ifInErrors_delta'] > 0 || $interface['ifOutErrors_delta'] > 0) { 
    $error_img = generateiflink($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>","port_errors"); 
  } else { $error_img = ""; }

  echo("<tr style=\"background-color: $row_colour; padding: 5px;\" valign=top onmouseover=\"this.style.backgroundColor='$list_highlight';\" onmouseout=\"this.style.backgroundColor='$row_colour';\"
  onclick=\"location.href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/'\" style='cursor: hand;'>
           <td valign=top width=350>");

  echo("        <span class=list-large>
                " . generateiflink($interface, $interface['ifIndex'] . ". ".$interface['label']) . "

             </span><br /><span class=interface-desc>".$interface['ifAlias']."</span>");


  if($interface['ifAlias']) { echo("<br />"); }

  unset ($break);
  if($port_details) {
    $ipdata = mysql_query("SELECT * FROM `ipv4_addresses` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip = mysql_fetch_Array($ipdata)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip[ipv4_address]')\">$ip[ipv4_address]/$ip[ipv4_prefixlen]</a>");
      $break = ",";
    }
    $ip6data = mysql_query("SELECT * FROM `ipv6_addresses` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip6 = mysql_fetch_Array($ip6data)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=".$ip6['ipv6_address']."')\">".Net_IPv6::compress($ip6['ipv6_address'])."/".$ip6['ipv6_prefixlen']."</a>");
      $break = ",";
    }
  }

  echo("</span>");

  $width="120"; $height="40"; $from = $day;

  echo("</td><td width=150>");
  echo("".formatRates($interface['adslAtucChanCurrTxRate']) . "/". formatRates($interface['adslAturChanCurrTxRate']));
  echo("<br />");
  $interface['graph_type'] = "port_adsl_speed";
  echo(generateiflink($interface, "<img src='graph.php?type=".$interface['graph_type']."&port=".$interface['interface_id']."&from=".$from."&to=".$now."&width=".$width."&height=".$height."&legend=no&bg=".
  str_replace("#","", $row_colour)."'>", $interface['graph_type']));

  echo("</td><td width=150>");
  echo("".formatRates($interface['adslAtucCurrAttainableRate']) . "/". formatRates($interface['adslAturCurrAttainableRate']));
  echo("<br />");
  $interface['graph_type'] = "port_adsl_attainable";
  echo(generateiflink($interface, "<img src='graph.php?type=".$interface['graph_type']."&port=".$interface['interface_id']."&from=".$from."&to=".$now."&width=".$width."&height=".$height."&legend=no&bg=".
  str_replace("#","", $row_colour)."'>", $interface['graph_type']));
  
  echo("</td><td width=150>");
  echo("".$interface['adslAtucCurrAtn'] . "dB/". $interface['adslAturCurrAtn'] . "dB");
  echo("<br />");
  $interface['graph_type'] = "port_adsl_attenuation";
  echo(generateiflink($interface, "<img src='graph.php?type=".$interface['graph_type']."&port=".$interface['interface_id']."&from=".$from."&to=".$now."&width=".$width."&height=".$height."&legend=no&bg=".
  str_replace("#","", $row_colour)."'>", $interface['graph_type']));

  echo("</td><td width=150>");
  echo("".$interface['adslAtucCurrSnrMgn'] . "dB/". $interface['adslAturCurrSnrMgn'] . "dB");
  echo("<br />");
  $interface['graph_type'] = "port_adsl_snr";
  echo(generateiflink($interface, "<img src='graph.php?type=".$interface['graph_type']."&port=".$interface['interface_id']."&from=".$from."&to=".$now."&width=".$width."&height=".$height."&legend=no&bg=".
  str_replace("#","", $row_colour)."'>", $interface['graph_type']));

  echo("</td><td width=150>");
  echo("".$interface['adslAtucCurrOutputPwr'] . "dBm/". $interface['adslAturCurrOutputPwr'] . "dBm");
  echo("<br />");
  $interface['graph_type'] = "port_adsl_power";
  echo(generateiflink($interface, "<img src='graph.php?type=".$interface['graph_type']."&port=".$interface['interface_id']."&from=".$from."&to=".$now."&width=".$width."&height=".$height."&legend=no&bg=".
  str_replace("#","", $row_colour)."'>", $interface['graph_type']));


#  if($interface[ifDuplex] != unknown) { echo("<span class=box-desc>Duplex " . $interface['ifDuplex'] . "</span>"); } else { echo("-"); }


#    echo("</td><td width=150>");
#    echo($port_adsl['adslLineCoding']."/".$port_adsl['adslLineType']);
#    echo("<br />");
#    echo("Sync:".formatRates($port_adsl['adslAtucChanCurrTxRate']) . "/". formatRates($port_adsl['adslAturChanCurrTxRate']));
#    echo("<br />");
#    echo("Max:".formatRates($port_adsl['adslAtucCurrAttainableRate']) . "/". formatRates($port_adsl['adslAturCurrAttainableRate']));
#    echo("</td><td width=150>");
#    echo("Atten:".$port_adsl['adslAtucCurrAtn'] . "dB/". $port_adsl['adslAturCurrAtn'] . "dB");
#    echo("<br />");
#    echo("SNR:".$port_adsl['adslAtucCurrSnrMgn'] . "dB/". $port_adsl['adslAturCurrSnrMgn']. "dB");



echo("</td>");

    if($graph_type == "etherlike")
    { 
      $graph_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/etherlike-". safename($interface['ifIndex']) . ".rrd"; 
    } else {
      $graph_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/". safename($interface['ifIndex']) . ".rrd";  
    }

     if($graph_type && is_file($graph_file)) {
 
          $type = $graph_type;

          $daily_traffic = "graph.php?port=$if_id&type=" . $graph_type . "&from=$from&to=$now&width=210&height=100";
          $daily_url = "graph.php?port=$if_id&type=" . $graph_type . "&from=$from&to=$now&width=500&height=150";

          $weekly_traffic = "graph.php?port=$if_id&type=" . $graph_type . "&from=$week&to=$now&width=210&height=100";
          $weekly_url = "graph.php?port=$if_id&type=" . $graph_type . "&from=$week&to=$now&width=500&height=150";

          $monthly_traffic = "graph.php?port=$if_id&type=" . $graph_type . "&from=$month&to=$now&width=210&height=100";
          $monthly_url = "graph.php?port=$if_id&type=" . $graph_type . "&from=$month&to=$now&width=500&height=150";

          $yearly_traffic = "graph.php?port=$if_id&type=" . $graph_type . "&from=$year&to=$now&width=210&height=100";
          $yearly_url = "graph.php?port=$if_id&type=" . $graph_type . "&from=$year&to=$now&width=500&height=150";

  echo("<tr style='background-color: $bg; padding: 5px;'><td colspan=7>");

  echo("<a href='device/" . $interface['device_id'] . "/interface/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" 
        onmouseout=\"return nd();\"> <img src='$daily_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/interface/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$weekly_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/interface/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$monthly_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/interface/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$yearly_traffic' border=0></a>");

  echo("</td></tr>");

      }

   ?>
