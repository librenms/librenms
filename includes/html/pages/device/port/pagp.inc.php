<?php

use LibreNMS\Util\Url;

// FIXME functions!
if (! $graph_type) {
    $graph_type = 'pagp_bits';
}

$daily_traffic = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1d', 'width' => 215, 'height' => 100]));
$daily_url = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1d', 'width' => 500, 'height' => 150]));

$weekly_traffic = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1w', 'width' => 215, 'height' => 100]));
$weekly_url = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1w', 'width' => 500, 'height' => 150]));

$monthly_traffic = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1mo', 'width' => 215, 'height' => 100]));
$monthly_url = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1mo', 'width' => 500, 'height' => 150]));

$yearly_traffic = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1y', 'width' => 215, 'height' => 100]));
$yearly_url = e(route('graph', ['port' => $port['port_id'], 'type' => $graph_type, 'from' => '-1y', 'width' => 500, 'height' => 150]));

echo Url::overlibLink('#', "<img src='$daily_traffic' border=0>", "<img src='$daily_url'>") . ' ';
echo Url::overlibLink('#', "<img src='$weekly_traffic' border=0>", "<img src='$weekly_url'>") . ' ';
echo Url::overlibLink('#', "<img src='$monthly_traffic' border=0>", "<img src='$monthly_url'>") . ' ';
echo Url::overlibLink('#', "<img src='$yearly_traffic' border=0>", "<img src='$yearly_url'>");

foreach (dbFetchRows('SELECT * FROM `ports` WHERE `pagpGroupIfIndex` = ? and `device_id` = ?', [$port['ifIndex'], $device['device_id']]) as $member) {
    $member = cleanPort($member);
    echo "$br<i class='fa fa-anchor fa-lg icon-theme' aria-hidden='true'></i> <strong>" . generate_port_link($member) . ' (PAgP)</strong>';
    $br = '<br />';
}
