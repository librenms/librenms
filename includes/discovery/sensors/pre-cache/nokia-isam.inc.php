<?php

// Lookup the Isam Port Numbering Method
$asamPortNumberingMethod = snmp_getnext($device, 'asamPortNumberingMethodSystem', '-Ovq', 'ASAM-SYSTEM-MIB', 'nokia');
$eqptHolderActualType = snmp_get($device, 'eqptHolderActualType.17', '-Ovq', 'ASAM-EQUIP-MIB', 'nokia');
echo 'ISAM Slot/Port Mapping for ' . $eqptHolderActualType . '';

//Typ to prodcut name
$eqptHolderTable = [
    'NFXS-B' => 'Shelf: ISAM 7330',
    'NFXR-A' => 'Shelf: ISAM 7356',
    'NFXS-D' => 'Shelf: ISAM 7360 (FX-16)',
    'NFXS-E' => 'Shelf: ISAM 7360 (FX-8)',
    'NFXS-F' => 'Shelf: ISAM 7360 (FX-4)',
    'CFXR-A' => 'Shelf: ISAM 7362 (DF-16GW)',
    'CFXS-C' => 'Shelf: ISAM 7362 (SF-8GW)',
];

if ($asamPortNumberingMethod == 'positionBasedSlotId') {
    // Slot mapping for "positionBasedSlotId" (see ASAM-SYSTEM-MIB)
    // also hardcoded because of no reference found in MIB but matched on DSLAM typ (eqptHolderActualTyp)
    // acutal tested on ISAM 7330/56/60/62
    // Nokia IDs for ISAM 7330
    if ($eqptHolderActualType == 'NFXS-B') {
        $slotTable = [
            '4352' => 'ntio-1:',
            '4353' => 'nt-a:',
            '4354' => 'nt-b:',
            '4355' => 'lt:1/1/4:',
            '4356' => 'lt:1/1/5:',
            '4357' => 'lt:1/1/6:',
            '4358' => 'lt:1/1/7:',
            '4359' => 'lt:1/1/8:',
            '4360' => 'lt:1/1/9:',
            '4361' => 'lt:1/1/10:',
            '4362' => 'lt:1/1/11:',
            '4417' => 'vlt:1/1/63:',
            '4418' => 'vlt:1/1/64:',
            '12545' => 'ctrl:3/1:',
            '12547' => 'lt:3/1/1:',
            '12548' => 'lt:3/1/2:',
            '16641' => 'ctrl:4/1:',
            '16643' => 'lt:4/1/1:',
            '16644' => 'lt:4/1/2:',
            '20737' => 'ctrl:5/1:',
            '20739' => 'lt:5/1/1:',
            '20740' => 'lt:5/1/2:',
            '24833' => 'ctrl:6/1:',
            '24835' => 'lt:6/1/1:',
            '24836' => 'lt:6/1/2:',
            '28929' => 'ctrl:7/1:',
            '28931' => 'lt:7/1/1:',
            '28932' => 'lt:7/1/2:',
        ];
        // Slot IDs for Nokia ISAM 7356
    } elseif ($eqptHolderActualType == 'NFXR-A') {
        $slotTable = [
            '4353' => 'nt:',
            '4355' => 'lt:1/1/1:',
            '4356' => 'lt:1/1/2:',
            '12545' => 'ctrl:3/1:',
            '12547' => 'lt:3/1/1:',
            '12548' => 'lt:3/1/2:',
            '16641' => 'ctrl:4/1:',
            '16643' => 'lt:4/1/1:',
            '16644' => 'lt:4/1/2:',
            '20737' => 'ctrl:5/1:',
            '20739' => 'lt:5/1/1:',
            '20740' => 'lt:5/1/2:',
            '24833' => 'ctrl:6/1:',
            '24835' => 'lt:6/1/1:',
            '24836' => 'lt:6/1/2:',
            '28929' => 'ctrl:7/1:',
            '28931' => 'lt:7/1/1:',
            '28932' => 'lt:7/1/2:',
        ];
        // Slot IDs for Nokia ISAM 7360
    } elseif ($eqptHolderActualType == 'NFXS-F') {
        $slotTable = [
            '4352' => 'acu:1/1:',
            '4353' => 'nt-a:',
            '4354' => 'nt-b:',
            '4355' => 'lt:1/1/4:',
            '4356' => 'lt:1/1/5:',
            '4357' => 'lt:1/1/6:',
            '4358' => 'lt:1/1/7:',
            '4359' => 'lt:1/1/8:',
            '4360' => 'lt:1/1/9:',
            '4361' => 'lt:1/1/10:',
            '4362' => 'lt:1/1/11:',
            '4417' => 'vlt:1/1/63:',
            '4418' => 'vlt:1/1/64:',
            '4481' => 'eqptSlotId:4481', // Unknown Card Typ not listed by "show equitment slot"
            '12545' => 'ctrl:3/1:',
            '12547' => 'lt:3/1/1:',
            '12548' => 'lt:3/1/2:',
            '16641' => 'ctrl:4/1:',
            '16643' => 'lt:4/1/1:',
            '16644' => 'lt:4/1/2:',
            '20737' => 'ctrl:5/1:',
            '20739' => 'lt:5/1/1:',
            '20740' => 'lt:5/1/2:',
            '24833' => 'ctrl:6/1:',
            '24835' => 'lt:6/1/1:',
            '24836' => 'lt:6/1/2:',
            '28929' => 'ctrl:7/1:',
            '28931' => 'lt:7/1/1:',
            '28932' => 'lt:7/1/2:',
        ];
        // Slot IDs for Nokia ISAM 7362
    } elseif ($eqptHolderActualType == 'CFXR-A') {
        $slotTable = [
            '4353' => 'nt:',
            '4355' => 'lt:1/1/1:',
        ];
    }
} else {
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
}
$pre_cache['nokiaIsamSlot'] = snmpwalk_cache_multi_oid($device, 'eqptBoardContainerOffset', [], 'ASAM-EQUIP-MIB', 'nokia');
$pre_cache['nokiaIsamSlot'] = snmpwalk_cache_multi_oid($device, 'eqptBoardIfSlotId', $pre_cache['nokiaIsamSlot'], 'ASAM-EQUIP-MIB', 'nokia');
foreach ($pre_cache['nokiaIsamSlot'] as $slotId => $slot) {
    $pre_cache['nokiaIsamSlot'][$slotId]['numBasedSlot'] = $slotTable[$slotId];
    $pre_cache['nokiaProductName'] = $eqptHolderTable[$eqptHolderActualType];
    if ($pre_cache['nokiaProductName'] == null) {
        $pre_cache['nokiaProductName'] = 'Shelf:';
    }
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
