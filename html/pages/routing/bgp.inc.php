<?php

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100% class='sortable'>");
  echo('<tr style="height: 30px"><td width=1></td><th>Local address</th><th></th><th>Peer address</th><th>Type</th><th>Remote AS</th><th>State</th><th>Uptime</th></tr>');

  $i = "1";

  if ($_GET['optb'] == "alerts")
  {
   $where = "AND (B.bgpPeerAdminStatus = 'start' or B.bgpPeerAdminStatus = 'running') AND B.bgpPeerState != 'established'";
  } elseif ($_GET['optb'] == "external") {
   $where = "AND D.bgpLocalAs != B.bgpPeerRemoteAs";
  } elseif ($_GET['optb'] == "internal") {
   $where = "AND D.bgpLocalAs = B.bgpPeerRemoteAs";
  }

  $peer_query = mysql_query("select * from bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id $where ORDER BY D.hostname, B.bgpPeerRemoteAs, B.bgpPeerIdentifier");
  while ($peer = mysql_fetch_assoc($peer_query))
  {
    unset ($alert, $bg_image);

    if (!is_integer($i/2)) { $bg_colour = $list_colour_b; } else { $bg_colour = $list_colour_a; }

    if ($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; if ($_GET['optb'] != "alerts") { $alert=1; $bg_image = "images/1px-pink.png"; } }
    if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "gray"; }

    if ($peer['bgpPeerRemoteAs'] == $peer['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>";
     if ($peer['bgpPeerRemoteAS'] >= '64512' && $peer['bgpPeerRemoteAS'] <= '65535') { $peer_type = "<span style='color: #f00;'>Priv eBGP</span>"; }
    }

    $peerhost = mysql_fetch_assoc(mysql_query("SELECT * FROM ipaddr AS A, ports AS I, devices AS D WHERE A.addr = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"));

    if ($peerhost) { $peername = generate_device_link($peerhost, shorthost($peerhost['hostname'])); } else { unset($peername); }

    // display overlib graphs

    $graph_type       = "bgp_prefixes";
    $local_daily_url  = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=$day&amp;to=$now&amp;width=500&amp;height=150&&afi=ipv4&safi=unicast";
    $localaddresslink = "<span class=list-large><a href='device/" . $peer['device_id'] . "/bgp/prefixes/ipv4.unicast/' onmouseover=\"return overlib('<img src=\'$local_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpLocalAddr'] . "</a></span>";

    $graph_type       = "bgp_updates";
    $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=$day&amp;to=$now&amp;width=500&amp;height=150";
    $peeraddresslink  = "<span class=list-large><a href='device/" . $peer['device_id'] . "/bgp/updates/' onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpPeerIdentifier'] . "</a></span>";

    echo('<tr bgcolor="'.$bg_colour.'"' . ($alert ? ' bordercolor="#cc0000"' : '') . ">
            <td></td>
            <td width=150>" . $localaddresslink . "<br />".generate_device_link($peer, shorthost($peer['hostname']), 'bgp/')."</td>
	     <td width=30>-></td>
            <td width=150>" . $peeraddresslink . "</td>
	     <td width=50><b>$peer_type</b></td>
            <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
            <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
            <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                Updates <img src='images/16/arrow_down.png' align=absmiddle /> " . format_si($peer['bgpPeerInUpdates']) . "
                        <img src='images/16/arrow_up.png' align=absmiddle /> " . format_si($peer['bgpPeerOutUpdates']) . "</td></tr>");


    if($graphs == "graphs")
    {
      $graph_array['height'] = "100";
      $graph_array['width']  = "215";
      $graph_array['to']     = $now;
      $graph_array['id']     = $peer['bgpPeer_id'];
      $graph_array['type']   = "bgp_updates";

      echo('<tr bgcolor="'.$bg_colour.'"' . ($bg_image ? ' background="'.$bg_image.'"' : '') . '"><td colspan="8">');

      include("includes/print-quadgraphs.inc.php");

      echo("</td></tr>");
    }

    $i++;
  }

  echo("</table></div>");
}

?>
