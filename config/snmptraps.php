<?php

return [
    'trap_handlers' => [
        'SNMPv2-MIB::authenticationFailure' => \LibreNMS\Snmptrap\Handlers\AuthenticationFailure::class,
        'BGP4-MIB::bgpEstablished' => \LibreNMS\Snmptrap\Handlers\BgpEstablished::class,
        'BGP4-MIB::bgpBackwardTransition' => \LibreNMS\Snmptrap\Handlers\BgpBackwardTransition::class,
        'IF-MIB::linkUp' => \LibreNMS\Snmptrap\Handlers\LinkUp::class,
        'IF-MIB::linkDown' => \LibreNMS\Snmptrap\Handlers\LinkDown::class,
        'MG-SNMP-UPS-MIB::upsmgUtilityFailure' => \LibreNMS\Snmptrap\Handlers\UpsmgUtilityFailure::class,
        'MG-SNMP-UPS-MIB::upsmgUtilityRestored' => \LibreNMS\Snmptrap\Handlers\UpsmgUtilityRestored::class,
        'CM-SYSTEM-MIB::cmObjectCreationTrap' => \LibreNMS\Snmptrap\Handlers\AdvaObjectCreation::class,
        'CM-SYSTEM-MIB::cmObjectDeletionTrap' => \LibreNMS\Snmptrap\Handlers\AdvaObjectDeletion::class,
        'CM-SYSTEM-MIB::cmStateChangeTrap' => \LibreNMS\Snmptrap\Handlers\AdvaStateChangeTrap::class,
        'CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdCrossingAlert' => \LibreNMS\Snmptrap\Handlers\AdvaThresholdCrossingAlert::class,
        'CM-ALARM-MIB::cmNetworkElementAlmTrap' => \LibreNMS\Snmptrap\Handlers\AdvaNetworkElementAlmTrap::class,
        'CM-ALARM-MIB::cmSysAlmTrap' => \LibreNMS\Snmptrap\Handlers\AdvaSysAlmTrap::class,
        'CM-SYSTEM-MIB::cmSnmpDyingGaspTrap' => \LibreNMS\Snmptrap\Handlers\AdvaSnmpDyingGaspTrap::class,
        'CM-SYSTEM-MIB::cmAttributeValueChangeTrap' => \LibreNMS\Snmptrap\Handlers\AdvaAttributeChange::class,
    ]
];
