<?php

# This file prints a table row for each interface

$interface['device_id'] = $device['device_id'];
$interface['hostname'] = $device['hostname'];

$if_id = $interface['interface_id'];

$interface = ifLabel($interface);

if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

if ($interface['ifInErrors_delta'] > 0 || $interface['ifOutErrors_delta'] > 0)
{
  $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>","port_errors");
} else {
  $error_img = "";
}

echo("<tr style=\"background-color: $row_colour; padding: 5px;\" valign=top onmouseover=\"this.style.backgroundColor='$list_highlight';\" onmouseout=\"this.style.backgroundColor='$row_colour';\"
onclick=\"location.href='device/".$device['device_id']."/port/".$interface['interface_id']."/'\" style='cursor: pointer;'>
 <td valign=top width=350>");
echo("        <span class=list-large>
              " . generate_port_link($interface, $interface['ifIndex'] . ". ".$interface['label']) . "
           </span><br /><span class=interface-desc>".$interface['ifAlias']."</span>");

if ($interface['ifAlias']) { echo("<br />"); }

unset ($break);
if ($port_details)
{
  foreach (dbFetchRows("SELECT * FROM `ipv4_addresses` WHERE `interface_id` = ?", array($interface['interface_id'])) as $ip)
  {
    echo("$break <a class=interface-desc href=\"javascript:popUp('netcmd.php?cmd=whois&amp;query=".$ip['ipv4_address']."')\">".$ip['ipv4_address']."/".$ip['ipv4_prefixlen']."</a>");
    $break = ",";
  }
  foreach (dbFetchRows("SELECT * FROM `ipv6_addresses` WHERE `interface_id` = ?", array($interface['interface_id']) as $ip6);
  {
    echo("$break <a class=interface-desc href=\"javascript:popUp('netcmd.php?cmd=whois&amp;query=".$ip6['ipv6_address']."')\">".Net_IPv6::compress($ip6['ipv6_address'])."/".$ip6['ipv6_prefixlen']."</a>");
    $break = ",";
  }
}

echo("</span>");

$width="120"; $height="40"; $from = $day;

echo("</td><td width=135>");
echo(formatRates($interface['ifInOctets_rate'] * 8)." <img class='optionicon' src='images/icons/arrow_updown.png' /> ".formatRates($interface['ifOutOctets_rate'] * 8));
echo("<br />");
$interface['graph_type'] = "port_bits";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

echo("</td><td width=135>");
echo("".formatRates($interface['adslAturChanCurrTxRate']) . "/". formatRates($interface['adslAtucChanCurrTxRate']));
echo("<br />");
$interface['graph_type'] = "port_adsl_speed";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

echo("</td><td width=135>");
echo("".formatRates($interface['adslAturCurrAttainableRate']) . "/". formatRates($interface['adslAtucCurrAttainableRate']));
echo("<br />");
$interface['graph_type'] = "port_adsl_attainable";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

echo("</td><td width=135>");
echo("".$interface['adslAturCurrAtn'] . "dB/". $interface['adslAtucCurrAtn'] . "dB");
echo("<br />");
$interface['graph_type'] = "port_adsl_attenuation";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

echo("</td><td width=135>");
echo("".$interface['adslAturCurrSnrMgn'] . "dB/". $interface['adslAtucCurrSnrMgn'] . "dB");
echo("<br />");
$interface['graph_type'] = "port_adsl_snr";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

echo("</td><td width=135>");
echo("".$interface['adslAturCurrOutputPwr'] . "dBm/". $interface['adslAtucCurrOutputPwr'] . "dBm");
echo("<br />");
$interface['graph_type'] = "port_adsl_power";
echo(generate_port_link($interface, "<img src='graph.php?type=".$interface['graph_type']."&amp;id=".$interface['interface_id']."&amp;from=".$from."&amp;to=".$now."&amp;width=".$width."&amp;height=".$height."&amp;legend=no&amp;bg=".
str_replace("#","", $row_colour)."'>", $interface['graph_type']));

#  if ($interface[ifDuplex] != unknown) { echo("<span class=box-desc>Duplex " . $interface['ifDuplex'] . "</span>"); } else { echo("-"); }

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

if ($graph_type == "etherlike")
{
  $graph_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/etherlike-". safename($interface['ifIndex']) . ".rrd";
} else {
  $graph_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/". safename($interface['ifIndex']) . ".rrd";
}

if ($graph_type && is_file($graph_file))
{
  $type = $graph_type;

  $daily_traffic = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$from&amp;to=$now&amp;width=210&amp;height=100";
  $daily_url = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$from&amp;to=$now&amp;width=500&amp;height=150";

  $weekly_traffic = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$week&amp;to=$now&amp;width=210&amp;height=100";
  $weekly_url = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";

  $monthly_traffic = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$month&amp;to=$now&amp;width=210&amp;height=100";
  $monthly_url = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$month&amp;to=$now&amp;width=500&amp;height=150";

  $yearly_traffic = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$year&amp;to=$now&amp;width=210&amp;height=100";
  $yearly_url = "graph.php?port=$if_id&amp;type=" . $graph_type . "&amp;from=$year&amp;to=$now&amp;width=500&amp;height=150";

  echo("<tr style='background-color: $bg; padding: 5px;'><td colspan=7>");

  echo("<a href='device/" . $interface['device_id'] . "/port/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\"
    onmouseout=\"return nd();\"> <img src='$daily_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/port/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\"
    onmouseout=\"return nd();\"> <img src='$weekly_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/port/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
    onmouseout=\"return nd();\"> <img src='$monthly_traffic' border=0></a>");
  echo("<a href='device/" . $interface['device_id'] . "/port/" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
    onmouseout=\"return nd();\"> <img src='$yearly_traffic' border=0></a>");

  echo("</td></tr>");
}

?>
