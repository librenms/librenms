<?php

return [
    'database_connect' => [
        'title' => '連線資料庫時發生錯誤',
    ],
    'database_inconsistent' => [
        'title' => '資料庫不一致',
        'header' => '在資料庫錯誤檢測過程中發現不一致，請先修正後再繼續。',
    ],
    'dusk_unsafe' => [
        'title' => '在正式環境中執行 Dusk 並不安全',
        'message' => '請執行「:command」移除 Dusk，或若您是開發人員，請設定正確的 APP_ENV。',
    ],
    'file_write_failed' => [
        'title' => '錯誤：無法寫入檔案',
        'message' => '無法寫入檔案（:file）。請檢查檔案權限，以及必要時檢查 SELinux / AppArmor 設定。',
    ],
    'host_exists' => [
        'hostname_exists' => '裝置 :hostname 已經存在',
        'ip_exists' => '無法新增 :hostname，因為已有裝置 :existing 使用相同 IP :ip',
        'sysname_exists' => '因 sysName 重複（:sysname），已存在裝置 :hostname',
    ],
    'host_unreachable' => [
        'unpingable' => '無法 Ping :hostname（:ip）',
        'unsnmpable' => '無法連線至 :hostname，請檢查 SNMP 設定與連線可達性',
        'unresolvable' => '主機名稱無法解析為 IP',
        'no_reply_community' => 'SNMP :version：使用共同社群 :credentials 無回應',
        'no_reply_credentials' => 'SNMP :version：使用認證資料 :credentials 無回應',
    ],
    'ldap_missing' => [
        'title' => '缺少 PHP LDAP 支援',
        'message' => 'PHP 未啟用 LDAP 支援，請安裝或啟用 PHP LDAP 擴充套件',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => '已超過 :seconds 秒的最長執行時間|已超過 :seconds 秒的最長執行時間',
        'message' => '頁面載入時間超過 PHP 設定的最長執行時間，請增加 php.ini 中的 max_execution_time 或提升伺服器效能。',
    ],
    'unserializable_route_cache' => [
        'title' => 'PHP 版本不符造成錯誤',
        'message' => '您的 Web 伺服器 PHP 版本（:web_version）與 CLI PHP 版本（:cli_version）不一致',
    ],
    'snmp_version_unsupported' => [
        'message' => '不支援的 SNMP 版本「:snmpver」，必須為 v1、v2c 或 v3',
    ],
];
