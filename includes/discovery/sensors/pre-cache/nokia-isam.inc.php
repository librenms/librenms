<?php

echo 'ISAM Slot/Port Mapping ';
// Slot mapping
// FIXME Hardcoded to Num Based Slot Numbering. This should be pulled from SNMP
$slotTable = [
    '4352' => 'acu:1/1/',
    '4353' => 'nt-a:',
    '4354' => 'nt-b:',
    '4355' => 'lt:1/1/1/',
    '4356' => 'lt:1/1/2/',
    '4357' => 'lt:1/1/3/',
    '4358' => 'lt:1/1/4/',
    '4359' => 'lt:1/1/5/',
    '4360' => 'lt:1/1/6/',
    '4361' => 'lt:1/1/7/',
    '4362' => 'lt:1/1/8/',
    '4481' => '4481', // FIXME define this
];
$pre_cache['nokiaIsamSlot'] = snmpwalk_cache_multi_oid($device, 'eqptBoardContainerOffset', [], 'ASAM-EQUIP-MIB', 'nokia');
$pre_cache['nokiaIsamSlot'] = snmpwalk_cache_multi_oid($device, 'eqptBoardIfSlotId', $pre_cache['nokiaIsamSlot'], 'ASAM-EQUIP-MIB', 'nokia');
foreach ($pre_cache['nokiaIsamSlot'] as $slotId => $slot) {
    $pre_cache['nokiaIsamSlot'][$slotId]['numBasedSlot'] = $slotTable[$slotId];
}

// Port mapping
// FIXME Hardcoded Port Numbering for FANT-F NT Card.
$portTable = [
    '257' => 'xfp:1',
    '258' => 'xfp:2',
    '259' => 'xfp:3',
    '260' => 'xfp:4',
];

// dbm pre cache
$pre_cache['nokiaIsamSfpPort'] = snmpwalk_cache_twopart_oid($device, 'sfpDiagAvailable', [], 'SFP-MIB', 'nokia');
foreach ($pre_cache['nokiaIsamSfpPort'] as $slotId => $slot) {
    foreach ($slot as $portId => $port) {
        if ($portTable[$portId]) {
            $pre_cache['nokiaIsamSfpPort'][$slotId][$portId]['numBasedPort'] = $portTable[$portId];
        } else {
            $pre_cache['nokiaIsamSfpPort'][$slotId][$portId]['numBasedPort'] = $portId;
        }
        $oId = '.' . $slotId . '.' . $portId;
        $oIds = [
            'sfpDiagRxPower' . $oId,
            'sfpDiagTxPower' . $oId,
            'sfpDiagRSSIRxPowerAlmLow' . $oId,
            'sfpDiagRSSIRxPowerAlmHigh' . $oId,
            'sfpDiagRSSIRxPowerWarnLow' . $oId,
            'sfpDiagRSSIRxPowerWarnHigh' . $oId,
            'sfpDiagRSSITxPowerAlmLow' . $oId,
            'sfpDiagRSSITxPowerAlmHigh' . $oId,
            'sfpDiagRSSITxPowerWarnLow' . $oId,
            'sfpDiagRSSITxPowerWarnHigh' . $oId,
        ];
        if ($port['sfpDiagAvailable'] == 'noError') {
            $twopart_value = snmp_get_multi($device, $oIds, '-OQUs', 'SFP-MIB', 'nokia', []);
            foreach ($twopart_value[$slotId . '.' . $portId] as $index => $value) {
                $value = str_replace(' dBm', '', $value);
                if (is_numeric($value)) {
                    $pre_cache['nokiaIsamSfpPort'][$slotId][$portId][$index] = $value;
                }
            }
            unset($twopart_value);
        }
    }
}
