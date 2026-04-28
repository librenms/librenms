<?php
/**
 * LibreNMS state sensor discovery for Hyperion switch
 *
 * Sensor: MIB-MODIO - Digital Inputs (wejścia cyfrowe)
 *   - dinAvailability: YES = odkryj sensor, NO = pomiń
 *   - dinState: LOW(0) = styk zamknięty, HIGH(1) = styk otwarty
 *   - dinName: opis wejścia, np. "DigitalIn 1/1"
 *
 * MIB-MODIO zwraca wartości enum jako tekst (YES/NO/LOW/HIGH) niezależnie
 * od flagi snmpwalk - dlatego porównujemy stringi, nie liczby.
 *
 * Stany:
 *   - close (LOW)  → value: 0, generic: 3
 *   - open  (HIGH) → value: 1, generic: 0
 *
 * Plik umieścić w:
 *   includes/discovery/sensors/state/hyperion.inc.php
 */

// Pobierz całą tabelę - flaga -OQUs dla wszystkich (MIB zwraca enum jako tekst)
$modio = snmpwalk_cache_oid($device, 'dinName',         [],     'MIB-MODIO', null, '-OQUs');
$modio = snmpwalk_cache_oid($device, 'dinAvailability', $modio, 'MIB-MODIO', null, '-OQUs');
$modio = snmpwalk_cache_oid($device, 'dinState',        $modio, 'MIB-MODIO', null, '-OQUs');

if (empty($modio)) {
    return;
}

// -------------------------------------------------------------------------
// Mapa string → wartość numeryczna
// Urządzenie zwraca "YES"/"NO" i "LOW"/"HIGH" jako stringi niezależnie od flag snmpwalk
// -------------------------------------------------------------------------
$availability_map = [
    'YES' => 1,
    'NO'  => 0,
];

$state_map = [
    'LOW'  => 0,   // styk zamknięty
    'HIGH' => 1,   // styk otwarty
];

// -------------------------------------------------------------------------
// Definicja stanów dla LibreNMS
//   generic: 0=OK, 1=Warning, 2=Critical, 3=Unknown
// -------------------------------------------------------------------------
$state_name = 'hyperion_din_state';

$states = [
    ['value' => 0, 'generic' => 3, 'graph' => 1, 'descr' => 'close'],  // LOW  = zamknięty
    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'open'],   // HIGH = otwarty
];

create_state_index($state_name, $states);

// -------------------------------------------------------------------------
// Iteruj po wejściach
// -------------------------------------------------------------------------
foreach ($modio as $index => $entry) {

    // Sprawdź dostępność przez porównanie stringa
    $avail_raw = strtoupper(trim($entry['dinAvailability'] ?? ''));
    $available  = $availability_map[$avail_raw] ?? 0;

    if ($available !== 1) {
        continue;  // pomiń wejścia oznaczone jako NO
    }

    // Zamień string stanu na wartość numeryczną
    $state_raw = strtoupper(trim($entry['dinState'] ?? ''));
    if (!array_key_exists($state_raw, $state_map)) {
        continue;  // nieznana wartość - pomiń
    }
    $value = $state_map[$state_raw];

    // Opis: usuń ewentualne cudzysłowy pozostawione przez snmpwalk
    $descr = trim($entry['dinName'] ?? ('Digital Input ' . $index), '"\'');

    // Numeryczny OID dla dinState.N
    $oid_num      = '.1.3.6.1.4.1.19829.1.6.2.1.4.' . $index;
    $sensor_index = 'dinState.' . $index;

    discover_sensor(
        null,           // $pre_cache
        'state',        // $class
        $device,        // $device
        $oid_num,       // $oid – numeryczny, wymagany przez pollera
        $sensor_index,  // $index – unikalny klucz sensora
        $state_name,    // $type – musi pasować do create_state_index()
        $descr,         // $descr – np. "DigitalIn 1/1"
        1,              // $divisor
        1,              // $multiplier
        null,           // $low_limit
        null,           // $low_warn_limit
        null,           // $warn_limit
        null,           // $high_limit
        $value,         // $current – wartość numeryczna (0 lub 1)
        'snmp',         // $poller_type
        null,           // $entPhysicalIndex
        null,           // $entPhysicalIndex_measured
        null,           // $user_func
        'DigitalIn'     // $group
    );
}

// -------------------------------------------------------------------------
// Bitstream Hyperion - Power Supply State Discovery
// MIB: MIB-SYSUTIL
// -------------------------------------------------------------------------

$state_name = 'sysutilStatusPowerSupplyState';

$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'active'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'standby'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
    ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'],
];

create_state_index($state_name, $states);

$psu_state_map = [
    'active'     => 0,
    'standby'    => 1,
    'notPresent' => 2,
    'fault'      => 3,
];
echo "DEBUG: Starting PSU discovery\n";
$psu = snmpwalk_cache_oid($device, 'sysutilStatusPowerSupplyDescription', [],   'MIB-SYSUTIL', null, '-OQUs');
$psu = snmpwalk_cache_oid($device, 'sysutilStatusPowerSupplyState',       $psu, 'MIB-SYSUTIL', null, '-OQUs');
echo "DEBUG: PSU array count: " . count($psu) . "\n";
print_r($psu);

foreach ($psu as $index => $entry) {
    // $index = "1.1" lub "1.2"
    // $entry['sysutilStatusPowerSupplyState'] = "active" / "notPresent" itp.
    // $entry['sysutilStatusPowerSupplyDescription'] = "Main power supply" itp.

    $state_raw = trim($entry['sysutilStatusPowerSupplyState'] ?? '');
    $descr     = trim($entry['sysutilStatusPowerSupplyDescription'] ?? ('Power Supply ' . $index), '"\'');

    if (!isset($psu_state_map[$state_raw])) {
        continue;
    }

    $value   = $psu_state_map[$state_raw];
    $oid_num = '.1.3.6.1.4.1.19829.1.24.1.3.2.1.3.' . $index;

    discover_sensor(
        null,
        'state',
        $device,
        $oid_num,
        'psuState.' . $index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $value,
        'snmp',
        null,
        null,
        null,
        'Power Supply'
    );
}