<?php

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'cras_sessions');

$rrd_options .= " DEF:email=$rrd_filename:email:AVERAGE";
$rrd_options .= " DEF:ipsec=$rrd_filename:ipsec:AVERAGE";
$rrd_options .= " DEF:l2l=$rrd_filename:l2l:AVERAGE";
$rrd_options .= " DEF:lb=$rrd_filename:lb:AVERAGE";
$rrd_options .= " DEF:svc=$rrd_filename:svc:AVERAGE";
$rrd_options .= " DEF:webvpn=$rrd_filename:webvpn:AVERAGE";

$rrd_options .= " COMMENT:'Sessions            Current    Average    Maximum\\n'";

$rrd_options .= " AREA:svc#1985A1:'SSL VPN Client':STACK";
$rrd_options .= " GPRINT:svc:LAST:'%8.2lf'";
$rrd_options .= " GPRINT:svc:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:svc:MAX:' %8.2lf\\\\n'";

$rrd_options .= " AREA:webvpn#4C5C68:'Clientless VPN':STACK";
$rrd_options .= " GPRINT:webvpn:LAST:'%8.2lf'";
$rrd_options .= " GPRINT:webvpn:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:webvpn:MAX:' %8.2lf\\\\n'";

$rrd_options .= " AREA:ipsec#46494C:'IPSEC         ':STACK";
$rrd_options .= " GPRINT:ipsec:LAST:'%8.2lf'";
$rrd_options .= " GPRINT:ipsec:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:ipsec:MAX:' %8.2lf\\\\n'";

$rrd_options .= " AREA:l2l#C5C3C6:'LAN-to-LAN    ':STACK";
$rrd_options .= ' GPRINT:l2l:LAST:%8.2lf';
$rrd_options .= " GPRINT:l2l:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:l2l:MAX:' %8.2lf\\\\n'";

$rrd_options .= " AREA:email#DCDCDD:'Email         ':STACK";
$rrd_options .= ' GPRINT:email:LAST:%8.2lf';
$rrd_options .= " GPRINT:email:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:email:MAX:' %8.2lf\\\\n'";

$rrd_options .= " AREA:lb#FFFFFF:'Load Balancer ':STACK";
$rrd_options .= ' GPRINT:lb:LAST:%8.2lf';
$rrd_options .= " GPRINT:lb:AVERAGE:' %8.2lf'";
$rrd_options .= " GPRINT:lb:MAX:' %8.2lf\\\\n'";

//  Total
$rrd_options .= " 'CDEF:TOTAL=email,ipsec,l2l,lb,svc,webvpn,+,+,+,+,+'";

$rrd_options .= " 'LINE1:TOTAL#000000FF:Total         '";

$rrd_options .= " 'GPRINT:TOTAL:LAST:%8.2lf'";
$rrd_options .= " 'GPRINT:TOTAL:AVERAGE: %8.2lf'";
$rrd_options .= " 'GPRINT:TOTAL:MAX: %8.2lf\\\\n'";
