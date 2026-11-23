<?php

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
use LibreNMS\OS\Routeros;
use LibreNMS\Util\Mac;

/** @var Routeros $os */

/*
 * LTE modem states. RouterOS reports these as free-form strings, so they are normalized to numeric states
 * here and again at poll time (Routeros::pollLteState()).
 * PIN and registration status only exist on newer RouterOS (MIB 2025+) and may be left empty (seen on 7.23.5).
 */
foreach (SnmpQuery::cache()->walk(['MIKROTIK-MIB::mtxrLTEModemTable', 'MIKROTIK-MIB::mtxrInterfaceStatsName'])->table(1) as $ifIndex => $modem) {
    $name = $modem['MIKROTIK-MIB::mtxrInterfaceStatsName'] ?? "lte$ifIndex";

    // SIM presence is derived from the ICCID, which works on every RouterOS version. It is empty without a SIM.
    if (isset($modem['MIKROTIK-MIB::mtxrLTEModemUICC'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.16.1.1.13.' . $ifIndex,
            'sensor_index' => 'mtxrLTEModemUICC.' . $ifIndex,
            'sensor_type' => 'mtxrLTEModemUICC',
            'sensor_descr' => "$name: SIM card",
            'sensor_current' => Routeros::normalizeLteState('mtxrLTEModemUICC', $modem['MIKROTIK-MIB::mtxrLTEModemUICC']),
            'group' => 'LTE',
            'user_func' => Routeros::class . '::pollLteState',
            'entPhysicalIndex' => Routeros::lteEntityIndex($ifIndex, true),
        ]))->withStateTranslations('mtxrLTEModemUICC', [
            StateTranslation::define('not detected', 0, Severity::Error),
            StateTranslation::define('present', 1, Severity::Ok),
        ]);
    }

    if (trim($modem['MIKROTIK-MIB::mtxrLTEModemRAT'] ?? '') !== '') {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.16.1.1.14.' . $ifIndex,
            'sensor_index' => 'mtxrLTEModemRAT.' . $ifIndex,
            'sensor_type' => 'mtxrLTEModemRAT',
            'sensor_descr' => "$name: Access technology",
            'sensor_current' => Routeros::normalizeLteState('mtxrLTEModemRAT', $modem['MIKROTIK-MIB::mtxrLTEModemRAT']),
            'group' => 'LTE',
            'user_func' => Routeros::class . '::pollLteState',
            'entPhysicalIndex' => Routeros::lteEntityIndex($ifIndex),
        ]))->withStateTranslations('mtxrLTEModemRAT', [
            StateTranslation::define('unknown', 0, Severity::Unknown),
            StateTranslation::define('2G', 2, Severity::Ok),
            StateTranslation::define('3G', 3, Severity::Ok),
            StateTranslation::define('LTE', 4, Severity::Ok),
            StateTranslation::define('5G NSA', 5, Severity::Ok),
            StateTranslation::define('5G SA', 6, Severity::Ok),
        ]);
    }

    if (trim($modem['MIKROTIK-MIB::mtxrLTEModemRegStatus'] ?? '') !== '') {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.16.1.1.17.' . $ifIndex,
            'sensor_index' => 'mtxrLTEModemRegStatus.' . $ifIndex,
            'sensor_type' => 'mtxrLTEModemRegStatus',
            'sensor_descr' => "$name: Registration",
            'sensor_current' => Routeros::normalizeLteState('mtxrLTEModemRegStatus', $modem['MIKROTIK-MIB::mtxrLTEModemRegStatus']),
            'group' => 'LTE',
            'user_func' => Routeros::class . '::pollLteState',
            'entPhysicalIndex' => Routeros::lteEntityIndex($ifIndex),
        ]))->withStateTranslations('mtxrLTEModemRegStatus', [
            StateTranslation::define('unknown', 0, Severity::Unknown),
            StateTranslation::define('registered', 1, Severity::Ok),
            StateTranslation::define('roaming', 2, Severity::Ok),
            StateTranslation::define('searching', 3, Severity::Warning),
            StateTranslation::define('denied', 4, Severity::Error),
            StateTranslation::define('not registered', 5, Severity::Error),
        ]);
    }

    if (trim($modem['MIKROTIK-MIB::mtxrLTEModemPinStatus'] ?? '') !== '') {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.16.1.1.18.' . $ifIndex,
            'sensor_index' => 'mtxrLTEModemPinStatus.' . $ifIndex,
            'sensor_type' => 'mtxrLTEModemPinStatus',
            'sensor_descr' => "$name: SIM PIN status",
            'sensor_current' => Routeros::normalizeLteState('mtxrLTEModemPinStatus', $modem['MIKROTIK-MIB::mtxrLTEModemPinStatus']),
            'group' => 'LTE',
            'user_func' => Routeros::class . '::pollLteState',
            'entPhysicalIndex' => Routeros::lteEntityIndex($ifIndex),
        ]))->withStateTranslations('mtxrLTEModemPinStatus', [
            StateTranslation::define('SIM not inserted', 0, Severity::Error),
            StateTranslation::define('ok', 1, Severity::Ok),
            StateTranslation::define('PIN required', 2, Severity::Error),
            StateTranslation::define('PUK required', 3, Severity::Error),
            StateTranslation::define('unknown', 4, Severity::Unknown),
        ]);
    }
}

