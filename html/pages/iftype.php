<?php
if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

if($_GET['id']) {
  $type = $_GET['id'];
  $sql  = "select *, I.id as iid, I.ifIndex as ifIndex, D.id as did, D.hostname as hostname, I.if as ifname, I.name as ifalias ";
  $sql .= "from interfaces as I, devices as D WHERE `name` like '$type: %' AND I.host = D.id ORDER BY I.name";
  $query = mysql_query($sql);
  while($data = mysql_fetch_array($query)) {
    $done = "yes";
    unset($class);
    $iid = $data[iid];
    $ifIndex = $data[ifIndex];
    $did = $data[did];
    $hostname = $data[hostname];
    $up = $data[up];
    $up_admin = $data[up_admin];
    $ifname = fixifname($data[ifname]);
    $ifalias = $data[ifalias];
    $ifalias = str_replace($type . ": ", "", $ifalias);
    $ifalias = str_replace("[PNI]", "Private", $ifalias);
    $ifclass = ifclass($up, $up_admin);
    if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
    echo("<tr bgcolor='$bg'>
             <td><span class=interface><a href='?page=interface&id=$iid'>$ifalias</a></span><br /> 
            <span class=interface-desc><a href='?page=device&id=$did'>$hostname</a> <a href='?page=interface&id=$iid'>$ifname</a></span></td></tr><tr bgcolor='$bg'><td>");

if(file_exists("rrd/" . $hostname . ".". $ifIndex . ".rrd")) {

    $graph_type = "bits";
    include("includes/print-interface-graphs.php");

}
      echo("</td></tr>");
  }
}

echo("</table>");

if(!$done) { echo("None found."); }

?>
