<?php

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
use LibreNMS\OS\Cdata;

$onuNames = $os->onuNames();

$state = function (string $oid, string $index, string $type, string $descr, $value, string $group, array $translations) {
    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => $type,
        'sensor_descr' => $descr,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $value,
        'group' => $group,
    ]))->withStateTranslations($type, $translations);
};

// ONU registration state
foreach (SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::onuOperationStatus')->table(1) as $index => $data) {
    $value = $data['NSCRTV-FTTX-GPON-MIB::onuOperationStatus'] ?? null;
    if (is_numeric($value)) {
        $state('.1.3.6.1.4.1.17409.2.8.4.1.1.7.' . $index, $index, 'cdataOnuOperationStatus', ($onuNames[$index] ?? "onu $index") . ' Status', $value, 'ONU', [
            StateTranslation::define('Up', 1, Severity::Ok),
            StateTranslation::define('Down', 2, Severity::Error),
        ]);
    }
}

// why the ONU last went offline. The firmware returns a string, the poller maps it back through the state descriptions.
$offlineReasons = [
    'none' => [0, Severity::Ok],
    'losi' => [1, Severity::Warning],
    'lofi' => [2, Severity::Warning],
    'dying-gasp' => [3, Severity::Warning],
    'dyinggasp' => [4, Severity::Warning],
    'power-off' => [5, Severity::Warning],
    'deactive' => [6, Severity::Ok],
    'deactivate' => [7, Severity::Ok],
    'reboot' => [8, Severity::Ok],
    'reset' => [9, Severity::Ok],
    'unknown' => [10, Severity::Unknown],
];
foreach ($os->onuRegInfo() as $index => $row) {
    $reason = strtolower(trim((string) ($row[Cdata::ONU_OFFLINE_REASON] ?? '')));
    if ($reason === '') {
        continue;
    }
    $state(Cdata::ONU_REG_TABLE . '.' . Cdata::ONU_OFFLINE_REASON . ".$index", $index, 'cdataOnuOfflineReason', ($onuNames[$index] ?? "onu $index") . ' Last Offline Reason', $offlineReasons[$reason][0] ?? 10, 'ONU',
        array_map(fn ($name, $def) => StateTranslation::define($name, $def[0], $def[1]), array_keys($offlineReasons), $offlineReasons));
}

// ONU user side ethernet ports, tells "ONU up but customer cable unplugged" apart from "ONU down"
foreach (SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::ethOperationStatus')->table(3) as $onu => $slots) {
    foreach ($slots as $slot => $ports) {
        foreach ($ports as $port => $data) {
            $value = $data['NSCRTV-FTTX-GPON-MIB::ethOperationStatus'] ?? null;
            if (is_numeric($value)) {
                $state(".1.3.6.1.4.1.17409.2.8.5.1.1.5.$onu.$slot.$port", "$onu.$slot.$port", 'cdataOnuEthPortStatus', ($onuNames[$onu] ?? "onu $onu") . " eth $port Link", $value, 'ONU', [
                    StateTranslation::define('Up', 1, Severity::Ok),
                    StateTranslation::define('Down', 2, Severity::Warning),
                ]);
            }
        }
    }
}

// power cards
foreach ($os->powerCards() as $index => $card) {
    [, $slot] = explode('.', $index);
    $name = 'Power ' . $slot;

    if (is_numeric($card[Cdata::POWER_OPER_STATUS] ?? null)) {
        $state(Cdata::POWER_TABLE . '.' . Cdata::POWER_OPER_STATUS . ".$index", $index, 'cdataPowerCardOperationStatus', "$name Status", $card[Cdata::POWER_OPER_STATUS], 'Power', [
            StateTranslation::define('Up', 1, Severity::Ok),
            StateTranslation::define('Down', 2, Severity::Error),
            StateTranslation::define('Testing', 3, Severity::Warning),
        ]);
    }
    if (is_numeric($card[Cdata::POWER_PRESENCE] ?? null)) {
        $state(Cdata::POWER_TABLE . '.' . Cdata::POWER_PRESENCE . ".$index", $index, 'cdataPowerCardPresenceStatus', "$name Presence", $card[Cdata::POWER_PRESENCE], 'Power', [
            StateTranslation::define('Installed', 1, Severity::Ok),
            StateTranslation::define('Not installed', 2, Severity::Warning),
            StateTranslation::define('Other', 3, Severity::Unknown),
        ]);
    }
    if (is_numeric($card[Cdata::POWER_REDUNDANCY] ?? null)) {
        $state(Cdata::POWER_TABLE . '.' . Cdata::POWER_REDUNDANCY . ".$index", $index, 'cdataPowerCardRedundancyStatus', "$name Redundancy", $card[Cdata::POWER_REDUNDANCY], 'Power', [
            StateTranslation::define('Active', 1, Severity::Ok),
            StateTranslation::define('Standby', 2, Severity::Ok),
            StateTranslation::define('Standalone', 3, Severity::Warning),
            StateTranslation::define('Load sharing', 4, Severity::Ok),
        ]);
    }
}

unset($onuNames, $state, $offlineReasons, $index, $row, $data, $value, $reason, $onu, $slots, $slot, $ports, $port, $card, $name);
