<?php

if ($_GET['from'])    { $from   = mres($_GET['from']);   }
if ($_GET['to'])      { $to     = mres($_GET['to']);     }
if ($_GET['width'])   { $width  = mres($_GET['width']);  }
if($config['trim_tobias']) { $width+=12; }
if ($_GET['height'])  { $height = mres($_GET['height']); }
if ($_GET['inverse']) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
if ($_GET['legend'] == "no")  { $rrd_options = " -g"; }

if ($_GET['title'] == "yes")  { $rrd_options .= " --title='".$graph_title."' "; }
if (isset($_GET['graph_title']))  { $rrd_options .= " --title='".$_GET['graph_title']."' "; }

#if (!isset($scale_min) && !isset($scale_max)) { $rrd_options .= " --alt-autoscale-max"; }

if (isset($scale_min)) { $rrd_options .= " -l $scale_min"; }
if (isset($scale_max)) { $rrd_options .= " -u $scale_max"; }
if (isset($scale_rigid)) { $rrd_options .= " -r"; }

$rrd_options .= " -E --start ".$from." --end " . $to . " --width ".$width." --height ".$height." ";
$rrd_options .= $config['rrdgraph_def_text'];

if ($_GET['bg']) { $rrd_options .= " -c CANVAS#" . mres($_GET['bg']) . " "; }

#$rrd_options .= " -c BACK#FFFFFF";

if ($height < "99")  { $rrd_options .= " --only-graph"; }

if ($width <= "300") { $rrd_options .= " --font LEGEND:7:" . $config['mono_font'] . " --font AXIS:6:" . $config['mono_font']; }
else {                 $rrd_options .= " --font LEGEND:8:" . $config['mono_font'] . " --font AXIS:7:" . $config['mono_font']; }

$rrd_options .= " --font-render-mode normal";

?>
