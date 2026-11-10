<?php

use App\Models\Port;

$atm_vp_id = $vars['id'] ?? 0;

$vp = dbFetchRow('SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D WHERE J.juniAtmVp_id = ? AND I.port_id = J.port_id AND I.device_id = D.device_id', [$atm_vp_id]);

if ($auth || port_permitted($vp['port_id'])) {
    $label = Port::find($vp['port_id'] ?? null)?->getLabel() ?? ($vp['ifDescr'] ?? '');
    $device = device_by_id_cache($vp['device_id']);
    $title = ' :: Port ' . e($label) . ' :: VP ' . $vp['vp_id'];
    $auth = true;
    $rrd_filename = Rrd::name($vp['hostname'], ['vp', $vp['ifIndex'], $vp['vp_id']]);
}
