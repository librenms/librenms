<?php

echo 'BTI800 dBm';

$multiplier = 1;
$divisor = 1;

foreach ($pre_cache['bti800'] as $index => $entry) {
    if ($entry['sfpInfoWigth'] != '0') {
        $oidRx = '.1.3.6.1.4.1.30005.1.7.100.1.2.6.3.1.8.' . $index;
        $oidTx = '.1.3.6.1.4.1.30005.1.7.100.1.2.6.3.1.7.' . $index;
        $currentRx = $entry['sfpDiagnosticRxPowerDbm'];
        $currentTx = $entry['sfpDiagnosticTxPowerDbm'];
        if ($currentRx != 0 || $currentTx != 0) {
            $entPhysicalIndex = $entry['sfpDiagnosticIndex'];
            $entPhysicalIndex_measured = 'ports';

            //Discover receive power level
            $descrRx = dbFetchCell('SELECT `ifName` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$entry['sfpDiagnosticIndex'], $device['device_id']]) . ' Rx Power';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRx,
                'sfpDiagnosticRxPowerDbm.' . $index,
                'bti800',
                $descrRx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentRx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );

            //Discover transmit power level
            $descrTx = dbFetchCell('SELECT `ifName` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$entry['sfpDiagnosticIndex'], $device['device_id']]) . ' Tx Power';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTx,
                'sfpDiagnosticTxPowerDbm.' . $index,
                'bti800',
                $descrTx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentTx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }
}

unset($entry);
