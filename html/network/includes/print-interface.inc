<?php


     $if_id = $interface['id'];
     $inf = fixifName($interface['if']);
     $ifIndex = $interface['ifIndex'];
     $ifclass = ifclass($interface['up'], $interface['up_admin']);
     $ifname = $interface['name'];
     if($ifname) {$ifname = $ifname . "</br>";}
     echo("<tr>
             <td valign=top width=250>
               <a class='$ifclass' href='?page=interface&id=$interface[id]'>
                 $i. $inf
               </a><br /><span class=interface-desc>$ifname</span>");
     if($ifalias && $ifalias != "") { echo("<span class=interface-desc>$ifalias</span><br />"); }
     unset ($break);

     $iftype = fixiftype($interface[ifType]);
if($show_all) {
  if($iftype && $iftype != "") { echo("<span class=box-desc>$iftype</span> ");
    if($mac && $mac != "") { echo("<span class=box-desc>$mac</span><br />");
    } else { echo("<br />"); }
  }

  if($interface[ifType] != "softwareLoopback") {
    if($speed == '0') { $speed = "0bps"; }
    echo("<span class=box-desc>$speed");
    if($interface[ifDuplex] != unknown) { echo(" / $interface[ifDuplex]-duplex"); }
    if($interface[ifMtu] && $interface[ifMtu] != "") { echo(" / $interface[ifMtu]MTU</span>"); }
    echo("</span><br />");
  }

  echo ("<span class=box-desc><b>$status</b></span> <br />");
}

     $ipdata = mysql_query("SELECT * FROM `ipaddr` WHERE `interface_id` = '$interface[id]'");
     while($ip = mysql_fetch_Array($ipdata)) {
       echo("$break <a href=\"javascript:popUp('/whois.php?query=$ip[addr]')\">$ip[addr]/$ip[cidr]</a>");
       $break = "<br />";
     }
     echo("</span></td>");
     echo("<td valign=top class=interface-desc>");

     if ( strpos($inf, "oopback") === false ) {

       $link_query = mysql_query("select I.if, D.hostname, D.id AS dev_id, I.id from links AS L, interfaces AS I, devices AS D WHERE L.src_if = '$if_id' AND L.dst_if = I.id AND I.host = D.id");
       while($link = mysql_fetch_array($link_query)) {
         $link_if = fixifName($link['if']);
         echo("--> <a href='?page=interface&id=$link[id]'>$link_if</a> on <a href='?page=device&id=$link[dev_id]'>$link[hostname]</a><br />");
         $br = "<br />";
       }
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
              $class = "list-device-ignored";
              if ($new['status'] == '1') { $class = "green"; }
            }
            $this_ifid = $new['iid'];
            $this_hostid = $new['did'];
            $this_hostname = $new['hostname'];
            $this_ifname = fixifName($new['if']);
            $wq = mysql_query("select count(*) FROM links  WHERE dst_if = '$this_ifid' AND src_if = $if_id;");
            if (@mysql_result($wq, 0) == '0') {
              echo("$br &nbsp;|- <a href=?page=interface&id=$this_ifid>$this_ifname</a> on <a class=$class href=?page=device&id=$this_hostid>$this_hostname</a>");
              $br = "<br />";
            }
          }
        }
      }
     echo("</td></tr>");

     // If we're showing graphs, generate the graph and print the img tags
     if($_GET[graphs] == 'yes' && is_file("rrd/" . $hostname . ".". $ifIndex . ".rrd")) {
        $trafgraph = trafgraph ($hostname . ".". $ifIndex . ".rrd", $hostname . ".". $ifIndex . "-traf.png");
        $trafgraphmonth = trafgraph ($hostname . ".". $ifIndex . ".rrd", $hostname . ".". $ifIndex . "-month-traf.png", "-1m");
        if($trafgraph !== false) {
         echo("<tr><td colspan=2><img src='$trafgraph'> <img src='$trafgraphmonth'></td></tr>");
        }
      }

   $i++;
   echo("<tr height=5><td colspan=2></td></tr>");


?>
