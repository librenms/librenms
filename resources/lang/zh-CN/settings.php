<?php

return [
    'readonly' => '在 config.php 里被设定成只读，请由 config.php 移除它来启用。',
    'groups' => [
        'alerting' => '警报',
        'auth' => '验证',
        'external' => '外部整合',
        'global' => '全域',
        'os' => '操作系统',
        'discovery' => '探索',
        'poller' => '轮询器',
        'system' => '系统',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => '一般警报设定',
            'email' => '电子邮件设定',
        ],
        'auth' => [
            'general' => '一般验证设定',
            'ad' => 'Active Directory 设定',
            'ldap' => 'LDAP 设定',
        ],
        'discovery' => [
            'general' => '一般探索设定',
            'route' => '路由探索模块',
        ],
        'external' => [
            'binaries' => '执行文件位置',
            'location' => '位置信息设定',
            'graylog' => 'Graylog 整合',
            'oxidized' => 'Oxidized 整合',
            'peeringdb' => 'PeeringDB 整合',
            'nfsen' => 'NfSen 整合',
            'unix-agent' => 'Unix-Agent 整合',
        ],
        'poller' => [
            'distributed' => '分布式轮询器',
            'ping' => 'Ping',
            'rrdtool' => 'RRDTool 设定',
            'snmp' => 'SNMP',
        ],
        'system' => [
            'cleanup' => '清理',
            'proxy' => 'Proxy',
            'updates' => '更新',
            'server' => '服务器',
        ],
        'webui' => [
            'availability-map' => '可用性地图设定',
            'graph' => '图表设定',
            'dashboard' => '信息广告牌设定',
            'search' => '搜寻设定',
            'style' => '样式',
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => '保留未登入使用者于',
                'help' => '设定使用者超过几天没有登入后，将会被 LibreNMS 自动删除。设为 0 表示不会删除，若使用者重新登入，将会重新建立账户。',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => '新增装置时检察是否 IP 重复',
            'help' => '以 IP 加入主机时，会先检查此 IP 是否已存在于系统上，若有则不予加入。若是以主机名称方式加入时，则不会做此检查。若设定为 True 时，则以主机名称方式加入时亦做此检查，以避免加入重复主机的意外发生。',
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => '预设认可值到警报解除选项',
                'help' => '预设认可值到警报解除',
            ],
            'admins' => [
                'description' => '向管理员发送警报',
                'help' => '管理员警报',
            ],
            'default_copy' => [
                'description' => '复制所有的邮件警报给预设连络人',
                'help' => '复制所有的邮件警报给预设连络人',
            ],
            'default_if_none' => [
                'description' => '无法在 WebUI 设定？',
                'help' => '如果没有找到其它连络人，请把邮件发送到预设连络人',
            ],
            'default_mail' => [
                'description' => '预设连络人',
                'help' => '预设连络人邮件地址',
            ],
            'default_only' => [
                'description' => '只发送警报给预设连络人',
                'help' => '只发送警报给预设邮件连络人',
            ],
            'disable' => [
                'description' => '停用警报',
                'help' => '停止产生警报',
            ],
            'fixed-contacts' => [
                'description' => 'Updates to contact email addresses not honored',
                'help' => 'If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active',
            ],
            'globals' => [
                'description' => '只发送警报给只读使用者',
                'help' => '只发送警报给只读管理员',
            ],
            'syscontact' => [
                'description' => '发送警报给 sysContact',
                'help' => '发送警报邮件给 SNMP 中的 sysContact',
            ],
            'transports' => [
                'mail' => [
                    'description' => '启用邮件警报',
                    'help' => '启用以邮件传输警报',
                ],
            ],
            'tolerance_window' => [
                'description' => 'cron 容错范围',
                'help' => 'Tolerance window in seconds',
            ],
            'users' => [
                'description' => '发送警报给一般使用者',
                'help' => '警报通知一般使用者',
            ],
        ],
        'alert_log_purge' => [
            'description' => '警报记录项目大于',
            'help' => 'Cleanup done by daily.sh',
        ],
        'allow_duplicate_sysName' => [
            'description' => '允许重复 sysName',
            'help' => 'By default duplicate sysNames are disabled from being added to prevent a device with multiple interfaces from being added multiple times',
        ],
        'allow_unauth_graphs' => [
            'description' => '允许未登入存取图表',
            'help' => '允许在不登入情况下存取图表',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '允许指定网络存取图表',
            'help' => '允许指定网络可以在未登入授权查看图表 (若未启用 允许未登入存取图表 则忽略此设定)',
        ],
        'api_demo' => [
            'description' => '这是展示',
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
            'description' => '基础 DN',
            'help' => 'groups and users must be under this dn. Example: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '检查凭证',
            'help' => 'Check certificates for validity. Some servers use self signed certificates, disabling this allows those.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'LDAP 群组筛选器',
            'help' => 'Active Directory LDAP filter for selecting groups',
        ],
        'auth_ad_groups' => [
            'description' => '群组存取权限',
            'help' => '定义群组具有的存取权限与等级',
        ],
        'auth_ad_user_filter' => [
            'description' => 'LDAP 使用者筛选',
            'help' => 'Active Directory LDAP filter for selecting users',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Attribute to check username against',
                'help' => 'Attribute used to identify users by username',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '系结 DN (覆写系结使用者名称)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '系结密码',
            'help' => 'Password for bind user',
        ],
        'auth_ldap_binduser' => [
            'description' => '系结使用者',
            'help' => 'Used to query the LDAP server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => '系结 DN (覆写系结使用者名称)',
            'help' => 'Full DN of bind user',
        ],
        'auth_ad_bindpassword' => [
            'description' => '系结密码',
            'help' => 'Password for bind user',
        ],
        'auth_ad_binduser' => [
            'description' => '系结使用者名称',
            'help' => 'Used to query the AD server when no user is logged in (alerts, API, etc)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP 快取有效期',
            'help' => 'Temporarily stores LDAP query results.  Improves speeds, but the data may be stale.',
        ],
        'auth_ldap_debug' => [
            'description' => '显示侦错信息',
            'help' => 'Shows debug information.  May expose private information, do not leave enabled.',
        ],
        'auth_ldap_emailattr' => [
            'description' => '邮件属性',
        ],
        'auth_ldap_group' => [
            'description' => '存取群组 DN',
            'help' => 'Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '群组基础 DN',
            'help' => 'Distinguished name to search for groups Example: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Group member attribute',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '以下列方式寻找群组成员',
            'options' => [
                'username' => '使用者名称',
                'fulldn' => 'Full DN (using prefix and suffix)',
                'puredn' => 'DN 搜寻 (使用 uid 属性搜寻)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Group access',
            'help' => 'Define groups that have access and level',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP 连接埠',
            'help' => 'Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636',
        ],
        'auth_ldap_prefix' => [
            'description' => '使用者前缀',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP 服务器',
            'help' => 'Set server(s), space separated. Prefix with ldaps:// for ssl',
        ],
        'auth_ldap_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => 'Use STARTTLS to secure the connection.  Alternative to LDAPS.',
            'options' => [
                'disabled' => '停用',
                'optional' => '选用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '使用者后缀',
            'help' => 'Used to turn a username into a distinguished name',
        ],
        'auth_ldap_timeout' => [
            'description' => '联机逾时',
            'help' => 'If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '唯一 ID 属性',
            'help' => 'LDAP attribute to use to identify users, must be numeric',
        ],
        'auth_ldap_userdn' => [
            'description' => '使用全名 DN',
            'help' => "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)",
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP 版本',
            'help' => '用来与 LDAP Server 进行连接的版本，通常应是 v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => '授权方法 (慎选!)',
            'help' => "授权方法。注意，若设定错误将导致您无法登入系统。若真的发生，您可以手动将 config.php 的设定改回 \$config['auth_mechanism'] = 'mysql';",
            'options' => [
                'mysql' => 'MySQL (预设)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP 验证',
                'ad-authorization' => '外部 AD 验证',
                'ldap-authorization' => '外部 LDAP 验证',
                'sso' => '单一签入 SSO',
            ],
        ],
        'auth_remember' => [
            'description' => '记住我的期限',
            'help' => 'Number of days to keep a user logged in when checking the remember me checkbox at log in.',
        ],
        'authlog_purge' => [
            'description' => '验证记录项目大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'base_url' => [
            'description' => '指定 URL',
            'help' => 'This should *only* be set if you want to *force* a particular hostname/port. It will prevent the web interface being usable form any other hostname',
        ],
        'device_perf_purge' => [
            'description' => '装置效能项目大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'distributed_poller' => [
            'description' => '启用分布式轮询 (需要额外设定)',
            'help' => 'Enable distributed polling system wide. This is intended for load sharing, not remote polling. You must read the documentation for steps to enable: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'distributed_poller_group' => [
            'description' => '预设轮询器群组',
            'help' => 'The default poller group all pollers should poll if none is set in config.php',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 主机',
            'help' => 'The hostname or ip for the memcached server. This is required for poller_wrapper.py and daily.sh locking.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 连接埠',
            'help' => 'The port for the memcached server. Default is 11211',
        ],
        'email_auto_tls' => [
            'description' => '启用 / 停用自动 TLS 支持',
            'options' => [
                'true' => '是',
                'false' => '否',
            ],
        ],
        'email_backend' => [
            'description' => '寄送邮件方式',
            'help' => 'The backend to use for sending email, can be mail, sendmail or SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '寄件者信箱地址',
            'help' => 'Email address used for sending emails (from)',
        ],
        'email_html' => [
            'description' => '使用 HTML 格式',
            'help' => '寄送 HTML 格式的邮件',
        ],
        'email_sendmail_path' => [
            'description' => '若启用此选项，sendmail 所在的位置',
        ],
        'email_smtp_auth' => [
            'description' => '启用 / 停用 SMTP 验证',
        ],
        'email_smtp_host' => [
            'description' => '指定寄信用的 SMTP 主机',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP 验证密码',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP 连接埠设定',
        ],
        'email_smtp_secure' => [
            'description' => '启用 / 停用加密 (使用 TLS 或 SSL)',
            'options' => [
                '' => '停用',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP 逾时设定',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP 验证使用者名称',
        ],
        'email_user' => [
            'description' => '寄件者名称',
            'help' => 'Name used as part of the from address',
        ],
        'eventlog_purge' => [
            'description' => '事件记录大于 (天)',
            'help' => '由 daily.sh 进行清理作业',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => '取代预设 Favicon.',
        ],
        'fping' => [
            'description' => 'fping 路径',
        ],
        'fping6' => [
            'description' => 'fping6 路径',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping 次数',
                'help' => 'The number of pings to send when checking if a host is up or down via icmp',
            ],
            'interval' => [
                'description' => 'fping 间隔',
                'help' => 'The amount of milliseconds to wait between pings',
            ],
            'timeout' => [
                'description' => 'fping 逾时',
                'help' => 'The amount of milliseconds to wait for an echo response before giving up',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '地理编码 API 金钥',
                'help' => 'Geocoding API Key (Required to function)',
            ],
            'engine' => [
                'description' => '地理编码引擎',
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
                'help' => 'Override the base uri in the case you have modified the Graylog default.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '装置概观记录等级',
                    'help' => 'Sets the maximum log level shown on the device overview page.',
                ],
                'rowCount' => [
                    'description' => '装置概观数据笔数',
                    'help' => 'Sets the number of rows show on the device overview page.',
                ],
            ],
            'password' => [
                'description' => '密码',
                'help' => 'Password for accessing Graylog API.',
            ],
            'port' => [
                'description' => '连接埠',
                'help' => 'The port used to access the Graylog API. If none give, it will be 80 for http and 443 for https.',
            ],
            'server' => [
                'description' => '服务器',
                'help' => 'The ip or hostname of the Graylog server API endpoint.',
            ],
            'timezone' => [
                'description' => '显示时区',
                'help' => 'Graylog times are stored in GMT, this setting will change the displayed timezone. The value must be a valid PHP timezone.',
            ],
            'username' => [
                'description' => '使用者名称',
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
        'ipmitool' => [
            'description' => 'ipmtool 路径',
        ],
        'login_message' => [
            'description' => '登入讯息',
            'help' => '显示于登入页面',
        ],
        'mono_font' => [
            'description' => 'Monospaced 字型',
        ],
        'mtr' => [
            'description' => 'mtr 路径',
        ],
        'mydomain' => [
            'description' => '主要网域',
            'help' => 'This domain is used for network auto-discovery and other processes. LibreNMS will attempt to append it to unqualified hostnames.',
        ],
        'nfsen_enable' => [
            'description' => '启用 NfSen',
            'help' => '启用 NfSen 整合',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD 目录',
            'help' => 'This value specifies where your NFSen RRD files are located.',
        ],
        'nfsen_subdirlayout' => [
            'description' => '设定 NfSen 子目录配置',
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
            'description' => '分隔字符',
            'help' => 'This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`',
        ],
        'nfsen_suffix' => [
            'description' => '文件名称前缀',
            'help' => 'This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed.',
        ],
        'nmap' => [
            'description' => 'nmap 路径',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 主机名称',
            'help' => 'Should be set to the hostname/ip the librenms server is added as',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Set the default group returned',
            ],
            'enabled' => [
                'description' => '启用 Oxidized 支援',
            ],
            'features' => [
                'versioning' => [
                    'description' => '启用组态版本存取',
                    'help' => 'Enable Oxidized config versioning (requires git backend)',
                ],
            ],
            'group_support' => [
                'description' => 'Enable the return of groups to Oxidized',
            ],
            'reload_nodes' => [
                'description' => '在每次新增装置后，重新加载 Oxidized 节点清单',
            ],
            'url' => [
                'description' => '您的 Oxidized API URL',
                'help' => 'Oxidized API url (For example: http://127.0.0.1:8888)',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => '密码最小长度',
                'help' => 'Passwords shorter than the given length will be rejected',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => '启用 PeeringDB 反查',
                'help' => '起用 PeeringDB lookup (资料将于由 daily.sh 进行下载)',
            ],
        ],
        'ping' => [
            'description' => 'ping 路径',
        ],
        'ports_fdb_purge' => [
            'description' => '连接端口 FDB 项目大于',
            'help' => 'Cleanup done by daily.sh',
        ],
        'ports_purge' => [
            'description' => '连接埠大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'public_status' => [
            'description' => '公开状态显示',
            'help' => '允许不登入的情况下，显示装置的状态信息。',
        ],
        'routes_max_number' => [
            'description' => '允许探索路由的最大路由数',
            'help' => 'No route will be discovered if the size of the routing table is bigger than this number',
        ],
        'route_purge' => [
            'description' => '路由记录大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => '变更 rrd 活动讯号值 (预设 600)',
            ],
            'step' => [
                'description' => '变更 rrd 间距值 (预设 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD 位置',
            'help' => 'Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files.',
        ],
        'rrd_purge' => [
            'description' => 'RRD 档案项目大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式设定',
            'help' => 'These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries.',
        ],
        'rrdcached' => [
            'description' => '启用 rrdcached (socket)',
            'help' => 'Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 路径',
        ],
        'rrdtool_tune' => [
            'description' => '调整所有 rrd 连接埠档案使用最大值',
            'help' => '自动调整 rrd 连接埠档案的最大值',
        ],
        'sfdp' => [
            'description' => 'sfdp 路径',
        ],
        'shorthost_target_length' => [
            'description' => 'Shortened hostname maximum length',
            'help' => 'Shrinks hostname to maximum length, but always complete subdomain parts',
        ],
        'site_style' => [
            'description' => '设定站台 css 样式',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '传输 (优先级)',
                'help' => 'Select enabled transports and order them as you want them to be tried.',
            ],
            'version' => [
                'description' => '版本 (优先级)',
                'help' => 'Select enabled versions and order them as you want them to be tried.',
            ],
            'community' => [
                'description' => '社群 (优先级)',
                'help' => 'Enter community strings for v1 and v2c and order them as you want them to be tried',
            ],
            'max_repeaters' => [
                'description' => '重复撷取最多次数',
                'help' => 'Set repeaters to use for SNMP bulk requests',
            ],
            'port' => [
                'description' => '连接埠',
                'help' => 'Set the tcp/udp port to be used for SNMP',
            ],
            'v3' => [
                'description' => 'SNMP v3 验证 (优先级)',
                'help' => 'Set up v3 authentication variables and order them as you want them to be tried',
                'auth' => '验证',
                'crypto' => '加密',
                'fields' => [
                    'authalgo' => '算法',
                    'authlevel' => '邓级',
                    'authname' => '使用者名称',
                    'authpass' => '密码',
                    'cryptoalgo' => '算法',
                    'cryptopass' => '算法密码',
                ],
                'level' => [
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication and Privacy',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'snmpbulkwalk 路径',
        ],
        'snmpget' => [
            'description' => 'snmpget 路径',
        ],
        'snmpgetnext' => [
            'description' => 'snmpgetnext 路径',
        ],
        'snmptranslate' => [
            'description' => 'snmptranslate 路径',
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk 路径',
        ],
        'syslog_filter' => [
            'description' => 'Filter syslog messages containing',
        ],
        'syslog_purge' => [
            'description' => 'Syslog 项目大于 (天)',
            'help' => 'Cleanup done by daily.sh',
        ],
        'title_image' => [
            'description' => '标题图片',
            'help' => 'Overrides the default Title Image.',
        ],
        'traceroute' => [
            'description' => 'traceroute 路径',
        ],
        'traceroute6' => [
            'description' => 'traceroute6 路径',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent 联机逾时',
            ],
            'port' => [
                'description' => '预设 unix-agent 连接埠',
                'help' => 'unix-agent (check_mk) 预设连接端口号码',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent 读取逾时',
            ],
        ],
        'update' => [
            'description' => '启用更新 ./daily.sh',
        ],
        'update_channel' => [
            'description' => '设定更新频道',
            'options' => [
                'master' => 'master',
                'release' => 'release',
            ],
        ],
        'virsh' => [
            'description' => 'virsh 路径',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => '可用性区块宽度',
                'help' => 'Input desired tile width in pixels for box size in full view',
            ],
            'availability_map_compact' => [
                'description' => '可用性地图精简模式',
                'help' => 'Availability map view with small indicators',
            ],
            'availability_map_sort_status' => [
                'description' => '依状态排序',
                'help' => '以状态做为装置与服务的排序',
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用装置群组筛选器',
                'help' => '启用装置群组筛选器',
            ],
            'default_dashboard_id' => [
                'description' => '预设信息广告牌',
                'help' => '对于没有设定预设信息广告牌的使用者，所要显示的预设信息广告牌',
            ],
            'dynamic_graphs' => [
                'description' => '启用动态群组',
                'help' => 'Enable dynamic graphs, enables zooming and panning on graphs',
            ],
            'global_search_result_limit' => [
                'description' => '设定搜寻结果笔数上限',
                'help' => '全域搜寻结果限制',
            ],
            'graph_stacked' => [
                'description' => '使用堆栈图表',
                'help' => 'Display stacked graphs instead of inverted graphs',
            ],
            'graph_type' => [
                'description' => '设定图表类型',
                'help' => '设定预设图表类型',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => '设定图表最小高度',
                'help' => '图表最小高度 (预设: 300)',
            ],
        ],
        'whois' => [
            'description' => 'whois 路径',
        ],
    ],
    'twofactor' => [
        'description' => '启用双因素验证',
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
