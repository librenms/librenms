<?php

function cpugraphHP ($rrd, $graph , $from, $to, $width, $height)
{
 global $config, $installdir;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";

    $optsa = array( "--start", $from, "--width", $width, "--height", $height, "--vertical-label", $vertical, "--alt-autoscale-max",
                 "-l 0",
                 "-E",
                 "--title", $title,
                 "DEF:load=$database:LOAD:AVERAGE",
                 "AREA:load#FAFDCE:",
                 "LINE1.25:load#dd8800:Load",
                 "GPRINT:load:LAST:Cur\:%3.2lf",
                 "GPRINT:load:AVERAGE:Avg\:%3.2lf",
                 "GPRINT:load:MIN:Min\:%3.2lf",
                 "GPRINT:load:MAX:Max\:%3.2lf\\n");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:".$config['mono_font']."",
                                      "--font", "AXIS:6:".$config['mono_font']."",
                                      "--font-render-mode", "normal");}
  $opts = array_merge($config['rrdgraph_defaults'], $$optsa, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
    #echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }
}

function memgraphHP ($rrd, $graph , $from, $to, $width, $height, $title, $vertical)
{
 global $config, $installdir;
    $database = "rrd/" . $rrd;
    $imgfile = "graphs/" . "$graph";
    $memrrd = $database;
    $opts = array("--start", "$from", "--alt-autoscale-max", "--width", "$width", "--height", "$height",
            "-l", "0", "-E", "-b", "1024",
             "DEF:TOTAL=$memrrd:TOTAL:AVERAGE",
             "DEF:FREE=$memrrd:FREE:AVERAGE",
             "DEF:USED=$memrrd:USED:AVERAGE",
             "AREA:USED#ee9900:Used",
             "AREA:FREE#FAFDCE:Free:STACK",
             "LINE1.5:TOTAL#cc0000:");

  if($width <= "300") {$optsb = array("--font", "LEGEND:7:".$config['mono_font']."",
                                      "--font", "AXIS:6:".$config['mono_font']."",
                                      "--font-render-mode", "normal");}

  $opts = array_merge($config['rrdgraph_defaults'], $$opts, $optsb);

  $ret = rrd_graph("$imgfile", $opts, count($opts));

  if( !is_array($ret) ) {
    $err = rrd_error();
    #echo "rrd_graph() ERROR: $err\n";
    return FALSE;
  } else {
    return $imgfile;
  }

}

