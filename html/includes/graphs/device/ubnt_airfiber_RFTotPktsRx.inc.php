<?php

include("includes/graphs/common.inc.php");

//$rrd_options .= " -l 0 -E ";

$rrdfilename  = $config['rrd_dir'] . "/".$device['hostname']."/ubnt-airfiber-mib.rrd";

if (file_exists($rrdfilename))
{
  $rrd_options .= " COMMENT:'Packets                Now      Min     Max\\n'";
  $rrd_options .= " DEF:rxpktsAll=".$rrdfilename.":rxpktsAll:AVERAGE ";
  $rrd_options .= " LINE1:rxpktsAll#CC0000:'Rx Packets     ' ";
  $rrd_options .= " GPRINT:rxpktsAll:LAST:%0.2lf%s ";
  $rrd_options .= " GPRINT:rxpktsAll:MIN:%0.2lf%s ";
  $rrd_options .= " GPRINT:rxpktsAll:MAX:%0.2lf%s\\\l ";
}

