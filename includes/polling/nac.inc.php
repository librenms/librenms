<?php

use App\Models\Port;
use App\Models\PortsNac;
use LibreNMS\Util\IP;

echo "\nCisco-NAC\n";

// cache port ifIndex -> port_id map
$ports_map = Port::where('device_id', $device['device_id'])->pluck('port_id', 'ifIndex');
$port_nac_ids = [];

// collect data via snmp and reorganize the session method entry a bit
$portAuthSessionEntry = snmpwalk_cache_oid($device, 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB');
if (!empty($portAuthSessionEntry)) {
    $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($device, 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB'))->mapWithKeys(function ($item, $key) {
        $key_parts = explode('.', $key);
        $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method
        return [$key => ['PortSessionMethod' => $key_parts[2], 'PortAuthSessionAuthcStatus' => $item['cafSessionMethodState']]];
    });
}

// update the DB
foreach ($portAuthSessionEntry as $index => $PortAuthSessionEntryParameters) {
    $auth_id = trim(strstr($index, "'"), "'");
    $ifIndex = substr($index, 0, strpos($index, "."));
    $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);

    $port_nac = PortsNac::updateOrCreate([
        'auth_id' => $auth_id,
        'device_id' => $device['device_id'],
    ], [
        'port_id' => $ports_map->get($ifIndex),
        'PortAuthSessionMacAddress' => implode(':', array_map('zeropad', explode(':', $PortAuthSessionEntryParameters['cafSessionClientMacAddress']))),
        'PortAuthSessionIPAddress' => (string)IP::fromHexString($PortAuthSessionEntryParameters['cafSessionClientAddress'], true),
        'PortAuthSessionAuthzStatus' => $PortAuthSessionEntryParameters['cafSessionStatus'],
        'PortAuthSessionDomain' => $PortAuthSessionEntryParameters['cafSessionDomain'],
        'PortAuthSessionHostMode' => $PortAuthSessionEntryParameters['cafSessionAuthHostMode'],
        'PortAuthSessionUserName' => $PortAuthSessionEntryParameters['cafSessionAuthUserName'],
        'PortAuthSessionAuthzBy' => $PortAuthSessionEntryParameters['cafSessionAuthorizedBy'],
        'PortAuthSessionTimeOut' => $PortAuthSessionEntryParameters['cafSessionTimeout'],
        'PortAuthSessionTimeLeft' => $PortAuthSessionEntryParameters['cafSessionTimeLeft'],
        'PortAuthSessionAuthcStatus' => $session_info['PortAuthSessionAuthcStatus'],
        'PortSessionMethod' => $session_info['PortSessionMethod'],
    ]);
    if (!$port_nac->port_id || $port_nac->port->ifIndex != $ifIndex) {
        $port_nac->port()->associate(Port::where(['device_id' => $device['device_id'], 'ifIndex' => $ifIndex])->first());
        $port_nac->save();
    }

    // save valid ids
    $port_nac_ids[] = $port_nac->ports_nac_id;
}


// delete old entries
\LibreNMS\DB\Eloquent::DB()->table('ports_nac')->whereNotIn('ports_nac_id', $port_nac_ids)->delete();
//    \App\Models\PortsNac::whereNotIn('ports_nac_id', $port_nac_ids)->get()->each->delete(); // alternate delete to trigger model events


unset($port_nac_ids, $ports_map, $portAuthSessionEntry, $cafSessionMethodsInfoEntry, $port_nac);
