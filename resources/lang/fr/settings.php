<?php

return [
    'readonly' => 'Dejà configuré dans config.php, le supprimer avant de pouvoir l\'éditer ici',
    'groups' => [
        'alerting' => 'Alertes',
        'api' => 'API',
        'authorization' => 'Autorisation',
        'auth' => 'Comptes et Utilisateurs',
        'external' => 'Modules Externes',
        'global' => 'Liste Globale',
        'os' => 'OS',
        'discovery' => 'Découverte',
        'poller' => 'Sondeurs',
        'system' => 'Système',
        'webui' => 'Interface Web',
    ],
    'sections' => [
        'alerting' => [
            'general' => 'Réglages généraux d\'Alertes',
            'email' => 'Options des emails',
            'rules' => 'Réglages généraux des Règles d\'Alertes',
        ],
        'api' => [
            'cors' => 'CORS',
        ],
        'auth' => [
            'general' => 'Réglages généraux d\'Accès',
            'ad' => 'Réglages Active Directory',
            'ldap' => 'Réglages LDAP',
        ],
        'authorization' => [
            'device-group' => 'Réglages de Groupes d\'équipements',
        ],
        'discovery' => [
            'general' => 'Réglages généraux de Découverte',
            'route' => 'Module: Routes',
            'discovery_modules' => 'Activation des Modules de Découverte',
            'storage' => 'Module: Stockage',
        ],
        'external' => [
            'binaries' => 'Emplacements des Exécutables',
            'location' => 'Cartes',
            'graylog' => 'Intégration Graylog',
            'oxidized' => 'Intégration Oxidized',
            'mac_oui' => 'Intégration des prefixes OUI d\'adresses mac',
            'peeringdb' => 'Intégration PeeringDB',
            'nfsen' => 'Intégration NfSen',
            'unix-agent' => 'Intégration Unix-Agent',
            'smokeping' => 'Intégration Smokeping',
            'snmptrapd' => 'Intégration des traps SNMP',
        ],
        'poller' => [
            'distributed' => 'Sondeurs distribués',
            'ping' => 'Ping',
            'rrdtool' => 'Configuration de RRDTool',
            'snmp' => 'SNMP',
        ],
        'system' => [
            'cleanup' => 'Nettoyage',
            'proxy' => 'Proxy',
            'updates' => 'Mises à jour',
            'server' => 'Serveur',
        ],
        'webui' => [
            'availability-map' => 'Disponibilités et cartes',
            'graph' => 'Graphiques',
            'dashboard' => 'Tableaux de Bord',
            'search' => 'Recherche',
            'style' => 'Style',
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Conserver les utilisateurs inactifs pendant',
                'help' => 'Les utilisateurs seront effacés de LibreNMS après ce nombre de jour(s) sans connexion. 0 signifie `jamais`. Les utilisteurs seront créés à nouveau quand ils se reconnecteront.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Vérifier les adresses IP déjà connues avant d\'ajouter un équipement',
            'help' => 'Si un équipement est ajouté via son adresse IP, une vérification sera faite pour contrôler que l\'IP n\'est pas déjà connue. Si c\'est le cas, l\'équipement ne sera pas ajouté. Si l\'équipement est ajouté par son nom DNS, cette vérification n\'est pas effectuée. Si ce réglage est actif, les noms DNS sont résolus et la vérification sera bien effectuée. Ceci permet d\'éviter les duplications accidentelles d\'équipements.',
        ],
        'alert_rule' => [
            'severity' => [
                'description' => 'Sévérité',
                'help' => 'Séverité par défault de l\'alerte',
            ],
            'max_alerts' => [
                'description' => 'Répétitions',
                'help' => 'Nombre d\'Alertes à envoyer',
            ],
            'delay' => [
                'description' => 'Delai avant notification',
                'help' => 'Delai avant de déclencher l\'envoi des notifications',
            ],
            'interval' => [
                'description' => 'Intervalle de répétition',
                'help' => 'Intervalle avant de déclencher à nouveau une alerte',
            ],
            'mute_alerts' => [
                'description' => 'Silence',
                'help' => 'Désactiver l\'envoi des notifications et conserver seulement l\'affichage GUI',
            ],
            'invert_rule_match' => [
                'description' => 'Inverser la condition',
                'help' => 'Alerter seulement si la règle est fausse',
            ],
            'recovery_alerts' => [
                'description' => 'Notifier le rétablissement',
                'help' => 'Générer une notification quand l\'alerte est désactivée',
            ],
            'invert_map' => [
                'description' => 'Tous les équipements sauf liste',
                'help' => 'L\'alerte s\'appliquera à tous les équipements sauf ceux spécifiés dans la liste',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Default acknowledge until alert clears option',
                'help' => 'Default acknowledge until alert clears',
            ],
            'admins' => [
                'description' => 'Envoyer les alertes aux administrateurs',
            ],
            'default_copy' => [
                'description' => 'Envoyer une copie des emails à tous les contacts par défaut',
            ],
            'default_if_none' => [
                'description' => 'Envoyer au contact par défaut si aucun contact',
                'help' => 'Si aucun contact n\'est trouvé pour cette alerte, le contact par défaut est utilisé',
            ],
            'default_mail' => [
                'description' => 'Contact par défaut',
                'help' => 'Adresse email de contact par défaut',
            ],
            'default_only' => [
                'description' => 'Envoyer les alertes seulement au contact par défaut',
            ],
            'disable' => [
                'description' => 'Désactiver les alertes',
                'help' => 'Empèche la génération des alertes',
            ],
            'fixed-contacts' => [
                'description' => 'Ne pas mettre à jour les contacts',
                'help' => 'Si cette option est active, tout changement sur sysContact ou sur un utilisateur sera ignoré tant que l\'alerte est active. ',
            ],
            'globals' => [
                'description' => 'Envoyer les alertes aux utilisateurs en lecture seule',
                'help' => 'Envoyer les alertes aux utilisateurs en lecture seules',
            ],
            'syscontact' => [
                'description' => 'Envoyer les alertes au `sysContact`',
                'help' => 'Envoyer les alertes au `sysContact` recu de l\'équipement concerné',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Activer les alertes par email',
                    'help' => 'Transport Email, module d\'envoi d\'alertes par email',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Fenêtre de tolérence',
                'help' => 'Fenêtre de tolérence pour `cron`, en secondes',
            ],
            'users' => [
                'description' => 'Envoyer les alertes aux utilisateurs standards',
                'help' => 'Envoyer les alertes aux utilisateurs standards',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Journaux d\'alertes plus anciens que',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Autoriser des `sysName` en doublons.',
            'help' => 'Par défault, les `sysName` en double ne sont pas ajoutés, afin d\'éviter qu\'un équipement ayant plusieurs interfaces soit ajouté plusieurs fois.',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Autoriser l\'accès aux graphes sans login',
            'help' => 'Autoriser toutes les requêtes sans login pour les graphes',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Autoriser l\'accès aux graphes pour les réseaux suivants sans login',
            'help' => '(ne s\'applique pas si l\'accès sans login est déjà permis)',
        ],
        'api_demo' => [
            'description' => 'Ceci est la démo',
            'help' => 'Démo',
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'Clef d\'API pour PowerDNS Recursor',
                    'help' => 'en cas de connexion directe',
                ],
                'https' => [
                    'description' => 'Activer HTTPs',
                    'help' => 'Utiliser HTTPs au lieu de HTTP pour l\'application PowerDNS Recursor app en cas de connexion directe',
                ],
                'port' => [
                    'description' => 'Port de connexion TCP',
                    'help' => 'Port TCP pour l\'application PowerDNS Recursor app en cas de connexion directe',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Key to hold cache of autonomous systems descriptions',
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'Les groupes et les utilisateurs doivent être sous ce DN. Example: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Verifier les certificats',
            'help' => 'Valider la conformité des certificats. Cette option doit être desactivée pour permettre les certificats auto-signés.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'Filtre de groupes AD',
            'help' => 'Filtre Active Directory pour la sélection des groupes',
        ],
        'auth_ad_groups' => [
            'description' => 'Table d\'accès des groupes',
            'help' => 'Correspondance entre les groupes et les droits associés',
        ],
        'auth_ad_user_filter' => [
            'description' => 'Filtres des utilisateurs AD',
            'help' => 'Filtre Active Directory pour la sélection des utilisateurs',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Champ contenant le nom d\'utilisateur',
                'help' => 'Définition de la propriété à utiliser comme `nom d\'utilisateur`',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'DN de LDAP "bind" (remplace le nom d\'utilisateur "bind")',
            'help' => 'DN complet de l\'utilisateur "bind"',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Mot de passe de LDAP "bind"',
            'help' => 'Mot de passe de l\'utilisteur "bind"',
        ],
        'auth_ldap_binduser' => [
            'description' => 'Utilisateur LDAP "bind"',
            'help' => 'Utilisé pour questionner l\'AD quand aucun autre utilisateur n\'est dans le contexte (alerts, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => 'DN de AD "bind" (remplace le nom d\'utilisateur "bind")',
            'help' => 'DN complet de l\'utilisateur "bind"',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Mot de passe de AD "bind"',
            'help' => 'Mot de passe de l\'utilisteur "bind"',
        ],
        'auth_ad_binduser' => [
            'description' => 'Utilisateur AD "bind"',
            'help' => 'Utilisé pour questionner l\'AD quand aucun autre utilisateur n\'est dans le contexte (alerts, API, etc)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'Expiration du cache LDAP',
            'help' => 'Durée du cache LDAP conservant les résultats des requêtes. Meilleure réactivité mais risque de données imprécises/en retard',
        ],
        'auth_ldap_debug' => [
            'description' => 'Affichage Debug',
            'help' => 'Risque d\'affichage d\'informations sensibles. Ne pas laisser actif.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Champ contenant l\'adresse email',
        ],
        'auth_ldap_group' => [
            'description' => 'Access group DN',
            'help' => 'Distinguished name d\'un groupe donnant les droits standards. Example: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'Group base DN',
            'help' => 'Distinguished name de base pour la recherche des groupes. Example: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Champ d\'appartenance aux groupes',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Trouver les membres du groupe via',
            'options' => [
                'username' => 'Nom d\'utilisateur',
                'fulldn' => 'DN complet (en utilisant préfixe et suffixe)',
                'puredn' => 'Recherche DN (via le uid)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Filtrage par Groupes',
            'help' => 'Définition des groupes et de leur niveau d\'accès',
        ],
        'auth_ldap_port' => [
            'description' => 'Port LDAP',
            'help' => 'Port de connexion au serveur LDAP. Pour LDAP, 389 par défaut, et pour  LDAPs, 636 par défaut.',
        ],
        'auth_ldap_prefix' => [
            'description' => 'Préfixe de "username"',
            'help' => 'Utilisé pour transformer un nom d\'utilisateur en Distinguished Name',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP Server(s)',
            'help' => 'Définition des serveur(s), séparés par des espaces. Préfixer avec ldaps:// pour activer SSL',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Utiliser STARTTLS',
            'help' => 'Utiliser STARTTLS pour sécuriser la connexion.  Alternative à LDAPS.',
            'options' => [
                'disabled' => 'Désactivé',
                'optional' => 'Optionnel',
                'required' => 'Obligatoire',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'Suffixe de "username"',
            'help' => 'Utilisé pour transformer un nom d\'utilisateur en Distinguished Name',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Délai d\'attente LDAP',
            'help' => 'Si un (ou plusieurs) serveur(s) sont inactifs, un grand délai d\'attente causera des ralentissements. Un délai trop court peut poser souci sur certaines transactions',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Unique ID',
            'help' => 'Champ LDAP pour identifier un utilisateur, doit être une valeur numérique',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Utiliser le DN d\'utilisateur complet',
            'help' => "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)",
        ],
        'auth_ldap_version' => [
            'description' => 'Version LDAP',
            'help' => 'Version LDAP à utiliser pour parler au serveur, en général v3.',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Source d\'Autentification/Autorisation (Attention!)',
            'help' => "Attention, vous pourriez perdre accès à l\'application. Cette valeur peut être restaurée vers MYSQL en modifiant config.php avec la ligne suivante:  \$config['auth_mechanism'] = 'mysql';",
            'options' => [
                'mysql' => 'MySQL (default)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'Authentification HTTP',
                'ad-authorization' => 'Authentification AD externe',
                'ldap-authorization' => 'Authentification LDAP externe',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Durée de "Se souvenir de moi"',
            'help' => 'Durée de conservation de l\'utilisateur quand la case "Se souvenir de moi" est cochée',
        ],
        'authlog_purge' => [
            'description' => 'Journaux de connexions plus anciens que (jours)',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'device_perf_purge' => [
            'description' => 'Stats de performances plus anciennes que (jours)',
            'help' => 'Statistiques de performances des équipements. Le nettoyage effectué par daily.sh',
        ],
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'ARP Table',
            ],
            'applications' => [
                'description' => 'Applications',
            ],
            'bgp-peers' => [
                'description' => 'BGP Peers',
            ],
            'cisco-cbqos' => [
                'description' => 'Cisco CBQOS',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'cisco-sla' => [
                'description' => 'Cisco SLA',
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
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'fdb-table' => [
                'description' => 'FDB Table',
            ],
            'hr-device' => [
                'description' => 'HR Device',
            ],
            'ipv4-addresses' => [
                'description' => 'IPv4 Addresses',
            ],
            'ipv6-addresses' => [
                'description' => 'IPv6 Addresses',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'libvirt-vminfo' => [
                'description' => 'Libvirt VMInfo',
            ],
            'loadbalancers' => [
                'description' => 'Loadbalancers',
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
                'description' => 'Ports',
            ],
            'ports-stack' => [
                'description' => 'Ports Stack',
            ],
            'processors' => [
                'description' => 'Processors',
            ],

            'route' => [
                'description' => 'Route',
            ],

            'sensors' => [
                'description' => 'Sensors',
            ],

            'services' => [
                'description' => 'Services',
            ],
            'storage' => [
                'description' => 'Storage',
            ],

            'stp' => [
                'description' => 'STP',
            ],
            'toner' => [
                'description' => 'Toner',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'vlans' => [
                'description' => 'VLans',
            ],
            'vmware-vminfo' => [
                'description' => 'VMWare VMInfo',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => 'Wireless',
            ],
        ],
        'distributed_poller' => [
            'description' => 'Activation des sondeurs distribués (`Distributed Pollers`, nécessite des configurations additionnelles)',
            'help' => 'Cf documentation : https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'distributed_poller_group' => [
            'description' => 'Groupe de sondeurs par défaut',
            'help' => 'Le groupe que tous les sondeurs doivent surveiller si aucun n\'est configuré',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Hôte Memcached',
            'help' => 'Nom ou adresse IP de la machine fournissant le service Memcached.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Port Memcached',
            'help' => 'Port d\'accès au service Memcached. Défaut: 11211',
        ],
        'email_auto_tls' => [
            'description' => 'Activer / Désactiver Auto TLS',
            'options' => [
                'true' => 'Oui',
                'false' => 'Non',
            ],
        ],
        'email_backend' => [
            'description' => 'Comment envoyer les emails',
            'help' => 'Le service email peut être fourni par  mail, sendmail ou SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'Expéditeur (email)',
            'help' => 'Adresse email pour l\'envoi (from)',
        ],
        'email_html' => [
            'description' => 'Utiliser les emails HTML',
            'help' => 'Envoyer les emails au format HTML',
        ],
        'email_sendmail_path' => [
            'description' => 'Chemin vers l\'exécutable `sendmail`',
        ],
        'email_smtp_auth' => [
            'description' => 'Activer/Désactiver l\'authentification SMTP',
        ],
        'email_smtp_host' => [
            'description' => 'Relai SMTP pour l\'envoi des emails',
        ],
        'email_smtp_password' => [
            'description' => 'Mot de passe SMTP',
        ],
        'email_smtp_port' => [
            'description' => 'Port SMTP',
        ],
        'email_smtp_secure' => [
            'description' => 'Activer/Désactiver le chiffrement (utilise tls ou ssl)',
            'options' => [
                '' => 'Disabled',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'Réglage du délai d\'attente SMTP',
        ],
        'email_smtp_username' => [
            'description' => 'Nom d\'utilisateur SMTP',
        ],
        'email_user' => [
            'description' => 'Expéditeur (nom)',
            'help' => 'Nom utilisé dans le champ "from" de l\'adresse',
        ],
        'eventlog_purge' => [
            'description' => 'Journaux d\'évenements plus anciens que (jours)',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Remplace la Favicon par défaut.',
        ],
        'fping' => [
            'description' => 'Chemin vers `fping`',
        ],
        'fping6' => [
            'description' => 'Chemin vers `fping6`',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'Nombre de paquets fping',
                'help' => 'Nombre de ping à envoyer à un hôte pour valider son état ICMP',
            ],
            'interval' => [
                'description' => 'Intervalle fping',
                'help' => 'Intervalle entre chaque ping (millisecondes)',
            ],
            'timeout' => [
                'description' => 'Délai d\'attente fping',
                'help' => 'Délai d\'attente avant de passer au test suivant (millisecondes)',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Clef Geocoding API',
                'help' => 'Geocoding API Key (Nécessaire)',
            ],
            'engine' => [
                'description' => 'Fond de carte',
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
                'description' => 'URI Base',
                'help' => 'Remplace l\'URI de base pour la configuration Graylog.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Filtrage par niveau pour la page d\'accueil',
                    'help' => 'Règle le niveau maximum de criticité pour les journaux affichés sur la page d\'accueil.',
                ],
                'rowCount' => [
                    'description' => 'Nombre de lignes affichées sur la page d\'accueil',
                    'help' => 'Règle le nombre de lignes de journaux affichées sur la page d\'accueil',
                ],
            ],
            'password' => [
                'description' => 'Mot de passe',
                'help' => 'pour accéder à la Graylog API.',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'En absence de ce paramètre, le port par défaut utilisé est le 80 si http, 443 si https.',
            ],
            'server' => [
                'description' => 'Serveur',
                'help' => 'IP ou nom du serveur Graylog API',
            ],
            'timezone' => [
                'description' => 'Fuseau horaire pour l\'affichage',
                'help' => 'Graylog conserve les heures au fuseau GMT. Ce paramètre doit être un fuseau PHP valide.',
            ],
            'username' => [
                'description' => 'Nom d\'utilisateur',
                'help' => 'Utilisateur pour accéder le serveur Graylog API.',
            ],
            'version' => [
                'description' => 'Version',
                'help' => 'This is used to automatically create the base_uri for the Graylog API. If you have modified the API uri from the default, set this to other and specify your base_uri.',
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(s) Proxy',
            'help' => 'Si les variables d\'environnement http_proxy ou https_proxy ne sont pas disponibles.',
        ],
        'ipmitool' => [
            'description' => 'Chemin vers `ipmtool`',
        ],
        'login_message' => [
            'description' => 'Logon Message',
            'help' => 'Displayed on the login page',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => 'Activer la recherche des préfixes OUI (prefixes d\'adresses mac par fournisseur)',
                'help' => 'Les données sont mises à jour via daily.sh',
            ],
        ],
        'mono_font' => [
            'description' => 'Monospaced Font',
        ],
        'mtr' => [
            'description' => 'Chemin vers `mtr`',
        ],
        'mydomain' => [
            'description' => 'Domaine principal',
            'help' => 'Ce domaine est utilisé pour la découverte automatique de réseau et d\'autres processus. LibreNMS essaie de le concatener aux noms incomplets.',
        ],
        'nfdump' => [
            'description' => 'Chemin vers `nfdump`',
        ],
        'nfsen_enable' => [
            'description' => 'Activer NfSen',
            'help' => 'Activer l\'intégration avec NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'Chemin vers les RRDs NfSen',
            'help' => 'Cette valeur indique l\'emplacement des fichiers RRDs pour NfSen.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Choisir la structure de répertoires NfSen',
            'help' => 'Ceci doit correspondre au schéma de répertoires configuré dans NfSen. Valeur par défaut : 1.',
        ],
        'nfsen_last_max' => [
            'description' => 'Dernier Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Nombre max de valeurs topN pour les stats',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => 'Défaut Top N',
        ],
        'nfsen_stat_default' => [
            'description' => 'Défaut Stat',
        ],
        'nfsen_order_default' => [
            'description' => 'Ordre par Défaut',
        ],
        'nfsen_last_default' => [
            'description' => 'Défaut Last',
        ],
        'nfsen_lasts' => [
            'description' => 'Défaut Last Options',
        ],
        'nfsen_split_char' => [
            'description' => 'Séparateur',
            'help' => 'This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'Suffixe de nom de fichier',
            'help' => 'This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed.',
        ],
        'nmap' => [
            'description' => 'Chemin vers `nmap`',
        ],
        'own_hostname' => [
            'description' => 'Nom d\'hôte LibreNMS',
            'help' => 'Doit être configuré avec le nom d\'hôte/IP du serveur LibreNMS.',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Configurer le groupe par défaut',
            ],
            'enabled' => [
                'description' => 'Activer le support Oxidized',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Activer l\'accès aux versions de configurations',
                    'help' => 'Active la conservation des versiones de configurations dans Oxidized (nécessite git)',
                ],
            ],
            'group_support' => [
                'description' => 'Envoyer les groupes à Oxidized',
            ],
            'reload_nodes' => [
                'description' => 'Recharger Oxidized à chaque ajout d\'équipement',
            ],
            'url' => [
                'description' => 'URL vers l\'API Oxidized',
                'help' => 'URL de Oxidized API (Par exemple: http://127.0.0.1:8888)',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Activer la recherche dans PeeringDB',
                'help' => 'Les données sont mises à jour via daily.sh',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => 'Activer les permissions par groupes d\'équipements dynamiques',
                ],
            ],
        ],
        'ping' => [
            'description' => 'Chemin vers `ping`',
        ],
        'ports_fdb_purge' => [
            'description' => 'Table port FDB, entrées plus anciennes que',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'ports_purge' => [
            'description' => 'Interfaces, entrées plus anciennes que (jours)',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'public_status' => [
            'description' => 'Afficher le status publiquement',
            'help' => 'Afficher le status de certains équipements sans authentification',
        ],
        'routes_max_number' => [
            'description' => 'Nombre max de routes pour la Découverte',
            'help' => 'Aucune route ne sera découverte si la taille de la table de routage est supérieure à ce nombre',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Changer la valeur `heartbeat` rrd (défaut 600)',
            ],
            'step' => [
                'description' => 'Changer la valeur `step` rrd (défaut 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'Emplacement des fichiers RRD',
            'help' => 'Emplacement des fichiers rrd.  Défaut: fichiers rrd dans le répertoire de LibreNMS. La modification de cette valeur ne déplace pas les fichiers RRD.',
        ],
        'rrd_purge' => [
            'description' => 'RRD, fichiers non modifiés depuis plus de (jours)',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'Format des RRDs',
            'help' => 'Il est necessaire d\'effacer les fichiers RRDs existants en cas de changement de cette valeur. Peut avoir un impact sur les perfomances (taille des RRAs, ...).',
        ],
        'rrdcached' => [
            'description' => 'Activer rrdcached (socket)',
            'help' => 'Activer rrdcached en configurant l\'emplacement du rrdcached socket. Socket UNIX ou réseau (unix:/run/rrdcached.sock ou localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Chemin vers `rrdtool`',
        ],
        'rrdtool_tune' => [
            'description' => 'Configurer tous les RRDs d\'interfaces pour utiliser la valuer `max`',
            'help' => 'Auto tune maximum value for rrd port files',
        ],
        'sfdp' => [
            'description' => 'Chemin vers `sfdp`',
        ],
        'site_style' => [
            'description' => 'Changer la feuille de style',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transport (priorité)',
                'help' => 'Activer et ordonner les transports à utiliser.',
            ],
            'version' => [
                'description' => 'Version (priorité)',
                'help' => 'Ordonner les versions SNMP à utiliser.',
            ],
            'community' => [
                'description' => 'Communautés (priorité)',
                'help' => 'Entrer les communautés SNMP v1 et v2c dans l\'ordre où elles doivent être essayées.',
            ],
            'max_repeaters' => [
                'description' => 'Max Repeaters',
                'help' => 'Configurer les `repeaters` pour les requètes SNMP `bulk`.',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Changer le port tcp/udp pour les connexions SNMP',
            ],
            'v3' => [
                'description' => 'SNMP v3 Authentication (priorité)',
                'help' => 'Configurer l\'authentification SNMP v3 et ordonner par préférences',
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
            'description' => 'Chemin vers `snmpbulkwalk`',
        ],
        'snmpget' => [
            'description' => 'Chemin vers `snmpget`',
        ],
        'snmpgetnext' => [
            'description' => 'Chemin vers `snmpgetnext`',
        ],
        'snmptranslate' => [
            'description' => 'Chemin vers `snmptranslate`',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'Journaliser les traps SNMP',
                'help' => 'En plus de toute action déjà effectuée',
            ],
            'eventlog_detailed' => [
                'description' => 'Journalisation détaillée',
                'help' => 'Ajouter tous les OIDs recus dans le trap',
            ],
        ],
        'snmpwalk' => [
            'description' => 'Chemin vers `snmpwalk`',
        ],
        'syslog_filter' => [
            'description' => 'Filtrer les messages syslog contenant',
        ],
        'syslog_purge' => [
            'description' => 'Entrées syslogs plus anciennes que (jours)',
            'help' => 'Nettoyage effectué par daily.sh',
        ],
        'title_image' => [
            'description' => 'Image d\'accueil',
            'help' => 'Remplace le logo LibreNMS sur la page d\'accueil/authentification',
        ],
        'traceroute' => [
            'description' => 'Chemin vers `traceroute`',
        ],
        'traceroute6' => [
            'description' => 'Chemin vers `traceroute6`',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Délai d\'attente de connexion Unix-agent',
            ],
            'port' => [
                'description' => 'Port par défaut unix-agent',
                'help' => 'Port par défaut unix-agent (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Délai d\'attente read Unix-agent',
            ],
        ],
        'update' => [
            'description' => 'Activer les updates dans ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Choisir le canal des mises à jour',
            'options' => [
                'master' => 'master',
                'release' => 'release',
            ],
        ],
        'uptime_warning' => [
            'description' => 'Warning si l\'uptime est inférieur à (seconds)',
            'help' => 'Afficher un Warning sur l\'équipement si l\'uptime est inférieur à la durée indiquée. Default 24h',
        ],
        'virsh' => [
            'description' => 'Chemin vers `virsh`',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Largeur',
                'help' => 'Input desired tile width in pixels for box size in full view',
            ],
            'availability_map_compact' => [
                'description' => 'Vue compacte',
                'help' => 'Availability map view with small indicators',
            ],
            'availability_map_sort_status' => [
                'description' => 'Trier par statut',
                'help' => 'Sort devices and services by status',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Activer le fitre par groupes d\'équipements',
                'help' => 'Enable usage of device groups filter',
            ],
            'default_dashboard_id' => [
                'description' => 'Tableau de bord par défaut',
                'help' => 'Global default dashboard_id for all users who do not have their own default set',
            ],
            'dynamic_graphs' => [
                'description' => 'Activer les graphes dynamiques',
                'help' => 'Graphes dynamiques, permettant de zoomer/faire défiler les graphes.',
            ],
            'global_search_result_limit' => [
                'description' => 'Nombre maximum de résultats',
                'help' => 'Nombre maximum de résultats pour la recherche globale',
            ],
            'graph_stacked' => [
                'description' => 'Afficher les graphes empilés',
                'help' => 'Afficher les graphes empilés au lieu de inversés (upload/download, etc etc)',
            ],
            'graph_type' => [
                'description' => 'Configurer le type',
                'help' => 'Configurer le type de graphes par défaut',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Configurer la hauteur minimale',
                'help' => 'Hauteur minimale des graphes (défaut: 300)',
            ],
        ],
        'whois' => [
            'description' => 'Chemin vers `whois`',
        ],
    ],
    'twofactor' => [
        'description' => 'Activer l\'Autentification à deux facteurs',
        'help' => 'Active le mécanisme interne à deux facteurs. Chaque compte utilisateur doit ensuite être configuré.',
    ],
    'units' => [
        'days' => 'jours',
        'ms' => 'ms',
        'seconds' => 'secondes',
    ],
    'validate' => [
        'boolean' => ':value n\'est pas un booléen valide',
        'color' => ':value n\'est pas un code couleur hexadécimal valide',
        'email' => ':value n\'est pas une adresse email valide',
        'integer' => ':value n\'est pas un entier',
        'password' => 'Le mot de passe est incorrect',
        'select' => ':value n\'est pas une valeur autorisée',
        'text' => ':value n\'est pas autorisé',
        'array' => 'Format invalide',
    ],
];
