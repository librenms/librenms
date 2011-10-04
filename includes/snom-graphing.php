<?php

# FIXME not used, do we still need this?

function callsgraphSNOM ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config;

  $database = $config['rrd_dir'] . "/" . $rrd;
  $imgfile = "graphs/" . "$graph";
  $optsa = array( "--start", $from, "--end", $to, "--width", $width, "--height", $height, "--vertical-label", $vertical ,"--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "--title", $title,
                 "DEF:call=$database:CALLS:AVERAGE",
                 "CDEF:calls=call,360,*",
                 "LINE1.25:calls#FF9900:Calls",
                 "GPRINT:calls:LAST:Cu\: %2.0lf/min",
                 "GPRINT:calls:AVERAGE:Av\: %2.0lf/min",
                 "GPRINT:calls:MAX:Mx\: %2.0lf/min\\n");
  if ($width <= "300") {$optsb = array("--font", "LEGEND:7:".$config['mono_font']."",
                                      "--font", "AXIS:6:".$config['mono_font']."",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($config['rrdgraph_defaults'], $optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if ( !is_array($ret) ) {
    $err = rrd_error();
    echo("rrd_graph() ERROR: $err\n");
    return FALSE;
  } else {
    return $imgfile;
  }
}

?>
