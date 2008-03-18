#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

echo("digraph G { sep=0.5; size=\"40,30\"; pack=15; bgcolor=transparent;splines=true;
     node [ fontname=\"times roman\", fontsize=24, fontstyle=bold, shape=box, style=bold]; 
     edge [ labelfontsize=14, labelfontname=\"times roman\", overlap=false];
     graph [bgcolor=transparent, remincross=true]; 

");

$linkdone = array();

$x = 1;

$loc_sql = "SELECT * FROM links AS L, interfaces AS I, interfaces AS X, devices as D WHERE I.device_id = D.device_id AND L.src_if = I.interface_id AND D.hostname LIKE '%vostron.net' AND D.hostname NOT LIKE 'cust.vostron.net' GROUP BY D.location ORDER BY D.device_id ASC";
$loc_result = mysql_query($loc_sql);
while($loc_data = mysql_fetch_array($loc_result)) {

   echo("subgraph \"". $loc_data['location']  ."\" {\n
  label = \"". $loc_data['location']  ."\"; 
  style=filled;
  color=lightgrey;\n\n");
    
  if($loc_data['location'] == "TFM3, Telehouse North, London") {
    echo("  \"Internet\" [shape=tripleoctagon style=filled fillcolor=crimson]\n"); 
    echo("  \"ADSL\" [shape=tripleoctagon style=filled fillcolor=orange]\n");
  }

  $dev_sql = "SELECT * FROM links AS L, interfaces AS I, interfaces AS X, devices as D WHERE D.location = '" . $loc_data['location'] . "' AND I.device_id = D.device_id AND L.src_if = I.interface_id AND D.hostname LIKE '%vostron.net' AND D.hostname NOT LIKE '%cust.vostron.net' GROUP BY D.hostname";
  $dev_result = mysql_query($dev_sql);
  while($dev_data = mysql_fetch_array($dev_result)) {
	$host = $dev_data['hostname'];
        unset($hostinfo);	
        if(strpos($host, "cust." . $config['mydomain'])) { $hostinfo = "shape=egg style=filled fillcolor=pink"; }
	elseif(strpos($host, $config['mydomain'])) { 
          if(strpos($host, "-sw")||strpos($host, "-cs")) { $hostinfo = "shape=rectangle style=filled fillcolor=skyblue"; } 
          if(strpos($host, "-gw")||strpos($host, "-pe")||strpos($host, "-er")) { $hostinfo = "shape=ellipse style=filled fillcolor=darkolivegreen3"; }
	  if(strpos($host, "-lns")) { $hostinfo = "shape=egg style=filled fillcolor=darkolivegreen4"; }
        } else { $hostinfo = "style=filled shape=circle fillcolor=lightgray"; }

        $host = $dev_data[hostname];
	$host = str_replace("." . $config['mydomain'],"", $host);
	echo("\"$host\" [$hostinfo]
  ");	

  }

  $links_sql = "SELECT *, X.ifDescr AS sif, I.ifDescr AS dif FROM links AS L, interfaces AS I, interfaces AS X, devices as D, devices as Y WHERE D.hostname LIKE '%vostron.net' AND I.device_id = D.device_id AND X.device_id = Y.device_id AND D.hostname NOT LIKE '%cust%' AND Y.hostname NOT LIKE '%cust%' AND L.src_if = I.interface_id AND X.interface_id = L.dst_if  AND D.location = '" . $loc_data['location'] . "' AND  D.location = Y.location ORDER BY D.location DESC";
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
           $info .= "color=darkred weight=10 style=\"setlinewidth(16)\"";
        } elseif ($src_speed >= "1000000000") {
           $info .= "color=navyblue weight=5 style=\"setlinewidth(8)\"";
        } elseif ($src_speed >= "100000000") {
           $info .= "color=darkgreen weight=1 style=\"setlinewidth(4)\"";
        } elseif ($src_speed >= "10000000") {
           $info .= "style=\"setlinewidth(2)\" weight=1";
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
#       echo("\"$src\" -> \"$dst\" [taillabel=\"$dif\"  headlabel=\"$sif\" arrowhead=none arrowtail=none $info];\n");
        echo("\"$src\" -> \"$dst\" [ arrowhead=none arrowtail=none $info];\n");
        $linkdone[] = "$src $link_data[sif] $dst $link_data[dif]";
        $x++;
        }
  }


echo("\n}\n");

}

echo("\"thnlon-pe01\" -> \"thelon-cs01\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");
echo("\"thnlon-pe01\" -> \"thelon-cs02\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");

echo("\"Internet\" -> \"thelon-pe01\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");
echo("\"Internet\" -> \"thelon-pe02\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");
echo("\"Internet\" -> \"thnlon-pe01\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");
echo("\"ADSL\" -> \"thnlon-pe01\" [ arrowhead=none arrowtail=none color=navyblue weight=5 style=\"setlinewidth(8)\"];\n");

$links_sql = "SELECT *, X.ifDescr AS sif, I.ifDescr AS dif FROM links AS L, interfaces AS I, interfaces AS X, devices as D, devices as Y WHERE D.hostname LIKE '%vostron.net' AND I.device_id = D.device_id AND X.device_id = Y.device_id AND D.hostname NOT LIKE '%cust%' AND L.src_if = I.interface_id AND X.interface_id = L.dst_if  AND D.location != Y.location AND D.hostname NOT LIKE '%cust.vostron.net' AND Y.hostname NOT LIKE '%cust.vostron.net' ORDER BY D.location DESC";
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
           $info .= "color=darkred weight=10 style=\"setlinewidth(16)\""; 
        } elseif ($src_speed >= "1000000000") {
           $info .= "color=navyblue weight=5 style=\"setlinewidth(8)\"";
        } elseif ($src_speed >= "100000000") {
           $info .= "color=darkgreen weight=1 style=\"setlinewidth(4)\"";
        } elseif ($src_speed >= "10000000") {
           $info .= "style=\"setlinewidth(2)\" weight=1";
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
	echo("\"$src\" -> \"$dst\" [taillabel=\"$dif\"  headlabel=\"$sif\" arrowhead=none arrowtail=none $info];\n");
#       echo("\"$src\" -> \"$dst\" [ arrowhead=none arrowtail=none $info];\n");
	$linkdone[] = "$src $link_data[sif] $dst $link_data[dif]";
	$x++;
        }
}

echo("}");

?>
