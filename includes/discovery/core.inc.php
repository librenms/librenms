<?php

use LibreNMS\Config;
use LibreNMS\Modules\Core;
use LibreNMS\OS;

$snmpdata = snmp_get_multi_oid($device, ['sysName.0', 'sysObjectID.0', 'sysDescr.0'], '-OUQn', 'SNMPv2-MIB');

$deviceModel = DeviceCache::getPrimary();
$deviceModel->fill([
    'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2.0'] ?? null,
    'sysName' => strtolower(trim($snmpdata['.1.3.6.1.2.1.1.5.0'] ?? '')),
    'sysDescr' => isset($snmpdata['.1.3.6.1.2.1.1.1.0']) ? str_replace(chr(218), "\n", $snmpdata['.1.3.6.1.2.1.1.1.0']) : null,
]);

foreach ($deviceModel->getDirty() as $attribute => $value) {
    Log::event($value . ' -> ' . $deviceModel->$attribute, $deviceModel, 'system', 3);
    $device[$attribute] = $value; // update device array
}

// detect OS
$deviceModel->os = Core::detectOS($device, false);

if ($deviceModel->isDirty('os')) {
    Log::event('Device OS changed: ' . $deviceModel->getOriginal('os') . ' -> ' . $deviceModel->os, $deviceModel, 'system', 3);
    $device['os'] = $deviceModel->os;

    echo 'Changed ';
}

$deviceModel->save();
load_os($device);
load_discovery($device);
$os = OS::make($device);

echo 'OS: ' . Config::getOsSetting($device['os'], 'text') . " ({$device['os']})\n\n";

unset($snmpdata, $attribute, $value, $deviceModel);
