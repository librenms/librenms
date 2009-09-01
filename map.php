#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

echo("digraph G { sep=0.6; size=\"40,20\"; pack=10; bgcolor=transparent;splines=true;
     node [ fontname=\"helvetica\", fontsize=38, fontstyle=bold, shape=box, style=bold]; 
     edge [ labelfontsize=10, labelfontname=\"helvetica\", overlap=false, fontstyle=bold];
     graph [bgcolor=transparent, remincross=true]; 

");

$linkdone = array();

$x = 1;

$loc_sql = "SELECT * FROM devices GROUP BY location";
$loc_result = mysql_query($loc_sql);
while($loc_data = mysql_fetch_array($loc_result)) {

  echo("subgraph \"". $loc_data['location']  ."\" {\n
  label = \"". $loc_data['location']  ."\"; 
  style=filled;
  color=lightgrey;\n\n");
    
  $dev_sql = "SELECT * FROM devices WHERE location = '" . $loc_data['location'] . "' and `os` LIKE '%IOS%' and disabled = 0";
  $dev_result = mysql_query($dev_sql);
  while($dev_data = mysql_fetch_array($dev_result)) {
        $device_id = $dev_data['device_id'];

#    if(mysql_result(mysql_query("SELECT count(*) from links WHERE src_if = '$device_id' OR dst_if = '$device_id'"),0)) {
	$host = $dev_data['hostname'];
        unset($hostinfo);	
        if(strpos($host, "cust." . $config['mydomain'])) { $hostinfo = "shape=egg style=filled fillcolor=pink"; }
        if(strpos($host, "bas")) { $hostinfo = "shape=rectangle style=filled fillcolor=skyblue"; } 
        if(strpos($host, "crs")) { $hostinfo = "shape=box3d style=filled fillcolor=skyblue"; }
        if(strpos($host, "lcr")) { $hostinfo = "shape=tripleoctagon style=filled fillcolor=darkolivegreen4"; }
	if(strpos($host, "ler")) { $hostinfo = "shape=octagon style=filled fillcolor=darkolivegreen1"; }
	if(strpos($host, "pbr")) { $hostinfo = "shape=ellipse style=filled fillcolor=orange"; }
	if(strpos($host, "tbr")) { $hostinfo = "shape=ellipse style=filled fillcolor=orange"; }
	if(strstr($host, "gwr")) { $hostinfo = "shape=ellipse style=filled fillcolor=orange"; }
	if(strpos($host, "bgw")) { $hostinfo = "shape=ellipse style=filled fillcolor=orange"; }
	if(strpos($host, "vax")) { $hostinfo = "shape=rect style=filled fillcolor=skyblue"; }
        if(strpos($host, "vsx")) { $hostinfo = "shape=box3d style=filled fillcolor=skyblue"; }
        #} else { $hostinfo = "style=filled shape=circle fillcolor=lightgray"; }

        $host = $dev_data[hostname];
	$host = str_replace("." . $config['mydomain'],"", $host);
	echo("\"$host\" [$hostinfo]
        ");	
 #   }
 

  }


echo("\n}\n");

}

$links_sql = "SELECT *, X.ifDescr AS sif, I.ifDescr AS dif FROM links AS L, interfaces AS I, interfaces AS X, devices as D, devices as Y WHERE  I.device_id = D.device_id AND X.device_id = Y.device_id AND L.src_if = I.interface_id AND X.interface_id = L.dst_if";

$links_result = mysql_query($links_sql);
while($link_data = mysql_fetch_array($links_result)) {

	$src_if = $link_data['src_if'];
	$dst_if = $link_data['dst_if'];

        $sq = mysql_fetch_row(mysql_query("SELECT `hostname`,`ifSpeed` FROM interfaces AS I, devices as D where I.device_id = D.device_id and I.interface_id = '$src_if'"));
        $dq = mysql_fetch_row(mysql_query("SELECT `hostname`,`ifSpeed` FROM interfaces AS I, devices as D where I.device_id = D.device_id and I.interface_id = '$dst_if'"));

        $src = $sq[0];
        $dst = $dq[0];

	$src_speed = $sq[1]; 
	$dst_speed = $dq[1];

	$src = str_replace("." . $config['mydomain'], "", $src);
	$dst = str_replace("." . $config['mydomain'], "", $dst);

	$info = "";

	if($src_speed >= "10000000000") { 
           $info .= "color=lightred weight=10 style=\"setlinewidth(8)\""; 
        } elseif ($src_speed >= "1000000000") {
           $info .= "color=lightblue weight=5 style=\"setlinewidth(4)\"";
        } elseif ($src_speed >= "100000000") {
           $info .= "color=lightgrey weight=1 style=\"setlinewidth(2)\"";
        } elseif ($src_speed >= "10000000") {
           $info .= "style=\"setlinewidth(1)\" weight=1";
        }

	unset($die);

	$i = 0;
	while ($i < count($linkdone)) {
	    $thislink = "$dst $link_data[dif] $src $link_data[sif]";
            if ($linkdone[$i] == $thislink) { $die = "yes"; }
	    $i++;
	}

	$sif = makeshortif($link_data[sif]);
	$dif = makeshortif($link_data[dif]);

	if(!$die){	
	echo("\"$src\" -> \"$dst\" [taillabel=\"$dif\"  headlabel=\"$sif\" arrowhead=dot arrowtail=dot $info];\n");
#       echo("\"$src\" -> \"$dst\" [ arrowhead=none arrowtail=none $info];\n");
	$linkdone[] = "$src $link_data[sif] $dst $link_data[dif]";
	$x++;
        }
}

echo("}");

?>
