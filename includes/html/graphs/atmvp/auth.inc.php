<?php

if ($_GET['id'] && is_numeric($_GET['id'])) {
    $atm_vp_id = $_GET['id'];
}

$vp = dbFetchRow('SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D WHERE J.juniAtmVp_id = ? AND I.port_id = J.port_id AND I.device_id = D.device_id', array($atm_vp_id));

if ($auth || port_permitted($vp['port_id'])) {
    $port         = cleanPort($vp);
    $device       = device_by_id_cache($port['device_id']);
    $title        = generate_device_link($device);
    $title       .= ' :: Port  '.generate_port_link($port);
    $title       .= ' :: VP '.$vp['vp_id'];
    $auth         = true;
    $rrd_filename = rrd_name($vp['hostname'], array('vp', $vp['ifIndex'], $vp['vp_id']));
}
