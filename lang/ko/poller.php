<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => '할당된 그룹',
                'help' => '이 노드는 지정된 폴러 그룹의 장비에만 작동합니다.',
            ],
            'poller_enabled' => [
                'description' => '폴러 활성화',
                'help' => '이 노드에서 폴러 워커를 활성화합니다.',
            ],
            'poller_workers' => [
                'description' => '폴러 워커 수',
                'help' => '이 노드에서 실행할 폴러 워커의 수입니다.',
            ],
            'poller_frequency' => [
                'description' => '폴링 주기 (주의!)',
                'help' => '이 노드에서 장비를 폴링하는 주기입니다. 주의! RRD 파일을 수정하지 않고 이 값을 변경하면 그래프가 깨집니다. 자세한 내용은 문서를 참조하십시오.',
            ],
            'poller_down_retry' => [
                'description' => '장비 다운 시 재시도',
                'help' => '이 노드에서 폴링 시도 시 장비가 다운 상태이면 재시도 전 대기 시간입니다.',
            ],
            'discovery_enabled' => [
                'description' => '탐색 활성화',
                'help' => '이 노드에서 탐색 워커를 활성화합니다.',
            ],
            'discovery_workers' => [
                'description' => '탐색 워커 수',
                'help' => '이 노드에서 실행할 탐색 워커의 수입니다. 너무 높게 설정하면 과부하가 발생할 수 있습니다.',
            ],
            'discovery_frequency' => [
                'description' => '탐색 주기',
                'help' => '이 노드에서 장비 탐색을 실행하는 주기입니다. 기본값은 하루 4회입니다.',
            ],
            'services_enabled' => [
                'description' => '서비스 활성화',
                'help' => '이 노드에서 서비스 워커를 활성화합니다.',
            ],
            'services_workers' => [
                'description' => '서비스 워커 수',
                'help' => '이 노드의 서비스 워커 수입니다.',
            ],
            'services_frequency' => [
                'description' => '서비스 주기',
                'help' => '이 노드에서 서비스를 실행하는 주기입니다. 폴링 주기와 일치해야 합니다.',
            ],
            'billing_enabled' => [
                'description' => '과금 활성화',
                'help' => '이 노드에서 과금 워커를 활성화합니다.',
            ],
            'billing_frequency' => [
                'description' => '과금 수집 주기',
                'help' => '이 노드에서 과금 데이터를 수집하는 주기입니다.',
            ],
            'billing_calculate_frequency' => [
                'description' => '과금 계산 주기',
                'help' => '이 노드에서 과금 사용량을 계산하는 주기입니다.',
            ],
            'alerting_enabled' => [
                'description' => '경보 활성화',
                'help' => '이 노드에서 경보 워커를 활성화합니다.',
            ],
            'alerting_frequency' => [
                'description' => '경보 점검 주기',
                'help' => '이 노드에서 경보 규칙을 점검하는 주기입니다. 데이터는 폴링 주기에 따라서만 갱신됩니다.',
            ],
            'ping_enabled' => [
                'description' => '빠른 핑 활성화',
                'help' => '빠른 핑은 장비가 업 또는 다운 상태인지 확인하기 위해 핑만 수행합니다.',
            ],
            'ping_frequency' => [
                'description' => '핑 주기',
                'help' => '이 노드에서 핑을 확인하는 주기입니다. 주의! 변경 시 추가 설정이 필요합니다. 빠른 핑 문서를 확인하십시오.',
            ],
            'update_enabled' => [
                'description' => '일일 유지보수 활성화',
                'help' => 'daily.sh 유지보수 스크립트를 실행하고 이후 디스패처 서비스를 재시작합니다.',
            ],
            'update_frequency' => [
                'description' => '유지보수 주기',
                'help' => '이 노드에서 일일 유지보수를 실행하는 주기입니다. 기본값은 1일이며 변경하지 않는 것을 강력히 권장합니다.',
            ],
            'loglevel' => [
                'description' => '로그 레벨',
                'help' => '디스패치 서비스의 로그 레벨입니다.',
            ],
            'watchdog_enabled' => [
                'description' => '워치독 활성화',
                'help' => '워치독은 로그 파일을 모니터링하고 갱신되지 않으면 서비스를 재시작합니다.',
            ],
            'watchdog_log' => [
                'description' => '감시할 로그 파일',
                'help' => '기본값은 LibreNMS 로그 파일입니다.',
            ],
        ],
        'units' => [
            'seconds' => '초',
            'workers' => '워커',
        ],
    ],
];
