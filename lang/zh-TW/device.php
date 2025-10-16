<?php

return [
    'attributes' => [
        'features' => '作業系統功能',
        'hardware' => '硬體',
        'icon' => '圖示',
        'ip' => 'IP 位址',
        'location' => '位置',
        'os' => '裝置作業系統',
        'serial' => '序號',
        'sysName' => 'sysName',
        'version' => '作業系統版本',
        'type' => '裝置類型',
    ],

    'vm_host' => 'VM 主機',
    'scheduled_maintenance' => '排程維護',

    'edit' => [
        'delete_device' => '刪除裝置',
        'rediscover_title' => '安排此裝置由輪詢器立即重新探索',
        'rediscover' => '重新探索裝置',

        'hostname_title' => '變更用於名稱解析的主機名稱',
        'hostname_ip' => '主機名稱 / IP 位址',

        'display_title' => '此裝置的顯示名稱，請保持簡短。可用的替代字串：hostname、sysName、sysName_fallback、ip（例如「:sysName」）',
        'display_name' => '顯示名稱',
        'system_default' => '系統預設',

        'overwrite_ip_title' => '使用此 IP 取代解析到的位址進行輪詢',
        'overwrite_ip' => '覆寫 IP（不建議使用）',

        'description' => '描述',
        'type' => '類型',

        'override_sysLocation' => '覆寫 sysLocation',
        'coordinates_title' => '若要設定座標，請輸入 [緯度,經度]',

        'override_sysContact' => '覆寫 sysContact',

        'depends_on' => '此裝置相依於',
        'none' => '無',

        'poller_group' => '輪詢器群組',
        'poller_group_general' => '一般',
        'default_poller' => '（預設輪詢器）',

        'disable_polling_alerting' => '停用輪詢與警報',
        'disable_alerting' => '停用警報',

        'ignore_alert_tag' => '忽略警報標籤',
        'ignore_alert_tag_title' => "將裝置標記為忽略警報，警報檢查仍會執行。\n不過，警報規則中可以讀取忽略標籤。\n如果設定了 `devices.ignore = 0` 或 `macros.device = 1` 且啟用了忽略警報標籤，警報規則將不會符合。",

        'ignore_device_status' => '忽略裝置狀態',
        'ignore_device_status_title' => '將裝置標記為忽略狀態，它將始終顯示為在線。',

        'save' => '儲存',

        'size_on_disk' => '磁碟使用大小',
        'rrd_files' => 'RRD 檔案',
        'last_polled' => '上次輪詢時間',
        'last_discovered' => '上次探索時間',

        'rediscover_error' => '將此裝置設定為重新探索時發生錯誤',
    ],
];
