<?php

echo("
<div style='padding: 10px; height: 20px; clear: both; display: block;'>
  <div style='float: left; font-size: 22px; font-weight: bold;'>Local AS : " . $device['bgpLocalAs'] . "</div>
</div>");

print_optionbar_start();
echo("
  <div style='margin: auto; text-align: left; padding-left: 11px; clear: both; display:block; height:20px;'>
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/'>No Graphs</a> |
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/bgp_updates/'>Updates</a> | Prefixes:
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv4.unicast/'>IPv4</a> |
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv4.vpn/'>VPNv4</a> |
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv6.unicast/'>IPv6</a>
  | Traffic:
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/macaccounting/'>Mac Accounting</a>
</div>
");
print_optionbar_end();

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");

   $i = "1";
   $peer_query = mysql_query("select * from bgpPeers WHERE device_id = '".$device['device_id']."' ORDER BY bgpPeerRemoteAs, bgpPeerIdentifier");
   while($peer = mysql_fetch_array($peer_query)) {
     if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
     #if($peer['bgpPeerAdminStatus'] == "start") { $img = "images/16/accept.png"; } else { $img = "images/16/delete.png"; }
     if($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $bg_colour = "#ffcccc"; }
     if($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "red"; $bg_colour = "#cccccc"; }

     if($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>"; }

     $query = "SELECT * FROM ipv4_addresses AS A, interfaces AS I, devices AS D WHERE ";
     $query .= "(A.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id)";
     $query .= " AND D.device_id = I.device_id";
     $ipv4_host = mysql_fetch_array(mysql_query($query));

     $query = "SELECT * FROM ipv6_addresses AS A, interfaces AS I, devices AS D WHERE ";
     $query .= "(A.ipv6_address = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id)";
     $query .= " AND D.device_id = I.device_id";
     $ipv6_host = mysql_fetch_array(mysql_query($query));

     if($ipv4_host) { 
       $peerhost = $ipv4_host;
     } elseif($ipv6_host) {
       $peerhost = $ipv6_host;
     }
    if($peerhost) {
      $peername = generatedevicelink($peerhost); 
    } else { 
      $peername = gethostbyaddr($peer['bgpPeerIdentifier']);
      if($peername == $peer['bgpPeerIdentifier']) { 
        unset ($peername);
      } else {
        $peername = "<i>".$peername."<i>";
      }
    }

     $af_query = mysql_query("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['bgpPeerIdentifier']."'");
     unset($peer_af);
     while($afisafi = mysql_fetch_array($af_query)) {
       $afi = $afisafi['afi']; 
       $safi = $afisafi['safi'];
       $peer_af .= $sep . $config['afi'][$afi][$safi];          ##### CLEAN ME UP, I AM MESSY AND I SMELL OF CHEESE!
       $sep = "<br />";
       $valid_afi_safi[$afi][$safi] = 1; ## Build a list of valid AFI/SAFI for this peer
     }

     unset($sep);
     echo("<tr bgcolor=$bg_colour>
             <td width=20><span class=list-large>$i</span></td>
             <td><span class=list-large>" . $peer['bgpPeerIdentifier'] . "</span><br />".$peername."</td>
	     <td>$peer_type</td>
             <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>" . (isset($peer_af) ? $peer_af : '') . "</td>
             <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
             <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
             <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                 Updates <img src='images/16/arrow_down.png' align=absmiddle> " . $peer['bgpPeerInUpdates'] . " 
                         <img src='images/16/arrow_up.png' align=absmiddle> " . $peer['bgpPeerOutUpdates'] . "</td></tr>");


  if (isset($_GET['opta']) && $_GET['opta'] != "macaccounting") {
  foreach(explode(" ", $_GET['opta']) as $graph_type) {        
   if($graph_type == "cbgp_prefixes") { list($afi, $safi) = explode(".", $_GET['optb']); $afisafi = "&afi=$afi&safi=$safi"; }
   if($graph_type == "bgp_updates" || $valid_afi_safi[$afi][$safi]) {
     $daily_traffic   = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$day&to=$now&width=210&height=100$afisafi";
     $daily_url       = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150$afisafi";
     $weekly_traffic  = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$week&to=$now&width=210&height=100$afisafi";
     $weekly_url      = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150$afisafi";
     $monthly_traffic = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$month&to=$now&width=210&height=100$afisafi";
     $monthly_url     = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150$afisafi";
     $yearly_traffic  = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$year&to=$now&width=210&height=100$afisafi";
     $yearly_url      = $config['base_url'] . "/graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150$afisafi";
     echo("<tr bgcolor=$bg_colour><td colspan=7>");
     echo("<a href='' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"><img src='$daily_traffic' border=0></a> ");
     echo("<a href='' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"><img src='$weekly_traffic' border=0></a> ");
     echo("<a href='' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\"><img src='$monthly_traffic' border=0></a> ");
     echo("<a href='' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\"><img src='$yearly_traffic' border=0></a>");
     echo("</td></tr>");
   } 
   if ($_GET['opta'] == "macaccounting") {

     if(mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv4_mac` AS I, mac_accounting AS M WHERE I.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND M.mac = I.mac_address"),0)) {
       $acc = mysql_fetch_array(mysql_query("SELECT * FROM `ipv4_mac` AS I, mac_accounting AS M WHERE I.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND M.mac = I.mac_address"));
       $graph_type = "mac_acc";    
       $database = $config['rrd_dir'] . "/" . $device['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
       if ( is_file($database) ) {

  $daily_traffic   = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$day&to=$now&width=210&height=100";
  $daily_url       = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150";

  $weekly_traffic  = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$week&to=$now&width=210&height=100";
  $weekly_url      = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150";

  $monthly_traffic = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$month&to=$now&width=210&height=100";
  $monthly_url     = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150";

  $yearly_traffic  = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$year&to=$now&width=210&height=100";
  $yearly_url      = "graph.php?id=" . $acc['ma_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150";

  echo("<tr bgcolor=$bg_colour><td colspan=7>");
  echo("<a href='?page=interface&id=" . $interface['ma_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['ma_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['ma_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['ma_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");
  echo("</td></tr>");

     }
}

   }
}
}

     $i++;
   }
   echo("</table></div>");

?>
