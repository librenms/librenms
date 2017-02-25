<?php
require 'includes/graphs/common.inc.php';
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Per Second';
$unitlen       = 10;
$bigdescrlen   = 12;
$smalldescrlen = 12;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Attr Hits',
            'ds'       => 'AttrHits',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Attr Misses',
            'ds'       => 'AttrMisses',
            'colour'   => '28774F'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Lkup Hits',
            'ds'       => 'LkupHits',
            'colour'   => 'AA6C39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Lkup Misses',
            'ds'       => 'LkupMisses',
            'colour'   => '88CC88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioR Hits',
            'ds'       => 'BioRHits',
            'colour'   => 'D46A6A'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioR Misses',
            'ds'       => 'BioRMisses',
            'colour'   => 'FFD1AA'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioW Hits',
            'ds'       => 'BioWHits',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioW Misses',
            'ds'       => 'BioWMisses',
            'colour'   => 'AA8839'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioRL Hits',
            'ds'       => 'BioRLHits',
            'colour'   => '28536C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioRL Misses',
            'ds'       => 'BioRLMisses',
            'colour'   => '880000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioD Hits',
            'ds'       => 'BioDHits',
            'colour'   => 'FF0000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'BioD Misses',
            'ds'       => 'BioDMisses',
            'colour'   => '285300'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'DirE Hits',
            'ds'       => 'DirEHits',
            'colour'   => '2800FF'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'DirE Misses',
            'ds'       => 'DirEMisses',
            'colour'   => '000080'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Accs Hits',
            'ds'       => 'AccsHits',
            'colour'   => '500000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Accs Misses',
            'ds'       => 'AccsMisses',
            'colour'   => 'AA6511'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
