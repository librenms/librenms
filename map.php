#!/usr/bin/php
<?

include("config.php");
include("includes/functions.php");

echo("digraph G { sep=0.5; size=\"50,50\"; pack=15; bgcolor=transparent;
     node [ fontname=\"times roman\" fontsize=18 fontstyle=bold ]; edge [ labelfontsize=12 labelfontname=\"times roman\" ];
     graph [bgcolor=transparent]; ");

$x = 1;

$dev_sql = "SELECT * FROM links AS L, interfaces AS I, interfaces AS X, devices as D 
            WHERE I.host = D.id AND L.src_if = I.id GROUP BY D.hostname ORDER BY hostname DESC";
$dev_result = mysql_query($dev_sql);
while($dev_data = mysql_fetch_array($dev_result)) {
	$host = $dev_data['hostname'];
        unset($hostinfo);	
        if(strpos($host, "cust." . $mydomain)) { $hostinfo = "shape=egg style=filled fillcolor=pink"; }
	elseif(strpos($host, $mydomain)) { 
          if(strpos($host, "-sw")) { $hostinfo = "shape=rectangle style=filled fillcolor=skyblue"; } 
          if(strpos($host, "-gw")) { $hostinfo = "shape=rectangle style=filled fillcolor=yellow"; }
        } else { $hostinfo = "style=filled shape=rectangle fillcolor=lightgray"; }

        $host = $dev_data[hostname];
	$host = str_replace("." . $mydomain,"", $host);
	echo("\"$host\" [$hostinfo]
");	

}

$linkdone = array();

$links_sql = "SELECT *, X.if AS sif, I.if AS dif FROM links AS L, interfaces AS I, interfaces AS X, devices as D where I.host = D.id AND L.src_if = I.id AND X.id = L.dst_if ORDER BY D.hostname";
$links_result = mysql_query($links_sql);
while($link_data = mysql_fetch_array($links_result)) {

	$src_if = $link_data['src_if'];
	$dst_if = $link_data['dst_if'];

        $sq = mysql_fetch_row(mysql_query("SELECT `hostname`,`ifSpeed` FROM interfaces AS I, devices as D where I.host = D.id and I.id = '$src_if'"));
        $dq = mysql_fetch_row(mysql_query("SELECT `hostname`,`ifSpeed` FROM interfaces AS I, devices as D where I.host = D.id and I.id = '$dst_if'"));

        $src = $sq[0];
        $dst = $dq[0];

	$src_speed = $sq[1]; 
	$dst_speed = $dq[1];

	$src = str_replace("." . $mydomain, "", $src);
	$dst = str_replace("." . $mydomain, "", $dst);

	$info = "";

#	if($src_speed == "10 Gbps") { 
#           $info .= "color=red weight=5"; 
#        } elseif ($src_speed == "1.0 Gbps") {
#           $info .= "color=blue weight=10";
#        } elseif ($src_speed == "100 Gbps") {
#           $info .= "color=green weight=1";
#        } elseif ($src_speed == "10 mbps") {
#           $info .= "";
#        }

	unset($die);

	$i = 0;
	while ($i < count($linkdone)) {
	    $thislink = "$dst $link_data[dif] $src $link_data[sif]";
            if ($linkdone[$i] == $thislink) { $die = "yes"; }
	    $i++;
	}

	$sif = makeshortif($link_data[sif]);
	$dif = makeshortif($link_data[dif]);

#       $sif = $link_data[sif];
#       $dif = $link_data[dif];


	if(!$die){	
#	echo("\"$src\" -> \"$dst\" [taillabel=\"$dif\"  headlabel=\"$sif\" arrowhead=none arrowtail=none $info];\n");
        echo("\"$src\" -> \"$dst\" [ arrowhead=none arrowtail=none $info];\n");
	$linkdone[] = "$src $link_data[sif] $dst $link_data[dif]";
	$x++;
        }
}

echo("}");

?>
