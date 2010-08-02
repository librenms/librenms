<?php

### FIXME : remove link when port/host is not in the database (things /seen/ but not *discovered*)

include("../includes/defaults.inc.php");
include("../config.php");
include("../includes/functions.php");
include("includes/functions.inc.php");
include("includes/authenticate.inc.php");

  if (is_array($config['branding'])) {
      if ($config['branding'][$_SERVER['SERVER_NAME']]) {
          foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval) {
              eval("\$config['" . $confitem . "'] = \$confval;");
          }
      } else {
          foreach ($config['branding']['default'] as $confitem => $confval) {
              eval("\$config['" . $confitem . "'] = \$confval;");
          }
      }
  }


if (isset($_GET['device'])) { $where = "WHERE device_id = ".$_GET['device']; } else { $where = ""; }
$deviceresult = mysql_query("SELECT * from devices $where");

if (isset($_GET['format']) && preg_match("/^[a-z]*$/", $_GET['format']))
{

  $map = "digraph G { sep=0.01; size=\"12,5.5\"; pack=100; bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=0;
     node [ fontname=\"helvetica\", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false;];
     edge [ bgcolor=white; fontname=\"helvetica\"; fontstyle=bold; arrowhead=dot; arrowtail=dot];
     graph [bgcolor=transparent;];\n\n";

  if(!$_SESSION['authenticated']) 
  {
    $map .= "\"Not authenticated\" [fontsize=20 fillcolor=\"lightblue\" URL=\"/\" shape=box3d]\n";
  }
  else
  {
    while ($device = mysql_fetch_array($deviceresult))
    {
      if($device)
      {
        $sql = "SELECT * from ports AS I, links AS L WHERE I.device_id = ".$device['device_id']." AND L.local_interface_id = I.interface_id ORDER BY L.remote_hostname";
        $links = mysql_query($sql);

        if (mysql_num_rows($links))
        {
          $map .= "\"".$device['hostname']."\" [fontsize=20 fillcolor=\"lightblue\" URL=\"{$config['base_url']}/device/".$device['device_id']."/map/\" shape=box3d]\n";
        }

        while($link = mysql_fetch_array($links)) 
        {
          $local_interface_id = $link['local_interface_id'];
          $remote_interface_id = $link['remote_interface_id'];

          $i = 0; $done = 0;
          while (isset($linkdone) && $i < count($linkdone))
          {
            if ($linkdone[$i] == "$remote_interface_id $local_interface_id") { $done = 1; }
            $i++;
          }

          if(!$done) 
          {
            $linkdone[] = "$local_interface_id $remote_interface_id";

            if($link['ifSpeed'] >= "10000000000") 
            {
              $info = "color=red3 style=\"setlinewidth(6)\"";
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
            if($remote_interface_id) {
              $dst = mysql_result(mysql_query("SELECT `hostname` FROM `devices` AS D, `ports` AS I WHERE I.interface_id = '$remote_interface_id'  AND D.device_id = I.device_id"),0);
              $dst_host = mysql_result(mysql_query("SELECT D.device_id FROM `devices` AS D, `ports` AS I WHERE I.interface_id = '$remote_interface_id'  AND D.device_id = I.device_id"),0);
            } else {
	      $dst_host = $link['remote_hostname'];
              $dst = $link['remote_hostname'];
            }

            $sif = ifNameDescr(mysql_fetch_array(mysql_query("SELECT * FROM ports WHERE `interface_id`=" . $link['local_interface_id'])),$device);
            if($remote_interface_id) {
              $dif = ifNameDescr(mysql_fetch_array(mysql_query("SELECT * FROM ports WHERE `interface_id`=" . $link['remote_interface_id'])));
            } else {
              $dif['label'] = $link['remote_port'];
	      $dif['interface_id'] = $link['remote_hostname'] . $link['remote_port'];
	    }

            $map .= "\"" . $sif['interface_id'] . "\" [label=\"" . $sif['label'] . "\", fontsize=12, fillcolor=lightblue URL=\"{$config['base_url']}/device/".$device['device_id']."/interface/$local_interface_id/\"]\n";
            if (!$ifdone[$src][$sif['interface_id']])
            {
              $map .= "\"$src\" -> \"" . $sif['interface_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";
              $ifdone[$src][$sif['interface_id']] = 1;
            }

            $map .= "\"$dst\" [URL=\"{$config['base_url']}/device/$dst_host/map/\" fontsize=20 shape=box3d]\n";

            if($dst_host == $device['device_id'] || $where == '') {
              $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"{$config['base_url']}/device/$dst_host/interface/$remote_interface_id/\"]\n";
            } else {
              $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . " \", fontsize=12, fillcolor=lightgray, URL=\"{$config['base_url']}/device/$dst_host/interface/$remote_interface_id/\"]\n";
            }

            if (!$ifdone[$dst][$dif['interface_id']])
            {
              $map .= "\"" . $dif['interface_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
              $ifdone[$dst][$dif['interface_id']] = 1;
            }
            $map .= "\"" . $sif['interface_id'] . "\" -> \"" . $dif['interface_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
          }
        }
        $done = 0;
      }
    }
  }

  $map .= "
};";

  if ($_GET['debug'] == 1) { echo("<pre>$map</pre>");exit(); }

  switch ($_GET['format'])
  {
    case 'svg':
    case 'png':
      break;
    default:
      $_GET['format'] = 'png';
  }

  $maptool = 'unflatten -f -l 5 |dot';
  
  if ($where == '') { $maptool = 'neato -Gpack'; }

  $img = shell_exec("echo \"".addslashes($map)."\" | $maptool -T".$_GET['format']."");
  if($_GET['format'] == "png") {
    header("Content-type: image/".$_GET['format']);
  } elseif ($_GET['format'] == "svg") {
    header("Content-type: image/svg+xml");
    $img = str_replace("<a ", "<a target  = \"_parent\" ", $img);
  }
  echo("$img");
}
else
{
  if ($_SESSION['authenticated']) ## FIXME level 10 only?
  {
    echo '<center>
    <object data="'. $config['base_url'] . '/map.php?format=svg" type="image/svg+xml">
    </object>
</center>';
  }
}
?>
