<?php

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");

   echo("<tr><td colspan=6><h2>Local AS : " . $device['bgpLocalAs'] . "</h2></td></tr>");

   $i = "1";
   $peer_query = mysql_query("select * from bgpPeers WHERE device_id = '".$device['device_id']."' ORDER BY bgpPeerRemoteAs, bgpPeerIdentifier");
   while($peer = mysql_fetch_array($peer_query)) {

     if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
     #if($peer['bgpPeerAdminStatus'] == "start") { $img = "images/16/accept.png"; } else { $img = "images/16/delete.png"; }
     if($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $bg_colour = "#fcc"; }
     if($peer['bgpPeerAdminStatus'] == "start") { $admin_col = "green"; } else { $admin_col = "red"; $bg_colour = "#ccc"; }

     if($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>"; }


     $peerhost = mysql_fetch_array(mysql_query("SELECT * FROM ipaddr AS A, interfaces AS I, devices AS D WHERE A.addr = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id AND D.device_id = I.device_id"));

     if($peerhost) { $peername = generatedevicelink($peerhost); } else { unset($peername); }

     echo("<tr bgcolor=$bg_colour>
             <td width=20><span class=list-large>$i</span></td>
             <td><span class=list-large>" . $peer['bgpPeerIdentifier'] . "</span><br />".$peername."</td>
	     <td>$peer_type</td>
             <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
             <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
             <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                 Updates <img src='images/16/arrow_down.png' align=absmiddle> " . $peer['bgpPeerInUpdates'] . " 
                         <img src='images/16/arrow_up.png' align=absmiddle> " . $peer['bgpPeerOutUpdates'] . "</td></tr>");

     if($graphs) {
          
       $graph_type = "bgpupdates";
 
         $daily_traffic   = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$day&to=$now&width=210&height=100";
  $daily_url       = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150";

  $weekly_traffic  = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$week&to=$now&width=210&height=100";
  $weekly_url      = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150";

  $monthly_traffic = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$month&to=$now&width=210&height=100";
  $monthly_url     = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150";

  $yearly_traffic  = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$year&to=$now&width=210&height=100";
  $yearly_url  = "graph.php?peer=" . $peer['bgpPeer_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150";

  echo("<tr><td colspan=6>");

  echo("<a href='?page=interface&id=" . $peer['bgpPeer_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $peer['bgpPeer_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $peer['bgpPeer_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $peer['bgpPeer_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");


       echo("</td></tr>");


     }

     $i++;
   }
   echo("</table></div>");

?>
