<?php

return [
    'database_connect' => [
        'title' => 'Greška u konekciji sa bazom podataka',
    ],
    'database_inconsistent' => [
        'title' => 'Oštećena baza podataka',
        'header' => 'Uočena je greška u strukturi baze podataka. Ispravite grešku pre nastavka.',
    ],
    'dusk_unsafe' => [
        'title' => 'Korišćenje Dusk-a u produkciji nije bezbedno',
        'message' => 'Pokrenite ":command" da obrišete Dusk ili podesite odgovarajući APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'Greška: ne može se pisati u fajl',
        'message' => 'Nemoguće je pisati u fajl (:file). Proverite prava pristupa fajlu i SELinux/AppArmor ako su omogućeni.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Uređaj :hostname već postoji',
        'ip_exists' => 'Ne može se dodati :hostname, postoji uređaj :existing sa ovom IP adresom :ip',
        'sysname_exists' => 'Već postoji uređaj :hostname sa dupliranim sysName: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'Nedostupan :hostname (:ip) putem PING-a',
        'unsnmpable' => 'Ne može se konektovati na :hostname, molim proverite SNMP podatke i dostupnost',
        'unresolvable' => 'Ime uređaja se ne može pretvoriti u IP',
        'no_reply_community' => 'SNMP :version: Nema odgovora sa SNMP imenom :credentials',
        'no_reply_credentials' => 'SNMP :version: Nema odgovora sa SNMP lozinkom :credentials',
    ],
    'ldap_missing' => [
        'title' => 'Nedostaje podršla za PHP LDAP',
        'message' => 'PHP ne podržava LDAP, molim instalirajte ili omogućite PHP LDAP dodatak',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Maksimalni vremenski period od :seconds sekundi je istekao|Maksimalno vreme za izvršavanje od :seconds sekundi je isteklo',
        'message' => 'Vreme za učitavanje stranice podešeno u PHP-u je isteklo.  Ili produžite vreme u php.ini-ju ili poboljšajte računar',
    ],
    'unserializable_route_cache' => [
        'title' => 'Greška nastala zbog različite verzije PHP-a',
        'message' => 'Verzija PHP-a koja pokreće WEB (:web_version) se ne poklapa sa CLI vezijom (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'SNMP verzija ":snmpver" nije podržana, mora biti v1, v2c, ili v3',
    ],
];
