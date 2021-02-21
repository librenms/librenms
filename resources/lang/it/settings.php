<?php

return [
    'readonly' => 'Set in config.php, remove from config.php to enable.',
    'groups' => [
        'alerting' => 'Avvisi',
        'auth' => 'Autenticazione',
        'authorization' => 'Authorization',
        'external' => 'External',
        'global' => 'Global',
        'os' => 'OS',
        'discovery' => 'Discovery',
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
        ],
        'external' => [
            'binaries' => 'Binary Locations',
            'location' => 'Location Settings',
            'graylog' => 'Graylog Integration',
            'oxidized' => 'Oxidized Integration',
            'peeringdb' => 'PeeringDB Integration',
            'nfsen' => 'NfSen Integration',
            'unix-agent' => 'Unix-Agent Integration',
        ],
        'poller' => [
            'distributed' => 'Distributed Poller',
            'graphite' => 'Datastore: Graphite',
            'influxdb' => 'Datastore: InfluxDB',
            'opentsdb' => 'Datastore: OpenTSDB',
            'ping' => 'Ping',
            'prometheus' => 'Datastore: Prometheus',
            'rrdtool' => 'Datastore: RRDTool',
            'snmp' => 'SNMP',
            'poller_modules' => 'Poller Modules',
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
        'distributed_poller_group' => [
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
                'description' => 'Geocoding API Key',
                'help' => 'Geocoding API Key (Required to function)',
            ],
            'engine' => [
                'description' => 'Geocoding Engine',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                ],
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
            'host' => [
                'description' => 'Server',
                'help' => 'The IP or hostname of the Prometheus Push Gateway to send data to',
            ],
            'port' => [
                'description' => 'Job',
                'help' => 'Job label for exported metrics',
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
            'description' => 'use SNMP SysName instead of Hostname',
            'help' => 'When using a dynamic DNS hostname or one that does not resolve, this option would allow you to make use of the SNMP sysName instead as the preferred reference to the device',
        ],
        'force_ip_to_sysname' => [
            'description' => 'use SNMP SysName instead of IP Address',
            'help' => 'When using IP addresses as a hostname you can instead represent the devices on the WebUI by its SNMP sysName resulting in an easier to read overview of your network. This would apply on networks where you don\'t have DNS records for most of your devices',
        ],
        'whois' => [
            'description' => 'Path to whois',
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
    ],
];
