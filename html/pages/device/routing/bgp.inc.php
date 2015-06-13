<?php

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'bgp');

if(!isset($vars['view'])) { $vars['view'] = "basic"; }

print_optionbar_start();

echo "<strong>Local AS : " .$device['bgpLocalAs']."</strong> ";

echo("<span style='font-weight: bold;'>BGP</span> &#187; ");

if ($vars['view'] == "basic") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("Basic", $link_array,array('view'=>'basic')));
if ($vars['view'] == "basic") { echo("</span>"); }

echo(" | ");

if ($vars['view'] == "updates") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("Updates", $link_array,array('view'=>'updates')));
if ($vars['view'] == "updates") { echo("</span>"); }

echo(" | Prefixes: ");

if ($vars['view'] == "prefixes_ipv4unicast") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("IPv4", $link_array,array('view'=>'prefixes_ipv4unicast')));
if ($vars['view'] == "prefixes_ipv4unicast") { echo("</span>"); }

echo(" | ");

if ($vars['view'] == "prefixes_vpnv4unicast") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("VPNv4", $link_array,array('view'=>'prefixes_vpnv4unicast')));
if ($vars['view'] == "prefixes_vpnv4unicast") { echo("</span>"); }

echo(" | ");

if ($vars['view'] == "prefixes_ipv6unicast") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("IPv6", $link_array,array('view'=>'prefixes_ipv6unicast')));
if ($vars['view'] == "prefixes_ipv6unicast") { echo("</span>"); }

echo(" | Traffic: ");

if ($vars['view'] == "macaccounting_bits") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("Bits", $link_array,array('view'=>'macaccounting_bits')));
if ($vars['view'] == "macaccounting_bits") { echo("</span>"); }
echo(" | ");
if ($vars['view'] == "macaccounting_pkts") { echo("<span class='pagemenu-selected'>"); }
echo(generate_link("Packets", $link_array,array('view'=>'macaccounting_pkts')));
if ($vars['view'] == "macaccounting_pkts") { echo("</span>"); }

print_optionbar_end();

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');
echo('<tr style="height: 30px"><td width=1></td><th></th><th>Peer address</th><th>Type</th><th>Remote AS</th><th>State</th><th>Uptime</th></tr>');

$i = "1";