/*
 * RouterBOARD firmware (RouterBOOT) is not upgraded together with RouterOS,
 * it stays behind until "/system routerboard upgrade" and a reboot.
 * CHR and x86 have no RouterBOOT.
 */
$firmware = SnmpQuery::get(['MIKROTIK-MIB::mtxrFirmwareVersion.0', 'MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0'])->values();
$firmwareCurrent = $firmware['MIKROTIK-MIB::mtxrFirmwareVersion.0'] ?? '';
$firmwareUpgrade = $firmware['MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0'] ?? '';

if ($firmwareCurrent !== '' && $firmwareUpgrade !== '') {
    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.7.4.0',
        'sensor_index' => 'mtxrFirmwareUpgrade.0',
        'sensor_type' => 'mtxrFirmwareUpgrade',
        'sensor_descr' => 'RouterBOOT firmware',
        'sensor_current' => Routeros::firmwareState($firmwareCurrent, $firmwareUpgrade),
        'group' => 'System',
        'user_func' => Routeros::class . '::pollFirmwareState',
    ]))->withStateTranslations('mtxrFirmwareUpgrade', [
        StateTranslation::define('up to date', 0, Severity::Ok),
        StateTranslation::define('upgrade available', 1, Severity::Ok),
        StateTranslation::define('unknown', 2, Severity::Unknown),
    ]);
}

/*
 * RouterOS has no VRRP MIB, a VRRP interface is only running (ifOperStatus up) while it is master.
 * VRRP interfaces are found by their virtual MAC 00:00:5e:00:01:<vrid> (IPv4) or 00:00:5e:00:02:<vrid> (IPv6).
 * Only ports from the ports table are checked (it excludes PPPoE session interfaces), so concentrators
 * with many sessions are not walked again.
 */
$vrrpPorts = $os->getDevice()->ports()->where('deleted', 0)->get();

// ifPhysAddress is filled by the ports poller, fetch it for ports that were not polled yet (new device)
$unpolledPorts = $vrrpPorts->whereNull('ifPhysAddress');
if ($unpolledPorts->isNotEmpty()) {
    $macs = [];
    foreach ($unpolledPorts->chunk(30) as $chunk) {
        $macs += SnmpQuery::get($chunk->map(fn ($port) => 'IF-MIB::ifPhysAddress.' . $port->ifIndex)->values()->all())->values();
    }
    foreach ($unpolledPorts as $port) {
        $port->ifPhysAddress = Mac::parse($macs["IF-MIB::ifPhysAddress[$port->ifIndex]"] ?? null)->hex() ?: null;
    }
}

// IF-MIB::ifOperStatus values
$operStatus = ['up' => 1, 'down' => 2, 'testing' => 3, 'unknown' => 4, 'dormant' => 5, 'notPresent' => 6, 'lowerLayerDown' => 7];

foreach ($vrrpPorts as $port) {
    if (! preg_match('/^00005e000[12]/', Mac::parse($port->ifPhysAddress)->hex())) {
        continue;
    }

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => '.1.3.6.1.2.1.2.2.1.8.' . $port->ifIndex,
        'sensor_index' => 'vrrp.' . $port->ifIndex,
        'sensor_type' => 'routerosVrrpState',
        'sensor_descr' => 'VRRP ' . $port->ifDescr,
        'sensor_current' => $operStatus[$port->ifOperStatus?->value] ?? null,
        'group' => 'VRRP',
        'entPhysicalIndex' => $port->ifIndex,
        'entPhysicalIndex_measured' => 'ports',
    ]))->withStateTranslations('routerosVrrpState', [
        // master and backup are both normal states, which router should be master is configuration.
        // The other ifOperStatus values mean the VRRP interface can't run, e.g. its parent interface is down.
        StateTranslation::define('master', 1, Severity::Ok),
        StateTranslation::define('backup', 2, Severity::Ok),
        StateTranslation::define('testing', 3, Severity::Unknown),
        StateTranslation::define('unknown', 4, Severity::Unknown),
        StateTranslation::define('dormant', 5, Severity::Warning),
        StateTranslation::define('notPresent', 6, Severity::Warning),
        StateTranslation::define('lowerLayerDown', 7, Severity::Warning),
    ]);
}
