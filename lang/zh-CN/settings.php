<?php

return [
    'title' => '设置',
    'readonly' => '已在 config.php 中设置，从 config.php 移除以启用。',
    'groups' => [
        'alerting' => '警告通知',
        'api' => '应用程序接口',
        'auth' => '认证',
        'authorization' => '授权',
        'external' => '外部',
        'global' => '全局',
        'os' => '操作系统',
        'discovery' => '发现',
        'graphing' => '图形化',
        'poller' => '轮询器',
        'system' => '系统',
        'webui' => '网页用户界面',
    ],
    'sections' => [
        'alerting' => [
            'general' => ['name' => '通用警告设置'],
            'email' => ['name' => '电子邮件选项'],
            'rules' => ['name' => '警告规则默认设置'],
        ],
        'api' => [
            'cors' => ['name' => 'CORS'],
        ],
        'auth' => [
            'general' => ['name' => '通用认证设置'],
            'ad' => ['name' => '活动目录设置'],
            'ldap' => ['name' => 'LDAP 设置'],
            'radius' => ['name' => 'Radius 设置'],
            'socialite' => ['name' => 'Socialite 设置'],
        ],
        'authorization' => [
            'device-group' => ['name' => '设备组设置'],
        ],
        'discovery' => [
            'general' => ['name' => '通用发现设置'],
            'route' => ['name' => '路由发现模块'],
            'discovery_modules' => ['name' => '发现模块'],
            'ports' => ['name' => '端口模块'],
            'storage' => ['name' => '存储模块'],
            'networks' => ['name' => '网络'],
        ],
        'external' => [
            'binaries' => ['name' => '二进制文件位置'],
            'location' => ['name' => '位置设置'],
            'graylog' => ['name' => 'Graylog 集成'],
            'oxidized' => ['name' => 'Oxidized 集成'],
            'mac_oui' => ['name' => 'Mac OUI 查找集成'],
            'peeringdb' => ['name' => 'PeeringDB 集成'],
            'nfsen' => ['name' => 'NfSen 集成'],
            'unix-agent' => ['name' => 'Unix-Agent 集成'],
            'smokeping' => ['name' => 'Smokeping 集成'],
            'snmptrapd' => ['name' => 'SNMP 陷阱集成'],
        ],
        'poller' => [
            'availability' => ['name' => '设备可用性'],
            'distributed' => ['name' => '分布式轮询器'],
            'graphite' => ['name' => '数据存储: Graphite'],
            'influxdb' => ['name' => '数据存储: InfluxDB'],
            'influxdbv2' => ['name' => '数据存储: InfluxDBv2'],
            'opentsdb' => ['name' => '数据存储: OpenTSDB'],
            'ping' => ['name' => 'Ping'],
            'prometheus' => ['name' => '数据存储: Prometheus'],
            'rrdtool' => ['name' => '数据存储: RRDTool'],
            'snmp' => ['name' => 'SNMP'],
            'poller_modules' => ['name' => '轮询器模块'],
        ],
        'system' => [
            'cleanup' => ['name' => '清理'],
            'proxy' => ['name' => '代理'],
            'updates' => ['name' => '更新'],
            'server' => ['name' => '服务器'],
            'reporting' => ['name' => '报告'],
        ],
        'webui' => [
            'availability-map' => ['name' => '可用性地图设置'],
            'graph' => ['name' => '图形设置'],
            'dashboard' => ['name' => '仪表板设置'],
            'port-descr' => ['name' => '接口描述解析'],
            'search' => ['name' => '搜索设置'],
            'style' => ['name' => '样式'],
            'device' => ['name' => '设备设置'],
            'worldmap' => ['name' => '世界地图设置'],
        ],
    ], 
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => '保留未活跃用户的时长',
                'help' => '用户在未登录此天数后将从 LibreNMS 中删除。0 表示永不删除，用户重新登录时会重新创建。如果主机以 IP 地址形式添加，则会检查确保该 IP 地址尚未存在。如果 IP 存在，则不添加主机。如果通过主机名添加，则不执行此检查。如果设置为 true，则解析主机名并同样执行检查。这有助于防止意外重复的主机。'
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => '添加设备时检查重复 IP',
            'help' => '如果作为 IP 地址添加主机，则会检查确保该 IP 地址尚未被占用。如果 IP 已存在，则不添加主机。如果是通过主机名添加，则不进行此检查。如果该设置为真，则解析主机名并同样执行检查。这有助于防止意外添加重复的主机。'
        ],
        'alert_rule' => [
            'acknowledged_alerts' => [
                'description' => '已确认警报',
                'help' => '当警报被确认时发送警报'
            ],
            'severity' => [
                'description' => '严重性',
                'help' => '警报的严重程度'
            ],
            'max_alerts' => [
                'description' => '最大警报数',
                'help' => '要发送的警报计数'
            ],
            'delay' => [
                'description' => '延迟',
                'help' => '发送警报前的延迟时间'
            ],
            'interval' => [
                'description' => '检查间隔',
                'help' => '对此警报进行检查的间隔时间'
            ],
            'mute_alerts' => [
                'description' => '静音警报',
                'help' => '警报是否仅在 WebUI 中可见'
            ],
            'invert_rule_match' => [
                'description' => '反转规则匹配',
                'help' => '仅在规则不匹配时发出警报'
            ],
            'recovery_alerts' => [
                'description' => '恢复警报',
                'help' => '警报恢复时通知'
            ],
            'acknowledgement_alerts' => [
                'description' => '确认警报通知',
                'help' => '警报被确认时通知'
            ],
            'invert_map' => [
                'description' => '列表外的所有设备',
                'help' => '仅对未列出的设备发出警报'
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => '默认确认直到警报清除选项',
                'help' => '默认确认直到警报清除'
            ],
            'admins' => [
                'description' => '向管理员发送警报（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'default_copy' => [
                'description' => '复制所有电子邮件警报给默认联系人（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'default_if_none' => [
                'description' => '无法在 WebUI 中设置？（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'default_mail' => [
                'description' => '默认联系人（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'default_only' => [
                'description' => '仅向默认联系人发送警报（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'disable' => [
                'description' => '禁用警报',
                'help' => '停止生成警报'
            ],
            'acknowledged' => [
                'description' => '发送已确认的警报',
                'help' => '如果警报已被确认则通知'
            ],
            'fixed-contacts' => [
                'description' => '禁止对活动警报更改联系人',
                'help' => '如果为 TRUE，在警报激活期间，对 sysContact 或用户邮箱的任何更改将不予考虑'
            ],
            'globals' => [
                'description' => '向只读用户发送警报（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'syscontact' => [
                'description' => '向 sysContact 发送警报（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
            'transports' => [
                'mail' => [
                    'description' => '启用电子邮件警报',
                    'help' => '邮件警报传输方式'
                ],
            ],
            'tolerance_window' => [
                'description' => 'cron 容忍窗口',
                'help' => '容忍窗口，单位秒'
            ],
            'users' => [
                'description' => '向普通用户发送警报（已弃用）',
                'help' => '已弃用，请改用邮件警报传输方式。'
            ],
        ],
        'alert_log_purge' => [
            'description' => '清除超过时长的警报日志条目',
            'help' => '由 daily.sh 脚本执行的清理操作'
        ],
       'discovery_on_reboot' => [
            'description' => '启动时发现',
            'help' => '在设备重启时进行发现',
        ],
        'allow_duplicate_sysName' => [
            'description' => '允许重复的sysName',
            'help' => '默认情况下，不允许添加重复的sysName，以防止设备因多个接口而多次被添加',
        ],
        'allow_unauth_graphs' => [
            'description' => '允许未验证的图形访问',
            'help' => '允许任何人在无需登录的情况下访问图形',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '允许给定网络的图形访问',
            'help' => '允许给定网络未验证的图形访问（当未验证图形启用时，此设置不适用）',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => '允许的头部',
                    'help' => '设置Access-Control-Allow-Headers响应头',
                ],
                'allowcredentials' => [
                    'description' => '允许凭据',
                    'help' => '设置Access-Control-Allow-Credentials头',
                ],
                'allowmethods' => [
                    'description' => '允许的方法',
                    'help' => '匹配请求方法',
                ],
                'enabled' => [
                    'description' => '启用API的CORS支持',
                    'help' => '允许从Web客户端加载API资源',
                ],
                'exposeheaders' => [
                    'description' => '暴露的头部',
                    'help' => '设置Access-Control-Expose-Headers响应头',
                ],
                'maxage' => [
                    'description' => '最大年龄',
                    'help' => '设置Access-Control-Max-Age响应头',
                ],
                'origin' => [
                    'description' => '允许请求源',
                    'help' => '匹配请求来源。可以使用通配符，例如*.mydomain.com',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'PowerDNS Recursor的API密钥',
                    'help' => '直接连接到PowerDNS Recursor应用时的API密钥',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor使用HTTPS？',
                    'help' => '直接连接到PowerDNS Recursor应用时使用HTTPS而不是HTTP',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor端口',
                    'help' => '直接连接到PowerDNS Recursor应用时使用的TCP端口',
                ],
            ],
        ],
        'astext' => [
            'description' => '用于存储自治系统描述的键',
        ],
        'auth' => [
            'allow_get_login' => [
                'description' => '允许通过GET登录（不安全）',
                'help' => '允许通过在URL的GET请求中放置用户名和密码变量进行登录，适用于无法交互式登录的显示系统。这被认为是不安全的，因为密码会在日志中显示，并且登录不受速率限制，因此可能会使您受到暴力攻击',
            ],
            'socialite' => [
                'redirect' => [
                    'description' => '重定向登录页面',
                    'help' => '登录页面应立即重定向到第一个定义的提供商。<br><br>提示：您可以通过在url后附加?redirect=0来阻止此操作',
                ],
                'register' => [
                    'description' => '允许通过提供商注册',
                ],
                'configs' => [
                    'description' => '提供商配置',
                ],
                'scopes' => [
                    'description' => '应包含在身份验证请求中的范围',
                    'help' => '参见https://laravel.com/docs/10.x/socialite#access-scopes',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => '基础DN',
            'help' => '组和用户必须在此dn下。示例：dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '检查证书',
            'help' => '检查证书的有效性。有些服务器使用自签名证书，禁用此功能可允许这些证书',
        ],
        'auth_ad_debug' => [
            'description' => '调试',
            'help' => '显示详细的错误消息，不要将其启用，因为它可能会泄露数据',
        ],
        'auth_ad_domain' => [
            'description' => '活动目录域',
            'help' => '活动目录域示例：example.com',
        ],
        'auth_ad_group_filter' => [
            'description' => '组LDAP过滤器',
            'help' => '活动目录的组LDAP过滤器',
        ],
        'auth_ad_groups' => [
            'description' => '组访问',
            'help' => '定义具有访问权限和级别的组',
        ],
        'auth_ad_require_groupmembership' => [
            'description' => '要求组成员资格',
            'help' => '仅允许属于定义组的用户登录',
        ],
        'auth_ad_user_filter' => [
            'description' => '用户LDAP过滤器',
            'help' => '活动目录的用户LDAP过滤器',
        ],
        'auth_ad_url' => [
            'description' => '活动目录服务器（s）',
            'help' => '设置服务器（s），用空格分隔。前缀为ldaps://以启用SSL。示例：ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => '检查用户名的属性',
                'help' => '用于识别用户的用户名属性',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '绑定DN（覆盖绑定用户名）',
            'help' => '绑定用户的完整DN',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '绑定密码',
            'help' => '绑定用户的密码',
        ],
        'auth_ldap_binduser' => [
            'description' => '绑定用户名',
            'help' => '在没有用户登录时（警报、API等）用于查询LDAP服务器',
        ],
       'auth_ad_binddn' => [
            'description' => '绑定DN（覆盖绑定用户名）',
            'help' => '完整的绑定用户DN',
        ],
        'auth_ad_bindpassword' => [
            'description' => '绑定密码',
            'help' => '绑定用户的密码',
        ],
        'auth_ad_binduser' => [
            'description' => '绑定用户名',
            'help' => '在没有用户登录时（如警报、API等）用于查询AD服务器',
        ],
        'auth_ad_starttls' => [
            'description' => '使用STARTTLS',
            'help' => '使用STARTTLS来加密连接。这是LDAPS的替代方案。',
            'options' => [
                'disabled' => '禁用',
                'optional' => '可选',
                'required' => '必需',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP缓存过期时间',
            'help' => '暂时存储LDAP查询结果。提高速度，但数据可能过时。',
        ],
        'auth_ldap_debug' => [
            'description' => '显示调试信息',
            'help' => '显示调试信息。可能会暴露私人信息，不要长期开启。',
        ],
        'auth_ldap_cacertfile' => [
            'description' => '覆盖系统TLS CA证书',
            'help' => '为LDAPS使用提供的CA证书。',
        ],
        'auth_ldap_ignorecert' => [
            'description' => '不需要有效的证书',
            'help' => '对LDAPS不要求有效的TLS证书。',
        ],
        'auth_ldap_emailattr' => [
            'description' => '邮件属性',
        ],
        'auth_ldap_group' => [
            'description' => '访问组DN',
            'help' => '赋予正常级别访问的组的唯一名称。例如：cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '组基DN',
            'help' => '搜索组的唯一名称。例如：ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => '组成员属性',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '按以下方式查找组成员',
            'options' => [
                'username' => '用户名',
                'fulldn' => '完整DN（使用前缀和后缀）',
                'puredn' => 'DN搜索（使用uid属性）',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => '组访问',
            'help' => '定义具有访问权限和级别的组',
        ],
        'auth_ldap_require_groupmembership' => [
            'description' => 'LDAP组成员资格验证',
            'help' => '在提供商允许（或不允许）比较操作时执行（或跳过）ldap_compare。',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP端口',
            'help' => '连接到服务器的端口。对于LDAP应为389，对于LDAPS应为636',
        ],
        'auth_ldap_prefix' => [
            'description' => '用户前缀',
            'help' => '用于将用户名转换为唯一名称',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP服务器（s）',
            'help' => '设置服务器（s），空格分隔。以ldaps://开头以启用ssl',
        ],
        'auth_ldap_starttls' => [
            'description' => '使用STARTTLS',
            'help' => '使用STARTTLS来加密连接。这是LDAPS的替代方案。',
            'options' => [
                'disabled' => '禁用',
                'optional' => '可选',
                'required' => '必需',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '用户后缀',
            'help' => '用于将用户名转换为唯一名称',
        ],
        'auth_ldap_timeout' => [
            'description' => '连接超时',
            'help' => '如果一个或多个服务器无响应，较高的超时会导致访问速度变慢。太低可能会在某些情况下导致连接失败',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '唯一ID属性',
            'help' => '用于标识用户的LDAP属性，必须是数字',
        ],
        'auth_ldap_userdn' => [
            'description' => '使用完整用户DN',
            'help' => "使用用户的完整DN作为组中member属性的值，而不是member: username使用前缀和后缀。（即member: uid=username,ou=groups,dc=domain,dc=com）",
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => '通配符用户OU',
            'help' => '根据用户名称独立搜索用户，而不考虑设置在用户后缀中的OU。如果您的用户在不同的OU中，这很有用。如果设置了绑定用户名，仍然使用后缀',
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP版本',
            'help' => '与服务器通信时使用的LDAP版本。通常应为v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => '授权方法（小心！）',
            'help' => "授权方法。小心，您可能会失去登录的能力。您可以通过在config.php中设置\$config['auth_mechanism'] = 'mysql';来将其恢复为mysql。",
            'options' => [
                'mysql' => 'MySQL（默认）',
                'active_directory' => '活动目录',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP身份验证',
                'ad-authorization' => '外部认证的AD',
                'ldap-authorization' => '外部认证的LDAP',
                'sso' => '单点登录',
            ],
        ],
        'auth_remember' => [
            'description' => '记住我时长',
            'help' => '登录时勾选“记住我”后，用户保持登录状态的天数',
        ],
        'authlog_purge' => [
            'description' => '认证日志条目超过',
            'help' => '由daily.sh执行清理',
        ],
        'peering_descr' => [
            'description' => '对等端口类型',
            'help' => '列出描述类型中的端口将在对等端口菜单项下显示。更多信息，请参阅接口描述解析文档。',
        ],
        'transit_descr' => [
            'description' => '传输端口类型',
            'help' => '列出描述类型中的端口将在传输端口菜单项下显示。更多信息，请参阅接口描述解析文档。',
        ],
        'core_descr' => [
            'description' => '核心端口类型',
            'help' => '列出描述类型中的端口将在核心端口菜单项下显示。更多信息，请参阅接口描述解析文档。',
        ],
        'customers_descr' => [
            'description' => '客户端口类型',
            'help' => '列出描述类型中的端口将在客户端口菜单项下显示。更多信息，请参阅接口描述解析文档。',
        ],
        'base_url' => [
            'description' => '特定URL',
            'help' => '只有当你想*强制*特定的主机名/端口时才应设置此选项。这将阻止从任何其他主机名使用Web界面',
        ], 
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'ARP 表',
            ],
            'applications' => [
                'description' => '应用程序',
            ],
            'bgp-peers' => [
                'description' => 'BGP 对等体',
            ],
            'cisco-cbqos' => [
                'description' => '思科 CBQOS',
            ],
            'cisco-cef' => [
                'description' => '思科 CEF',
            ],
            'cisco-mac-accounting' => [
                'description' => '思科 MAC 计费',
            ],
            'cisco-otv' => [
                'description' => '思科 OTV',
            ],
            'cisco-qfp' => [
                'description' => '思科 QFP',
            ],
            'slas' => [
                'description' => '服务级别协议跟踪',
            ],
            'cisco-pw' => [
                'description' => '思科 PW',
            ],
            'cisco-vrf-lite' => [
                'description' => '思科 VRF Lite',
            ],
            'discovery-arp' => [
                'description' => '发现 ARP',
            ],
            'discovery-protocols' => [
                'description' => '发现协议',
            ],
            'entity-physical' => [
                'description' => '实体物理',
            ],
            'entity-state' => [
                'description' => '实体状态',
            ],
            'fdb-table' => [
                'description' => 'FDB 表',
            ],
            'hr-device' => [
                'description' => 'HR 设备',
            ],
            'ipv4-addresses' => [
                'description' => 'IPv4 地址',
            ],
            'ipv6-addresses' => [
                'description' => 'IPv6 地址',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'loadbalancers' => [
                'description' => '负载均衡器',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mempools' => [
                'description' => '内存池',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'os' => [
                'description' => '操作系统',
            ],
            'ports' => [
                'description' => '端口',
            ],
            'ports-stack' => [
                'description' => '端口堆叠',
            ],
            'processors' => [
                'description' => '处理器',
            ],

            'route' => [
                'description' => '路由',
            ],

            'sensors' => [
                'description' => '传感器',
            ],

            'services' => [
                'description' => '服务',
            ],
            'storage' => [
                'description' => '存储',
            ],

            'stp' => [
                'description' => 'STP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD 磁盘 I/O',
            ],
            'vlans' => [
                'description' => 'VLAN',
            ],
            'vminfo' => [
                'description' => '虚拟机信息',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => '无线',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => '打印机耗材',
            ],
        ], 
        'distributed_poller' => [
            'description' => '启用分布式轮询（需要额外设置）',
            'help' => '全局启用分布式轮询系统。这是为了负载共享，而不是远程轮询。您必须阅读文档以了解启用步骤：https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => '默认轮询器组',
            'help' => '如果没有在config.php中设置，所有轮询器应轮询的默认轮询器组',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 主机',
            'help' => 'Memcached 服务器的主机名或 IP。这对于 poller_wrapper.py 和 daily.sh 锁定是必需的。',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 端口',
            'help' => 'Memcached 服务器的端口。默认值为 11211',
        ],
        'email_auto_tls' => [
            'description' => '自动 TLS 支持',
            'help' => '尝试在回退到未加密之前使用 TLS',
        ],
        'email_attach_graphs' => [
            'description' => '附加图表图像',
            'help' => '当触发警报时，这将生成一个图表并将其附加并嵌入电子邮件中。',
        ],
        'email_backend' => [
            'description' => '邮件发送方式',
            'help' => '用于发送电子邮件的后端，可以是 mail、sendmail 或 SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '发件人电子邮件地址',
            'help' => '用于发送电子邮件的电子邮件地址（发件人）',
        ],
        'email_html' => [
            'description' => '使用 HTML 邮件',
            'help' => '发送 HTML 邮件',
        ],
        'email_sendmail_path' => [
            'description' => 'sendmail 可执行文件的路径',
        ],
        'email_smtp_auth' => [
            'description' => 'SMTP 认证',
            'help' => '如果您的 SMTP 服务器需要认证，请启用此功能',
        ],
        'email_smtp_host' => [
            'description' => 'SMTP 服务器',
            'help' => '用于投递邮件的 SMTP 服务器的 IP 或 DNS 名称',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP 认证密码',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP 端口设置',
        ],
        'email_smtp_secure' => [
            'description' => '加密',
            'options' => [
                '' => '禁用',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP 超时设置',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP 认证用户名',
        ],
        'email_user' => [
            'description' => '发件人名称',
            'help' => '作为发件人地址一部分的名称',
        ],
        'eventlog_purge' => [
            'description' => '事件日志条目超过',
            'help' => '由 daily.sh 执行的清理操作',
        ],
        'favicon' => [
            'description' => '图标',
            'help' => '覆盖默认的图标',
        ],
        'fping' => [
            'description' => 'fping 的路径',
        ],
        'fping6' => [
            'description' => 'fping6 的路径',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping 计数',
                'help' => '检查主机是否通过 ICMP 上线或下线时发送的 ping 次数',
            ],
            'interval' => [
                'description' => 'fping 间隔',
                'help' => '两次 ping 之间等待的毫秒数',
            ],
            'timeout' => [
                'description' => 'fping 超时',
                'help' => '等待回显响应超时的毫秒数',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '映射引擎 API 密钥',
                'help' => '地理编码 API 密钥（功能所必需）',
            ],
            'dns' => [
                'description' => '使用 DNS 位置记录',
                'help' => '从 DNS 服务器使用 LOC 记录获取主机名的地理位置',
            ],
            'engine' => [
                'description' => '映射引擎',
                'options' => [
                    'google' => '谷歌地图',
                    'openstreetmap' => '开放街道地图',
                    'mapquest' => 'MapQuest',
                    'bing' => '必应地图',
                ],
            ],
            'latlng' => [
                'description' => '尝试对位置进行地理编码',
                'help' => '在轮询期间尝试通过地理编码 API 查找纬度和经度',
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => '启用',
                'help' => '导出指标到 Graphite',
            ],
            'host' => [
                'description' => '服务器',
                'help' => '将数据发送到的 Graphite 服务器的 IP 或主机名',
            ],
            'port' => [
                'description' => '端口',
                'help' => '连接到 Graphite 服务器时使用的端口',
            ],
            'prefix' => [
                'description' => '前缀（可选）',
                'help' => '将在所有指标的开头添加前缀。必须由点分隔的字母数字字符',
            ],
        ], 
        'graphing' => [
            'availability' => [
                'description' => '持续时间',
                'help' => '计算设备在指定持续时间内的可用性。（持续时间以秒为单位）',
            ],
            'availability_consider_maintenance' => [
                'description' => '计划维护不影响可用性',
                'help' => '禁用因设备处于维护模式而创建故障和降低可用性的功能。',
            ],
        ],
        'graphs' => [
            'port_speed_zoom' => [
                'description' => '端口速度缩放',
                'help' => '使端口图始终以端口速度为最大值，禁用时端口图将缩放到流量。',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => '基础URI',
                'help' => '如果已修改Graylog默认设置，则覆盖基础URI。',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '设备概览日志级别',
                    'help' => '设置设备概览页面上显示的最大日志级别。',
                ],
                'rowCount' => [
                    'description' => '设备概览行数',
                    'help' => '设置设备概览页面上显示的行数。',
                ],
            ],
            'password' => [
                'description' => '密码',
                'help' => '访问Graylog API的密码。',
            ],
            'port' => [
                'description' => '端口',
                'help' => '用于访问Graylog API的端口。如果没有指定，则默认为http的80端口和https的443端口。',
            ],
            'server' => [
                'description' => '服务器',
                'help' => 'Graylog服务器API端点的IP或主机名。',
            ],
            'timezone' => [
                'description' => '显示时区',
                'help' => 'Graylog的时间以GMT存储，此设置将更改显示的时区。该值必须是有效的PHP时区。',
            ],
            'username' => [
                'description' => '用户名',
                'help' => '访问Graylog API的用户名。',
            ],
            'version' => [
                'description' => '版本',
                'help' => '这用于自动为Graylog API创建基础URI。如果修改了API URI的默认值，请将其设置为其他并指定您的基础URI。',
            ],
            'query' => [
                'field' => [
                    'description' => '查询API字段',
                    'help' => '更改默认查询Graylog API的字段。',
                ],
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => '主要下拉链接',
                    'help' => '设置设备下拉菜单中的主要链接。',
                ],
            ],
        ],
        'http_auth_header' => [
            'description' => '包含用户名的字段名称',
            'help' => '可以是ENV或HTTP头字段，如REMOTE_USER，PHP_AUTH_USER或自定义变体',
        ],
        'http_proxy' => [
            'description' => 'HTTP代理',
            'help' => '当http_proxy环境变量不可用时，将其设置为备用选项。',
        ],
        'https_proxy' => [
            'description' => 'HTTPS代理',
            'help' => '当https_proxy环境变量不可用时，将其设置为备用选项。',
        ],
        'ignore_mount' => [
            'description' => '要忽略的挂载点',
            'help' => '不监控这些挂载点的磁盘使用情况',
        ],
        'ignore_mount_network' => [
            'description' => '忽略网络挂载点',
            'help' => '不监控网络挂载点的磁盘使用情况',
        ],
        'ignore_mount_optical' => [
            'description' => '忽略光驱',
            'help' => '不监控光驱的磁盘使用情况',
        ],
        'ignore_mount_removable' => [
            'description' => '忽略可移动驱动器',
            'help' => '不监控可移动设备的磁盘使用情况',
        ],
        'ignore_mount_regexp' => [
            'description' => '匹配正则表达式以忽略的挂载点',
            'help' => '不监控至少匹配其中一个正则表达式的挂载点的磁盘使用情况',
        ],
        'ignore_mount_string' => [
            'description' => '包含字符串以忽略的挂载点',
            'help' => '不监控包含至少其中一个字符串的挂载点的磁盘使用情况',
        ],
        'influxdb' => [
            'db' => [
                'description' => '数据库',
                'help' => '用于存储指标的InfluxDB数据库名称',
            ],
            'enable' => [
                'description' => '启用',
                'help' => '导出指标到InfluxDB',
            ],
            'host' => [
                'description' => '服务器',
                'help' => '向其发送数据的InfluxDB服务器的IP或主机名',
            ],
            'password' => [
                'description' => '密码',
                'help' => '连接InfluxDB所需的密码（如果需要）',
            ],
            'port' => [
                'description' => '端口',
                'help' => '连接到InfluxDB服务器时使用的端口',
            ],
            'timeout' => [
                'description' => '超时',
                'help' => '等待InfluxDB服务器的时间，0表示默认超时',
            ],
            'transport' => [
                'description' => '传输方式',
                'help' => '连接到InfluxDB服务器时使用的端口',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDP',
                ],
            ],
            'username' => [
                'description' => '用户名',
                'help' => '连接InfluxDB所需的用户名（如果需要）',
            ],
            'verifySSL' => [
                'description' => '验证SSL',
                'help' => '验证SSL证书是否有效且受信任',
            ],
        ],
        'influxdbv2' => [
            'bucket' => [
                'description' => '存储桶',
                'help' => '存储指标的InfluxDB v2存储桶名称',
            ],
            'enable' => [
                'description' => '启用',
                'help' => '使用InfluxDB v2 API导出指标',
            ],
            'host' => [
                'description' => '服务器',
                'help' => '发送数据到的InfluxDB服务器的IP或主机名',
            ],
            'token' => [
                'description' => '令牌',
                'help' => '连接InfluxDB所需的令牌（如果需要）',
            ],
            'port' => [
                'description' => '端口',
                'help' => '连接到InfluxDB服务器时使用的端口',
            ],
            'transport' => [
                'description' => '传输方式',
                'help' => '连接到InfluxDB服务器时使用的协议',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                ],
            ],
            'organization' => [
                'description' => '组织',
                'help' => 'InfluxDB服务器上包含存储桶的组织',
            ],
            'allow_redirects' => [
                'description' => '允许重定向',
                'help' => '允许从InfluxDB服务器重定向',
            ],
        ],
        'ipmitool' => [
            'description' => 'ipmitool路径',
        ],
        'login_message' => [
            'description' => '登录消息',
            'help' => '显示在登录页面上',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => '启用MAC OUI查询',
                'help' => '启用MAC地址厂商（OUI）查询（数据通过daily.sh下载）',
            ],
        ],
        'mono_font' => [
            'description' => '等宽字体',
        ],
        'mtr' => [
            'description' => 'mtr路径',
        ],
        'mydomain' => [
            'description' => '主域名',
            'help' => '此域名用于网络自动发现和其他进程。LibreNMS会尝试将其附加到不合格的主机名后。',
        ],
        'network_map_show_on_worldmap' => [
            'description' => '在地图上显示网络链接',
            'help' => '在世界地图上显示不同位置之间的网络链接（类似weathermap）',
        ],
        'nfsen_enable' => [
            'description' => '启用NfSen',
            'help' => '启用与NfSen的集成',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD目录',
            'help' => '指定您的NfSen RRD文件存放的位置。',
        ],
        'nfsen_subdirlayout' => [
            'description' => '设置NfSen子目录布局',
            'help' => '这必须与您在NfSen中设置的子目录布局相匹配。默认为1。',
        ],
        'nfsen_last_max' => [
            'description' => '最后最大值',
        ],
        'nfsen_top_max' => [
            'description' => '顶级最大值',
            'help' => '统计信息的最大topN值',
        ],
        'nfsen_top_N' => [
            'description' => '顶级N',
        ],
        'nfsen_top_default' => [
            'description' => '默认顶级',
        ],
        'nfsen_stat_default' => [
            'description' => '默认统计',
        ],
        'nfsen_order_default' => [
            'description' => '默认排序',
        ],
        'nfsen_last_default' => [
            'description' => '默认最后',
        ],
        'nfsen_lasts' => [
            'description' => '默认最后选项',
        ],
        'nfsen_split_char' => [
            'description' => '分割字符',
            'help' => '此值告诉我们要替换设备主机名中的句点`.`为什么字符。通常是：`_`',
        ],
        'nfsen_suffix' => [
            'description' => '文件名后缀',
            'help' => '这是非常重要的部分，因为在NfSen中设备名称被限制为21个字符。这意味着设备的完全域名可能很难压缩进去，因此通常会移除这一部分。',
        ],
        'nmap' => [
            'description' => 'nmap路径',
        ],
        'no_proxy' => [
            'description' => '代理例外',
            'help' => '如果no_proxy环境变量不可用，设置此选项作为备选。逗号分隔的IP、主机或不适用代理的域名列表。',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => '启用',
                'help' => '向OpenTSDB导出指标',
            ],
            'host' => [
                'description' => '服务器',
                'help' => '发送数据到的OpenTSDB服务器的IP或主机名',
            ],
            'port' => [
                'description' => '端口',
                'help' => '连接到OpenTSDB服务器时使用的端口',
            ],
        ],
        'own_hostname' => [
            'description' => 'LibreNMS主机名',
            'help' => '应设置为librenms服务器被添加为的主机名/IP',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => '设置默认返回的组',
            ],
            'ignore_groups' => [
                'description' => '不备份这些Oxidized组',
                'help' => '通过变量映射设置的，排除发送到Oxidized的组',
            ],
            'enabled' => [
                'description' => '启用Oxidized支持',
            ],
            'features' => [
                'versioning' => [
                    'description' => '启用配置版本控制访问',
                    'help' => '启用Oxidized配置版本控制（需要git后端）',
                ],
            ],
            'group_support' => [
                'description' => '启用向Oxidized返回组的功能',
            ],
            'ignore_os' => [
                'description' => '不备份这些操作系统',
                'help' => '不要使用Oxidized备份列出的操作系统。操作系统名称必须与LibreNMS的操作系统名称匹配（全部小写，无空格）。仅允许现有操作系统。',
            ],
            'ignore_types' => [
                'description' => '不备份这些设备类型',
                'help' => '不要使用Oxidized备份列出的设备类型。仅允许现有类型。',
            ],
            'reload_nodes' => [
                'description' => '每次添加设备时，重新加载Oxidized节点列表',
            ],
            'maps' => [
                'description' => '变量映射',
                'help' => '用于设置组或其他变量，或映射不同的操作系统名称。',
            ],
            'url' => [
                'description' => '您的Oxidized API的URL',
                'help' => 'Oxidized API的URL（例如：http://127.0.0.1:8888）',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => '密码最小长度',
                'help' => '短于给定长度的密码将被拒绝',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => '启用PeeringDB查询',
                'help' => '启用PeeringDB查询（数据通过daily.sh下载）',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => '启用用户通过动态设备组的访问权限',
                ],
            ],
        ],
        'bad_if' => [
            'description' => '不良接口名称',
            'help' => '应忽略的网络接口IF-MIB:!:ifName',
        ],
        'bad_if_regexp' => [
            'description' => '不良接口名称正则表达式',
            'help' => '使用正则表达式应忽略的网络接口IF-MIB:!:ifName',
        ],
        'bad_ifoperstatus' => [
            'description' => '不良接口运行状态',
            'help' => '应忽略的网络接口IF-MIB:!:ifOperStatus',
        ],
        'bad_iftype' => [
            'description' => '不良接口类型',
            'help' => '应忽略的网络接口IF-MIB:!:ifType',
        ],
        'ping' => [
            'description' => 'ping命令的路径',
        ],
        'ping_rrd_step' => [
            'description' => 'Ping频率',
            'help' => '检查间隔。设置所有节点的默认值。警告！如果您更改此设置，还必须进行其他更改。请参阅快速Ping文档。',
        ],
        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Unix代理',
            ],
            'os' => [
                'description' => '操作系统',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'sensors' => [
                'description' => '传感器',
            ],
            'processors' => [
                'description' => '处理器',
            ],
            'mempools' => [
                'description' => '内存池',
            ],
            'storage' => [
                'description' => '存储',
            ],
            'netstats' => [
                'description' => '网络统计',
            ],
            'hr-mib' => [
                'description' => 'HR MIB',
            ],
            'ucd-mib' => [
                'description' => 'UCD MIB',
            ],
            'ipSystemStats' => [
                'description' => 'ipSystemStats',
            ],
            'ports' => [
                'description' => '端口',
            ],
            'bgp-peers' => [
                'description' => 'BGP对等体',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wireless' => [
                'description' => '无线',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => '思科 IPSec流量监控',
            ],
            'cisco-remote-access-monitor' => [
                'description' => '思科远程访问监控',
            ],
            'cisco-cef' => [
                'description' => '思科 CEF',
            ],
            'slas' => [
                'description' => '服务级别协议跟踪',
            ],
            'cisco-mac-accounting' => [
                'description' => '思科 MAC计费',
            ],
            'cipsec-tunnels' => [
                'description' => 'Cipsec隧道',
            ],
            'cisco-ace-loadbalancer' => [
                'description' => '思科 ACE负载均衡器',
            ],
            'cisco-ace-serverfarms' => [
                'description' => '思科 ACE服务器场',
            ],
            'cisco-asa-firewall' => [
                'description' => '思科 ASA防火墙',
            ],
            'cisco-voice' => [
                'description' => '思科语音',
            ],
            'cisco-cbqos' => [
                'description' => '思科 CBQOS',
            ],
            'cisco-otv' => [
                'description' => '思科 OTV',
            ],
            'cisco-qfp' => [
                'description' => '思科 QFP',
            ],
            'cisco-vpdn' => [
                'description' => '思科 VPDN',
            ],
            'nac' => [
                'description' => '网络接入控制（NAC）',
            ],
            'netscaler-vsvr' => [
                'description' => 'Netscaler 虚拟服务器（VSVR）',
            ],
            'aruba-controller' => [
                'description' => 'Aruba 控制器',
            ],
            'availability' => [
                'description' => '可用性',
            ],
            'entity-physical' => [
                'description' => '实体物理信息',
            ],
            'entity-state' => [
                'description' => '实体状态',
            ],
            'applications' => [
                'description' => '应用程序',
            ],
            'stp' => [
                'description' => '生成树协议（STP）',
            ],
            'vminfo' => [
                'description' => '虚拟机信息（Hypervisor VM）',
            ],
            'ntp' => [
                'description' => '网络时间协议（NTP）',
            ],
            'loadbalancers' => [
                'description' => '负载均衡器',
            ],
            'mef' => [
                'description' => '多业务交换（MEF）',
            ],
            'mpls' => [
                'description' => '多协议标签交换（MPLS）',
            ],
            'xdsl' => [
                'description' => 'xDSL（数字用户线路）',
            ],
            'printer-supplies' => [
                'description' => '打印机耗材',
            ],
        ],
        'ports_fdb_purge' => [
            'description' => '删除超过指定时间的端口FDB条目',
            'help' => '由daily.sh执行清理',
        ],
        'ports_nac_purge' => [
            'description' => '删除超过指定时间的端口NAC条目',
            'help' => '由daily.sh执行清理',
        ],
        'ports_purge' => [
            'description' => '删除已删除的端口',
            'help' => '由daily.sh执行清理',
        ],
        'prometheus' => [
            'enable' => [
                'description' => '启用',
                'help' => '将指标导出到Prometheus Push Gateway',
            ],
            'url' => [
                'description' => 'URL',
                'help' => 'Prometheus Push Gateway的数据发送地址',
            ],
            'Job' => [
                'description' => '作业',
                'help' => '导出指标的作业标签',
            ],
            'attach_sysname' => [
                'description' => '附加设备sysName',
                'help' => '将sysName信息附加到Prometheus中',
            ],
            'prefix' => [
                'description' => '前缀',
                'help' => '可选，用于导出指标名称的前缀文本',
            ],
        ],
        'public_status' => [
            'description' => '公开显示状态',
            'help' => '在登录页面上无需认证即显示部分设备的状态',
        ],
        'routes_max_number' => [
            'description' => '允许发现的最大路由数量',
            'help' => '如果路由表大小超过此数字，则不会发现任何路由',
        ],
        'default_port_group' => [
            'description' => '默认端口组',
            'help' => '新发现的端口将被分配到此端口组',
        ],
        'nets' => [
            'description' => '自动发现网络',
            'help' => '将自动发现这些网络中的设备',
        ],
        'autodiscovery' => [
            'nets-exclude' => [
                'description' => '要忽略的网络/IP',
                'help' => '将不会自动发现这些网络/IP。也排除自动生成发现网络中的IP',
            ],
        ],
        'radius' => [
            'default_roles' => [
                'description' => '默认用户角色',
                'help' => '除非Radius发送指定角色的属性，否则将为用户分配这些角色',
            ],
            'enforce_roles' => [
                'description' => '登录时强制角色',
                'help' => '如果启用，在登录时将角色设置为Filter-ID属性或radius.default_roles指定的角色。否则，在创建用户时设置，之后不再更改',
            ],
        ],
        'reporting' => [
            'error' => [
                'description' => '发送错误报告',
                'help' => '将某些错误发送给LibreNMS以进行分析和修复',
            ],
            'usage' => [
                'description' => '发送使用情况报告',
                'help' => '报告使用情况和版本信息给LibreNMS。要删除匿名统计，请访问关于页面。您可以在https://stats.librenms.org查看统计信息',
            ],
            'dump_errors' => [
                'description' => '转储调试错误（可能破坏安装）',
                'help' => '转储通常隐藏的错误，以便开发者查找并解决问题',
            ],
            'throttle' => [
                'description' => '限制错误报告频率',
                'help' => '报告将在指定秒数后发送。如果没有这个，如果公共代码中有错误，报告可能会失控。设置为0以禁用限制',
            ],
        ],
        'route_purge' => [
            'description' => '删除超过指定时间的路由条目',
            'help' => '由daily.sh执行清理',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => '更改rrd心跳值（默认600）',
            ],
            'step' => [
                'description' => '更改rrd步长值（默认300）',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD 文件位置',
            'help' => 'RRD 文件的位置。默认是在 LibreNMS 目录内的 rrd 子目录。更改此设置不会移动 RRD 文件。',
        ],
        'rrd_purge' => [
            'description' => '超过 RRD 文件条目的年龄',
            'help' => '由 daily.sh 执行的清理操作',
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式设置',
            'help' => '在不删除现有 RRD 文件的情况下，这些设置无法更改。尽管如果遇到性能问题或拥有无性能担忧的高速 I/O 系统，理论上可以增加或减少每个 RRA 的大小。',
        ],
        'rrdcached' => [
            'description' => '启用 rrdcached（套接字）',
            'help' => '通过设置 rrdcached 套接字的位置来启用 rrdcached。可以是 Unix 或网络套接字（unix:/run/rrdcached.sock 或 localhost:42217）',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 的路径',
        ],
        'rrdtool_tune' => [
            'description' => '调整所有 rrd 端口文件以使用最大值',
            'help' => '自动调整 rrd 端口文件的最大值',
        ],
        'rrdtool_version' => [
            'description' => '设置服务器上的 rrdtool 版本',
            'help' => '高于 1.5.5 的版本支持 LibreNMS 使用的所有功能，但不应设置高于已安装版本的值',
        ],
        'service_poller_enabled' => [
            'description' => '启用轮询',
            'help' => '启用轮询工作者。为所有节点设置默认值。',
        ],
        'service_master_timeout' => [
            'description' => '主调度器超时',
            'help' => '主锁过期前的时间。如果主节点消失，其他节点需要等待这么多时间才能接管。但是，如果派发工作所需时间超过超时时间，就会出现多个主节点',
        ],
        'service_poller_workers' => [
            'description' => '轮询工作者数量',
            'help' => '要启动的轮询工作者数量。为所有节点设置默认值。',
        ],
        'service_poller_frequency' => [
            'description' => '轮询频率（警告！）',
            'help' => '设备轮询的频率。为所有节点设置默认值。警告！在不修复 rrd 文件的情况下更改此设置将破坏图表。请参阅文档获取更多信息。',
        ],
        'service_poller_down_retry' => [
            'description' => '设备下线重试',
            'help' => '当尝试轮询时设备下线，这是在重试之前等待的时间。为所有节点设置默认值。',
        ],
        'service_discovery_enabled' => [
            'description' => '发现功能已启用',
            'help' => '启用发现工作者。为所有节点设置默认值。',
        ],
        'service_discovery_workers' => [
            'description' => '发现工作者数量',
            'help' => '要运行的发现工作者数量。设置过高可能导致过载。为所有节点设置默认值。',
        ],
        'service_discovery_frequency' => [
            'description' => '发现频率',
            'help' => '执行设备发现的频率。为所有节点设置默认值。默认每天四次。',
        ],
        'service_services_enabled' => [
            'description' => '服务已启用',
            'help' => '启用服务工作者。为所有节点设置默认值。',
        ],
        'service_services_workers' => [
            'description' => '服务工作者数量',
            'help' => '服务工作者的数量。为所有节点设置默认值。',
        ],
        'service_services_frequency' => [
            'description' => '服务频率',
            'help' => '执行服务的频率。应与轮询频率匹配。为所有节点设置默认值。',
        ],
        'service_billing_enabled' => [
            'description' => '计费功能已启用',
            'help' => '启用计费工作者。为所有节点设置默认值。',
        ],
        'service_billing_frequency' => [
            'description' => '计费频率',
            'help' => '收集计费数据的频率。为所有节点设置默认值。',
        ],
        'service_billing_calculate_frequency' => [
            'description' => '计费计算频率',
            'help' => '计算账单使用的频率。为所有节点设置默认值。',
        ],
        'service_alerting_enabled' => [
            'description' => '警报功能已启用',
            'help' => '启用警报工作者。为所有节点设置默认值。',
        ],
        'service_alerting_frequency' => [
            'description' => '警报频率',
            'help' => '检查警报规则的频率。请注意，数据仅根据轮询频率更新。为所有节点设置默认值。',
        ],
        'service_ping_enabled' => [
            'description' => '快速 Ping 功能已启用',
            'help' => '快速 Ping 仅用于 ping 设备以检查其是否在线或离线。为所有节点设置默认值。',
        ],
        'service_update_enabled' => [
            'description' => '每日维护已启用',
            'help' => '运行 daily.sh 维护脚本并在之后重启调度服务。为所有节点设置默认值。',
        ], 
        'service_update_frequency' => [
            'description' => '维护频率',
            'help' => '每日维护运行的频率，默认为1天。强烈建议不要更改此设置。为所有节点设置默认值。',
        ],
        'service_loglevel' => [
            'description' => '日志级别',
            'help' => '调度服务的日志级别。为所有节点设置默认值。',
        ],
        'service_watchdog_enabled' => [
            'description' => '监控器启用',
            'help' => '监控器监视日志文件并在服务未更新时重新启动服务。为所有节点设置默认值。',
        ],
        'service_watchdog_log' => [
            'description' => '监视的日志文件',
            'help' => '默认为LibreNMS日志文件。为所有节点设置默认值。',
        ],
        'sfdp' => [
            'description' => 'sfdp路径',
        ],
        'shorthost_target_length' => [
            'description' => '缩短主机名的最大长度',
            'help' => '将主机名缩短到最大长度，但始终完整保留子域名部分',
        ],
        'site_style' => [
            'description' => '默认主题',
            'options' => [
                'blue' => '蓝色',
                'dark' => '深色',
                'light' => '浅色',
                'mono' => '单色',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '传输方式（优先级）',
                'help' => '选择启用的传输方式并按您希望尝试的顺序排列它们。',
            ],
            'version' => [
                'description' => '版本（优先级）',
                'help' => '选择启用的版本并按您希望尝试的顺序排列它们。',
            ],
            'community' => [
                'description' => '社区字符串（优先级）',
                'help' => '输入v1和v2c的社区字符串并按您希望尝试的顺序排列它们',
            ],
            'max_oid' => [
                'description' => '最大OID数',
                'help' => '每个查询的最大OID数。可以在操作系统和设备级别覆盖此设置。',
            ],
            'max_repeaters' => [
                'description' => '最大重复器',
                'help' => '设置SNMP批量请求要使用的重复器数量',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => '禁用OID的SNMP批量操作',
                    'help' => '禁用某些OID的SNMP批量操作。通常，应在操作系统上设置此选项。格式应为MIB::OID',
                ],
                'unordered' => [
                    'description' => '允许OID响应无序',
                    'help' => '忽略某些OID在SNMP响应中的无序OID。无序的OID可能会导致SNMPwalk时的oid循环。通常，应在操作系统上设置此选项。格式应为MIB::OID',
                ],
            ],
            'port' => [
                'description' => '端口',
                'help' => '设置用于SNMP的tcp/udp端口',
            ],
            'timeout' => [
                'description' => '超时',
                'help' => 'SNMP超时（秒）',
            ],
            'retries' => [
                'description' => '重试次数',
                'help' => '查询重试次数',
            ],
            'v3' => [
                'description' => 'SNMP v3 认证（优先级）',
                'help' => '设置v3认证变量并按您希望尝试的顺序排列它们',
                'auth' => '认证',
                'crypto' => '加密',
                'fields' => [
                    'authalgo' => '算法',
                    'authlevel' => '级别',
                    'authname' => '用户名',
                    'authpass' => '密码',
                    'cryptoalgo' => '算法',
                    'cryptopass' => '密码',
                ],
                'level' => [
                    'noAuthNoPriv' => '无认证，无隐私',
                    'authNoPriv' => '认证，无隐私',
                    'authPriv' => '认证和隐私',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'snmpbulkwalk路径',
        ],
        'snmpget' => [
            'description' => 'snmpget路径',
        ],
        'snmpgetnext' => [
            'description' => 'snmpgetnext路径',
        ],
        'snmptranslate' => [
            'description' => 'snmptranslate路径',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => '创建snmptraps事件日志',
                'help' => '独立于可能映射到陷阱的操作',
            ],
            'eventlog_detailed' => [
                'description' => '启用详细日志',
                'help' => '在事件日志中添加接收到的所有OID',
            ],
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk路径',
        ],
        'syslog_filter' => [
            'description' => '过滤包含以下内容的syslog消息',
        ],
        'syslog_purge' => [
            'description' => '清理syslog条目的时间超过',
            'help' => '由daily.sh执行清理',
        ], 
        'title_image' => [
            'description' => '标题图片',
            'help' => '覆盖默认的标题图片。',
        ],
        'traceroute' => [
            'description' => '到traceroute的路径',
        ],
        'twofactor' => [
            'description' => '双因素认证',
            'help' => '允许用户激活并使用基于时间（TOTP）或基于计数器（HOTP）的一次性密码（OTP）',
        ],
        'twofactor_lock' => [
            'description' => '双因素认证锁定时间（秒）',
            'help' => '如果连续三次双因素认证失败，等待此秒数后再允许进一步尝试 - 将提示用户等待这么长时间。设置为0以禁用，导致永久账户锁定，并向用户显示联系管理员的消息',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent连接超时',
            ],
            'port' => [
                'description' => 'Unix-agent默认端口',
                'help' => 'Unix-agent（check_mk）的默认端口',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent读取超时',
            ],
        ],
        'update' => [
            'description' => '在./daily.sh中启用更新',
        ],
        'update_channel' => [
            'description' => '更新通道',
            'options' => [
                'master' => '每日',
                'release' => '每月',
            ],
        ],
        'uptime_warning' => [
            'description' => '如果运行时间低于（秒）则显示设备警告',
            'help' => '如果运行时间低于此值，则显示设备警告。默认24小时',
        ],
        'virsh' => [
            'description' => 'virsh的路径',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => '可用性框宽度',
                'help' => '输入期望的像素宽度作为全视图中的框大小',
            ],
            'availability_map_compact' => [
                'description' => '紧凑型可用性地图视图',
                'help' => '带有小指示器的可用性地图视图',
            ],
            'availability_map_sort_status' => [
                'description' => '按状态排序',
                'help' => '按状态对设备和服务进行排序',
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用设备组过滤器',
                'help' => '启用设备组过滤器的使用',
            ],
            'default_dashboard_id' => [
                'description' => '默认仪表板',
                'help' => '全局默认仪表板ID，适用于未自己设置默认值的所有用户',
            ],
            'dynamic_graphs' => [
                'description' => '启用动态图表',
                'help' => '启用动态图表，允许在图表上缩放和平移',
            ],
            'global_search_result_limit' => [
                'description' => '设置最大搜索结果限制',
                'help' => '全局搜索结果限制',
            ],
            'graph_stacked' => [
                'description' => '使用堆叠图表',
                'help' => '显示堆叠图表而非倒置图表',
            ],
            'graph_type' => [
                'description' => '设置图表类型',
                'help' => '设置默认图表类型',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => '设置最小图表高度',
                'help' => '最小图表高度（默认：300）',
            ],
            'graph_stat_percentile_disable' => [
                'description' => '全局禁用统计图表的百分位数',
                'help' => '禁用显示图表上的百分位数值和线，如果有的话',
            ],
        ],
        'device_display_default' => [
            'description' => '默认设备显示名称模板',
            'help' => '为所有设备设置默认显示名称（每台设备可单独覆盖）。主机名/IP：仅显示添加设备时使用的主机名或IP。sysName：仅显示snmp中的sysName。主机名或sysName：显示主机名，但如果它是IP，则显示sysName。',
            'options' => [
                'hostname' => '主机名 / IP',
                'sysName_fallback' => '主机名，IP时回退到sysName',
                'sysName' => 'sysName',
                'ip' => 'IP（来自主机名IP或解析）',
            ],
        ],
        'device_location_map_open' => [
            'description' => '位置地图打开',
            'help' => '默认显示位置地图',
        ],
        'whois' => [
            'description' => 'whois的路径',
        ],
        'smokeping.integration' => [
            'description' => '启用',
            'help' => '启用smokeping集成',
        ],
        'smokeping.dir' => [
            'description' => 'rrds的路径',
            'help' => '到Smokeping RRDs的完整路径',
        ],
        'smokeping.pings' => [
            'description' => 'Ping次数',
            'help' => '在Smokeping中配置的Ping次数',
        ],
        'smokeping.url' => [
            'description' => '到smokeping的URL',
            'help' => '到smokeping GUI的完整URL',
        ],
    ],
    'twofactor' => [
        'description' => '启用双因素认证',
        'help' => '启用内置的双因素认证。您必须为每个账户设置使其生效。',
    ],
    'units' => [
        'days' => '天',
        'ms' => '毫秒',
        'seconds' => '秒',
    ],
    'validate' => [
        'boolean' => ':value不是一个有效的布尔值',
        'color' => ':value不是一个有效的十六进制颜色代码',
        'email' => ':value不是一个有效的电子邮件地址',
        'float' => ':value不是一个浮点数',
        'integer' => ':value不是一个整数',
        'password' => '密码不正确',
        'select' => ':value不是一个允许的值',
        'text' => ':value不允许',
        'array' => '无效格式',
        'executable' => ':value不是一个有效的可执行文件',
        'directory' => ':value不是一个有效的目录',
    ],
];
