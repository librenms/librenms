<?php

return [
    'title' => '設定',
    'readonly' => '在 config.php 裡被設定成唯讀，請由 config.php 移除它來啟用。',
    'groups' => [
        'alerting' => '警報',
        'api' => 'API',
        'apps' => '應用程式',
        'auth' => '驗證',
        'authorization' => '授權',
        'external' => '外部整合',
        'global' => '全域',
        'os' => '作業系統',
        'discovery' => '探索',
        'graphing' => '繪圖',
        'poller' => '輪詢器',
        'system' => '系統',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => [
                'name' => '一般警報設定',
            ],
            'email' => [
                'name' => '電子郵件設定',
            ],
            'rules' => [
                'name' => '警報規則預設值',
            ],
            'scheduled-maintenance' => [
                'name' => '排程維護',
            ],
        ],
        'api' => [
            'cors' => [
                'name' => 'CORS',
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'name' => 'PowerDNS Recursor',
            ],
            'oslv_monitor' => [
                'name' => 'OSLV Monitor',
            ],
            'sneck' => [
                'name' => 'Sneck',
            ],
            'ssl-certificates' => [
                'name' => 'SSL 憑證',
            ],
        ],
        'auth' => [
            'general' => [
                'name' => '一般驗證設定',
            ],
            'ad' => [
                'name' => 'Active Directory 設定',
            ],
            'ldap' => [
                'name' => 'LDAP 設定',
            ],
            'radius' => [
                'name' => 'Radius 設定',
            ],
            'socialite' => [
                'name' => 'Socialite 設定',
            ],
            'http' => [
                'name' => 'HTTP 驗證設定',
            ],
            'sso' => [
                'name' => '單一登入',
            ],
        ],
        'authorization' => [
            'device-group' => [
                'name' => '裝置群組設定',
            ],
        ],
        'discovery' => [
            'general' => [
                'name' => '一般探索設定',
            ],
            'route' => [
                'name' => '路由探索模組',
            ],
            'discovery_modules' => [
                'name' => '探索模組',
            ],
            'autodiscovery' => [
                'name' => '網路探索',
            ],
            'ports' => [
                'name' => '連接埠模組',
            ],
            'storage' => [
                'name' => '儲存模組',
            ],
            'processor' => [
                'name' => '處理器模組',
            ],
            'ipmi' => [
                'name' => 'IPMI 模組',
            ],
            'sensors' => [
                'name' => '感測器模組',
            ],
            'virtualization' => [
                'name' => '虛擬化模組',
            ],
        ],
        'external' => [
            'binaries' => [
                'name' => '執行檔位置',
            ],
            'location' => [
                'name' => '位置資訊設定',
            ],
            'graylog' => [
                'name' => 'Graylog 整合',
            ],
            'oxidized' => [
                'name' => 'Oxidized 整合',
            ],
            'mac_oui' => [
                'name' => 'Mac OUI 查詢整合',
            ],
            'peeringdb' => [
                'name' => 'PeeringDB 整合',
            ],
            'nfsen' => [
                'name' => 'NfSen 整合',
            ],
            'unix-agent' => [
                'name' => 'Unix-Agent 整合',
            ],
            'smokeping' => [
                'name' => 'Smokeping 整合',
            ],
            'snmptrapd' => [
                'name' => 'SNMP Traps 整合',
            ],
            'rancid' => [
                'name' => 'RANCID 整合',
            ],
            'collectd' => [
                'name' => 'Collectd 整合',
            ],
        ],
        'poller' => [
            'availability' => [
                'name' => '裝置可用性',
            ],
            'distributed' => [
                'name' => '分散式輪詢器',
            ],
            'graphite' => [
                'name' => '資料存放區: Graphite',
            ],
            'influxdb' => [
                'name' => '資料存放區: InfluxDB',
            ],
            'influxdbv2' => [
                'name' => '資料儲存：InfluxDBv2',
            ],
            'kafka' => [
                'name' => '資料存放區: Kafka',
            ],
            'mtu' => [
                'name' => 'MTU 檢查',
            ],
            'opentsdb' => [
                'name' => '資料存放區: OpenTSDB',
            ],
            'ping' => [
                'name' => 'Ping',
            ],
            'prometheus' => [
                'name' => '資料存放區: Prometheus',
            ],
            'rrdtool' => [
                'name' => 'RRDTool 設定',
            ],
            'snmp' => [
                'name' => 'SNMP',
            ],
            'dispatcherservice' => [
                'name' => 'Dispatcher Service',
            ],
            'poller_modules' => [
                'name' => '輪詢器模組',
            ],
            'ports' => [
                'name' => '連接埠輪詢模組',
            ],
        ],
        'system' => [
            'billing' => [
                'name' => '帳務',
            ],
            'cleanup' => [
                'name' => '清理',
            ],
            'proxy' => [
                'name' => 'Proxy',
            ],
            'updates' => [
                'name' => '更新',
            ],
            'scheduledtasks' => [
                'name' => '排程工作',
            ],
            'server' => [
                'name' => '伺服器',
            ],
            'reporting' => [
                'name' => '報表',
            ],
        ],
        'webui' => [
            'availability-map' => [
                'name' => '可用性地圖設定',
            ],
            'custom-map' => [
                'name' => '自訂地圖設定',
            ],
            'graph' => [
                'name' => '圖表設定',
            ],
            'dashboard' => [
                'name' => '資訊看板設定',
            ],
            'port-descr' => [
                'name' => '介面描述解析',
            ],
            'search' => [
                'name' => '搜尋設定',
            ],
            'style' => [
                'name' => '樣式',
            ],
            'device' => [
                'name' => '裝置設定',
            ],
            'worldmap' => [
                'name' => '世界地圖設定',
            ],
            'general' => [
                'name' => '一般 Web UI 設定',
            ],
            'front-page' => [
                'name' => '首頁設定',
            ],
            'menu' => [
                'name' => '選單設定',
            ],
            'scheduled-maintenance' => [
                'name' => '排程維護',
            ],
            'alert-map' => [
                'name' => '警報地圖設定',
            ],
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
            'acknowledged_alerts' => [
                'description' => '已確認的警報',
                'help' => '當警報被確認時發送警報',
            ],
            'severity' => [
                'description' => '嚴重性',
                'help' => '警報的嚴重程度',
            ],
            'default_operation_steps_to' => [
                'description' => '預設操作：結束步驟',
                'help' => '新建操作列的預設升級結束步驟（-1 表示無上限）',
            ],
            'default_operation_start_in' => [
                'description' => '預設操作：起始延遲',
                'help' => '發送操作通知前的預設延遲',
            ],
            'default_operation_step_duration' => [
                'description' => '預設操作：步驟持續時間',
                'help' => '預設操作步驟持續時間（分鐘）',
            ],
            'default_operation_notifications_suppressed' => [
                'description' => '預設操作：抑制通知',
                'help' => '新建操作列預設抑制通知',
            ],
            'invert_rule_match' => [
                'description' => '反轉比對規則',
                'help' => '僅在規則不符合時才警報',
            ],
            'recovery_alerts' => [
                'description' => '警報解除',
                'help' => '警報恢復時通知',
            ],
            'acknowledgement_alerts' => [
                'description' => '確認警報',
                'help' => '警報被確認時通知',
            ],
            'invert_map' => [
                'description' => '除了清單之外的所有裝置',
                'help' => '僅針對未列出的裝置發出警報',
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
            'acknowledged' => [
                'description' => '發送已確認的警報',
                'help' => '警報已被確認時通知',
            ],
            'fixed-contacts' => [
                'description' => '在警告期間不接受連絡人電子郵件的修改',
                'help' => '若設為 TRUE，警報啟用期間對 sysContact 或使用者電子郵件的任何變更都不會生效',
            ],
            'globals' => [
                'description' => '只發送警報給唯讀使用者',
                'help' => '只發送警報給唯讀管理員',
            ],
            'scheduled_maintenance_default_behavior' => [
                'description' => '排程維護的預設行為',
                'help' => '排程維護的預設行為',
                'options' => [
                    '1' => '略過警報',
                    '2' => '靜音警報',
                    '3' => '執行警報',
                ],
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
                'help' => '容許時間範圍（秒）',
            ],
            'users' => [
                'description' => '發送警報給一般使用者',
                'help' => '警報通知一般使用者',
            ],
        ],
        'alert_log_purge' => [
            'description' => '警報記錄項目大於',
            'help' => '由 daily.sh 執行清除',
        ],
        'discovery_on_reboot' => [
            'description' => '重新開機時探索',
            'help' => '對重新開機的裝置進行探索',
        ],
        'allow_duplicate_sysName' => [
            'description' => '允許重複 sysName',
            'help' => '預設停用新增重複的 sysName，以避免具有多個介面的裝置被重複新增',
        ],
        'allow_unauth_graphs' => [
            'description' => '允許未登入存取圖表',
            'help' => '允許在不登入情況下存取圖表',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '允許指定網路存取圖表',
            'help' => '允許指定網路可以在未登入授權查看圖表 (若未啟用 允許未登入存取圖表 則忽略此設定)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => '允許的標頭',
                    'help' => '設定 Access-Control-Allow-Headers 回應標頭',
                ],
                'allowcredentials' => [
                    'description' => '允許認證資訊',
                    'help' => '設定 Access-Control-Allow-Credentials 標頭',
                ],
                'allowmethods' => [
                    'description' => '允許的方法',
                    'help' => '比對請求方法。',
                ],
                'enabled' => [
                    'description' => '為 API 啟用 CORS 支援',
                    'help' => '允許您從 Web 用戶端載入 API 資源',
                ],
                'exposeheaders' => [
                    'description' => '公開的標頭',
                    'help' => '設定 Access-Control-Expose-Headers 回應標頭',
                ],
                'maxage' => [
                    'description' => '最長存留時間',
                    'help' => '設定 Access-Control-Max-Age 回應標頭',
                ],
                'origin' => [
                    'description' => '允許的請求來源',
                    'help' => '比對請求來源。可使用萬用字元，例如 *.mydomain.com',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'PowerDNS Recursor 的 API 金鑰',
                    'help' => '直接連線時 PowerDNS Recursor 應用程式使用的 API 金鑰',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor 是否使用 HTTPS？',
                    'help' => '直接連線時 PowerDNS Recursor 應用程式使用 HTTPS 而非 HTTP',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor 連接埠',
                    'help' => '直接連線時 PowerDNS Recursor 應用程式使用的 TCP 連接埠',
                ],
            ],
            'oslv_monitor' => [
                'seen_age' => [
                    'description' => '可見時間閾值',
                    'help' => '經過多少秒後項目視為過期',
                ],
                'linux_pg_memory_stats' => [
                    'description' => 'Linux 分頁記憶體統計',
                    'help' => '啟用 Linux 分頁記憶體統計收集',
                ],
                'misc_linux_memory_stats' => [
                    'description' => '其他 Linux 記憶體統計',
                    'help' => '啟用其他 Linux 記憶體統計收集',
                ],
                'zswap_size' => [
                    'description' => 'ZSwap 大小統計',
                    'help' => '啟用 ZSwap 大小統計收集',
                ],
                'zswap_activity' => [
                    'description' => 'ZSwap 活動統計',
                    'help' => '啟用 ZSwap 活動統計收集',
                ],
                'workingset_stats' => [
                    'description' => '工作集統計',
                    'help' => '啟用工作集統計收集',
                ],
                'thp_activity' => [
                    'description' => 'THP 活動統計',
                    'help' => '啟用透通大型分頁（THP）活動統計收集',
                ],
            ],
            'sneck' => [
                'polling_time_diff' => [
                    'description' => '輪詢時間差',
                    'help' => '為 Sneck 啟用輪詢時間差追蹤',
                ],
            ],
        ],
        'astext' => [
            'description' => '用於保存自治系統描述快取的鍵值',
        ],
        'auth' => [
            'allow_get_login' => [
                'description' => '允許 GET 登入（不安全）',
                'help' => '允許將使用者名稱與密碼變數放在 URL 的 GET 請求中登入，適用於無法互動式登入的顯示系統。此方式視為不安全，因為密碼會顯示在記錄中，且登入沒有速率限制，可能讓您遭受暴力破解攻擊。',
            ],
            'socialite' => [
                'redirect' => [
                    'description' => '重新導向登入頁面',
                    'help' => '登入頁面應立即重新導向至第一個已定義的提供者。<br><br>提示：您可以在 URL 後附加 ?redirect=0 來避免此行為',
                ],
                'register' => [
                    'description' => '允許透過提供者註冊',
                ],
                'configs' => [
                    'description' => '提供者組態',
                ],
                'scopes' => [
                    'description' => '驗證請求中應包含的範圍（scopes）',
                    'help' => '請參閱 https://laravel.com/docs/10.x/socialite#access-scopes',
                ],
                'default_role' => [
                    'description' => '預設角色',
                ],
                'claims' => [
                    'description' => 'Claims',
                    'help' => '將群組對應到角色',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => '基礎 DN',
            'help' => '群組與使用者必須位於此 DN 之下。範例：dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '檢查憑證',
            'help' => '檢查憑證的有效性。部分伺服器使用自我簽署憑證，停用此項可允許這類憑證。',
        ],
        'auth_ad_debug' => [
            'description' => '除錯',
            'help' => '顯示詳細的錯誤訊息，請勿持續啟用，因為可能洩漏資料。',
        ],
        'auth_ad_domain' => [
            'description' => 'Active Directory 網域',
            'help' => 'Active Directory 網域，範例：example.com',
        ],
        'auth_ad_global_read' => [
            'description' => '全域唯讀',
            'help' => '允許所有使用者全域唯讀存取',
        ],
        'auth_ad_group' => [
            'description' => '存取群組 DN',
            'help' => '授予一般層級存取權的群組辨別名稱。範例：cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ad_group_filter' => [
            'description' => 'LDAP 群組篩選器',
            'help' => '用於選取群組的 Active Directory LDAP 篩選器',
        ],
        'auth_ad_groups' => [
            'description' => '群組存取權限',
            'help' => '定義群組具有的存取權限與等級',
        ],
        'auth_ad_require_groupmembership' => [
            'description' => '要求群組成員資格',
            'help' => '僅允許屬於指定群組的使用者登入',
        ],
        'auth_ad_timeout' => [
            'description' => '連線逾時',
            'help' => '若有一或多台伺服器沒有回應，較長的逾時會導致登入緩慢；設得太低在某些情況下可能造成連線失敗',
        ],
        'auth_ad_user_filter' => [
            'description' => 'LDAP 使用者篩選',
            'help' => '用於選取使用者的 Active Directory LDAP 篩選器',
        ],
        'auth_ad_url' => [
            'description' => 'Active Directory 伺服器',
            'help' => '設定伺服器，以空格分隔。前綴 ldaps:// 以使用 SSL。範例：ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => '用於比對使用者名稱的屬性',
                'help' => '用於以使用者名稱識別使用者的屬性',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '繫結 DN (覆寫繫結使用者名稱)',
            'help' => 'bind 使用者的完整 DN',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '繫結密碼',
            'help' => 'bind 使用者的密碼',
        ],
        'auth_ldap_binduser' => [
            'description' => '繫結使用者',
            'help' => '在沒有使用者登入時（警報、API 等）用於查詢 LDAP 伺服器',
        ],
        'auth_ad_binddn' => [
            'description' => '繫結 DN (覆寫繫結使用者名稱)',
            'help' => 'bind 使用者的完整 DN',
        ],
        'auth_ad_bindpassword' => [
            'description' => '繫結密碼',
            'help' => 'bind 使用者的密碼',
        ],
        'auth_ad_binduser' => [
            'description' => '繫結使用者名稱',
            'help' => '在沒有使用者登入時（警報、API 等）用於查詢 AD 伺服器',
        ],
        'auth_ad_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => '使用 STARTTLS 保護連線。為 LDAPS 的替代方案。',
            'options' => [
                'disabled' => '停用',
                'optional' => '選用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP 快取有效期',
            'help' => '暫時儲存 LDAP 查詢結果。可提升速度，但資料可能過時。',
        ],
        'auth_ldap_debug' => [
            'description' => '顯示偵錯資訊',
            'help' => '顯示除錯資訊。可能洩漏私密資訊，請勿持續啟用。',
        ],
        'auth_ldap_cacertfile' => [
            'description' => '覆寫系統 TLS CA 憑證',
            'help' => '為 LDAPS 使用所提供的 CA 憑證。',
        ],
        'auth_ldap_ignorecert' => [
            'description' => '不要求有效憑證',
            'help' => 'LDAPS 不要求有效的 TLS 憑證。',
        ],
        'auth_ldap_emailattr' => [
            'description' => '郵件屬性',
        ],
        'auth_ldap_group' => [
            'description' => '存取群組 DN',
            'help' => '授予一般層級存取權的群組辨別名稱。範例：cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '群組基礎 DN',
            'help' => '用於搜尋群組的辨別名稱。範例：ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => '群組成員屬性',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '以下列方式尋找群組成員',
            'options' => [
                'username' => '使用者名稱',
                'fulldn' => '完整 DN（使用前綴與後綴）',
                'puredn' => 'DN 搜尋 (使用 uid 屬性搜尋)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => '群組存取',
            'help' => '定義具有存取權的群組及其層級',
        ],
        'auth_ldap_require_groupmembership' => [
            'description' => 'LDAP 群組成員資格驗證',
            'help' => '當提供者允許（或不允許）Compare 動作時，執行（或略過）ldap_compare。',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP 連接埠',
            'help' => '連線伺服器所用的連接埠。LDAP 應為 389，LDAPS 應為 636',
        ],
        'auth_ldap_prefix' => [
            'description' => '使用者首碼',
            'help' => '用於將使用者名稱轉換為辨別名稱',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP 伺服器',
            'help' => '設定伺服器，以空格分隔。前綴 ldaps:// 以使用 SSL',
        ],
        'auth_ldap_starttls' => [
            'description' => '使用 STARTTLS',
            'help' => '使用 STARTTLS 保護連線。為 LDAPS 的替代方案。',
            'options' => [
                'disabled' => '停用',
                'optional' => '選用',
                'required' => '必要',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '使用者尾碼',
            'help' => '用於將使用者名稱轉換為辨別名稱',
        ],
        'auth_ldap_timeout' => [
            'description' => '連線逾時',
            'help' => '若有一或多台伺服器沒有回應，較長的逾時會導致存取緩慢；設得太低在某些情況下可能造成連線失敗',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '唯一 ID 屬性',
            'help' => '用於識別使用者的 LDAP 屬性，必須為數字',
        ],
        'auth_ldap_userdn' => [
            'description' => '使用全名 DN',
            'help' => 'Uses a user\'s full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)',
        ],
        'auth_ldap_userlist_filter' => [
            'description' => '自訂 LDAP 使用者篩選器',
            'help' => '自訂 LDAP 篩選器，若您的 LDAP 目錄有數千名使用者，可用來限制回應數量',
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => '萬用字元使用者 OU',
            'help' => '搜尋符合使用者名稱的使用者，不受使用者後綴中所設 OU 的限制。若您的使用者分散在不同 OU 時很有用。Bind 使用者名稱若有設定，仍使用使用者後綴',
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
            'help' => '授權方法。注意，若設定錯誤將導致您無法登入系統。若真的發生，您可以手動將 config.php 的設定改回 $config[\'auth_mechanism\'] = \'mysql\';',
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
            'help' => '登入時勾選「記住我」後，使用者保持登入狀態的天數。',
        ],
        'authlog_purge' => [
            'description' => '驗證記錄項目大於',
            'help' => '由 daily.sh 執行清除',
        ],
        'availablity' => [
            'threshold_ok' => [
                'description' => '可用性正常閾值',
                'help' => '綠色的閾值',
            ],
            'threshold_warning' => [
                'description' => '可用性警告閾值',
                'help' => '橘色的閾值',
            ],
        ],
        'bad_entity_sensor_regex' => [
            'description' => '不良 Entity 感測器 Regex',
            'help' => '用於比對不良 entity 感測器的正規表示式，這些感測器不會顯示在 Web 介面中。',
        ],
        'billing' => [
            '95th_default_agg' => [
                'description' => '預設 95 百分位彙整',
                'help' => '將 95 百分位計算的預設選項設為彙整。',
            ],
        ],
        'enable_billing' => [
            'description' => '啟用帳務',
            'help' => '啟用帳務模組，讓您可以監控連接埠用量。',
        ],
        'peering_descr' => [
            'description' => 'Peering 連接埠類型',
            'help' => '所列描述類型的連接埠會顯示在 peering ports 選單項目下。詳情請參閱「介面描述解析」文件。',
        ],
        'transit_descr' => [
            'description' => 'Transit 連接埠類型',
            'help' => '所列描述類型的連接埠會顯示在 transit ports 選單項目下。詳情請參閱「介面描述解析」文件。',
        ],
        'collectd_dir' => [
            'description' => 'Collectd 目錄',
            'help' => 'Collectd 儲存其 RRD 檔案的目錄。用於在 LibreNMS 中顯示來自 collectd 的資料。',
        ],
        'collectd_sock' => [
            'description' => 'Collectd Socket',
            'help' => 'Collectd 監聽的 socket。用於在 LibreNMS 中顯示來自 collectd 的資料。',
        ],
        'core_descr' => [
            'description' => 'Core 連接埠類型',
            'help' => '所列描述類型的連接埠會顯示在 core ports 選單項目下。詳情請參閱「介面描述解析」文件。',
        ],
        'custom_descr' => [
            'description' => '自訂連接埠類型',
            'help' => '所列描述類型的連接埠會顯示在 custom ports 選單項目下。詳情請參閱「介面描述解析」文件。',
        ],
        'custom_map' => [
            'background_type' => [
                'description' => '背景類型',
                'help' => '新地圖的預設背景類型。需要設定背景資料。',
            ],
            'background_data' => [
                'color' => [
                    'description' => '背景顏色',
                    'help' => '地圖背景的初始顏色',
                ],
                'lat' => [
                    'description' => '背景地圖緯度',
                    'help' => '背景地理地圖的初始緯度',
                ],
                'lng' => [
                    'description' => '背景地圖經度',
                    'help' => '背景地理地圖的初始經度',
                ],
                'layer' => [
                    'description' => '背景地圖圖層',
                    'help' => '背景地理地圖的初始圖層',
                ],
                'zoom' => [
                    'description' => '背景地圖縮放',
                    'help' => '背景地理地圖的初始縮放',
                ],
            ],
            'edge_font_color' => [
                'description' => '連線文字顏色',
                'help' => '連線標籤的預設字型顏色',
            ],
            'edge_font_face' => [
                'description' => '連線字型',
                'help' => '連線標籤的預設字型',
            ],
            'edge_font_size' => [
                'description' => '連線文字大小',
                'help' => '連線標籤的預設字型大小',
            ],
            'edge_seperation' => [
                'description' => '連線間距',
                'help' => '新地圖的預設連線間距',
            ],
            'height' => [
                'description' => '地圖高度',
                'help' => '新地圖的預設地圖高度',
            ],
            'node_align' => [
                'description' => '節點對齊',
                'help' => '新地圖的預設節點對齊方式',
            ],
            'node_background' => [
                'description' => '節點背景',
                'help' => '節點標籤的預設背景顏色',
            ],
            'node_border' => [
                'description' => '節點邊框',
                'help' => '節點標籤的預設邊框顏色',
            ],
            'node_font_color' => [
                'description' => '節點文字顏色',
                'help' => '節點標籤的預設字型顏色',
            ],
            'node_font_face' => [
                'description' => '節點字型',
                'help' => '節點標籤的預設字型',
            ],
            'node_font_size' => [
                'description' => '節點文字大小',
                'help' => '節點標籤的預設字型大小',
            ],
            'node_size' => [
                'description' => '節點大小',
                'help' => '節點的預設大小',
            ],
            'node_type' => [
                'description' => '節點顯示類型',
                'help' => '節點的預設顯示類型',
            ],
            'reverse_arrows' => [
                'description' => '反轉連線箭頭',
                'help' => '預設箭頭方向。朝向中心（預設）或朝向兩端',
            ],
            'width' => [
                'description' => '地圖寬度',
                'help' => '新地圖的預設地圖寬度',
            ],
        ],
        'customers_descr' => [
            'description' => 'Customer 連接埠類型',
            'help' => '所列描述類型的連接埠會顯示在 customers ports 選單項目下。詳情請參閱「介面描述解析」文件。',
        ],
        'base_url' => [
            'description' => '指定 URL',
            'help' => '此項*僅*在您想*強制*使用特定主機名稱／連接埠時才應設定。設定後將無法從其他任何主機名稱使用 Web 介面',
        ],
        'disabled_sensors' => [
            'description' => '已停用的感測器',
            'help' => '不應輪詢或顯示於 Web 介面中的感測器。',
        ],
        'disabled_sensors_regex' => [
            'description' => '已停用感測器 Regex',
            'help' => '符合此正規表示式的感測器將不會被輪詢或顯示於 Web 介面中。',
        ],
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'ARP 表',
            ],
            'applications' => [
                'description' => '應用程式',
            ],
            'bgp-peers' => [
                'description' => 'BGP 鄰居',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'mac-accounting' => [
                'description' => 'MAC Accounting',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'slas' => [
                'description' => '服務等級協定（SLA）追蹤',
            ],
            'cisco-pw' => [
                'description' => 'Cisco PW',
            ],
            'cisco-vrf-lite' => [
                'description' => 'Cisco VRF Lite',
            ],
            'discovery-arp' => [
                'description' => '探索 ARP',
            ],
            'discovery-protocols' => [
                'description' => '探索協定',
            ],
            'entity-physical' => [
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'fdb-table' => [
                'description' => 'FDB 表',
            ],
            'hr-device' => [
                'description' => 'HR Device',
            ],
            'ipv4-addresses' => [
                'description' => 'IPv4 位址',
            ],
            'ipv6-addresses' => [
                'description' => 'IPv6 位址',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'loadbalancers' => [
                'description' => '負載平衡器',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mempools' => [
                'description' => '記憶體集區',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'os' => [
                'description' => '作業系統',
            ],
            'ports' => [
                'description' => '連接埠',
            ],
            'ports-stack' => [
                'description' => 'Ports Stack',
            ],
            'processors' => [
                'description' => '處理器',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'route' => [
                'description' => '路由',
            ],
            'sensors' => [
                'description' => '感測器',
            ],
            'services' => [
                'description' => '服務',
            ],
            'storage' => [
                'description' => '儲存空間',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'vlans' => [
                'description' => 'VLANs',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM 資訊',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => '無線',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => '印表機耗材',
            ],
        ],
        'distributed_poller' => [
            'description' => '啟用分散式輪詢 (需要額外設定)',
            'help' => '於系統全域啟用分散式輪詢。此功能用於負載分擔，而非遠端輪詢。您必須閱讀文件以了解啟用步驟：https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => '預設輪詢器群組',
            'help' => '若 config.php 中未設定，所有輪詢器應輪詢的預設輪詢器群組',
        ],
        'device_traffic_iftype' => [
            'description' => '裝置流量介面類型',
            'help' => '要從裝置圖表中排除的介面類型。',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 主機',
            'help' => 'memcached 伺服器的主機名稱或 IP。poller_wrapper.py 與 daily.sh 鎖定需要此項。',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 連接埠',
            'help' => 'memcached 伺服器的連接埠。預設為 11211',
        ],
        'enable_ports_etherlike' => [
            'description' => '為連接埠啟用 etherlike 圖表',
        ],
        'email_auto_tls' => [
            'description' => '啟用 / 停用自動 TLS 支援',
            'help' => '在退回未加密連線前，先嘗試使用 TLS',
        ],
        'email_smtp_verifypeer' => [
            'description' => '驗證對端憑證',
            'help' => '透過 TLS 連線到 SMTP 伺服器時不驗證對端憑證',
        ],
        'email_smtp_allowselfsigned' => [
            'description' => '允許自我簽署憑證',
            'help' => '透過 TLS 連線到 SMTP 伺服器時允許自我簽署憑證',
        ],
        'email_attach_graphs' => [
            'description' => '附加圖表影像',
            'help' => '這會在警報觸發時產生圖表，並附加及內嵌於電子郵件中。',
        ],
        'email_backend' => [
            'description' => '寄送郵件方式',
            'help' => '用於發送電子郵件的後端，可為 mail、sendmail 或 SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '寄件者信箱位址',
            'help' => '用於發送電子郵件的寄件者電子郵件地址',
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
            'help' => '若您的 SMTP 伺服器需要驗證，請啟用此項',
        ],
        'email_smtp_host' => [
            'description' => '指定寄信用的 SMTP 主機',
            'help' => '要投遞郵件的 SMTP 伺服器 IP 或 DNS 名稱',
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
            'help' => '用於寄件者地址的名稱',
        ],
        'enable_clear_discovery' => [
            'description' => '啟用清除探索',
            'help' => '啟用清除裝置探索日期與時間的功能。這會強制重新探索該裝置。',
        ],
        'enable_inventory' => [
            'description' => '啟用庫存',
            'help' => '啟用庫存頁面，顯示裝置的硬體庫存。',
        ],
        'enable_lazy_load' => [
            'description' => '啟用延遲載入',
            'help' => '延遲載入僅在當下載入所需資料，藉此加快頁面載入速度。若您遇到問題，可停用此項。',
        ],
        'enable_libvirt' => [
            'description' => '啟用 Libvirt',
            'help' => '啟用 libvirt 頁面，顯示裝置的虛擬機器。',
        ],
        'enable_proxmox' => [
            'description' => '啟用 Proxmox',
            'help' => '啟用 Proxmox 頁面，顯示裝置的虛擬機器。',
        ],
        'enable_pseudowires' => [
            'description' => '啟用 Pseudowires',
            'help' => '啟用 pseudowires 頁面，顯示裝置的 pseudowires。',
        ],
        'enable_syslog' => [
            'description' => '啟用 Syslog',
            'help' => '在 WebUI 中啟用 syslog 的可見性。',
        ],
        'eventlog_purge' => [
            'description' => '事件記錄大於',
            'help' => '由 daily.sh 進行清理作業',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => '取代預設 Favicon.',
        ],
        'front_page' => [
            'description' => '首頁',
            'help' => '設定自訂首頁，即您首次登入時看到的頁面。例如，若您建立 `resources/views/overview/custom/foobar.blade.php`，請將 `front_page` 設為 `foobar`',
        ],
        'front_page_down_box_limit' => [
            'description' => '離線裝置上限',
            'help' => '首頁離線方塊中要顯示的裝置數量',
        ],
        'front_page_settings' => [
            'top_devices' => [
                'description' => '前段裝置',
                'help' => '首頁要顯示的前段裝置數量',
            ],
            'top_ports' => [
                'description' => '前段連接埠',
                'help' => '首頁要顯示的前段連接埠數量',
            ],
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
                'help' => '透過 ICMP 檢查主機是否上線時要發送的 ping 次數',
            ],
            'interval' => [
                'description' => 'fping 間隔',
                'help' => '每次 ping 之間等待的毫秒數',
            ],
            'timeout' => [
                'description' => 'fping 逾時',
                'help' => '放棄前等待 echo 回應的毫秒數',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '地理編碼 API 金鑰',
                'help' => '地理編碼 API 金鑰（運作所需）',
            ],
            'dns' => [
                'description' => '使用 DNS Location 記錄',
                'help' => '使用 DNS 伺服器的 LOC 記錄取得主機名稱的地理座標',
            ],
            'engine' => [
                'description' => '地理編碼引擎',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                    'esri' => 'ESRI ArcGIS',
                ],
            ],
            'latlng' => [
                'description' => '嘗試對位置進行地理編碼',
                'help' => '輪詢期間嘗試透過地理編碼 API 查詢經緯度',
            ],
            'layer' => [
                'description' => '初始地圖圖層',
                'help' => '要顯示的初始地圖圖層。*並非所有圖層都適用於所有地圖引擎。',
                'options' => [
                    'Streets' => '街道',
                    'Sattelite' => '衛星',
                    'Topography' => '地形',
                    'Satellite' => '衛星',
                ],
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => '啟用',
                'help' => '將指標匯出至 Graphite',
            ],
            'host' => [
                'description' => '伺服器',
                'help' => '要傳送資料的 Graphite 伺服器 IP 或主機名稱',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '用於連線 Graphite 伺服器的連接埠',
            ],
            'prefix' => [
                'description' => '前綴（選填）',
                'help' => '會將前綴加到所有指標的開頭。必須為以點分隔的英數字元',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => '期間',
                'help' => '計算所列期間的裝置可用性。（期間以秒定義）',
            ],
            'availability_consider_maintenance' => [
                'description' => '定期維護不影響可用性',
                'help' => '停用為處於維護模式的裝置建立中斷記錄及降低可用性。',
            ],
        ],
        'graphs' => [
            'port_speed_zoom' => [
                'description' => '將連接埠圖表縮放至連接埠速度',
                'help' => '縮放連接埠圖表，使最大值永遠為連接埠速度；停用後連接埠圖表會縮放至流量',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => '基礎 URI',
                'help' => '若您修改了 Graylog 預設值，可覆寫 base uri。',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '裝置概觀記錄等級',
                    'help' => '設定裝置概觀頁面顯示的最高記錄等級。',
                ],
                'rowCount' => [
                    'description' => '裝置概觀資料筆數',
                    'help' => '設定裝置概觀頁面顯示的資料筆數。',
                ],
            ],
            'password' => [
                'description' => '密碼',
                'help' => '存取 Graylog API 的密碼。',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '用於存取 Graylog API 的連接埠。若未提供，HTTP 為 80，HTTPS 為 443。',
            ],
            'server' => [
                'description' => '伺服器',
                'help' => 'Graylog 伺服器 API 端點的 IP 或主機名稱。',
            ],
            'timezone' => [
                'description' => '顯示時區',
                'help' => 'Graylog 時間以 GMT 儲存，此設定會變更顯示的時區。值必須為有效的 PHP 時區。',
            ],
            'username' => [
                'description' => '使用者名稱',
                'help' => '存取 Graylog API 的使用者名稱。',
            ],
            'version' => [
                'description' => '版本',
                'help' => '用於自動建立 Graylog API 的 base_uri。若您已將 API URI 從預設值修改，請將此設為 other 並指定您的 base_uri。',
            ],
            'query' => [
                'field' => [
                    'description' => '查詢 API 欄位',
                    'help' => '變更查詢 Graylog API 的預設欄位。',
                ],
            ],
            'match-any-address' => [
                'description' => '比對任一位址',
                'help' => '用於將裝置的任一位址比對到 graylog 記錄訊息的來源；預設僅使用主要位址',
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => '主要下拉連結',
                    'help' => '設定裝置下拉選單中的主要連結',
                ],
            ],
        ],
        'http_auth_header' => [
            'description' => '包含使用者名稱的欄位名稱',
            'help' => '可為 ENV 或 HTTP 標頭欄位，例如 REMOTE_USER、PHP_AUTH_USER 或自訂變體',
        ],
        'http_auth_guest' => [
            'description' => 'HTTP 驗證訪客使用者',
            'help' => '若設定，允許所有 HTTP 使用者驗證，並將未知使用者指派為指定的本機使用者名稱',
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) 代理',
            'help' => 'Set this as a fallback if http_proxy or https_proxy environment variable is not available.',
        ],
        'https_proxy' => [
            'description' => 'HTTPS Proxy',
            'help' => '若 https_proxy 環境變數無法使用，將此設為備援。',
        ],
        'icmp_check' => [
            'description' => 'ICMP 檢查',
            'help' => '為所有裝置全域啟用 ICMP 檢查，這會 ping 裝置以檢查其上線或離線。停用此項可能導致輪詢無法準時完成。',
        ],
        'ignore_mount' => [
            'description' => '忽略掛接點',
            'help' => '不要監控這些掛載點的磁碟使用量',
        ],
        'ignore_mount_network' => [
            'description' => '忽略網路掛接點',
            'help' => '不要監控網路掛載點的磁碟使用量',
        ],
        'ignore_mount_optical' => [
            'description' => '忽略光碟機',
            'help' => '不要監控光碟機的磁碟使用量',
        ],
        'ignore_mount_removable' => [
            'description' => '忽略卸除式磁碟機',
            'help' => '不要監控卸除式裝置的磁碟使用量',
        ],
        'ignore_mount_regexp' => [
            'description' => '以 Regex 設定要忽略的掛接點',
            'help' => '不要監控符合至少一個這些正規表示式的掛載點的磁碟使用量',
        ],
        'ignore_mount_string' => [
            'description' => '以內含字串設定要忽略的掛接點',
            'help' => '不要監控包含至少一個這些字串的掛載點的磁碟使用量',
        ],
        'influxdb' => [
            'db' => [
                'description' => '資料庫',
                'help' => '用於儲存指標的 InfluxDB 資料庫名稱',
            ],
            'enable' => [
                'description' => '啟用',
                'help' => '將指標匯出至 InfluxDB',
            ],
            'host' => [
                'description' => '伺服器',
                'help' => '要傳送資料的 InfluxDB 伺服器 IP 或主機名稱',
            ],
            'password' => [
                'description' => '密碼',
                'help' => '連線 InfluxDB 的密碼（如有需要）',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '用於連線 InfluxDB 伺服器的連接埠',
            ],
            'timeout' => [
                'description' => '逾時',
                'help' => '等待 InfluxDB 伺服器的時間，0 表示預設逾時',
            ],
            'transport' => [
                'description' => '傳輸方式',
                'help' => '用於連線 InfluxDB 伺服器的連接埠',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDP',
                ],
            ],
            'username' => [
                'description' => '使用者名稱',
                'help' => '連線 InfluxDB 的使用者名稱（如有需要）',
            ],
            'batch_size' => [
                'description' => '批次大小',
                'help' => '單一批次要傳送的指標數量，0 表示不分批',
            ],
            'measurements' => [
                'description' => 'Measurements',
                'help' => '要傳送至 InfluxDB 的 measurement 清單，留空則全部傳送',
            ],
            'verifySSL' => [
                'description' => '驗證 SSL',
                'help' => '驗證 SSL 憑證是否有效且受信任',
            ],
            'debug' => [
                'description' => '除錯',
                'help' => '啟用或停用對 CLI 的詳細輸出',
            ],
        ],
        'influxdbv2' => [
            'bucket' => [
                'description' => 'Bucket',
                'help' => '用於儲存指標的 InfluxDB Bucket 名稱',
            ],
            'enable' => [
                'description' => '啟用',
                'help' => '使用 InfluxDBv2 API 將指標匯出至 InfluxDB',
            ],
            'host' => [
                'description' => '伺服器',
                'help' => '要傳送資料的 InfluxDB 伺服器 IP 或主機名稱',
            ],
            'token' => [
                'description' => '權杖',
                'help' => '連線 InfluxDB 的權杖（如有需要）',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '用於連線 InfluxDB 伺服器的連接埠',
            ],
            'transport' => [
                'description' => '傳輸方式',
                'help' => '用於連線 InfluxDB 伺服器的連接埠',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                ],
            ],
            'organization' => [
                'description' => '組織',
                'help' => 'InfluxDB 伺服器上包含該 bucket 的組織',
            ],
            'allow_redirects' => [
                'description' => '允許重新導向',
                'help' => '允許來自 InfluxDB 伺服器的重新導向',
            ],
            'debug' => [
                'description' => '除錯',
                'help' => '啟用或停用對 CLI 的詳細輸出',
            ],
            'log_file' => [
                'description' => '記錄檔',
                'help' => '若需要，可為除錯定義另一個記錄檔',
            ],
            'groups-exclude' => [
                'description' => '排除的裝置群組',
                'help' => '排除不傳送資料至 InfluxDBv2 的裝置群組',
            ],
            'timeout' => [
                'description' => '逾時',
                'help' => '逾時（秒）',
            ],
            'verify' => [
                'description' => '驗證',
                'help' => '驗證憑證',
            ],
            'batch_size' => [
                'description' => '批次大小',
                'help' => '傳送前應綑綁多少指標',
            ],
            'max_retry' => [
                'description' => '最大重試次數',
                'help' => '應重試多少次',
            ],
        ],
        'kafka' => [
            'enable' => [
                'description' => '啟用',
                'help' => '使用 idealo/php-rdkafka-ffi 將指標匯出至 Kafka',
            ],
            'groups-exclude' => [
                'description' => '排除的裝置群組 ID',
                'help' => '排除不傳送資料至 Kafka 的裝置群組 ID',
            ],
            'measurement-exclude' => [
                'description' => '排除的 measurement',
                'help' => '排除不傳送至 Kafka 的探索模組',
            ],
            'debug' => [
                'description' => '除錯',
                'help' => '啟用 Kafka 內部儲存程序的詳細記錄',
            ],
            'security' => [
                'debug' => [
                    'description' => '安全性除錯',
                    'help' => '顯示與 Kafka broker 之間安全性通訊的更詳細資訊',
                ],
            ],
            'broker' => [
                'list' => [
                    'description' => 'Kafka Broker 伺服器清單，格式為 host!:port',
                    'help' => 'Kafka broker 清單，格式為 host!:port。https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md',
                ],
            ],
            'idempotence' => [
                'description' => '冪等性',
                'help' => '設為 true 時，生產者會確保訊息恰好成功產生一次，並維持原始的產生順序',
            ],
            'topic' => [
                'description' => '主題',
                'help' => '用於組織訊息的類別',
            ],
            'ssl' => [
                'enable' => [
                    'description' => '啟用 SSL',
                    'help' => '在 Kafka 中啟用 SSL 支援',
                ],
                'protocol' => [
                    'description' => 'SSL 協定',
                    'help' => '與 broker 通訊所用的協定',
                ],
                'ca' => [
                    'location' => [
                        'description' => 'SSL 憑證授權單位位置',
                        'help' => '用於驗證 broker 金鑰的 CA 憑證檔案或目錄路徑。',
                    ],
                ],
                'certificate' => [
                    'location' => [
                        'description' => 'SSL 憑證位置',
                        'help' => '用於驗證的用戶端公開金鑰（PEM）路徑。',
                    ],
                ],
                'key' => [
                    'location' => [
                        'description' => 'SSL 憑證金鑰位置',
                        'help' => '用於驗證的用戶端私密金鑰（PEM）路徑。',
                    ],
                    'password' => [
                        'description' => 'SSL 憑證金鑰密碼',
                        'help' => '私密金鑰密語（搭配 kafka.ssl.key.location 使用）。',
                    ],
                ],
                'keystore' => [
                    'location' => [
                        'description' => 'SSL Keystore 憑證位置',
                        'help' => '用於驗證的用戶端 keystore（PKCS#12）路徑。',
                    ],
                    'password' => [
                        'description' => 'SSL Keystore 金鑰密碼',
                        'help' => '用戶端 keystore（PKCS#12）密碼。',
                    ],
                ],
            ],
            'flush' => [
                'timeout' => [
                    'description' => 'Kafka Flush 逾時',
                    'help' => 'Kafka 等待此逾時時間以清空佇列中的訊息',
                ],
            ],
            'buffer' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka 緩衝區保留於輪詢器記憶體中的最大訊息數',
                        'help' => 'Kafka 緩衝區允許保留於輪詢器記憶體中的最大訊息數',
                    ],
                ],
            ],
            'batch' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka 每次呼叫 Kafka 伺服器所發送的最大訊息數',
                        'help' => 'Kafka 每次呼叫 Kafka 伺服器所發送的最大訊息數',
                    ],
                ],
            ],
            'linger' => [
                'ms' => [
                    'description' => 'Kafka 在發送批次前於輪詢器記憶體中累積訊息的等待時間（毫秒）',
                    'help' => 'Kafka 在發送批次前於輪詢器記憶體中累積訊息的等待時間（毫秒）',
                ],
            ],
            'request' => [
                'required' => [
                    'acks' => [
                        'description' => 'Kafka 要求的必要 acks',
                        'help' => 'Kafka 要求的必要 acks',
                    ],
                ],
            ],
        ],
        'int_core' => [
            'description' => '啟用 Core 連接埠選單',
            'help' => '在 Web 介面中啟用 core ports 選單',
        ],
        'int_customers' => [
            'description' => '啟用 Customers 連接埠選單',
            'help' => '在 Web 介面中啟用 customers ports 選單',
        ],
        'int_peering' => [
            'description' => '啟用 Peering 連接埠選單',
            'help' => '在 Web 介面中啟用 peering ports 選單',
        ],
        'int_transit' => [
            'description' => '啟用 Transit 連接埠選單',
            'help' => '在 Web 介面中啟用 transit ports 選單',
        ],
        'int_l2tp' => [
            'description' => '啟用 L2TP 連接埠選單',
            'help' => '在 Web 介面中啟用 L2TP ports 選單',
        ],
        'ipmitool' => [
            'description' => 'ipmtool 路徑',
        ],
        'ipmi.type' => [
            'description' => 'IPMI 類型',
            'help' => '要使用的 IPMI 類型，可為 `lan`、`lanplus`、`open`、`sol`、`raw` 或 `shell`',
        ],
        'ipmi_unit' => [
            'description' => 'IPMI 單位',
            'help' => '可探索的 IPMI 單位類型。',
        ],
        'libvirt_protocols' => [
            'description' => 'Libvirt 協定',
            'help' => '用於 libvirt 連線的協定。',
        ],
        'libvirt_username' => [
            'description' => 'Libvirt 使用者名稱',
            'help' => '用於 libvirt 連線的使用者名稱。',
        ],
        'location_map' => [
            'description' => '特定位置對應',
            'help' => '將某個 sysLocation 值對應到另一個值。',
        ],
        'location_map_regex' => [
            'description' => '使用 regex 的特定位置對應',
            'help' => '使用 regex 將某個 sysLocation 值對應到另一個值。',
        ],
        'location_map_regex_sub' => [
            'description' => '使用 regex 替換的特定位置對應',
            'help' => '使用 regex 替換 sysLocation 值。',
        ],
        'login_message' => [
            'description' => '登入訊息',
            'help' => '顯示於登入頁面',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => '啟用 MAC OUI 查詢',
                'help' => '啟用 MAC 位址廠商（OUI）查詢（資料由 daily.sh 下載）',
            ],
        ],
        'mono_font' => [
            'description' => 'Monospaced 字型',
        ],
        'mtr' => [
            'description' => 'mtr 路徑',
        ],
        'mtu_options' => [
            'bytes' => [
                'description' => 'MTU 測試封包大小',
                'help' => 'MTU 測試封包的大小（位元組）（留空以停用 MTU 測試）',
            ],
        ],
        'mydomain' => [
            'description' => '主要網域',
            'help' => '此網域用於網路自動探索及其他程序。LibreNMS 會嘗試將其附加到非完整網域名稱的主機名稱上。',
        ],
        'network_map_show_on_worldmap' => [
            'description' => '在地圖上顯示網路連線',
            'help' => '在世界地圖上顯示不同位置之間的網路連線（類似 weathermap）',
        ],
        'network_map_worldmap_show_disabled_alerts' => [
            'description' => '顯示已停用警報的裝置',
            'help' => '在網路地圖上顯示已停用警報的裝置',
        ],
        'network_map_worldmap_link_type' => [
            'description' => '網路地圖來源',
            'help' => '選擇網路地圖連線的資料來源',
        ],
        'nfsen_enable' => [
            'description' => '啟用 NfSen',
            'help' => '啟用 NfSen 整合',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD 目錄',
            'help' => '此值指定您的 NFSen RRD 檔案位置。',
        ],
        'nfsen_subdirlayout' => [
            'description' => '設定 NfSen 子目錄配置',
            'help' => '此項必須與您在 NfSen 中設定的子目錄配置相符。1 為預設值。',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => '統計的最大 topN 值',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => '預設 Top N',
        ],
        'nfsen_stats_default' => [
            'description' => '預設統計',
        ],
        'nfsen_order_default' => [
            'description' => '預設排序',
        ],
        'nfsen_last_default' => [
            'description' => '預設 Last',
        ],
        'nfsen_lasts' => [
            'description' => '預設 Last 選項',
        ],
        'nfsen_base' => [
            'description' => 'NFSen 基礎目錄',
            'help' => '用於定位裝置專屬的圖表',
        ],
        'nfsen_split_char' => [
            'description' => '分隔字元',
            'help' => '此值告訴我們要將裝置主機名稱中的句點 `.` 替換成什麼。通常為：`_`',
        ],
        'nfsen_suffix' => [
            'description' => '檔案名稱首碼',
            'help' => '這是非常重要的部分，因為 NfSen 中的裝置名稱限制為 21 個字元。這表示裝置的完整網域名稱可能很難塞入，因此這段通常會被移除。',
        ],
        'no_proxy' => [
            'description' => 'Proxy 例外',
            'help' => '若 no_proxy 環境變數無法使用，將此設為備援。以逗號分隔要忽略的 IP、主機或網域清單。',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => '啟用',
                'help' => '將指標匯出至 OpenTSDB',
            ],
            'host' => [
                'description' => '伺服器',
                'help' => '要傳送資料的 OpenTSDB 伺服器 IP 或主機名稱',
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '用於連線 OpenTSDB 伺服器的連接埠',
            ],
        ],
        'overview_show_sysDescr' => [
            'description' => '在裝置概觀顯示 sysDescr',
            'help' => '在裝置概觀頁面顯示 sysDescr',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 主機名稱',
            'help' => '應設為 LibreNMS 伺服器被新增時所用的主機名稱／IP',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => '設定預設群組',
            ],
            'ignore_groups' => [
                'description' => '不要備份這些 Oxidized 群組',
                'help' => '排除不傳送至 Oxidized 的群組（透過變數對應設定）',
            ],
            'enabled' => [
                'description' => '啟用 Oxidized 支援',
            ],
            'features' => [
                'versioning' => [
                    'description' => '啟用組態版本存取',
                    'help' => '啟用 Oxidized 組態版本控制（需要 git 後端）',
                ],
            ],
            'group_support' => [
                'description' => '啟用將群組提供給 Oxidized',
            ],
            'ignore_os' => [
                'description' => '不要備份這些 OS',
                'help' => '不要使用 Oxidized 備份所列的 OS。OS 必須與 LibreNMS 的 OS 名稱相符（皆為小寫且無空格）。僅允許既有的 OS。',
            ],
            'ignore_types' => [
                'description' => '不要備份這些裝置類型',
                'help' => '不要使用 Oxidized 備份所列的裝置類型。僅允許既有的類型。',
            ],
            'reload_nodes' => [
                'description' => '在每次新增裝置後，重新載入 Oxidized 節點清單',
            ],
            'maps' => [
                'description' => '變數對應',
                'help' => '用於設定群組或其他變數，或對應名稱不同的 OS。',
            ],
            'url' => [
                'description' => '您的 Oxidized API URL',
                'help' => 'Oxidized API URL（例如：http://127.0.0.1:8888）',
            ],
        ],
        'page_refresh' => [
            'description' => '頁面重新整理',
            'help' => '每隔多少秒重新整理頁面。設為 0 以停用。',
        ],
        'password' => [
            'min_length' => [
                'description' => '密碼最小長度',
                'help' => '短於指定長度的密碼將被拒絕',
            ],
            'uncompromised' => [
                'description' => '要求密碼未遭外洩',
                'help' => '使用 k-anonymity 比對 HaveIBeenPwned 資料庫檢查密碼',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => '啟用 PeeringDB 反查',
                'help' => '起用 PeeringDB lookup (資料將於由 daily.sh 進行下載)',
            ],
        ],
        'percentile_value' => [
            'description' => '百分位值',
            'help' => '用於流量圖表的百分位值。0 表示停用。',
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => '啟用使用者存限可取用動態裝置群組',
                ],
            ],
        ],
        'bad_if' => [
            'description' => '不良介面 ifDescr',
            'help' => '應忽略的網路介面 IF-MIB::ifDescr',
        ],
        'bad_if_regexp' => [
            'description' => '不良介面 ifDescr Regex',
            'help' => '使用正規表示式應忽略的網路介面 IF-MIB::ifDescr',
        ],
        'bad_ifalias_regexp' => [
            'description' => '不良介面 ifAlias Regex',
            'help' => '使用正規表示式應忽略的網路介面 IF-MIB::ifAlias',
        ],
        'bad_ifname_regexp' => [
            'description' => '不良介面 ifName Regex',
            'help' => '使用正規表示式應忽略的網路介面 IF-MIB::ifName',
        ],
        'bad_ifoperstatus' => [
            'description' => '不良介面 ifOperStatus 狀態',
            'help' => '應忽略的網路介面 IF-MIB::ifOperStatus',
        ],
        'bad_iftype' => [
            'description' => '捨棄介面',
            'help' => '應該被忽略的網路介面類型',
        ],
        'ping_rrd_step' => [
            'description' => 'Ping 頻率',
            'help' => '多久檢查一次。設定所有節點的預設值。警告！若您變更此項，必須進行額外的變更。請參閱 Fast Ping 文件。',
        ],
        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Unix Agent',
            ],
            'os' => [
                'description' => '作業系統',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'sensors' => [
                'description' => '感測器',
            ],
            'processors' => [
                'description' => '處理器',
            ],
            'mempools' => [
                'description' => '記憶體集區',
            ],
            'storage' => [
                'description' => '儲存空間',
            ],
            'netstats' => [
                'description' => '網路統計',
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
                'description' => '連接埠',
            ],
            'bgp-peers' => [
                'description' => 'BGP 鄰居',
            ],
            'vlans' => [
                'description' => 'VLANs',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wireless' => [
                'description' => '無線',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'ospfv3' => [
                'description' => 'OSPFv3',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => 'Cisco IPSec Flow Monitor',
            ],
            'cisco-remote-access-monitor' => [
                'description' => 'Cisco Remote Access Monitor',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'slas' => [
                'description' => '服務等級協定（SLA）追蹤',
            ],
            'mac-accounting' => [
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
                'description' => 'Aruba 控制器',
            ],
            'availability' => [
                'description' => '可用性',
            ],
            'entity-physical' => [
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'applications' => [
                'description' => '應用程式',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM 資訊',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'loadbalancers' => [
                'description' => '負載平衡器',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => '印表機耗材',
            ],
            'port-security' => [
                'description' => '連接埠安全性',
            ],
        ],
        'polling.selected_ports' => [
            'description' => '選取的連接埠輪詢',
            'help' => '啟用選取的連接埠輪詢，僅輪詢已上線且已啟用的連接埠',
        ],
        'ports_fdb_purge' => [
            'description' => '連接埠 FDB 項目大於',
            'help' => '由 daily.sh 執行清除',
        ],
        'ports_ipv4_neighbours' => [
            'description' => '連接埠 IPv4 鄰居查詢方法',
            'help' => '檢視連接埠詳細資料時用於查詢 IPv4 鄰居的方法。ARP 會使用 ARP 表尋找具有相符 IP 與 MAC 位址的裝置。Subnet 則僅尋找位於相同子網路的 IP 位址裝置。',
        ],
        'ports_nac_purge' => [
            'description' => '連接埠 NAC 項目超過此時間',
            'help' => '由 daily.sh 執行清除',
        ],
        'ports_page_default' => [
            'description' => '預設連接埠分頁',
            'help' => '在裝置頁面檢視連接埠時預設開啟的分頁',
        ],
        'ports_purge' => [
            'description' => '清除已刪除的連接埠',
            'help' => '由 daily.sh 執行清除',
        ],
        'processor.default_perc_warn' => [
            'description' => '處理器預設百分比警告',
            'help' => '觸發警告前處理器使用率的預設百分比。',
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
            'Job' => [
                'description' => 'Job',
                'help' => '匯出指標的 Job 標籤',
            ],
            'attach_sysname' => [
                'description' => '附加 sysName',
                'help' => '附加裝置的 sysName 資訊至 Prometheus Push Gateway。',
            ],
            'prefix' => [
                'description' => '前綴',
                'help' => '選擇性附加到匯出指標名稱前的文字',
            ],
        ],
        'public_status' => [
            'description' => '公開狀態顯示',
            'help' => '允許不登入的情況下，顯示裝置的狀態資訊。',
        ],
        'routes_max_number' => [
            'description' => '允許探索路由的最大路由數',
            'help' => '若路由表大小超過此數字，將不會探索任何路由',
        ],
        'default_port_group' => [
            'description' => '預設連接埠群組',
            'help' => '新探索到的連接埠將被指派到此連接埠群組。',
        ],
        'nets' => [
            'description' => '自動探索網路',
            'help' => '自動探索裝置的來源網路。',
        ],
        'autodiscovery' => [
            'bgp' => [
                'description' => '啟用 BGP 鄰居探索',
                'help' => '根據 BGP 鄰居新增連線與鄰居',
            ],
            'cdp_exclude' => [
                'platform_regexp' => [
                    'description' => 'CDP 排除平台 regex',
                    'help' => '若 sysName 符合正規表示式，則防止裝置透過 CDP 被新增',
                ],
            ],
            'nets-exclude' => [
                'description' => '要忽略的網路或 IP',
                'help' => '不會被自動探索的網路／IP。同時也會將這些 IP 從自動探索網路中排除',
            ],
            'ospf' => [
                'description' => '啟用 OSPF 鄰居探索',
                'help' => '根據 OSPF 鄰居新增連線與鄰居',
            ],
            'ospfv3' => [
                'description' => '啟用 OSPFv3 鄰居探索',
                'help' => '根據 OSPFv3 鄰居新增連線與鄰居',
            ],
            'xdp' => [
                'description' => '啟用 xDP 探索協定',
                'help' => '使用 LLDP、CDP 等協定探索網路拓撲與鄰居，並將其加入 LibreNMS',
            ],
            'xdp_exclude' => [
                'sysname_regexp' => [
                    'description' => 'xDP 排除 sysName regex',
                    'help' => '若 sysName 符合正規表示式，則防止裝置被新增',
                ],
                'sysdesc_regexp' => [
                    'description' => 'xDP 排除 sysDescr regex',
                    'help' => '若 sysDescr 符合正規表示式，則防止裝置被新增',
                ],
            ],
        ],
        'radius' => [
            'default_roles' => [
                'description' => '預設使用者角色',
                'help' => '設定指派給使用者的角色，除非 Radius 發送了指定角色的屬性',
            ],
            'enforce_roles' => [
                'description' => '登入時強制套用角色',
                'help' => '若啟用，登入時角色會被設為 Filter-ID 屬性或 radius.default_roles 所指定的角色。否則，角色會在使用者建立時設定，之後不再變更。',
            ],
        ],
        'rancid_configs' => [
            'description' => 'RANCID 組態',
            'help' => 'RANCID 組態目錄，用於在裝置頁面顯示組態差異',
        ],
        'rancid_repo_type' => [
            'description' => 'RANCID 儲存庫類型',
            'help' => 'RANCID 使用的儲存庫類型，用於在裝置頁面顯示組態差異',
        ],
        'rancid_repo_url' => [
            'description' => 'RANCID 儲存庫 URL',
            'help' => 'RANCID 儲存庫 URL，用於指向視覺化 bare Git 儲存庫的 GitWeb',
        ],
        'rancid_ignorecomments' => [
            'description' => 'RANCID 忽略註解',
            'help' => '比較 RANCID 組態時忽略註解，用於在裝置頁面顯示組態差異',
        ],
        'reporting' => [
            'error' => [
                'description' => '發送錯誤報告',
                'help' => '將部分錯誤發送給 LibreNMS 以供分析與修正',
            ],
            'usage' => [
                'description' => '發送使用情況報告',
                'help' => '向 LibreNMS 回報使用情況與版本。若要刪除匿名統計，請造訪 about 頁面。您可於 https://stats.librenms.org 檢視統計',
            ],
            'dump_errors' => [
                'description' => '傾印除錯錯誤（將破壞您的安裝）',
                'help' => '傾印通常隱藏的錯誤，讓您身為開發人員能找出並修正可能的問題。',
            ],
            'throttle' => [
                'description' => '限制錯誤報告頻率',
                'help' => '報告僅會每隔指定秒數發送一次。若無此項，當常用程式碼出現錯誤時，回報可能會失控。設為 0 以停用節流。',
            ],
        ],
        'rewrite_if' => [
            'description' => '重寫 ifDescr',
            'help' => '重寫 ifDescr 以移除介面類型與編號，例如 GigabitEthernet0/1 變成 GigabitEthernet',
        ],
        'route_purge' => [
            'description' => '路由記錄大於',
            'help' => '由 daily.sh 執行清除',
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
            'help' => 'RRD 檔案的位置。預設為 LibreNMS 目錄內的 rrd。變更此設定不會移動現有的 RRD 檔案。',
        ],
        'rrd_purge' => [
            'description' => 'RRD 檔案項目大於',
            'help' => '由 daily.sh 執行清除',
        ],
        'rrd_rra' => [
            'description' => 'RRD 格式設定',
            'help' => '未刪除現有 RRD 檔案前無法變更這些設定。不過若您有效能問題，或擁有非常快速且無效能疑慮的 I/O 子系統，理論上可以增加或減少每個 RRA 的大小。',
        ],
        'rrdcached' => [
            'description' => '啟用 rrdcached (socket)',
            'help' => '透過設定 rrdcached socket 的位置來啟用 rrdcached。可為 unix 或網路 socket（unix:/run/rrdcached.sock 或 localhost:42217）',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 路徑',
        ],
        'rrdtool_tune' => [
            'description' => '調整所有 rrd 連接埠檔案使用最大值',
            'help' => '自動調整 rrd 連接埠檔案的最大值',
        ],
        'rrdtool_version' => [
            'description' => '設定您伺服器上 rrdtool 的版本',
            'help' => '1.5.5 以上的任何版本皆支援 LibreNMS 使用的所有功能，請勿設得高於您已安裝的版本',
        ],
        'schedule_type' => [
            'alerting' => [
                'description' => '警報',
                'help' => '警報任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_billing_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'cron' => 'Cron（lnms alerts:notify）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'billing' => [
                'description' => '帳務',
                'help' => '帳務任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_billing_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'cron' => 'Cron（poll-billing.php 與 billing-calculate.php）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'discovery' => [
                'description' => '探索',
                'help' => '探索任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_discovery_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'cron' => 'Cron（lnms device:discover）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'ping' => [
                'description' => 'Fast Ping',
                'help' => 'Fast ping 任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_ping_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'disabled' => '停用（僅在輪詢時 ping）',
                    'cron' => 'Cron（lnms device:ping fast）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'poller' => [
                'description' => '輪詢器',
                'help' => '輪詢器任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_poller_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'cron' => 'Cron（poller.php）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
            'services' => [
                'description' => '服務',
                'help' => '服務任務排程方法。Legacy 會在 crontab 項目存在時使用 cron，並在 legacy 設定選項 service_services_enabled 設為 true 時使用 dispatcher 服務。',
                'options' => [
                    'legacy' => 'Legacy（不限制）',
                    'cron' => 'Cron（check-services.php）',
                    'dispatcher' => 'Dispatcher Service',
                ],
            ],
        ],
        'sensors' => [
            'guess_limits' => [
                'description' => '推測感測器限制',
                'help' => '若啟用，LibreNMS 會嘗試根據感測器類型與數值推測感測器限制。這並非總是準確，可能導致不正確的限制。',
            ],
        ],
        'service_master_timeout' => [
            'description' => '主 Dispatcher 逾時',
            'help' => '主鎖定到期前的時間。若主節點消失，另一個節點需要這麼長的時間才能接手。但若派發工作所花的時間超過逾時，您將會有多個主節點',
        ],
        'service_poller_workers' => [
            'description' => '輪詢器工作程序',
            'help' => '要產生的輪詢器工作程序數量。設定所有節點的預設值。',
        ],
        'service_poller_frequency' => [
            'description' => '輪詢器頻率（警告！）',
            'help' => '多久輪詢一次裝置。設定所有節點的預設值。警告！未修正 RRD 檔案就變更此項會破壞圖表。詳情請參閱文件。',
        ],
        'service_poller_down_retry' => [
            'description' => '裝置離線重試',
            'help' => '若嘗試輪詢時裝置離線，這是重試前的等待時間。設定所有節點的預設值。',
        ],
        'service_discovery_workers' => [
            'description' => '探索工作程序',
            'help' => '要執行的探索工作程序數量。設得太高可能造成負載過重。設定所有節點的預設值。',
        ],
        'service_discovery_frequency' => [
            'description' => '探索頻率',
            'help' => '多久執行一次裝置探索。設定所有節點的預設值。預設為每天 4 次。',
        ],
        'service_services_workers' => [
            'description' => '服務工作程序',
            'help' => '服務工作程序數量。設定所有節點的預設值。',
        ],
        'service_services_frequency' => [
            'description' => '服務頻率',
            'help' => '多久執行一次服務。此項應與輪詢器頻率相符。設定所有節點的預設值。',
        ],
        'service_billing_frequency' => [
            'description' => '帳務頻率',
            'help' => '多久收集一次帳務資料。設定所有節點的預設值。',
        ],
        'service_billing_calculate_frequency' => [
            'description' => '帳務計算頻率',
            'help' => '多久計算一次帳單用量。設定所有節點的預設值。',
        ],
        'service_alerting_frequency' => [
            'description' => '警報頻率',
            'help' => '多久檢查一次警報規則。請注意，資料僅依輪詢器頻率更新。設定所有節點的預設值。',
        ],
        'service_update_enabled' => [
            'description' => '啟用每日維護',
            'help' => '執行 daily.sh 維護指令稿，並於之後重新啟動 dispatcher 服務。設定所有節點的預設值。',
        ],
        'service_update_frequency' => [
            'description' => '維護頻率',
            'help' => '多久執行一次每日維護。預設為 1 天。強烈建議不要變更此項。設定所有節點的預設值。',
        ],
        'service_loglevel' => [
            'description' => '記錄等級',
            'help' => '派發服務的記錄等級。設定所有節點的預設值。',
        ],
        'service_watchdog_enabled' => [
            'description' => '啟用 Watchdog',
            'help' => 'Watchdog 會監控記錄檔，若記錄檔未更新則重新啟動服務。設定所有節點的預設值。',
        ],
        'service_watchdog_log' => [
            'description' => '要監看的記錄檔',
            'help' => '預設為 LibreNMS 記錄檔。設定所有節點的預設值。',
        ],
        'service_health_file' => [
            'description' => '服務健康檔',
            'help' => '用於確保 dispatcher 服務執行中的健康檔路徑',
        ],
        'shorthost_target_length' => [
            'description' => '縮短主機名稱的最大長度',
            'help' => '將主機名稱縮短至最大長度，但永遠保留完整的子網域部分',
        ],
        'show_locations' => [
            'description' => '在導覽列顯示位置',
            'help' => '在導覽列顯示位置',
        ],
        'show_locations_dropdown' => [
            'description' => '在下拉選單顯示位置',
            'help' => '在下拉選單顯示位置',
        ],
        'show_services' => [
            'description' => '在導覽列顯示服務',
            'help' => '在導覽列顯示服務',
        ],
        'site_style' => [
            'description' => '設定站台 css 樣式',
            'options' => [
                'device' => '裝置',
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '傳輸 (優先順序)',
                'help' => '選取已啟用的傳輸方式，並依您希望嘗試的順序排列。',
            ],
            'version' => [
                'description' => '版本 (優先順序)',
                'help' => '選取已啟用的版本，並依您希望嘗試的順序排列。',
            ],
            'community' => [
                'description' => '社群 (優先順序)',
                'help' => '輸入 v1 與 v2c 的 community 字串，並依您希望嘗試的順序排列',
            ],
            'max_oid' => [
                'description' => '最大 OID 數',
                'help' => '每次查詢的最大 OID 數。可於 OS 與裝置層級覆寫。',
            ],
            'max_repeaters' => [
                'description' => '重複擷取最多次數',
                'help' => '設定 SNMP bulk 請求所用的 repeaters',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => '對特定 OID 停用 SNMP bulk',
                    'help' => '對特定 OID 停用 SNMP bulk 操作。一般而言，此項應改在 OS 上設定。格式應為 MIB::OID',
                ],
                'unordered' => [
                    'description' => '允許特定 OID 的 SNMP 回應亂序',
                    'help' => '忽略特定 OID 之 SNMP 回應中的亂序 OID。亂序 OID 可能導致 snmpwalk 期間發生 OID 迴圈。一般而言，此項應改在 OS 上設定。格式應為 MIB::OID',
                ],
            ],
            'port' => [
                'description' => '連接埠',
                'help' => '設定 SNMP 所用的 TCP/UDP 連接埠',
            ],
            'timeout' => [
                'description' => '逾時',
                'help' => 'SNMP 逾時（秒）',
            ],
            'retries' => [
                'description' => '重試次數',
                'help' => '查詢的重試次數',
            ],
            'v3' => [
                'description' => 'SNMP v3 驗證 (優先順序)',
                'help' => '設定 v3 驗證變數，並依您希望嘗試的順序排列',
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
                    'noAuthNoPriv' => '無驗證、無加密',
                    'authNoPriv' => '有驗證、無加密',
                    'authPriv' => '有驗證與加密',
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
                'help' => '獨立於可能對應到該 trap 的動作之外',
            ],
            'eventlog_detailed' => [
                'description' => '啟用詳細記錄',
                'help' => '將 trap 收到的所有 OID 加入事件記錄中',
            ],
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk 路徑',
        ],
        'ssl_certificates' => [
            'auto_discover' => [
                'description' => '自動探索 SSL 憑證',
                'help' => '自動探索 SSL 憑證',
            ],
            'skip_hosts' => [
                'description' => '略過主機',
                'help' => '從 SSL 憑證探索中略過的主機',
            ],
            'days_until_expiry_warning' => [
                'description' => '警告（天）',
                'help' => '距離憑證到期多少天時觸發警告',
            ],
            'days_until_expiry_danger' => [
                'description' => '危險（天）',
                'help' => '距離憑證到期多少天時觸發危險警報',
            ],
        ],
        'sso' => [
            'create_users' => [
                'description' => '建立使用者',
                'help' => '是否應在登入時建立新使用者。',
            ],
            'descr_attr' => [
                'description' => '使用者描述屬性',
                'help' => '包含使用者描述的屬性。',
            ],
            'email_attr' => [
                'description' => '電子郵件屬性',
                'help' => '包含使用者電子郵件地址的屬性。',
            ],
            'group_attr' => [
                'description' => '群組屬性',
                'help' => '使用對應時包含群組資訊的屬性。',
            ],
            'group_delimiter' => [
                'description' => '群組分隔符號',
                'help' => '使用對應群組策略時，用於群組資訊的分隔符號。',
            ],
            'group_filter' => [
                'description' => '群組篩選 Regexp',
                'help' => '使用對應群組策略時，用於篩選群組資訊。',
            ],
            'group_level_map' => [
                'description' => '群組層級對應',
                'help' => '群組對角色的對應。',
            ],
            'group_strategy' => [
                'description' => '群組策略',
                'help' => '群組對應的執行方式。',
            ],
            'level_attr' => [
                'description' => '層級屬性',
                'help' => '使用屬性群組策略時要使用的屬性。',
            ],
            'mode' => [
                'description' => '模式',
                'help' => '應使用環境變數或 HTTP 標頭。',
            ],
            'realname_attr' => [
                'description' => '真實姓名屬性',
                'help' => '包含使用者真實姓名的屬性。',
            ],
            'static_level' => [
                'description' => '靜態層級',
                'help' => '若使用靜態方式，套用給每位具有存取權者的角色層級值。',
            ],
            'trusted_proxies' => [
                'description' => '受信任的 Proxy',
                'help' => '受信任的 proxy 清單。',
            ],
            'update_users' => [
                'description' => '更新使用者',
                'help' => '是否應在登入時更新使用者。',
            ],
            'user_attr' => [
                'description' => '使用者屬性',
                'help' => '包含使用者名稱的屬性。',
            ],
        ],
        'storage_perc_warn' => [
            'description' => '儲存空間預設百分比警告',
            'help' => '觸發警告前儲存空間使用率的預設百分比。0 表示停用警告。',
        ],
        'syslog_filter' => [
            'description' => '過濾包含以下內容的 syslog 訊息',
        ],
        'syslog_purge' => [
            'description' => 'Syslog 項目大於',
            'help' => '由 daily.sh 執行清除',
        ],
        'title_image' => [
            'description' => '標題圖片',
            'help' => 'Overrides the default Title Image.',
        ],
        'traceroute' => [
            'description' => 'traceroute 路徑',
        ],
        'twofactor' => [
            'description' => '雙因素驗證',
            'help' => '允許使用者啟用基於時間 (TOTP) 或基於雜湊訊息驗證 (HOTP) 的一次性密碼 (OTP)',
        ],
        'twofactor_lock' => [
            'description' => '雙因素驗證碼有效時間 (秒)',
            'help' => '若雙因素驗證連續失敗 3 次，允許再次嘗試前需等待的鎖定時間（秒）—— 會提示使用者等待這段時間。設為 0 以停用，將導致帳號永久鎖定，並顯示訊息要使用者聯絡管理員',
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
                'master' => 'Daily',
                'release' => '每月',
            ],
        ],
        'update_on_days' => [
            'description' => '僅在這些日子執行更新',
            'help' => '若有設定（非空），daily.sh 僅會在今天符合以下其中一個值時執行程式碼更新：monday-sunday 或 mon-sun。留空則允許每天更新。',
        ],
        'uptime_warning' => [
            'description' => '如果運作時間低於設定(秒)將裝置顯示警告',
            'help' => 'Shows Device as warning if Uptime is below this value. Default 24h',
        ],
        'virsh' => [
            'description' => 'virsh 路徑',
        ],
        'web_mouseover' => [
            'description' => '啟用滑鼠停留',
            'help' => '在 Web 介面中啟用滑鼠停留圖表',
        ],
        'webui' => [
            'scheduled_maintenance_default_behavior' => [
                'description' => '預設行為',
                'help' => '管理排程維護時，這會是「行為」選項的預設選項。',
            ],
            'alert_map_compact' => [
                'description' => '警報地圖精簡檢視',
                'help' => '使用小型指示器的警報地圖檢視',
            ],
            'alert_map_sort_status' => [
                'description' => '依狀態排序',
                'help' => '依狀態排序警報',
            ],
            'alert_map_use_device_groups' => [
                'description' => '使用裝置群組篩選',
                'help' => '啟用裝置群組篩選的使用',
            ],
            'alert_map_box_size' => [
                'description' => '警報方塊寬度',
                'help' => '輸入完整檢視時方塊大小所需的圖塊寬度（像素）',
            ],
            'availability_map_box_size' => [
                'description' => '可用性區塊寬度',
                'help' => '輸入完整檢視時方塊大小所需的圖塊寬度（像素）',
            ],
            'availability_map_compact' => [
                'description' => '可用性地圖精簡模式',
                'help' => '使用小型指示器的可用性地圖檢視',
            ],
            'availability_map_sort_status' => [
                'description' => '依狀態排序',
                'help' => '以狀態做為裝置與服務的排序',
            ],
            'availability_map_use_device_groups' => [
                'description' => '使用裝置群組篩選器',
                'help' => '啟用裝置群組篩選器',
            ],
            'custom_css' => [
                'description' => '自訂 CSS',
                'help' => '為 Web 介面新增自訂 CSS',
            ],
            'default_dashboard_id' => [
                'description' => '預設資訊看板',
                'help' => '對於沒有設定預設資訊看板的使用者，所要顯示的預設資訊看板',
            ],
            'dynamic_graphs' => [
                'description' => '啟用動態圖表',
                'help' => '啟用動態圖表，可在圖表上縮放與平移',
            ],
            'global_search_result_limit' => [
                'description' => '設定搜尋結果筆數上限',
                'help' => '全域搜尋結果限制',
            ],
            'graph_stacked' => [
                'description' => '使用堆疊圖表',
                'help' => '顯示堆疊圖表而非反轉圖表',
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
            'graph_stat_percentile_disable' => [
                'description' => '全域停用統計圖表的百分位',
                'help' => '停用顯示百分位值與線條（適用於會顯示這些內容的圖表）',
            ],
        ],
        'device_display_default' => [
            'description' => '預設裝置顯示名稱範本',
            'help' => '設定所有裝置的預設顯示名稱（可逐一裝置覆寫）。Hostname/IP：僅顯示裝置被新增時所用的主機名稱或 IP。sysName：僅顯示來自 SNMP 的 sysName。Hostname 或 sysName：顯示主機名稱，但若為 IP，則顯示 sysName。',
            'options' => [
                'hostname' => 'Hostname / IP',
                'sysName_fallback' => 'Hostname，IP 時改用 sysName',
                'sysName' => 'sysName',
                'ip' => 'IP（來自主機名稱 IP 或解析結果）',
            ],
        ],
        'device_location_map_open' => [
            'description' => '開啟位置圖',
            'help' => '預設顯示位置地圖',
        ],
        'device_location_map_show_devices' => [
            'description' => '在位置地圖上顯示裝置',
            'help' => '當位置地圖可見時，在其上顯示所有裝置',
        ],
        'device_location_map_show_device_dependencies' => [
            'description' => '在位置地圖上顯示裝置相依關係',
            'help' => '根據父層相依關係，在位置地圖上顯示裝置之間的連線',
        ],
        'device_stats_avg_factor' => [
            'description' => '平均因子',
            'help' => '我們使用指數加權移動平均函式計算移動平均。此為該函式所用的因子，用以控制目前值對平均的影響程度。值越接近 1，平均變化越快。',
        ],
        'smokeping.integration' => [
            'description' => '啟用',
            'help' => '啟用 Smokeping 整合',
        ],
        'smokeping.dir' => [
            'description' => 'RRD 存放路徑',
            'help' => 'Smokeping RRD 的完整路徑',
        ],
        'smokeping.pings' => [
            'description' => 'Ping 數量',
            'help' => 'Smokeping 中設定的 ping 次數',
        ],
        'smokeping.url' => [
            'description' => 'Smokeping URL 位址',
            'help' => 'Smokeping GUI 的完整 URL',
        ],
    ],
    'twofactor' => [
        'description' => '啟用雙因素驗證',
        'help' => '啟用內建的雙因素驗證。您必須設定每個帳號才能使其生效。',
    ],
    'units' => [
        'days' => '日',
        'ms' => '微秒',
        'seconds' => '秒',
        'percent' => '%',
    ],
    'validate' => [
        'boolean' => ':value 不是有效的布林值',
        'color' => ':value 不是有效的十六進位色碼',
        'email' => ':value 不是有效的電子郵件',
        'float' => ':value 不是浮點數',
        'integer' => ':value 不是整數',
        'password' => '密碼不正確',
        'select' => ':value 不是允許的值',
        'text' => ':value 不被允許',
        'array' => '格式無效',
        'password-array' => '格式無效',
        'executable' => ':value 不是有效的可執行檔',
        'directory' => ':value 不是有效的目錄',
    ],
];
