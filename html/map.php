<?php

### FIXME : remove link when port/host is not in the database (things /seen/ but not *discovered*)
###         No, that should be there... I like to see that stuff :> (adama)

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);

$links = 1;

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/functions.php");
include_once("../includes/dbFacile.php");
include_once("includes/functions.inc.php");
include_once("includes/authenticate.inc.php");

if (is_array($config['branding']))
{
  if ($config['branding'][$_SERVER['SERVER_NAME']])
  {
    foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  } else {
    foreach ($config['branding']['default'] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  }
}

if (isset($_GET['device'])) { $where = "WHERE device_id = ".mres($_GET['device']); } else { $where = ""; }

## FIXME this shit probably needs tidied up.

if (isset($_GET['format']) && preg_match("/^[a-z]*$/", $_GET['format']))
{
  $map = 'digraph G { sep=0.01; size="12,5.5"; pack=100; bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=0;
     node [ fontname="helvetica", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false];
     edge [ bgcolor=white, fontname="helvetica", fontstyle=bold, arrowhead=dot, arrowtail=dot];
     graph [bgcolor=transparent];

';

  if (!$_SESSION['authenticated'])
  {
    $map .= "\"Not authenticated\" [fontsize=20 fillcolor=\"lightblue\", URL=\"/\" shape=box3d]\n";
  }
  else
  {
    $loc_count = 1;

    foreach (dbFetch("SELECT * from devices ".$where) as $device)
    {
      if ($device)
      {
        $links = dbFetch("SELECT * from ports AS I, links AS L WHERE I.device_id = ? AND L.local_interface_id = I.interface_id ORDER BY L.remote_hostname", array($device['device_id']));
        if (count($links))
        {
	  if(!isset($locations[$device['location']])) { $locations[$device['location']] = $loc_count; $loc_count++; }
          $loc_id = $locations[$device['location']];

          $map .= "\"".$device['hostname']."\" [fontsize=20, fillcolor=\"lightblue\", group=".$loc_id." URL=\"{$config['base_url']}/device/device=".$device['device_id']."/tab=map/\" shape=box3d]\n";
        }

        foreach  ($links as $link)
        {
          $local_interface_id = $link['local_interface_id'];
          $remote_interface_id = $link['remote_interface_id'];

          $i = 0; $done = 0;
          if ($linkdone[$remote_interface_id][$local_interface_id]) { $done = 1; }

          if (!$done)
          {
            $linkdone[$local_interface_id][$remote_interface_id] = TRUE;

            $links++;

            if ($link['ifSpeed'] >= "10000000000")
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
            if ($remote_interface_id)
            {
              $dst = dbFetchCell("SELECT `hostname` FROM `devices` AS D, `ports` AS I WHERE I.interface_id = ? AND D.device_id = I.device_id", array($remote_interface_id));
              $dst_host = dbFetchCell("SELECT D.device_id FROM `devices` AS D, `ports` AS I WHERE I.interface_id = ?  AND D.device_id = I.device_id", array($remote_interface_id));
            } else {
              unset($dst_host);
              $dst = $link['remote_hostname'];
            }

            $sif = ifNameDescr(dbFetchRow("SELECT * FROM ports WHERE `interface_id` = ?", array($link['local_interface_id'])),$device);
            if ($remote_interface_id)
            {
              $dif = ifNameDescr(dbFetchRow("SELECT * FROM ports WHERE `interface_id` = ?", array($link['remote_interface_id'])));
            } else {
              $dif['label'] = $link['remote_port'];
              $dif['interface_id'] = $link['remote_hostname'] . $link['remote_port'];
            }
          
            if($where == "")
            {
              $map .= "\"$src\" -> \"" . $dst . "\" [weight=500000, arrowsize=0, len=0];\n";
              $ifdone[$src][$sif['interface_id']] = 1;
            } else {
              $map .= "\"" . $sif['interface_id'] . "\" [label=\"" . $sif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"{$config['base_url']}/device/device=".$device['device_id']."/port/$local_interface_id/\"]\n";
              if (!$ifdone[$src][$sif['interface_id']])
              {
                $map .= "\"$src\" -> \"" . $sif['interface_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";
                $ifdone[$src][$sif['interface_id']] = 1;
              }

              if ($dst_host)
              {
                $map .= "\"$dst\" [URL=\"{$config['base_url']}/device/device=$dst_host/tab=map/\", fontsize=20, shape=box3d]\n";
              } else {
                $map .= "\"$dst\" [ fontsize=20 shape=box3d]\n";
              }

              if ($dst_host == $device['device_id'] || $where == '')
              {
                $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"{$config['base_url']}/device/device=$dst_host/tab=port/port=$remote_interface_id/\"]\n";
              } else {
                $map .= "\"" . $dif['interface_id'] . "\" [label=\"" . $dif['label'] . " \", fontsize=12, fillcolor=lightgray, URL=\"{$config['base_url']}/device/device=$dst_host/tab=port/port=$remote_interface_id/\"]\n";
              }

              if (!$ifdone[$dst][$dif['interface_id']])
              {
                $map .= "\"" . $dif['interface_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
                $ifdone[$dst][$dif['interface_id']] = 1;
              }
              $map .= "\"" . $sif['interface_id'] . "\" -> \"" . $dif['interface_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
            }
          }
        }
        $done = 0;
      }
    }
  }

  $map .= "
};";

  if ($_GET['debug'] == 1)
  {
    echo("<pre>$map</pre>");exit();
  }

  switch ($_GET['format'])
  {
    case 'svg':
      break;
    case 'png':
      $_GET['format'] = 'png:gd';
      break;
    case 'dot':
      echo($map);exit();
    default:
      $_GET['format'] = 'png:gd';
  }

  if ($links > 30) ### Unflatten if there are more than 10 links. beyond that it gets messy
  {
    $maptool = $config['unflatten'] . ' -f -l 5 | ' . $config['sfdp'] . ' -Gpack -Gcharset=latin1 | '.$config['dot'];
  } else {
    $maptool = $config['dot'];
  }

  if ($where == '')
  {
#    $maptool = $config['unflatten'] . ' -f -l 5 | ' . $config['sfdp'] . ' -Gpack -Goverlap=prism -Gcharset=latin1 | dot';
#    $maptool = $config['sfdp'] . ' -Gpack -Goverlap=prism -Gcharset=latin1 -Gsize=20,20';
     $maptool = $config['neato'];
  }

  $descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w") );

  $mapfile = $config['temp_dir'] . "/"  . strgen() . ".png";

  $process = proc_open($maptool.' -T'.$_GET['format'],$descriptorspec,$pipes);

  if (is_resource($process))
  {
    fwrite($pipes[0],  "$map");
    fclose($pipes[0]);
    while (! feof($pipes[1])) {$img .= fgets($pipes[1]);}
    fclose($pipes[1]);
    $return_value = proc_close($process);
  }

  if ($_GET['format'] == "png:gd")
  {
    header("Content-type: image/png");
  } elseif ($_GET['format'] == "svg") {
    header("Content-type: image/svg+xml");
    $img = str_replace("<a ", '<a target="_parent" ', $img);
  }
  echo("$img");
}
else
{
  if ($_SESSION['authenticated']) ## FIXME level 10 only?
  {
    echo('<center>
    <object width=1200 height=1000 data="'. $config['base_url'] . '/map.php?format=svg" type="image/svg+xml">
    </object>
</center>');
  }
}

?>
