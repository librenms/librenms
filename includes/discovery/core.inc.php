<?php

use LibreNMS\Config;
use LibreNMS\OS;

$snmpdata = snmp_get_multi_oid($device, ['sysName.0', 'sysObjectID.0', 'sysDescr.0'], '-OUQn', 'SNMPv2-MIB');

DeviceCache::getPrimary()->fill([
    'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2.0'],
    'sysName' => strtolower(trim($snmpdata['.1.3.6.1.2.1.1.5.0'])),
    'sysDescr' => $snmpdata['.1.3.6.1.2.1.1.1.0'],
]);

foreach (DeviceCache::getPrimary()->getDirty() as $attribute) {
    Log::event($attribute . ' -> ' . DeviceCache::getPrimary()->$attribute, DeviceCache::getPrimary(), 'system', 3);
    $device[$attribute] = DeviceCache::getPrimary()->$attribute; // update device array
}

// detect OS
DeviceCache::getPrimary()->os = getHostOS($device, false);

if (DeviceCache::getPrimary()->isDirty('os')) {
    Log::event('Device OS changed: ' . DeviceCache::getPrimary()->getOriginal('os') . ' -> ' . DeviceCache::getPrimary()->os , DeviceCache::getPrimary(), 'system', 3);
    $device['os'] = DeviceCache::getPrimary()->os;

    echo "Changed ";
}

DeviceCache::getPrimary()->save();
load_os($device);
load_discovery($device);
$os = OS::make($device);

echo "OS: " . Config::getOsSetting($device['os'], 'text') . " ({$device['os']})\n\n";

register_mibs($device, Config::getOsSetting($device['os'], 'register_mibs', []), 'includes/discovery/os/' . $device['os'] . '.inc.php');

unset($snmpdata, $attribute);
