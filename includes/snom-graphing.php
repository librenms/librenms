<?php

function callsgraphSNOM ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $rrdtool, $installdir, $mono_font;
  $database = "rrd/" . $rrd;
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
  if($width <= "300") {$optsb = array("--font", "LEGEND:7:$installdir/DejaVuSansMono.ttf",
                                      "--font", "AXIS:6:$installdir/DejaVuSansMono.ttf",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}



?>
