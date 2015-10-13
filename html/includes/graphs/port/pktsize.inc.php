<?php

// How to compute the average packet size :
// - Get all octets we got
// - Get all numbers of packets : unicat, non-unicast packets
// Result wille be : octets * 8 / (nb of packets)

$custom_defs  = ' DEF:in_octets=' .$rrd_filename.':INOCTETS:AVERAGE';
$custom_defs .= ' DEF:out_octets='.$rrd_filename.':OUTOCTETS:AVERAGE';
$custom_defs .= ' DEF:in_nucast=' .$rrd_filename.':INNUCASTPKTS:AVERAGE';
$custom_defs .= ' DEF:out_nucast='.$rrd_filename.':OUTNUCASTPKTS:AVERAGE';
$custom_defs .= ' DEF:in_ucast='  .$rrd_filename.':INUCASTPKTS:AVERAGE';
$custom_defs .= ' DEF:out_ucast=' .$rrd_filename.':OUTUCASTPKTS:AVERAGE';
$custom_defs .= ' CDEF:in_bits=in_octets,8,*';
$custom_defs .= ' CDEF:out_bits=out_octets,8,*';
$custom_defs .= ' CDEF:in_pkts=in_ucast,in_nucast,+';
$custom_defs .= ' CDEF:out_pkts=out_ucast,out_nucast,+';

$custom_defs .= ' CDEF:in=in_octets,in_pkts,/';
$custom_defs .= ' CDEF:out=out_octets,out_pkts,/';

$custom_defs .= ' CDEF:in_max=in';
$custom_defs .= ' CDEF:out_max=out';

$colour_area_in  = 'AA66AA';
$colour_line_in  = '330033';
$colour_area_out = 'FFDD88';
$colour_line_out = 'FF6600';

$colour_area_in_max  = 'cc88cc';
$colour_area_out_max = 'FFefaa';

$unit_text = 'Oct/Pkt';

$args['nototal'] = 1;
$print_total = 0;
$nototal = 1;

$graph_max = 1;

include('includes/graphs/generic_duplex.inc.php');

