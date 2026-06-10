<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => '已指派的群組',
                'help' => '此節點只會對這些輪詢器群組內的裝置進行處理。',
            ],
            'poller_enabled' => [
                'description' => '啟用輪詢器',
                'help' => '在此節點啟用輪詢器工作程序。',
            ],
            'poller_workers' => [
                'description' => '輪詢器工作數',
                'help' => '此節點要啟動的輪詢器工作程序數量。',
            ],
            'poller_frequency' => [
                'description' => '輪詢頻率（警告！）',
                'help' => '此節點對裝置進行輪詢的頻率。警告：若未同步調整 RRD 檔案就更改此值，將會導致圖表毀損。詳情請參閱文件。',
            ],
            'poller_down_retry' => [
                'description' => '裝置離線重試時間',
                'help' => '若輪詢時裝置離線，這是等待後再次嘗試的時間。',
            ],
            'discovery_enabled' => [
                'description' => '啟用探索',
                'help' => '在此節點啟用探索工作程序。',
            ],
            'discovery_workers' => [
                'description' => '探索工作數',
                'help' => '此節點要啟動的探索工作程序數量。設定過高可能導致過載。',
            ],
            'discovery_frequency' => [
                'description' => '探索頻率',
                'help' => '此節點執行裝置探索的頻率。預設為每天 4 次。',
            ],
            'services_enabled' => [
                'description' => '啟用服務檢查',
                'help' => '在此節點啟用服務檢查工作程序。',
            ],
            'services_workers' => [
                'description' => '服務檢查工作數',
                'help' => '此節點的服務檢查工作程序數量。',
            ],
            'services_frequency' => [
                'description' => '服務檢查頻率',
                'help' => '此節點執行服務檢查的頻率。應與輪詢器頻率一致。',
            ],
            'billing_enabled' => [
                'description' => '啟用計費',
                'help' => '在此節點啟用計費工作程序。',
            ],
            'billing_frequency' => [
                'description' => '計費頻率',
                'help' => '此節點收集計費資料的頻率。',
            ],
            'billing_calculate_frequency' => [
                'description' => '計費計算頻率',
                'help' => '此節點計算計費用量的頻率。',
            ],
            'alerting_enabled' => [
                'description' => '啟用警報',
                'help' => '在此節點啟用警報工作程序。',
            ],
            'alerting_frequency' => [
                'description' => '警報檢查頻率',
                'help' => '此節點檢查警報規則的頻率。注意：資料僅會依輪詢器頻率更新。',
            ],
            'ping_enabled' => [
                'description' => '啟用快速 Ping',
                'help' => '快速 Ping 只會對裝置發送 Ping 以檢查是否在線。',
            ],
            'ping_frequency' => [
                'description' => 'Ping 頻率',
                'help' => '此節點檢查 Ping 的頻率。警告：更改此值必須配合其他調整，詳情請參閱快速 Ping 文件。',
            ],
            'update_enabled' => [
                'description' => '啟用每日維護',
                'help' => '執行 daily.sh 維護腳本，並在完成後重新啟動調度服務。',
            ],
            'update_frequency' => [
                'description' => '維護頻率',
                'help' => '此節點執行每日維護的頻率。預設為 1 天一次，強烈建議不要更改此值。',
            ],
            'loglevel' => [
                'description' => '記錄層級',
                'help' => '調度服務的記錄層級。',
            ],
            'watchdog_enabled' => [
                'description' => '啟用看門狗',
                'help' => '看門狗會監控記錄檔，若長時間未更新則重新啟動服務。',
            ],
            'watchdog_log' => [
                'description' => '監控的記錄檔',
                'help' => '預設為 LibreNMS 的記錄檔。',
            ],
        ],
        'units' => [
            'seconds' => '秒',
            'workers' => '工作程序',
        ],
    ],
];
