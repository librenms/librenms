<?php

return [
    'errors' => [
        'db_connect' => 'Det gick inte att ansluta till databasen. Kontrollera att databastjänsten körs och anslutningsinställningar.',
        'db_auth' => 'Det gick inte att ansluta till databasen. Verifiera autentiseringsuppgifter: :error',
        'no_devices' => 'Inga enheter hittades som matchar din enhetsspecifikation',
        'no_new_devices' => 'Inga nya enheter',
    ],
    'config:clear' => [
        'description' => 'Rensa konfigurationscache.  Detta kommer att tillåta alla ändringar som har gjorts sedan den senaste fullständiga konfigurationsladdningen att återspeglas i den aktuella konfigurationen.',
    ],
    'config:get' => [
        'description' => 'Få konfigurationsvärde',
        'arguments' => [
            'setting' => 'inställning för att få värdet av i punktnotation (exempel: snmp.community.0)',
        ],
        'options' => [
            'dump' => 'Mata ut hela konfigurationen som json',
        ],
    ],
    'config:list' => [
        'description' => 'Lista och sökkonfigurationsinställningar',
        'arguments' => [
            'search' => 'Sök efter en inställning, matchande konfigurationsnamn eller beskrivning',
        ],
        'not_found' => 'Inga inställningar hittades som matchar \':search\'',
    ],
    'config:set' => [
        'description' => 'Ställ in konfigurationsvärde (eller avaktiverat)',
        'arguments' => [
            'setting' => 'inställning för att sätta i punktnotation (exempel: snmp.community.0) För att lägga till ett arraysuffix med .+',
            'value' => 'värde att ställa in, avaktivera inställning om detta utelämnas',
        ],
        'options' => [
            'ignore-checks' => 'Ignorera alla säkerhetskontroller',
        ],
        'confirm' => 'Återställa :setting till standard?',
        'forget_from' => 'Glömma :path från :parent?',
        'errors' => [
            'append' => 'Det går inte att lägga till i icke-matrisinställning',
            'failed' => 'Det gick inte att ställa in :setting',
            'invalid' => 'Detta är inte en giltig inställning. Kontrollera din input',
            'invalid_os' => 'Specificerat operativsystem (:os) finns inte',
            'nodb' => 'Databasen är inte ansluten',
            'no-validation' => 'Kan inte ställa in :setting, den saknar valideringsdefinition.',
        ],
    ],
    'db:seed' => [
        'existing_config' => 'Databasen innehåller befintliga inställningar. Fortsätta?',
    ],
    'dev:check' => [
        'description' => 'LibreNMS kodkontroller. Att köra utan alternativ kör alla kontroller',
        'arguments' => [
            'check' => 'Kör den angivna kontrollen :checks',
        ],
        'options' => [
            'commands' => 'Skriv ut kommandon som endast skulle köras, inga kontroller',
            'db' => 'Kör enhetstester som kräver en databasanslutning',
            'fail-fast' => 'Stoppa kontroller när något fel uppstår',
            'full' => 'Kör fullständiga kontroller och ignorera ändrad filfiltrering',
            'module' => 'Specifik modul att köra tester på. Antyder enhet, --db, --snmpsim',
            'os' => 'Specifikt OS att köra tester på. Kan vara en regex eller kommaseparerad lista. Antyder enhet, --db, --snmpsim',
            'os-modules-only' => 'Hoppa över OS-detektionstest när du anger ett specifikt OS.  Snabbar upp testtiden vid kontroll av ändringar som inte har upptäckts.',
            'quiet' => 'Dölj utdata om det inte finns ett fel',
            'snmpsim' => 'Använd snmpsim för enhetstester',
        ],
    ],
    'dev:simulate' => [
        'description' => 'Simulera enheter med hjälp av testdata',
        'arguments' => [
            'file' => 'Filnamnet (endast basnamn) för snmprec-filen som ska uppdateras eller läggas till i LibreNMS. Om filen inte anges kommer ingen enhet att läggas till eller uppdateras.',
        ],
        'options' => [
            'multiple' => 'Använd gemenskapsnamn för värdnamn istället för snmpsim',
            'remove' => 'Ta bort enheten efter att ha stoppats',
        ],
        'added' => 'Enhet :hostname (:id) har lagts till',
        'exit' => 'Ctrl-C för att stoppa',
        'removed' => 'Enheten :id har tagits bort',
        'updated' => 'Enhet :hostname (:id) uppdaterad',
        'setup' => 'Konfigurera snmpsim venv i :dir',
    ],
    'device:add' => [
        'description' => 'Lägg till en ny enhet',
        'arguments' => [
            'device spec' => 'Värdnamn eller IP att lägga till',
        ],
        'options' => [
            'v1' => 'Använd SNMP v1',
            'v2c' => 'Använd SNMP v2c',
            'v3' => 'Använd SNMP v3',
            'display-name' => 'En sträng som ska visas som namnet på den här enheten är standard som värdnamn.
Kan vara en enkel mall som använder ersättningar: {{ $hostname }}, {{ $sysName }}, {{ $sysName_fallback }}, {{ $ip }}',
            'force' => 'Lägg bara till enheten, gör inga säkerhetskontroller',
            'group' => 'Pollergrupp (för distribuerad omröstning)',
            'ping-fallback' => 'Lägg bara till enheten som ping om den inte svarar på SNMP',
            'port-association-mode' => 'Ställer in hur portar mappas. ifName föreslås för Linux/Unix',
            'community' => 'SNMP v1 eller v2 community',
            'transport' => 'Transport för att ansluta till enheten',
            'port' => 'SNMP transportport',
            'security-name' => 'SNMPv3 säkerhetsanvändarnamn',
            'auth-password' => 'SNMPv3-autentiseringslösenord',
            'auth-protocol' => 'SNMPv3-autentiseringsprotokoll',
            'privacy-protocol' => 'SNMPv3 sekretessprotokoll',
            'privacy-password' => 'SNMPv3 sekretesslösenord',
            'ping-only' => 'Lägg till en ping-enhet',
            'os' => 'Endast ping: ange OS',
            'hardware' => 'Endast ping: ange hårdvara',
            'sysName' => 'Endast ping: ange sysName',
        ],
        'validation-errors' => [
            'port.between' => 'Port ska vara 1-65535',
            'poller-group.in' => 'Den givna pollargruppen existerar inte',
        ],
        'messages' => [
            'save_failed' => 'Det gick inte att spara enheten :hostname',
            'try_force' => 'Du kan försöka med alternativet --force att hoppa över säkerhetskontroller',
            'added' => 'Lade till enhet :hostname (:device_id)',
        ],
    ],
    'device:discover' => [
        'description' => 'Upptäck information om befintliga enheter, definierar vad som ska pollas',
        'arguments' => [
            'device spec' => 'Enhetsspecifikation att upptäcka: device_id, hostname, jokertecken (*), udda, jämnt, alla',
        ],
        'options' => [
            'modules' => 'Ange modul(er) som ska köras. undermoduler kan läggas till med /.  Flera värden tillåtna.',
            'os' => 'Upptäck endast enheter med specificerat operativsystem',
            'type' => 'Upptäck endast enheter med angiven typ',
        ],
        'errors' => [
            'none_up' => 'Enheten var nere, kunde inte upptäcka.|Alla enheter var nere, kunde inte upptäcka.',
            'none_actioned' => 'Inga enheter upptäcktes.',
        ],
        'actioned' => 'Upptäckte :count-enheter i :time',
        'starting' => 'Börjar upptäckt:',
    ],
    'device:ping' => [
        'description' => 'Pinga enheten och registrera data för svar',
        'arguments' => [
            'device spec' => 'Enhet för att pinga en av: <Device ID>, <Hostname/IP>, alla, snabbt ("snabb" kommer att pinga alla enheter och uppdatera grafer och status)',
        ],
        'options' => [
            'groups' => 'Grupp-ID att pinga. Ange flera gånger för flera grupper. (gäller endast med snabb)',
        ],
        'errors' => [
            'groups_without_fast' => 'Alternativet --groups (-g) stöds endast med "snabb" enhetsspecifikation.',
        ],
    ],
    'device:poll' => [
        'description' => 'Polldata från enhet(er) enligt definitionen av upptäckt',
        'arguments' => [
            'device spec' => 'Enhetsspecifikation för omröstning: device_id, hostname, jokertecken (*), udda, jämnt, alla',
        ],
        'options' => [
            'modules' => 'Ange en modul som ska köras. Kommaseparerade moduler, undermoduler kan läggas till med /',
            'no-data' => 'Uppdatera inte datalager (RRD, InfluxDB, etc)',
            'os' => 'Enkätenheter endast med specificerat operativsystem',
            'type' => 'Omröstningsenheter endast med angiven typ',
        ],
        'errors' => [
            'none_up' => 'Enheten var nere, kunde inte polla.|Alla enheter var nere, kunde inte polla.',
            'none_actioned' => 'Inga enheter avfrågades.',
        ],
        'actioned' => 'Polled :count enheter i :time',
        'starting' => 'Startar omröstningen:',
    ],
    'device:remove' => [
        'doesnt_exists' => 'Ingen sådan enhet: :device',
    ],
    'key:rotate' => [
        'description' => 'Rotera APP_KEY, detta dekrypterar all krypterad data med den givna gamla nyckeln och lagrar den med den nya nyckeln i APP_KEY.',
        'arguments' => [
            'old_key' => 'Den gamla APP_KEY som är giltig för krypterad data',
        ],
        'options' => [
            'generate-new-key' => 'Om du inte har den nya nyckeln inställd i .env, använd APP_KEY från .env för att dekryptera data och generera en ny nyckel och ställa in den i .env',
            'forgot-key' => 'Om du inte har den gamla nyckeln måste du radera all krypterad data för att kunna fortsätta använda vissa LibreNMS-funktioner',
        ],
        'destroy' => 'Förstöra all krypterad konfigurationsdata?',
        'destroy_confirm' => 'Förstör bara all krypterad data om du inte kan hitta den gamla APP_KEY!',
        'cleared-cache' => 'Config cacheades, rensade cacheminnet för att se till att APP_KEY är korrekt. Vänligen kör lnms-nyckeln igen:rotate',
        'backup_keys' => 'Dokumentera BÅDA nycklarna! Om något går fel, ställ in den nya nyckeln i .env och använd den gamla nyckeln som ett argument för detta kommando',
        'backup_key' => 'Dokumentera den här nyckeln! Denna nyckel krävs för att komma åt krypterad data',
        'backups' => 'Detta kommando kan orsaka oåterkallelig förlust av data och kommer att ogiltigförklara alla webbläsarsessioner. Se till att du har säkerhetskopior.',
        'confirm' => 'Jag har säkerhetskopior och vill fortsätta',
        'decrypt-failed' => 'Det gick inte att dekryptera :item, hoppar över',
        'failed' => 'Det gick inte att dekryptera objekt(er).  Ställ in ny nyckel som APP_KEY och kör detta igen med den gamla nyckeln som argument.',
        'current_key' => 'Aktuell APP_KEY: :key',
        'new_key' => 'Ny APP_KEY: :key',
        'old_key' => 'Gammal APP_KEY: :key',
        'save_key' => 'Spara ny nyckel till .env?',
        'success' => 'Nycklar har roterats framgångsrikt!',
        'validation-errors' => [
            'not_in' => ':attribute får inte matcha nuvarande APP_KEY',
            'required' => 'Antingen gammal nyckel eller --generate-new-key krävs.',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => 'Den valda :option är ogiltig. Bör vara en av: :values',
        ],
    ],
    'maintenance:cleanup-database' => [
        'description' => 'Databasrensning av föräldralösa föremål.',
    ],
    'maintenance:cleanup-networks' => [
        'delete' => 'Raderar :count oanvända nätverk',
    ],
    'maintenance:fetch-ouis' => [
        'description' => 'Hämta MAC-OUI:er och lagra dem i cache för att visa leverantörsnamn för MAC-adresser',
        'options' => [
            'force' => 'Ignorera eventuella inställningar eller lås som hindrar kommandot från att köras',
            'wait' => 'Vänta en slumpmässig tid, som används av sceduelern för att förhindra serverbelastning',
        ],
        'disabled' => 'Mac OUI-integration inaktiverad (:setting)',
        'enable_question' => 'Aktivera Mac OUI-integration och schemalagd hämtning?',
        'recently_fetched' => 'MAC OUI-databas hämtades nyligen, hoppar över uppdatering.',
        'waiting' => 'Väntar :minutes minut innan du försöker uppdatera MAC OUI|Väntar :minutes minuter innan du försöker uppdatera MAC OUI',
        'starting' => 'Lagra Mac OUI i databasen',
        'downloading' => 'Laddar ner',
        'processing' => 'Bearbetar CSV',
        'saving' => 'Sparar resultat',
        'success' => 'Uppdaterade OUI/leverantörsmappningar. :count modifierad OUI|Uppdaterad. :count modifierade användargränssnitt',
        'error' => 'Fel vid bearbetning av Mac OUI:',
        'vendor_update' => 'Lägger till OUI :oui för :vendor',
    ],
    'maintenance:rrd-step' => [
        'description' => 'Konvertera RRD-filer för att matcha konfigurerade steg och hjärtslag',
        'arguments' => [
            'device' => 'Värdnamn, enhets-id eller alla',
        ],
        'options' => [
            'confirm' => 'Bekräfta att du har säkerhetskopierat dina rrd-filer.',
        ],
        'errors' => [
            'invalid' => 'Ogiltigt värdnamn eller enhets-ID har angetts',
        ],
        'confirm_backup' => 'Innan du fortsätter, vänligen bekräfta att du har säkerhetskopierat dina rrd-filer.',
        'mismatched_heartbeat' => ':file: Felaktigt hjärtslag. :ds != :hb',
        'skipping' => 'Hoppa över :file, steget är redan :step.',
        'converting' => 'Konvertera :file:',
        'summary' => 'Konverterat: :converted Misslyckades: :failed Hoppat över: :skipped',
    ],
    'maintenance:cleanup-syslog' => [
        'description' => 'Rensningssyslogposter som är äldre än ett angivet antal dagar',
        'arguments' => [
            'days' => 'Antal dagar att behålla syslog-poster (standard: syslog_purge konfigurerat värde)',
        ],
        'bad_days_input' => 'Dagarna måste vara numeriska',
        'bad_days_setting' => 'Syslog-rensning inaktiverad på grund av ogiltig syslog_purge-inställning',
        'delete' => 'Rensade syslog-poster äldre än :days dagar (:count rader)',
        'disabled' => 'Syslog-rensning inaktiverad, dagar <= 0',
    ],
    'maintenance:discover-ssl-certificates' => [
        'description' => 'Upptäck SSL-certifikat på enheter (HTTPS-port 443)',
        'options' => [
            'device' => 'Enhetsspecifikation att upptäcka: device_id, hostname eller all',
        ],
        'no_devices' => 'Inga enheter hittades',
        'summary' => 'Skapad: :created, Uppdaterad: :updated, Misslyckades: :failed',
    ],
    'maintenance:refresh-ssl-certificates' => [
        'description' => 'Uppdatera certifikatdata för lagrade SSL-certifikat',
        'options' => [
            'id' => 'Certifikat-ID att uppdatera (uteslut att uppdatera alla aktiverade)',
        ],
        'none' => 'Inga aktiverade certifikat att uppdatera',
        'summary' => 'Uppdaterad: :refreshed, Misslyckades: :failed',
    ],
    'plugin:disable' => [
        'description' => 'Inaktivera alla insticksprogram med det angivna namnet',
        'arguments' => [
            'plugin' => 'Namnet på insticksprogrammet som ska inaktiveras, eller "alla" för att inaktivera samtliga',
        ],
        'already_disabled' => 'Insticksprogrammet är redan inaktiverat',
        'disabled' => ':count insticksprogram inaktiverat|:count insticksprogram inaktiverade',
        'failed' => 'Det gick inte att inaktivera insticksprogrammet eller insticksprogrammen',
    ],
    'plugin:enable' => [
        'description' => 'Aktivera det senast tillagda insticksprogrammet med det angivna namnet',
        'arguments' => [
            'plugin' => 'Namnet på insticksprogrammet som ska aktiveras, eller "alla" för att aktivera samtliga',
        ],
        'already_enabled' => 'Insticksprogrammet är redan aktiverat',
        'enabled' => ':count insticksprogram aktiverat|:count insticksprogram aktiverade',
        'failed' => 'Det gick inte att aktivera insticksprogrammet eller insticksprogrammen',
    ],
    'port:tune' => [
        'description' => 'Justera port rrd-filer för att begränsa den maximala överföringshastigheten baserat på ifSpeed',
        'arguments' => [
            'device spec' => 'Enhetsspecifikation att ställa in: device_id, hostname, jokertecken (*), udda, jämnt, alla',
            'ifname' => 'Port ifName att matcha kan använda alla eller * för ett jokertecken',
        ],
        'device' => 'Enhet :device:',
        'port' => 'Tuning port :port',
    ],
    'report:devices' => [
        'description' => 'Skriv ut data från enheter',
        'columns' => 'Databaskolumner:',
        'synthetic' => 'Ytterligare fält:',
        'counts' => 'Relation räknas:',
        'arguments' => [
            'device spec' => 'Enhetsspecifikation för omröstning: device_id, hostname, jokertecken (*), udda, jämnt, alla',
        ],
        'options' => [
            'list-fields' => 'Skriv ut en lista med giltiga fält',
            'fields' => 'En kommaseparerad lista över fält som ska visas. Giltiga alternativ: enhetskolumnnamn från databasen, antal relationer (ports_count) och/eller displayName. Används inte för json-utgång.',
            'output' => 'Utdataformat för att visa data :types',
            'no-header' => 'Lägg inte till rubriken',
            'relationships' => 'En kommaseparerad lista över relationer som ska inkluderas. Används endast för json-utgång.',
            'list-relationships' => 'Skriv ut en lista/beskrivning av relationer',
            'all-relationships' => 'Inkludera alla relationer. -r, --relationer tar ordförandeskapet.',
            'devices-as-array' => 'Returnera utdata som en JSON-array istället för en JSON-post per enhet och rad',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => 'Använd en av --sonder och --mål',
        'config-insufficient' => 'För att generera en rökningskonfiguration måste du ha ställt in "smokeping.probes", "fping" och "fping6" i din konfiguration',
        'dns-fail' => 'gick inte att lösa och uteslöts från konfigurationen',
        'description' => 'Skapa en konfiguration som lämpar sig för användning med rökning',
        'header-first' => 'Den här filen genererades automatiskt av "lnms smokeping:generate',
        'header-second' => 'Lokala ändringar kan skrivas över utan förvarning eller säkerhetskopiering',
        'header-third' => 'För mer information se https://docs.librenms.org/Extensions/Smokeping/"',
        'no-devices' => 'Inga kvalificerade enheter hittades – enheter får inte inaktiveras.',
        'no-probes' => 'Minst en sond krävs.',
        'options' => [
            'probes' => 'Generera sondlista - används för att dela upp rökningskonfigurationen i flera filer. Konflikter med "--mål"',
            'targets' => 'Generera mållistan - används för att dela upp rökningskonfigurationen i flera filer. Konflikter med "--probes"',
            'no-header' => 'Lägg inte till en kommentar i början av den genererade filen',
            'no-dns' => 'Hoppa över DNS-uppslagningar',
            'single-process' => 'Använd endast en enda process för rökning',
            'compat' => '[utfasad] Efterlikna beteendet hos gen_smokeping.php',
        ],
    ],
    'snmp:fetch' => [
        'description' => 'Kör snmp-fråga mot en enhet',
        'arguments' => [
            'device spec' => 'Enhetsspecifikation för omröstning: device_id, hostname, jokertecken (*), udda, jämnt, alla',
            'oid(s)' => 'En eller flera SNMP OID att hämta.  Bör vara antingen MIB::oid eller en numerisk oid',
        ],
        'failed' => 'SNMP-kommandot misslyckades!',
        'numeric' => 'Numerisk',
        'oid' => 'OID',
        'options' => [
            'output' => 'Ange utdataformatet :formats',
            'numeric' => 'Numeriska OID',
            'depth' => 'Djup att gruppera snmp-tabellen på. Vanligtvis samma antal som objekten i tabellens index',
        ],
        'not_found' => 'Enheten hittades inte',
        'textual' => 'Textuellt',
        'value' => 'Värde',
    ],
    'translation:generate' => [
        'description' => 'Generera uppdaterade json-språkfiler för användning i webbgränssnittet',
    ],
    'user:add' => [
        'description' => 'Lägg till en lokal användare, du kan bara logga in med denna användare om auth är inställt på mysql',
        'arguments' => [
            'username' => 'Användarnamnet som användaren kommer att logga in med',
        ],
        'options' => [
            'descr' => 'Användarbeskrivning',
            'email' => 'E-post att använda för användaren',
            'password' => 'Lösenord för användaren, om det inte ges kommer du att bli tillfrågad',
            'full-name' => 'Fullständigt namn för användaren',
            'role' => 'Ställ in användaren på önskad roll :roles',
        ],
        'form' => [
            'username' => 'Användarnamn',
            'password' => 'Lösenord',
            'roles' => 'Välj användarroll(er)',
            'email' => 'E-post (valfritt)',
            'full-name' => 'Fullständigt namn (valfritt)',
            'descr' => 'Beskrivning (valfritt)',
        ],
        'success' => 'Lyckad användare: :username',
        'wrong-auth' => 'Varning! Du kommer inte att kunna logga in med denna användare eftersom du inte använder MySQL auth',
    ],
];
