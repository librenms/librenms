<?php

function graph_fortigate_sessions ($rrd, $graph, $from, $to, $width, $height, $title=NULL, $vertical=NULL) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $options = "-Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:sessions=$database:sessions:AVERAGE";
  $options .= " COMMENT:Sessions\ \ \ \ \ \ Current\ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:sessions#006600:Allocated";
  $options .= " GPRINT:sessions:LAST:\ %6.0lf";
  $options .= " GPRINT:sessions:AVERAGE:\ \ \ %6.0lf";
  $options .= " GPRINT:sessions:MAX:\ \ \ %6.0lf\\\\n";
  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_fortigate_cpu ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $period = $to - $from;
  $options = "-l 0 -Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:cpu=$database:cpu:AVERAGE";
  $options .= " COMMENT:Usage\ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:cpu#2020c0:Average";
  $options .= " GPRINT:cpu:LAST:\ \ %5.2lf%%";
  $options .= " GPRINT:cpu:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:cpu:MAX:\ \ \ %5.2lf%%\\\\n";
  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

function graph_fortigate_memory ($rrd, $graph, $from, $to, $width, $height, $title=NULL, $vertical=NULL) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $options = "-l 0 -Y -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:mem=$database:mem:AVERAGE";
  $options .= " DEF:kcap=$database:memcapacity:AVERAGE";
  $options .= " CDEF:used=kcap,1024,*,100,/,mem,*";
  $options .= " CDEF:cap=kcap,1024,*";
  $options .= " COMMENT:Memory\ \ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:mem#ffcccc";
  $options .= " LINE1.25:used#cc0000:Used\ \ \ \ \ ";
  $options .= " GPRINT:used:MAX:%6.2lf%sB";
  $options .= " GPRINT:used:AVERAGE:\ %6.2lf%sB";
  $options .= " GPRINT:used:MAX:\ %6.2lf%sB\\\\n";
  $options .= " LINE1.25:cap#0000cc:Total\ \ \ ";
  $options .= " GPRINT:cap:MAX:\ %6.2lf%sB\\\\n";

  shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}


?>
