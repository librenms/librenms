<?php

include('common.inc.php');

$rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/netstats-icmp.rrd";

$rrd_options .= " DEF:sqIn=$rrd_filename:icmpInSrcQuenchs:AVERAGE";
$rrd_options .= " DEF:sqOut=$rrd_filename:icmpOutSrcQuenchs:AVERAGE";
$rrd_options .= " DEF:redirIn=$rrd_filename:icmpInRedirects:AVERAGE";
$rrd_options .= " DEF:redirOut=$rrd_filename:icmpOutRedirects:AVERAGE";
$rrd_options .= " DEF:maskIn=$rrd_filename:icmpInAddrMasks:AVERAGE";
$rrd_options .= " DEF:maskOut=$rrd_filename:icmpOutAddrMasks:AVERAGE";
$rrd_options .= " DEF:maskrepIn=$rrd_filename:icmpInAddrMaskReps:AVERAGE";
$rrd_options .= " DEF:maskrepOut=$rrd_filename:icmpOutAddrMaskReps:AVERAGE";
$rrd_options .= " CDEF:sqOutInv=sqOut,-1,*";
$rrd_options .= " CDEF:redirOutInv=redirOut,-1,*";
$rrd_options .= " CDEF:maskOutInv=maskOut,-1,*";
$rrd_options .= " CDEF:maskrepOutInv=maskrepOut,-1,*";
$rrd_options .= " AREA:sqIn#00ff00:'Source Quenches In '";
$rrd_options .= " GPRINT:sqIn:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:sqIn:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:sqIn:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:redirIn#0000ff:'Redirects In       '";
$rrd_options .= " GPRINT:redirIn:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:redirIn:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:redirIn:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:maskIn#ff00b4:'Mask Requests In   '";
$rrd_options .= " GPRINT:maskIn:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskIn:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskIn:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:maskrepIn#00ff72:'Mask Replies In    '";
$rrd_options .= " GPRINT:maskrepIn:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskrepIn:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskrepIn:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " COMMENT:'\\n'";
$rrd_options .= " AREA:sqOut#00ff00:'Source Quenches Out'";
$rrd_options .= " GPRINT:sqOut:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:sqOut:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:sqOut:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:redirOut#0000ff:'Redirects Out      '";
$rrd_options .= " GPRINT:redirOut:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:redirOut:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:redirOut:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:maskOut#ff00b4:'Mask Requests Out  '";
$rrd_options .= " GPRINT:maskOut:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskOut:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskOut:MAX:'Max \\: %8.2lf %s\\n'";
$rrd_options .= " STACK:maskrepOut#00ff72:'Mask Replies Out   '";
$rrd_options .= " GPRINT:maskrepOut:AVERAGE:'Avg \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskrepOut:MIN:'Min \\: %8.2lf %s'";
$rrd_options .= " GPRINT:maskrepOut:MAX:'Max \\: %8.2lf %s'";

?>
