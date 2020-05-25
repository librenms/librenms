<?php
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'per second';
$unitlen       = 10;
$bigdescrlen   = 10;
$smalldescrlen = 10;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'getattr',
            'ds'       => 'getattr',
            'colour'   => '582a72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'setattr',
            'ds'       => 'setattr',
            'colour'   => '28774f'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'lookup',
            'ds'       => 'lookup',
            'colour'   => 'aa6c39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'readlink',
            'ds'       => 'readlink',
            'colour'   => '88cc88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'read',
            'ds'       => 'read',
            'colour'   => 'd46a6a'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'write',
            'ds'       => 'write',
            'colour'   => 'ffd1aa'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'create',
            'ds'       => 'create',
            'colour'   => '582a72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'remove',
            'ds'       => 'remove',
            'colour'   => 'aa8839'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'rename',
            'ds'       => 'rename',
            'colour'   => '28536c'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'link',
            'ds'       => 'link',
            'colour'   => '880000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'symlink',
            'ds'       => 'symlink',
            'colour'   => 'ff0000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'mkdir',
            'ds'       => 'mkdir',
            'colour'   => '285300'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'rmdir',
            'ds'       => 'rmdir',
            'colour'   => '2800ff'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'readdir',
            'ds'       => 'readdir',
            'colour'   => '000080'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'rdirplus',
            'ds'       => 'rdirplus',
            'colour'   => '500000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'access',
            'ds'       => 'access',
            'colour'   => 'aa6511'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'mknod',
            'ds'       => 'mknod',
            'colour'   => '98139c'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'fsstat',
            'ds'       => 'fsstat',
            'colour'   => 'd853dc'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'fsinfo',
            'ds'       => 'fsinfo',
            'colour'   => 'd853dc'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'pathconf',
            'ds'       => 'pathconf',
            'colour'   => 'f8f36c'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'commit',
            'ds'       => 'commit',
            'colour'   => 'ff536c'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
