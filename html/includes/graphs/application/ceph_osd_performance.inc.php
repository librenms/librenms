<?php

$ds_in  = 'apply_ms';
$ds_out = 'commit_ms';

$in_text = 'Apply';
$out_text = 'Commit';

$rrd = join('-', array('app', 'ceph', $vars['id'], 'osd', $vars['osd'])).'.rrd';

$ceph_osd_rrd = join('/', array($config['rrd_dir'], $device['hostname'], $rrd));

if (is_file($ceph_osd_rrd)) {
    $rrd_filename = $ceph_osd_rrd;
}

$colour_area_in  = 'FF3300';
$colour_line_in  = 'FF0000';
$colour_area_out = 'FF6633';
$colour_line_out = 'CC3300';

$colour_area_in_max  = 'FF6633';
$colour_area_out_max = 'FF9966';

$unit_text = 'Miliseconds';

require 'includes/graphs/generic_duplex.inc.php';
