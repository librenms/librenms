<?php


echo("
<div style='padding: 5px; height: 20px; clear: both; display: block;'>
  <div style='float: left; font-size: 22px; font-weight: bold;'>Local AS : " . $device['bgpLocalAs'] . "</div>
  <div style='float: right; text-align: right;'>
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/'>No Graphs</a> | 
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/bgp_updates/'>Updates</a> | Prefixes: 
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv4.unicast/'>IPv4</a> | 
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv4.vpn/'>VPNv4</a> |
  <a href='".$config['base_url']."/device/" . $_GET['id'] . "/bgp/cbgp_prefixes/ipv6.unicast/'>IPv6</a>
 </div>
</div>");


   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");

   $i = "1";
   $peer_query = mysql_query("select * from bgpPeers WHERE device_id = '".$device['device_id']."' ORDER BY bgpPeerRemoteAs, bgpPeerIdentifier");
   while($peer = mysql_fetch_array($peer_query)) {

     if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
     #if($peer['bgpPeerAdminStatus'] == "start") { $img = "images/16/accept.png"; } else { $img = "images/16/delete.png"; }
     if($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $bg_colour = "#ffcccc"; }
     if($peer['bgpPeerAdminStatus'] == "start") { $admin_col = "green"; } else { $admin_col = "red"; $bg_colour = "#cccccc"; }

     if($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>"; }

     $peerhost = mysql_fetch_array(mysql_query("SELECT * FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"));

     if($peerhost) { $peername = generatedevicelink($peerhost); } else { unset($peername); }

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
             <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>$peer_af</td>
             <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
             <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
             <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                 Updates <img src='images/16/arrow_down.png' align=absmiddle> " . $peer['bgpPeerInUpdates'] . " 
                         <img src='images/16/arrow_up.png' align=absmiddle> " . $peer['bgpPeerOutUpdates'] . "</td></tr>");


 if($_GET['opta']) {
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
    }
  }
     $i++;
   }
   echo("</table></div>");

?>
