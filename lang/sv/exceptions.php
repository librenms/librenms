<?php

return [
    'database_connect' => [
        'title' => 'Fel vid anslutning till databasen',
    ],
    'database_inconsistent' => [
        'title' => 'Databas inkonsekvent',
        'header' => 'Databasinkonsekvenser hittades under ett databasfel, åtgärda för att fortsätta.',
    ],
    'dusk_unsafe' => [
        'title' => 'Det är osäkert att köra Dusk i produktion',
        'message' => 'Kör ":command" för att ta bort Dusk eller om du är en utvecklare ställ in lämplig APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'Fel: Kunde inte skriva till filen',
        'message' => 'Det gick inte att skriva till filen (:file).  Kontrollera behörigheter och SELinux/AppArmor om tillämpligt.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Enheten :hostname finns redan',
        'ip_exists' => 'Kan inte lägga till :hostname, har redan enhet :existing med denna IP :ip',
        'sysname_exists' => 'Har redan enheten :hostname på grund av dubblett av sysName: :sysname',
    ],
    'host_name_empty' => 'Värdnamnet är tomt',
    'host_unreachable' => [
        'unpingable' => 'Kunde inte pinga :hostname (:ip)',
        'unsnmpable' => 'Det gick inte att ansluta till :hostname, kontrollera snmp-detaljerna och snmp-nåbarheten',
        'unresolvable' => 'Värdnamn löstes inte till IP',
        'no_reply_community' => 'SNMP :version: Inget svar med communityn :credentials',
        'no_reply_credentials' => 'SNMP :version: Inget svar med referenser :credentials',
    ],
    'ldap_missing' => [
        'title' => 'PHP LDAP-stöd saknas',
        'message' => 'PHP stöder inte LDAP, installera eller aktivera PHP LDAP-tillägget',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Maximal exekveringstid på :seconds sekund överskriden|Maximala exekveringstid på :seconds sekunder överskriden',
        'message' => 'Sidladdningen överskred din maximala körningstid som konfigurerats i PHP.  Öka antingen max_execution_time i din php.ini eller förbättra serverns hårdvara',
    ],
    'unserializable_route_cache' => [
        'title' => 'Fel orsakat av att PHP-versionen inte matchar',
        'message' => 'Den version av PHP som din webbserver kör (:web_version) matchar inte CLI-versionen (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'SNMP-versionen ":snmpver" som inte stöds måste vara v1, v2c eller v3',
    ],
];
