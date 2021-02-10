<?php

return [
    'readonly' => '在 config.php 裡被設定成唯讀，請由 config.php 移除它來啟用。',
    'groups' => [
        'alerting' => '警報',
        'auth' => '驗證',
        'authorization' => '授權',
        'external' => '外部整合',
        'global' => '全域',
        'os' => '作業系統',
        'discovery' => '探索',
        'poller' => '輪詢器',
        'system' => '系統',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => '一般警報設定',
            'email' => '電子郵件設定',
            'rules' => '警報規則預設值',
        ],
        'auth' => [
            'general' => '一般驗證設定',
            'ad' => 'Active Directory 設定',
            'ldap' => 'LDAP 設定',
        ],
        'authorization' => [
            'device-group' => '裝置群組設定',
        ],
        'discovery' => [
            'general' => '一般探索設定',
            'route' => '路由探索模組',
            'discovery_modules' => '探索模組',
            'storage' => '儲存模組',
            'networks' => '網路',
        ],
        'external' => [
            'binaries' => '執行檔位置',
            'location' => '位置資訊設定',
            'graylog' => 'Graylog 整合',
            'oxidized' => 'Oxidized 整合',
            'peeringdb' => 'PeeringDB 整合',
            'nfsen' => 'NfSen 整合',
            'unix-agent' => 'Unix-Agent 整合',
            'smokeping' => 'Smokeping 整合',
            'snmptrapd' => 'SNMP Traps 整合',
        ],
        'poller' => [
            'availability' => '裝置可用性',
            'distributed' => '分散式輪詢器',
            'graphite' => '資料存放區: Graphite',
            'influxdb' => '資料存放區: InfluxDB',
            'opentsdb' => '資料存放區: OpenTSDB',
            'ping' => 'Ping',
            'prometheus' => '資料存放區: Prometheus',
            'rrdtool' => 'RRDTool 設定',
            'snmp' => 'SNMP',
            'poller_modules' => '輪詢器模組',
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
            'device' => '裝置設定',
            'worldmap' => '世界地圖設定',
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => '保留未登入使用者於',
                'help' => '設定使用者超過幾天沒有登入後，將會被 LibreNMS 自動刪除。設為 0 表示不會刪除，若使用者重新登入，將會重新建立帳戶。',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => '新增裝置時檢察是否 IP 重複',
            'help' => '以 IP 加入主機時，會先檢查此 IP 是否已存在於系統上，若有則不予加入。若是以主機名稱方式加入時，則不會做此檢查。若設定為 True 時，則以主機名稱方式加入時亦做此檢查，以避免加入重複主機的意外發生。',
        ],
        'alert_rule' => [
            'severity' => [
                'description' => '嚴重性',
                'help' => 'Severity for an Alert',
            ],
            'max_alerts' => [
                'description' => '最多警報次數',
                'help' => 'Count of Alerts to be sent',
            ],
            'delay' => [
                'description' => '延遲',
                'help' => 'Delay before an Alert will be sent',
            ],
            'interval' => [
                'description' => '間隔',
                'help' => 'Interval to be checked for this Alert',
            ],
            'mute_alerts' => [
                'description' => '警報靜音',
                'help' => 'Should Alert only be seen in WebUI',
            ],
            'invert_rule_match' => [
                'description' => '反轉比對規則',
                'help' => 'Alert only if rule doesn\'t match',
            ],
            'recovery_alerts' => [
                'description' => '警報解除',
                'help' => 'Notify if Alert recovers',
            ],
            'invert_map' => [
                'description' => '除了清單之外的所有裝置',
                'help' => 'Alert only for Devices which are not listed',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => '預設認可值到警報解除選項',
                'help' => '預設認可值到警報解除',
            ],
            'admins' => [
                'description' => '向管理員發送警報',
                'help' => '管理員警報',
            ],
            'default_copy' => [
                'description' => '複製所有的郵件警報給預設連絡人',
                'help' => '複製所有的郵件警報給預設連絡人',
            ],
            'default_if_none' => [
                'description' => '無法在 WebUI 設定？',
                'help' => '如果沒有找到其它連絡人，請把郵件發送到預設連絡人',
            ],
            'default_mail' => [
                'description' => '預設連絡人',
                'help' => '預設連絡人郵件位址',
            ],
            'default_only' => [
                'description' => '只發送警報給預設連絡人',
                'help' => '只發送警報給預設郵件連絡人',
            ],
            'disable' => [
                'description' => '停用警報',
                'help' => '停止產生警報',
            ],
            'fixed-contacts' => [
                'description' => '在警告期間不接受連絡人電子郵件的修改',
                'help' => 'If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active',
            ],
            'globals' => [
                'description' => '只發送警報給唯讀使用者',
                'help' => '只發送警報給唯讀管理員',
            ],
            'syscontact' => [
                'description' => '發送警報給 sysContact',
                'help' => '發送警報郵件給 SNMP 中的 sysContact',
            ],
            'transports' => [
                'mail' => [
                    'description' => '啟用郵件警報',
                    'help' => '啟用以郵件傳輸警報',
                ],
            ],
            'tolerance_window' => [
                'description' => 'cron 容錯範圍',
                'help' => 'Tolerance window in seconds',
            ],
            'users' => [
                'description' => '發送警報給一般使用者',
                'help' => '警報通知一般使用者',
            ],
        ],
        'alert_log_purge' => [
            'description' => '警報記錄項目大於',
            'help' => 'Cleanup done by daily.sh',
        ],
        'allow_duplicate_sysName' => [
            'description' => '允許重複 sysName',
            'help' => 'By default duplicate sysNames are disabled from being added to prevent a device with multiple interfaces from being added multiple times',
        ],
        'allow_unauth_graphs' => [
            'description' => '允許未登入存取圖表',
            'help' => '允許在不登入情況下存取圖表',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '允許指定網路存取圖表',
            'help' => '允許指定網路可以在未登入授權查看圖表 (若未啟用 允許未登入存取圖表 則忽略此設定)',
        ],
        'api_demo' => [
            'description' => '這是展示',
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
            'description' => '基礎 DN',
            'help' => 'groups and users must be under this dn. Example: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '檢查憑證',
            'help' => 'Check certificates for validity. Some servers use self signed certificates, disabling this allows those.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'LDAP 群組篩選器',
            'help' => 'Active Directory LDAP filter for selecting groups',
        ],
        'auth_ad_groups' => [
            'description' => '群組存取權限',
            'help' => '定義群組具有的存取權限與等級',
        ],
        'auth_ad_user_filter' => [
            'description' => 'LDAP 使用者篩選',
            'help' => 'Active Directory LDAP filter for selecting users',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribute to check username against',
                'help' => 'Attribute used to identify users by username',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '繫結 DN (覆寫繫結使用者名稱)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '繫結密碼',
            'help' => 'Password for bind user',
        ],
        'auth_ldap_binduser' => [
            'description' => '繫結使用者',
            'help' => 'Used to query the LDAP server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => '繫結 DN (覆寫繫結使用者名稱)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ad_bindpassword' => [
            'description' => '繫結密碼',
            'help' => 'Password for bind user',
        ],
        'auth_ad_binduser' => [
            'description' => '繫結使用者名稱',
            'help' => 'Used to query the AD server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP 快取有效期',
            'help' => 'Temporarily stores LDAP query results.  Improves speeds, but the data may be stale.',
        ],
        'auth_ldap_debug' => [
            'description' => '顯示偵錯資訊',
            'help' => 'Shows debug information.  May expose private information, do not leave enabled.',
        ],
        'auth_ldap_emailattr' => [
            'description' => '郵件屬性',
        ],
        'auth_ldap_group' => [
            'description' => '存取群組 DN',
            'help' => 'Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '群組基礎 DN',
            'help' => 'Distinguished name to search for groups Example: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Group member attribute',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '以下列方式尋找群組成員',
            'options' => [
                'username' => '使用者名稱',
                'fulldn' => 'Full DN (using prefix and suffix)',
                'puredn' => 'DN 搜尋 (使用 uid 屬性搜尋)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP 連接埠',
            'help' => 'Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636',
        ],
        'auth_ldap_prefix' => [
            'description' => '使用者首碼',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP 伺服器',
            'help' => 'Set server(s), space separated. Prefix with ldaps:// for ssl',
        ],
        'auth_ldap_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => 'Use STARTTLS to secure the connection.  Alternative to LDAPS.',
            'options' => [
                'disabled' => '停用',
                'optional' => '選用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '使用者尾碼',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_timeout' => [
            'description' => '連線逾時',
            'help' => 'If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '唯一 ID 屬性',
            'help' => 'LDAP attribute to use to identify users, must be numeric',
        ],
        'auth_ldap_userdn' => [
            'description' => '使用全名 DN',
            'help' => "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)",
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP 版本',
            'help' => '用來與 LDAP Server 進行連接的版本，通常應是 v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => '授權方法 (慎選!)',
            'help' => "授權方法。注意，若設定錯誤將導致您無法登入系統。若真的發生，您可以手動將 config.php 的設定改回 \$config['auth_mechanism'] = 'mysql';",
            'options' => [
                'mysql' => 'MySQL (預設)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP 驗證',
                'ad-authorization' => '外部 AD 驗證',
                'ldap-authorization' => '外部 LDAP 驗證',
                'sso' => '單一簽入 SSO',
            ],
        ],
        'auth_remember' => [
            'description' => '記住我的期限',
            'help' => 'Number of days to keep a user logged in when checking the remember me checkbox at log in.',
        ],
        'authlog_purge' => [
            'description' => '驗證記錄項目大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'base_url' => [
            'description' => '指定 URL',
            'help' => 'This should *only* be set if you want to *force* a particular hostname/port. It will prevent the web interface being usable form any other hostname',
        ],
        'device_perf_purge' => [
            'description' => '裝置效能項目大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'distributed_poller' => [
            'description' => '啟用分散式輪詢 (需要額外設定)',
            'help' => 'Enable distributed polling system wide. This is intended for load sharing, not remote polling. You must read the documentation for steps to enable: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'distributed_poller_group' => [
            'description' => '預設輪詢器群組',
            'help' => 'The default poller group all pollers should poll if none is set in config.php',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 主機',
            'help' => 'The hostname or ip for the memcached server. This is required for poller_wrapper.py and daily.sh locking.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 連接埠',
            'help' => 'The port for the memcached server. Default is 11211',
        ],
        'email_auto_tls' => [
            'description' => '啟用 / 停用自動 TLS 支援',
            'options' => [
                'true' => '是',
                'false' => '否',
            ],
        ],
        'email_backend' => [
            'description' => '寄送郵件方式',
            'help' => 'The backend to use for sending email, can be mail, sendmail or SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '寄件者信箱位址',
            'help' => 'Email address used for sending emails (from)',
        ],
        'email_html' => [
            'description' => '使用 HTML 格式',
            'help' => '寄送 HTML 格式的郵件',
        ],
        'email_sendmail_path' => [
            'description' => '若啟用此選項，sendmail 所在的位置',
        ],
        'email_smtp_auth' => [
            'description' => '啟用 / 停用 SMTP 驗證',
        ],
        'email_smtp_host' => [
            'description' => '指定寄信用的 SMTP 主機',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP 驗證密碼',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP 連接埠設定',
        ],
        'email_smtp_secure' => [
            'description' => '啟用 / 停用加密 (使用 TLS 或 SSL)',
            'options' => [
                '' => '停用',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP 逾時設定',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP 驗證使用者名稱',
        ],
        'email_user' => [
            'description' => '寄件者名稱',
            'help' => 'Name used as part of the from address',
        ],
        'eventlog_purge' => [
            'description' => '事件記錄大於 (天)',
            'help' => '由 daily.sh 進行清理作業',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => '取代預設 Favicon.',
        ],
        'fping' => [
            'description' => 'fping 路徑',
        ],
        'fping6' => [
            'description' => 'fping6 路徑',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping 次數',
                'help' => 'The number of pings to send when checking if a host is up or down via icmp',
            ],
            'interval' => [
                'description' => 'fping 間隔',
                'help' => 'The amount of milliseconds to wait between pings',
            ],
            'timeout' => [
                'description' => 'fping 逾時',
                'help' => 'The amount of milliseconds to wait for an echo response before giving up',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '地理編碼 API 金鑰',
                'help' => 'Geocoding API Key (Required to function)',
            ],
            'engine' => [
                'description' => '地理編碼引擎',
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
                'description' => '啟用',
                'help' => 'Exports metrics to Graphite',
            ],
            'host' => [
                'description' => '伺服器',
                'help' => 'The IP or hostname of the Graphite server to send data to',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => 'The port to use to connect to the Graphite server',
            ],
            'prefix' => [
                'description' => 'Prefix (Optional)',
                'help' => 'Will add the prefix to the start of all metrics.  Must be alphanumeric separated by dots',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => '期間',
                'help' => 'Calculate Device Availability for listed durations. (Durations are defined in seconds)',
            ],
            'availability_consider_maintenance' => [
                'description' => '定期維護不影響可用性',
                'help' => 'Disables the creation of outages and decreasing of availability for devices which are in maintenance mode.',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => '基礎 URI',
                'help' => 'Override the base uri in the case you have modified the Graylog default.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '裝置概觀記錄等級',
                    'help' => 'Sets the maximum log level shown on the device overview page.',
                ],
                'rowCount' => [
                    'description' => '裝置概觀資料筆數',
                    'help' => 'Sets the number of rows show on the device overview page.',
                ],
            ],
            'password' => [
                'description' => '密碼',
                'help' => 'Password for accessing Graylog API.',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => 'The port used to access the Graylog API. If none give, it will be 80 for http and 443 for https.',
            ],
            'server' => [
                'description' => '伺服器',
                'help' => 'The ip or hostname of the Graylog server API endpoint.',
            ],
            'timezone' => [
                'description' => '顯示時區',
                'help' => 'Graylog times are stored in GMT, this setting will change the displayed timezone. The value must be a valid PHP timezone.',
            ],
            'username' => [
                'description' => '使用者名稱',
                'help' => 'Username for accessing the Graylog API.',
            ],
            'version' => [
                'description' => '版本',
                'help' => 'This is used to automatically create the base_uri for the Graylog API. If you have modified the API uri from the default, set this to other and specify your base_uri.',
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) 代理',
            'help' => 'Set this as a fallback if http_proxy or https_proxy environment variable is not available.',
        ],
        'ignore_mount' => [
            'description' => '忽略掛接點',
            'help' => 'Don\'t monitor Disc Usage of this Mountpoints',
        ],
        'ignore_mount_network' => [
            'description' => '忽略網路掛接點',
            'help' => 'Don\'t monitor Disc Usage of Network Mountpoints',
        ],
        'ignore_mount_optical' => [
            'description' => '忽略光碟機',
            'help' => 'Don\'t monitor Disc Usage of optical Drives',
        ],
        'ignore_mount_removable' => [
            'description' => '忽略卸除式磁碟機',
            'help' => 'Don\'t monitor Disc Usage of removable Devices',
        ],
        'ignore_mount_regexp' => [
            'description' => '以 Regex 設定要忽略的掛接點',
            'help' => 'Don\'t monitor Disc Usage of Mountpoints which are matching at least one of this Regular Expressions',
        ],
        'ignore_mount_string' => [
            'description' => '以內含字串設定要忽略的掛接點',
            'help' => 'Don\'t monitor Disc Usage of Mountpoints which contains at least one of this Strings',
        ],
        'ipmitool' => [
            'description' => 'ipmtool 路徑',
        ],
        'login_message' => [
            'description' => '登入訊息',
            'help' => '顯示於登入頁面',
        ],
        'mono_font' => [
            'description' => 'Monospaced 字型',
        ],
        'mtr' => [
            'description' => 'mtr 路徑',
        ],
        'mydomain' => [
            'description' => '主要網域',
            'help' => 'This domain is used for network auto-discovery and other processes. LibreNMS will attempt to append it to unqualified hostnames.',
        ],
        'nfsen_enable' => [
            'description' => '啟用 NfSen',
            'help' => '啟用 NfSen 整合',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD 目錄',
            'help' => 'This value specifies where your NFSen RRD files are located.',
        ],
        'nfsen_subdirlayout' => [
            'description' => '設定 NfSen 子目錄配置',
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
            'description' => '分隔字元',
            'help' => 'This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`',
        ],
        'nfsen_suffix' => [
            'description' => '檔案名稱首碼',
            'help' => 'This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed.',
        ],
        'nmap' => [
            'description' => 'nmap 路徑',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 主機名稱',
            'help' => 'Should be set to the hostname/ip the librenms server is added as',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => '設定預設群組',
            ],
            'enabled' => [
                'description' => '啟用 Oxidized 支援',
            ],
            'features' => [
                'versioning' => [
                    'description' => '啟用組態版本存取',
                    'help' => 'Enable Oxidized config versioning (requires git backend)',
                ],
            ],
            'group_support' => [
                'description' => '啟用將群組提供給 Oxidized',
            ],
            'reload_nodes' => [
                'description' => '在每次新增裝置後，重新載入 Oxidized 節點清單',
            ],
            'url' => [
                'description' => '您的 Oxidized API URL',
                'help' => 'Oxidized API url (For example: http://127.0.0.1:8888)',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => '密碼最小長度',
                'help' => 'Passwords shorter than the given length will be rejected',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => '啟用 PeeringDB 反查',
                'help' => '起用 PeeringDB lookup (資料將於由 daily.sh 進行下載)',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => '啟用使用者存限可取用動態裝置群組',
                ],
            ],
        ],
        'ping' => [
            'description' => 'ping 路徑',
        ],
        'ports_fdb_purge' => [
            'description' => '連接埠 FDB 項目大於',
            'help' => 'Cleanup done by daily.sh',
        ],
        'ports_purge' => [
            'description' => '連接埠大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'prometheus' => [
            'enable' => [
                'description' => '啟用',
                'help' => '匯出指標數據至 Prometheus Push Gateway',
            ],
            'url' => [
                'description' => '網址',
                'help' => '要傳送資料至 Prometheus Push Gateway 的主機網址。',
            ],
            'job' => [
                'description' => 'Job',
                'help' => '指定匯出指標數據的 Job 標籤',
            ],
            'attach_sysname' => [
                'description' => '附加 sysName',
                'help' => '附加裝置的 sysName 資訊至 Prometheus Push Gateway。',
            ],
        ],
        'public_status' => [
            'description' => '公開狀態顯示',
            'help' => '允許不登入的情況下，顯示裝置的狀態資訊。',
        ],
        'routes_max_number' => [
            'description' => '允許探索路由的最大路由數',
            'help' => 'No route will be discovered if the size of the routing table is bigger than this number',
        ],
        'nets' => [
            'description' => '自動探索網路',
            'help' => 'Networks from which devices will be discovered automatically.',
        ],
        'autodiscovery' => [
            'nets-exclude' => [
                'description' => '要忽略的網路或 IP',
                'help' => 'Networks/IPs which will not be discovered automatically. Excludes also IPs from Autodiscovery Networks',
            ],
        ],
        'route_purge' => [
            'description' => '路由記錄大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => '變更 rrd 活動訊號值 (預設 600)',
            ],
            'step' => [
                'description' => '變更 rrd 間距值 (預設 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD 位置',
            'help' => 'Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files.',
        ],
        'rrd_purge' => [
            'description' => 'RRD 檔案項目大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式設定',
            'help' => 'These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries.',
        ],
        'rrdcached' => [
            'description' => '啟用 rrdcached (socket)',
            'help' => 'Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 路徑',
        ],
        'rrdtool_tune' => [
            'description' => '調整所有 rrd 連接埠檔案使用最大值',
            'help' => '自動調整 rrd 連接埠檔案的最大值',
        ],
        'sfdp' => [
            'description' => 'sfdp 路徑',
        ],
        'shorthost_target_length' => [
            'description' => 'Shortened hostname maximum length',
            'help' => 'Shrinks hostname to maximum length, but always complete subdomain parts',
        ],
        'site_style' => [
            'description' => '設定站台 css 樣式',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '傳輸 (優先順序)',
                'help' => 'Select enabled transports and order them as you want them to be tried.',
            ],
            'version' => [
                'description' => '版本 (優先順序)',
                'help' => 'Select enabled versions and order them as you want them to be tried.',
            ],
            'community' => [
                'description' => '社群 (優先順序)',
                'help' => 'Enter community strings for v1 and v2c and order them as you want them to be tried',
            ],
            'max_repeaters' => [
                'description' => '重複擷取最多次數',
                'help' => 'Set repeaters to use for SNMP bulk requests',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => 'Set the tcp/udp port to be used for SNMP',
            ],
            'v3' => [
                'description' => 'SNMP v3 驗證 (優先順序)',
                'help' => 'Set up v3 authentication variables and order them as you want them to be tried',
                'auth' => '驗證',
                'crypto' => '加密',
                'fields' => [
                    'authalgo' => '演算法',
                    'authlevel' => '鄧級',
                    'authname' => '使用者名稱',
                    'authpass' => '密碼',
                    'cryptoalgo' => '演算法',
                    'cryptopass' => '演算法密碼',
                ],
                'level' => [
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication and Privacy',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'snmpbulkwalk 路徑',
        ],
        'snmpget' => [
            'description' => 'snmpget 路徑',
        ],
        'snmpgetnext' => [
            'description' => 'snmpgetnext 路徑',
        ],
        'snmptranslate' => [
            'description' => 'snmptranslate 路徑',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => '為 snmptraps 建立事件記錄',
                'help' => 'Independently of the action that may be mapped to the trap',
            ],
            'eventlog_detailed' => [
                'description' => '啟用詳細記錄',
                'help' => 'Add all OIDs received with the trap in the eventlog',
            ],
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk 路徑',
        ],
        'syslog_filter' => [
            'description' => 'Filter syslog messages containing',
        ],
        'syslog_purge' => [
            'description' => 'Syslog 項目大於 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'title_image' => [
            'description' => '標題圖片',
            'help' => 'Overrides the default Title Image.',
        ],
        'traceroute' => [
            'description' => 'traceroute 路徑',
        ],
        'traceroute6' => [
            'description' => 'traceroute6 路徑',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent 連線逾時',
            ],
            'port' => [
                'description' => '預設 unix-agent 連接埠',
                'help' => 'unix-agent (check_mk) 預設連接埠號碼',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent 讀取逾時',
            ],
        ],
        'update' => [
            'description' => '啟用更新 ./daily.sh',
        ],
        'update_channel' => [
            'description' => '設定更新頻道',
            'options' => [
                'master' => 'master',
                'release' => 'release',
            ],
        ],
        'uptime_warning' => [
            'description' => '如果運作時間低於設定(秒)將裝置顯示警告',
            'help' => 'Shows Device as warning if Uptime is below this value. Default 24h',
        ],
        'virsh' => [
            'description' => 'virsh 路徑',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => '可用性區塊寬度',
                'help' => 'Input desired tile width in pixels for box size in full view',
            ],
            'availability_map_compact' => [
                'description' => '可用性地圖精簡模式',
                'help' => 'Availability map view with small indicators',
            ],
            'availability_map_sort_status' => [
                'description' => '依狀態排序',
                'help' => '以狀態做為裝置與服務的排序',
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用裝置群組篩選器',
                'help' => '啟用裝置群組篩選器',
            ],
            'default_dashboard_id' => [
                'description' => '預設資訊看板',
                'help' => '對於沒有設定預設資訊看板的使用者，所要顯示的預設資訊看板',
            ],
            'dynamic_graphs' => [
                'description' => '啟用動態圖表',
                'help' => 'Enable dynamic graphs, enables zooming and panning on graphs',
            ],
            'global_search_result_limit' => [
                'description' => '設定搜尋結果筆數上限',
                'help' => '全域搜尋結果限制',
            ],
            'graph_stacked' => [
                'description' => '使用堆疊圖表',
                'help' => 'Display stacked graphs instead of inverted graphs',
            ],
            'graph_type' => [
                'description' => '設定圖表類型',
                'help' => '設定預設圖表類型',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => '設定圖表最小高度',
                'help' => '圖表最小高度 (預設: 300)',
            ],
        ],
        'device_location_map_open' => [
            'description' => '開啟位置圖',
            'help' => 'Location Map is shown by default',
        ],
        'force_hostname_to_sysname' => [
            'description' => '將 Hostname 改以 SysName 顯示',
            'help' => 'When using a dynamic DNS hostname or one that does not resolve, this option would allow you to make use of the sysName instead as the preferred reference to the device',
        ],
        'force_ip_to_sysname' => [
            'description' => '將 IP 位址改以 SysName 顯示',
            'help' => 'When using IP addresses as a hostname you can instead represent the devices on the WebUI by its sysName resulting in an easier to read overview of your network. This would apply on networks where you don\'t have DNS records for most of your devices',
        ],
        'whois' => [
            'description' => 'whois 路徑',
        ],
        'smokeping.integration' => [
            'description' => '啟用',
            'help' => 'Enable smokeping integration',
        ],
        'smokeping.dir' => [
            'description' => 'RRD 存放路徑',
            'help' => 'Full path to Smokeping RRDs',
        ],
        'smokeping.pings' => [
            'description' => 'Ping 數量',
            'help' => 'Number of pings configured in Smokeping',
        ],
        'smokeping.url' => [
            'description' => 'Smokeping URL 位址',
            'help' => 'Full URL to the smokeping gui',
        ],
    ],
    'twofactor' => [
        'description' => '啟用雙因素驗證',
        'help' => 'Enables the built in Two-Factor authentication. You must set up each account to make it active.',
    ],
    'units' => [
        'days' => '日',
        'ms' => '微秒',
        'seconds' => '秒',
    ],
    'validate' => [
        'boolean' => ':value is not a valid boolean',
        'email' => ':value is not a valid email',
        'integer' => ':value is not an integer',
        'password' => 'The password is incorrect',
        'select' => ':value is not an allowed value',
        'text' => ':value is not allowed',
        'array' => 'Invalid format',
    ],
];