foreach (dbFetchRows("SELECT * FROM `bgpPeers` WHERE `device_id` = ? ORDER BY `bgpPEerRemoteAs`, `bgpPeerIdentifier`", array($device['device_id'])) as $peer)
{
  $has_macaccounting = dbFetchCell("SELECT COUNT(*) FROM `ipv4_mac` AS I, mac_accounting AS M WHERE I.ipv4_address = ? AND M.mac = I.mac_address", array($peer['bgpPeerIdentifier']));
  unset($bg_image);
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  unset ($alert, $bg_image);
  unset ($peerhost, $peername);

  if (!is_integer($i/2)) { $bg_colour = $list_colour_b; } else { $bg_colour = $list_colour_a; }
  if ($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $peer['alert']=1; }
  if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "gray"; }
  if ($peer['bgpPeerAdminStatus'] == "stop") { $peer['alert']=0; $peer['disabled']=1; }

  if ($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>"; }

  $query = "SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE ";
  $query .= "(A.ipv4_address = ? AND I.port_id = A.port_id)";
  $query .= " AND D.device_id = I.device_id";
  $ipv4_host = dbFetchRow($query,array($peer['bgpPeerIdentifier']));

  $query = "SELECT * FROM ipv6_addresses AS A, ports AS I, devices AS D WHERE ";
  $query .= "(A.ipv6_address = ? AND I.port_id = A.port_id)";
  $query .= " AND D.device_id = I.device_id";
  $ipv6_host = dbFetchRow($query,array($peer['bgpPeerIdentifier']));

  if ($ipv4_host)
  {
    $peerhost = $ipv4_host;
  } elseif ($ipv6_host) {
    $peerhost = $ipv6_host;
  } else {
    unset($peerhost);
  }

  if (is_array($peerhost))
  {
    #$peername = generate_device_link($peerhost);
    $peername = generate_device_link($peerhost) ." ". generate_port_link($peerhost);
    $peer_url         = "device/device=" . $peer['device_id'] . "/tab=routing/proto=bgp/view=updates/";
  }
  else
  {
    #$peername = gethostbyaddr($peer['bgpPeerIdentifier']); // FFffuuu DNS // Cache this in discovery?
#    if ($peername == $peer['bgpPeerIdentifier'])
#    {
#      unset($peername);
#    } else {
#      $peername = "<i>".$peername."<i>";
#    }
  }

  unset($peer_af);
  unset($sep);

  foreach (dbFetchRows("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerIdentifier = ?", array($device['device_id'], $peer['bgpPeerIdentifier'])) as $afisafi)
  {
    $afi = $afisafi['afi'];
    $safi = $afisafi['safi'];
    $this_afisafi = $afi.$safi;
    $peer['afi'] .= $sep . $afi .".".$safi;
    $sep = "<br />";
    $peer['afisafi'][$this_afisafi] = 1; // Build a list of valid AFI/SAFI for this peer
  }

  unset($sep);

  if (filter_var($peer['bgpLocalAddr'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE) {
      $peer['bgpPeerIdentifier'] = Net_IPv6::compress($peer['bgpPeerIdentifier']);
  }


  $graph_type       = "bgp_updates";
  $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150";
  $peeraddresslink  = "<span class=list-large><a onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpPeerIdentifier'] . "</a></span>";

  echo('<tr bgcolor="'.$bg_colour.'"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') .
                                        ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . ">
  ");

  echo("   <td width=20><span class=list-large>".$i."</span></td>
           <td>" . $peeraddresslink . "<br />".$peername."</td>
             <td>$peer_type</td>
           <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>" . (isset($peer['afi']) ? $peer['afi'] : '') . "</td>
           <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
           <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
           <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
               Updates <img src='images/16/arrow_down.png' align=absmiddle> " . $peer['bgpPeerInUpdates'] . "
                       <img src='images/16/arrow_up.png' align=absmiddle> " . $peer['bgpPeerOutUpdates'] . "</td>
          </tr>
          <tr height=5></tr>");

  unset($invalid);

  switch ($vars['view'])
  {
    case 'prefixes_ipv4unicast':
    case 'prefixes_ipv4multicast':
    case 'prefixes_ipv4vpn':
    case 'prefixes_ipv6unicast':
    case 'prefixes_ipv6multicast':
      list(,$afisafi) = explode("_", $vars['view']);
      if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      // FIXME no break??
    case 'updates':
      $graph_array['type']   = "bgp_" . $vars['view'];
      $graph_array['id']     = $peer['bgpPeer_id'];
  }

  switch ($vars['view'])
  {
    case 'macaccounting_bits':
    case 'macaccounting_pkts':
      $acc = dbFetchRow("SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = ? AND M.mac = I.mac_address AND P.port_id = M.port_id AND D.device_id = P.device_id", array($peer['bgpPeerIdentifier']));
      $database = $config['rrd_dir'] . "/" . $device['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
      if (is_array($acc) && is_file($database))
      {
        $peer['graph']       = 1;
        $graph_array['id']   = $acc['ma_id'];
        $graph_array['type'] = $vars['view'];
      }
  }

  if ($vars['view'] == 'updates') { $peer['graph'] = 1; }

  if ($peer['graph'])
  {
    $graph_array['height'] = "100";
    $graph_array['width']  = "216";
    $graph_array['to']     = $config['time']['now'];
    echo('<tr bgcolor="'.$bg_colour.'"' . ($bg_image ? ' background="'.$bg_image.'"' : '') . '"><td colspan="7">');

    include("includes/print-graphrow.inc.php");

    echo("</td></tr>");
  }

  $i++;

  unset($valid_afi_safi);
}
?>

</table>
