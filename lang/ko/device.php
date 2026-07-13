<?php

return [
    'all_devices' => '모든 장비',
    'attributes' => [
        'hostname' => '호스트명',
        'features' => 'OS 기능',
        'hardware' => '하드웨어',
        'icon' => '아이콘',
        'ip' => 'IP',
        'location' => '위치',
        'os' => '장비 OS',
        'serial' => '시리얼',
        'sysDescr' => 'sysDescr',
        'sysName' => 'sysName',
        'sysObjectID' => 'sysObjectID',
        'version' => 'OS 버전',
        'type' => '장비 유형',
    ],

    'never_polled' => '폴링된 적 없음',
    'vm_host' => 'VM 호스트',
    'scheduled_maintenance' => '예약된 유지보수',
    'delete_device' => '장비 삭제',
    'delete' => ':name 삭제',
    'confirm_delete' => ':name 장비를 삭제하시겠습니까?',
    'deleted' => ':hostname 장비가 삭제되었습니다.',
    'please_select' => '선택하십시오',
    'warning_monitored' => '경고: 이 장비가 모니터링 대상에서 제거됩니다!',
    'warning_data' => '다음과 같은 이 장비의 과거 데이터도 함께 삭제됩니다:',
    'device_group' => '장비 그룹',
    'show_filter' => '필터 표시',
    'show_header' => '헤더 표시',
    'os' => 'OS',
    'status' => '상태',
    'status_up' => '업',
    'status_down' => '다운',
    'device_type' => '장비 유형',
    'alerts_disabled' => '경보 비활성화됨',

    'edit' => [
        'delete_device' => '장비 삭제',
        'rediscover_title' => '폴러가 즉시 재탐색하도록 장비를 예약합니다',
        'rediscover' => '장비 재탐색',

        'hostname_title' => '이름 해석에 사용되는 호스트명을 변경합니다',
        'hostname_ip' => '호스트명 / IP',

        'display_title' => '이 장비의 표시 이름입니다. 짧게 유지하십시오. 사용 가능한 자리표시자: hostname, sysName, sysName_fallback, ip (예: ":sysName")',
        'display_name' => '표시 이름',
        'system_default' => '시스템 기본값',

        'overwrite_ip_title' => '폴링 시 해석된 IP 대신 이 IP를 사용합니다',
        'overwrite_ip' => 'IP 덮어쓰기 (사용하지 마십시오)',

        'description' => '설명',
        'type' => '유형',
        'static_groups' => '정적 그룹',

        'override_sysLocation' => 'sysLocation 재정의',
        'coordinates_title' => '좌표를 설정하려면 [위도,경도]를 포함하십시오',

        'override_sysContact' => 'sysContact 재정의',

        'depends_on' => '이 장비의 의존 대상',
        'none' => '없음',

        'poller_group' => '폴러 그룹',
        'poller_group_general' => '일반',
        'default_poller' => '(기본 폴러)',

        'disable_polling_alerting' => '폴링 및 경보 비활성화',
        'disable_alerting' => '경보 비활성화',

        'ignore_alert_tag' => '경보 무시 태그',
        'ignore_alert_tag_title' => "경보를 무시하도록 장비에 태그를 지정합니다. 경보 검사는 계속 실행됩니다.\n단, 무시 태그는 경보 규칙에서 읽을 수 있습니다.\n`devices.ignore = 0` 또는 `macros.device = 1` 조건이 설정되어 있고 경보 무시 태그가 켜져 있으면 해당 경보 규칙은 일치하지 않습니다.",

        'ignore_device_status' => '장비 상태 무시',
        'ignore_device_status_title' => '상태를 무시하도록 장비에 태그를 지정합니다. 항상 온라인으로 표시됩니다.',

        'save' => '저장',

        'size_on_disk' => '디스크 사용량',
        'rrd_files' => 'RRD 파일',
        'last_polled' => '마지막 폴링',
        'last_discovered' => '마지막 탐색',

        'rediscover_error' => '이 장비를 재탐색 대상으로 설정하는 중 오류가 발생했습니다',
    ],

    'oxidized' => [
        'connection_error' => 'Oxidized에서 장비 정보를 가져올 수 없습니다',
    ],
];
