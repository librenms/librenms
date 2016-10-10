<?php

$genEquipInventoryCardName = snmp_get($device, 'MWRM-UNIT-MIB::genEquipInventoryCardName.127', '-Osqnv');

if (preg_match('/IP-[1,2]0/', $genEquipInventoryCardName)) {
    $os = 'ceraos';
}
