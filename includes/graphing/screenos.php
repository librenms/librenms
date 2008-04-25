<?php

function graph_netscreen_sessions ($rrd, $graph, $from, $to, $width, $height, $title=NULL, $vertical=NULL) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $options = "-l 0 -Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:alloc=$database:allocate:AVERAGE";
  $options .= " DEF:max=$database:max:AVERAGE";
  $options .= " DEF:failed=$database:failed:AVERAGE";
  $options .= " COMMENT:Sessions\ \ \ \ \ \ Current\ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:max#cc0000:Supported";
  $options .= " GPRINT:max:MAX:\ %6.0lf\\\\n";
  $options .= " LINE1.25:alloc#006600:Allocated";
  $options .= " GPRINT:alloc:LAST:\ %6.0lf";
  $options .= " GPRINT:alloc:AVERAGE:\ \ \ %6.0lf";
  $options .= " GPRINT:alloc:MAX:\ \ \ %6.0lf\\\\n";
  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_netscreen_cpu ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $period = $to - $from;
  $options = "-l 0 -Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:av=$database:average:AVERAGE";
  $options .= " DEF:5m=$database:5min:AVERAGE";
  $options .= " COMMENT:Usage\ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:5m#ffcccc";
  $options .= " LINE1.25:5m#aa2000:5min";
  $options .= " GPRINT:5m:LAST:\ \ \ \ \ %5.2lf%%";
  $options .= " GPRINT:5m:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:5m:MAX:\ \ \ %5.2lf%%\\\\n";
  $options .= " LINE1.25:av#2020c0:Average";
  $options .= " GPRINT:av:LAST:\ \ %5.2lf%%";
  $options .= " GPRINT:av:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:av:MAX:\ \ \ %5.2lf%%\\\\n";
  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_netscreen_memory ($rrd, $graph, $from, $to, $width, $height, $title=NULL, $vertical=NULL) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $options = "-l 0 -Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:alloc=$database:allocate:AVERAGE";
  $options .= " DEF:left=$database:left:AVERAGE";
  $options .= " DEF:frag=$database:frag:AVERAGE";
  $options .= " CDEF:total=alloc,left,+";
  $options .= " COMMENT:Memory\ \ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:alloc#ffcccc";
  $options .= " LINE1.25:alloc#cc0000:Allocated";
  $options .= " GPRINT:alloc:MAX:%6.2lf%sB";
  $options .= " GPRINT:alloc:AVERAGE:\ %6.2lf%sB";
  $options .= " GPRINT:alloc:MAX:\ %6.2lf%sB\\\\n";
  $options .= " LINE1.25:left#006600:Left";
  $options .= " GPRINT:left:LAST:\ \ \ \ \ %6.2lf%sB";
  $options .= " GPRINT:left:AVERAGE:\ %6.2lf%sB";
  $options .= " GPRINT:left:MAX:\ %6.2lf%sB\\\\n";
  $options .= " LINE1.25:frag#666600:Fragments";
  $options .= " GPRINT:frag:LAST:%6.2lf%sB";
  $options .= " GPRINT:frag:AVERAGE:\ %6.2lf%sB";
  $options .= " GPRINT:frag:MAX:\ %6.2lf%sB\\\\n";

  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


?>
