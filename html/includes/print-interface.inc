<?php

#  This file prints a table row for each interface 
 
  $if_id = $interface['interface_id'];
  $ifDescr = fixifName($interface['ifDescr']);
  $ifIndex = $interface['ifIndex'];
  $ifAlias = $interface['ifAlias'];
  $ifSpeed = humanspeed($interface['ifSpeed']);
  $ifPhysAddress = $interface['ifPhysAddress'];
  $ifType = fixiftype($interface['ifType']);
  $ifHardType = $interface['ifHardType'];

#  echo($interface['ifHardType']);

  if($ifAlias) {$ifAlias = $ifAlias . "</br>";}
  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  $graph_url = "graph.php?if=$if_id&from=$twoday&to=$now&width=400&height=120&type=bits";

  echo("<tr style=\"background-color: $bg; padding: 5px;\" valign=top>
           <td valign=top width=300>
              <span class=list-large>
                " . generateiflink($interface, "$i. $ifDescr") . "
              </span><br /><span class=interface-desc>$ifAlias</span>");
  unset ($break);
  if(!$dographs) {
    $ipdata = mysql_query("SELECT * FROM `ipaddr` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip = mysql_fetch_Array($ipdata)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip[addr]')\">$ip[addr]/$ip[cidr]</a>");
      $break = " ";
    } 
  echo("</span>");
  echo("</td><td width=70>");
  if($ifSpeed && $ifSpeed != "") { echo("<span class=box-desc>$ifSpeed</span>"); }
  echo("<br />");
#  if($interface[ifDuplex] != unknown) { echo("<span class=box-desc>Duplex " . $interface['ifDuplex'] . "</span>"); } else { echo("-"); }

  if($device['os'] == "IOS") {

    if($interface['ifTrunk']) { echo("<span class=box-desc><span class=red>" . $interface['ifTrunk'] . "</span></span>"); 
    } elseif ($interface['ifVlan']) { echo("<span class=box-desc><span class=blue>VLAN " . $interface['ifVlan'] . "</span></span>"); }

  }

  echo("</td><td width=150>");
  if($ifType && $ifType != "") { echo("<span class=box-desc>" . $ifType . "</span>"); } else { echo("-"); } 
  echo("<br />");
  if($ifHardType && $ifHardType != "") { echo("<span class=box-desc>" . $ifHardType . "</span>"); } else { echo("-"); }
  echo("</td><td width=150>");
  if($interface['ifPhysAddress'] && $interface['ifPhysAddress'] != "") { echo("<span class=box-desc>" . $interface['ifPhysAddress'] . "</span>"); } else { echo("-"); }
  echo("<br />");
  if($interface['ifMtu'] && $interface['ifMtu'] != "") { echo("<span class=box-desc>MTU " . $interface['ifMtu'] . "</span>"); } else { echo("-"); }
}

echo("</td>");
     echo("<td valign=top class=interface-desc>");
     if ( strpos($ifDescr, "oopback") === false && !$dographs) {
       $link_query = mysql_query("select * from links AS L, interfaces AS I, devices AS D WHERE L.src_if = '$if_id' AND L.dst_if = I.interface_id AND I.device_id = D.device_id");
       while($link = mysql_fetch_array($link_query)) {
         echo("<img src='images/16/connect.png' align=absmiddle alt='Directly Connected' /> " . generateiflink($link) . " on " . generatedevicelink($link) . "</a><br />");
         $br = "<br />";
       }
       unset($br);
       $adj_sql = "SELECT * FROM networks AS N, interfaces AS I, adjacencies AS A ";
        $adj_sql = $adj_sql . "WHERE I.interface_id = A.interface_id AND A.network_id = N.id ";
        $adj_sql = $adj_sql . "AND I.interface_id = '$if_id'";
        $adj_query = mysql_query("$adj_sql");
        while($adjs = mysql_fetch_array($adj_query)) {
          $network_id = $adjs['network_id'];
          $newsql = "SELECT * FROM adjacencies AS A, networks as N, interfaces as I, devices as D ";
          $newsql = $newsql . "WHERE N.id = '$network_id'  AND A.network_id = N.id AND I.interface_id = A.interface_id AND D.device_id = I.device_id ";
          $newsql = $newsql . "AND D.device_id != '$device[id]' AND I.ifDescr NOT LIKE '%loopback%' GROUP BY D.device_id ORDER BY D.hostname";
          $new_query = mysql_query($newsql);
          while($new = mysql_fetch_array($new_query)) {
            if ($new['status'] == '0') { $class = "red"; } else { $class = "blue"; }
            if ($new['ignore'] == '1') {
              $class = "grey";
              if ($new['status'] == '1') { $class = "green"; }
            }
            $this_ifid = $new['interface_id'];
            $this_hostid = $new['device_id'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['ifDescr']);
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
