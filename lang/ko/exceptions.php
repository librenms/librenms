<?php

return [
    'database_connect' => [
        'title' => '데이터베이스 연결 오류',
    ],
    'database_inconsistent' => [
        'title' => '데이터베이스 불일치',
        'header' => '데이터베이스 오류 중 불일치가 발견되었습니다. 계속하려면 문제를 해결하십시오.',
    ],
    'dusk_unsafe' => [
        'title' => '프로덕션 환경에서 Dusk를 실행하는 것은 안전하지 않습니다',
        'message' => '":command"를 실행하여 Dusk를 제거하거나, 개발자라면 APP_ENV를 알맞게 설정하십시오',
    ],
    'file_write_failed' => [
        'title' => '오류: 파일에 쓸 수 없습니다',
        'message' => '파일(:file)에 쓰지 못했습니다. 권한을 확인하고, 해당되는 경우 SELinux/AppArmor를 확인하십시오.',
    ],
    'host_exists' => [
        'hostname_exists' => ':hostname 장비가 이미 존재합니다',
        'ip_exists' => ':hostname을(를) 추가할 수 없습니다. IP :ip을(를) 사용하는 장비 :existing이(가) 이미 있습니다',
        'sysname_exists' => 'sysName 중복(:sysname)으로 인해 :hostname 장비가 이미 존재합니다',
    ],
    'host_name_empty' => '호스트명이 비어 있습니다',
    'host_unreachable' => [
        'unpingable' => ':hostname (:ip)에 ping할 수 없습니다',
        'unsnmpable' => ':hostname에 연결할 수 없습니다. SNMP 설정과 SNMP 접근 가능 여부를 확인하십시오',
        'unresolvable' => '호스트명이 IP로 해석되지 않았습니다',
        'no_reply_community' => 'SNMP :version: 커뮤니티 :credentials(으)로 응답이 없습니다',
        'no_reply_credentials' => 'SNMP :version: 자격 증명 :credentials(으)로 응답이 없습니다',
    ],
    'ldap_missing' => [
        'title' => 'PHP LDAP 지원 누락',
        'message' => 'PHP가 LDAP를 지원하지 않습니다. PHP LDAP 확장을 설치하거나 활성화하십시오',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => '최대 실행 시간 :seconds초를 초과했습니다|최대 실행 시간 :seconds초를 초과했습니다',
        'message' => '페이지 로드가 PHP에 설정된 최대 실행 시간을 초과했습니다. php.ini의 max_execution_time을 늘리거나 서버 하드웨어를 개선하십시오',
    ],
    'unserializable_route_cache' => [
        'title' => 'PHP 버전 불일치로 인한 오류',
        'message' => '웹 서버에서 실행 중인 PHP 버전(:web_version)이 CLI 버전(:cli_version)과 일치하지 않습니다',
    ],
    'snmp_version_unsupported' => [
        'message' => '지원하지 않는 SNMP 버전 ":snmpver"입니다. v1, v2c, v3 중 하나여야 합니다',
    ],
];
