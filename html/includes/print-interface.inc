<?php

#  This file prints a table row for each interface 
 
  $interface['device_id'] = $device['device_id'];
  $interface['hostname'] = $device['hostname'];

  if(!$_GET['type']) { $_GET['type'] = "bits"; }

  $if_id = $interface['interface_id'];
  $ifDescr = fixifName($interface['ifDescr']);
  $ifIndex = $interface['ifIndex'];
  $ifAlias = $interface['ifAlias'];
  $ifSpeed = humanspeed($interface['ifSpeed']);
  $ifPhysAddress = $interface['ifPhysAddress'];
  $ifType = fixiftype($interface['ifType']);
  $ifHardType = $interface['ifHardType'];
  
  if($ifAlias) {$ifAlias = $ifAlias . "</br>";}
  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  if($interface['in_errors'] > 0 || $interface['out_errors'] > 0) { 
    $error_img = generateiflink($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors); 
  } else { $error_img = ""; }

  $graph_url = "graph.php?if=$if_id&from=$twoday&to=$now&width=400&height=120&type=bits";

  echo("<tr style=\"background-color: $bg; padding: 5px;\" valign=top>
           <td valign=top width=300>
              <span class=list-large>
                " . generateiflink($interface, "$i. $ifDescr") . " $error_img
              </span><br /><span class=interface-desc>$ifAlias</span>");

	if($ifAlias) { echo("<br />"); }

  unset ($break);
  if(!$dographs) {
    $ipdata = mysql_query("SELECT * FROM `ipaddr` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip = mysql_fetch_Array($ipdata)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip[addr]')\">$ip[addr]/$ip[cidr]</a>");
      $break = "<br />";
    }
    $ip6data = mysql_query("SELECT * FROM `ip6addr` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip6 = mysql_fetch_Array($ip6data)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip6[addr]')\">".Net_IPv6::compress($ip6[addr])."/$ip6[cidr]</a>");
      $break = "<br />";
    }
 
  echo("</span>");
  echo("</td><td width=100>");
  if($interface['ifOperStatus'] == "up") {

    $in_perc = @round($interface['in_rate']/$interface['ifSpeed']*100);
    $out_perc = @round($interface['in_rate']/$interface['ifSpeed']*100);
   
    echo("<img src='images/16/arrow_left.png' align=absmiddle> <span style='color: " . percent_colour($in_perc) . "'>" . 
      formatRates($interface['in_rate']) . "</span><br />");
    echo("<img align=absmiddle src='images/16/arrow_out.png'> <span style='color: " . percent_colour($out_perc) . "'>" . 
      formatRates($interface['out_rate']) . "</span>");
    
  }
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
     echo("<td width=375 valign=top class=interface-desc>");
     if ( strpos($ifDescr, "oopback") === false && !$dographs) {
       $link_query = mysql_query("select * from links AS L, interfaces AS I, devices AS D WHERE L.src_if = '$if_id' AND L.dst_if = I.interface_id AND I.device_id = D.device_id");
       while($link = mysql_fetch_array($link_query)) {
#         echo("<img src='images/16/connect.png' align=absmiddle alt='Directly Connected' /> " . generateiflink($link, makeshortif($link['ifDescr'])) . " on " . generatedevicelink($link) . "</a><br />");
#         $br = "<br />";
          $int_links[$link['interface_id']] = $link['interface_id'];
          $int_links_phys[$link['interface_id']] = 1;    
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
          $newsql = $newsql . "AND D.device_id != '".$device['device_id']."' AND I.ifDescr NOT LIKE '%loopback%' GROUP BY D.device_id ORDER BY D.hostname";
          $new_query = mysql_query($newsql);
          while($new = mysql_fetch_array($new_query)) {

            $this_ifid = $new['interface_id'];
            $this_hostid = $new['device_id'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['ifDescr']);
            $wq = mysql_query("select count(*) FROM links  WHERE dst_if = '$this_ifid' AND src_if = $if_id;");

	    $int_links[$this_ifid] = $this_ifid;
            $int_links_v4[$this_ifid] = 1;


#            if (@mysql_result($wq, 0) == '0' && $this_hostname != $hostname) {
#              $graph_url = "graph.php?if=$this_ifid&from=$twoday&to=$now&width=400&height=120&type=bits'";
#              echo("$br &nbsp;<img src='images/16/bullet_go.png' alt='Same Subnet' align=absmiddle />" . generateiflink($new, makeshortif($new['ifDescr'])) . " on " . generatedevicelink($new));
#              $br = "<br />";
#            }
          }
        }
        unset($br);
     # }
 
        $sql = "SELECT network_id FROM ip6addr AS A, ip6networks AS N, interfaces AS I
        WHERE A.interface_id = I.interface_id
        AND A.network = N.cidr
        AND I.interface_id = '".$interface['interface_id']."'";

	$nets_query = mysql_query($sql);

        while($net = mysql_fetch_array($nets_query)) {
          $network_id = $net['network_id'];

        $sql = "SELECT I.interface_id FROM ip6addr AS A, ip6networks AS N, interfaces AS I, devices AS D
        WHERE A.interface_id = I.interface_id
        AND A.network = N.cidr AND N.network_id = '".$net['network_id']."' AND D.device_id = I.device_id 
        AND D.device_id != '".$device['device_id']."' AND A.origin != 'linklayer' AND A.origin != 'wellknown'";

          $new_query = mysql_query($sql);
          while($new = mysql_fetch_array($new_query)) {
	    
	    echo($new['network_id']);

            $this_ifid = $new['interface_id'];
            $this_hostid = $new['device_id'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['ifDescr']);

            $int_links[$this_ifid] = $this_ifid;
            $int_links_v6[$this_ifid] = 1;

          }
        }

     foreach($int_links as $int_link) {

       $interface = mysql_fetch_array(mysql_query("SELECT * from interfaces AS I, devices AS D WHERE I.device_id = D.device_id and I.interface_id = '".$int_link."'"));

       echo("$br");

       if($int_links_phys[$int_link]) { echo("<img align=absmiddle src='images/16/connect.png'> "); } else {
                                        echo("<img align=absmiddle src='images/16/bullet_go.png'> "); }

       echo("<b>" . generateiflink($interface, makeshortif($interface['ifDescr'])) . " on " . generatedevicelink($interface) );

       if($int_links_v6[$int_link]) { echo(" <b style='color: #a10000;'>v6</b>"); }
       if($int_links_v4[$int_link]) { echo(" <b style='color: #00a100'>v4</b>"); }
       $br = "<br />";
     }
  
     unset($int_links, $int_links_v6, $int_links_v4, $int_links_phys, $br);

     }

     echo("</td></tr>");

     // If we're showing graphs, generate the graph and print the img tags
     if($dographs && is_file($rrd_dir . "/" . $hostname . "/". $ifIndex . ".rrd")) {
 
          $type = $_GET['type'];

          $daily_traffic = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$day&to=$now&width=210&height=100";
          $daily_url = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$day&to=$now&width=500&height=150";

          $weekly_traffic = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$week&to=$now&width=210&height=100";
          $weekly_url = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$week&to=$now&width=500&height=150";

          $monthly_traffic = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$month&to=$now&width=210&height=100";
          $monthly_url = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$month&to=$now&width=500&height=150";

          $yearly_traffic = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$year&to=$now&width=210&height=100";
          $yearly_url = "graph.php?if=$if_id&type=" . $_GET['type'] . "&from=$year&to=$now&width=500&height=150";

  echo("<tr style='background-color: $bg; padding: 5px;'><td colspan=3>");

  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" 
        onmouseout=\"return nd();\"> <img src='$daily_traffic' border=0></a>");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$weekly_traffic' border=0></a>");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$monthly_traffic' border=0></a>");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT, WIDTH, 350".$config['overlib_defaults'].");\"
        onmouseout=\"return nd();\"> <img src='$yearly_traffic' border=0></a>");



  echo("</td></tr>");

      }

   

   $i++;


?>
