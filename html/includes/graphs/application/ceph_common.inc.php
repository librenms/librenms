<?php

function ceph_rrd($gtype) {
    global $device;
    global $vars;
    global $config;

    if ($gtype == "osd") {
        $var = $vars['osd'];
    }
    else {
        $var = $vars['pool'];
    }

    $rrd = join('-', array('app', 'ceph', $vars['id'], $gtype, $var)).'.rrd';
    return join('/', array($config['rrd_dir'], $device['hostname'], $rrd));
}
