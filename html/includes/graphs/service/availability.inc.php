<?php

$scale_min = '0';
$scale_max = '1';

require 'includes/graphs/common.inc.php';

$service_text = substr(str_pad($service['service_type'], 28), 0, 28);

$rrd_options .= " COMMENT:'                                Cur    Avail\\n'";
$rrd_options .= " DEF:status=$rrd_filename:status:AVERAGE";
$rrd_options .= ' CDEF:percent=status,100,*';
$rrd_options .= ' CDEF:down=status,1,LT,status,UNKN,IF';
$rrd_options .= ' CDEF:percentdown=down,100,*';
$rrd_options .= ' AREA:percent#CCFFCC';
$rrd_options .= ' AREA:percentdown#FFCCCC';
$rrd_options .= " LINE1.5:percent#009900:'".$service_text."'";
// Ugly hack :(
$rrd_options .= ' LINE1.5:percentdown#cc0000';
$rrd_options .= ' GPRINT:status:LAST:%3.0lf';
$rrd_options .= ' GPRINT:percent:AVERAGE:%3.5lf%%\l';
