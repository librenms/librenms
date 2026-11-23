<?php

use App\Models\RadiusServer;

// RADIUS responses per minute of a server (vars id), or of all servers of the device
$serverId = $vars['id'] ?? null;
$servers = RadiusServer::where('device_id', $device['device_id'])
    ->when($serverId !== null, fn ($query) => $query->where('id', $serverId))
    ->get();
$series = $servers->contains(fn (RadiusServer $server) => $server->accepts !== null)
    ? ['accepts' => 'Accepts', 'rejects' => 'Rejects', 'timeouts' => 'Timeouts']
    : ['requests' => 'Requests', 'timeouts' => 'Timeouts']; // CISCO-AAA-SERVER-MIB has no accepts and rejects

$rrd_list = [];
foreach ($servers as $server) {
    $rrd_filename = Rrd::name($device['hostname'], ['radius', "$server->address-$server->port"]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        foreach ($series as $ds => $descr) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr' => $servers->count() > 1 ? "$descr $server->address" : $descr,
                'ds' => $ds,
            ];
        }
    }
}

$unit_text = 'Per minute';
$units = '';
$total_units = '';
$colours = 'mixed';
$multiplier = 60;
$scale_min = '0';
$nototal = 1;
$descr_len = $servers->count() > 1 ? 24 : 12;

if ($rrd_list) {
    include 'includes/html/graphs/generic_multi_line.inc.php';
}
