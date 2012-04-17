<?php

function data_uri($file, $mime)
{
  $contents = file_get_contents($file);
  $base64   = base64_encode($contents);
  return ('data:' . $mime . ';base64,' . $base64);
}

$width    = $_GET['width'];
$height   = $_GET['height'];
$title    = $_GET['title'];
$vertical = $_GET['vertical'];
$legend   = $_GET['legend'];
$id       = $_GET['id'];

$from     = (isset($_GET['from']) ? $_GET['from'] : time() - 60*60*24);
$to       = (isset($_GET['to']) ? $_GET['to'] : time());

if ($from < 0) { $from = $to + $from; }

$period = $to - $from;

$prev_from = $from - $period;

$graphfile = $config['temp_dir'] . "/"  . strgen() . ".png";

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $_GET['type'], $graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];

if (is_file($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php"))
{
  if (isset($config['allow_unauth_graphs_cidr']) && count($config['allow_unauth_graphs_cidr']) > 0)
  {
    foreach ($config['allow_unauth_graphs_cidr'] as $range)
    {
      if (Net_IPv4::ipInNetwork($_SERVER['REMOTE_ADDR'], $range))
      {
        $auth = "1";
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
  graph_error("Graph Template Missing");
}

function graph_error($string)
{
  global $_GET, $config, $debug, $graphfile;

  $_GET['bg'] = "FFBBBB";

  include("includes/graphs/common.inc.php");

  $rrd_options .= " HRULE:0#555555";
  $rrd_options .= " --title='".$string."'";

  rrdtool_graph($graphfile, $rrd_options);

  if ($height > "99")  {
    $woo = shell_exec($rrd_cmd);
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
    $orange = imagecolorallocate($im, 255, 225, 225);
    $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
    imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
    imagepng($im);
    imagedestroy($im);
    exit();
  }
}

if ($error_msg) {
  /// We have an error :(

  graph_error($graph_error);

} elseif (!$auth) {
  /// We are unauthenticated :(

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
          if($config['trim_tobias'])
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
	    $white = imagecolorallocate($dst_im, 255, 255, 255);
	    imagefill($dst_im, 0, 0, $white);
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
