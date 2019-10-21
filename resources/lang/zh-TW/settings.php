<?php

return [
    'readonly' => '在 config.php 裡被設定成唯讀，請由 config.php 移除它來啟用。',
    'groups' => [
        'alerting' => '警報',
        'auth' => '驗證',
        'external' => '外部整合',
        'global' => '全域',
        'os' => '作業系統',
        'poller' => '輪詢器',
        'system' => '系統',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => '一般警報設定',
            'email' => '電子郵件設定',
        ],
        'auth' => [
            'general' => '一般驗證設定',
            'ad' => 'Active Directory 設定',
            'ldap' => 'LDAP 設定'
        ],
        'external' => [
            'binaries' => '執行檔位置',
            'location' => '位置資訊設定',
            'oxidized' => 'Oxidized 整合',
            'peeringdb' => 'PeeringDB 整合',
            'nfsen' => 'NfSen 整合',
            'unix-agent' => 'Unix-Agent 整合',
        ],
        'poller' => [
            'distributed' => '分散式輪詢器',
            'ping' => 'Ping',
            'rrdtool' => 'RRDTool 設定',
            'snmp' => 'SNMP',
        ],
        'system' => [
            'cleanup' => '清理',
            'proxy' => 'Proxy',
            'updates' => '更新',
            'server' => '伺服器',
        ],
        'webui' => [
            'availability-map' => '可用性地圖設定',
            'graph' => '圖表設定',
            'dashboard' => '資訊看板設定',
            'search' => '搜尋設定',
            'style' => '樣式',
        ]
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Keep inactive users for',
                'help' => 'Users will be deleted from LibreNMS after this may days of not logging in. 0 means never and users will be recreated if the user logs back in.',
            ]
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Check for duplicate IP when adding devices',
            'help' => 'If a host is added as an ip address it is checked to ensure the ip is not already present. If the ip is present the host is not added. If host is added by hostname this check is not performed. If the setting is true hostnames are resolved and the check is also performed. This helps prevents accidental duplicate hosts.'
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Default acknowledge until alert clears option',
                'help' => 'Default acknowledge until alert clears'
            ],
            'admins' => [
                'description' => 'Issue alerts to admins',
                'help' => 'Alert administrators'
            ],
            'default_copy' => [
                'description' => 'Copy all email alerts to default contact',
                'help' => 'Copy all email alerts to default contact'
            ],
            'default_if_none' => [
                'description' => 'cannot set in webui?',
                'help' => 'Send mail to default contact if no other contacts are found'
            ],
            'default_mail' => [
                'description' => 'Default contact',
                'help' => 'The default mail contact'
            ],
            'default_only' => [
                'description' => 'Send alerts to default contact only',
                'help' => 'Only alert default mail contact'
            ],
            'disable' => [
                'description' => 'Disable alerting',
                'help' => 'Stop alerts being generated'
            ],
            'fixed-contacts' => [
                'description' => 'Updates to contact email addresses not honored',
                'help' => 'If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active'
            ],
            'globals' => [
                'description' => 'Issue alerts to read only users',
                'help' => 'Alert read only administrators'
            ],
            'syscontact' => [
                'description' => 'Issue alerts to sysContact',
                'help' => 'Send alert to email in SNMP sysContact'
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Enable email alerting',
                    'help' => 'Mail alerting transport'
                ]
            ],
            'tolerance_window' => [
                'description' => 'Tolerance window for cron',
                'help' => 'Tolerance window in seconds'
            ],
            'users' => [
                'description' => 'Issue alerts to normal users',
                'help' => 'Alert normal users'
            ]
        ],
        'alert_log_purge' => [
            'description' => 'Alert log entries older than',
            'help' => 'Cleanup done by daily.sh',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Allow unauthenticated graph access',
            'help' => 'Allows any one to access graphs without login'
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Allow the given networks graph access',
            'help' => 'Allow the given networks unauthenticated graph access (does not apply when unauthenticated graphs is enabled)'
        ],
        'api_demo' => [
            'description' => 'This is the demo'
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'API key for PowerDNS Recursor',
                    'help' => 'API key for the PowerDNS Recursor app when connecting directly'
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor use HTTPS?',
                    'help' => 'Use HTTPS instead of HTTP for the PowerDNS Recursor app when connecting directly'
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor port',
                    'help' => 'TCP port to use for the PowerDNS Recursor app when connecting directly'
                ]
            ]
        ],
        'astext' => [
            'description' => 'Key to hold cache of autonomous systems descriptions'
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'groups and users must be under this dn. Example: dc=example,dc=com'
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Check certificate',
            'help' => 'Check certificates for validity. Some servers use self signed certificates, disabling this allows those.'
        ],
        'auth_ad_group_filter' => [
            'description' => 'Group LDAP filter',
            'help' => 'Active Directory LDAP filter for selecting groups'
        ],
        'auth_ad_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level'
        ],
        'auth_ad_user_filter' => [
            'description' => 'User LDAP filter',
            'help' => 'Active Directory LDAP filter for selecting users'
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribute to check username against',
                'help' => 'Attribute used to identify users by username'
            ]
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (overrides bind username)',
            'help' => 'Full DN of bind user'
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Bind password',
            'help' => 'Password for bind user'
        ],
        'auth_ldap_binduser' => [
            'description' => 'Bind username',
            'help' => 'Used to query the LDAP server when no user is logged in (alerts, API, etc)'
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (overrides bind username)',
            'help' => 'Full DN of bind user'
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Bind password',
            'help' => 'Password for bind user'
        ],
        'auth_ad_binduser' => [
            'description' => 'Bind username',
            'help' => 'Used to query the AD server when no user is logged in (alerts, API, etc)'
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP cache expiration',
            'help' => 'Temporarily stores LDAP query results.  Improves speeds, but the data may be stale.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Show debug',
            'help' => 'Shows debug information.  May expose private information, do not leave enabled.'
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Mail attribute'
        ],
        'auth_ldap_group' => [
            'description' => 'Access group DN',
            'help' => 'Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com'
        ],
        'auth_ldap_groupbase' => [
            'description' => 'Group base DN',
            'help' => 'Distinguished name to search for groups Example: ou=group,dc=example,dc=com'
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Group member attribute'
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Find group members by',
            'options' => [
                'username' => 'Username',
                'fulldn' => 'Full DN (using prefix and suffix)',
                'puredn' => 'DN Search (search using uid attribute)'
            ]
        ],
        'auth_ldap_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level'
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP port',
            'help' => 'Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636'
        ],
        'auth_ldap_prefix' => [
            'description' => 'User prefix',
            'help' => 'Used to turn a username into a distinguished name'
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP Server(s)',
            'help' => 'Set server(s), space separated. Prefix with ldaps:// for ssl'
        ],
        'auth_ldap_starttls' => [
            'description' => 'Use STARTTLS',
            'help' => 'Use STARTTLS to secure the connection.  Alternative to LDAPS.',
            'options' => [
                'disabled' => 'Disabled',
                'optional' => 'Optional',
                'required' => 'Required'
            ]
        ],
        'auth_ldap_suffix' => [
            'description' => 'User suffix',
            'help' => 'Used to turn a username into a distinguished name'
        ],
        'auth_ldap_timeout' => [
            'description' => 'Connection timeout',
            'help' => 'If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Unique ID attribute',
            'help' => 'LDAP attribute to use to identify users, must be numeric'
        ],
        'auth_ldap_userdn' => [
            'description' => 'Use full user DN',
            'help' => "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)"
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP version',
            'help' => 'LDAP version to use to talk to the server.  Usually this should be v3',
            'options' => [
                "2" => "2",
                "3" => "3"
            ]
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
                'sso' => 'Single Sign On'
            ]
        ],
        'auth_remember' => [
            'description' => 'Remember me duration',
            'help' => 'Number of days to keep a user logged in when checking the remember me checkbox at log in.',
        ],
        'authlog_purge' => [
            'description' => 'Auth log entries older than (days)',
            'help' => 'Cleanup done by daily.sh'
        ],
        'device_perf_purge' => [
            'description' => 'Device performance entries older than (days)',
            'help' => 'Cleanup done by daily.sh'
        ],
        'distributed_poller' => [
            'description' => 'Enable Distributed Polling (requires additional setup)',
            'help' => 'Enable distributed polling system wide. This is intended for load sharing, not remote polling. You must read the documentation for steps to enable: https://docs.librenms.org/Extensions/Distributed-Poller/'
        ],        
        'distributed_poller_group' => [
            'description' => 'Default Poller Group',
            'help' => 'The default poller group all pollers should poll if none is set in config.php'
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached host',
            'help' => 'The hostname or ip for the memcached server. This is required for poller_wrapper.py and daily.sh locking.'
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached port',
            'help' => 'The port for the memcached server. Default is 11211'
        ],
        'email_auto_tls' => [
            'description' => 'Enable / disable Auto TLS support',
            'options' => [
                'true' => 'Yes',
                'false' => 'No'
            ]
        ],
        'email_backend' => [
            'description' => 'How to deliver mail',
            'help' => 'The backend to use for sending email, can be mail, sendmail or SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP'
            ]
        ],
        'email_from' => [
            'description' => 'From email address',
            'help' => 'Email address used for sending emails (from)'
        ],
        'email_html' => [
            'description' => 'Use HTML emails',
            'help' => 'Send HTML emails'
        ],
        'email_sendmail_path' => [
            'description' => 'Location of sendmail if using this option'
        ],
        'email_smtp_auth' => [
            'description' => 'Enable / disable smtp authentication'
        ],
        'email_smtp_host' => [
            'description' => 'SMTP Host for sending email if using this option'
        ],
        'email_smtp_password' => [
            'description' => 'SMTP Auth password'
        ],
        'email_smtp_port' => [
            'description' => 'SMTP port setting'
        ],
        'email_smtp_secure' => [
            'description' => 'Enable / disable encryption (use tls or ssl)',
            'options' => [
                '' => 'Disabled',
                'tls' => 'TLS',
                'ssl' => 'SSL'
            ]
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP timeout setting'
        ],
        'email_smtp_username' => [
            'description' => 'SMTP Auth username'
        ],
        'email_user' => [
            'description' => 'From name',
            'help' => 'Name used as part of the from address'
        ],
        'eventlog_purge' => [
            'description' => 'Event log entries older than (days)',
            'help' => 'Cleanup done by daily.sh'
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Overrides the default favicon.'
        ],
        'fping' => [
            'description' => 'fping 路徑'
        ],
        'fping6' => [
            'description' => 'fping6 路徑'
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping count',
                'help' => 'The number of pings to send when checking if a host is up or down via icmp'
            ],
            'interval' => [
                'description' => 'fping interval',
                'help' => 'The amount of milliseconds to wait between pings',
            ],
            'timeout' => [
                'description' => 'fping timeout',
                'help' => 'The amount of milliseconds to wait for an echo response before giving up',
            ]
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Geocoding API Key',
                'help' => 'Geocoding API Key (Required to function)'
            ],
            'engine' => [
                'description' => 'Geocoding Engine',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps'
                ]
            ]
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) Proxy',
            'help' => 'Set this as a fallback if http_proxy or https_proxy environment variable is not available.'
        ],
        'ipmitool' => [
            'description' => 'ipmtool 路徑'
        ],
        'login_message' => [
            'description' => '登入訊息',
            'help' => '顯示於登入頁面'
        ],
        'mono_font' => [
            'description' => 'Monospaced Font',
        ],
        'mtr' => [
            'description' => 'mtr 路徑'
        ],
        'nfsen_enable' => [
            'description' => 'Enable NfSen',
            'help' => 'Enable Integration with NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD Directories',
            'help' => 'This value specifies where your NFSen RRD files are located.'
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Set NfSen subdir layout',
            'help' => 'This must match the subdir layout you have set in NfSen. 1 is the default.',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max'
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Max topN value for stats',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N'
        ],
        'nfsen_top_default' => [
            'description' => 'Default Top N'
        ],
        'nfsen_stat_default' => [
            'description' => 'Default Stat'
        ],
        'nfsen_order_default' => [
            'description' => 'Default Order'
        ],
        'nfsen_last_default' => [
            'description' => 'Default Last'
        ],
        'nfsen_lasts' => [
            'description' => 'Default Last Options'
        ],
        'nfsen_split_char' => [
            'description' => 'Split Char',
            'help' => 'This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`'
        ],
        'nfsen_suffix' => [
            'description' => 'File name suffix',
            'help' => 'This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed.'
        ],
        'nmap' => [
            'description' => 'nmap 路徑'
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 主機名稱',
            'help' => 'Should be set to the hostname/ip the librenms server is added as'
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Set the default group returned'
            ],
            'enabled' => [
                'description' => '啟用 Oxidized 支援'
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Enable config versioning access',
                    'help' => 'Enable Oxidized config versioning (requires git backend)'
                ]
            ],
            'group_support' => [
                'description' => 'Enable the return of groups to Oxidized'
            ],
            'reload_nodes' => [
                'description' => 'Reload Oxidized nodes list, each time a device is added'
            ],
            'url' => [
                'description' => 'URL to your Oxidized API',
                'help' => 'Oxidized API url (For example: http://127.0.0.1:8888)'
            ]
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Enable PeeringDB lookup',
                'help' => 'Enable PeeringDB lookup (data is downloaded with daily.sh)'
            ]
        ],
        'perf_times_purge' => [
            'description' => 'Poller performance log entries older than (days)',
            'help' => 'Cleanup done by daily.sh'
        ],
        'ping' => [
            'description' => 'ping 路徑'
        ],
        'ports_fdb_purge' => [
            'description' => 'Port FDB entries older than',
            'help' => 'Cleanup done by daily.sh'
        ],
        'public_status' => [
            'description' => 'Show status publicly',
            'help' => 'Shows the status of some devices on the logon page without authentication.'
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Change the rrd heartbeat value (default 600)'
            ],
            'step' => [
                'description' => 'Change the rrd step value (default 300)'
            ]
        ],
        'rrd_dir' => [
            'description' => 'RRD 位置',
            'help' => 'Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files.'
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式設定',
            'help' => 'These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries.'
        ],
        'rrdcached' => [
            'description' => '啟用 rrdcached (socket)',
            'help' => 'Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost:42217)'
        ],
        'rrdtool' => [
            'description' => 'rrdtool 路徑'
        ],
        'rrdtool_tune' => [
            'description' => 'Tune all rrd port files to use max values',
            'help' => 'Auto tune maximum value for rrd port files'
        ],
        'sfdp' => [
            'description' => 'sfdp 路徑'
        ],
        'site_style' => [
            'description' => 'Set the site css style',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ]
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transport (priority)',
                'help' => 'Select enabled transports and order them as you want them to be tried.'
            ],
            'version' => [
                'description' => 'Version (priority)',
                'help' => 'Select enabled versions and order them as you want them to be tried.'
            ],
            'community' => [
                'description' => 'Communities (priority)',
                'help' => 'Enter community strings for v1 and v2c and order them as you want them to be tried'
            ],
            'max_repeaters' => [
                'description' => 'Max Repeaters',
                'help' => 'Set repeaters to use for SNMP bulk requests'
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Set the tcp/udp port to be used for SNMP'
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
                    'cryptopass' => 'Password'
                ],
                'level' => [
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication and Privacy'
                ]
            ]
        ],
        'snmpbulkwalk' => [
            'description' => 'snmpbulkwalk 路徑'
        ],
        'snmpget' => [
            'description' => 'snmpget 路徑'
        ],
        'snmpgetnext' => [
            'description' => 'snmpgetnext 路徑'
        ],
        'snmptranslate' => [
            'description' => 'snmptranslate 路徑'
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk 路徑'
        ],
        'syslog_filter' => [
            'description' => 'Filter syslog messages containing'
        ],
        'syslog_purge' => [
            'description' => 'Syslog entries older than (days)',
            'help' => 'Cleanup done by daily.sh'
        ],
        'traceroute' => [
            'description' => 'traceroute 路徑'
        ],
        'traceroute6' => [
            'description' => 'traceroute6 路徑'
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent 連線逾時'
            ],
            'port' => [
                'description' => '預設 unix-agent 連接埠',
                'help' => 'unix-agent (check_mk) 預設連接埠號碼'
            ],
            'read-timeout' => [
                'description' => 'Unix-agent 讀取逾時'
            ]
        ],
        'update' => [
            'description' => 'Enable updates in ./daily.sh'
        ],
        'update_channel' => [
            'description' => '設定更新頻道',
            'options' => [
                'master' => 'master',
                'release' => 'release'
            ]
        ],
        'virsh' => [
            'description' => 'virsh 路徑'
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Availability box width',
                'help' => 'Input desired tile width in pixels for box size in full view'
            ],
            'availability_map_compact' => [
                'description' => 'Availability map compact view',
                'help' => 'Availability map view with small indicators'
            ],
            'availability_map_sort_status' => [
                'description' => '依狀態排序',
                'help' => '以狀態做為裝置與服務的排序'
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用裝置群組篩選器',
                'help' => '啟用裝置群組篩選器'
            ],
            'default_dashboard_id' => [
                'description' => '預設資訊看板',
                'help' => '對於沒有設定預設資訊看板的使用者，所要顯示的預設資訊看板'
            ],
            'dynamic_graphs' => [
                'description' => 'Enable dynamic graphs',
                'help' => 'Enable dynamic graphs, enables zooming and panning on graphs'
            ],
            'global_search_result_limit' => [
                'description' => '設定搜尋結果筆數上限',
                'help' => '全域搜尋結果限制'
            ],
            'graph_stacked' => [
                'description' => '使用堆疊圖表',
                'help' => 'Display stacked graphs instead of inverted graphs'
            ],
            'graph_type' => [
                'description' => '設定圖表類型',
                'help' => '設定預設圖表類型',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG'
                ]
            ],
            'min_graph_height' => [
                'description' => '設定圖表最小高度',
                'help' => '圖表最小高度 (預設: 300)'
            ]
        ],
        'whois' => [
            'description' => 'whois 路徑'
        ]
    ],
    'units' => [
        'days' => '日',
        'ms' => '微秒',
        'seconds' => '秒',
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
    ]
];
