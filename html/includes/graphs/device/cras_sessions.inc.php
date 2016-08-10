<?php

$scale_min = '0';

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], 'cras_sessions');

$rrd_options .= " DEF:email=$rrd_filename:email:AVERAGE";
$rrd_options .= " DEF:ipsec=$rrd_filename:ipsec:AVERAGE";
$rrd_options .= " DEF:l2l=$rrd_filename:l2l:AVERAGE";
$rrd_options .= " DEF:lb=$rrd_filename:lb:AVERAGE";
$rrd_options .= " DEF:svc=$rrd_filename:svc:AVERAGE";
$rrd_options .= " DEF:webvpn=$rrd_filename:webvpn:AVERAGE";
$rrd_options .= ' CDEF:webvpn_only=webvpn,svc,-';

$rrd_options .= " COMMENT:'Sessions         Current    Average   Maximum\\n'";

$rrd_options .= " AREA:svc#aa0000:'SSLVPN Tunnels':STACK";
$rrd_options .= " GPRINT:svc:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:svc:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:svc:MAX:' %6.2lf%s\\\\n'";

$rrd_options .= " AREA:webvpn_only#999999:'Clientless VPN':STACK";
$rrd_options .= " GPRINT:webvpn_only:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:webvpn_only:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:webvpn_only:MAX:' %6.2lf%s\\\\n'";

$rrd_options .= " AREA:ipsec#00aa00:'IPSEC         ':STACK";
$rrd_options .= " GPRINT:ipsec:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:ipsec:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:ipsec:MAX:' %6.2lf%s\\\\n'";

$rrd_options .= " AREA:l2l#aaaa00:'Lan-to-Lan    ':STACK";
$rrd_options .= ' GPRINT:l2l:LAST:%6.2lf%s';
$rrd_options .= " GPRINT:l2l:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:l2l:MAX:' %6.2lf%s\\\\n'";

$rrd_options .= " AREA:email#0000aa:'Email         ':STACK";
$rrd_options .= ' GPRINT:email:LAST:%6.2lf%s';
$rrd_options .= " GPRINT:email:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:email:MAX:' %6.2lf%s\\\\n'";

$rrd_options .= " AREA:lb#aa00aa:'Load Balancer ':STACK";
$rrd_options .= ' GPRINT:lb:LAST:%6.2lf%s';
$rrd_options .= " GPRINT:lb:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:lb:MAX:' %6.2lf%s\\\\n'";
