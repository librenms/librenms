<?php

return [
    'title' => 'Settings',
    'readonly' => 'Set in config.php, remove from config.php to enable.',
    'groups' => [
        'alerting' => 'Alerting',
        'api' => 'API',
        'auth' => 'Authentication',
        'authorization' => 'Authorization',
        'external' => 'External',
        'global' => 'Global',
        'os' => 'OS',
        'discovery' => 'Discovery',
        'graphing' => 'Graphing',
        'poller' => 'Poller',
        'system' => 'System',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => 'General Alert Settings',
            'email' => 'Email Options',
            'rules' => 'Alert Rule Default Settings',
        ],
        'api' => [
            'cors' => 'CORS',
        ],
        'auth' => [
            'general' => 'General Authentication Settings',
            'ad' => 'Active Directory Settings',
            'ldap' => 'LDAP Settings',
        ],
        'authorization' => [
            'device-group' => 'Device Group Settings',
        ],
        'discovery' => [
            'general' => 'General Discovery Settings',
            'route' => 'Routes Discovery Module',
            'discovery_modules' => 'Discovery Modules',
            'storage' => 'Storage Module',
            'networks' => 'Networks',
        ],
        'external' => [
            'binaries' => 'Binary Locations',
            'location' => 'Location Settings',
            'graylog' => 'Graylog Integration',
            'oxidized' => 'Oxidized Integration',
            'peeringdb' => 'PeeringDB Integration',
            'nfsen' => 'NfSen Integration',
            'unix-agent' => 'Unix-Agent Integration',
            'smokeping' => 'Smokeping Integration',
            'snmptrapd' => 'SNMP Traps Integration',
        ],
        'poller' => [
            'availability' => 'Device Availability',
            'distributed' => 'Distributed Poller',
            'graphite' => 'Datastore: Graphite',
            'influxdb' => 'Datastore: InfluxDB',
            'opentsdb' => 'Datastore: OpenTSDB',
            'ping' => 'Ping',
            'prometheus' => 'Datastore: Prometheus',
            'rrdtool' => 'Datastore: RRDTool',
            'snmp' => 'SNMP',
            'poller_modules' => 'Poller Modules',
            'interface_types' => 'Interface Type by RFC 7224',
        ],
        'system' => [
            'cleanup' => 'Cleanup',
            'proxy' => 'Proxy',
            'updates' => 'Updates',
            'server' => 'Server',
        ],
        'webui' => [
            'availability-map' => 'Availability Map Settings',
            'graph' => 'Graph Settings',
            'dashboard' => 'Dashboard Settings',
            'port-descr' => 'Interface Description Parsing',
            'search' => 'Search Settings',
            'style' => 'Style',
            'device' => 'Device Settings',
            'worldmap' => 'World Map Settings',
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Keep inactive users for',
                'help' => 'Users will be deleted from LibreNMS after this may days of not logging in. 0 means never and users will be recreated if the user logs back in.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Check for duplicate IP when adding devices',
            'help' => 'If a host is added as an ip address it is checked to ensure the ip is not already present. If the ip is present the host is not added. If host is added by hostname this check is not performed. If the setting is true hostnames are resolved and the check is also performed. This helps prevents accidental duplicate hosts.',
        ],
        'alert_rule' => [
            'severity' => [
                'description' => 'Severity',
                'help' => 'Severity for an Alert',
            ],
            'max_alerts' => [
                'description' => 'Max Alerts',
                'help' => 'Count of Alerts to be sent',
            ],
            'delay' => [
                'description' => 'Delay',
                'help' => 'Delay before an Alert will be sent',
            ],
            'interval' => [
                'description' => 'Interval',
                'help' => 'Interval to be checked for this Alert',
            ],
            'mute_alerts' => [
                'description' => 'Mute Alerts',
                'help' => 'Should Alert only be seen in WebUI',
            ],
            'invert_rule_match' => [
                'description' => 'Invert Rule Match',
                'help' => 'Alert only if rule doesn\'t match',
            ],
            'recovery_alerts' => [
                'description' => 'Recovery Alerts',
                'help' => 'Notify if Alert recovers',
            ],
            'invert_map' => [
                'description' => 'All devices except in list',
                'help' => 'Alert only for Devices which are not listed',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Default acknowledge until alert clears option',
                'help' => 'Default acknowledge until alert clears',
            ],
            'admins' => [
                'description' => 'Issue alerts to admins',
                'help' => 'Alert administrators',
            ],
            'default_copy' => [
                'description' => 'Copy all email alerts to default contact',
                'help' => 'Copy all email alerts to default contact',
            ],
            'default_if_none' => [
                'description' => 'cannot set in webui?',
                'help' => 'Send mail to default contact if no other contacts are found',
            ],
            'default_mail' => [
                'description' => 'Default contact',
                'help' => 'The default mail contact',
            ],
            'default_only' => [
                'description' => 'Send alerts to default contact only',
                'help' => 'Only alert default mail contact',
            ],
            'disable' => [
                'description' => 'Disable alerting',
                'help' => 'Stop alerts being generated',
            ],
            'fixed-contacts' => [
                'description' => 'Updates to contact email addresses not honored',
                'help' => 'If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active',
            ],
            'globals' => [
                'description' => 'Issue alerts to read only users',
                'help' => 'Alert read only administrators',
            ],
            'syscontact' => [
                'description' => 'Issue alerts to sysContact',
                'help' => 'Send alert to email in SNMP sysContact',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Enable email alerting',
                    'help' => 'Mail alerting transport',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Tolerance window for cron',
                'help' => 'Tolerance window in seconds',
            ],
            'users' => [
                'description' => 'Issue alerts to normal users',
                'help' => 'Alert normal users',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Alert log entries older than',
            'help' => 'Cleanup done by daily.sh',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Allow Duplicate sysName',
            'help' => 'By default duplicate sysNames are disabled from being added to prevent a device with multiple interfaces from being added multiple times',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Allow unauthenticated graph access',
            'help' => 'Allows any one to access graphs without login',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Allow the given networks graph access',
            'help' => 'Allow the given networks unauthenticated graph access (does not apply when unauthenticated graphs is enabled)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => 'Allow Headers',
                    'help' => 'Sets the Access-Control-Allow-Headers response header',
                ],
                'allowcredentials' => [
                    'description' => 'Allow Credentials',
                    'help' => 'Sets the Access-Control-Allow-Credentials header',
                ],
                'allowmethods' => [
                    'description' => 'Allowed Methods',
                    'help' => 'Matches the request method.',
                ],
                'enabled' => [
                    'description' => 'Enable CORS support for the API',
                    'help' => 'Allows you to load api resources from a web client',
                ],
                'exposeheaders' => [
                    'description' => 'Expose Headers',
                    'help' => 'Sets the Access-Control-Expose-Headers response header',
                ],
                'maxage' => [
                    'description' => 'Max Age',
                    'help' => 'Sets the Access-Control-Max-Age response header',
                ],
                'origin' => [
                    'description' => 'Allow Request Origins',
                    'help' => 'Matches the request origin. Wildcards can be used, eg. *.mydomain.com',
                ],
            ],
        ],
        'api_demo' => [
            'description' => 'This is the demo',
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'API key for PowerDNS Recursor',
                    'help' => 'API key for the PowerDNS Recursor app when connecting directly',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor use HTTPS?',
                    'help' => 'Use HTTPS instead of HTTP for the PowerDNS Recursor app when connecting directly',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor port',
                    'help' => 'TCP port to use for the PowerDNS Recursor app when connecting directly',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Key to hold cache of autonomous systems descriptions',
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'groups and users must be under this dn. Example: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Check certificate',
            'help' => 'Check certificates for validity. Some servers use self signed certificates, disabling this allows those.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'Group LDAP filter',
            'help' => 'Active Directory LDAP filter for selecting groups',
        ],
        'auth_ad_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level',
        ],
        'auth_ad_user_filter' => [
            'description' => 'User LDAP filter',
            'help' => 'Active Directory LDAP filter for selecting users',
        ],
        'auth_ad_url' => [
            'description' => 'Active Directory Server(s)',
            'help' => 'Set server(s), space separated. Prefix with ldaps:// for ssl. Example: ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ad_domain' => [
            'description' => 'Active Directory Domain',
            'help' => 'Active Directory Domain Example: example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribute to check username against',
                'help' => 'Attribute used to identify users by username',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (overrides bind username)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Bind password',
            'help' => 'Password for bind user',
        ],
        'auth_ldap_binduser' => [
            'description' => 'Bind username',
            'help' => 'Used to query the LDAP server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (overrides bind username)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Bind password',
            'help' => 'Password for bind user',
        ],
        'auth_ad_binduser' => [
            'description' => 'Bind username',
            'help' => 'Used to query the AD server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP cache expiration',
            'help' => 'Temporarily stores LDAP query results.  Improves speeds, but the data may be stale.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Show debug',
            'help' => 'Shows debug information.  May expose private information, do not leave enabled.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Mail attribute',
        ],
        'auth_ldap_group' => [
            'description' => 'Access group DN',
            'help' => 'Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'Group base DN',
            'help' => 'Distinguished name to search for groups Example: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Group member attribute',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Find group members by',
            'options' => [
                'username' => 'Username',
                'fulldn' => 'Full DN (using prefix and suffix)',
                'puredn' => 'DN Search (search using uid attribute)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP port',
            'help' => 'Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636',
        ],
        'auth_ldap_prefix' => [
            'description' => 'User prefix',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP Server(s)',
            'help' => 'Set server(s), space separated. Prefix with ldaps:// for ssl',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Use STARTTLS',
            'help' => 'Use STARTTLS to secure the connection.  Alternative to LDAPS.',
            'options' => [
                'disabled' => 'Disabled',
                'optional' => 'Optional',
                'required' => 'Required',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'User suffix',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Connection timeout',
            'help' => 'If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Unique ID attribute',
            'help' => 'LDAP attribute to use to identify users, must be numeric',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Use full user DN',
            'help' => "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)",
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => 'Wildcard user OU',
            'help' => 'Search for user matching user name independently of OU set in user suffix. Useful if your users are in different OU. Bind username, if set, still user suffix',
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP version',
            'help' => 'LDAP version to use to talk to the server.  Usually this should be v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Authorization Method (Caution!)',
            'help' => "Authorization method.  Caution, you may lose the ability to log in. You can override this back to mysql by setting \$config['auth_mechanism'] = 'mysql'; in your config.php",
            'options' => [
                'mysql' => 'MySQL (default)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP Authentication',
                'ad-authorization' => 'Externally authenticated AD',
                'ldap-authorization' => 'Externally authenticated LDAP',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Remember me duration',
            'help' => 'Number of days to keep a user logged in when checking the remember me checkbox at log in.',
        ],
        'authlog_purge' => [
            'description' => 'Auth log entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'peering_descr' => [
            'description' => 'Peering Port Types',
            'help' => 'Ports of the listed description type(s) will be shown under the peering ports menu entry.  See Interface Description Parsing docs for more info.',
        ],
        'transit_descr' => [
            'description' => 'Transit Port Types',
            'help' => 'Ports of the listed description type(s) will be shown under the transit ports menu entry.  See Interface Description Parsing docs for more info.',
        ],
        'core_descr' => [
            'description' => 'Core Port Types',
            'help' => 'Ports of the listed description type(s) will be shown under the core ports menu entry.  See Interface Description Parsing docs for more info.',
        ],
        'customers_descr' => [
            'description' => 'Customer Port Types',
            'help' => 'Ports of the listed description type(s) will be shown under the customers ports menu entry.  See Interface Description Parsing docs for more info.',
        ],
        'base_url' => [
            'description' => 'Specific URL',
            'help' => 'This should *only* be set if you want to *force* a particular hostname/port. It will prevent the web interface being usable form any other hostname',
        ],
        'device_perf_purge' => [
            'description' => 'Device performance entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
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
            'description' => 'Enable Distributed Polling (requires additional setup)',
            'help' => 'Enable distributed polling system wide. This is intended for load sharing, not remote polling. You must read the documentation for steps to enable: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => 'Default Poller Group',
            'help' => 'The default poller group all pollers should poll if none is set in config.php',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached host',
            'help' => 'The hostname or ip for the memcached server. This is required for poller_wrapper.py and daily.sh locking.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached port',
            'help' => 'The port for the memcached server. Default is 11211',
        ],
        'email_auto_tls' => [
            'description' => 'Auto TLS support',
            'help' => 'Tries to use TLS before falling back to un-encrypted',
        ],
        'email_backend' => [
            'description' => 'How to deliver mail',
            'help' => 'The backend to use for sending email, can be mail, sendmail or SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'From email address',
            'help' => 'Email address used for sending emails (from)',
        ],
        'email_html' => [
            'description' => 'Use HTML emails',
            'help' => 'Send HTML emails',
        ],
        'email_sendmail_path' => [
            'description' => 'Path to sendmail binary',
        ],
        'email_smtp_auth' => [
            'description' => 'SMTP authentication',
            'help' => 'Enable this if your SMTP server requires authentication',
        ],
        'email_smtp_host' => [
            'description' => 'SMTP Server',
            'help' => 'IP or dns name for the SMTP server to deliver mail to',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP Auth password',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP port setting',
        ],
        'email_smtp_secure' => [
            'description' => 'Encryption',
            'options' => [
                '' => 'Disabled',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP timeout setting',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP Auth username',
        ],
        'email_user' => [
            'description' => 'From name',
            'help' => 'Name used as part of the from address',
        ],
        'eventlog_purge' => [
            'description' => 'Event log entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Overrides the default favicon.',
        ],
        'fping' => [
            'description' => 'Path to fping',
        ],
        'fping6' => [
            'description' => 'Path to fping6',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping count',
                'help' => 'The number of pings to send when checking if a host is up or down via icmp',
            ],
            'interval' => [
                'description' => 'fping interval',
                'help' => 'The amount of milliseconds to wait between pings',
            ],
            'timeout' => [
                'description' => 'fping timeout',
                'help' => 'The amount of milliseconds to wait for an echo response before giving up',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Mapping Engine API Key',
                'help' => 'Geocoding API Key (Required to function)',
            ],
            'dns' => [
                'description' => 'Use DNS Location Record',
                'help' => 'Use LOC Record from DNS Server to get geographic coordinates for Hostname',
            ],
            'engine' => [
                'description' => 'Mapping Engine',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                ],
            ],
            'latlng' => [
                'description' => 'Attempt to Geocode Locations',
                'help' => 'Try to lookup latitude and longitude via geocoding API during polling',
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => 'Enable',
                'help' => 'Exports metrics to Graphite',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'The IP or hostname of the Graphite server to send data to',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'The port to use to connect to the Graphite server',
            ],
            'prefix' => [
                'description' => 'Prefix (Optional)',
                'help' => 'Will add the prefix to the start of all metrics.  Must be alphanumeric separated by dots',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => 'Duration',
                'help' => 'Calculate Device Availability for listed durations. (Durations are defined in seconds)',
            ],
            'availability_consider_maintenance' => [
                'description' => 'Scheduled maintenance does not affect availability',
                'help' => 'Disables the creation of outages and decreasing of availability for devices which are in maintenance mode.',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'Base URI',
                'help' => 'Override the base uri in the case you have modified the Graylog default.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Device Overview Log Level',
                    'help' => 'Sets the maximum log level shown on the device overview page.',
                ],
                'rowCount' => [
                    'description' => 'Device Overview Row Count',
                    'help' => 'Sets the number of rows show on the device overview page.',
                ],
            ],
            'password' => [
                'description' => 'Password',
                'help' => 'Password for accessing Graylog API.',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'The port used to access the Graylog API. If none give, it will be 80 for http and 443 for https.',
            ],
            'server' => [
                'description' => 'Server',
                'help' => 'The ip or hostname of the Graylog server API endpoint.',
            ],
            'timezone' => [
                'description' => 'Display Timezone',
                'help' => 'Graylog times are stored in GMT, this setting will change the displayed timezone. The value must be a valid PHP timezone.',
            ],
            'username' => [
                'description' => 'Username',
                'help' => 'Username for accessing the Graylog API.',
            ],
            'version' => [
                'description' => 'Version',
                'help' => 'This is used to automatically create the base_uri for the Graylog API. If you have modified the API uri from the default, set this to other and specify your base_uri.',
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => 'Primary Dropdown Link',
                    'help' => 'Sets the primary link in the device dropdown menu',
                ],
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) Proxy',
            'help' => 'Set this as a fallback if http_proxy or https_proxy environment variable is not available.',
        ],
        'ignore_mount' => [
            'description' => 'Mountpoints to be ignored',
            'help' => 'Don\'t monitor Disc Usage of this Mountpoints',
        ],
        'ignore_mount_network' => [
            'description' => 'Ignore Network Mountpoints',
            'help' => 'Don\'t monitor Disc Usage of Network Mountpoints',
        ],
        'ignore_mount_optical' => [
            'description' => 'Ignore Optical Drives',
            'help' => 'Don\'t monitor Disc Usage of optical Drives',
        ],
        'ignore_mount_removable' => [
            'description' => 'Ignore Removable Drives',
            'help' => 'Don\'t monitor Disc Usage of removable Devices',
        ],
        'ignore_mount_regexp' => [
            'description' => 'Mountpoints matching Regex to be ignored',
            'help' => 'Don\'t monitor Disc Usage of Mountpoints which are matching at least one of this Regular Expressions',
        ],
        'ignore_mount_string' => [
            'description' => 'Mountpoints containing String to be ignored',
            'help' => 'Don\'t monitor Disc Usage of Mountpoints which contains at least one of this Strings',
        ],
        'influxdb' => [
            'db' => [
                'description' => 'Database',
                'help' => 'Name of the InfluxDB database to store metrics',
            ],
            'enable' => [
                'description' => 'Enable',
                'help' => 'Exports metrics to InfluxDB',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'The IP or hostname of the InfluxDB server to send data to',
            ],
            'password' => [
                'description' => 'Password',
                'help' => 'Password to connect to InfluxDB, if required',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'The port to use to connect to the InfluxDB server',
            ],
            'timeout' => [
                'description' => 'Timeout',
                'help' => 'How long to wait for InfluxDB server, 0 means default timeout',
            ],
            'transport' => [
                'description' => 'Transport',
                'help' => 'The port to use to connect to the InfluxDB server',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDPRRRRRRR',
                ],
            ],
            'username' => [
                'description' => 'Username',
                'help' => 'Username to connect to InfluxDB, if required',
            ],
            'verifySSL' => [
                'description' => 'Verify SSL',
                'help' => 'Verify the SSL certificate is valid and trusted',
            ],
        ],
        'ipmitool' => [
            'description' => 'Path to ipmtool',
        ],
        'login_message' => [
            'description' => 'Logon Message',
            'help' => 'Displayed on the login page',
        ],
        'mono_font' => [
            'description' => 'Monospaced Font',
        ],
        'mtr' => [
            'description' => 'Path to mtr',
        ],
        'mydomain' => [
            'description' => 'Primary Domain',
            'help' => 'This domain is used for network auto-discovery and other processes. LibreNMS will attempt to append it to unqualified hostnames.',
        ],
        'network_map_show_on_worldmap' => [
            'description' => 'Display network links on the map',
            'help' => 'Show the networks links between the different location on the worldmap (weathermap-like)',
        ],
        'nfsen_enable' => [
            'description' => 'Enable NfSen',
            'help' => 'Enable Integration with NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD Directories',
            'help' => 'This value specifies where your NFSen RRD files are located.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Set NfSen subdir layout',
            'help' => 'This must match the subdir layout you have set in NfSen. 1 is the default.',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Max topN value for stats',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => 'Default Top N',
        ],
        'nfsen_stat_default' => [
            'description' => 'Default Stat',
        ],
        'nfsen_order_default' => [
            'description' => 'Default Order',
        ],
        'nfsen_last_default' => [
            'description' => 'Default Last',
        ],
        'nfsen_lasts' => [
            'description' => 'Default Last Options',
        ],
        'nfsen_split_char' => [
            'description' => 'Split Char',
            'help' => 'This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'File name suffix',
            'help' => 'This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed.',
        ],
        'nmap' => [
            'description' => 'Path to nmap',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => 'Enable',
                'help' => 'Exports metrics to OpenTSDB',
            ],
            'host' => [
                'description' => 'Server',
                'help' => 'The IP or hostname of the OpenTSDB server to send data to',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'The port to use to connect to the OpenTSDB server',
            ],
        ],
        'own_hostname' => [
            'description' => 'LibreNMS hostname',
            'help' => 'Should be set to the hostname/ip the librenms server is added as',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Set the default group returned',
            ],
            'enabled' => [
                'description' => 'Enable Oxidized support',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Enable config versioning access',
                    'help' => 'Enable Oxidized config versioning (requires git backend)',
                ],
            ],
            'group_support' => [
                'description' => 'Enable the return of groups to Oxidized',
            ],
            'reload_nodes' => [
                'description' => 'Reload Oxidized nodes list, each time a device is added',
            ],
            'url' => [
                'description' => 'URL to your Oxidized API',
                'help' => 'Oxidized API url (For example: http://127.0.0.1:8888)',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => 'Minimum password length',
                'help' => 'Passwords shorter than the given length will be rejected',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Enable PeeringDB lookup',
                'help' => 'Enable PeeringDB lookup (data is downloaded with daily.sh)',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => 'Enable user access via dynamic Device Groups',
                ],
            ],
        ],
        'ping' => [
            'description' => 'Path to ping',
        ],
        'interface_types' => [
            'a12MppSwitch' => [
                'description' => 'a12MppSwitch',
                'help' => 'Avalon Parallel Processor.',
            ],
            'aal2' => [
                'description' => 'aal2',
                'help' => 'ATM adaptation layer 2.',
            ],
            'aal5' => [
                'description' => 'aal5',
                'help' => 'AAL5 over ATM.',
            ],
            'actelisMetaLOOP' => [
                'description' => 'actelisMetaLOOP',
                'help' => 'Acteleis proprietary MetaLOOP High Speed Link.',
            ],
            'adsl2plus' => [
                'description' => 'adsl2plus',
                'help' => 'Asymmetric Digital Subscriber Loop Version 2 - Version 2 Plus and all variants.',
            ],
            'adsl' => [
                'description' => 'adsl',
                'help' => 'Asymmetric Digital Subscriber Loop.',
            ],
            'aflane8023' => [
                'description' => 'aflane8023',
                'help' => 'ATM Emulated LAN for 802.3.',
            ],
            'aflane8025' => [
                'description' => 'aflane8025',
                'help' => 'ATM Emulated LAN for 802.5.',
            ],
            'aluELP' => [
                'description' => 'aluELP',
                'help' => 'Alcatel-Lucent Ethernet Link Protection.',
            ],
            'aluEpon' => [
                'description' => 'aluEpon',
                'help' => 'Ethernet Passive Optical Networks (E-PON).',
            ],
            'aluEponLogicalLink' => [
                'description' => 'aluEponLogicalLink',
                'help' => 'The emulation of a point-to-point link over the EPON layer.',
            ],
            'aluEponOnu' => [
                'description' => 'aluEponOnu',
                'help' => 'EPON Optical Network Unit.',
            ],
            'aluEponPhysicalUni' => [
                'description' => 'aluEponPhysicalUni',
                'help' => 'EPON physical User to Network interface.',
            ],
            'aluGponOnu' => [
                'description' => 'aluGponOnu',
                'help' => 'GPON Optical Network Unit.',
            ],
            'aluGponPhysicalUni' => [
                'description' => 'aluGponPhysicalUni',
                'help' => 'GPON physical User to Network interface.',
            ],
            'arap' => [
                'description' => 'arap',
                'help' => 'Appletalk Remote Access Protocol.',
            ],
            'arcnet' => [
                'description' => 'arcnet',
                'help' => 'ARCnet.',
            ],
            'arcnetPlus' => [
                'description' => 'arcnetPlus',
                'help' => 'ARCnet Plus.',
            ],
            'async' => [
                'description' => 'async',
                'help' => 'Asynchronous Protocol.',
            ],
            'atmbond' => [
                'description' => 'atmbond',
                'help' => 'atmbond.',
            ],
            'atm' => [
                'description' => 'atm',
                'help' => 'ATM cells.',
            ],
            'atmDxi' => [
                'description' => 'atmDxi',
                'help' => 'ATM DXI.',
            ],
            'atmFuni' => [
                'description' => 'atmFuni',
                'help' => 'ATM FUNI.',
            ],
            'atmIma' => [
                'description' => 'atmIma',
                'help' => 'ATM IMA.',
            ],
            'atmLogical' => [
                'description' => 'atmLogical',
                'help' => 'ATM Logical Port.',
            ],
            'atmRadio' => [
                'description' => 'atmRadio',
                'help' => 'ATM over radio links.',
            ],
            'atmSubInterface' => [
                'description' => 'atmSubInterface',
                'help' => 'ATM Sub Interface.',
            ],
            'atmVciEndPt' => [
                'description' => 'atmVciEndPt',
                'help' => 'ATM VCI End Point.',
            ],
            'atmVirtual' => [
                'description' => 'atmVirtual',
                'help' => 'ATM Virtual Interface.',
            ],
            'aviciOpticalEther' => [
                'description' => 'aviciOpticalEther',
                'help' => 'Avici Optical Ethernet Aggregate.',
            ],
            'bgppolicyaccounting' => [
                'description' => 'bgppolicyaccounting',
                'help' => 'BGP Policy Accounting.',
            ],
            'bits' => [
                'description' => 'bits',
                'help' => 'bitsport.',
            ],
            'bridge' => [
                'description' => 'bridge',
                'help' => 'Transparent bridge interface.',
            ],
            'bsc' => [
                'description' => 'bsc',
                'help' => 'Bisynchronous Protocol.',
            ],
            'cableDownstreamRfPort' => [
                'description' => 'cableDownstreamRfPort',
                'help' => 'CATV downstream RF Port.',
            ],
            'capwapDot11Bss' => [
                'description' => 'capwapDot11Bss',
                'help' => 'WLAN BSS Interface.',
            ],
            'capwapDot11Profile' => [
                'description' => 'capwapDot11Profile',
                'help' => 'WLAN Profile Interface.',
            ],
            'capwapWtpVirtualRadio' => [
                'description' => 'capwapWtpVirtualRadio',
                'help' => 'WTP Virtual Radio Interface.',
            ],
            'cblVectaStar' => [
                'description' => 'cblVectaStar',
                'help' => 'Cambridge Broadband Networks Limited VectaStar.',
            ],
            'cctEmul' => [
                'description' => 'cctEmul',
                'help' => 'ATM Emulated circuit.',
            ],
            'ces' => [
                'description' => 'ces',
                'help' => 'Circuit Emulation Service.',
            ],
            'channel' => [
                'description' => 'channel',
                'help' => 'Channel.',
            ],
            'ciscoISLvlan' => [
                'description' => 'ciscoISLvlan',
                'help' => 'Layer 2 Virtual LAN using Cisco ISL.',
            ],
            'cnr' => [
                'description' => 'cnr',
                'help' => 'Combat Net Radio.',
            ],
            'coffee' => [
                'description' => 'coffee',
                'help' => 'Coffee pot.',
            ],
            'compositeLink' => [
                'description' => 'compositeLink',
                'help' => 'Avici Composite Link Interface.',
            ],
            'dcn' => [
                'description' => 'dcn',
                'help' => 'Data Communications Network.',
            ],
            'digitalPowerline' => [
                'description' => 'digitalPowerline',
                'help' => 'IP over Power Lines.',
            ],
            'digitalWrapperOverheadChannel' => [
                'description' => 'digitalWrapperOverheadChannel',
                'help' => 'Digital Wrapper.',
            ],
            'dlsw' => [
                'description' => 'dlsw',
                'help' => 'Data Link Switching.',
            ],
            'docsCableDownstream' => [
                'description' => 'docsCableDownstream',
                'help' => 'CATV Downstream interface.',
            ],
            'docsCableMaclayer' => [
                'description' => 'docsCableMaclayer',
                'help' => 'CATV Mac Layer.',
            ],
            'docsCableMCmtsDownstream' => [
                'description' => 'docsCableMCmtsDownstream',
                'help' => 'CATV Modular CMTS Downstream Interface.',
            ],
            'docsCableUpstreamChannel' => [
                'description' => 'docsCableUpstreamChannel',
                'help' => 'CATV Upstream Channel.',
            ],
            'docsCableUpstream' => [
                'description' => 'docsCableUpstream',
                'help' => 'CATV Upstream interface.',
            ],
            'docsCableUpstreamRfPort' => [
                'description' => 'docsCableUpstreamRfPort',
                'help' => 'DOCSIS CATV Upstream RF Port.',
            ],
            'ds0Bundle' => [
                'description' => 'ds0Bundle',
                'help' => 'Group of ds0s on the same ds1.',
            ],
            'ds0' => [
                'description' => 'ds0',
                'help' => 'Digital Signal Level 0.',
            ],
            'ds1FDL' => [
                'description' => 'ds1FDL',
                'help' => 'Facility Data Link (4Kbps) on a DS1.',
            ],
            'ds3' => [
                'description' => 'ds3',
                'help' => 'DS3-MIB.',
            ],
            'dtm' => [
                'description' => 'dtm',
                'help' => 'Dynamic synchronous Transfer Mode.',
            ],
            'dvbAsiIn' => [
                'description' => 'dvbAsiIn',
                'help' => 'DVB-ASI Input.',
            ],
            'dvbAsiOut' => [
                'description' => 'dvbAsiOut',
                'help' => 'DVB-ASI Output.',
            ],
            'dvbRccDownstream' => [
                'description' => 'dvbRccDownstream',
                'help' => 'DVB-RCC Downstream Channel.',
            ],
            'dvbRccMacLayer' => [
                'description' => 'dvbRccMacLayer',
                'help' => 'DVB-RCC MAC Layer.',
            ],
            'dvbRccUpstream' => [
                'description' => 'dvbRccUpstream',
                'help' => 'DVB-RCC Upstream Channel.',
            ],
            'dvbRcsMacLayer' => [
                'description' => 'dvbRcsMacLayer',
                'help' => 'DVB-RCS MAC Layer.',
            ],
            'dvbRcsTdma' => [
                'description' => 'dvbRcsTdma',
                'help' => 'DVB-RCS TDMA.',
            ],
            'dvbTdm' => [
                'description' => 'dvbTdm',
                'help' => 'DVB Satellite TDM.',
            ],
            'econet' => [
                'description' => 'econet',
                'help' => 'Acorn Econet.',
            ],
            'eon' => [
                'description' => 'eon',
                'help' => 'CLNP over IP.',
            ],
            'eplrs' => [
                'description' => 'eplrs',
                'help' => 'Ext Pos Loc Report Sys.',
            ],
            'escon' => [
                'description' => 'escon',
                'help' => 'IBM Enterprise Systems Connection.',
            ],
            'ethernet3Mbit' => [
                'description' => 'ethernet3Mbit',
                'help' => 'ethernet3Mbit ',
            ],
            'fast' => [
                'description' => 'fast',
                'help' => 'Fast channel.',
            ],
            'fcipLink' => [
                'description' => 'fcipLink',
                'help' => 'FCIP Link.',
            ],
            'fibreChannel' => [
                'description' => 'fibreChannel',
                'help' => 'Fibre Channel.',
            ],
            'frameRelay' => [
                'description' => 'frameRelay',
                'help' => 'DTE only.',
            ],
            'frameRelayMPI' => [
                'description' => 'frameRelayMPI',
                'help' => 'Multiproto Interconnect over FR.',
            ],
            'frameRelayService' => [
                'description' => 'frameRelayService',
                'help' => 'FRNETSERV-MIB.',
            ],
            'frDlciEndPt' => [
                'description' => 'frDlciEndPt',
                'help' => 'Frame Relay DLCI End Point.',
            ],
            'frf16MfrBundle' => [
                'description' => 'frf16MfrBundle',
                'help' => 'FRF.16 Multilink Frame Relay.',
            ],
            'frForward' => [
                'description' => 'frForward',
                'help' => 'Frame Forward Interface.',
            ],
            'g703at2mb' => [
                'description' => 'g703at2mb',
                'help' => 'Obsolete; see DS1-MIB.',
            ],
            'g703at64k' => [
                'description' => 'g703at64k',
                'help' => 'CCITT G703 at 64Kbps.',
            ],
            'g9981' => [
                'description' => 'g9981',
                'help' => 'G.998.1 bonded interface.',
            ],
            'g9982' => [
                'description' => 'g9982',
                'help' => 'G.998.2 bonded interface.',
            ],
            'g9983' => [
                'description' => 'g9983',
                'help' => 'G.998.3 bonded interface.',
            ],
            'gfp' => [
                'description' => 'gfp',
                'help' => 'Generic Framing Procedure (GFP).',
            ],
            'gpon' => [
                'description' => 'gpon',
                'help' => 'Gigabit-capable passive optical networks (G-PON) as per ITU-T G.948.',
            ],
            'gr303IDT' => [
                'description' => 'gr303IDT',
                'help' => 'Integrated Digital Terminal.',
            ],
            'gr303RDT' => [
                'description' => 'gr303RDT',
                'help' => 'Remote Digital Terminal.',
            ],
            'gtp' => [
                'description' => 'gtp',
                'help' => 'GTP (GPRS Tunneling Protocol).',
            ],
            'h323Gatekeeper' => [
                'description' => 'h323Gatekeeper',
                'help' => 'H323 Gatekeeper.',
            ],
            'h323Proxy' => [
                'description' => 'h323Proxy',
                'help' => 'H323 Voice and Video Proxy.',
            ],
            'hdlc' => [
                'description' => 'hdlc',
                'help' => 'HDLC.',
            ],
            'hdsl2' => [
                'description' => 'hdsl2',
                'help' => 'High Bit-Rate DSL - 2nd generation.',
            ],
            'hiperlan2' => [
                'description' => 'hiperlan2',
                'help' => 'HIPERLAN Type 2 Radio Interface.',
            ],
            'hippi' => [
                'description' => 'hippi',
                'help' => 'hippi ',
            ],
            'hippiInterface' => [
                'description' => 'hippiInterface',
                'help' => 'HIPPI interfaces.',
            ],
            'homepna' => [
                'description' => 'homepna',
                'help' => 'HomePNA ITU-T G.989.',
            ],
            'hostPad' => [
                'description' => 'hostPad',
                'help' => 'CCITT-ITU X.29 PAD Protocol.',
            ],
            'hssi' => [
                'description' => 'hssi',
                'help' => 'hssi ',
            ],
            'ibm370parChan' => [
                'description' => 'ibm370parChan',
                'help' => 'IBM System 360/370 OEMI Channel.',
            ],
            'idsl' => [
                'description' => 'idsl',
                'help' => 'Digital Subscriber Loop over ISDN.',
            ],
            'ieee1394' => [
                'description' => 'ieee1394',
                'help' => 'IEEE1394 High Performance Serial Bus.',
            ],
            'ieee80211' => [
                'description' => 'ieee80211',
                'help' => 'Radio spread spectrum.',
            ],
            'ieee80212' => [
                'description' => 'ieee80212',
                'help' => '100BaseVG.',
            ],
            'ieee802154' => [
                'description' => 'ieee802154',
                'help' => 'IEEE 802.15.4 WPAN interface.',
            ],
            'ieee80216WMAN' => [
                'description' => 'ieee80216WMAN',
                'help' => 'IEEE 802.16 WMAN interface.',
            ],
            'ieee8023adLag' => [
                'description' => 'ieee8023adLag',
                'help' => 'IEEE 802.3ad Link Aggregate.',
            ],
            'if-gsn' => [
                'description' => 'if-gsn',
                'help' => 'HIPPI-6400.',
            ],
            'ifPwType' => [
                'description' => 'ifPwType',
                'help' => 'Pseudowire interface type.',
            ],
            'ifVfiType' => [
                'description' => 'ifVfiType',
                'help' => 'VPLS Forwarding Instance Interface Type.',
            ],
            'ilan' => [
                'description' => 'ilan',
                'help' => 'Internal LAN on a bridge per IEEE 802.1ap.',
            ],
            'imt' => [
                'description' => 'imt',
                'help' => 'Inter-Machine Trunks.',
            ],
            'infiniband' => [
                'description' => 'infiniband',
                'help' => 'Infiniband.',
            ],
            'interleave' => [
                'description' => 'interleave',
                'help' => 'Interleave channel.',
            ],
            'ip' => [
                'description' => 'ip',
                'help' => 'IP (for APPN HPR in IP networks).',
            ],
            'ipForward' => [
                'description' => 'ipForward',
                'help' => 'IP Forwarding Interface.',
            ],
            'ipOverAtm' => [
                'description' => 'ipOverAtm',
                'help' => 'IBM ipOverAtm.',
            ],
            'ipOverCdlc' => [
                'description' => 'ipOverCdlc',
                'help' => 'IBM ipOverCdlc.',
            ],
            'ipOverClaw' => [
                'description' => 'ipOverClaw',
                'help' => 'IBM Common Link Access to Workstn.',
            ],
            'ipSwitch' => [
                'description' => 'ipSwitch',
                'help' => 'IP Switching Objects.',
            ],
            'isdn' => [
                'description' => 'isdn',
                'help' => 'ISDN and X.25.',
            ],
            'isdns' => [
                'description' => 'isdns',
                'help' => 'ISDN S/T interface.',
            ],
            'isdnu' => [
                'description' => 'isdnu',
                'help' => 'ISDN U interface.',
            ],
            'iso88022llc' => [
                'description' => 'iso88022llc',
                'help' => 'iso88022llc ',
            ],
            'iso88025CRFPInt' => [
                'description' => 'iso88025CRFPInt',
                'help' => 'ISO 802.5 CRFP.',
            ],
            'iso88025Dtr' => [
                'description' => 'iso88025Dtr',
                'help' => 'ISO 802.5r DTR.',
            ],
            'iso88025Fiber' => [
                'description' => 'iso88025Fiber',
                'help' => 'ISO 802.5j Fiber Token Ring.',
            ],
            'isup' => [
                'description' => 'isup',
                'help' => 'ISUP.',
            ],
            'l2vlan' => [
                'description' => 'l2vlan',
                'help' => 'Layer 2 Virtual LAN using 802.1Q.',
            ],
            'l3ipvlan' => [
                'description' => 'l3ipvlan',
                'help' => 'Layer 3 Virtual LAN using IP.',
            ],
            'l3ipxvlan' => [
                'description' => 'l3ipxvlan',
                'help' => 'Layer 3 Virtual LAN using IPX.',
            ],
            'lapd' => [
                'description' => 'lapd',
                'help' => 'Link Access Protocol D.',
            ],
            'lapf' => [
                'description' => 'lapf',
                'help' => 'LAP F.',
            ],
            'linegroup' => [
                'description' => 'linegroup',
                'help' => 'Interface common to multiple lines.',
            ],
            'lmp' => [
                'description' => 'lmp',
                'help' => 'Link Management Protocol.',
            ],
            'localTalk' => [
                'description' => 'localTalk',
                'help' => 'localTalk ',
            ],
            'macSecControlledIF' => [
                'description' => 'macSecControlledIF',
                'help' => 'MACSecControlled.',
            ],
            'macSecUncontrolledIF' => [
                'description' => 'macSecUncontrolledIF',
                'help' => 'MACSecUncontrolled.',
            ],
            'mediaMailOverIp' => [
                'description' => 'mediaMailOverIp',
                'help' => 'Multimedia Mail over IP.',
            ],
            'mfSigLink' => [
                'description' => 'mfSigLink',
                'help' => 'Multi-frequency signaling link.',
            ],
            'mocaVersion1' => [
                'description' => 'mocaVersion1',
                'help' => 'MultiMedia over Coax Alliance (MoCA) Interface as documented in information provided privately to IANA.',
            ],
            'modem' => [
                'description' => 'modem',
                'help' => 'Generic modem.',
            ],
            'mpc' => [
                'description' => 'mpc',
                'help' => 'IBM multi-protocol channel support.',
            ],
            'mpegTransport' => [
                'description' => 'mpegTransport',
                'help' => 'MPEG transport interface.',
            ],
            'mpls' => [
                'description' => 'mpls',
                'help' => 'MPLS.',
            ],
            'mplsTunnel' => [
                'description' => 'mplsTunnel',
                'help' => 'MPLS Tunnel Virtual Interface.',
            ],
            'msdsl' => [
                'description' => 'msdsl',
                'help' => 'Multi-rate Symmetric DSL.',
            ],
            'mvl' => [
                'description' => 'mvl',
                'help' => 'Multiple Virtual Lines DSL.',
            ],
            'myrinet' => [
                'description' => 'myrinet',
                'help' => 'Myricom Myrinet.',
            ],
            'nfas' => [
                'description' => 'nfas',
                'help' => 'Non-Facility Associated Signaling.',
            ],
            'nsip' => [
                'description' => 'nsip',
                'help' => 'XNS over IP.',
            ],
            'opticalChannel' => [
                'description' => 'opticalChannel',
                'help' => 'Optical Channel.',
            ],
            'opticalChannelGroup' => [
                'description' => 'opticalChannelGroup',
                'help' => 'Optical Channel Group.',
            ],
            'opticalTransport' => [
                'description' => 'opticalTransport',
                'help' => 'Optical Transport.',
            ],
            'otnOdu' => [
                'description' => 'otnOdu',
                'help' => 'OTN Optical Data Unit.',
            ],
            'otnOtu' => [
                'description' => 'otnOtu',
                'help' => 'OTN Optical channel Transport Unit.',
            ],
            'para' => [
                'description' => 'para',
                'help' => 'Parallel-port.',
            ],
            'pdnEtherLoop1' => [
                'description' => 'pdnEtherLoop1',
                'help' => 'Paradyne EtherLoop 1.',
            ],
            'pdnEtherLoop2' => [
                'description' => 'pdnEtherLoop2',
                'help' => 'Paradyne EtherLoop 2.',
            ],
            'pip' => [
                'description' => 'pip',
                'help' => 'Provider Instance Port on a bridge per IEEE 802.1ah PBB.',
            ],
            'plc' => [
                'description' => 'plc',
                'help' => 'Power Line Communications.',
            ],
            'pon155' => [
                'description' => 'pon155',
                'help' => 'FSAN 155Mb Symetrical PON interface.',
            ],
            'pon622' => [
                'description' => 'pon622',
                'help' => 'FSAN 622Mb Symetrical PON interface.',
            ],
            'pos' => [
                'description' => 'pos',
                'help' => 'Packet over SONET/SDH Interface.',
            ],
            'ppp' => [
                'description' => 'ppp',
                'help' => 'ppp ',
            ],
            'pppMultilinkBundle' => [
                'description' => 'pppMultilinkBundle',
                'help' => 'PPP Multilink Bundle.',
            ],
            'propAtm' => [
                'description' => 'propAtm',
                'help' => 'Proprietary ATM.',
            ],
            'propCnls' => [
                'description' => 'propCnls',
                'help' => 'Proprietary Connectionless Protocol.',
            ],
            'propDocsWirelessDownstream' => [
                'description' => 'propDocsWirelessDownstream',
                'help' => 'Cisco proprietary Downstream.',
            ],
            'propDocsWirelessMaclayer' => [
                'description' => 'propDocsWirelessMaclayer',
                'help' => 'Cisco proprietary Maclayer.',
            ],
            'propDocsWirelessUpstream' => [
                'description' => 'propDocsWirelessUpstream',
                'help' => 'Cisco proprietary Upstream.',
            ],
            'propMultiplexor' => [
                'description' => 'propMultiplexor',
                'help' => 'Proprietary multiplexing.',
            ],
            'propPointToPointSerial' => [
                'description' => 'propPointToPointSerial',
                'help' => 'Proprietary serial.',
            ],
            'propVirtual' => [
                'description' => 'propVirtual',
                'help' => 'Proprietary virtual/internal.',
            ],
            'propWirelessP2P' => [
                'description' => 'propWirelessP2P',
                'help' => 'Prop. P2P wireless interface.',
            ],
            'q2931' => [
                'description' => 'q2931',
                'help' => 'Q.2931.',
            ],
            'qam' => [
                'description' => 'qam',
                'help' => 'RF Qam Interface.',
            ],
            'qllc' => [
                'description' => 'qllc',
                'help' => 'SNA QLLC.',
            ],
            'radioMAC' => [
                'description' => 'radioMAC',
                'help' => 'MAC layer over radio links.',
            ],
            'radsl' => [
                'description' => 'radsl',
                'help' => 'Rate-Adapt. Digital Subscriber Loop.',
            ],
            'reachDSL' => [
                'description' => 'reachDSL',
                'help' => 'Long Reach DSL.',
            ],
            'rpr' => [
                'description' => 'rpr',
                'help' => 'Resilient Packet Ring Interface Type.',
            ],
            'rs232' => [
                'description' => 'rs232',
                'help' => 'rs232 ',
            ],
            'rsrb' => [
                'description' => 'rsrb',
                'help' => 'Remote Source Route Bridging.',
            ],
            'sdsl' => [
                'description' => 'sdsl',
                'help' => 'Symmetric Digital Subscriber Loop.',
            ],
            'shdsl' => [
                'description' => 'shdsl',
                'help' => 'Multirate HDSL2.',
            ],
            'sip' => [
                'description' => 'sip',
                'help' => 'SMDS, coffee.',
            ],
            'sipSig' => [
                'description' => 'sipSig',
                'help' => 'SIP Signaling.',
            ],
            'sipTg' => [
                'description' => 'sipTg',
                'help' => 'SIP Trunk Group.',
            ],
            'slip' => [
                'description' => 'slip',
                'help' => 'Generic SLIP.',
            ],
            'smdsDxi' => [
                'description' => 'smdsDxi',
                'help' => 'smdsDxi ',
            ],
            'smdsIcip' => [
                'description' => 'smdsIcip',
                'help' => 'SMDS InterCarrier Interface.',
            ],
            'softwareLoopback' => [
                'description' => 'softwareLoopback',
                'help' => 'softwareLoopback ',
            ],
            'sonet' => [
                'description' => 'sonet',
                'help' => 'SONET or SDH.',
            ],
            'sonetOverheadChannel' => [
                'description' => 'sonetOverheadChannel',
                'help' => 'SONET Overhead Channel.',
            ],
            'sonetPath' => [
                'description' => 'sonetPath',
                'help' => 'sonetPath ',
            ],
            'sonetVT' => [
                'description' => 'sonetVT',
                'help' => 'sonetVT ',
            ],
            'srp' => [
                'description' => 'srp',
                'help' => 'Spatial Reuse Protocol.',
            ],
            'ss7SigLink' => [
                'description' => 'ss7SigLink',
                'help' => 'SS7 Signaling Link.',
            ],
            'stackToStack' => [
                'description' => 'stackToStack',
                'help' => 'IBM stackToStack.',
            ],
            'tdlc' => [
                'description' => 'tdlc',
                'help' => 'IBM twinaxial data link control.',
            ],
            'teLink' => [
                'description' => 'teLink',
                'help' => 'TE Link.',
            ],
            'termPad' => [
                'description' => 'termPad',
                'help' => 'CCITT-ITU X.3 PAD Facility.',
            ],
            'tr008' => [
                'description' => 'tr008',
                'help' => 'TR008.',
            ],
            'transpHdlc' => [
                'description' => 'transpHdlc',
                'help' => 'Transp HDLC.',
            ],
            'tunnel' => [
                'description' => 'tunnel',
                'help' => 'Encapsulation interface.',
            ],
            'ultra' => [
                'description' => 'ultra',
                'help' => 'Ultra Technologies.',
            ],
            'usb' => [
                'description' => 'usb',
                'help' => 'USB Interface.',
            ],
            'v11' => [
                'description' => 'v11',
                'help' => 'CCITT V.11/X.21.',
            ],
            'v35' => [
                'description' => 'v35',
                'help' => 'v35 ',
            ],
            'v36' => [
                'description' => 'v36',
                'help' => 'CCITT V.36.',
            ],
            'v37' => [
                'description' => 'v37',
                'help' => 'V.37.',
            ],
            'vdsl2' => [
                'description' => 'vdsl2',
                'help' => 'Very high speed digital subscriber line Version 2 (as per ITU-T Recommendation G.993.2).',
            ],
            'vdsl' => [
                'description' => 'vdsl',
                'help' => 'Very H-Speed Digital Subscrib. Loop.',
            ],
            'virtualIpAddress' => [
                'description' => 'virtualIpAddress',
                'help' => 'IBM VIPA.',
            ],
            'virtualTg' => [
                'description' => 'virtualTg',
                'help' => 'Virtual Trunk Group.',
            ],
            'vmwareNicTeam' => [
                'description' => 'vmwareNicTeam',
                'help' => 'VMware NIC Team.',
            ],
            'vmwareVirtualNic' => [
                'description' => 'vmwareVirtualNic',
                'help' => 'VMware Virtual Network Interface.',
            ],
            'voiceDID' => [
                'description' => 'voiceDID',
                'help' => 'Voice Direct Inward Dialing.',
            ],
            'voiceEBS' => [
                'description' => 'voiceEBS',
                'help' => 'Voice P-phone EBS physical interface.',
            ],
            'voiceEM' => [
                'description' => 'voiceEM',
                'help' => 'Voice recEive and transMit.',
            ],
            'voiceEMFGD' => [
                'description' => 'voiceEMFGD',
                'help' => 'Voice E&amp;M Feature Group D.',
            ],
            'voiceEncap' => [
                'description' => 'voiceEncap',
                'help' => 'Voice encapsulation.',
            ],
            'voiceFGDEANA' => [
                'description' => 'voiceFGDEANA',
                'help' => 'Voice FGD Exchange Access North American.',
            ],
            'voiceFGDOS' => [
                'description' => 'voiceFGDOS',
                'help' => 'Voice FGD Operator Services.',
            ],
            'voiceFXO' => [
                'description' => 'voiceFXO',
                'help' => 'Voice Foreign Exchange Office.',
            ],
            'voiceFXS' => [
                'description' => 'voiceFXS',
                'help' => 'Voice Foreign Exchange Station.',
            ],
            'voiceOverAtm' => [
                'description' => 'voiceOverAtm',
                'help' => 'Voice over ATM.',
            ],
            'voiceOverCable' => [
                'description' => 'voiceOverCable',
                'help' => 'Voice Over Cable Interface.',
            ],
            'voiceOverFrameRelay' => [
                'description' => 'voiceOverFrameRelay',
                'help' => 'Voice Over Frame Relay.',
            ],
            'voiceOverIp' => [
                'description' => 'voiceOverIp',
                'help' => 'Voice over IP encapsulation.',
            ],
            'wwanPP2' => [
                'description' => 'wwanPP2',
                'help' => '3GPP2 WWAN.',
            ],
            'wwanPP' => [
                'description' => 'wwanPP',
                'help' => '3GPP WWAN.',
            ],
            'x213' => [
                'description' => 'x213',
                'help' => 'CCITT-ITU X213.',
            ],
            'x25huntGroup' => [
                'description' => 'x25huntGroup',
                'help' => 'X25 Hunt Group.',
            ],
            'x25mlp' => [
                'description' => 'x25mlp',
                'help' => 'Multi-Link Protocol.',
            ],
            'x25ple' => [
                'description' => 'x25ple',
                'help' => 'x25ple ',
            ],
            'x86Laps' => [
                'description' => 'x86Laps',
                'help' => 'LAPS based on ITU-T X.86/Y.1323.',
            ],
        ],

        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Unix Agent',
            ],
            'os' => [
                'description' => 'OS',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'sensors' => [
                'description' => 'Sensors',
            ],
            'processors' => [
                'description' => 'Processors',
            ],
            'mempools' => [
                'description' => 'Mempools',
            ],
            'storage' => [
                'description' => 'Storage',
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
                'description' => 'Ports',
            ],
            'bgp-peers' => [
                'description' => 'BGP Peers',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'toner' => [
                'description' => 'Toner',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wifi' => [
                'description' => 'Wifi',
            ],
            'wireless' => [
                'description' => 'Wireless',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => 'Cisco IPSec flow Monitor',
            ],
            'cisco-remote-access-monitor' => [
                'description' => 'Cisco remote access Monitor',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'cisco-sla' => [
                'description' => 'Cisco SLA',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cipsec-tunnels' => [
                'description' => 'Cipsec Tunnels',
            ],
            'cisco-ace-loadbalancer' => [
                'description' => 'Cisco ACE Loadbalancer',
            ],
            'cisco-ace-serverfarms' => [
                'description' => 'Cisco ACE Serverfarms',
            ],
            'cisco-asa-firewall' => [
                'description' => 'Cisco ASA Firewall',
            ],
            'cisco-voice' => [
                'description' => 'Cisco Voice',
            ],
            'cisco-cbqos' => [
                'description' => 'Cisco CBQOS',
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
                'description' => 'Availability',
            ],
            'entity-physical' => [
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'applications' => [
                'description' => 'Applications',
            ],
            'mib' => [
                'description' => 'MIB',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'loadbalancers' => [
                'description' => 'Loadbalancers',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
        ],
        'ports_fdb_purge' => [
            'description' => 'Port FDB entries older than',
            'help' => 'Cleanup done by daily.sh',
        ],
        'ports_purge' => [
            'description' => 'Ports older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'prometheus' => [
            'enable' => [
                'description' => 'Enable',
                'help' => 'Exports metrics to Prometheus Push Gateway',
            ],
            'url' => [
                'description' => 'URL',
                'help' => 'The URL of the Prometheus Push Gateway to send data to',
            ],
            'Job' => [
                'description' => 'Job',
                'help' => 'Job label for exported metrics',
            ],
            'attach_sysname' => [
                'description' => 'Attach Device sysName',
                'help' => 'Attach sysName information put to Prometheus.',
            ],
        ],
        'public_status' => [
            'description' => 'Show status publicly',
            'help' => 'Shows the status of some devices on the logon page without authentication.',
        ],
        'routes_max_number' => [
            'description' => 'Max number of routes allowed for discovery',
            'help' => 'No route will be discovered if the size of the routing table is bigger than this number',
        ],
        'nets' => [
            'description' => 'Autodiscovery Networks',
            'help' => 'Networks from which devices will be discovered automatically.',
        ],
        'autodiscovery' => [
            'nets-exclude' => [
                'description' => 'Networks/IPs to be ignored',
                'help' => 'Networks/IPs which will not be discovered automatically. Excludes also IPs from Autodiscovery Networks',
            ],
        ],
        'route_purge' => [
            'description' => 'Route entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Change the rrd heartbeat value (default 600)',
            ],
            'step' => [
                'description' => 'Change the rrd step value (default 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD Location',
            'help' => 'Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files.',
        ],
        'rrd_purge' => [
            'description' => 'RRD Files entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'RRD Format Settings',
            'help' => 'These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries.',
        ],
        'rrdcached' => [
            'description' => 'Enable rrdcached (socket)',
            'help' => 'Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Path to rrdtool',
        ],
        'rrdtool_tune' => [
            'description' => 'Tune all rrd port files to use max values',
            'help' => 'Auto tune maximum value for rrd port files',
        ],
        'sfdp' => [
            'description' => 'Path to sfdp',
        ],
        'shorthost_target_length' => [
            'description' => 'Shortened hostname maximum length',
            'help' => 'Shrinks hostname to maximum length, but always complete subdomain parts',
        ],
        'site_style' => [
            'description' => 'Set the site css style',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transport (priority)',
                'help' => 'Select enabled transports and order them as you want them to be tried.',
            ],
            'version' => [
                'description' => 'Version (priority)',
                'help' => 'Select enabled versions and order them as you want them to be tried.',
            ],
            'community' => [
                'description' => 'Communities (priority)',
                'help' => 'Enter community strings for v1 and v2c and order them as you want them to be tried',
            ],
            'max_repeaters' => [
                'description' => 'Max Repeaters',
                'help' => 'Set repeaters to use for SNMP bulk requests',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Set the tcp/udp port to be used for SNMP',
            ],
            'timeout' => [
                'description' => 'Timeout',
                'help' => 'SNMP Timeout in seconds',
            ],
            'retries' => [
                'description' => 'Retries',
                'help' => 'how many times to retry the query',
            ],
            'v3' => [
                'description' => 'SNMP v3 Authentication (priority)',
                'help' => 'Set up v3 authentication variables and order them as you want them to be tried',
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
            'description' => 'Path to snmpbulkwalk',
        ],
        'snmpget' => [
            'description' => 'Path to snmpget',
        ],
        'snmpgetnext' => [
            'description' => 'Path to snmpgetnext',
        ],
        'snmptranslate' => [
            'description' => 'Path to snmptranslate',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'Create eventlog for snmptraps',
                'help' => 'Independently of the action that may be mapped to the trap',
            ],
            'eventlog_detailed' => [
                'description' => 'Enable detailed logs',
                'help' => 'Add all OIDs received with the trap in the eventlog',
            ],
        ],
        'snmpwalk' => [
            'description' => 'Path to snmpwalk',
        ],
        'syslog_filter' => [
            'description' => 'Filter syslog messages containing',
        ],
        'syslog_purge' => [
            'description' => 'Syslog entries older than (days)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'title_image' => [
            'description' => 'Title Image',
            'help' => 'Overrides the default Title Image.',
        ],
        'traceroute' => [
            'description' => 'Path to traceroute',
        ],
        'traceroute6' => [
            'description' => 'Path to traceroute6',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent connection timeout',
            ],
            'port' => [
                'description' => 'Default unix-agent port',
                'help' => 'Default port for the unix-agent (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent read timeout',
            ],
        ],
        'update' => [
            'description' => 'Enable updates in ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Set update Channel',
            'options' => [
                'master' => 'master',
                'release' => 'release',
            ],
        ],
        'uptime_warning' => [
            'description' => 'Show Device as warning if Uptime below (seconds)',
            'help' => 'Shows Device as warning if Uptime is below this value. Default 24h',
        ],
        'virsh' => [
            'description' => 'Path to virsh',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Availability box width',
                'help' => 'Input desired tile width in pixels for box size in full view',
            ],
            'availability_map_compact' => [
                'description' => 'Availability map compact view',
                'help' => 'Availability map view with small indicators',
            ],
            'availability_map_sort_status' => [
                'description' => 'Sort by status',
                'help' => 'Sort devices and services by status',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Use device groups filter',
                'help' => 'Enable usage of device groups filter',
            ],
            'default_dashboard_id' => [
                'description' => 'Default dashboard',
                'help' => 'Global default dashboard_id for all users who do not have their own default set',
            ],
            'dynamic_graphs' => [
                'description' => 'Enable dynamic graphs',
                'help' => 'Enable dynamic graphs, enables zooming and panning on graphs',
            ],
            'global_search_result_limit' => [
                'description' => 'Set the max search result limit',
                'help' => 'Global search results limit',
            ],
            'graph_stacked' => [
                'description' => 'Use stacked graphs',
                'help' => 'Display stacked graphs instead of inverted graphs',
            ],
            'graph_type' => [
                'description' => 'Set the graph type',
                'help' => 'Set the default graph type',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Set the minimum graph height',
                'help' => 'Minimum Graph Height (default: 300)',
            ],
        ],
        'device_location_map_open' => [
            'description' => 'Location Map open',
            'help' => 'Location Map is shown by default',
        ],
        'force_hostname_to_sysname' => [
            'description' => 'show SysName instead of Hostname',
            'help' => 'When using a dynamic DNS hostname or one that does not resolve, this option would allow you to make use of the sysName instead as the preferred reference to the device',
        ],
        'force_ip_to_sysname' => [
            'description' => 'show SysName instead of IP Address',
            'help' => 'When using IP addresses as a hostname you can instead represent the devices on the WebUI by its sysName resulting in an easier to read overview of your network. This would apply on networks where you don\'t have DNS records for most of your devices',
        ],
        'whois' => [
            'description' => 'Path to whois',
        ],
        'smokeping.integration' => [
            'description' => 'Enable',
            'help' => 'Enable smokeping integration',
        ],
        'smokeping.dir' => [
            'description' => 'Path to rrds',
            'help' => 'Full path to Smokeping RRDs',
        ],
        'smokeping.pings' => [
            'description' => 'Pings',
            'help' => 'Number of pings configured in Smokeping',
        ],
        'smokeping.url' => [
            'description' => 'URL to smokeping',
            'help' => 'Full URL to the smokeping gui',
        ],
    ],
    'twofactor' => [
        'description' => 'Enable Two-Factor Auth',
        'help' => 'Enables the built in Two-Factor authentication. You must set up each account to make it active.',
    ],
    'units' => [
        'days' => 'days',
        'ms' => 'ms',
        'seconds' => 'seconds',
    ],
    'validate' => [
        'boolean' => ':value is not a valid boolean',
        'color' => ':value is not a valid hex color code',
        'email' => ':value is not a valid email',
        'integer' => ':value is not an integer',
        'password' => 'The password is incorrect',
        'select' => ':value is not an allowed value',
        'text' => ':value is not allowed',
        'array' => 'Invalid format',
        'executable' => ':value is not a valid executable',
        'directory' => ':value is not a valid directory',
    ],
];
