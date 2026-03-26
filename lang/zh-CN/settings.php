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
            'general' => ['name' => '一般警报设定'],
            'email' => ['name' => '电子邮件设定'],
        ],
        'auth' => [
            'general' => ['name' => '一般验证设定'],
            'ad' => ['name' => 'Active Directory 设定'],
            'ldap' => ['name' => 'LDAP 设定'],
        ],
        'discovery' => [
            'general' => ['name' => '一般探索设定'],
            'route' => ['name' => '路由探索模块'],
        ],
        'external' => [
            'binaries' => ['name' => '执行文件位置'],
            'location' => ['name' => '位置信息设定'],
            'graylog' => ['name' => 'Graylog 整合'],
            'oxidized' => ['name' => 'Oxidized 整合'],
            'peeringdb' => ['name' => 'PeeringDB 整合'],
            'nfsen' => ['name' => 'NfSen 整合'],
            'unix-agent' => ['name' => 'Unix-Agent 整合'],
        ],
        'poller' => [
            'distributed' => ['name' => '分布式轮询器'],
            'ping' => ['name' => 'Ping'],
            'rrdtool' => ['name' => 'RRDTool 设定'],
            'snmp' => ['name' => 'SNMP'],
        ],
        'system' => [
            'cleanup' => ['name' => '清理'],
            'proxy' => ['name' => '代理'],
            'updates' => ['name' => '更新'],
            'server' => ['name' => '服务器'],
        ],
        'webui' => [
            'availability-map' => ['name' => '可用性地图设定'],
            'graph' => ['name' => '图表设定'],
            'dashboard' => ['name' => '信息广告牌设定'],
            'search' => ['name' => '搜寻设定'],
            'style' => ['name' => '样式'],
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
            'description' => '新增设备时检察是否 IP 重复',
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
                'description' => '更新联系电子邮件地址未得到认可',
                'help' => '如果设为TRUE，任何对sysContact或用户电子邮件的更改在警报激活期间将不被采纳。',
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
            'help' => '默认情况下，禁止添加重复的sysName，以防止具有多个接口的设备被多次添加',
        ],
        'allow_unauth_graphs' => [
            'description' => '允许未登入存取图表',
            'help' => '允许在不登入情况下存取图表',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '允许指定网络存取图表',
            'help' => '允许指定网络可以在未登入授权查看图表 (若未启用 允许未登入存取图表 则忽略此设定)',
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'PowerDNS 解析器的 API 密钥',
                    'help' => '直接连接时，PowerDNS 解析器应用的 API 密钥',
                ],
                'https' => [
                    'description' => 'PowerDNS 解析器是否使用 HTTPS？',
                    'help' => '直接连接时，对于 PowerDNS 解析器应用，是否使用 HTTPS 而非 HTTP',
                ],
                'port' => [
                    'description' => 'PowerDNS 解析器端口',
                    'help' => '直接连接时，用于 PowerDNS 解析器应用的 TCP 端口',
                ],
            ],
        ],
        'astext' => [
            'description' => '用于存储自治系统描述的缓存的密钥',
        ],
        'auth_ad_base_dn' => [
            'description' => '基础 DN',
            'help' => '组和用户必须位于此 DN 下。例如：dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '检查凭证',
            'help' => '检查证书的有效性。一些服务器使用自签名证书，禁用此选项可允许此类证书。',
        ],
        'auth_ad_group_filter' => [
            'description' => 'LDAP 群组筛选器',
            'help' => '用于选择组的 Active Directory LDAP 过滤器',
        ],
        'auth_ad_groups' => [
            'description' => '群组存取权限',
            'help' => '定义群组具有的存取权限与等级',
        ],
        'auth_ad_user_filter' => [
            'description' => 'LDAP 使用者筛选',
            'help' => '用于选择用户的 Active Directory LDAP 过滤器',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => '用于核对用户名的属性',
                'help' => '用于通过用户名标识用户的属性',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '系结 DN (覆写系结使用者名称)',
            'help' => '绑定用户的完整 DN',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '系结密码',
            'help' => '绑定用户的密码',
        ],
        'auth_ldap_binduser' => [
            'description' => '系结使用者',
            'help' => '当没有用户登录时（如警报、API等），用于查询LDAP服务器',
        ],
        'auth_ad_binddn' => [
            'description' => '系结 DN (覆写系结使用者名称)',
            'help' => '绑定用户的完整DN',
        ],
        'auth_ad_bindpassword' => [
            'description' => '系结密码',
            'help' => '绑定用户的密码',
        ],
        'auth_ad_binduser' => [
            'description' => '系结使用者名称',
            'help' => '当没有用户登录时（例如，警报、API等），用于查询AD服务器',
        ],
        'auth_ad_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => '使用STARTTLS来加密连接。这是LDAPS的替代方案。',
            'options' => [
                'disabled' => '停用',
                'optional' => '选用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP 快取有效期',
            'help' => '临时存储LDAP查询结果。可以提高速度，但数据可能不是最新的。',
        ],
        'auth_ldap_debug' => [
            'description' => '显示侦错信息',
            'help' => '显示调试信息。可能会暴露私人信息，不要保持启用状态。',
        ],
        'auth_ldap_emailattr' => [
            'description' => '邮件属性',
        ],
        'auth_ldap_group' => [
            'description' => '存取群组 DN',
            'help' => '授予普通级别访问权限的组的专有名称。示例：cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '群组基础 DN',
            'help' => '搜索组的专有名称 示例：ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => '组成员属性',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '以下列方式寻找群组成员',
            'options' => [
                'username' => '使用者名称',
                'fulldn' => 'Full DN (使用前缀和后缀)',
                'puredn' => 'DN 搜寻 (使用 uid 属性搜寻)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => '群体访问',
            'help' => '定义具有访问权限和级别的群体',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP 连接端口',
            'help' => '用于连接服务器的端口。对于LDAP，端口应为389，对于LDAPS，端口应为636。',
        ],
        'auth_ldap_prefix' => [
            'description' => '使用者前缀',
            'help' => '用于将用户名转换为可分辨名称（Distinguished Name）',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP 服务器',
            'help' => '设置服务器（如果有多个，用空格分隔）。若使用SSL，请在服务器地址前加上ldaps://前缀。',
        ],
        'auth_ldap_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => '使用STARTTLS来加密连接。这是LDAPS的替代方案。',
            'options' => [
                'disabled' => '停用',
                'optional' => '选用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '使用者后缀',
            'help' => '用于将用户名转换为可分辨名称（Distinguished Name）',
        ],
        'auth_ldap_timeout' => [
            'description' => '联机超时',
            'help' => '如果一个或多个服务器无响应，较高的超时时间会导致访问速度变慢。而设置得太低，在某些情况下可能导致连接失败。',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '唯一 ID 属性',
            'help' => '用于标识用户的LDAP属性，必须是数字类型。',
        ],
        'auth_ldap_userdn' => [
            'description' => '使用全名 DN',
            'help' => '使用用户的完整DN作为群组中成员属性的值，而非采用前缀和后缀的方式（如member: uid=username,ou=groups,dc=domain,dc=com）。',
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
            'help' => '当用户登录时勾选“记住我”复选框后，保持用户登录状态的天数。',
        ],
        'authlog_purge' => [
            'description' => '验证记录项目大于',
            'help' => '由daily.sh脚本执行的清理任务',
        ],
        'base_url' => [
            'description' => '指定 URL',
            'help' => '此设置仅在您需要*强制*使用特定主机名/端口时才应设置。它将阻止从任何其他主机名访问Web界面。',
        ],
        'distributed_poller' => [
            'description' => '启用分布式轮询 (需要额外设定)',
            'help' => '启用全系统分布式轮询功能。此功能旨在实现负载分担，而非远程轮询。您必须阅读以下文档以获取启用步骤：https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'distributed_poller_group' => [
            'description' => '预设轮询器群组',
            'help' => '如果在config.php文件中没有设置，默认的轮询器组应所有轮询器进行轮询。',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 主机',
            'help' => 'Memcached服务器的主机名或IP地址。这是poller_wrapper.py和daily.sh锁定所需的。',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 连接端口',
            'help' => 'Memcached服务器的端口。默认是11211',
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
            'help' => '用于发送邮件的后端，可以是mail、sendmail或SMTP。',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '寄件者信箱地址',
            'help' => '用于发送电子邮件的电子邮件地址（发件人）',
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
            'description' => 'SMTP 连接端口设定',
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
            'description' => 'SMTP 超时设定',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP 验证使用者名称',
        ],
        'email_user' => [
            'description' => '寄件者名称',
            'help' => '作为发件人地址一部分使用的名称',
        ],
        'eventlog_purge' => [
            'description' => '事件记录大于',
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
                'help' => '当通过icmp检查主机是否在线或离线时发送的ping次数',
            ],
            'interval' => [
                'description' => 'fping 间隔',
                'help' => '每次ping之间等待的毫秒数',
            ],
            'timeout' => [
                'description' => 'fping 超时',
                'help' => '在放弃之前等待回显响应的毫秒数',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '地理编码 API 密钥',
                'help' => '地理编码API密钥（功能所需）',
            ],
            'engine' => [
                'description' => '地理编码引擎',
                'options' => [
                    'google' => '谷歌地图',
                    'openstreetmap' => '开放式街图（OpenStreetMap）',
                    'mapquest' => 'MapQuest地图',
                    'bing' => '必应地图',
                ],
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'Base URI',
                'help' => '如果您已修改了Graylog的默认设置，此选项可覆盖基本URI。',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '设备概观记录等级',
                    'help' => '设置设备概览页面上显示的最大日志级别。',
                ],
                'rowCount' => [
                    'description' => '设备概观数据笔数',
                    'help' => '设置设备概览页面上显示的行数。',
                ],
            ],
            'password' => [
                'description' => '密码',
                'help' => '访问Graylog API的密码。',
            ],
            'port' => [
                'description' => '连接端口',
                'help' => '用于访问Graylog API的端口。如果不指定，默认情况下http使用80端口，https使用443端口。',
            ],
            'server' => [
                'description' => '服务器',
                'help' => 'Graylog服务器API端点的IP或主机名。',
            ],
            'timezone' => [
                'description' => '显示时区',
                'help' => 'Graylog中的时间以GMT存储，此设置将更改显示的时区。值必须为有效的PHP时区。',
            ],
            'username' => [
                'description' => '使用者名称',
                'help' => '用户名，用于访问Graylog API。',
            ],
            'version' => [
                'description' => '版本',
                'help' => '此设置用于自动生成Graylog API的基本URI。如果您已从默认设置修改了API URI，请将其设置为“其他”并指定您的基本URI。',
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) 代理',
            'help' => '如果环境变量http_proxy或https_proxy不可用，可将此设置作为回退。',
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
            'help' => '此域名用于网络自动发现和其他进程。LibreNMS将尝试将其附加到未完全限定的主机名上。',
        ],
        'nfsen_enable' => [
            'description' => '启用 NfSen',
            'help' => '启用 NfSen 整合',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD 目录',
            'help' => '此值指定您的NFSen RRD文件存放的位置。',
        ],
        'nfsen_subdirlayout' => [
            'description' => '设定 NfSen 子目录配置',
            'help' => '这必须与您在NfSen中设置的子目录结构相匹配。默认值为1。',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => '最大统计数据的TopN值',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => '默认 Top N',
        ],
        'nfsen_stats_default' => [
            'description' => '默认统计',
        ],
        'nfsen_order_default' => [
            'description' => '默认排序方式',
        ],
        'nfsen_last_default' => [
            'description' => '默认最后一个',
        ],
        'nfsen_lasts' => [
            'description' => '默认最后选项',
        ],
        'nfsen_split_char' => [
            'description' => '分隔字符',
            'help' => '此值告诉我们用什么来替换设备主机名中的句点`.`。通常使用：`_`',
        ],
        'nfsen_suffix' => [
            'description' => '文件名称前缀',
            'help' => '这是非常关键的一点，因为在NfSen中，设备名称被限制为21个字符。这意味着设备的完整域名可能很难压缩进去，因此通常会移除这一部分。',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 主机名称',
            'help' => '应设置为librenms服务器添加时使用的主机名/IP地址',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => '设置返回的默认分组',
            ],
            'enabled' => [
                'description' => '启用 Oxidized 支援',
            ],
            'features' => [
                'versioning' => [
                    'description' => '启用组态版本存取',
                    'help' => '启用Oxidized配置版本控制（需要git后端支持）',
                ],
            ],
            'group_support' => [
                'description' => '启用向 Oxidized 返回分组的功能',
            ],
            'reload_nodes' => [
                'description' => '在每次新增设备后，重新加载 Oxidized 节点清单',
            ],
            'url' => [
                'description' => '您的 Oxidized API URL',
                'help' => 'Oxidized API 的网址（例如：http://127.0.0.1:8888）',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => '密码最小长度',
                'help' => '低于指定长度的密码将会被拒绝',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => '启用 PeeringDB 反查',
                'help' => '起用 PeeringDB lookup (资料将于由 daily.sh 进行下载)',
            ],
        ],
        'ports_fdb_purge' => [
            'description' => '连接端口 FDB 项目大于',
            'help' => '由 daily.sh 脚本完成的日常清理工作',
        ],
        'ports_purge' => [
            'description' => '清除端口已删除',
            'help' => '由 daily.sh 脚本执行的日常清理操作',
        ],
        'public_status' => [
            'description' => '公开状态显示',
            'help' => '允许不登入的情况下，显示设备的状态信息。',
        ],
        'routes_max_number' => [
            'description' => '允许探索路由的最大路由数',
            'help' => '如果路由表的大小超过此数值，将不会发现任何路由信息',
        ],
        'route_purge' => [
            'description' => '路由记录大于',
            'help' => '由 daily.sh 脚本执行的日常清理任务',
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
            'help' => 'RRD 文件的存储位置。默认位置是 LibreNMS 目录内的 rrd 文件夹。更改此设置不会移动现有的 RRD 文件。',
        ],
        'rrd_purge' => [
            'description' => 'RRD 档案项目大于',
            'help' => '由 daily.sh 脚本完成的日常清理任务',
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式设定',
            'help' => '这些设置无法在不删除现有 RRD 文件的情况下更改。但是，如果遇到性能问题，或者拥有非常快速的 I/O 系统且无需担心性能，理论上可以通过增加或减少每个 RRA 的大小来进行调整。',
        ],
        'rrdcached' => [
            'description' => '启用 rrdcached (socket)',
            'help' => '通过设置 rrdcached 套接字的位置来启用 rrdcached。可以是 unix 套接字或网络套接字（unix:/run/rrdcached.sock 或 localhost:42217）',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 路径',
        ],
        'rrdtool_tune' => [
            'description' => '调整所有 rrd 连接端口档案使用最大值',
            'help' => '自动调整 rrd 连接端口档案的最大值',
        ],
        'shorthost_target_length' => [
            'description' => '缩短后的主机名最大长度',
            'help' => '缩短主机名至最大长度，但始终保留完整的子域名部分',
        ],
        'site_style' => [
            'description' => '设定站点 css 样式',
            'options' => [
                'blue' => '蓝色',
                'dark' => '深色',
                'light' => '浅色',
                'mono' => '单色',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '传输 (优先级)',
                'help' => '选择启用的传输方式，并按您希望尝试的顺序排列它们。',
            ],
            'version' => [
                'description' => '版本 (优先级)',
                'help' => '选择启用的版本，并按您希望尝试的顺序排列它们。',
            ],
            'community' => [
                'description' => '社群 (优先级)',
                'help' => '输入 v1 和 v2c 的团体字符串，并按您希望尝试的顺序排列它们',
            ],
            'max_repeaters' => [
                'description' => '重复撷取最多次数',
                'help' => '设置用于 SNMP 批量请求的中继器',
            ],
            'port' => [
                'description' => '连接端口',
                'help' => '设置用于 SNMP 的 TCP/UDP 端口',
            ],
            'v3' => [
                'description' => 'SNMP v3 验证 (优先级)',
                'help' => '设置 v3 认证变量，并按您希望尝试的顺序排列它们',
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
                    'noAuthNoPriv' => '无认证，无隐私保护',
                    'authNoPriv' => '认证，无隐私保护',
                    'authPriv' => '认证与隐私',
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
            'description' => '过滤包含在内的 syslog 消息',
        ],
        'syslog_purge' => [
            'description' => 'Syslog 项目大于',
            'help' => '由 daily.sh 完成的清理工作',
        ],
        'title_image' => [
            'description' => '标题图片',
            'help' => '覆盖默认的标题图像。',
        ],
        'traceroute' => [
            'description' => 'traceroute 路径',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix-agent 联机超时',
            ],
            'port' => [
                'description' => '预设 unix-agent 连接端口',
                'help' => 'unix-agent (check_mk) 预设连接端口号码',
            ],
            'read-timeout' => [
                'description' => 'Unix-agent 读取超时',
            ],
        ],
        'update' => [
            'description' => '启用更新 ./daily.sh',
        ],
        'update_channel' => [
            'description' => '设定更新频道',
            'options' => [
                'master' => '每日',
                'release' => '每月',
            ],
        ],
        'virsh' => [
            'description' => 'virsh 路径',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => '可用性区块宽度',
                'help' => '输入全视图中盒子大小所需的瓦片宽度（像素）',
            ],
            'availability_map_compact' => [
                'description' => '可用性地图精简模式',
                'help' => '带有小指示符的可用性地图视图',
            ],
            'availability_map_sort_status' => [
                'description' => '依状态排序',
                'help' => '以状态做为设备与服务的排序',
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用设备群组筛选器',
                'help' => '启用设备群组筛选器',
            ],
            'default_dashboard_id' => [
                'description' => '预设信息广告牌',
                'help' => '对于没有设定预设信息广告牌的使用者，所要显示的预设信息广告牌',
            ],
            'dynamic_graphs' => [
                'description' => '启用动态群组',
                'help' => '启用动态图表，允许在图表上进行缩放和平移',
            ],
            'global_search_result_limit' => [
                'description' => '设定搜寻结果笔数上限',
                'help' => '全域搜寻结果限制',
            ],
            'graph_stacked' => [
                'description' => '使用堆栈图表',
                'help' => '显示堆叠图而不是倒置图',
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
    ],
    'twofactor' => [
        'description' => '启用双因素验证',
        'help' => '启用内置的双因素认证。您必须为每个帐户设置以使其激活。',
    ],
    'units' => [
        'days' => '日',
        'ms' => '微秒',
        'seconds' => '秒',
    ],
    'validate' => [
        'boolean' => ':值不是一个有效的布尔值',
        'email' => ':值不是一个有效的电子邮件地址',
        'integer' => ':值不是一个整数',
        'password' => '密码不正确',
        'select' => ':值不是允许的值',
        'text' => ':值不允许',
        'array' => '格式无效',
    ],
];
