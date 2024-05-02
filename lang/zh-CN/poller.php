<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => '分配的组',
                'help' => '此节点仅对这些轮询器组中的设备执行操作。',
            ],
            'poller_enabled' => [
                'description' => '轮询器启用',
                'help' => '在此节点上启用轮询器工作者。',
            ],
            'poller_workers' => [
                'description' => '轮询器工作者',
                'help' => '在此节点上生成的轮询器工作者数量。',
            ],
            'poller_frequency' => [
                'description' => '轮询器频率（警告！）',
                'help' => '在此节点上轮询设备的频率。警告！在不修复 rrd 文件的情况下更改此设置将导致图表损坏。请参阅文档了解更多信息。',
            ],
            'poller_down_retry' => [
                'description' => '设备故障重试',
                'help' => '如果尝试在此节点上轮询时设备处于离线状态。这是在重试之前等待的时间。',
            ],
            'discovery_enabled' => [
                'description' => '发现启用',
                'help' => '在此节点上启用发现工作者。',
            ],
            'discovery_workers' => [
                'description' => '发现工作者',
                'help' => '在此节点上运行的发现工作者数量。设置过高可能导致过载。',
            ],
            'discovery_frequency' => [
                'description' => '发现频率',
                'help' => '在此节点上运行设备发现的频率。默认每天 4 次。',
            ],
            'services_enabled' => [
                'description' => '服务启用',
                'help' => '在此节点上启用服务工作者。',
            ],
            'services_workers' => [
                'description' => '服务工作者',
                'help' => '在此节点上的服务工作者数量。',
            ],
            'services_frequency' => [
                'description' => '服务频率',
                'help' => '在此节点上运行服务的频率。这应与轮询器频率匹配。',
            ],
            'billing_enabled' => [
                'description' => '计费启用',
                'help' => '在此节点上启用计费工作者。',
            ],
            'billing_frequency' => [
                'description' => '计费频率',
                'help' => '在此节点上收集计费数据的频率。',
            ],
            'billing_calculate_frequency' => [
                'description' => '计费计算频率',
                'help' => '在此节点上计算账单使用的频率。',
            ],
            'alerting_enabled' => [
                'description' => '告警启用',
                'help' => '在此节点上启用告警工作者。',
            ],
            'alerting_frequency' => [
                'description' => '告警频率',
                'help' => '在此节点上检查告警规则的频率。请注意，数据仅根据轮询器频率更新。',
            ],
            'ping_enabled' => [
                'description' => '快速 Ping 启用',
                'help' => '快速 Ping 仅用于检查设备是否在线或离线',
            ],
            'ping_frequency' => [
                'description' => 'Ping 频率',
                'help' => '在此节点上检查 Ping 的频率。警告！如果您更改此设置，必须进行其他更改。请参阅快速 Ping 文档。',
            ],
            'update_enabled' => [
                'description' => '每日维护启用',
                'help' => '运行 daily.sh 维护脚本并在之后重启调度程序服务。',
            ],
            'update_frequency' => [
                'description' => '维护频率',
                'help' => '在此节点上运行每日维护的频率。默认为 1 天。强烈建议不要更改此设置。',
            ],
            'loglevel' => [
                'description' => '日志级别',
                'help' => '调度程序服务的日志级别。',
            ],
            'watchdog_enabled' => [
                'description' => '看门狗启用',
                'help' => '看门狗监视日志文件并在服务日志未更新时重启服务。',
            ],
            'watchdog_log' => [
                'description' => '监视的日志文件',
                'help' => '默认为 LibreNMS 日志文件。',
            ],
        ],
        'units' => [
            'seconds' => '秒',
            'workers' => '工作者',
        ],
    ],
];
