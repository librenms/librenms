<?php

// Push $_GET into $vars to be compatible with web interface naming

foreach ($_GET as $name => $value)
{
  $vars[$name] = $value;
}

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $vars['type'], $graphtype);

if(is_numeric($vars['device']))
{
  $device = device_by_id_cache($vars['device']);
} elseif(!empty($vars['device'])) {
  $device = device_by_name($vars['device']);
}

// FIXME -- remove these

$width    = $vars['width'];
$height   = $vars['height'];
$title    = $vars['title'];
$vertical = $vars['vertical'];
$legend   = $vars['legend'];

$from     = (isset($vars['from']) ? $vars['from'] : time() - 60*60*24);
$to       = (isset($vars['to']) ? $vars['to'] : time());

if ($from < 0) { $from = $to + $from; }

$period = $to - $from;

$prev_from = $from - $period;

$graphfile = $config['temp_dir'] . "/"  . strgen() . ".png";

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];

if (is_file($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php"))
{

  if (isset($config['allow_unauth_graphs']) && $config['allow_unauth_graphs'])
  {
    $auth = "1"; // hardcode auth for all with config function
  }

  if (isset($config['allow_unauth_graphs_cidr']) && count($config['allow_unauth_graphs_cidr']) > 0)
  {
    foreach ($config['allow_unauth_graphs_cidr'] as $range)
    {
      if (Net_IPv4::ipInNetwork($_SERVER['REMOTE_ADDR'], $range))
      {
        $auth = "1";
        if ($debug) { echo("matched $range"); }
        break;
      }
    }
  }

  include($config['install_dir'] . "/html/includes/graphs/$type/auth.inc.php");

  if (isset($auth) && $auth)
  {
    include($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php");
  }

}
else
{
  graph_error("$type*$subtype ");//Graph Template Missing");
}

function graph_error($string)
{
  global $vars, $config, $debug, $graphfile;

  $vars['bg'] = "FFBBBB";

  include("includes/graphs/common.inc.php");

  $rrd_options .= " HRULE:0#555555";
  $rrd_options .= " --title='".$string."'";

  rrdtool_graph($graphfile, $rrd_options);

  if ($height > "99")  {
    shell_exec($rrd_cmd);
    if ($debug) { echo("<pre>".$rrd_cmd."</pre>"); }
    if (is_file($graphfile) && !$debug)
    {
      header('Content-type: image/png');
      $fd = fopen($graphfile,'r'); fpassthru($fd); fclose($fd);
      unlink($graphfile);
      exit();
    }
  } else {
    if (!$debug) { header('Content-type: image/png'); }
    $im     = imagecreate($width, $height);
    $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
    imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
    imagepng($im);
    imagedestroy($im);
    exit();
  }
}


if ($error_msg) {
  // We have an error :(

  graph_error($graph_error);

} elseif ($auth === null) {
  // We are unauthenticated :(

  if ($width < 200)
  {
   graph_error("No Auth");
  } else {
   graph_error("No Authorisation");
  }
} else {
  #$rrd_options .= " HRULE:0#999999";
  if ($no_file)
  {
    if ($width < 200)
    {
      graph_error("No RRD");
    } else {
      graph_error("Missing RRD Datafile");
    }
  } elseif($command_only) {
    echo("<div class='infobox'>");
    echo("<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>");
    echo("rrdtool graph $graphfile $rrd_options");
    echo("</span>");
    $return = rrdtool_graph($graphfile, $rrd_options);
    echo("<br /><br />");
    echo("<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>$return");
    unlink($graphfile);
    echo("</div>");
  } else {

    if ($rrd_options)
    {
      rrdtool_graph($graphfile, $rrd_options);
      if ($debug) { echo($rrd_cmd); }
      if (is_file($graphfile))
      {
        if (!$debug)
        {
          header('Content-type: image/png');
          if ($config['trim_tobias'])
          {
            list($w, $h, $type, $attr) = getimagesize($graphfile);
            $src_im = imagecreatefrompng($graphfile);
            $src_x = '0';   // begin x
              $src_y = '0';   // begin y
              $src_w = $w-12; // width
            $src_h = $h; // height
            $dst_x = '0';   // destination x
            $dst_y = '0';   // destination y
            $dst_im = imagecreatetruecolor($src_w, $src_h);
             imagesavealpha($dst_im, true);
            $white = imagecolorallocate($dst_im, 255, 255, 255);
            $trans_colour = imagecolorallocatealpha($dst_im, 0, 0, 0, 127);
            imagefill($dst_im, 0, 0, $trans_colour);
            imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
            imagepng($dst_im);
            imagedestroy($dst_im);
          } else {
            $fd = fopen($graphfile,'r');fpassthru($fd);fclose($fd);
          }

        } else {
          echo(`ls -l $graphfile`);
          echo('<img src="'.data_uri($graphfile,'image/png').'" alt="graph" />');
        }
        unlink($graphfile);
      }
      else
      {
        if ($width < 200)
        {
          graph_error("Draw Error");
        }
        else
        {
          graph_error("Error Drawing Graph");
        }
      }
    }
    else
    {
      if ($width < 200)
      {
        graph_error("Def Error");
      } else {
        graph_error("Graph Definition Error");
      }
    }
  }
}

?>
