<?php

require 'includes/graphs/common.inc.php';

$sla = dbFetchRow('SELECT `sla_nr` FROM `slas` WHERE `sla_id` = ?', array($vars['id']));

$rrd_options .= ' -l 0 -E ';
$rrd_filename = $config['rrd_dir']."/".$device['hostname']."/".safename('sla-'.$sla['sla_nr'].'.rrd');

if (file_exists($rrd_filename)) {
    $rrd_options .= " COMMENT:'Round Trip Time   Cur      Min     Max\\n'";
    $rrd_options .= " DEF:rtt=$rrd_filename:rtt:AVERAGE ";
    $rrd_options .= ' VDEF:avg=rtt,AVERAGE ';
    $rrd_options .= " LINE1:avg#CCCCFF:'Average':dashes";
    $rrd_options .= ' GPRINT:rtt:AVERAGE:%4.1lfms\\\l ';
    $rrd_options .= " LINE1:rtt#CC0000:'RTT'";
    $rrd_options .= ' GPRINT:rtt:LAST:%4.1lfms ';
    $rrd_options .= ' GPRINT:rtt:MIN:%4.1lfms ';
    $rrd_options .= ' GPRINT:rtt:MAX:%4.1lfms\\\l ';
}
