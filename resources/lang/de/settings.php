<?php

return [
    'readonly' => 'Ist in config.php konfiguriert. Zur Aktivierung entfernen Sie den Eintrag aus der config.php.',
    'groups' => [
        'alerting' => 'Alarmierung',
        'auth' => 'Authentifizierung',
        'external' => 'Extern',
        'global' => 'Global',
        'os' => 'OS',
        'discovery' => 'Discovery',
        'poller' => 'Poller',
        'system' => 'System',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => 'Generelle Alarm Einstellungen',
            'email' => 'E-Mail Optionen',
        ],
        'auth' => [
            'general' => 'Generelle Authentifizierungseinstellungen',
            'ad' => 'Active Directory Einstellungen',
            'ldap' => 'LDAP Einstellungen',
        ],
        'discovery' => [
            'general' => 'Generelle Discovery Einstellungen',
        ],
        'external' => [
            'binaries' => 'Programmpfade',
            'location' => 'Location Settings',
            'graylog' => 'Graylog Integration',
            'oxidized' => 'Oxidized Integration',
            'peeringdb' => 'PeeringDB Integration',
            'nfsen' => 'NfSen Integration',
            'unix-agent' => 'Unix-Agent Integration',
        ],
        'poller' => [
            'distributed' => 'Distributed Poller',
            'ping' => 'Ping',
            'rrdtool' => 'RRDTool Konfiguration',
            'snmp' => 'SNMP',
        ],
        'system' => [
            'cleanup' => 'Bereinigung',
            'proxy' => 'Proxy',
            'updates' => 'Updates',
            'server' => 'Server',
        ],
        'webui' => [
            'availability-map' => 'Availability Map Einstellungen',
            'graph' => 'Graphen Einstellungen',
            'dashboard' => 'Dashboard Einstellungen',
            'search' => 'Sucheinstellungen',
            'style' => 'Theme',
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Behalte inaktive Nutzer',
                'help' => 'Benutzer werden von LibreNMS gelöscht nach diesen Tagen Inaktivität. 0 bedeutet nie und der Benutzer wird neu erstellt wenn er sich erneut anmeldet',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Prüfe auf doppelte IPs wenn ein Gerät hinzugefügt wird',
            'help' => 'Wenn ein Gerät als IP Adresse hinzugefügt wird, wird geprüft das diese IP Adresse noch nicht vorhanden ist. Ist die IP Adresse schon vorhanden wird sie nicht hinzugefügt. Wird das Gerät als Hostname hinzugefügt wird dieser Check nicht durchgeführt. Ist diese Einstellung aktiviert, werden die Hostnamen aufgelöst und der Check wird ebenso durchgeführt. Dies vermeidet versehentliches hinzufügen von Geräteduplikaten.',
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Standard Meldung bis der Alarm abklingt',
                'help' => 'Standard Meldung bis der Alarm abklingt',
            ],
            'admins' => [
                'description' => 'Melde Alarme den Administratoren',
                'help' => 'Alarmiere Administratoren',
            ],
            'default_copy' => [
                'description' => 'Sende alle E-Mail Alarme als Kopie zum Standard Kontakt',
                'help' => 'Sende alle E-Mail Alarme als Kopie zum Standard Kontakt',
            ],
            'default_if_none' => [
                'description' => 'Kann nicht auf der Webseite konfiguriert werden?',
                'help' => 'Sende E-Mail zum Standard Kontakt wenn keine anderen Kontakte gefunden werden',
            ],
            'default_mail' => [
                'description' => 'Standard Kontakt',
                'help' => 'Der Standard E-Mail Kontakt',
            ],
            'default_only' => [
                'description' => 'Sende Alarme nur zum Standard Kontakt',
                'help' => 'Alarmiere nur den Standard E-Mail Kontakt',
            ],
            'disable' => [
                'description' => 'Deaktivere Alarmierungen',
                'help' => 'Stoppe Generierung von Alarmen',
            ],
            'fixed-contacts' => [
                'description' => 'Update der Kontakt E-Mail Adresse werden nicht berücksichtigt',
                'help' => 'Wenn aktiviert werden jegliche Änderungen in sysContact oder Benutzer E-Mails nicht berücksichtigt solange Alarme aktiv sind',
            ],
            'globals' => [
                'description' => 'Melde Alarme zu Benutzer welche nur Leserechte haben',
                'help' => 'Alarmiere Administratoren welche nur Leserechte haben',
            ],
            'syscontact' => [
                'description' => 'Benachrictige sysContact über Alarme',
                'help' => 'Sende AlarmierungsE-Mail an den SNMP sysContact',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Aktiviere Benachrichtigungen per E-Mail',
                    'help' => 'Benachrichtige via E-Mail',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Toleranzfenster für cron',
                'help' => 'Toleranzfenster in Sekunden',
            ],
            'users' => [
                'description' => 'Benachrichtige normale Benutzer über Alarme',
                'help' => 'Alarmiere normale Benutzer',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Entferne Alarm Logeinträge älter als',
            'help' => 'Bereinigung wird erledigt von daily.sh',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Erlaube doppelten sysName',
            'help' => 'Standardmässig ist das hinzufügen von doppelten sysNames deaktiviert um zu verhindern das Netzwerkinterfaces mehrfach hinzugefügt werden',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Erlaube Gästen Zugriff auf Graphen',
            'help' => 'Erlaube jedem Zugriff auf Graphen ohne eingeloggt zu sein',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Erlaube diesem Netzwerk Zugriff auf Graphen',
            'help' => 'Erlaube den angegebenen Netzwerken Zugrif auf Graphen ohne eingeloggt zu sein (funktioniert nicht wenn authentifizierte Graphen aktiviert sind)',
        ],
        'api_demo' => [
            'description' => 'Dies ist eine Demo',
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'API Schlüssel für PowerDNS Recursor',
                    'help' => 'API Schlüssel für PowerDNS Recursor App zur direkten Verbindung',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor nutzt HTTPS?',
                    'help' => 'Nutze HTTPS anstatt HTTP für PowerDNS Recursor App zur direkten Verbindung',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor Port',
                    'help' => 'TCP Port für die Nutzung von PowerDNS Recursor App zur direkten Verbindung',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Key zum cachen von autonomen Systembeschreibungen',
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'Gruppen und Benutzer müssen Teil von  dn sein. Beispiel: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Prüfe Zertifikate',
            'help' => 'Prüfe die Gültigkeit der Zertifikate. Einige Server nutzen selbstsignierte Zertifikate. Die Deaktivierung erlaubt dies.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'LDAP Gruppenfilter',
            'help' => 'Active Directory LDAP Filter zur Auswahl von Gruppen',
        ],
        'auth_ad_groups' => [
            'description' => 'Gruppenzugriff',
            'help' => 'Definiere Gruppen die Zugriff haben',
        ],
        'auth_ad_user_filter' => [
            'description' => 'LDAP Benutzerfilter',
            'help' => 'Active Directory LDAP Filter zur Auswahl von Benutzern',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribute zur Prüfung von Benutzernamen',
                'help' => 'Attribute welche zur Identifizierung der Benutzer an Hand des Usernamen genutzt werden',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (überschreibt bind username)',
            'help' => 'Voller DN von bind user',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'LDAP Passwort',
            'help' => 'Passwort für bind user',
        ],
        'auth_ldap_binduser' => [
            'description' => 'LDAP Benutzername',
            'help' => 'Benutzt zum durchsuchen des LDAP Servers wenn kein Nutzer eingeloggt ist in (alerts, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (überschreibt Benutzername)',
            'help' => 'voller DN des Benutzernamen',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Active Directory Passwort',
            'help' => 'Passwort für Active Directory',
        ],
        'auth_ad_binduser' => [
            'description' => 'Active Directory Benutzername',
            'help' => 'Benutzt zum durchsuchen des AD Server wenn kein Nutzer eingeloggt ist in (alerts, API, etc)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP Cache Gültigkeit',
            'help' => 'Speichert temporär LDAP Suchergebnisse.  Erhöht die Geschwindigkeit, aber die Daten können veraltet sein.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Zeige Debugausgaben',
            'help' => 'Zeige Debug Informationene. Zeigt eventuell private Informationen an, lassen Sie es nicht aktiviert.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Mail Einstellungen',
        ],
        'auth_ldap_group' => [
            'description' => 'Zugriffsgruppen DN',
            'help' => 'Name der Gruppe für normalen Level Zugriff. Beispiel: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'Gruppen base DN',
            'help' => 'Name um nach Gruppen zu suchen. Beispiel: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Gruppenmitglieder Attribute',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Finde Gruppenmitglieder bei',
            'options' => [
                'username' => 'Benutzername',
                'fulldn' => 'Voller DN (benutze prefix und suffix)',
                'puredn' => 'DN Suche (suche UID Attribute)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Gruppenzugriff',
            'help' => 'Definiere LDAP Gruppen die Zugriff haben',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP Port',
            'help' => 'Port um zum Server zu verbinden. Für LDAP sollte es 389 sein, für LDAPS ist es 636',
        ],
        'auth_ldap_prefix' => [
            'description' => 'Benutzer Prefix',
            'help' => 'Wird verwendet um einen Benutzernamen speziell anzugeben',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP Server(s)',
            'help' => 'Definiere Server(s), Leerzeichen getrennt. Prefix mit ldaps:// for SSL',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Benutze STARTTLS',
            'help' => 'Benutze STARTTLS um Verbindungen abzusichern.  Alternative zu LDAPS.',
            'options' => [
                'disabled' => 'Deaktiviert',
                'optional' => 'Optional',
                'required' => 'Benötigt',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'Benutzer suffix',
            'help' => 'Benutzt um einen Benutzernamen zu spezifizieren',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Verbindungstimeout',
            'help' => 'Wenn einer oder mehrere Server nicht antworten, höhere Timeout verursachen langsameren Zugriff. Zu geringe Zugriffszeiten können Verbindungsfehler verursachen.',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Unique ID Attribute',
            'help' => 'LDAP Attribute werden genutzt um Benutzer zu identifizieren, muss numerisch sein',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Benutze volle user DN',
            'help' => "Benutzt user's voller DN als Wert der Mitglieder Attribute in einer Gruppe anstelle von Mitgliedern: Username nutzt den prefix und suffix. (es ist ein Mitglied: uid=username,ou=groups,dc=domain,dc=com)",
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP Version',
            'help' => 'LDAP Version zur Kommunikation mit dem Server.  Gewöhnlich sollte es v3 sein',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Authentifizierungsmethode (Achtung!)',
            'help' => "Authentifierungsmethode.  Achtung, Sie könnnten die Möglichkeit verlieren sich einzuloggen. Sie können es überschreiben indem Sie \$config['auth_mechanism'] = 'mysql'; in ihre config.php eintragen",
            'options' => [
                'mysql' => 'MySQL (default)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP Authentifizierung',
                'ad-authorization' => 'Extern authentifiziertes AD',
                'ldap-authorization' => 'Extern authentifiziertes LDAP',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Sessiongültigkeit',
            'help' => 'Anzahl an Tagen welche ein Benutzer eingeloggt bleibt wenn er die Checkbox zum eingeloggt bleiben nutzt',
        ],
        'authlog_purge' => [
            'description' => 'Entferne Auth log Einträge welche älter sind als (Tage)',
            'help' => 'Wird durch daily.sh erledigt',
        ],
        'base_url' => [
            'description' => 'Spezifiziere URL',
            'help' => 'Sollte nur gesetzt werden wenn man den Zugriff nur über einen bestimmten Hostnamen/Port erlauben möchte',
        ],
        'device_perf_purge' => [
            'description' => 'Entferne Performanzdaten welche älter sind als (Tage)',
            'help' => 'Wird durch daily.sh erledigt',
        ],
        'distributed_poller' => [
            'description' => 'aktiviere Distributed Polling (benötigt zusätzliche Konfiguration)',
            'help' => 'aktiviere systemweites Distributed Polling. Dies wird genutzt für Lastverteilung und nicht remote Polling. Lesen Sie hierzu folgende Dokumentation: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'distributed_poller_group' => [
            'description' => 'Standard Poller Gruppe',
            'help' => 'Die Standard Poller Gruppe aller Poller sollte pollen, wenn sie nicht in config.php konfiguriert ist',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached Host',
            'help' => 'Hostname oder IP Adresse des Memcached Servers. Dies wird benötigt um poller_wrapper.py sowie daily.sh einzufrieren',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached Port',
            'help' => 'Der Port des Memcached Server. Standard ist 11211',
        ],
        'email_auto_tls' => [
            'description' => 'Aktiviere / deaktiviere Auto TLS Unterstützung',
            'options' => [
                'true' => 'Ja',
                'false' => 'Nein',
            ],
        ],
        'email_backend' => [
            'description' => 'Wie werden E-Mails versendet',
            'help' => 'Das Backend welches für den Versand von E-Mail genutzt wird. Es kann mail, sendmail oder SMTP sein',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'From E-Mail Adresses',
            'help' => 'E-mail Addresse welche für den Versand von E-Mails genutzt wird (from)',
        ],
        'email_html' => [
            'description' => 'Nutze HTML E-Mails',
            'help' => 'Sende HTML E-Mails',
        ],
        'email_sendmail_path' => [
            'description' => 'Pfad zu sendmail wenn genutzt',
        ],
        'email_smtp_auth' => [
            'description' => 'Aktiviere / Deaktiviere SMTP Authentifizierung',
        ],
        'email_smtp_host' => [
            'description' => 'SMTP Host für den E-Mail Versand',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP Passwort',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP Port',
        ],
        'email_smtp_secure' => [
            'description' => 'Aktiviere / Deaktiviere Verschlüsselung (nutze TLS oder SSL)',
            'options' => [
                '' => 'Deaktiviert',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP Timeout',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP Benutzername',
        ],
        'email_user' => [
            'description' => 'From Name',
            'help' => 'Name benutzt als Teil der "from" E-Mail Adresse',
        ],
        'eventlog_purge' => [
            'description' => 'Entferne Event Log Daten welche älter sind als (Tage)',
            'help' => 'Wird durch daily.sh erledigt',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Überschreibt das Standard favicon.',
        ],
        'fping' => [
            'description' => 'Pfad zu fping',
        ],
        'fping6' => [
            'description' => 'Pfad zu fping6',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping Anzahl',
                'help' => 'Anzahl von Pings zur Prüfung ob ein Gerät online oder offline ist, via ICMP',
            ],
            'interval' => [
                'description' => 'fping Interval',
                'help' => 'Die Wartezeit in Millisekunden zwischen den Pings',
            ],
            'timeout' => [
                'description' => 'fping Timeout',
                'help' => 'Die Wartezeit in Millisekunden auf eine Echo Antwort vor dem aufgeben',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Geocoding API Key',
                'help' => 'Geocoding API Key (Benötigt zur Funktion)',
            ],
            'engine' => [
                'description' => 'Geocoding System',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                ],
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'Base URI',
                'help' => 'Überschreibt die base uri falls Sie den Graylog Standardwert geändert haben.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Geräteübersicht Log Level',
                    'help' => 'Definiert den maximalen Log Level welcher in der Geräteübersicht angezeigt wird.',
                ],
                'rowCount' => [
                    'description' => 'Geräteübersicht Anzahl an Zeilen',
                    'help' => 'Definiert die Anzahl an Zeilen welche in der Geräteübersicht angezeigt werden.',
                ],
            ],
            'password' => [
                'description' => 'Passwort',
                'help' => 'Passwort für den Zugriff auf die Graylog API.',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Port welcher für den Zugriff auf die Graylog API genutzt wird. Wenn nicht gesetzt wird 80 für http und 443 für https genutzt.',
            ],
            'server' => [
                'description' => 'Server',
                'help' => 'IP Addresse oder Hostname des Graylog Server API Endpunkts.',
            ],
            'timezone' => [
                'description' => 'Anzeigezeitzone',
                'help' => 'Graylog Zeiten werden in GMT gespeichert. Diese Einstellung ändert die angezeigte Zeitzone. Der Wert muss kompatibel mit PHP Zeitzoneneinstellung sein.',
            ],
            'username' => [
                'description' => 'Benutzername',
                'help' => 'Benutzername für den Zugriff auf die Graylog API.',
            ],
            'version' => [
                'description' => 'Version',
                'help' => 'Dies wird genutzt um automatisch eine base_uri für die Graylog API zu erstellen. Haben Sie die API modifiziert konfigurieren Sie diesen Wert entsprechend ihren Anpassungen.',
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) Proxy',
            'help' => 'Konfigurieren Sie dies als Fallback wenn http_proxy oder https_proxy Umgebungsvariablen nicht verfügbar sind.',
        ],
        'ipmitool' => [
            'description' => 'Pfad zum ipmitool',
        ],
        'login_message' => [
            'description' => 'Begrüssungsmeldung',
            'help' => 'Wird auf der Anmeldeseit angezeigt',
        ],
        'mono_font' => [
            'description' => 'Monospaced Font',
        ],
        'mtr' => [
            'description' => 'Pfad zu mtr',
        ],
        'mydomain' => [
            'description' => 'Primäre Domain',
            'help' => 'Diese Domaine wird genutzt für Netzwerkautoerkennung und andere Prozesse. LibreNMS wird versuchen diese Hostnamen hinzuzufügen.',
        ],
        'nfsen_enable' => [
            'description' => 'Aktiviere NfSen',
            'help' => 'Aktiviere Integration mit NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD Verzeichnisse',
            'help' => 'Diese Werte geben an wo ihre NFSen RRD Dateien gespeichert werden.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Definiere NfSen Unterverzeichnisstruktur',
            'help' => 'Dies muss mit der Unterverzeichnisstruktur welche Sie in NfSen konfiguriert haben übereinstimmen. 1 ist der Standard.',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Maximales topN Werte für stats',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => 'Standard Top N',
        ],
        'nfsen_stat_default' => [
            'description' => 'Standard Stat',
        ],
        'nfsen_order_default' => [
            'description' => 'Standard Order',
        ],
        'nfsen_last_default' => [
            'description' => 'Standard Last',
        ],
        'nfsen_lasts' => [
            'description' => 'Standard Last Options',
        ],
        'nfsen_split_char' => [
            'description' => 'Splitte Char',
            'help' => 'Diese Werte geben uns an welche Zeichen ersetzt weden sollen. `.` in den Hostnamen der Geräte. Gewöhnlich: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'Dateinamensuffix',
            'help' => 'Dies ist ein sehr wichtig da Gerätename in NfSen auf 21 Zeichen limitiert sind. Dies bedeutet das es problematisch werden kann full domain names zu schrumpfen, daher wird unnützen gewöhnlich entfernt.',
        ],
        'nmap' => [
            'description' => 'Pfad zu nmap',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS hostname',
            'help' => 'Sollte auf den hostname/ip des LibreNMS Servers gesetzt werden',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Definiert die Standardgruppe',
            ],
            'enabled' => [
                'description' => 'Aktiviert Oxidized Support',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Aktiviere config Versionierungszugriff',
                    'help' => 'Aktiviere Oxidized config Versionierung (benötigt ein GIT Backend)',
                ],
            ],
            'group_support' => [
                'description' => 'Aktiviere die Übergabe von Gruppen an Oxidized',
            ],
            'reload_nodes' => [
                'description' => 'Aktualisiere Oxidized Node Liste, wenn ein Node hinzugefügt wurde',
            ],
            'url' => [
                'description' => 'URL zur Oxidized API',
                'help' => 'Oxidized API URL (Zum Beispiel: http://127.0.0.1:8888)',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Aktiviere PeeringDB lookup',
                'help' => 'Aktiviere PeeringDB lookup (Daten werden mit daily.sh heruntergeladen)',
            ],
        ],
        'ping' => [
            'description' => 'Pfad zu ping',
        ],
        'ports_fdb_purge' => [
            'description' => 'Port FDB Einträge älter als',
            'help' => 'Bereinigung wird erledigt durch daily.sh',
        ],
        'ports_purge' => [
            'description' => 'Ports älter als (days)',
            'help' => 'Bereinigung wird erledigt durch daily.sh',
        ],
        'public_status' => [
            'description' => 'Zeige den Status öffentlich an',
            'help' => 'Zeigt den Status mancher Geräte auf der Login Seite an ohne das man sich anmelden muss.',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Ändere den RRD heartbeat Wert (Standard 600)',
            ],
            'step' => [
                'description' => 'Ändere die RRD Schrittweite (Standard 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD Verzeichnis',
            'help' => 'Verzeichnis für RRD Dateien.  Standardmässig ist RRD innerhalb des LibreNMS Verzeichnisses.  Eine Änderung verschiebt nicht die vorhandenen RRD Dateien.',
        ],
        'rrd_purge' => [
            'description' => 'RRD Dateien älter als (Tage)',
            'help' => 'Bereinigung wird erledigt durch daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'RRD Format Einstellungen',
            'help' => 'Dies kann nicht verändert werden ohne existierende RRD Dateien zu löschen. Ebeneo könnte dies die Größe der RRD Dateien positiv oder auch negativ verändern.',
        ],
        'rrdcached' => [
            'description' => 'Aktiviere rrdcached (socket)',
            'help' => 'Aktiviert rrdcached mit Angabe des rrdcaced Sockets. Dies kann ein Unix oder ein Netzwerksocket sein(unix:/run/rrdcached.sock oder localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Pfad zu rrdtool',
        ],
        'rrdtool_tune' => [
            'description' => 'Tune alle RRD port Dateien zur Nutzung maximaler Werte',
            'help' => 'Automatisches Tuning auf maximale Werte für RRD port Dateien',
        ],
        'sfdp' => [
            'description' => 'Pfad zu sfdp',
        ],
        'shorthost_target_length' => [
            'description' => 'verkürzt einen Hostnamen auf diese maximale Länge',
            'help' => 'kürzt einen Hostnamen auf diese maximale Länge, aber beinhaltet immer die maximal mögliche Subdomain',
        ],
        'site_style' => [
            'description' => 'Definiert den CSS Style der Seite',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transport (Priorität)',
                'help' => 'Sortiere aktivierte Transports nach der gewünschten Priorität.',
            ],
            'version' => [
                'description' => 'Version (Prioriät)',
                'help' => 'Sortiere aktivierte Versionen nach der gewünschten Priorität.',
            ],
            'community' => [
                'description' => 'Communities (Priorität)',
                'help' => 'Gebe Community Strings für v1 und v2c and und sortiere sie nach der gewünschten Priorität',
            ],
            'max_repeaters' => [
                'description' => 'Max Repeaters',
                'help' => 'Definiere repeaters für SNMP bulk requests',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Definiere den  TCP/UDP Port für SNMP',
            ],
            'v3' => [
                'description' => 'SNMP v3 Authentifizierung (Priorität)',
                'help' => 'Definiere v3 Authentifizierungsvariablen und sortiere sie nach der gewünschten Priorität',
                'auth' => 'Auth',
                'crypto' => 'Crypto',
                'fields' => [
                    'authalgo' => 'Algorithm',
                    'authlevel' => 'Level',
                    'authname' => 'Username',
                    'authpass' => 'Password',
                    'cryptoalgo' => 'Algorithm',
                    'cryptopass' => 'Password',
                ],
                'level' => [
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication and Privacy',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'Pfad zu snmpbulkwalk',
        ],
        'snmpget' => [
            'description' => 'Pfad zu snmpget',
        ],
        'snmpgetnext' => [
            'description' => 'Pfad zu snmpgetnext',
        ],
        'snmptranslate' => [
            'description' => 'Pfad zu snmptranslate',
        ],
        'snmpwalk' => [
            'description' => 'Pfad zu snmpwalk',
        ],
        'syslog_filter' => [
            'description' => 'Filtere Syslog Meldungen beinhaltend',
        ],
        'syslog_purge' => [
            'description' => 'Syslog Einträge älter als (Tage)',
            'help' => 'Bereinigung wird erledigt durch daily.sh',
        ],
        'title_image' => [
            'description' => 'Titelbild',
            'help' => 'Überschreibt das Standardtitelbild.',
        ],
        'traceroute' => [
            'description' => 'Pfad zu  traceroute',
        ],
        'traceroute6' => [
            'description' => 'Pfad zu traceroute6',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent Verbindungstimeout',
            ],
            'port' => [
                'description' => 'Standard Unix-agent Port',
                'help' => 'Standard Port des Unix-agent (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent Lesetimeout',
            ],
        ],
        'update' => [
            'description' => 'Aktiviere Updates mittels ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Definiere Updatekanal',
            'options' => [
                'master' => 'master',
                'release' => 'release',
            ],
        ],
        'virsh' => [
            'description' => 'Pfad zu virsh',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Verfügbarkeit box width',
                'help' => 'Eingabe gibt die Breite in Pixel für die Boxgröße in voller Ansicht an',
            ],
            'availability_map_compact' => [
                'description' => 'Verfügbarkeit map compact view',
                'help' => 'Verfügbarkeitskartenansicht mit kleinen Indikatoren',
            ],
            'availability_map_sort_status' => [
                'description' => 'Sortiere nach Status',
                'help' => 'Sortiere Geräte und Services nach Status',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Nutze Gerätegruppenfilter',
                'help' => 'Aktiviert die Nutzung des Gerätegruppenfilters',
            ],
            'default_dashboard_id' => [
                'description' => 'Standard Dashboard',
                'help' => 'Globales Standard für alle Nutzer welche kein eigenes haben',
            ],
            'dynamic_graphs' => [
                'description' => 'aktiviert dynamische Graphen',
                'help' => 'Akiviert dynmaischen Graphen, welche gezoomt und gescrollt werden können',
            ],
            'global_search_result_limit' => [
                'description' => 'Definiert das maximale Sucherergebnis',
                'help' => 'Definiert das globale maximale Suchergebnis',
            ],
            'graph_stacked' => [
                'description' => 'Nutze gestapelte Graphen',
                'help' => 'Zeige gestapelte anstelle von invertierten Graphen',
            ],
            'graph_type' => [
                'description' => 'Definiere den Graph Typ',
                'help' => 'Definiere den Standard Graph Typ',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Definiere die minimale Graph Höhe',
                'help' => 'Minimale Graph Höhe (Standard: 300)',
            ],
        ],
        'whois' => [
            'description' => 'Pfad zu whois',
        ],
    ],
    'twofactor' => [
        'description' => 'Aktiviere Zweifaktor Authentifizierung',
        'help' => 'Aktiviert die integrierte Zweifaktor Authentifizierung. Sie müssen jedes Konto konfigurieren um es zu aktivieren.',
    ],
    'units' => [
        'days' => 'Tage',
        'ms' => 'ms',
        'seconds' => 'Sekunden',
    ],
    'validate' => [
        'boolean' => ':value ist kein gültiger Boolean',
        'color' => ':value ist kein gültiger hex Farbcode',
        'email' => ':value is keine gültige E-Mail',
        'integer' => ':value ist kein Integer',
        'password' => 'Das Passwort ist falsch',
        'select' => ':value ist kein erlaubter Wert',
        'text' => ':value ist nicht erlaubt',
        'array' => 'Ungültiges Format',
    ],
];
