<?php

#  This file prints a table row for each interface 
 
  $if_id = $interface['id'];
  $inf = fixifName($interface['if']);
  $ifIndex = $interface['ifIndex'];
  $ifclass = ifclass($interface['up'], $interface['up_admin']);
  $ifname = $interface['name'];
  $speed = humanspeed($interface['ifSpeed']);
  $mac = $interface['ifPhysAddress'];
  if($ifname) {$ifname = $ifname . "</br>";}
  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
  $graph_url = "graph.php?if=$if_id&from=$twoday&to=$now&width=400&height=120&type=bits";
  echo("<tr style=\"background-color: $bg; padding: 5px;\" valign=top>
           <td valign=top width=275>
              <span class=list-large><a onmouseover=\"return overlib('<img src=\'$graph_url\'>');\" onmouseout=\"return nd();\" 
               class='$ifclass' href='?page=interface&id=$interface[id]'>
                 $i. $inf
              </a></span><br /><span class=interface-desc>$ifname</span>");
  unset ($break);
  $iftype = fixiftype($interface[ifType]);
  if(!$dographs) {
    $ipdata = mysql_query("SELECT * FROM `ipaddr` WHERE `interface_id` = '$interface[id]'");
    while($ip = mysql_fetch_Array($ipdata)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip[addr]')\">$ip[addr]/$ip[cidr]</a>");
      $break = " ";
    } 
  echo("</span>");
  echo("</td><td width=80>");
  if($speed && $speed != "") { echo("<span class=box-desc>$speed</span>"); }
  echo("</td><td width=80>");
  if($iftype && $iftype != "") { echo("<span class=box-desc>$iftype</span>"); } else { echo("-"); } 
  echo("</td><td width=80>");
  if($mac && $mac != "") { echo("<span class=box-desc>$mac</span>"); } else { echo("-"); }
  echo("</td><td width=80>");
  if($interface[ifMtu] && $interface[ifMtu] != "") { echo("<span class=box-desc>MTU $interface[ifMtu]</span>"); } else { echo("-"); }

#  if($interface[ifDuplex] != unknown) { echo("<span class=box-desc>Duplex $interface[ifDuplex]</span>"); } else { echo("-"); }
}

echo("</td>");
     echo("<td valign=top class=interface-desc>");
     if ( strpos($inf, "oopback") === false && !$dographs) {
       $link_query = mysql_query("select I.if, D.hostname, D.id AS dev_id, I.id from links AS L, interfaces AS I, devices AS D WHERE L.src_if = '$if_id' AND L.dst_if = I.id AND I.host = D.id");
       while($link = mysql_fetch_array($link_query)) {
         echo("<img src='images/16/connect.png' align=absmiddle alt='Directly Connected' /> " . generateiflink($link) . " on " . generatedevicelink($link) . "</a><br />");
         $br = "<br />";
       }
       unset($br);
       $adj_sql = "SELECT * FROM networks AS N, interfaces AS I, adjacencies AS A ";
        $adj_sql = $adj_sql . "WHERE I.id = A.interface_id AND A.network_id = N.id ";
        $adj_sql = $adj_sql . "AND I.id = '$if_id'";
        $adj_query = mysql_query("$adj_sql");
        while($adjs = mysql_fetch_array($adj_query)) {
          $network_id = $adjs['network_id'];
          $newsql = "SELECT *, I.id AS iid, D.id AS did FROM adjacencies AS A, networks as N, interfaces as I, devices as D ";
          $newsql = $newsql . "WHERE N.id = '$network_id'  AND A.network_id = N.id AND I.id = A.interface_id AND D.id = I.host ";
          $newsql = $newsql . "AND D.id != '$device[id]' AND I.if NOT LIKE '%loopback%' GROUP BY D.id ORDER BY D.hostname";
          $new_query = mysql_query($newsql);
          while($new = mysql_fetch_array($new_query)) {
            if ($new['status'] == '0') { $class = "red"; } else { $class = "blue"; }
            if ($new['ignore'] == '1') {
              $class = "grey";
              if ($new['status'] == '1') { $class = "green"; }
            }
            $this_ifid = $new['iid'];
            $this_hostid = $new['did'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['if']);
            $wq = mysql_query("select count(*) FROM links  WHERE dst_if = '$this_ifid' AND src_if = $if_id;");
            if (@mysql_result($wq, 0) == '0' && $this_hostname != $hostname) {
              $graph_url = "graph.php?if=$this_ifid&from=$twoday&to=$now&width=400&height=120&type=bits";
              echo("$br &nbsp;<img src='images/16/bullet_go.png' alt='Same Subnet' align=absmiddle />" . generateiflink($new) . " on " . generatedevicelink($new));
              $br = "<br />";
            }
          }
        }
        unset($br);
      }
   
     echo("</td></tr>");

     // If we're showing graphs, generate the graph and print the img tags
     if($dographs && is_file("rrd/" . $hostname . ".". $ifIndex . ".rrd")) {
          $daily_traffic = "graph.php?if=$if_id&type=bits&from=$day&to=$now&width=210&height=100";
          $weekly_traffic = "graph.php?if=$if_id&type=bits&from=$week&to=$now&width=210&height=100";
          $monthly_traffic = "graph.php?if=$if_id&type=bits&from=$month&to=$now&width=210&height=100";
          $yearly_traffic = "graph.php?if=$if_id&type=bits&from=$year&to=$now&width=210&height=100";

          echo("<tr style='background-color: $bg; padding: 5px;'><td colspan=3>");
          echo("<img src='$daily_traffic'> <img src='$weekly_traffic'> ");
          echo("<img src='$monthly_traffic'> <img src='$yearly_traffic'>");
          echo("</td></tr>");
      }

   

   $i++;


?>
