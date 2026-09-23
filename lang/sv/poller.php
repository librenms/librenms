<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => 'Tilldelade grupper',
                'help' => 'Den här noden kommer bara att vidta åtgärder på enheter i dessa pollargrupper.',
            ],
            'poller_enabled' => [
                'description' => 'Poller aktiverad',
                'help' => 'Aktivera pollar-arbetare på den här noden.',
            ],
            'poller_workers' => [
                'description' => 'Pollerarbetare',
                'help' => 'Mängden pollararbetare som ska leka på denna nod.',
            ],
            'poller_frequency' => [
                'description' => 'Pollerfrekvens (varning!)',
                'help' => 'Hur ofta ska man polla enheter på den här noden.  Varning! Att ändra detta utan att fixa rrd-filer kommer att bryta grafer. Se dokument för mer information.',
            ],
            'poller_down_retry' => [
                'description' => 'Enheten nere Försök igen',
                'help' => 'Om en enhet är nere när polling görs på denna nod. Det här är hur lång tid det ska vänta innan du försöker igen.',
            ],
            'discovery_enabled' => [
                'description' => 'Upptäckt aktiverad',
                'help' => 'Aktivera upptäcktsarbetare på den här noden.',
            ],
            'discovery_workers' => [
                'description' => 'Upptäckararbetare',
                'help' => 'Antalet upptäcktsarbetare som ska köras på den här noden.  För hög inställning kan orsaka överbelastning.',
            ],
            'discovery_frequency' => [
                'description' => 'Upptäcktsfrekvens',
                'help' => 'Hur ofta du ska köra enhetsupptäckt på den här noden.  Standard är 4 gånger om dagen.',
            ],
            'services_enabled' => [
                'description' => 'Tjänster aktiverade',
                'help' => 'Aktivera tjänstearbetare på den här noden.',
            ],
            'services_workers' => [
                'description' => 'Tjänster Arbetare',
                'help' => 'Antalet tjänstearbetare på denna nod.',
            ],
            'services_frequency' => [
                'description' => 'Tjänster Frekvens',
                'help' => 'Hur ofta man kör tjänster på denna nod.  Detta bör matcha pollers frekvens.',
            ],
            'billing_enabled' => [
                'description' => 'Fakturering aktiverad',
                'help' => 'Aktivera faktureringsarbetare på den här noden.',
            ],
            'billing_frequency' => [
                'description' => 'Faktureringsfrekvens',
                'help' => 'Hur ofta ska faktureringsdata samlas in på denna nod.',
            ],
            'billing_calculate_frequency' => [
                'description' => 'Fakturering Beräkna Frekvens',
                'help' => 'Hur ofta ska man beräkna räkningsanvändning på denna nod.',
            ],
            'alerting_enabled' => [
                'description' => 'Varning aktiverad',
                'help' => 'Aktivera varningsarbetaren på den här noden.',
            ],
            'alerting_frequency' => [
                'description' => 'Varningsfrekvens',
                'help' => 'Hur ofta kontrolleras varningsregler på denna nod.  Observera att data endast uppdateras baserat på pollers frekvens.',
            ],
            'ping_enabled' => [
                'description' => 'Snabb ping aktiverad',
                'help' => 'Fast Ping pingar bara enheter för att kontrollera om de är uppe eller nere',
            ],
            'ping_frequency' => [
                'description' => 'Pingfrekvens',
                'help' => 'Hur ofta ska man kontrollera ping på denna nod.  Varning! Om du ändrar detta måste du göra ytterligare ändringar.  Kontrollera dokumenten för snabb ping.',
            ],
            'update_enabled' => [
                'description' => 'Dagligt underhåll aktiverat',
                'help' => 'Kör daily.sh underhållsskript och starta om dispatcher-tjänsten efteråt.',
            ],
            'update_frequency' => [
                'description' => 'Underhållsfrekvens',
                'help' => 'Hur ofta kör dagligt underhåll på denna nod. Standard är 1 dag. Det rekommenderas starkt att inte ändra detta.',
            ],
            'loglevel' => [
                'description' => 'Loggnivå',
                'help' => 'Loggnivå för leveranstjänsten.',
            ],
            'watchdog_enabled' => [
                'description' => 'Watchdog aktiverad',
                'help' => 'Watchdog övervakar loggfilen och startar om tjänsten om den inte har uppdaterats',
            ],
            'watchdog_log' => [
                'description' => 'Loggfil att titta på',
                'help' => 'Standard är LibreNMS-loggfilen.',
            ],
        ],
        'units' => [
            'seconds' => 'Sekunder',
            'workers' => 'Arbetare',
        ],
    ],
];
