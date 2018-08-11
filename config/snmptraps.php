<?php

return [
    'trap_handlers' => [
        'SNMPv2-MIB::authenticationFailure' => \LibreNMS\Snmptrap\Handler\AuthenticationFailure::class,
        'BGP4-MIB::bgpEstablished' => \LibreNMS\Snmptrap\Handler\BgpEstablished::class,
        'BGP4-MIB::bgpBackwardTransition' => \LibreNMS\Snmptrap\Handler\BgpBackwardTransition::class,
        'IF-MIB::linkUp' => \LibreNMS\Snmptrap\Handler\LinkUp::class,
        'IF-MIB::linkDown' => \LibreNMS\Snmptrap\Handler\LinkDown::class,
    ]
];
