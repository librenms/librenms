<?php

return [
    'alpha_space' => ':attribute får endast innehålla bokstäver, siffror, understreck och mellanslag.',
    'ip_or_hostname' => ':attribute måste ha en giltig IP-adress/nätverk eller värdnamn.',
    'is_regex' => ':attribute är inte ett giltigt reguljärt uttryck',
    'array_keys_not_empty' => ':attribute innehåller tomma arraynycklar.',
    'custom' => [
        'attribute-name' => [
        ],
    ],
    'attributes' => [
    ],
    'results' => [
        'autofix' => 'Försök att automatiskt fixa',
        'fix' => 'Fixa',
        'fixed' => 'Korrigeringen har slutförts, uppdatera för att köra valideringarna igen.',
        'fetch_failed' => 'Det gick inte att hämta valideringsresultat',
        'backend_failed' => 'Det gick inte att ladda data från backend, kör ./validate.php på konsolen för att kontrollera.',
        'invalid_fixer' => 'Ogiltig fixer',
        'show_all' => 'Visa alla',
        'show_less' => 'Visa mindre',
        'validate' => 'Validera',
        'validating' => 'Validerar',
        'skipped' => 'Hoppade över',
        'run' => 'Kör',
    ],
    'validations' => [
        'groups' => [
            'configuration' => 'Konfiguration',
            'database' => 'Databas',
            'dependencies' => 'Beroenden',
            'disk' => 'Disk',
            'distributedpoller' => 'Distribuerad pollare',
            'mail' => 'Mail',
            'php' => 'PHP',
            'poller' => 'Poller',
            'programs' => 'Program',
            'python' => 'Python',
            'rrd' => 'RRD',
            'rrdcheck' => 'RRD-kontroll',
            'scheduler' => 'Schemaläggare',
            'system' => 'System',
            'updates' => 'Uppdateringar',
            'user' => 'Användare',
            'webserver' => 'Webbserver',
        ],
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'rrdtool version :installed_version är för gammal, LibreNMS kräver en minsta version av 1.5.5',
                'fail_config' => 'Den rrdtool_version :config_version du har angett är för gammal, LibreNMS kräver en minsta version av 1.5.5',
                'fix' => 'Antingen kommentera bort eller ta bort $config[\'rrdtool_version\'] = \':version\'; från din config.php-fil',
                'ok' => 'rrdtool version ok',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => ':socket verkar inte existera, rrdcachad anslutningstest misslyckades',
                'fail_port' => 'Kan inte ansluta till rrdcachad server :server på port :port',
                'ok' => 'Ansluten till rrdcached',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'Din RRD-katalog ägs av root, vänligen överväg att byta till en användare som inte är root-användare',
                'fail_mode' => 'Din RRD-katalog är inte inställd på 0775',
                'ok' => 'rrd_dir är skrivbar',
            ],
            'CheckRrdStep' => [
                'fail' => 'Vissa RRD-filer har fel steg. :bad/:total',
                'fail_bad_files' => 'Det gick inte att läsa RRD-filer. :bad/:total',
                'list_bad_step_title' => 'RRD-filer med felaktiga steg',
                'list_bad_files_title' => 'Fel vid körning av rrdinfo på filer',
                'list_bad_step_item' => ':file: steget är :step, bör vara :target',
                'ok' => 'Alla :total RRD-filer har rätt steg.',
                'timeout' => 'Att kontrollera RRD-filer tog för lång tid, krysset hoppade över. Du kan köra :command för att kontrollera och fixa alla rrd-filer.',
            ],
        ],
        'database' => [
            'CheckDatabaseConnected' => [
                'fail' => 'Det går inte att ansluta till databasen',
                'fail_connect' => 'Det går inte att ansluta till databasen. Bekräfta att databasservern körs och att anslutningsinformationen är korrekt.  Kontrollera DB_HOST, DB_PORT och DB_NAME i miljön eller i :env_file',
                'fail_access' => 'Databas ansluten, men användaren har inte behörighet att komma åt databasen. Kör SQL-fråga för att ge behörigheter (ändra lokal värd till lokalt värdnamn om databasen är avlägsen)',
                'fail_auth' => 'Databasuppgifterna är felaktiga. Dubbelkolla autentiseringsuppgifterna i DB_USERNAME och DB_PASSWORD antingen i miljön eller i :env_file',
                'ok' => 'Databas ansluten',
            ],
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'Du har small_case_table_names satt till 1 eller true i mysql config.',
                'fix' => 'Ställ in lower_case_table_names=0 i din mysql-konfigurationsfil i avsnittet [mysqld].',
                'ok' => 'small_case_table_names är aktiverat',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => ':server version :min är den minsta version som stöds från och med :date.',
                'fix' => 'Uppdatera :server till en version som stöds, föreslog :suggested.',
                'ok' => 'SQL Server uppfyller minimikraven',
            ],
            'CheckMysqlEngine' => [
                'fail' => 'Vissa tabeller använder inte den rekommenderade InnoDB-motorn, detta kan orsaka problem.',
                'tables' => 'Tabeller',
                'ok' => 'MySQL-motorn är optimal',
            ],
            'CheckSqlServerTime' => [
                'fail' => 'Tiden mellan denna server och mysql-databasen är avstängd
 Mysql-tid :mysql_time
 PHP-tid :php_time',
                'ok' => 'MySQL och PHP tidsmatchning',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => 'Din databas är inaktuell!',
                'fail_legacy_outdated' => 'Ditt databasschema (:current) är äldre än det senaste (:latest).',
                'fix_legacy_outdated' => 'Kör ./daily.sh manuellt och kontrollera eventuella fel.',
                'warn_extra_migrations' => 'Ditt databasschema har extra migrering (:migrations). Om du precis bytte till den stabila utgåvan från den dagliga utgåvan ligger din databas mellan utgåvorna och detta kommer att lösas med nästa utgåva.',
                'warn_legacy_newer' => 'Ditt databasschema (:current) är nyare än förväntat (:latest). Om du precis bytte till den stabila utgåvan från den dagliga utgåvan ligger din databas mellan utgåvorna och detta kommer att lösas med nästa utgåva.',
                'ok' => 'Databasschemat är aktuellt',
            ],
            'CheckSchemaCollation' => [
                'ok' => 'Databas- och kolumnsamlingar är korrekta',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => 'Inställningen för distribuerad polling är aktiverad globalt',
                'not_enabled' => 'Du har inte aktiverat distributed_poller',
                'not_enabled_globally' => 'Du har inte aktiverat distributed_poller globalt',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'Du har inte konfigurerat distributed_poller_memcached_host',
                'not_configured_port' => 'Du har inte konfigurerat distributed_poller_memcached_port',
                'could_not_connect' => 'Kunde inte ansluta till memcachad server',
                'ok' => 'Anslutning till memcached är ok',
            ],
            'CheckRrdcached' => [
                'fail' => 'Du har inte aktiverat rrdcached',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => 'Poller körs inte.  Ingen poller har körts inom de senaste :interval sekunderna.',
                'both_fail' => 'Både Dispatcher Service och Python Wrapper var aktiva nyligen, detta kan orsaka dubbel polling',
                'ok' => 'Aktiva pollare hittades',
            ],
            'CheckDispatcherService' => [
                'fail' => 'Inga aktiva avsändarnoder hittades',
                'ok' => 'Dispatcher Service är aktiverad',
                'nodes_down' => 'Vissa avsändarnoder har inte checkat in nyligen',
                'not_detected' => 'Dispatcher Service inte upptäckt',
                'warn' => 'Dispatcher Service har använts, men inte nyligen',
            ],
            'CheckLocking' => [
                'fail' => 'Cacheserverproblem: :message',
                'ok' => 'Lås är funktionella',
            ],
            'CheckPythonWrapper' => [
                'fail' => 'Inga aktiva pythonomslagspolare hittades',
                'no_pollers' => 'Inga pythonomslagspolare hittades',
                'cron_unread' => 'Kunde inte läsa cron-filer',
                'ok' => 'Python poller wrapper pollar',
                'nodes_down' => 'Vissa pollernoder har inte checkat in nyligen',
                'not_detected' => 'Python wrapper cron-post är inte närvarande',
            ],
            'CheckRedis' => [
                'bad_driver' => 'Genom att använda :driver för låsning bör du ställa in CACHE_STORE=redis',
                'ok' => 'Redis är funktionell',
                'unavailable' => 'Redis är inte tillgänglig',
            ],
        ],
    ],
];
