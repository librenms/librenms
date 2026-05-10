<?php

use App\Facades\DeviceCache;
use App\Facades\PortCache;
use LibreNMS\Util\Url;

$atm_vp_id = $vars['id'] ?? 0;

$vp = dbFetchRow('SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D WHERE J.juniAtmVp_id = ? AND I.port_id = J.port_id AND I.device_id = D.device_id', [$atm_vp_id]);

if ($auth || port_permitted($vp['port_id'])) {
    $port = PortCache::get($vp['port_id']);
    $device = DeviceCache::get($port->device_id);
    $title = Url::deviceLink($device);
    $title .= ' :: Port  ' . Url::portLink($port);
    $title .= ' :: VP ' . $vp['vp_id'];
    $auth = true;
    $rrd_filename = Rrd::name($vp['hostname'], ['vp', $vp['ifIndex'], $vp['vp_id']]);
}
