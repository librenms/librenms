<?php

include("../config.php");
include("../includes/functions.php");

#FIXME no auth on thie file??
if ($_GET['device']) { $where = "WHERE device_id = ".$_GET['device']; } else { $where = ""; }
$deviceresult = mysql_query("SELECT * from devices $where");

if (preg_match("/^[a-z]*$/", $_GET['format']))
{

  $map = "digraph G { sep=0.01; size=\"12,5.5\"; pack=100; bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=0;
     node [ fontname=\"helvetica\", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false;];
     edge [ bgcolor=white; fontname=\"helvetica\"; fontstyle=bold; arrowhead=dot; arrowtail=dot];
     graph [bgcolor=transparent;];

";

  while ($device = mysql_fetch_array($deviceresult))
  {
    if($device)
    {
      $sql = "SELECT * from interfaces AS I, links AS L WHERE I.device_id = ".$device['device_id']." AND L.src_if = I.interface_id";
      $links = mysql_query($sql);

      if (mysql_num_rows($links))
      {
        $map .= "\"".$device['hostname']."\" [fontsize=20 fillcolor=\"lightblue\" URL=\"/device/".$device['device_id']."/map/\" shape=box3d]\n";
      }

      while($link = mysql_fetch_array($links)) 
      {
        $src_if = $link['src_if'];
        $dst_if = $link['dst_if'];

        $i = 0; $done = 0;
        while ($i < count($linkdone)) 
        {
          $thislink = "$dst_if $src_if";
          if ($linkdone[$i] == $thislink) { $done = 1; }
          $i++;
        }

        if(!$done) 
        {
          $linkdone[] = "$src_if $dst_if";

          if($link['ifSpeed'] >= "10000000000") 
          {
            $info = "color=lightred style=\"setlinewidth(8)\"";
          } elseif ($link['ifSpeed'] >= "1000000000") {
            $info = "color=lightblue style=\"setlinewidth(4)\"";
          } elseif ($link['ifSpeed'] >= "100000000") {
            $info = "color=lightgrey style=\"setlinewidth(2)\"";
          } elseif ($link['ifSpeed'] >= "10000000") {
            $info = "style=\"setlinewidth(1)\"";
          } else {
            $info = "style=\"setlinewidth(1)\"";
          }

          $src = $device['hostname'];
          $dst = mysql_result(mysql_query("SELECT `hostname` FROM `devices` AS D, `interfaces` AS I WHERE I.interface_id = '$dst_if'  AND D.device_id = I.device_id"),0);
          $dst_host = mysql_result(mysql_query("SELECT D.device_id FROM `devices` AS D, `interfaces` AS I WHERE I.interface_id = '$dst_if'  AND D.device_id = I.device_id"),0);

          $sif = ifNameDescr(mysql_fetch_array(mysql_query("SELECT * FROM interfaces WHERE `interface_id`=" . $link['src_if'])),$device);
          $dif = ifNameDescr(mysql_fetch_array(mysql_query("SELECT * FROM interfaces WHERE `interface_id`=" . $link['dst_if'])));

          $map .= "\"" . $sif['interface_id'] . "\" [label=\"" . $sif['label'] . "\", fontsize=12, fillcolor=lightblue URL=\"/device/".$device['device_id']."/interface/$src_if/\"]\n";
          $map .= "\"$src\" -> \"" . $sif['interface_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";

          $map .= "\"$dst\" [URL=\"/device/$dst_host/map/\" fontsize=20 shape=box3d]\n";

          if($dst_host == $device['device_id']) {
            $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"/device/$dst_host/interface/$dst_if/\"]\n";
          } else {
            $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . " \", fontsize=12, fillcolor=lightgray, URL=\"/device/$dst_host/interface/$dst_if/\"]\n";
          }

          $map .= "\"" . $dif['interface_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
          $map .= "\"" . $sif['interface_id'] . "\" -> \"" . $dif['interface_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
        }
      }
      $done = 0;
    }
  }

  $map .= "
};";

  if ($_GET['debug'] == 1) { echo("<pre>$map</pre>");exit(); }

  $img = shell_exec("echo \"".addslashes($map)."\" | dot -T".$_GET['format']."");
  if($_GET['format'] == "png") {
    header("Content-type: image/".$_GET['format']);
  } elseif ($_GET['format'] == "svg") {
    header("Content-type: image/svg+xml");
    $img = str_replace("<a ", "<a target  = \"_parent\" ", $img);
  }
  echo("$img");
}

?>
