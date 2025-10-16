<?php

return [
    'config:clear' => [
        'description' => '清除設定快取。這會讓自上次完整載入後的變更，反映到目前的設定中。',
    ],
    'config:get' => [
        'description' => '取得設定值',
        'arguments' => [
            'setting' => '要以 dot 標記法取得的設定鍵（範例：snmp.community.0）',
        ],
        'options' => [
            'dump' => '以 JSON 輸出整份設定',
        ],
    ],
    'config:list' => [
        'description' => '列出並搜尋設定項目',
        'arguments' => [
            'search' => '搜尋設定（比對名稱或描述）',
        ],
        'not_found' => '找不到符合「:search」的設定',
    ],
    'config:set' => [
        'description' => '設定（或清除）設定值',
        'arguments' => [
            'setting' => '要以 dot 標記法設定的鍵（範例：snmp.community.0）。若要附加至陣列，於結尾加上 .+',
            'value' => '要設定的值；若省略此值則會清除此設定',
        ],
        'options' => [
            'ignore-checks' => '忽略所有安全檢查',
        ],
        'confirm' => '要將 :setting 重設為預設值嗎？',
        'forget_from' => '要自 :parent 移除 :path 嗎？',
        'errors' => [
            'append' => '無法附加到非陣列型態的設定',
            'failed' => '無法設定 :setting',
            'invalid' => '這不是有效的設定，請檢查輸入內容',
            'invalid_os' => '指定的作業系統（:os）不存在',
            'nodb' => '資料庫未連線',
            'no-validation' => '無法設定 :setting，缺少驗證定義。',
        ],
    ],
    'db:seed' => [
        'existing_config' => '資料庫已含既有設定。要繼續嗎？',
    ],
    'dev:check' => [
        'description' => 'LibreNMS 程式碼檢查。不帶選項執行時會執行所有檢查',
        'arguments' => [
            'check' => '只執行指定的檢查：:checks',
        ],
        'options' => [
            'commands' => '僅列出將會執行的指令，不實際進行檢查',
            'db' => '執行需要資料庫連線的單元測試',
            'fail-fast' => '遇到任何失敗即停止',
            'full' => '忽略變更檔案篩選，執行完整檢查',
            'module' => '指定模組進行測試。隱含啟用 unit、--db、--snmpsim',
            'os' => '指定作業系統進行測試。可使用正規表示式或以逗號分隔清單。隱含啟用 unit、--db、--snmpsim',
            'os-modules-only' => '指定 OS 時略過 OS 偵測測試，加速非偵測相關變更的測試時間。',
            'quiet' => '除非發生錯誤，否則隱藏輸出',
            'snmpsim' => '於單元測試中使用 snmpsim',
        ],
    ],
    'dev:simulate' => [
        'description' => '使用測試資料模擬裝置',
        'arguments' => [
            'file' => '要更新或加入到 LibreNMS 的 snmprec 檔案（僅檔名本體）。若未指定，將不新增或更新裝置。',
        ],
        'options' => [
            'multiple' => '使用社群字串作為主機名稱（而非 snmpsim）',
            'remove' => '停止後移除裝置',
        ],
        'added' => '已新增裝置 :hostname (:id)',
        'exit' => '按 Ctrl-C 以停止',
        'removed' => '已移除裝置 :id',
        'updated' => '已更新裝置 :hostname (:id)',
        'setup' => '在 :dir 建立 snmpsim 虛擬環境',
    ],
    'device:add' => [
        'description' => '新增裝置',
        'arguments' => [
            'device spec' => '要新增的主機名稱或 IP',
        ],
        'options' => [
            'v1' => '使用 SNMP v1',
            'v2c' => '使用 SNMP v2c',
            'v3' => '使用 SNMP v3',
            'display-name' => "用於顯示的裝置名稱，預設為主機名稱。\n可用簡易範本與替代：{{ \$hostname }}、{{ \$sysName }}、{{ \$sysName_fallback }}、{{ \$ip }}",
            'force' => '直接新增裝置，不進行安全檢查',
            'group' => '輪詢器群組（分散式輪詢）',
            'ping-fallback' => '若不回應 SNMP，則以僅 Ping 模式加入',
            'port-association-mode' => '設定連接埠對應方式。Linux/Unix 建議使用 ifName',
            'community' => 'SNMP v1 或 v2 社群字串',
            'transport' => '用於連線至裝置的傳輸協定',
            'port' => 'SNMP 傳輸連接埠',
            'security-name' => 'SNMPv3 安全性使用者名稱',
            'auth-password' => 'SNMPv3 驗證密碼',
            'auth-protocol' => 'SNMPv3 驗證協定',
            'privacy-protocol' => 'SNMPv3 隱私（加密）協定',
            'privacy-password' => 'SNMPv3 隱私（加密）密碼',
            'ping-only' => '新增僅 Ping 的裝置',
            'os' => '僅 Ping：指定作業系統',
            'hardware' => '僅 Ping：指定硬體',
            'sysName' => '僅 Ping：指定 sysName',
        ],
        'validation-errors' => [
            'port.between' => '連接埠應介於 1 到 65535',
            'poller-group.in' => '指定的輪詢器群組不存在',
        ],
        'messages' => [
            'save_failed' => '無法儲存裝置 :hostname',
            'try_force' => '可嘗試使用 --force 以略過安全檢查',
            'added' => '已新增裝置 :hostname (:device_id)',
        ],
    ],
    'device:ping' => [
        'description' => 'Ping 裝置並記錄回應資料',
        'arguments' => [
            'device spec' => '要 Ping 的裝置：<裝置 ID>、<主機名稱/IP>、all 其一',
        ],
    ],
    'device:poll' => [
        'description' => '依據探索結果自裝置擷取（輪詢）資料',
        'arguments' => [
            'device spec' => '要輪詢的裝置指定：device_id、hostname、萬用字元 (*)、odd、even、all',
        ],
        'options' => [
            'modules' => '指定要執行的單一模組。可用逗號分隔，多層子模組以 / 指定',
            'no-data' => '不更新資料存放區（RRD、InfluxDB 等）',
        ],
        'errors' => [
            'db_connect' => '無法連線到資料庫。請確認資料庫服務執行中且連線設定正確。',
            'db_auth' => '無法連線到資料庫。請確認認證資訊：:error',
            'no_devices' => '找不到符合指定條件的裝置。',
            'none_up' => '裝置離線，無法輪詢。|所有裝置皆離線，無法輪詢。',
            'none_polled' => '沒有任何裝置被輪詢。',
        ],
        'polled' => '已於 :time 內輪詢 :count 台裝置',
    ],
    'device:remove' => [
        'doesnt_exists' => '沒有此裝置：:device',
    ],
    'key:rotate' => [
        'description' => '輪替 APP_KEY，會以舊金鑰解密所有已加密資料後，使用 .env 中的新 APP_KEY 重新加密並儲存。',
        'arguments' => [
            'old_key' => '舊的 APP_KEY（可用來解密既有資料）',
        ],
        'options' => [
            'generate-new-key' => '若 .env 尚未設定新金鑰，使用 .env 的 APP_KEY 解密資料、產生新金鑰並寫入 .env',
            'forgot-key' => '若沒有舊金鑰，必須刪除所有加密資料，才能繼續使用部分 LibreNMS 功能',
        ],
        'destroy' => '要刪除所有加密的設定資料嗎？',
        'destroy_confirm' => '只有在找不到舊 APP_KEY 時才刪除所有加密資料！',
        'cleared-cache' => '偵測到設定已被快取，已清除快取以確保 APP_KEY 正確。請重新執行 lnms key:rotate',
        'backup_keys' => '請記錄「兩把」金鑰！若發生問題，請在 .env 設定新金鑰，並將舊金鑰作為參數傳給此指令',
        'backup_key' => '請記錄此金鑰！存取加密資料需要它',
        'backups' => '此指令可能造成無法復原的資料遺失，並使所有瀏覽器工作階段失效。請先確保已有備份。',
        'confirm' => '我已備份並要繼續',
        'decrypt-failed' => '無法解密 :item，已略過',
        'failed' => '解密項目失敗。請將新金鑰設為 APP_KEY，並以舊金鑰為參數再次執行此指令。',
        'current_key' => '目前 APP_KEY：:key',
        'new_key' => '新的 APP_KEY：:key',
        'old_key' => '舊的 APP_KEY：:key',
        'save_key' => '要將新金鑰寫入 .env 嗎？',
        'success' => '金鑰輪替成功！',
        'validation-errors' => [
            'not_in' => ':attribute 不得與目前的 APP_KEY 相同',
            'required' => '必須提供舊金鑰或 --generate-new-key 其一。',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => '所選的 :option 無效，應為下列其一：:values',
        ],
    ],
    'maintenance:cleanup-networks' => [
        'delete' => '正在刪除 :count 個未使用的網路',
    ],
    'maintenance:fetch-ouis' => [
        'description' => '抓取 MAC OUI 並快取，以顯示 MAC 位址的廠商名稱',
        'options' => [
            'force' => '忽略任何阻止執行此指令的設定或鎖定',
            'wait' => '等待隨機時間；排程器用以避免伺服器負載過高',
        ],
        'disabled' => '已停用 Mac OUI 整合（:setting）',
        'enable_question' => '要啟用 Mac OUI 整合與排程抓取嗎？',
        'recently_fetched' => 'MAC OUI 資料庫剛更新過，略過本次更新。',
        'waiting' => '等待 :minutes 分鐘後再嘗試更新 MAC OUI|等待 :minutes 分鐘後再嘗試更新 MAC OUI',
        'starting' => '開始將 Mac OUI 儲存至資料庫',
        'downloading' => '下載中',
        'processing' => '處理 CSV',
        'saving' => '儲存結果',
        'success' => '已成功更新 OUI/廠商對應。修改了 :count 筆 OUI|已成功更新。修改了 :count 筆 OUI',
        'error' => '處理 Mac OUI 時發生錯誤：',
        'vendor_update' => '新增 OUI :oui（:vendor）',
    ],
    'plugin:disable' => [
        'description' => '停用符合名稱的所有外掛',
        'arguments' => [
            'plugin' => '要停用的外掛名稱，或輸入 "all" 停用全部外掛',
        ],
        'already_disabled' => '外掛已停用',
        'disabled' => '已停用 :count 個外掛|已停用 :count 個外掛',
        'failed' => '停用外掛失敗',
    ],
    'plugin:enable' => [
        'description' => '啟用符合名稱的最新外掛',
        'arguments' => [
            'plugin' => '要啟用的外掛名稱，或輸入 "all" 停用全部外掛',
        ],
        'already_enabled' => '外掛已啟用',
        'enabled' => '已啟用 :count 個外掛|已啟用 :count 個外掛',
        'failed' => '啟用外掛失敗',
    ],
    'port:tune' => [
        'description' => '調整連接埠的 RRD 檔，使其最大傳輸率受 ifSpeed 限制',
        'arguments' => [
            'device spec' => '要調整的裝置指定：device_id、hostname、萬用字元 (*)、odd、even、all',
            'ifname' => '要比對的連接埠 ifName，可用 all 或 * 作為萬用字元',
        ],
        'device' => '裝置 :device：',
        'port' => '正在調整連接埠 :port',
    ],
    'report:devices' => [
        'description' => '輸出裝置相關資料',
        'columns' => '資料表欄位：',
        'synthetic' => '額外欄位：',
        'counts' => '關聯計數：',
        'arguments' => [
            'device spec' => '要輪詢的裝置指定：device_id、hostname、萬用字元 (*)、odd、even、all',
        ],
        'options' => [
            'list-fields' => '列出可用欄位清單',
            'fields' => '以逗號分隔的欄位清單。可用：資料庫中的裝置欄位名稱、關聯計數（如 ports_count）、以及 displayName。JSON 輸出不使用此項。',
            'output' => '指定輸出格式：:types',
            'no-header' => '不要輸出表頭',
            'relationships' => '以逗號分隔的關聯清單要包含於輸出中（僅用於 JSON 輸出）',
            'list-relationships' => '列出／說明可用的關聯',
            'all-relationships' => '包含所有關聯。-r, --relationships 具有優先權。',
            'devices-as-array' => '以 JSON 陣列回傳，而非每列一筆 JSON',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => '請使用 --probes 或 --targets 其中之一',
        'config-insufficient' => '要產生可用於 smokeping 的設定，必須先在設定中設好「smokeping.probes」、「fping」與「fping6」',
        'dns-fail' => '無法解析，已自設定中省略',
        'description' => '產生可用於 smokeping 的設定檔',
        'header-first' => '此檔案由「lnms smokeping:generate」自動產生',
        'header-second' => '本機變更可能在未通知或未備份的情況下被覆寫',
        'header-third' => '更多資訊請見 https://docs.librenms.org/Extensions/Smokeping/',
        'no-devices' => '找不到符合的裝置——裝置不可為停用狀態。',
        'no-probes' => '至少需要一個 probe。',
        'options' => [
            'probes' => '產生 probe 清單——用於將 smokeping 設定拆分為多個檔案。與 "--targets" 衝突',
            'targets' => '產生 target 清單——用於將 smokeping 設定拆分為多個檔案。與 "--probes" 衝突',
            'no-header' => '不要在檔案開頭加入註解樣板',
            'no-dns' => '略過 DNS 解析',
            'single-process' => 'smokeping 僅使用單一程序',
            'compat' => '【已淘汰】模擬 gen_smokeping.php 的行為',
        ],
    ],
    'snmp:fetch' => [
        'description' => '對裝置執行 SNMP 查詢',
        'arguments' => [
            'device spec' => '要輪詢的裝置指定：device_id、hostname、萬用字元 (*)、odd、even、all',
            'oid(s)' => '一或多個要擷取的 SNMP OID。應為 MIB::oid 或數字型 OID',
        ],
        'failed' => 'SNMP 指令執行失敗！',
        'numeric' => '數字',
        'oid' => 'OID',
        'options' => [
            'output' => '指定輸出格式：:formats',
            'numeric' => '以數字表示 OID',
            'depth' => '彙整 SNMP 表格的階層深度。通常等於索引中項目的數量',
        ],
        'not_found' => '找不到裝置',
        'textual' => '文字',
        'value' => '數值',
    ],
    'translation:generate' => [
        'description' => '產生前端使用的最新 JSON 語系檔',
    ],
    'user:add' => [
        'description' => '新增本機使用者；只有在 auth 設為 mysql 時，才能使用此帳號登入',
        'arguments' => [
            'username' => '使用者登入的帳號名稱',
        ],
        'options' => [
            'descr' => '使用者描述',
            'email' => '使用者的電子郵件',
            'password' => '使用者密碼；若未提供，將提示輸入',
            'full-name' => '使用者全名',
            'role' => '設定使用者角色：:roles',
        ],
        'password-request' => '請輸入該使用者的密碼',
        'success' => '已成功新增使用者：:username',
        'wrong-auth' => '警告！由於未使用 MySQL 驗證，您將無法以此使用者登入',
    ],
    'maintenance:database-cleanup' => [
        'description' => '清理資料庫中的孤立項目。',
    ],
];
