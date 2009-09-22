<?php

#  This file prints a table row for each interface 
 
  $interface['device_id'] = $device['device_id'];
  $interface['hostname'] = $device['hostname'];

  $interface = ifNameDescr($interface);

  $if_id = $interface['interface_id'];

  if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  if($interface['in_errors'] > 0 || $interface['out_errors'] > 0) { 
    $error_img = generateiflink($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors); 
  } else { $error_img = ""; }

  echo("<tr style=\"background-color: $row_colour; padding: 5px;\" valign=top onmouseover=\"this.style.backgroundColor='$list_highlight';\" onmouseout=\"this.style.backgroundColor='$row_colour';\" onclick=\"location.href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/'\" style='cursor: hand;'>
           <td valign=top width=300>");

  echo("        <span class=list-large>
                " . generateiflink($interface, $interface['ifIndex'] . ". ".fixifName($interface['label'])) . " $error_img

             </span><br /><span class=interface-desc>".$interface['ifAlias']."</span>");

  if($interface['ifAlias']) { echo("<br />"); }

  unset ($break);
  if($port_details) {
    $ipdata = mysql_query("SELECT * FROM `ipv4_addresses` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip = mysql_fetch_Array($ipdata)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=$ip[ipv4_address]')\">$ip[ipv4_address]/$ip[ipv4_prefixlen]</a>");
      $break = "<br />";
    }
    $ip6data = mysql_query("SELECT * FROM `ipv6_addresses` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
    while($ip6 = mysql_fetch_Array($ip6data)) {
      echo("$break <a class=interface-desc href=\"javascript:popUp('/netcmd.php?cmd=whois&query=".$ip6['ipv6_address']."')\">".Net_IPv6::compress($ip6['ipv6_address'])."/".$ip6['ipv6_prefixlen']."</a>");
      $break = "<br />";
    }
  }

  echo("</span>");

  echo("</td><td width=100>");

  if(mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0)){
    echo("<a href='device/".$interface['device_id']."/interface/".$interface['interface_id']."/macaccounting/'><img src='/images/16/chart_curve.png' align='absmiddle'> Mac Acc</a>");
  }

  echo("</td><td width=120>");
  if($interface['ifOperStatus'] == "up") {
    $in_perc = @round($interface['in_rate']/$interface['ifSpeed']*100);
    $out_perc = @round($interface['in_rate']/$interface['ifSpeed']*100);
    echo("<img src='images/16/arrow_left.png' align=absmiddle> <span style='color: " . percent_colour($in_perc) . "'>" . 
      formatRates($interface['in_rate']) . "</span><br />");
    echo("<img align=absmiddle src='images/16/arrow_out.png'> <span style='color: " . percent_colour($out_perc) . "'>" . 
      formatRates($interface['out_rate']) . "</span>");  
  }

  echo("</td><td width=75>");
  if($interface['ifSpeed'] && $interface['ifAlias'] != "") { echo("<span class=box-desc>".humanspeed($interface['ifSpeed'])."</span>"); }
  echo("<br />");

#  if($interface[ifDuplex] != unknown) { echo("<span class=box-desc>Duplex " . $interface['ifDuplex'] . "</span>"); } else { echo("-"); }

  if($device['os'] == "IOS" || $device['os'] == "IOS XE") {
    if($interface['ifTrunk']) { 
      echo("<span class=box-desc><span class=red>" . $interface['ifTrunk'] . "</span></span>"); 
    } elseif ($interface['ifVlan']) { 
      echo("<span class=box-desc><span class=blue>VLAN " . $interface['ifVlan'] . "</span></span>"); 
    } elseif ($interface['ifVrf']) {
      $vrf = mysql_fetch_array(mysql_query("SELECT * FROM vrfs WHERE vrf_id = '".$interface['ifVrf']."'"));
      echo("<span style='color: green;'>" . $vrf['vrf_name'] . "</span>");
    }   
  }

  echo("</td><td width=150>");
  if($interface['ifType'] && $interface['ifType'] != "") { echo("<span class=box-desc>" . fixiftype($interface['ifType']) . "</span>"); } else { echo("-"); } 
  echo("<br />");
  if($ifHardType && $ifHardType != "") { echo("<span class=box-desc>" . $ifHardType . "</span>"); } else { echo("-"); }
  echo("</td><td width=150>");
  if($interface['ifPhysAddress'] && $interface['ifPhysAddress'] != "") { echo("<span class=box-desc>" . $interface['ifPhysAddress'] . "</span>"); } else { echo("-"); }
  echo("<br />");
  if($interface['ifMtu'] && $interface['ifMtu'] != "") { echo("<span class=box-desc>MTU " . $interface['ifMtu'] . "</span>"); } else { echo("-"); }

#}

echo("</td>");
     echo("<td width=375 valign=top class=interface-desc>");
     if ( strpos($interface['label'], "oopback") === false && !$graph_type) {
       $link_query = mysql_query("select * from links AS L, interfaces AS I, devices AS D WHERE L.src_if = '$if_id' AND L.dst_if = I.interface_id AND I.device_id = D.device_id");
       while($link = mysql_fetch_array($link_query)) {
#         echo("<img src='images/16/connect.png' align=absmiddle alt='Directly Connected' /> " . generateiflink($link, makeshortif($link['label'])) . " on " . generatedevicelink($link, shorthost($link['hostname'])) . "</a><br />");
#         $br = "<br />";
          $int_links[$link['interface_id']] = $link['interface_id'];
          $int_links_phys[$link['interface_id']] = 1;    
       }

       unset($br);

       if($port_details) { ## Show which other devices are on the same subnet as this interface

        $sql = "SELECT `ipv4_network_id` FROM `ipv4_addresses` WHERE `interface_id` = '".$interface['interface_id']."' AND `ipv4_address` NOT LIKE '127.%'";
        $nets_query = mysql_query($sql);
        while($net = mysql_fetch_array($nets_query)) {
          $ipv4_network_id = $net['ipv4_network_id'];
          $sql = "SELECT I.interface_id FROM ipv4_addresses AS A, interfaces AS I, devices AS D
          WHERE A.interface_id = I.interface_id
          AND A.ipv4_network_id = '".$net['ipv4_network_id']."' AND D.device_id = I.device_id
          AND D.device_id != '".$device['device_id']."'";
          $new_query = mysql_query($sql);
          while($new = mysql_fetch_array($new_query)) {
            echo($new['ipv4_network_id']);
            $this_ifid = $new['interface_id'];
            $this_hostid = $new['device_id'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['label']);
            $int_links[$this_ifid] = $this_ifid;
            $int_links_v4[$this_ifid] = 1;
          }
        }

        $sql = "SELECT ipv6_network_id FROM ipv6_addresses WHERE interface_id = '".$interface['interface_id']."'";
	$nets_query = mysql_query($sql);
        while($net = mysql_fetch_array($nets_query)) {
          $ipv6_network_id = $net['ipv6_network_id'];
          $sql = "SELECT I.interface_id FROM ipv6_addresses AS A, interfaces AS I, devices AS D
          WHERE A.interface_id = I.interface_id
          AND A.ipv6_network_id = '".$net['ipv6_network_id']."' AND D.device_id = I.device_id 
          AND D.device_id != '".$device['device_id']."' AND A.ipv6_origin != 'linklayer' AND A.ipv6_origin != 'wellknown'";
          $new_query = mysql_query($sql);
          while($new = mysql_fetch_array($new_query)) {  
	    echo($new['ipv6_network_id']);
            $this_ifid = $new['interface_id'];
            $this_hostid = $new['device_id'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['label']);
            $int_links[$this_ifid] = $this_ifid;
            $int_links_v6[$this_ifid] = 1;
          }
        }
     }

     foreach($int_links as $int_link) {

       $link_if = mysql_fetch_array(mysql_query("SELECT * from interfaces AS I, devices AS D WHERE I.device_id = D.device_id and I.interface_id = '".$int_link."'"));

       echo("$br");

       if($int_links_phys[$int_link]) { echo("<img align=absmiddle src='images/16/connect.png'> "); } else {
                                        echo("<img align=absmiddle src='images/16/bullet_go.png'> "); }

       echo("<b>" . generateiflink($link_if, makeshortif($link_if['label'])) . " on " . generatedevicelink($link_if, shorthost($link_if['hostname'])) );

       if($int_links_v6[$int_link]) { echo(" <b style='color: #a10000;'>v6</b>"); }
       if($int_links_v4[$int_link]) { echo(" <b style='color: #00a100'>v4</b>"); }
       $br = "<br />";
     }
  
     unset($int_links, $int_links_v6, $int_links_v4, $int_links_phys, $br);

     }

  $pseudowires = mysql_query("SELECT * FROM `pseudowires` WHERE `interface_id` = '" . $interface['interface_id'] . "'");
  while($pseudowire = mysql_fetch_array($pseudowires)) {
  #`interface_id`,`peer_device_id`,`peer_ldp_id`,`cpwVcID`,`cpwOid`
    $pw_peer_dev = mysql_fetch_array(mysql_query("SELECT * from `devices` WHERE `device_id` = '" . $pseudowire['peer_device_id'] . "'"));
    $pw_peer_int = mysql_fetch_array(mysql_query("SELECT * from `interfaces` AS I, pseudowires AS P WHERE I.device_id = '".$pseudowire['peer_device_id']."' AND
                                                                                                          P.cpwVcID = '".$pseudowire['cpwVcID']."' AND
                                                                                                          P.interface_id = I.interface_id"));
    $pw_peer_int = ifNameDescr($pw_peer_int);
    echo("<img src='images/16/arrow_switch.png' align=absmiddle><b> " . generateiflink($pw_peer_int, makeshortif($pw_peer_int['label'])) ." on ". generatedevicelink($pw_peer_dev, shorthost($pw_peer_dev['hostname'])) . "</b>");
  }


     echo("</td></tr>");

     // If we're showing graphs, generate the graph and print the img tags
     if($graph_type && is_file($config['rrd_dir'] . "/" . $device['hostname'] . "/". $interface['ifIndex'] . ".rrd")) {
 
          $type = $graph_type;

          $daily_traffic = "graph.php?if=$if_id&type=" . $graph_type . "&from=$day&to=$now&width=210&height=100";
          $daily_url = "graph.php?if=$if_id&type=" . $graph_type . "&from=$day&to=$now&width=500&height=150";

          $weekly_traffic = "graph.php?if=$if_id&type=" . $graph_type . "&from=$week&to=$now&width=210&height=100";
          $weekly_url = "graph.php?if=$if_id&type=" . $graph_type . "&from=$week&to=$now&width=500&height=150";

          $monthly_traffic = "graph.php?if=$if_id&type=" . $graph_type . "&from=$month&to=$now&width=210&height=100";
          $monthly_url = "graph.php?if=$if_id&type=" . $graph_type . "&from=$month&to=$now&width=500&height=150";

          $yearly_traffic = "graph.php?if=$if_id&type=" . $graph_type . "&from=$year&to=$now&width=210&height=100";
          $yearly_url = "graph.php?if=$if_id&type=" . $graph_type . "&from=$year&to=$now&width=500&height=150";

  echo("<tr style='background-color: $bg; padding: 5px;'><td colspan=7>");

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

   ?>
