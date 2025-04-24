<?php

return [
    'database_connect' => [
        'title' => '連線到資料庫發生錯誤',
    ],
    'database_inconsistent' => [
        'title' => '資料庫不一致',
        'header' => '在資料庫連線錯誤時發現資料庫不一致，請修正後繼續。',
    ],
    'dusk_unsafe' => [
        'title' => '在正式環境中執行 Dusk 是不安全的',
        'message' => '執行 ":command" 以移除 Dusk，或如果您是開發人員，請設定適當的 APP_ENV',
    ],
    'file_write_failed' => [
        'title' => '錯誤：無法寫入檔案',
        'message' => '無法寫入檔案 (:file)。請檢查權限和 SELinux/AppArmor（若有啟用）。',
    ],
    'host_exists' => [
        'hostname_exists' => '裝置 :hostname 已存在',
        'ip_exists' => '無法新增 :hostname，已有裝置 :existing 使用此 IP :ip',
        'sysname_exists' => '因重複的系統名稱 :sysname，已有裝置 :hostname',
    ],
    'host_unreachable' => [
        'unpingable' => '無法 ping 到 :hostname (:ip)',
        'unsnmpable' => '無法連線到 :hostname，請檢查 SNMP 設定和 SNMP 可達性',
        'unresolvable' => '主機名稱無法解析為 IP',
        'no_reply_community' => 'SNMP :version: 使用社群字串 :credentials 無回應',
        'no_reply_credentials' => 'SNMP :version: 使用憑證 :credentials 無回應',
    ],
    'ldap_missing' => [
        'title' => '缺少 PHP LDAP 支援',
        'message' => 'PHP 不支援 LDAP，請安裝或啟用 PHP LDAP 擴充模組',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => '超過最大執行時間 :seconds 秒|超過最大執行時間 :seconds 秒',
        'message' => '頁面載入超過您在 PHP 中設定的最大執行時間。請增加 php.ini 中的 max_execution_time 或改善伺服器硬體',
    ],
    'unserializable_route_cache' => [
        'title' => 'PHP 版本不符導致錯誤',
        'message' => '您的網頁伺服器運行的 PHP 版本 (:web_version) 與 CLI 版本 (:cli_version) 不符',
    ],
    'snmp_version_unsupported' => [
        'message' => '不支援的 SNMP 版本 ":snmpver"，必須是 v1、v2c 或 v3',
    ],
];
