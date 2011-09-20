<?php

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
}
else
{

  print_optionbar_start('', '');

  echo('<span style="font-weight: bold;">BGP</span> &#187; ');

  if (!$_GET['optb']) { $_GET['optb'] = "all"; }

  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "all") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/all/'.$graphs.'/">All</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "all") { echo("</span>"); }
  echo(' | ');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "internal") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/internal/'.$graphs.'/">Internal</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "internal") { echo("</span>"); }
  echo(" | ");
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "external") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/external/'.$graphs.'/">External</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "external") { echo("</span>"); }
  echo(" | ");
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "disabled") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/disabled/'.$graphs.'/">Disabled</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "disabled") { echo("</span>"); }
  echo(" | ");
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "alerts") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/alerts/'.$graphs.'/">Alerts</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "alerts") { echo("</span>"); }

  echo('');
  ## End BGP Menu

  echo('<div style="float: right;">');

  if (!$_GET['optc']) { $_GET['optc'] = "nographs"; }

  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "nographs") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/nographs/">No Graphs</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "nographs") { echo("</span>"); }
  echo(" | ");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "updates") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/updates/">Updates</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "updates") { echo("</span>"); }

  echo(" | Prefixes: Unicast (");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4unicast") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/prefixes_ipv4unicast/">IPv4</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4unicast") { echo("</span>"); }
  echo("|");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv6unicast") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/prefixes_ipv6unicast/">IPv6</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv6unicast") { echo("</span>"); }
  echo("|");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4vpn") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/prefixes_ipv4vpn/">VPNv4</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4vpn") { echo("</span>"); }
  echo(")");

  echo(" | Multicast (");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4multicast") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/prefixes_ipv4multicast/">IPv4</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv4multicast") { echo("</span>"); }
  echo("|");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv6multicast") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/prefixes_ipv6multicast/">IPv6</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "prefixes_ipv6multicast") { echo("</span>"); }
  echo(")");

  echo(" | MAC (");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "macaccounting_bits") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/macaccounting_bits/">Bits</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "macaccounting_bits") { echo("</span>"); }
  echo("|");
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "macaccounting_pkts") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/'.$_GET['optb'].'/macaccounting_pkts/">Packets</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optc'] == "macaccounting_pkts") { echo("</span>"); }
  echo(")");


  echo('</div>');

  print_optionbar_end();


  echo("<table border=0 cellspacing=0 cellpadding=5 width=100% class='sortable'>");
  echo('<tr style="height: 30px"><td width=1></td><th>Local address</th><th></th><th>Peer address</th><th>Type</th><th>Family</th><th>Remote AS</th><th>State</th><th width=200>Uptime / Updates</th></tr>');

  $i = "1";

  if ($_GET['optb'] == "alerts")
  {
   $where = "AND (B.bgpPeerAdminStatus = 'start' or B.bgpPeerAdminStatus = 'running') AND B.bgpPeerState != 'established'";
  } elseif ($_GET['optb'] == "disabled") {
   $where = "AND B.bgpPeerAdminStatus = 'stop'";
  } elseif ($_GET['optb'] == "external") {
   $where = "AND D.bgpLocalAs != B.bgpPeerRemoteAs";
  } elseif ($_GET['optb'] == "internal") {
   $where = "AND D.bgpLocalAs = B.bgpPeerRemoteAs";
  }

  $peer_query = "select * from bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id ".$where." ORDER BY D.hostname, B.bgpPeerRemoteAs, B.bgpPeerIdentifier";
  foreach(dbFetchRows($peer_query) as $peer)
  {
    unset ($alert, $bg_image);

    if (!is_integer($i/2)) { $bg_colour = $list_colour_b; } else { $bg_colour = $list_colour_a; }

    if ($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $peer['alert']=1; }
    if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "gray"; }
    if ($peer['bgpPeerAdminStatus'] == "stop") { $peer['alert']=0; $peer['disabled']=1; }
    if ($peer['bgpPeerRemoteAs'] == $peer['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>";
     if ($peer['bgpPeerRemoteAS'] >= '64512' && $peer['bgpPeerRemoteAS'] <= '65535') { $peer_type = "<span style='color: #f00;'>Priv eBGP</span>"; }
    }

    $peerhost = dbFetchRow("SELECT * FROM ipaddr AS A, ports AS I, devices AS D WHERE A.addr = ? AND I.interface_id = A.interface_id AND D.device_id = I.device_id", array($peer['bgpPeerIdentifier']));

    if ($peerhost) { $peername = generate_device_link($peerhost, shorthost($peerhost['hostname'])); } else { unset($peername); }

    // display overlib graphs

    $graph_type       = "bgp_updates";
    $local_daily_url  = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=$day&amp;to=$now&amp;width=500&amp;height=150&&afi=ipv4&safi=unicast";
    $localaddresslink = "<span class=list-large><a href='device/" . $peer['device_id'] . "/routing/bgp/' onmouseover=\"return overlib('<img src=\'$local_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpLocalAddr'] . "</a></span>";

    $graph_type       = "bgp_updates";
    $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=$day&amp;to=$now&amp;width=500&amp;height=150";
    $peeraddresslink  = "<span class=list-large><a href='device/" . $peer['device_id'] . "/routing/bgp/' onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpPeerIdentifier'] . "</a></span>";

    echo('<tr bgcolor="'.$bg_colour.'"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') . ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . ">");

    unset($sep);
    foreach (dbFetchRows("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerIdentifier = ?", array($peer['device_id'], $peer['bgpPeerIdentifier'])) as $afisafi)
    {
      $afi = $afisafi['afi'];
      $safi = $afisafi['safi'];
      $this_afisafi = $afi.$safi;
      $peer['afi'] .= $sep . $afi .".".$safi;
      $sep = "<br />";
      $peer['afisafi'][$this_afisafi] = 1; ## Build a list of valid AFI/SAFI for this peer
    }
    unset($sep);

    echo("  <td></td>
            <td width=150>" . $localaddresslink . "<br />".generate_device_link($peer, shorthost($peer['hostname']), 'routing/bgp/')."</td>
            <td width=30><b>&#187;</b></td>
            <td width=150>" . $peeraddresslink . "</td>
            <td width=50><b>$peer_type</b></td>
            <td width=50>".$peer['afi']."</td>
            <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
            <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
            <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                Updates <img src='images/16/arrow_down.png' align=absmiddle /> " . format_si($peer['bgpPeerInUpdates']) . "
                        <img src='images/16/arrow_up.png' align=absmiddle /> " . format_si($peer['bgpPeerOutUpdates']) . "</td></tr>
         <tr height=5></tr>");


    unset($invalid);
    switch ($_GET['optc'])
    {
      case 'prefixes_ipv4unicast':
      case 'prefixes_ipv4multicast':
      case 'prefixes_ipv4vpn':
      case 'prefixes_ipv6unicast':
      case 'prefixes_ipv6multicast':
        list(,$afisafi) = explode("_", $_GET['optc']);
        if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      case 'updates':
        $graph_array['type']   = "bgp_" . $_GET['optc'];
        $graph_array['id']     = $peer['bgpPeer_id'];
    }

    switch ($_GET['optc'])
    {
      case 'macaccounting_bits':
      case 'macaccounting_pkts':
        $acc = dbFetchRow("SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = ? AND M.mac = I.mac_address AND P.interface_id = M.interface_id AND D.device_id = P.device_id", array($peer['bgpPeerIdentifier']));
        $database = $config['rrd_dir'] . "/" . $device['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
        if (is_array($acc) && is_file($database))
        {
          $peer['graph']       = 1;
          $graph_array['id']   = $acc['ma_id'];
          $graph_array['type'] = $_GET['optc'];
        }
    }

    if ($_GET['optc'] == 'updates') { $peer['graph'] = 1; }

    if ($peer['graph'])
    {
        $graph_array['height'] = "100";
        $graph_array['width']  = "220";
        $graph_array['to']     = $now;
        echo('<tr bgcolor="'.$bg_colour.'"' . ($bg_image ? ' background="'.$bg_image.'"' : '') . '"><td colspan="9">');
        include("includes/print-quadgraphs.inc.php");
        echo("</td></tr>");
    }

    $i++;
  }

  echo("</table>");
}

?>
