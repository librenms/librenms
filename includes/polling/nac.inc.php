<?php

use App\Models\Port;
use App\Models\PortsNac;
use LibreNMS\Util\DiscoveryModelObserver;
use LibreNMS\Util\IP;

echo "\nCisco-NAC\n";

// cache port ifIndex -> port_id map
$ports_map = Port::where('device_id', $device['device_id'])->pluck('port_id', 'ifIndex');
$port_nac_ids = [];

// discovery output (but don't install it twice (testing can can do this)
if (!PortsNac::getEventDispatcher()->hasListeners('eloquent.created: App\Models\PortsNac')) {
    PortsNac::observe(new DiscoveryModelObserver());
}

// collect data via snmp and reorganize the session method entry a bit
$portAuthSessionEntry = snmpwalk_cache_oid($device, 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB');
if (!empty($portAuthSessionEntry)) {
    $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($device, 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB'))->mapWithKeys(function ($item, $key) {
        $key_parts = explode('.', $key);
        $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method
        return [$key => ['method' => $key_parts[2], 'authc_status' => $item['cafSessionMethodState']]];
    });
}

// update the DB
foreach ($portAuthSessionEntry as $index => $portAuthSessionEntryParameters) {
    $auth_id = trim(strstr($index, "'"), "'");
    $ifIndex = substr($index, 0, strpos($index, "."));
    $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);

    $port_nac = PortsNac::updateOrCreate([
        'port_id' => $ports_map->get($ifIndex, 0),
        'domain' => $portAuthSessionEntryParameters['cafSessionDomain'],
    ], [
        'device_id' => $device['device_id'],
        'auth_id' => $auth_id,
        'mac_address' => strtoupper(implode(':', array_map('zeropad', explode(':', $portAuthSessionEntryParameters['cafSessionClientMacAddress'])))),
        'ip_address' => (string)IP::fromHexString($portAuthSessionEntryParameters['cafSessionClientAddress'], true),
        'authz_status' => $portAuthSessionEntryParameters['cafSessionStatus'],
        'host_mode' => $portAuthSessionEntryParameters['cafSessionAuthHostMode'],
        'username' => $portAuthSessionEntryParameters['cafSessionAuthUserName'],
        'authz_by' => $portAuthSessionEntryParameters['cafSessionAuthorizedBy'],
        'timeout' => $portAuthSessionEntryParameters['cafSessionTimeout'],
        'time_left' => $portAuthSessionEntryParameters['cafSessionTimeLeft'],
        'authc_status' => $session_info['authc_status'],
        'method' => $session_info['method'],
    ]);
    if (!$port_nac->port_id || $port_nac->port->ifIndex != $ifIndex) {
        $port_nac->port()->associate(Port::where(['device_id' => $device['device_id'], 'ifIndex' => $ifIndex])->first());
        $port_nac->save();
    }

    // save valid ids
    $port_nac_ids[] = $port_nac->ports_nac_id;
}


// delete old entries
$count = \LibreNMS\DB\Eloquent::DB()->table('ports_nac')->whereNotIn('ports_nac_id', $port_nac_ids)->delete();
d_echo('Deleted ' . $count, str_repeat('-', $count));
//    \App\Models\PortsNac::whereNotIn('ports_nac_id', $port_nac_ids)->get()->each->delete(); // alternate delete to trigger model events


unset($port_nac_ids, $ports_map, $portAuthSessionEntry, $cafSessionMethodsInfoEntry, $port_nac);
