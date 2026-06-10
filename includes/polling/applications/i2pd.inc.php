<?php

/*

LibreNMS Application for I2PD
Poller
  Fetches data via SNMP EXTEND using remote agent i2pd-stats.py

@author     Kossusukka <kossusukka@kossulab.net>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\RRD\RrdDefinition;

$name = 'i2pd';
$JSONVER = '1';

try {
    $i2pd = json_app_get($device, $name, $JSONVER);
} catch (JsonAppExtendErroredException $e) {
    // Remote agent error, probably I2PC API error. Log and stop
    $err = 'ERROR(' . $e->getParsedJson()['error'] . '): ' . $e->getParsedJson()['errorString'];

    update_application($app, $err, []);
    Eventlog::log('App ' . $name . ' failed at remote agent! ' . $err, $device['device_id'], 'application', Severity::Error);

    return;
} catch (JsonAppException $e) {
    // Unhandled exception. Log and stop
    $err = 'ERROR(' . $e->getCode() . '): ' . $e->getMessage();

    update_application($app, $err, []);
    Eventlog::log('App ' . $name . ' failed for JsonAppException! ' . $err, $device['device_id'], 'application', Severity::Error);

    return;
}

$rrd_def = RrdDefinition::make()
    ->addDataset('uptime', 'GAUGE', 0)
    ->addDataset('bw_in_1s', 'GAUGE', 0)
    ->addDataset('bw_in_15s', 'GAUGE', 0)
    ->addDataset('bw_out_1s', 'GAUGE', 0)
    ->addDataset('bw_out_15s', 'GAUGE', 0)
    ->addDataset('net_status', 'GAUGE', 0)
    ->addDataset('tunnels_participating', 'GAUGE', 0)
    ->addDataset('tunnels_successrate', 'GAUGE', 0)
    ->addDataset('active_peers', 'GAUGE', 0)
    ->addDataset('known_peers', 'GAUGE', 0)
    ->addDataset('total_rx_bytes', 'DERIVE', 0)
    ->addDataset('total_tx_bytes', 'DERIVE', 0);

$fields = [
    'uptime' => $i2pd['data']['i2p.router.uptime'],
    'bw_in_1s' => $i2pd['data']['i2p.router.net.bw.inbound.1s'],
    'bw_in_15s' => $i2pd['data']['i2p.router.net.bw.inbound.15s'],
    'bw_out_1s' => $i2pd['data']['i2p.router.net.bw.outbound.1s'],
    'bw_out_15s' => $i2pd['data']['i2p.router.net.bw.outbound.15s'],
    'net_status' => $i2pd['data']['i2p.router.net.status'],
    'tunnels_participating' => $i2pd['data']['i2p.router.net.tunnels.participating'],
    'tunnels_successrate' => $i2pd['data']['i2p.router.net.tunnels.successrate'],
    'active_peers' => $i2pd['data']['i2p.router.netdb.activepeers'],
    'known_peers' => $i2pd['data']['i2p.router.netdb.knownpeers'],
    'total_rx_bytes' => $i2pd['data']['i2p.router.net.total.received.bytes'],
    'total_tx_bytes' => $i2pd['data']['i2p.router.net.total.sent.bytes'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];

$net_status_codes = [
    '0' => 'OK',
    '1' => 'TESTING',
    '2' => 'FIREWALLED',
    '3' => 'HIDDEN',
    '4' => 'WARN_FIREWALLED_AND_FAST',
    '5' => 'WARN_FIREWALLED_AND_FLOODFILL',
    '6' => 'WARN_FIREWALLED_WITH_INBOUND_TCP',
    '7' => 'WARN_FIREWALLED_WITH_UDP_DISABLED',
    '8' => 'ERROR_I2CP',
    '9' => 'ERROR_CLOCK_SKEW',
    '10' => 'ERROR_PRIVATE_TCP_ADDRESS',
    '11' => 'ERROR_SYMMETRIC_NAT',
    '12' => 'ERROR_UDP_PORT_IN_USE',
    '13' => 'ERROR_NO_ACTIVE_PEERS_CHECK_CONNECTION_AND_FIREWALL',
    '14' => 'ERROR_UDP_DISABLED_AND_TCP_UNSET',
];
$net_status_resp = $i2pd['data']['i2p.router.net.status'] ?? null;
app('Datastore')->put($device, 'app', $tags, $fields);

if (is_numeric($net_status_resp) && array_key_exists($net_status_resp, $net_status_codes)) {
    // Save network status for health monitoring
    $app->data = ['net_stat_code' => $net_status_resp,
        'net_stat_msg' => $net_status_codes[$net_status_resp]];

    $resp = $net_status_resp >= 8 ? 'ERROR' : 'OK'; // Error8 is first fatal error, errors 2-7 are only degraded

    update_application($app, $resp, $fields, $net_status_codes[$net_status_resp]);
} else {
    $app->data = ['net_stat_code' => null,
        'net_stat_msg' => null];

    update_application($app, 'ERROR', $fields, 'No data received.');
}
