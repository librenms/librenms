<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    // Librenms specific
    'alpha_space' => ':attribute 只能包含英文字母、數字、底線與空白。',
    'ip_or_hostname' => ':attribute 必須是有效的 IP 位址 / 網段或主機名稱。',
    'is_regex' => ':attribute 不是有效的正規表示式。',
    'array_keys_not_empty' => ':attribute 含有空的陣列鍵。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'results' => [
        'autofix' => '嘗試自動修復',
        'fix' => '修復',
        'fixed' => '已完成修復，請重新整理以再次執行驗證。',
        'fetch_failed' => '無法擷取驗證結果',
        'backend_failed' => '無法從後端載入資料，請檢查主控台錯誤。',
        'invalid_fixer' => '無效的修復工具',
        'show_all' => '顯示全部',
        'show_less' => '顯示較少',
        'validate' => '驗證',
        'validating' => '正在驗證',
    ],
    'validations' => [
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => '您指定的 rrdtool 版本比目前安裝的版本新。設定值：:config_version，已安裝版本：:installed_version',
                'fix' => '請在 config.php 檔案中註解或刪除 $config[\'rrdtool_version\'] = \':version\';',
                'ok' => 'rrdtool 版本正確',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => ':socket 不存在，rrdcached 連線測試失敗',
                'fail_port' => '無法連線到 rrdcached 伺服器的通訊埠 :port',
                'ok' => '已成功連線至 rrdcached',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => '您的 RRD 目錄由 root 擁有，建議更改為非 root 使用者。',
                'fail_mode' => '您的 RRD 目錄權限未設定為 0775。',
                'ok' => 'rrd_dir 可寫入',
            ],
        ],
        'database' => [
            'CheckDatabaseConnected' => [
                'fail' => '無法連線到資料庫',
                'fail_connect' => '無法連線到資料庫。請確認資料庫伺服器已啟動，且連線資訊正確。請檢查環境設定或 :env_file 中的 DB_HOST、DB_PORT 與 DB_NAME。',
                'fail_access' => '已連線至資料庫，但使用者沒有存取權限。請執行 SQL 指令以授予權限（若資料庫為遠端，請將 localhost 改為實際主機名稱）。',
                'fail_auth' => '資料庫帳號或密碼錯誤。請在環境設定或 :env_file 中檢查 DB_USERNAME 與 DB_PASSWORD。',
                'ok' => '資料庫已成功連線',
            ],
            'CheckDatabaseTableNamesCase' => [
                'fail' => '您的 MySQL 設定中，lower_case_table_names 被設為 1 或 true。',
                'fix' => '請在 MySQL 設定檔的 [mysqld] 區段中設定 lower_case_table_names=0。',
                'ok' => 'lower_case_table_names 設定正確',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => ':server 版本必須為 :min（自 :date 起為最低支援版本）。',
                'fix' => '請將 :server 更新至支援版本，建議版本為 :suggested。',
                'ok' => 'SQL Server 符合最低版本要求',
            ],
            'CheckMysqlEngine' => [
                'fail' => '部分資料表未使用建議的 InnoDB 引擎，可能會導致問題。',
                'tables' => '資料表',
                'ok' => 'MySQL 引擎設定最佳',
            ],
            'CheckSqlServerTime' => [
                'fail' => "此伺服器與 MySQL 資料庫時間不同步\nMySQL 時間：:mysql_time\nPHP 時間：:php_time",
                'ok' => 'MySQL 與 PHP 時間一致',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => '您的資料庫版本已過時！',
                'fail_legacy_outdated' => '您的資料庫架構版本 (:current) 落後於最新版本 (:latest)。',
                'fix_legacy_outdated' => '請手動執行 ./daily.sh，並檢查是否有錯誤。',
                'warn_extra_migrations' => '您的資料庫架構有額外的遷移紀錄 (:migrations)。若您剛從每日版本切換到穩定版本，這屬於版本過渡狀態，將在下個版本中解決。',
                'warn_legacy_newer' => '您的資料庫架構版本 (:current) 新於預期版本 (:latest)。若您剛從每日版本切換到穩定版本，這屬於版本過渡狀態，將在下個版本中解決。',
                'ok' => '資料庫架構為最新版本',
            ],
            'CheckSchemaCollation' => [
                'ok' => '資料庫與欄位定序設定正確',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => '已全域啟用分散式輪詢設定',
                'not_enabled' => '您尚未啟用 distributed_poller',
                'not_enabled_globally' => '您尚未全域啟用 distributed_poller',
            ],
            'CheckMemcached' => [
                'not_configured_host' => '您尚未設定 distributed_poller_memcached_host',
                'not_configured_port' => '您尚未設定 distributed_poller_memcached_port',
                'could_not_connect' => '無法連線至 memcached 伺服器',
                'ok' => '成功連線至 memcached',
            ],
            'CheckRrdcached' => [
                'fail' => '您尚未啟用 rrdcached',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => '輪詢器未執行。在過去 :interval 秒內沒有偵測到輪詢器執行。',
                'both_fail' => 'Dispatcher Service 與 Python Wrapper 最近同時處於運行中狀態，可能導致重複輪詢。',
                'ok' => '已找到運行中的輪詢器',
            ],
            'CheckDispatcherService' => [
                'fail' => '未找到運行中的調度器節點',
                'ok' => 'Dispatcher Service 已啟用',
                'nodes_down' => '部分調度器節點近期未簽入',
                'not_detected' => '未偵測到 Dispatcher Service',
                'warn' => 'Dispatcher Service 有啟用，但近期未使用',
            ],
            'CheckLocking' => [
                'fail' => '快取伺服器發生問題：:message',
                'ok' => '鎖定功能正常',
            ],
            'CheckPythonWrapper' => [
                'fail' => '未找到運行中的 Python Wrapper 輪詢器',
                'no_pollers' => '未找到 Python Wrapper 輪詢器',
                'cron_unread' => '無法讀取 cron 檔案',
                'ok' => 'Python 輪詢器包裝器正在運行',
                'nodes_down' => '部分輪詢器節點近期未簽入',
                'not_detected' => 'Python Wrapper cron 項目不存在',
            ],
            'CheckRedis' => [
                'bad_driver' => '目前鎖定使用 :driver，建議設定 CACHE_STORE=redis',
                'ok' => 'Redis 功能正常',
                'unavailable' => 'Redis 不可用',
            ],
        ],
    ],
];
