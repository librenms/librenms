<?php

include("includes/graphs/common.inc.php");

$rrd_options .= " -l 0 -E ";

$rrdfilename  = $config['rrd_dir'] . "/".$device['hostname']."/ubnt-airfiber-mib.rrd";

if (file_exists($rrdfilename))
{
  $rrd_options .= " COMMENT:'Metres                     Now    Min     Max\\n'";
  $rrd_options .= " DEF:radioLinkDistM=".$rrdfilename.":radioLinkDistM:AVERAGE ";
  $rrd_options .= " LINE1:radioLinkDistM#CC0000:'Distance             ' ";
  $rrd_options .= " GPRINT:radioLinkDistM:LAST:%3.2lf%s ";
  $rrd_options .= " GPRINT:radioLinkDistM:MIN:%3.2lf%s ";
  $rrd_options .= " GPRINT:radioLinkDistM:MAX:%3.2lf%s\\\l ";
}

?>
