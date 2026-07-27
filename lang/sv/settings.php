<?php

return [
    'title' => 'Inställningar',
    'readonly' => 'Ställ in config.php, ta bort från config.php för att aktivera.',
    'groups' => [
        'alerting' => 'Varning',
        'api' => 'API',
        'apps' => 'Ansökningar',
        'auth' => 'Autentisering',
        'authorization' => 'Auktorisation',
        'external' => 'Externt',
            'global' => 'Globalt',
        'os' => 'OS',
        'discovery' => 'Upptäckt',
        'graphing' => 'Grafer',
        'poller' => 'Poller',
        'system' => 'System',
        'webui' => 'Webbgränssnitt',
    ],
    'sections' => [
        'alerting' => [
            'general' => [
                'name' => 'Allmänna varningsinställningar',
            ],
            'email' => [
                'name' => 'E-postalternativ',
            ],
            'rules' => [
                'name' => 'Standardinställningar för varningsregel',
            ],
            'scheduled-maintenance' => [
                'name' => 'Schemalagt underhåll',
            ],
        ],
        'api' => [
            'cors' => [
                'name' => 'CORS',
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'name' => 'PowerDNS Recursor',
            ],
            'oslv_monitor' => [
                'name' => 'OSLV Monitor',
            ],
            'sneck' => [
                'name' => 'Sneck',
            ],
            'ssl-certificates' => [
                'name' => 'SSL-certifikat',
            ],
        ],
        'auth' => [
            'general' => [
                'name' => 'Allmänna autentiseringsinställningar',
            ],
            'ad' => [
                'name' => 'Active Directory-inställningar',
            ],
            'ldap' => [
                'name' => 'LDAP-inställningar',
            ],
            'radius' => [
                'name' => 'Radieinställningar',
            ],
            'socialite' => [
                'name' => 'Socialite-inställningar',
            ],
            'http' => [
                'name' => 'HTTP Auth-inställningar',
            ],
            'sso' => [
                'name' => 'Enkel inloggning',
            ],
        ],
        'authorization' => [
            'device-group' => [
                'name' => 'Inställningar för enhetsgrupp',
            ],
        ],
        'discovery' => [
            'general' => [
                'name' => 'Allmänna upptäcktsinställningar',
            ],
            'route' => [
                'name' => 'Rutter Discovery Module',
            ],
            'discovery_modules' => [
                'name' => 'Upptäcktsmoduler',
            ],
            'autodiscovery' => [
                'name' => 'Nätverksupptäckt',
            ],
            'ports' => [
                'name' => 'Portmodul',
            ],
            'storage' => [
                'name' => 'Förvaringsmodul',
            ],
            'processor' => [
                'name' => 'Processormodul',
            ],
            'ipmi' => [
                'name' => 'IPMI-modul',
            ],
            'sensors' => [
                'name' => 'Sensormodul',
            ],
            'virtualization' => [
                'name' => 'Virtualiseringsmodul',
            ],
        ],
        'external' => [
            'binaries' => [
                'name' => 'Binära platser',
            ],
            'location' => [
                'name' => 'Platsinställningar',
            ],
            'graylog' => [
                'name' => 'Graylog-integration',
            ],
            'oxidized' => [
                'name' => 'Oxiderad integration',
            ],
            'mac_oui' => [
                'name' => 'Integration för uppslagning av MAC-OUI',
            ],
            'peeringdb' => [
                'name' => 'PeeringDB-integration',
            ],
            'nfsen' => [
                'name' => 'NfSen-integration',
            ],
            'unix-agent' => [
                'name' => 'Unix-agentintegration',
            ],
            'smokeping' => [
                'name' => 'Integration av rökning',
            ],
            'snmptrapd' => [
                'name' => 'Integration för SNMP-trap',
            ],
            'rancid' => [
                'name' => 'RANCID integration',
            ],
            'collectd' => [
                'name' => 'Samlad integration',
            ],
            'unimus' => [
                'name' => 'Unimus integration',
            ],
        ],
        'poller' => [
            'availability' => [
                'name' => 'Enhetens tillgänglighet',
            ],
            'distributed' => [
                'name' => 'Distribuerad pollare',
            ],
            'graphite' => [
                'name' => 'Databutik: Grafit',
            ],
            'influxdb' => [
                'name' => 'Datalager: InfluxDB',
            ],
            'influxdbv2' => [
                'name' => 'Dataarkiv: InfluxDBv2',
            ],
            'kafka' => [
                'name' => 'Databutik: Kafka',
            ],
            'mtu' => [
                'name' => 'MTU-kontroll',
            ],
            'opentsdb' => [
                'name' => 'Datastore: OpenTSDB',
            ],
            'ping' => [
                'name' => 'Ping',
            ],
            'prometheus' => [
                'name' => 'Databutik: Prometheus',
            ],
            'rrdtool' => [
                'name' => 'Datalager: RRDTool',
            ],
            'snmp' => [
                'name' => 'SNMP',
            ],
            'dispatcherservice' => [
                'name' => 'Distributionstjänst',
            ],
            'poller_modules' => [
                'name' => 'Pollermoduler',
            ],
            'ports' => [
                'name' => 'Avläsningsmodul för portar',
            ],
        ],
        'system' => [
            'billing' => [
                'name' => 'Fakturering',
            ],
            'cleanup' => [
                'name' => 'Rengöring',
            ],
            'proxy' => [
                'name' => 'Proxy',
            ],
            'updates' => [
                'name' => 'Uppdateringar',
            ],
            'scheduledtasks' => [
                'name' => 'Schemalagda uppgifter',
            ],
            'server' => [
                'name' => 'Server',
            ],
            'reporting' => [
                'name' => 'Rapportering',
            ],
        ],
        'webui' => [
            'availability-map' => [
                'name' => 'Kartinställningar för tillgänglighet',
            ],
            'custom-map' => [
                'name' => 'Anpassade kartinställningar',
            ],
            'graph' => [
                'name' => 'Grafinställningar',
            ],
            'dashboard' => [
                'name' => 'Inställningar för instrumentpanelen',
            ],
            'port-descr' => [
                'name' => 'Gränssnitt Beskrivning Parsing',
            ],
            'search' => [
                'name' => 'Sökinställningar',
            ],
            'style' => [
                'name' => 'Stil',
            ],
            'device' => [
                'name' => 'Enhetsinställningar',
            ],
            'worldmap' => [
                'name' => 'Världskartans inställningar',
            ],
            'general' => [
                'name' => 'Allmänna inställningar för webbgränssnittet',
            ],
            'front-page' => [
                'name' => 'Inställningar för förstasidan',
            ],
            'menu' => [
                'name' => 'Menyinställningar',
            ],
            'scheduled-maintenance' => [
                'name' => 'Schemalagt underhåll',
            ],
            'alert-map' => [
                'name' => 'Inställningar för larmkarta',
            ],
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Behåll inaktiva användare för',
                'help' => 'Användare kommer att raderas från LibreNMS efter så många dagar att de inte loggat in. 0 betyder aldrig och användare kommer att återskapas om användaren loggar in igen.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Kontrollera om det finns dubbletter av IP när du lägger till enheter',
            'help' => 'När en värd läggs till med en IP-adress kontrolleras att adressen inte redan finns. Om den finns läggs värden inte till. Kontrollen görs inte när en värd läggs till med värdnamn. Om inställningen är aktiverad slås värdnamnet upp och kontrollen görs även då. Detta förhindrar oavsiktliga dubbletter.',
        ],
        'alert_rule' => [
            'acknowledged_alerts' => [
                'description' => 'Godkända varningar',
                'help' => 'Skicka varningar när en varning kvitteras',
            ],
            'severity' => [
                'description' => 'Allvarlighet',
                'help' => 'Allvarlighet för en varning',
            ],
            'default_operation_steps_to' => [
                'description' => 'Standardfunktion: Steg till',
                'help' => 'Standard eskaleringsslutsteg för skapade operationsrader (-1 betyder ingen gräns)',
            ],
            'default_operation_start_in' => [
                'description' => 'Standarddrift: Starta om',
                'help' => 'Standardfördröjning innan ett operationsmeddelande skickas',
            ],
            'default_operation_step_duration' => [
                'description' => 'Standarddrift: Stegets varaktighet',
                'help' => 'Standard driftstegs varaktighet (minuter)',
            ],
            'default_operation_notifications_suppressed' => [
                'description' => 'Standardfunktion: Undertryck aviseringar',
                'help' => 'Undertryck aviseringar som standard för skapade operationsrader',
            ],
            'invert_rule_match' => [
                'description' => 'Invertera regelmatchning',
                'help' => 'Varning endast om regeln inte matchar',
            ],
            'recovery_alerts' => [
                'description' => 'Återställningsvarningar',
                'help' => 'Meddela om Alert återställs',
            ],
            'acknowledgement_alerts' => [
                'description' => 'Kvitteringsvarningar',
                'help' => 'Meddela om varningen bekräftas',
            ],
            'invert_map' => [
                'description' => 'Alla enheter utom i listan',
                'help' => 'Varning endast för enheter som inte är listade',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Bekräfta som standard tills varningen rensar alternativet',
                'help' => 'Bekräfta som standard tills varningen försvinner',
            ],
            'admins' => [
                'description' => 'Utfärda varningar till administratörer (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'default_copy' => [
                'description' => 'Kopiera alla e-postvarningar till standardkontakt (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'default_if_none' => [
                'description' => 'kan inte ställas in i webui? (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'default_mail' => [
                'description' => 'Standardkontakt (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'default_only' => [
                'description' => 'Skicka varningar endast till standardkontakt (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'disable' => [
                'description' => 'Inaktivera varning',
                'help' => 'Stoppa varningar som genereras',
            ],
            'acknowledged' => [
                'description' => 'Skicka bekräftade varningar',
                'help' => 'Meddela om varningen har bekräftats',
            ],
            'fixed-contacts' => [
                'description' => 'Inaktivera kontaktändringar för aktiva varningar',
                'help' => 'Om TRUE kommer några ändringar i sysContact eller användarnas e-postmeddelanden inte att respekteras medan varningen är aktiv',
            ],
            'globals' => [
                'description' => 'Utfärda varningar för skrivskyddade användare (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'scheduled_maintenance_default_behavior' => [
                'description' => 'Standardbeteende för schemalagt underhåll',
                'help' => 'Standardbeteende för schemalagt underhåll',
                'options' => [
                    '1' => 'Hoppa över varningar',
                    '2' => 'Stäng av aviseringar',
                    '3' => 'Kör varningar',
                ],
            ],
            'syscontact' => [
                'description' => 'Utfärda varningar till sysContact (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Aktivera e-postvarning',
                    'help' => 'Mail varningstransport',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Toleransfönster för cron',
                'help' => 'Toleransfönster på sekunder',
            ],
            'users' => [
                'description' => 'Utfärda varningar till vanliga användare (utfasad)',
                'help' => 'Utfasad, använd e-postvarningstransporten istället.',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Varningsloggposter äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'discovery_on_reboot' => [
            'description' => 'Upptäckt vid omstart',
            'help' => 'Gör en upptäckt på en omstartad enhet',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Tillåt Duplicate sysName',
            'help' => 'Som standard är dubbletter av sysNames inaktiverade från att läggas till för att förhindra att en enhet med flera gränssnitt läggs till flera gånger',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Tillåt oautentiserad grafåtkomst',
            'help' => 'Tillåter vem som helst att komma åt grafer utan inloggning',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Tillåt åtkomst till givna nätverksdiagram',
            'help' => 'Tillåt det givna nätverket oautentiserad grafåtkomst (gäller inte när oautentiserade diagram är aktiverat)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => 'Tillåt rubriker',
                    'help' => 'Ställer in Access-Control-Allow-Headers svarshuvud',
                ],
                'allowcredentials' => [
                    'description' => 'Tillåt inloggningsuppgifter',
                    'help' => 'Ställer in rubriken Access-Control-Allow-Credentials',
                ],
                'allowmethods' => [
                    'description' => 'Tillåtna metoder',
                    'help' => 'Matchar förfrågningsmetoden.',
                ],
                'enabled' => [
                    'description' => 'Aktivera CORS-stöd för API',
                    'help' => 'Låter dig ladda api-resurser från en webbklient',
                ],
                'exposeheaders' => [
                    'description' => 'Exponera rubriker',
                    'help' => 'Ställer in Access-Control-Expose-Headers svarshuvud',
                ],
                'maxage' => [
                    'description' => 'Max ålder',
                    'help' => 'Ställer in svarshuvudet Access-Control-Max-Age',
                ],
                'origin' => [
                    'description' => 'Tillåt Request Origins',
                    'help' => 'Matchar begärans ursprung. Jokertecken kan användas, t.ex. *.mindomän.com',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'API-nyckel för PowerDNS Recursor',
                    'help' => 'API-nyckel för PowerDNS Recursor-appen vid direktanslutning',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor använder HTTPS?',
                    'help' => 'Använd HTTPS istället för HTTP för PowerDNS Recursor-appen när du ansluter direkt',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor-port',
                    'help' => 'TCP-port att använda för PowerDNS Recursor-appen vid direktanslutning',
                ],
            ],
            'oslv_monitor' => [
                'seen_age' => [
                    'description' => 'Sett ålderströskel',
                    'help' => 'Åldras i sekunder efter vilket föremål anses vara inaktuella',
                ],
                'linux_pg_memory_stats' => [
                    'description' => 'Minnesstatistik för Linux-sidor',
                    'help' => 'Aktivera insamling av minnesstatistik för Linux-sidor',
                ],
                'misc_linux_memory_stats' => [
                    'description' => 'Diverse Linux-minnesstatistik',
                    'help' => 'Aktivera insamling av diverse Linux-minnesstatistik',
                ],
                'zswap_size' => [
                    'description' => 'ZSwap storleksstatistik',
                    'help' => 'Aktivera insamling av ZSwap-storleksstatistik',
                ],
                'zswap_activity' => [
                    'description' => 'ZSwap aktivitetsstatistik',
                    'help' => 'Aktivera insamling av ZSwap-aktivitetsstatistik',
                ],
                'workingset_stats' => [
                    'description' => 'Arbetsuppsättningsstatistik',
                    'help' => 'Aktivera insamling av arbetsuppsättningsstatistik',
                ],
                'thp_activity' => [
                    'description' => 'THP aktivitetsstatistik',
                    'help' => 'Aktivera insamling av Transparent Huge Pages-aktivitetsstatistik',
                ],
            ],
            'sneck' => [
                'polling_time_diff' => [
                    'description' => 'Omröstningstidsskillnad',
                    'help' => 'Aktivera spårning av pollingtidsskillnad för Sneck',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Nyckel för att hålla cache för autonoma systembeskrivningar',
        ],
        'auth' => [
            'allow_get_login' => [
                'description' => 'Tillåt inloggning (osäkert)',
                'help' => 'Tillåt inloggning genom att sätta in användarnamn och lösenordsvariabler i url get request, användbart för visningssystem där du inte kan logga in interaktivt. Detta anses osäkert eftersom lösenordet kommer att visas i loggar och inloggningar är inte hastighetsbegränsade så det kan öppna dig för brute force attacker.',
            ],
            'socialite' => [
                'redirect' => [
                    'description' => 'Omdirigera inloggningssida',
                    'help' => 'Inloggningssidan bör omdirigera omedelbart till den först definierade leverantören.<br><br>TIPS: Du kan förhindra det genom att lägga till ?redirect=0 i webbadressen',
                ],
                'register' => [
                    'description' => 'Tillåt registrering via leverantör',
                ],
                'configs' => [
                    'description' => 'Leverantörskonfigurationer',
                ],
                'scopes' => [
                    'description' => 'Omfattningar som bör inkluderas i autentiseringsbegäran',
                    'help' => 'Se https://laravel.com/docs/10.x/socialite#access-scopes',
                ],
                'default_role' => [
                    'description' => 'Standardroll',
                ],
                'claims' => [
                    'description' => 'Anspråk',
                    'help' => 'Kartlägg grupper till roller',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => 'Bas DN',
            'help' => 'grupper och användare måste vara under denna dn. Exempel: dc=exempel,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Kontrollera certifikatet',
            'help' => 'Kontrollera certifikatens giltighet. Vissa servrar använder självsignerade certifikat, om du inaktiverar detta tillåter dessa.',
        ],
        'auth_ad_debug' => [
            'description' => 'Felsök',
            'help' => 'Visa detaljerade felmeddelanden, lämna inte detta aktiverat eftersom det kan läcka data.',
        ],
        'auth_ad_domain' => [
            'description' => 'Active Directory-domän',
            'help' => 'Exempel på Active Directory-domän: example.com',
        ],
        'auth_ad_global_read' => [
            'description' => 'Global läsning',
            'help' => 'Tillåt global läsåtkomst för alla användare',
        ],
        'auth_ad_group' => [
            'description' => 'Åtkomstgrupp DN',
            'help' => 'Särskilt namn för en grupp för att ge normal nivååtkomst. Exempel: cn=gruppnamn,ou=grupper,dc=exempel,dc=com',
        ],
        'auth_ad_group_filter' => [
            'description' => 'Grupp LDAP-filter',
            'help' => 'Active Directory LDAP-filter för att välja grupper',
        ],
        'auth_ad_groups' => [
            'description' => 'Gruppåtkomst',
            'help' => 'Definiera grupper som har åtkomst och nivå',
        ],
        'auth_ad_require_groupmembership' => [
            'description' => 'Kräv gruppmedlemskap',
            'help' => 'Tillåt endast användare att logga in om de ingår i en definierad grupp',
        ],
        'auth_ad_timeout' => [
            'description' => 'Timeout för anslutning',
            'help' => 'Om en eller flera servrar inte svarar, kommer högre timeout att orsaka långsamma inloggningar. För låg kan orsaka anslutningsfel i vissa fall',
        ],
        'auth_ad_user_filter' => [
            'description' => 'Användar-LDAP-filter',
            'help' => 'Active Directory LDAP-filter för att välja användare',
        ],
        'auth_ad_url' => [
            'description' => 'Active Directory-server(ar)',
            'help' => 'Ställ in server(ar), utrymme separerat. Prefix med ldaps:// för ssl. Exempel: ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribut att kontrollera användarnamn mot',
                'help' => 'Attribut används för att identifiera användare med användarnamn',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (åsidosätter binda användarnamn)',
            'help' => 'Fullständigt DN för bindanvändare',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Bind lösenord',
            'help' => 'Lösenord för bindanvändare',
        ],
        'auth_ldap_binduser' => [
            'description' => 'Bind användarnamn',
            'help' => 'Används för att fråga LDAP-servern när ingen användare är inloggad (varningar, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (åsidosätter binda användarnamn)',
            'help' => 'Fullständigt DN för bindanvändare',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Bind lösenord',
            'help' => 'Lösenord för bindanvändare',
        ],
        'auth_ad_binduser' => [
            'description' => 'Bind användarnamn',
            'help' => 'Används för att fråga AD-servern när ingen användare är inloggad (varningar, API, etc)',
        ],
        'auth_ad_starttls' => [
            'description' => 'Använd STARTTLS',
            'help' => 'Använd STARTTLS för att säkra anslutningen.  Alternativ till LDAPS.',
            'options' => [
                'disabled' => 'Inaktiverad',
                'optional' => 'Valfritt',
                'required' => 'Obligatoriskt',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP-cache-utgång',
            'help' => 'Lagrar tillfälligt LDAP-frågeresultat.  Förbättrar hastigheter, men data kan vara inaktuella.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Visa felsökning',
            'help' => 'Visar felsökningsinformation.  Kan avslöja privat information, lämna inte aktiverad.',
        ],
        'auth_ldap_cacertfile' => [
            'description' => 'Åsidosätt systemet TLS CA Cert',
            'help' => 'Använd medföljande CA-certifikat för LDAPS.',
        ],
        'auth_ldap_ignorecert' => [
            'description' => 'Kräver inget giltigt certifikat',
            'help' => 'Kräv inte ett giltigt TLS-certifikat för LDAPS.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Mail attribut',
        ],
        'auth_ldap_group' => [
            'description' => 'Åtkomstgrupp DN',
            'help' => 'Särskilt namn för en grupp för att ge normal nivååtkomst. Exempel: cn=gruppnamn,ou=grupper,dc=exempel,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'Gruppbas DN',
            'help' => 'Särskilt namn för att söka efter grupper Exempel: ou=grupp,dc=exempel,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Gruppmedlemsattribut',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Hitta gruppmedlemmar efter',
            'options' => [
                'username' => 'Användarnamn',
                'fulldn' => 'Fullständig DN (med prefix och suffix)',
                'puredn' => 'DN-sökning (sök med uid-attribut)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Gruppåtkomst',
            'help' => 'Definiera grupper som har åtkomst och nivå',
        ],
        'auth_ldap_require_groupmembership' => [
            'description' => 'Verifiering av medlemskap i LDAP-gruppen',
            'help' => 'Utför (eller hoppa över) ldap_compare när leverantören tillåter (eller inte gör det) för åtgärden Jämför.',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP-port',
            'help' => 'Port att ansluta till servrar på. För LDAP ska det vara 389, för LDAPS ska det vara 636',
        ],
        'auth_ldap_prefix' => [
            'description' => 'Användarprefix',
            'help' => 'Används för att förvandla ett användarnamn till ett framstående namn',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP-server(ar)',
            'help' => 'Ställ in server(ar), utrymme separerat. Prefix med ldaps:// för ssl',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Använd STARTTLS',
            'help' => 'Använd STARTTLS för att säkra anslutningen.  Alternativ till LDAPS.',
            'options' => [
                'disabled' => 'Inaktiverad',
                'optional' => 'Valfritt',
                'required' => 'Obligatoriskt',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'Användarsuffix',
            'help' => 'Används för att förvandla ett användarnamn till ett framstående namn',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Timeout för anslutning',
            'help' => 'Om en eller flera servrar inte svarar, kommer högre timeouts att orsaka långsam åtkomst. För låg kan orsaka anslutningsfel i vissa fall',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Unikt ID-attribut',
            'help' => 'LDAP-attribut som ska användas för att identifiera användare måste vara numeriskt',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Använd fullständigt användar-DN',
            'help' => 'Använder en användares fullständiga DN som värdet på medlemsattributet i en grupp istället för medlem: användarnamn med prefix och suffix. (det är medlem: uid=användarnamn,ou=grupper,dc=domän,dc=com)',
        ],
        'auth_ldap_userlist_filter' => [
            'description' => 'Anpassat LDAP-användarfilter',
            'help' => 'Anpassat ldap-filter för att begränsa antalet svar om du har en ldap-katalog med tusentals användare',
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => 'Jokerteckenanvändares OU',
            'help' => 'Sök efter användarnamn som matchar användarnamn oberoende av OU som anges i användarsuffixet. Användbart om dina användare är i olika organisationsenheter. Bind användarnamn, om angivet, fortfarande användarsuffix',
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP-version',
            'help' => 'LDAP-version att använda för att prata med servern.  Vanligtvis bör detta vara v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Auktoriseringsmetod (varning!)',
            'help' => 'Auktoriseringsmetod.  Varning, du kan förlora möjligheten att logga in. Du kan åsidosätta detta tillbaka till mysql genom att ställa in $config[\'auth_mechanism\'] = \'mysql\'; i din config.php',
            'options' => [
                'mysql' => 'MySQL (standard)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radie',
                'http-auth' => 'HTTP-autentisering',
                'ad-authorization' => 'Externt autentiserat AD',
                'ldap-authorization' => 'Externt autentiserad LDAP',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Kom ihåg mig varaktighet',
            'help' => 'Antal dagar för att hålla en användare inloggad när du markerar kryssrutan kom ihåg mig vid inloggning.',
        ],
        'authlog_purge' => [
            'description' => 'Auth-loggposter äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'availablity' => [
            'threshold_ok' => [
                'description' => 'Tillgänglighet Ok Tröskel',
                'help' => 'Tröskel för grön färg',
            ],
            'threshold_warning' => [
                'description' => 'Tillgänglighetsvarningströskel',
                'help' => 'Tröskel för orange färg',
            ],
        ],
        'bad_entity_sensor_regex' => [
            'description' => 'Dålig Entity Sensor Regex',
            'help' => 'Regex för att matcha dåliga enhetssensorer, dessa kommer inte att visas i webbgränssnittet.',
        ],
        'billing' => [
            '95th_default_agg' => [
                'description' => 'Standardaggregering för 95:e percentilen',
                'help' => 'Ange standardalternativ för aggregerad beräkning av den 95:e percentilen.',
            ],
        ],
        'enable_billing' => [
            'description' => 'Aktivera fakturering',
            'help' => 'Aktivera faktureringsmodul, detta låter dig övervaka portanvändning.',
        ],
        'peering_descr' => [
            'description' => 'Peering-porttyper',
            'help' => 'Portar av de listade beskrivningstyperna kommer att visas under menyposten peering-portar.  Se Gränssnittsbeskrivning Parsning av dokument för mer information.',
        ],
        'transit_descr' => [
            'description' => 'Transitporttyper',
            'help' => 'Hamnar av den eller de angivna beskrivningstyperna kommer att visas under menyposten transithamnar.  Se Gränssnittsbeskrivning Parsning av dokument för mer information.',
        ],
        'collectd_dir' => [
            'description' => 'Samlad katalog',
            'help' => 'Katalog där collectd lagrar sina RRD-filer.  Detta används för att visa data från insamlad i LibreNMS.',
        ],
        'collectd_sock' => [
            'description' => 'Collectd-socket',
            'help' => 'Socket collectd lyssnar på.  Detta används för att visa data från insamlad i LibreNMS.',
        ],
        'core_descr' => [
            'description' => 'Kärnporttyper',
            'help' => 'Portar av den eller de angivna beskrivningstyperna kommer att visas under menyposten för kärnportar.  Se Gränssnittsbeskrivning Parsning av dokument för mer information.',
        ],
        'custom_descr' => [
            'description' => 'Anpassade porttyper',
            'help' => 'Portar av den eller de angivna beskrivningstyperna kommer att visas under menyposten för anpassade portar.  Se Gränssnittsbeskrivning Parsning av dokument för mer information.',
        ],
        'custom_map' => [
            'background_type' => [
                'description' => 'Bakgrundstyp',
                'help' => 'Standardbakgrundstyp för nya kartor. Kräver bakgrundsdatauppsättning.',
            ],
            'background_data' => [
                'color' => [
                    'description' => 'Bakgrundsfärg',
                    'help' => 'Inledande färg för kartbakgrund',
                ],
                'lat' => [
                    'description' => 'Bakgrundskarta Lattitude',
                    'help' => 'Initial latitud för bakgrundskarta',
                ],
                'lng' => [
                    'description' => 'Bakgrundskarta Longitud',
                    'help' => 'Initial longitud för bakgrundskarta',
                ],
                'layer' => [
                    'description' => 'Bakgrundskartlager',
                    'help' => 'Inledande kartlager för geokarta i bakgrunden',
                ],
                'zoom' => [
                    'description' => 'Bakgrundskarta Zoom',
                    'help' => 'Initial kartzoom för bakgrundskarta',
                ],
            ],
            'edge_font_color' => [
                'description' => 'Kanttextfärg',
                'help' => 'Standard teckensnittsfärg för kantetiketter',
            ],
            'edge_font_face' => [
                'description' => 'Edge Font',
                'help' => 'Standardteckensnitt för kantetiketter',
            ],
            'edge_font_size' => [
                'description' => 'Kanttextstorlek',
                'help' => 'Standard teckenstorlek för kantetiketter',
            ],
            'edge_seperation' => [
                'description' => 'Kantseparation',
                'help' => 'Standard kantseparation för nya kartor',
            ],
            'height' => [
                'description' => 'Karthöjd',
                'help' => 'Standard karthöjd för nya kartor',
            ],
            'node_align' => [
                'description' => 'Nodjustering',
                'help' => 'Standardnodjustering för nya kartor',
            ],
            'node_background' => [
                'description' => 'Nodbakgrund',
                'help' => 'Standardbakgrundsfärg för nodetiketter',
            ],
            'node_border' => [
                'description' => 'Nodgräns',
                'help' => 'Standard kantfärg för nodetiketter',
            ],
            'node_font_color' => [
                'description' => 'Nodtextfärg',
                'help' => 'Standard teckensnittsfärg för nodetiketter',
            ],
            'node_font_face' => [
                'description' => 'Nodfont',
                'help' => 'Standardteckensnitt för nodetiketter',
            ],
            'node_font_size' => [
                'description' => 'Nodtextstorlek',
                'help' => 'Standard teckenstorlek för nodetiketter',
            ],
            'node_size' => [
                'description' => 'Nodstorlek',
                'help' => 'Standardstorlek för noder',
            ],
            'node_type' => [
                'description' => 'Nodvisningstyp',
                'help' => 'Standardvisningstyp för noder',
            ],
            'reverse_arrows' => [
                'description' => 'Omvänd kantpilar',
                'help' => 'Standard pilriktning. Mot mitten (standard) eller mot ändarna',
            ],
            'width' => [
                'description' => 'Kartbredd',
                'help' => 'Standard kartbredd för nya kartor',
            ],
        ],
        'customers_descr' => [
            'description' => 'Kundporttyper',
            'help' => 'Portar av de angivna beskrivningstyperna kommer att visas under menyposten för kundens portar.  Se Gränssnittsbeskrivning Parsning av dokument för mer information.',
        ],
        'base_url' => [
            'description' => 'Bas-URL',
            'help' => 'Detta bör *bara* ställas in om du vill *tvinga* ett visst värdnamn/port. Det kommer att förhindra att webbgränssnittet kan användas från något annat värdnamn',
        ],
        'disabled_sensors' => [
            'description' => 'Inaktiverade sensorer',
            'help' => 'Sensorer som inte ska pollas eller visas i webbgränssnittet.',
        ],
        'disabled_sensors_regex' => [
            'description' => 'Reguljärt uttryck för inaktiverade sensorer',
            'help' => 'Sensorer som matchar detta regex kommer inte att pollas eller visas i webbgränssnittet.',
        ],
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'ARP bord',
            ],
            'applications' => [
                'description' => 'Ansökningar',
            ],
            'bgp-peers' => [
                'description' => 'BGP-kamrater',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'mac-accounting' => [
                'description' => 'MAC bokföring',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'slas' => [
                'description' => 'Spårning av servicenivåavtal',
            ],
            'cisco-pw' => [
                'description' => 'Cisco PW',
            ],
            'cisco-vrf-lite' => [
                'description' => 'Cisco VRF Lite',
            ],
            'discovery-arp' => [
                'description' => 'Discovery ARP',
            ],
            'discovery-protocols' => [
                'description' => 'Discovery Protocols',
            ],
            'entity-physical' => [
                'description' => 'Entitet Fysisk',
            ],
            'entity-state' => [
                'description' => 'Entitetsstat',
            ],
            'fdb-table' => [
                'description' => 'FDB-tabell',
            ],
            'hr-device' => [
                'description' => 'HR-enhet',
            ],
            'ipv4-addresses' => [
                'description' => 'IPv4-adresser',
            ],
            'ipv6-addresses' => [
                'description' => 'IPv6-adresser',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'loadbalancers' => [
                'description' => 'Lastbalanserare',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mempools' => [
                'description' => 'Mempools',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'os' => [
                'description' => 'OS',
            ],
            'ports' => [
                'description' => 'Hamnar',
            ],
            'ports-stack' => [
                'description' => 'Ports Stack',
            ],
            'processors' => [
                'description' => 'Processorer',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'route' => [
                'description' => 'Rutt',
            ],
            'sensors' => [
                'description' => 'Sensorer',
            ],
            'services' => [
                'description' => 'Tjänster',
            ],
            'storage' => [
                'description' => 'Förvaring',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'vlans' => [
                'description' => 'VLAN',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM Info',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => 'Trådlös',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => 'Tillbehör för skrivare',
            ],
        ],
        'distributed_poller' => [
            'description' => 'Aktivera distribuerad polling (kräver ytterligare inställningar)',
            'help' => 'Aktivera distribuerat avfrågningssystem. Detta är avsett för lastdelning, inte för fjärrpolling. Du måste läsa dokumentationen för steg för att aktivera: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => 'Standard pollergrupp',
            'help' => 'Den förinställda pollergruppen ska alla pollare polla om ingen är inställd i config.php',
        ],
        'device_traffic_iftype' => [
            'description' => 'Typer av enhetstrafikgränssnitt',
            'help' => 'Gränssnittstyper ska uteslutas från enhetsdiagram.',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached-värd',
            'help' => 'Värdnamnet eller IP-adressen till Memcached-servern. Detta krävs för låsning i poller_wrapper.py och daily.sh.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached-port',
            'help' => 'Porten för Memcached-servern. Standardvärdet är 11211.',
        ],
        'enable_ports_etherlike' => [
            'description' => 'Aktivera eterliknande grafer för portar',
        ],
        'email_auto_tls' => [
            'description' => 'Stöd för automatisk TLS',
            'help' => 'Försöker använda TLS innan den går tillbaka till okrypterad',
        ],
        'email_smtp_verifypeer' => [
            'description' => 'Verifiera peer-certifikat',
            'help' => 'Verifiera inte peer-certifikat när du ansluter till SMTP-server via TLS',
        ],
        'email_smtp_allowselfsigned' => [
            'description' => 'Tillåt självsignerat certifikat',
            'help' => 'Tillåt självsignerat certifikat när du ansluter till SMTP-server via TLS',
        ],
        'email_attach_graphs' => [
            'description' => 'Bifoga grafbilder',
            'help' => 'Detta kommer att generera en graf när varningen höjs och bifoga den och bädda in den i e-postmeddelandet.',
        ],
        'email_backend' => [
            'description' => 'Hur man levererar post',
            'help' => 'Backend att använda för att skicka e-post, kan vara mail, sendmail eller SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'skicka mail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'Från e-postadress',
            'help' => 'E-postadress som används för att skicka e-post (från)',
        ],
        'email_html' => [
            'description' => 'Använd HTML-e-postmeddelanden',
            'help' => 'Skicka HTML-e-post',
        ],
        'email_sendmail_path' => [
            'description' => 'Sökväg till binär sendmail',
        ],
        'email_smtp_auth' => [
            'description' => 'SMTP-autentisering',
            'help' => 'Aktivera detta om din SMTP-server kräver autentisering',
        ],
        'email_smtp_host' => [
            'description' => 'SMTP-server',
            'help' => 'IP- eller dns-namn för SMTP-servern att leverera e-post till',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP Auth lösenord',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP-portinställning',
        ],
        'email_smtp_secure' => [
            'description' => 'Kryptering',
            'options' => [
                '' => 'Inaktiverad',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP timeout inställning',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP Auth användarnamn',
        ],
        'email_user' => [
            'description' => 'Från namn',
            'help' => 'Namn som används som en del av från-adressen',
        ],
        'enable_clear_discovery' => [
            'description' => 'Aktivera Clear Discovery',
            'help' => 'Möjliggör möjligheten att rensa upptäcktsdatum och tid för en enhet. Detta kommer att tvinga fram en återupptäckt av enheten.',
        ],
        'enable_inventory' => [
            'description' => 'Aktivera inventering',
            'help' => 'Aktiverar inventeringssidan, som visar maskinvaruinventeringen av enheter.',
        ],
        'enable_lazy_load' => [
            'description' => 'Aktivera Lazy Loading',
            'help' => 'Lazy loading används för att påskynda laddningen av sidor genom att bara ladda den data som behövs vid tillfället. Detta kan inaktiveras om du har problem med det.',
        ],
        'enable_libvirt' => [
            'description' => 'Aktivera Libvirt',
            'help' => 'Aktiverar sidan libvirt, som visar enheters virtuella maskiner.',
        ],
        'enable_proxmox' => [
            'description' => 'Aktivera Proxmox',
            'help' => 'Aktiverar Proxmox-sidan, som visar enheters virtuella maskiner.',
        ],
        'enable_pseudowires' => [
            'description' => 'Aktivera Pseudowires',
            'help' => 'Aktiverar sidan pseudotrådar, som visar enheters pseudotrådar.',
        ],
        'enable_syslog' => [
            'description' => 'Aktivera Syslog',
            'help' => 'Aktiverar synlighet för syslog inom webbgränssnittet.',
        ],
        'eventlog_purge' => [
            'description' => 'Händelseloggposter äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'favicon' => [
            'description' => 'Favoritikon',
            'help' => 'Åsidosätter standardfavicon.',
        ],
        'front_page' => [
            'description' => 'Första sidan',
            'help' => 'Ställ in en anpassad framsida, det här är sidan du ser när du loggar in första gången. Om du till exempel skapar `resources/views/overview/custom/foobar.blade.php`, ställ in `front_page` till `foobar`',
        ],
        'front_page_down_box_limit' => [
            'description' => 'Nedanstående enhetsgräns',
            'help' => 'Antal enheter att visa i nedrutan på framsidan',
        ],
        'front_page_settings' => [
            'top_devices' => [
                'description' => 'Toppenheter',
                'help' => 'Antal toppenheter att visa på förstasidan',
            ],
            'top_ports' => [
                'description' => 'Bästa hamnar',
                'help' => 'Antal toppportar att visa på framsidan',
            ],
        ],
        'fping' => [
            'description' => 'Vägen till fping',
        ],
        'fping6' => [
            'description' => 'Sökväg till fping6',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping räkning',
                'help' => 'Antalet ping som ska skickas när man kontrollerar om en värd är uppe eller nere via icmp',
            ],
            'interval' => [
                'description' => 'fping-intervall',
                'help' => 'Antalet millisekunder att vänta mellan pingarna',
            ],
            'timeout' => [
                'description' => 'fping timeout',
                'help' => 'Antalet millisekunder att vänta på ett ekosvar innan du ger upp',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Mapping Engine API-nyckel',
                'help' => 'Geocoding API Key (krävs för att fungera)',
            ],
            'dns' => [
                'description' => 'Använd DNS-platspost',
                'help' => 'Använd LOC Record från DNS Server för att få geografiska koordinater för värdnamn',
            ],
            'engine' => [
                'description' => 'Mapping Engine',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing kartor',
                    'esri' => 'ESRI ArcGIS',
                ],
            ],
            'latlng' => [
                'description' => 'Försök att geokoda platser',
                'help' => 'Försök att slå upp latitud och longitud via geokodnings-API under polling',
            ],
            'layer' => [
                'description' => 'Inledande kartlager',
                'help' => 'Inledande kartlager att visa. *Alla lager är inte tillgängliga för alla mappningsmotorer.',
                'options' => [
                    'Streets' => 'Gator',
                    'Sattelite' => 'Satellit',
                    'Topography' => 'Topografi',
                ],
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till grafit',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'IP- eller värdnamnet för grafitservern att skicka data till',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Porten som ska användas för att ansluta till Graphite-servern',
            ],
            'prefix' => [
                'description' => 'Prefix (valfritt)',
                'help' => 'Lägger till prefixet i början av alla mätvärden.  Måste vara alfanumeriskt separerade med punkter',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => 'Varaktighet',
                'help' => 'Beräkna enhetstillgänglighet för angivna varaktigheter. (Längden definieras i sekunder)',
            ],
            'availability_consider_maintenance' => [
                'description' => 'Schemalagt underhåll påverkar inte tillgängligheten',
                'help' => 'Inaktiverar skapandet av avbrott och minskad tillgänglighet för enheter som är i underhållsläge.',
            ],
        ],
        'graphs' => [
            'row' => [
                'normal' => [
                    'options' => [
                        'sixhour' => '6 timmar',
                        'day' => '24 timmar',
                        'twoday' => '48 timmar',
                        'week' => '1 vecka',
                        'twoweek' => '2 veckor',
                        'month' => '1 månad',
                        'twomonth' => '2 månader',
                        'year' => '1 år',
                        'twoyear' => '2 år',
                    ],
                ],
            ],
            'port_speed_zoom' => [
                'description' => 'Zooma portgrafer till porthastighet',
                'help' => 'Zooma portgrafer så att max alltid är porthastigheten, inaktiverade portgrafer zoomar till trafik',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'Bas-URI',
                'help' => 'Åsidosätt bas-uri om du har ändrat Graylog-standarden.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Enhetsöversikt Loggnivå',
                    'help' => 'Ställer in den maximala loggnivån som visas på enhetens översiktssida.',
                ],
                'rowCount' => [
                    'description' => 'Enhetsöversikt Antal rader',
                    'help' => 'Ställer in antalet rader som ska visas på enhetens översiktssida.',
                ],
            ],
            'password' => [
                'description' => 'Lösenord',
                'help' => 'Lösenord för åtkomst till Graylog API.',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Porten som används för att komma åt Graylog API. Om ingen ger blir det 80 för http och 443 för https.',
            ],
            'server' => [
                'description' => 'Server',
                'help' => 'IP-adressen eller värdnamnet för Graylog-serverns API-slutpunkt.',
            ],
            'timezone' => [
                'description' => 'Visa tidszon',
                'help' => 'Graylog-tider lagras i GMT, denna inställning kommer att ändra den visade tidszonen. Värdet måste vara en giltig PHP-tidszon.',
            ],
            'username' => [
                'description' => 'Användarnamn',
                'help' => 'Användarnamn för åtkomst till Graylog API.',
            ],
            'version' => [
                'description' => 'Version',
                'help' => 'Detta används för att automatiskt skapa base_uri för Graylog API. Om du har ändrat API-uri från standardinställningen, ställ in denna till annan och ange din base_uri.',
            ],
            'query' => [
                'field' => [
                    'description' => 'Fråga api-fält',
                    'help' => 'Ändrar standardfältet för att fråga efter graylog API.',
                ],
            ],
            'match-any-address' => [
                'description' => 'Matcha vilken adress som helst',
                'help' => 'Detta används för att matcha vilken adress som helst för en enhet med källan till ett greylog-loggmeddelande, som standard används endast den primära adressen',
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => 'Primär rullgardinslänk',
                    'help' => 'Ställer in den primära länken i enhetsrullgardinsmenyn',
                ],
            ],
        ],
        'http_auth_header' => [
            'description' => 'Fältnamn som innehåller användarnamn',
            'help' => 'Kan vara ett ENV- eller HTTP-huvudfält som REMOTE_USER, PHP_AUTH_USER eller en anpassad variant',
        ],
        'http_auth_guest' => [
            'description' => 'Http Auth gästanvändare',
            'help' => 'Om inställt, tillåter alla http-användare att autentisera och tilldelar okända användare att ge lokalt användarnamn',
        ],
        'http_proxy' => [
            'description' => 'HTTP-proxy',
            'help' => 'Ställ in detta som en reserv om miljövariabeln http_proxy inte är tillgänglig.',
        ],
        'https_proxy' => [
            'description' => 'HTTPS-proxy',
            'help' => 'Ställ in detta som en reserv om miljövariabeln https_proxy inte är tillgänglig.',
        ],
        'icmp_check' => [
            'description' => 'ICMP-kontroll',
            'help' => 'Aktivera ICMP-kontroll för alla enheter globalt, detta kommer att pinga enheter för att kontrollera om de är uppe eller nere. Om du inaktiverar detta kan det leda till att omröstningen inte slutförs i tid.',
        ],
        'ignore_mount' => [
            'description' => 'Monteringspunkter som ska ignoreras',
            'help' => 'Övervaka inte skivanvändningen av dessa monteringspunkter',
        ],
        'ignore_mount_network' => [
            'description' => 'Ignorera nätverksmonteringspunkter',
            'help' => 'Övervaka inte skivanvändning av nätverksmonteringspunkter',
        ],
        'ignore_mount_optical' => [
            'description' => 'Ignorera optiska enheter',
            'help' => 'Övervaka inte skivanvändningen av optiska enheter',
        ],
        'ignore_mount_removable' => [
            'description' => 'Ignorera flyttbara enheter',
            'help' => 'Övervaka inte skivanvändningen av flyttbara enheter',
        ],
        'ignore_mount_regexp' => [
            'description' => 'Monteringspunkter som matchar Regex ska ignoreras',
            'help' => 'Övervaka inte skivanvändning av monteringspunkter som matchar minst ett av dessa reguljära uttryck',
        ],
        'ignore_mount_string' => [
            'description' => 'Mountpoints som innehåller String som ska ignoreras',
            'help' => 'Övervaka inte skivanvändning av monteringspunkter som innehåller minst en av dessa strängar',
        ],
        'influxdb' => [
            'db' => [
                'description' => 'Databas',
                'help' => 'Namn på InfluxDB-databasen för att lagra mätvärden',
            ],
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till InfluxDB',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'IP-adressen eller värdnamnet för InfluxDB-servern att skicka data till',
            ],
            'password' => [
                'description' => 'Lösenord',
                'help' => 'Lösenord för att ansluta till InfluxDB, om det behövs',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Porten som ska användas för att ansluta till InfluxDB-servern',
            ],
            'timeout' => [
                'description' => 'Timeout',
                'help' => 'Hur länge man ska vänta på InfluxDB-server, 0 betyder standard timeout',
            ],
            'transport' => [
                'description' => 'Transport',
                'help' => 'Porten som ska användas för att ansluta till InfluxDB-servern',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDPRRRRRRR',
                ],
            ],
            'username' => [
                'description' => 'Användarnamn',
                'help' => 'Användarnamn för att ansluta till InfluxDB, om det behövs',
            ],
            'batch_size' => [
                'description' => 'Batchstorlek',
                'help' => 'Antal mätvärden att skicka i en enda batch, 0 betyder ingen batchning',
            ],
            'measurements' => [
                'description' => 'Mått',
                'help' => 'Lista över mätningar att skicka till InfluxDB, lämna tom för att skicka alla',
            ],
            'verifySSL' => [
                'description' => 'Verifiera SSL',
                'help' => 'Kontrollera att SSL-certifikatet är giltigt och pålitligt',
            ],
            'debug' => [
                'description' => 'Felsök',
                'help' => 'För att aktivera eller inaktivera utförlig utmatning till CLI',
            ],
        ],
        'influxdbv2' => [
            'bucket' => [
                'description' => 'hink',
                'help' => 'Namn på InfluxDB Bucket för att lagra mätvärden',
            ],
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till InfluxDB med hjälp av InfluxDBv2 API',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'IP-adressen eller värdnamnet för InfluxDB-servern att skicka data till',
            ],
            'token' => [
                'description' => 'Token',
                'help' => 'Token för att ansluta till InfluxDB, om det behövs',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Porten som ska användas för att ansluta till InfluxDB-servern',
            ],
            'transport' => [
                'description' => 'Transport',
                'help' => 'Porten som ska användas för att ansluta till InfluxDB-servern',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                ],
            ],
            'organization' => [
                'description' => 'Organisation',
                'help' => 'Organisationen som innehåller hinken på InfluxDB-servern',
            ],
            'allow_redirects' => [
                'description' => 'Tillåt omdirigeringar',
                'help' => 'För att tillåta omdirigering från InfluxDB-servern',
            ],
            'debug' => [
                'description' => 'Felsök',
                'help' => 'För att aktivera eller inaktivera utförlig utmatning till CLI',
            ],
            'log_file' => [
                'description' => 'Loggfil',
                'help' => 'Definiera en annan loggfil om så önskas för felsökningen',
            ],
            'groups-exclude' => [
                'description' => 'Uteslutna enhetsgrupper',
                'help' => 'Enhetsgrupper uteslutna från att skicka data till InfluxDBv2',
            ],
            'timeout' => [
                'description' => 'Timeout',
                'help' => 'Timeout på sekunder',
            ],
            'verify' => [
                'description' => 'Verifiera',
                'help' => 'Verifiera certifikatet',
            ],
            'batch_size' => [
                'description' => 'Batchstorlek',
                'help' => 'Hur många mätvärden ska paketeras innan du skickar',
            ],
            'max_retry' => [
                'description' => 'Max försök igen',
                'help' => 'Hur många reties ska vi prova',
            ],
        ],
        'kafka' => [
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till Kafka med hjälp av idealo/php-rdkafka-ffi',
            ],
            'groups-exclude' => [
                'description' => 'Utesluten enhetsgrupp-id',
                'help' => 'Id för enhetsgrupper uteslutna från att skicka data till Kafka',
            ],
            'measurement-exclude' => [
                'description' => 'Uteslutna mått',
                'help' => 'Discovery-moduler ska uteslutas från att skicka till kafka',
            ],
            'debug' => [
                'description' => 'Felsök',
                'help' => 'Aktivera detaljerade loggar om intern kafka-butiksprocess',
            ],
            'security' => [
                'debug' => [
                    'description' => 'Säkerhetsfelsökning',
                    'help' => 'Visa mer detaljerad information om säkerhetskommunikation med Kafka-mäklare',
                ],
            ],
            'broker' => [
                'list' => [
                    'description' => 'Lista över Kafka Brokers servrar i värdformat!:port',
                    'help' => 'Lista över kafka-mäklare i värdformat!:port. https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md',
                ],
            ],
            'idempotence' => [
                'description' => 'Idempotens',
                'help' => 'När den är inställd på sant kommer producenten att se till att meddelanden produceras exakt en gång och i den ursprungliga produktionsordningen',
            ],
            'topic' => [
                'description' => 'Ämne',
                'help' => 'Kategorierna som används för att organisera meddelanden',
            ],
            'ssl' => [
                'enable' => [
                    'description' => 'SSL aktivera',
                    'help' => 'Aktivera SSL-stöd i Kafka',
                ],
                'protocol' => [
                    'description' => 'SSL-protokoll',
                    'help' => 'Protokoll som används för att kommunicera med mäklare',
                ],
                'ca' => [
                    'location' => [
                        'description' => 'Plats för SSL-certifikatutfärdare',
                        'help' => 'Fil- eller katalogsökväg till CA-certifikat för att verifiera mäklarens nyckel.',
                    ],
                ],
                'certificate' => [
                    'location' => [
                        'description' => 'Plats för SSL-certifikat',
                        'help' => 'Sökväg till klientens publika nyckel (PEM) som används för autentisering.',
                    ],
                ],
                'key' => [
                    'location' => [
                        'description' => 'Plats för SSL-certifikatnyckel',
                        'help' => 'Sökväg till klientens privata nyckel (PEM) som används för autentisering.',
                    ],
                    'password' => [
                        'description' => 'Lösenord för SSL-certifikatnyckel',
                        'help' => 'Privat nyckellösenord (att användas med kafka.ssl.key.location).',
                    ],
                ],
                'keystore' => [
                    'location' => [
                        'description' => 'Plats för SSL Keystore-certifikat',
                        'help' => 'Sökväg till klientens nyckellager (PKCS#12) som används för autentisering.',
                    ],
                    'password' => [
                        'description' => 'SSL Keystore Key Lösenord',
                        'help' => 'Klientens nyckellager (PKCS#12) lösenord.',
                    ],
                ],
            ],
            'flush' => [
                'timeout' => [
                    'description' => 'Kafka Flush Timeout',
                    'help' => 'Kafka vänta denna timeout för att spola meddelanden i kön',
                ],
            ],
            'buffer' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka buffert det maximala antalet meddelanden som lagras i pollarens minne',
                        'help' => 'Kafka buffert det maximala antalet tillåtna meddelanden som lagras i pollerminnet',
                    ],
                ],
            ],
            'batch' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka batch maximalt antal meddelanden som skickas varje samtal till kafka-servrar',
                        'help' => 'Kafka batch maximalt antal meddelanden som skickas varje samtal till kafka-servrar',
                    ],
                ],
            ],
            'linger' => [
                'ms' => [
                    'description' => 'Kafka väntetid i ms för att samla meddelanden i pollerminnet innan partiet skickas',
                    'help' => 'Kafka väntetid i ms för att samla meddelanden i pollerminnet innan partiet skickas',
                ],
            ],
            'request' => [
                'required' => [
                    'acks' => [
                        'description' => 'Kafka begäran krävde acks',
                        'help' => 'Kafka begäran krävde acks',
                    ],
                ],
            ],
        ],
        'int_core' => [
            'description' => 'Aktivera Core Ports-menyn',
            'help' => 'Aktivera menyn för kärnportar i webbgränssnittet',
        ],
        'int_customers' => [
            'description' => 'Aktivera menyn Kundportar',
            'help' => 'Aktivera kunders portmeny i webbgränssnittet',
        ],
        'int_peering' => [
            'description' => 'Aktivera Peering Ports-menyn',
            'help' => 'Aktivera peering-portar-menyn i webbgränssnittet',
        ],
        'int_transit' => [
            'description' => 'Aktivera menyn Transitportar',
            'help' => 'Aktivera menyn för transitportar i webbgränssnittet',
        ],
        'int_l2tp' => [
            'description' => 'Aktivera L2TP-portar-menyn',
            'help' => 'Aktivera L2TP-portar-menyn i webbgränssnittet',
        ],
        'ipmitool' => [
            'description' => 'Sökväg till ipmtool',
        ],
        'ipmi.type' => [
            'description' => 'IPMI typ',
            'help' => 'Typ av IPMI att använda, kan vara "lan", "lanplus", "öppen", "sol", "rå" eller "skal".',
        ],
        'ipmi_unit' => [
            'description' => 'IPMI-enhet',
            'help' => 'IPMI-enhetstyper som kan upptäckas.',
        ],
        'libvirt_protocols' => [
            'description' => 'Libvirt-protokoll',
            'help' => 'Protokoll att använda för libvirt-anslutningar.',
        ],
        'libvirt_username' => [
            'description' => 'Libvirt Användarnamn',
            'help' => 'Användarnamn att använda för libvirt-anslutningar.',
        ],
        'location_map' => [
            'description' => 'Specifik platskarta',
            'help' => 'Mappa ett sysLocation-värde till ett annat värde.',
        ],
        'location_map_regex' => [
            'description' => 'Specifik platskarta med regex',
            'help' => 'Mappa ett sysLocation-värde till ett annat värde med hjälp av regex.',
        ],
        'location_map_regex_sub' => [
            'description' => 'Specifik platskarta med regex-ersättning',
            'help' => 'Ersätt sysLocation-värdet med regex-substitution.',
        ],
        'login_message' => [
            'description' => 'Inloggningsmeddelande',
            'help' => 'Visas på inloggningssidan',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => 'Aktivera MAC OUI-uppslagning',
                'help' => 'Aktivera uppslagning av mac-adressleverantör (OUI) (data laddas ner av daily.sh)',
            ],
        ],
        'mono_font' => [
            'description' => 'Monospaced teckensnitt',
        ],
        'mtr' => [
            'description' => 'Väg till mtr',
        ],
        'mtu_options' => [
            'bytes' => [
                'description' => 'MTU-testpaketstorlek',
                'help' => 'Storlek på paket för MTU-test i byte (tomt för att inaktivera MTU-tester)',
            ],
        ],
        'mydomain' => [
            'description' => 'Primär domän',
            'help' => 'Den här domänen används för automatisk upptäckt av nätverk och andra processer. LibreNMS kommer att försöka lägga till det till okvalificerade värdnamn.',
        ],
        'network_map_show_on_worldmap' => [
            'description' => 'Visa nätverkslänkar på kartan',
            'help' => 'Visa nätverkslänkarna mellan de olika platserna på världskartan (liknande väderkarta)',
        ],
        'network_map_worldmap_show_disabled_alerts' => [
            'description' => 'Visa enheter med varningar inaktiverade',
            'help' => 'Visa enheter på nätverkskartan som har varningar inaktiverade',
        ],
        'network_map_worldmap_link_type' => [
            'description' => 'Nätverkskartkälla',
            'help' => 'Välj datakällan för nätverkskartlänkarna',
        ],
        'nfsen_enable' => [
            'description' => 'Aktivera NfSen',
            'help' => 'Aktivera integration med NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD-kataloger',
            'help' => 'Detta värde anger var dina NFSen RRD-filer finns.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Ställ in NfSen subdir layout',
            'help' => 'Detta måste matcha underkataloglayouten du har ställt in i NfSen. 1 är standard.',
        ],
        'nfsen_last_max' => [
            'description' => 'Sista Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Max topN-värde för statistik',
        ],
        'nfsen_top_N' => [
            'description' => 'Topp N',
        ],
        'nfsen_top_default' => [
            'description' => 'Standard Top N',
        ],
        'nfsen_stats_default' => [
            'description' => 'Standardstat',
        ],
        'nfsen_order_default' => [
            'description' => 'Standardordning',
        ],
        'nfsen_last_default' => [
            'description' => 'Standard sista',
        ],
        'nfsen_lasts' => [
            'description' => 'Standard Senaste alternativ',
        ],
        'nfsen_base' => [
            'description' => 'NFSen Base Directory',
            'help' => 'Används för att lokalisera enhetsspecifika grafer',
        ],
        'nfsen_split_char' => [
            'description' => 'Split Char',
            'help' => 'Detta värde talar om för oss vad vi ska ersätta punkterna `.` i enhetens värdnamn med. Vanligtvis: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'Filnamnssuffix',
            'help' => 'Detta är en mycket viktig bit eftersom enhetsnamn i NfSen är begränsade till 21 tecken. Detta innebär att fullständiga domännamn för enheter kan vara mycket problematiska att klämma in, så därför tas denna bit vanligtvis bort.',
        ],
        'no_proxy' => [
            'description' => 'Proxy-undantag',
            'help' => 'Ställ in detta som en reserv om miljövariabeln no_proxy inte är tillgänglig. Kommaseparerad lista över IP-adresser, värdar eller domäner att ignorera.',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till OpenTSDB',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'IP-adressen eller värdnamnet för OpenTSDB-servern att skicka data till',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Porten som ska användas för att ansluta till OpenTSDB-servern',
            ],
        ],
        'overview_show_sysDescr' => [
            'description' => 'Visa sysDescr på enhetsöversikt',
            'help' => 'Visa sysDescr på enhetens översiktssida',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS värdnamn',
            'help' => 'Bör ställas in på värdnamnet/ip librenms-servern läggs till som',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Ställ in standardgruppen som returneras',
            ],
            'ignore_groups' => [
                'description' => 'Säkerhetskopiera inte dessa oxiderade grupper',
                'help' => 'Grupper (inställd via Variable Mapping) exkluderade från att skickas till Oxidized',
            ],
            'enabled' => [
                'description' => 'Aktivera oxiderat stöd',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Aktivera åtkomst till konfigurationsversion',
                    'help' => 'Aktivera oxiderad konfigurationsversionering (kräver git-backend)',
                ],
            ],
            'group_support' => [
                'description' => 'Aktivera återgång av grupper till Oxidized',
            ],
            'ignore_os' => [
                'description' => 'Säkerhetskopiera inte dessa operativsystem',
                'help' => 'Säkerhetskopiera inte det angivna operativsystemet med Oxidized.  OS måste matcha LibreNMS OS-namnet (dessa är alla gemener utan mellanslag).  Tillåter endast befintligt operativsystem.',
            ],
            'ignore_types' => [
                'description' => 'Säkerhetskopiera inte dessa enhetstyper',
                'help' => 'Säkerhetskopiera inte de listade enhetstyperna med Oxidized. Tillåter endast befintliga typer.',
            ],
            'reload_nodes' => [
                'description' => 'Ladda om listan med oxiderade noder, varje gång en enhet läggs till',
            ],
            'maps' => [
                'description' => 'Variabel mappning',
                'help' => 'Används för att ställa in grupp- eller andra variabler eller mappa OS-namn som skiljer sig.',
            ],
            'url' => [
                'description' => 'URL till ditt Oxidized API',
                'help' => 'Oxiderad API-url (till exempel: http://127.0.0.1:8888)',
            ],
        ],
        'page_refresh' => [
            'description' => 'Uppdatera sidan',
            'help' => 'Hur ofta du ska uppdatera sidan på några sekunder. Ställ in på 0 för att inaktivera.',
        ],
        'password' => [
            'min_length' => [
                'description' => 'Minsta lösenordslängd',
                'help' => 'Lösenord som är kortare än den angivna längden kommer att avvisas',
            ],
            'uncompromised' => [
                'description' => 'Kräv lösenord för att vara kompromisslöst',
                'help' => 'Kontrollerar lösenordet mot databasen HaveIBeenPwned med k-anonymitet',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Aktivera PeeringDB-sökning',
                'help' => 'Aktivera PeeringDB-sökning (data laddas ner med daily.sh)',
            ],
        ],
        'percentile_value' => [
            'description' => 'Percentilvärde',
            'help' => 'Percentilvärdet som ska användas för trafikdiagram. 0 betyder inaktiverad.',
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => 'Aktivera användaråtkomst via dynamiska enhetsgrupper',
                ],
            ],
        ],
        'bad_if' => [
            'description' => 'Dåligt gränssnitt om Descr',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifDescr som bör ignoreras',
        ],
        'bad_if_regexp' => [
            'description' => 'Dåligt gränssnitt ifDescr Regex',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifDescr som bör ignoreras med reguljära uttryck',
        ],
        'bad_ifalias_regexp' => [
            'description' => 'Dåligt gränssnitt om Alias Regex',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifAlias som bör ignoreras med reguljära uttryck',
        ],
        'bad_ifname_regexp' => [
            'description' => 'Dåligt gränssnitt ifName Regex',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifName som bör ignoreras med reguljära uttryck',
        ],
        'bad_ifoperstatus' => [
            'description' => 'Dåligt gränssnitt ifOperStatus Status',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifOperStatus som bör ignoreras',
        ],
        'bad_iftype' => [
            'description' => 'Dåligt gränssnitt ifType',
            'help' => 'Nätverksgränssnitt IF-MIB:!:ifType som bör ignoreras',
        ],
        'ping' => [
            'description' => 'Sökväg till ping',
        ],
        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Unix-agent',
            ],
            'os' => [
                'description' => 'OS',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'sensors' => [
                'description' => 'Sensorer',
            ],
            'processors' => [
                'description' => 'Processorer',
            ],
            'mempools' => [
                'description' => 'Mempools',
            ],
            'storage' => [
                'description' => 'Förvaring',
            ],
            'netstats' => [
                'description' => 'Netstats',
            ],
            'hr-mib' => [
                'description' => 'HR Mib',
            ],
            'ucd-mib' => [
                'description' => 'Ucd Mib',
            ],
            'ipSystemStats' => [
                'description' => 'ipSystemStats',
            ],
            'ports' => [
                'description' => 'Hamnar',
            ],
            'ports-stack' => [
                'description' => 'Ports Stack',
            ],
            'bgp-peers' => [
                'description' => 'BGP-kamrater',
            ],
            'vlans' => [
                'description' => 'VLAN',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wireless' => [
                'description' => 'Trådlös',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'ospfv3' => [
                'description' => 'OSPFv3',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => 'Cisco IPSec flödesövervakare',
            ],
            'cisco-remote-access-monitor' => [
                'description' => 'Cisco fjärråtkomstmonitor',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'slas' => [
                'description' => 'Spårning av servicenivåavtal',
            ],
            'mac-accounting' => [
                'description' => 'Cisco MAC-redovisning',
            ],
            'cipsec-tunnels' => [
                'description' => 'Cipsec-tunnlar',
            ],
            'cisco-ace-loadbalancer' => [
                'description' => 'Cisco ACE Loadbalancer',
            ],
            'cisco-ace-serverfarms' => [
                'description' => 'Cisco ACE Serverfarms',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'cisco-vpdn' => [
                'description' => 'Cisco VPDN',
            ],
            'nac' => [
                'description' => 'NAC',
            ],
            'netscaler-vsvr' => [
                'description' => 'Netscaler VSVR',
            ],
            'aruba-controller' => [
                'description' => 'Aruba Controller',
            ],
            'availability' => [
                'description' => 'Tillgänglighet',
            ],
            'entity-physical' => [
                'description' => 'Entitet Fysisk',
            ],
            'entity-state' => [
                'description' => 'Entitetsstat',
            ],
            'applications' => [
                'description' => 'Ansökningar',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM Info',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'loadbalancers' => [
                'description' => 'Lastbalanserare',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => 'Tillbehör för skrivare',
            ],
            'port-security' => [
                'description' => 'Portsäkerhet',
            ],
        ],
        'polling.selected_ports' => [
            'description' => 'Vald portavsökning',
            'help' => 'Aktivera vald portpolling för att endast pollingportar som är uppe och aktiverade',
        ],
        'ports_fdb_purge' => [
            'description' => 'Port FDB-poster äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'ports_ipv4_neighbours' => [
            'description' => 'Uppslagsmetod för port IPv4 grann',
            'help' => 'Metod att använda för att leta upp IPv4-grannar när du visar portdetaljer.  ARP kommer att använda ARP-tabellen för att hitta enheter med matchande IP- och MAC-adresser.  Subnät letar bara efter enheter med IP-adresser i samma subnät.',
        ],
        'ports_nac_purge' => [
            'description' => 'Port NAC-poster äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'ports_page_default' => [
            'description' => 'Fliken Standardportar',
            'help' => 'Standardfliken för att öppna när portar visas på enhetssidan',
        ],
        'ports_purge' => [
            'description' => 'Rensa portar raderade',
            'help' => 'Städning utförd av daily.sh',
        ],
        'processor.default_perc_warn' => [
            'description' => 'Varning för standardprocessorprocent',
            'help' => 'Standard Procentandel av processor som används innan en varning visas.',
        ],
        'prometheus' => [
            'enable' => [
                'description' => 'Aktivera',
                'help' => 'Exporterar mätvärden till Prometheus Push Gateway',
            ],
            'url' => [
                'description' => 'URL',
                'help' => 'URL-adressen till Prometheus Push Gateway som data ska skickas till',
            ],
            'Job' => [
                'description' => 'Jobb',
                'help' => 'Jobbetikett för exporterade mätvärden',
            ],
            'attach_sysname' => [
                'description' => 'Bifoga enhet sysName',
                'help' => 'Bifoga sysName-information till Prometheus.',
            ],
            'prefix' => [
                'description' => 'Prefix',
                'help' => 'Valfri text som ska läggas före exporterade metriska namn',
            ],
        ],
        'public_status' => [
            'description' => 'Visa status offentligt',
            'help' => 'Visar status för vissa enheter på inloggningssidan utan autentisering.',
        ],
        'routes_max_number' => [
            'description' => 'Max antal tillåtna rutter för upptäckt',
            'help' => 'Ingen rutt kommer att upptäckas om storleken på routingtabellen är större än detta antal',
        ],
        'default_port_group' => [
            'description' => 'Standardportgrupp',
            'help' => 'Nya upptäckta portar kommer att tilldelas denna portgrupp.',
        ],
        'nets' => [
            'description' => 'Autodiscovery-nätverk',
            'help' => 'Nätverk från vilka enheter kommer att upptäckas automatiskt.',
        ],
        'autodiscovery' => [
            'bgp' => [
                'description' => 'Aktivera BGP granne upptäckt',
                'help' => 'Lägg till länkar och grannar baserat på BGP-kamrater',
            ],
            'cdp_exclude' => [
                'platform_regexp' => [
                    'description' => 'CDP exkluderar plattformsregex',
                    'help' => 'Förhindra enheter från att läggas till av CDP om sysName matchar reguljärt uttryck',
                ],
            ],
            'nets-exclude' => [
                'description' => 'Nätverk och IP-adresser som ska ignoreras',
                'help' => 'Nätverk och IP-adresser som inte ska identifieras automatiskt. Utesluter även IP-adresser från nätverk för automatisk identifiering.',
            ],
            'ospf' => [
                'description' => 'Aktivera OSPF grannupptäckt',
                'help' => 'Lägg till länkar och grannar baserat på OSPF-kamrater',
            ],
            'ospfv3' => [
                'description' => 'Aktivera OSPFv3 grannupptäckt',
                'help' => 'Lägg till länkar och grannar baserat på OSPFv3-kamrater',
            ],
            'xdp' => [
                'description' => 'Aktivera xDP-upptäcktsprotokoll',
                'help' => 'Använd LLDP, CDP, etc protokoll för att upptäcka nätverkstopologi och grannar och lägga till dem i LibreNMS',
            ],
            'xdp_exclude' => [
                'sysname_regexp' => [
                    'description' => 'xDP exclude sysName regex',
                    'help' => 'Förhindra enheter från att läggas till om sysName matchar reguljärt uttryck',
                ],
                'sysdesc_regexp' => [
                    'description' => 'xDP exkludera sysDescr regex',
                    'help' => 'Förhindra enheter från att läggas till om sysDescr matchar reguljärt uttryck',
                ],
            ],
        ],
        'radius' => [
            'default_roles' => [
                'description' => 'Standardanvändarroller',
                'help' => 'Ställer in rollerna som kommer att tilldelas användaren om inte Radius skickar attribut som anger roll(er)',
            ],
            'enforce_roles' => [
                'description' => 'Genomför roller vid inloggning',
                'help' => 'Om det är aktiverat kommer roller att ställas in på de som anges av attributet Filter-ID eller radius.default_roles vid inloggning.  Annars kommer de att ställas in när användaren skapas och aldrig ändras efter det.',
            ],
        ],
        'rancid_configs' => [
            'description' => 'RANCID Configs',
            'help' => 'RANCID configs katalog, används för att visa config diffs på enhetssidor',
        ],
        'rancid_repo_type' => [
            'description' => 'RANCID förrådstyp',
            'help' => 'Typ av arkiv som används av RANCID, används för att visa konfigurationsdifferenser på enhetssidor',
        ],
        'rancid_repo_url' => [
            'description' => 'RANCID Repository URL',
            'help' => 'RANCID repository URL, används för att peka på GitWeb som visualiserar ett blott Git repository',
        ],
        'rancid_ignorecomments' => [
            'description' => 'RANCID Ignorera kommentarer',
            'help' => 'Ignorera kommentarer när du jämför RANCID-konfigurationer, används för att visa konfigurationsdifferenser på enhetssidor',
        ],
        'reporting' => [
            'error' => [
                'description' => 'Skicka felrapporter',
                'help' => 'Skickar några fel till LibreNMS för analys och korrigering',
            ],
            'usage' => [
                'description' => 'Skicka användningsrapporter',
                'help' => 'Rapporterar användning och versioner till LibreNMS. För att ta bort anonym statistik, besök sidan Om. Du kan se statistik på https://stats.librenms.org',
            ],
            'dump_errors' => [
                'description' => 'Dumpa felsökningsfel (kommer att bryta din installation)',
                'help' => 'Dumpa bort fel som normalt är dolda så att du som utvecklare kan hitta och åtgärda eventuella problem.',
            ],
            'throttle' => [
                'description' => 'Gasspjällsfelrapporter',
                'help' => 'Rapporter kommer endast att skickas varje angivet antal sekunder. Utan detta kan om du har ett fel i vanlig kodrapportering gå över styr. Ställ in på 0 för att inaktivera gasreglaget.',
            ],
        ],
        'rewrite_if' => [
            'description' => 'Skriv om ifDescr',
            'help' => 'Skriv om ifDescr för att ta bort gränssnittstyp och nummer, t.ex. GigabitEthernet0/1 blir GigabitEthernet',
        ],
        'route_purge' => [
            'description' => 'Ruttposter äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Ändra det första hjärtslagsvärdet (standard 600)',
            ],
            'step' => [
                'description' => 'Ändra värdet för första steget (standard 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD Plats',
            'help' => 'Plats för rrd-filer.  Standard är rrd i LibreNMS-katalogen.  Att ändra denna inställning flyttar inte rrd-filerna.',
        ],
        'rrd_purge' => [
            'description' => 'RRD Files-poster äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'RRD-formatinställningar',
            'help' => 'Dessa kan inte ändras utan att ta bort dina befintliga RRD-filer. Även om man skulle kunna tänka sig att öka eller minska storleken på varje RRA om man hade prestandaproblem eller om man hade ett mycket snabbt I/O-undersystem utan prestandabekymmer.',
        ],
        'rrdcached' => [
            'description' => 'Aktivera rrdcached (socket)',
            'help' => 'Aktiverar rrdcached genom att ställa in platsen för den rrdcachade socket. Kan vara unix eller nätverkssocket (unix:/run/rrdcached.sock eller localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Sökväg till rrdtool',
        ],
        'rrdtool_tune' => [
            'description' => 'Justera alla rrd-portfiler för att använda maxvärden',
            'help' => 'Autojustera maximalt värde för rrd-portfiler',
        ],
        'rrdtool_version' => [
            'description' => 'Ställer in versionen av rrdtool på din server',
            'help' => 'Allt över 1.5.5 stöder alla funktioner som LibreNMS använder, ställ inte in högre än din installerade version',
        ],
        'schedule_type' => [
            'alerting' => [
                'description' => 'Varning',
                'help' => 'Schemaläggningsmetod för varning. Legacy kommer att använda cron om crontab-posten finns och dispatcher-tjänsten om det äldre konfigurationsalternativet service_billing_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'cron' => 'Cron (alerts.php)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'billing' => [
                'description' => 'Fakturering',
                'help' => 'Schemaläggningsmetod för fakturering. Legacy kommer att använda cron om crontab-posten finns och dispatcher-tjänsten om det äldre konfigurationsalternativet service_billing_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'cron' => 'Cron (poll-billing.php och billing-calculate.php)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'discovery' => [
                'description' => 'Upptäckt',
                'help' => 'Schemaläggningsmetod för upptäcktsuppgifter. Legacy kommer att använda cron om crontab-posten finns och dispatcher-tjänsten om det äldre konfigurationsalternativet service_discovery_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'cron' => 'Cron (lnms-enhet:discover)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'ping' => [
                'description' => 'Snabb Ping',
                'help' => 'Snabb ping-uppgiftsschemaläggningsmetod. Legacy kommer att använda cron om crontab-posten finns och använda dispatcher-tjänsten om det äldre konfigurationsalternativet service_ping_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'disabled' => 'Inaktiverad (pingar endast under omröstning)',
                    'cron' => 'Cron (ping.php)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'poller' => [
                'description' => 'Poller',
                'help' => 'Schemaläggningsmetod för polleruppgift. Legacy kommer att använda cron om crontab-posten finns och dispatcher-tjänsten om det äldre konfigurationsalternativet service_poller_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'cron' => 'Cron (poller.php)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'services' => [
                'description' => 'Tjänster',
                'help' => 'Schemaläggningsmetod för tjänster. Legacy kommer att använda cron om crontab-posten finns och dispatcher-tjänsten om det äldre konfigurationsalternativet service_services_enabled är satt till true.',
                'options' => [
                    'legacy' => 'Legacy (Obegränsad)',
                    'cron' => 'Cron (check-services.php)',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
        ],
        'sensors' => [
            'guess_limits' => [
                'description' => 'Gissa sensorgränser',
                'help' => 'Om det är aktiverat kommer LibreNMS att försöka gissa sensorgränserna baserat på sensortypen och -värdet. Detta är inte alltid korrekt och kan leda till felaktiga gränser.',
            ],
        ],
        'service_master_timeout' => [
            'description' => 'Master Dispatcher Timeout',
            'help' => 'Tiden innan huvudlåset löper ut.  Om master försvinner kommer det att ta så lång tid för en annan nod att ta över.  Men om det tar längre tid än timeouten att skicka arbetet kommer du att ha flera masters',
        ],
        'service_ping_frequency' => [
            'description' => 'Pingfrekvens',
            'help' => 'Hur ofta man kör snabb ping på alla enheter.',
        ],
        'service_poller_workers' => [
            'description' => 'Pollerarbetare',
            'help' => 'Mängden pollararbetare som ska leka. Ställer in standardvärdet för alla noder.',
        ],
        'service_poller_frequency' => [
            'description' => 'Pollerfrekvens (varning!)',
            'help' => 'Hur ofta ska man polla enheter. Ställer in standardvärdet för alla noder. Varning! Att ändra detta utan att fixa rrd-filer kommer att bryta grafer. Se dokument för mer information.',
        ],
        'service_poller_down_retry' => [
            'description' => 'Enheten nere Försök igen',
            'help' => 'Om en enhet är nere när polling görs. Det här är hur lång tid det ska vänta innan du försöker igen. Ställer in standardvärdet för alla noder.',
        ],
        'service_discovery_workers' => [
            'description' => 'Upptäckararbetare',
            'help' => 'Antalet upptäcktsarbetare att köra. För hög inställning kan orsaka överbelastning. Ställer in standardvärdet för alla noder.',
        ],
        'service_discovery_frequency' => [
            'description' => 'Upptäcktsfrekvens',
            'help' => 'Hur ofta du ska köra enhetsupptäckt. Ställer in standardvärdet för alla noder. Standard är 4 gånger om dagen.',
        ],
        'service_services_workers' => [
            'description' => 'Tjänster Arbetare',
            'help' => 'Antalet tjänstearbetare. Ställer in standardvärdet för alla noder.',
        ],
        'service_services_frequency' => [
            'description' => 'Tjänster Frekvens',
            'help' => 'Hur ofta man kör tjänster. Detta bör matcha pollers frekvens. Ställer in standardvärdet för alla noder.',
        ],
        'service_billing_frequency' => [
            'description' => 'Faktureringsfrekvens',
            'help' => 'Hur ofta man samlar in faktureringsdata. Ställer in standardvärdet för alla noder.',
        ],
        'service_billing_calculate_frequency' => [
            'description' => 'Fakturering Beräkna Frekvens',
            'help' => 'Hur ofta man ska beräkna räkningsanvändning. Ställer in standardvärdet för alla noder.',
        ],
        'service_alerting_frequency' => [
            'description' => 'Varningsfrekvens',
            'help' => 'Hur ofta kontrolleras varningsregler. Observera att data endast uppdateras baserat på pollers frekvens. Ställer in standardvärdet för alla noder.',
        ],
        'service_update_enabled' => [
            'description' => 'Dagligt underhåll aktiverat',
            'help' => 'Kör daily.sh underhållsskript och starta om dispatcher-tjänsten efteråt. Ställer in standardvärdet för alla noder.',
        ],
        'service_update_frequency' => [
            'description' => 'Underhållsfrekvens',
            'help' => 'Hur ofta ska man köra dagligt underhåll. Standard är 1 dag. Det rekommenderas starkt att inte ändra detta. Ställer in standardvärdet för alla noder.',
        ],
        'service_loglevel' => [
            'description' => 'Loggnivå',
            'help' => 'Loggnivå för leveranstjänsten. Ställer in standardvärdet för alla noder.',
        ],
        'service_watchdog_enabled' => [
            'description' => 'Watchdog aktiverad',
            'help' => 'Watchdog övervakar loggfilen och startar om tjänsten om den inte har uppdaterats. Ställer in standardvärdet för alla noder.',
        ],
        'service_watchdog_log' => [
            'description' => 'Loggfil att titta på',
            'help' => 'Standard är LibreNMS-loggfilen. Ställer in standardvärdet för alla noder.',
        ],
        'service_health_file' => [
            'description' => 'Service Hälsofil',
            'help' => 'Sökväg till hälsofil för att säkerställa att avsändartjänsten körs',
        ],
        'shorthost_target_length' => [
            'description' => 'Maxlängd för kortat värdnamn',
            'help' => 'Krymper värdnamn till maximal längd, men kompletta alltid underdomändelar',
        ],
        'show_locations' => [
            'description' => 'Visa platser i navigering',
            'help' => 'Visa platsen i navigeringsfältet',
        ],
        'show_locations_dropdown' => [
            'description' => 'Visa platser i rullgardinsmenyn',
            'help' => 'Visa platsen i rullgardinsmenyn',
        ],
        'show_services' => [
            'description' => 'Visa tjänster i navigering',
            'help' => 'Visa tjänsterna i navigeringsfältet',
        ],
        'site_style' => [
            'description' => 'Standardtema',
            'options' => [
                'device' => 'Enhet',
                'blue' => 'Blå',
                'dark' => 'Mörkt',
                'light' => 'Ljus',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transport (prioritet)',
                'help' => 'Välj aktiverade transporter och beställ dem som du vill att de ska provas.',
            ],
            'version' => [
                'description' => 'Version (prioritet)',
                'help' => 'Välj aktiverade versioner och beställ dem som du vill att de ska testas.',
            ],
            'community' => [
                'description' => 'Grupper (prioritet)',
                'help' => 'Ange community-strängar för v1 och v2c och beställ dem som du vill att de ska prövas',
            ],
            'max_oid' => [
                'description' => 'Max OID',
                'help' => 'Maximalt OID per fråga.  Kan åsidosättas på OS- och enhetsnivå.',
            ],
            'max_repeaters' => [
                'description' => 'Max Repeaters',
                'help' => 'Ställ in repeaters att använda för SNMP-bulkförfrågningar',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => 'Inaktivera snmp-bulk för OID',
                    'help' => 'Inaktivera snmp-bulkdrift för vissa OID. I allmänhet bör detta ställas in på ett OS istället. Formatet ska vara MIB::OID',
                ],
                'unordered' => [
                    'description' => 'Tillåt ur funktion snmp-svar för OID',
                    'help' => 'Ignorera oordnade OID i snmp-svar för vissa OID. Oordnade OID kan resultera i en oid loop under en snmpwalk. I allmänhet bör detta ställas in på ett OS istället. Formatet ska vara MIB::OID',
                ],
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Ställ in tcp/udp-porten som ska användas för SNMP',
            ],
            'timeout' => [
                'description' => 'Timeout',
                'help' => 'SNMP Timeout i sekunder',
            ],
            'retries' => [
                'description' => 'Försöker igen',
                'help' => 'hur många gånger du ska försöka igen',
            ],
            'v3' => [
                'description' => 'SNMP v3-autentisering (prioritet)',
                'help' => 'Ställ in v3-autentiseringsvariabler och ordna dem som du vill att de ska testas',
                'auth' => 'Auth',
                'crypto' => 'Krypto',
                'fields' => [
                    'authalgo' => 'Algoritm',
                    'authlevel' => 'Nivå',
                    'authname' => 'Användarnamn',
                    'authpass' => 'Lösenord',
                    'cryptoalgo' => 'Algoritm',
                    'cryptopass' => 'Lösenord',
                ],
                'level' => [
                    'noAuthNoPriv' => 'Ingen autentisering, ingen sekretess',
                    'authNoPriv' => 'Autentisering, ingen sekretess',
                    'authPriv' => 'Autentisering och sekretess',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'Vägen till snmpbulkwalk',
        ],
        'snmpget' => [
            'description' => 'Sökväg till snmpget',
        ],
        'snmpgetnext' => [
            'description' => 'Sökväg till snmpgetnext',
        ],
        'snmptranslate' => [
            'description' => 'Sökväg till snmptranslate',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'Skapa händelselogg för snmptraps',
                'help' => 'Oberoende av åtgärden som kan vara mappad till fällan',
            ],
            'eventlog_detailed' => [
                'description' => 'Aktivera detaljerade loggar',
                'help' => 'Lägg till alla OID som tagits emot med fällan i händelseloggen',
            ],
        ],
        'snmpwalk' => [
            'description' => 'Vägen till snmpwalk',
        ],
        'ssl_certificates' => [
            'auto_discover' => [
                'description' => 'Auto Discover SSL-certifikat',
                'help' => 'Upptäck automatiskt SSL-certifikat',
            ],
            'skip_hosts' => [
                'description' => 'Hoppa över värdar',
                'help' => 'Hoppa över värdar från upptäckt av SSL-certifikat',
            ],
            'days_until_expiry_warning' => [
                'description' => 'Varning (dagar)',
                'help' => 'Antal dagar innan certifikatet löper ut för att utlösa en varning',
            ],
            'days_until_expiry_danger' => [
                'description' => 'Fara (dagar)',
                'help' => 'Antal dagar innan certifikatet löper ut för att utlösa en faralarm',
            ],
        ],
        'sso' => [
            'create_users' => [
                'description' => 'Skapa användare',
                'help' => 'Om nya användare ska skapas vid inloggning.',
            ],
            'descr_attr' => [
                'description' => 'Användarbeskrivning Attribut',
                'help' => 'Attributet som innehåller en beskrivning av användaren.',
            ],
            'email_attr' => [
                'description' => 'E-postattribut',
                'help' => 'Attributet som innehåller användarens e-postadress.',
            ],
            'group_attr' => [
                'description' => 'Gruppattribut',
                'help' => 'Attributet som innehåller gruppinformationen om man använder mappning.',
            ],
            'group_delimiter' => [
                'description' => 'Gruppavgränsare',
                'help' => 'Avgränsaren som ska användas för gruppinformation om man använder mappningsgruppsstrategin.',
            ],
            'group_filter' => [
                'description' => 'Gruppfilter Regexp',
                'help' => 'Används för att filtrera gruppinformation om man använder mappningsgruppsstrategi.',
            ],
            'group_level_map' => [
                'description' => 'Karta på gruppnivå',
                'help' => 'Grupp till roll kartläggning.',
            ],
            'group_strategy' => [
                'description' => 'Gruppens strategi',
                'help' => 'Hur gruppkartläggningen ska göras.',
            ],
            'level_attr' => [
                'description' => 'Nivåattribut',
                'help' => 'Attributet som ska användas om du använder attributgruppstrategin.',
            ],
            'mode' => [
                'description' => 'Läge',
                'help' => 'Om den ska använda miljövariablerna eller HTTP-huvudet.',
            ],
            'realname_attr' => [
                'description' => 'Realname Attribut',
                'help' => 'Attributet som innehåller användarens riktiga namn.',
            ],
            'static_level' => [
                'description' => 'Statisk nivå',
                'help' => 'Om statisk används, det rollnivåvärde som ska användas för alla med åtkomst.',
            ],
            'trusted_proxies' => [
                'description' => 'Pålitliga proxyservrar',
                'help' => 'En lista över betrodda ombud.',
            ],
            'update_users' => [
                'description' => 'Uppdatera användare',
                'help' => 'Om användare ska uppdateras vid inloggning.',
            ],
            'user_attr' => [
                'description' => 'Användarattribut',
                'help' => 'Attributet som innehåller användarnamnet.',
            ],
        ],
        'storage_perc_warn' => [
            'description' => 'Varning för standardlagringsprocent',
            'help' => 'Standard Procentandel av lagring som används innan en varning höjs. 0 inaktiverar varning.',
        ],
        'syslog_filter' => [
            'description' => 'Filtrera syslogmeddelanden som innehåller',
        ],
        'syslog_purge' => [
            'description' => 'Syslog-poster äldre än',
            'help' => 'Städning utförd av daily.sh',
        ],
        'title_image' => [
            'description' => 'Titelbild',
            'help' => 'Åsidosätter standardtitelbilden. SVG från samma server kommer att inkluderas och kan använda currentColor för att matcha det aktuella temat dynamiskt.',
        ],
        'traceroute' => [
            'description' => 'Vägen till traceroute',
        ],
        'twofactor' => [
            'description' => 'Tvåfaktor',
            'help' => 'Tillåt användare att aktivera och använda tidsbaserade (TOTP) eller motbaserade (HOTP) engångslösenord (OTP)',
        ],
        'twofactor_lock' => [
            'description' => 'Tvåfaktors gaspådragstid (sekunder)',
            'help' => 'Spärrtid att vänta i sekunder innan ytterligare försök tillåts om tvåfaktorsautentisering misslyckades 3 gånger i följd - kommer att uppmana användaren att vänta så här länge.  Ställ in på 0 för att inaktivera vilket resulterar i en permanent kontolåsning och ett meddelande till användaren att kontakta administratören',
        ],
        'unimus' => [
            'api_version' => [
                'description' => 'Unimus API-version',
            ],
            'enabled' => [
                'description' => 'Aktivera Unimus-support',
                'help' => 'Visa säkerhetskopior av enhetskonfiguration från Unimus på fliken enhetskonfiguration',
            ],
            'token' => [
                'description' => 'Unimus API-token',
                'help' => 'API-token skapad i Unimus (grundläggande/skrivskyddad åtkomst räcker)',
            ],
            'url' => [
                'description' => 'Unimus URL',
                'help' => 'Bas-URL för din Unimus-server, till exempel: http://unimus.example.com:8085',
            ],
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent anslutning timeout',
            ],
            'port' => [
                'description' => 'Standard port för unix-agent',
                'help' => 'Standardport för unix-agenten (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent läs timeout',
            ],
        ],
        'update' => [
            'description' => 'Aktivera uppdateringar i ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Uppdatera kanal',
            'options' => [
                'master' => 'Dagligen',
                'release' => 'Månadsvis',
            ],
        ],
        'update_on_days' => [
            'description' => 'Kör bara uppdateringar dessa dagar',
            'help' => 'Om den är inställd (ej tom), kommer daily.sh endast att köra koduppdateringar när idag matchar ett av dessa värden: måndag-söndag eller mån-sön. Lämna tomt för att tillåta uppdateringar varje dag.',
        ],
        'uptime_warning' => [
            'description' => 'Visa Enhet som varning om Drifttid nedan (sekunder)',
            'help' => 'Visar Enhet som varning om Drifttid är under detta värde. Status för anpassade kartor återspeglar denna inställning. 0 inaktiverar varning. Standard 24h',
        ],
        'virsh' => [
            'description' => 'Vägen till virsh',
        ],
        'web_mouseover' => [
            'description' => 'Aktivera mouseover',
            'help' => 'Aktiverar musövergraferna i webbgränssnittet',
        ],
        'webui' => [
            'scheduled_maintenance_default_behavior' => [
                'description' => 'Standardbeteende',
                'help' => 'När du hanterar schemalagt underhåll kommer detta att vara standardalternativet för alternativet Beteende.',
            ],
            'alert_map_compact' => [
                'description' => 'Alert karta kompakt vy',
                'help' => 'Alert kartvy med små indikatorer',
            ],
            'alert_map_sort_status' => [
                'description' => 'Sortera efter status',
                'help' => 'Sortera varningar efter status',
            ],
            'alert_map_use_device_groups' => [
                'description' => 'Använd filter för enhetsgrupper',
                'help' => 'Aktivera användning av filter för enhetsgrupper',
            ],
            'alert_map_box_size' => [
                'description' => 'Varningslådans bredd',
                'help' => 'Ange önskad bredd i pixlar för boxstorlek i helvy',
            ],
            'availability_map_box_size' => [
                'description' => 'Tillgänglighet box bredd',
                'help' => 'Ange önskad bredd i pixlar för boxstorlek i helvy',
            ],
            'availability_map_compact' => [
                'description' => 'Tillgänglighetskarta kompakt vy',
                'help' => 'Tillgänglighetskartvy med små indikatorer',
            ],
            'availability_map_sort_status' => [
                'description' => 'Sortera efter status',
                'help' => 'Sortera enheter och tjänster efter status',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Använd filter för enhetsgrupper',
                'help' => 'Aktivera användning av filter för enhetsgrupper',
            ],
            'custom_css' => [
                'description' => 'Anpassad CSS',
                'help' => 'Lägg till anpassad CSS till webbgränssnittet',
            ],
            'default_dashboard_id' => [
                'description' => 'Standard instrumentpanel',
                'help' => 'Global standard dashboard_id för alla användare som inte har sin egen standarduppsättning',
            ],
            'dynamic_graphs' => [
                'description' => 'Aktivera dynamiska grafer',
                'help' => 'Aktivera dynamiska grafer, möjliggör zoomning och panorering på grafer',
            ],
            'global_search_result_limit' => [
                'description' => 'Ställ in maxgränsen för sökresultat',
                'help' => 'Gräns för globala sökresultat',
            ],
            'graph_stacked' => [
                'description' => 'Använd staplade grafer',
                'help' => 'Visa staplade grafer istället för inverterade grafer',
            ],
            'graph_type' => [
                'description' => 'Ställ in graftypen',
                'help' => 'Ställ in standardgraftypen',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Ställ in den lägsta grafhöjden',
                'help' => 'Minsta grafhöjd (standard: 300)',
            ],
            'graph_stat_percentile_disable' => [
                'description' => 'Inaktivera Percentil för statistikgrafer globalt',
                'help' => 'Inaktiverar visning av percentilvärden och linjer för grafer som visar dessa',
            ],
        ],
        'device_display_default' => [
            'description' => 'Standardmall för enhetsvisningsnamn',
            'help' => 'Ställer in standardvisningsnamnet för alla enheter (kan åsidosättas per enhet).  Värdnamn/IP: Visa bara värdnamnet eller IP-adressen som enheten lades till med. sysName: Visa bara sysName från snmp. Värdnamn eller sysName: Visa värdnamn, men om det är en IP, visa sysName.',
            'options' => [
                'hostname' => 'Värdnamn / IP',
                'sysName_fallback' => 'Värdnamn, reserv till sysName för IP-adresser',
                'sysName' => 'sysName',
                'ip' => 'IP (från värdnamn IP eller löst)',
            ],
        ],
        'device_location_map_open' => [
            'description' => 'Platskarta öppen',
            'help' => 'Platskarta visas som standard',
        ],
        'device_location_map_show_devices' => [
            'description' => 'Visa enheter på platskartan',
            'help' => 'Visa alla enheter på platskartan när den är synlig',
        ],
        'device_location_map_show_device_dependencies' => [
            'description' => 'Visa enhetsberoende på platskartan',
            'help' => 'Visa länkar mellan enheter på platskartan baserat på föräldraberoenden',
        ],
        'device_stats_avg_factor' => [
            'description' => 'Medelvärdesfaktor',
            'help' => 'Vi beräknar ett glidande medelvärde med hjälp av en exponentiellt vägd glidande medelvärdefunktion.  Detta är den faktor som används av funktionen för att styra hur mycket det aktuella värdet påverkar medelvärdet.  Värden närmare 1 kommer att göra den genomsnittliga förändringen snabbare.',
        ],
        'smokeping.integration' => [
            'description' => 'Aktivera',
            'help' => 'Aktivera integrering av rökning',
        ],
        'smokeping.dir' => [
            'description' => 'Vägen till rrds',
            'help' => 'Hela vägen till Smoking RRDs',
        ],
        'smokeping.pings' => [
            'description' => 'Pingar',
            'help' => 'Antal pingar konfigurerade i Smokeping',
        ],
        'smokeping.url' => [
            'description' => 'URL till rökning',
            'help' => 'Fullständig URL till rökningsguiden',
        ],
    ],
    'twofactor' => [
        'description' => 'Aktivera tvåfaktorsautentisering',
        'help' => 'Aktiverar den inbyggda tvåfaktorsautentiseringen. Du måste konfigurera varje konto för att göra det aktivt.',
    ],
    'units' => [
        'days' => 'dagar',
        'ms' => 'ms',
        'seconds' => 'sekunder',
        'percent' => '%',
    ],
    'validate' => [
        'boolean' => ':value är inte en giltig boolean',
        'color' => ':value är inte en giltig hex-färgkod',
        'email' => ':value är inte en giltig e-post',
        'float' => ':value är inte en flottör',
        'integer' => ':value är inte ett heltal',
        'password' => 'Lösenordet är felaktigt',
        'select' => ':value är inte ett tillåtet värde',
        'text' => ':value är inte tillåtet',
        'array' => 'Ogiltigt format',
        'password-array' => 'Ogiltigt format',
        'executable' => ':value är inte en giltig körbar fil',
        'directory' => ':value är inte en giltig katalog',
    ],
];
