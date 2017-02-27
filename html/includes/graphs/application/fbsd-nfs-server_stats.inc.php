<?php
$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Per Second';
$unitlen       = 10;
$bigdescrlen   = 10;
$smalldescrlen = 10;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Getattr',
            'ds'       => 'Getattr',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Setattr',
            'ds'       => 'Setattr',
            'colour'   => '28774F'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Lookup',
            'ds'       => 'Lookup',
            'colour'   => 'AA6C39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Readlink',
            'ds'       => 'Readlink',
            'colour'   => '88CC88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Read',
            'ds'       => 'Read',
            'colour'   => 'D46A6A'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Write',
            'ds'       => 'Write',
            'colour'   => 'FFD1AA'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Create',
            'ds'       => 'Create',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Remove',
            'ds'       => 'Remove',
            'colour'   => 'AA8839'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Rename',
            'ds'       => 'Rename',
            'colour'   => '28536C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Link',
            'ds'       => 'Link',
            'colour'   => '880000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Symlink',
            'ds'       => 'Symlink',
            'colour'   => 'FF0000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Mkdir',
            'ds'       => 'Mkdir',
            'colour'   => '285300'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Rmdir',
            'ds'       => 'Rmdir',
            'colour'   => '2800FF'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Readdir',
            'ds'       => 'Readdir',
            'colour'   => '000080'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'RdirPlus',
            'ds'       => 'RdirPlus',
            'colour'   => '500000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Access',
            'ds'       => 'Access',
            'colour'   => 'AA6511'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Mknod',
            'ds'       => 'Mknod',
            'colour'   => '98139C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Fsstat',
            'ds'       => 'Fsstat',
            'colour'   => 'D853DC'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Fsinfo',
            'ds'       => 'Fsinfo',
            'colour'   => 'D853DC'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'PathConf',
            'ds'       => 'PathConf',
            'colour'   => 'F8F36C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Commit',
            'ds'       => 'Commit',
            'colour'   => 'FF536C'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
