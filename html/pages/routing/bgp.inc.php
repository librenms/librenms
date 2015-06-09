<?php

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  $link_array = array('page' => 'routing', 'protocol' => 'bgp');

  print_optionbar_start('', '');

  echo('<span style="font-weight: bold;">BGP</span> &#187; ');

  if (!$vars['type']) { $vars['type'] = "all"; }

  if ($vars['type'] == "all") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("All",$vars, array('type' => 'all')));
  if ($vars['type'] == "all") { echo("</span>"); }

  echo(" | ");

  if ($vars['type'] == "internal") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("iBGP",$vars, array('type' => 'internal')));
  if ($vars['type'] == "internal") { echo("</span>"); }

  echo(" | ");

  if ($vars['type'] == "external") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("eBGP",$vars, array('type' => 'external')));
  if ($vars['type'] == "external") { echo("</span>"); }

  echo(" | ");

  if ($vars['adminstatus'] == "stop")
  {
    echo("<span class='pagemenu-selected'>");
    echo(generate_link("Shutdown",$vars, array('adminstatus' => NULL)));
    echo("</span>");
  } else {
    echo(generate_link("Shutdown",$vars, array('adminstatus' => 'stop')));
  }

  echo(" | ");

  if ($vars['adminstatus'] == "start")
  {
    echo("<span class='pagemenu-selected'>");
    echo(generate_link("Enabled",$vars, array('adminstatus' => NULL)));
    echo("</span>");
  } else {
    echo(generate_link("Enabled",$vars, array('adminstatus' => 'start')));
  }

  echo(" | ");

  if ($vars['state'] == "down")
  {
    echo("<span class='pagemenu-selected'>");
    echo(generate_link("Down",$vars, array('state' => NULL)));
    echo("</span>");
  } else {
    echo(generate_link("Down",$vars, array('state' => 'down')));
  }

  // End BGP Menu

  if (!isset($vars['view'])) { $vars['view'] = 'details'; }

  echo('<div style="float: right;">');

  if ($vars['view'] == "details") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("No Graphs",$vars, array('view' => 'details', 'graph' => 'NULL')));
  if ($vars['view'] == "details") { echo("</span>"); }

  echo(" | ");

  if ($vars['graph'] == "updates") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("Updates",$vars, array('view' => 'graphs', 'graph' => 'updates')));
  if ($vars['graph'] == "updates") { echo("</span>"); }

  echo(" | Prefixes: Unicast (");
  if ($vars['graph'] == "prefixes_ipv4unicast") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("IPv4",$vars, array('view' => 'graphs', 'graph' => 'prefixes_ipv4unicast')));
  if ($vars['graph'] == "prefixes_ipv4unicast") { echo("</span>"); }

  echo("|");

  if ($vars['graph'] == "prefixes_ipv6unicast") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("IPv6",$vars, array('view' => 'graphs', 'graph' => 'prefixes_ipv6unicast')));
  if ($vars['graph'] == "prefixes_ipv6unicast") { echo("</span>"); }

  echo("|");

  if ($vars['graph'] == "prefixes_ipv4vpn") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("VPNv4",$vars, array('view' => 'graphs', 'graph' => 'prefixes_ipv4vpn')));
  if ($vars['graph'] == "prefixes_ipv4vpn") { echo("</span>"); }
  echo(")");

  echo(" | Multicast (");
  if ($vars['graph'] == "prefixes_ipv4multicast") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("IPv4",$vars, array('view' => 'graphs', 'graph' => 'prefixes_ipv4multicast')));
  if ($vars['graph'] == "prefixes_ipv4multicast") { echo("</span>"); }

  echo("|");

  if ($vars['graph'] == "prefixes_ipv6multicast") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("IPv6",$vars, array('view' => 'graphs', 'graph' => 'prefixes_ipv6multicast')));
  if ($vars['graph'] == "prefixes_ipv6multicast") { echo("</span>"); }
  echo(")");

  echo(" | MAC (");
  if ($vars['graph'] == "macaccounting_bits") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("Bits",$vars, array('view' => 'graphs', 'graph' => 'macaccounting_bits')));
  if ($vars['graph'] == "macaccounting_bits") { echo("</span>"); }

  echo("|");

  if ($vars['graph'] == "macaccounting_pkts") { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link("Packets",$vars, array('view' => 'graphs', 'graph' => 'macaccounting_pkts')));
  if ($vars['graph'] == "macaccounting_pkts") { echo("</span>"); }
  echo(")");

  echo('</div>');

  print_optionbar_end();

  echo("<table border=0 cellspacing=0 cellpadding=5 width=100% class='sortable'>");
  echo('<tr style="height: 30px"><td width=1></td><th>Local address</th><th></th><th>Peer address</th><th>Type</th><th>Family</th><th>Remote AS</th><th>State</th><th width=200>Uptime / Updates</th></tr>');

  if ($vars['type'] == "external")
  {
   $where = "AND D.bgpLocalAs != B.bgpPeerRemoteAs";
  } elseif ($vars['type'] == "internal") {
   $where = "AND D.bgpLocalAs = B.bgpPeerRemoteAs";
  }

  if ($vars['adminstatus'] == "stop")
  {
    $where .= " AND (B.bgpPeerAdminStatus = 'stop')";
  } elseif ($vars['adminstatus'] == "start")
  {
    $where .= " AND (B.bgpPeerAdminStatus = 'start')";
  }

  if ($vars['state'] == "down")
  {
    $where .= " AND (B.bgpPeerState != 'established')";
  }

  $peer_query = "select * from bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id ".$where." ORDER BY D.hostname, B.bgpPeerRemoteAs, B.bgpPeerIdentifier";
  foreach (dbFetchRows($peer_query) as $peer)
  {
    unset ($alert, $bg_image);

    if ($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $peer['alert']=1; }
    if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "gray"; }
    if ($peer['bgpPeerAdminStatus'] == "stop") { $peer['alert']=0; $peer['disabled']=1; }
    if ($peer['bgpPeerRemoteAs'] == $peer['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>";
     if ($peer['bgpPeerRemoteAS'] >= '64512' && $peer['bgpPeerRemoteAS'] <= '65535') { $peer_type = "<span style='color: #f00;'>Priv eBGP</span>"; }
    }

    $peerhost = dbFetchRow("SELECT * FROM ipaddr AS A, ports AS I, devices AS D WHERE A.addr = ? AND I.port_id = A.port_id AND D.device_id = I.device_id", array($peer['bgpPeerIdentifier']));

    if ($peerhost) { $peername = generate_device_link($peerhost, shorthost($peerhost['hostname'])); } else { unset($peername); }

    // display overlib graphs

    $graph_type       = "bgp_updates";
    $local_daily_url  = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150&&afi=ipv4&safi=unicast";
    if (filter_var($peer['bgpLocalAddr'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE) {
        $peer_ip = Net_IPv6::compress($peer['bgpLocalAddr']);
    } else {
        $peer_ip = $peer['bgpLocalAddr'];
    }
    $localaddresslink = "<span class=list-large><a href='device/device=" . $peer['device_id'] . "/tab=routing/proto=bgp/' onmouseover=\"return overlib('<img src=\'$local_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer_ip . "</a></span>";

    $graph_type       = "bgp_updates";
    $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150";
    if (filter_var($peer['bgpPeerIdentifier'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE) {
        $peer_ident = Net_IPv6::compress($peer['bgpPeerIdentifier']);
    } else {
        $peer_ident = $peer['bgpPeerIdentifier'];
    }

    $peeraddresslink  = "<span class=list-large><a href='device/device=" . $peer['device_id'] . "/tab=routing/proto=bgp/' onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer_ident . "</a></span>";

    echo('<tr class="bgp"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') . ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . ">");

    unset($sep);
    foreach (dbFetchRows("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerIdentifier = ?", array($peer['device_id'], $peer['bgpPeerIdentifier'])) as $afisafi)
    {
      $afi = $afisafi['afi'];
      $safi = $afisafi['safi'];
      $this_afisafi = $afi.$safi;
      $peer['afi'] .= $sep . $afi .".".$safi;
      $sep = "<br />";
      $peer['afisafi'][$this_afisafi] = 1; // Build a list of valid AFI/SAFI for this peer
    }
    unset($sep);

    echo("  <td></td>
            <td width=150>" . $localaddresslink . "<br />".generate_device_link($peer, shorthost($peer['hostname']), array('tab' => 'routing', 'proto' => 'bgp'))."</td>
            <td width=30><b>&#187;</b></td>
            <td width=150>" . $peeraddresslink . "</td>
            <td width=50><b>$peer_type</b></td>
            <td width=50>".$peer['afi']."</td>
            <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
            <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
            <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                Updates <img src='images/16/arrow_down.png' align=absmiddle /> " . format_si($peer['bgpPeerInUpdates']) . "
                        <img src='images/16/arrow_up.png' align=absmiddle /> " . format_si($peer['bgpPeerOutUpdates']) . "</td></tr>");

    unset($invalid);
    switch ($vars['graph'])
    {
      case 'prefixes_ipv4unicast':
      case 'prefixes_ipv4multicast':
      case 'prefixes_ipv4vpn':
      case 'prefixes_ipv6unicast':
      case 'prefixes_ipv6multicast':
        list(,$afisafi) = explode("_", $vars['graph']);
        if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      case 'updates':
        $graph_array['type']   = "bgp_" . $vars['graph'];
        $graph_array['id']     = $peer['bgpPeer_id'];
    }

    switch ($vars['graph'])
    {
      case 'macaccounting_bits':
      case 'macaccounting_pkts':
        $acc = dbFetchRow("SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = ? AND M.mac = I.mac_address AND P.port_id = M.port_id AND D.device_id = P.device_id", array($peer['bgpPeerIdentifier']));
        $database = $config['rrd_dir'] . "/" . $device['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
        if (is_array($acc) && is_file($database))
        {
          $peer['graph']       = 1;
          $graph_array['id']   = $acc['ma_id'];
          $graph_array['type'] = $vars['graph'];
        }
    }

    if ($vars['graph'] == 'updates') { $peer['graph'] = 1; }

    if ($peer['graph'])
    {
        $graph_array['height'] = "100";
        $graph_array['width']  = "218";
        $graph_array['to']     = $config['time']['now'];
        echo('<tr></tr><tr class="bgp"' . ($bg_image ? ' background="'.$bg_image.'"' : '') . '"><td colspan="9">');

        include("includes/print-graphrow.inc.php");

        echo("</td></tr>");
    }
  }

  echo("</table>");
}

?>
