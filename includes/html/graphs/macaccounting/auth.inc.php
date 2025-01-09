<?php

use App\Models\Port;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mac;

if (! is_numeric($vars['id'])) {
    throw new RrdGraphException('invalid id');
}

$acc = dbFetchRow('SELECT * FROM `mac_accounting` AS M, `ports` AS I, `devices` AS D WHERE M.ma_id = ? AND I.port_id = M.port_id AND I.device_id = D.device_id', [$vars['id']]);

if (Debug::isEnabled()) {
    echo '<pre>';
    print_r($acc);
    echo '</pre>';
}

if (! is_array($acc)) {
    throw new RrdGraphException('entry not found');
}

if (! $auth && ! port_permitted($acc['port_id'])) {
    throw new RrdGraphException('unauthenticated');
}

$filename = Rrd::name($acc['hostname'], ['cip', $acc['ifIndex'], $acc['mac']]);
Log::debug($filename);

if (! is_file($filename)) {
    throw new RrdGraphException('file not found');
}
Log::debug('exists');

$rrd_filename = $filename;
$port = cleanPort(Port::find($acc['port_id']));
$device = DeviceCache::get($port['device_id']);
$title = generate_device_link($device);
$title .= ' :: Port  ' . generate_port_link($port);
$title .= ' :: ' . Mac::parse($acc['mac'])->readable();
$auth = true;
