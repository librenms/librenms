<?php

return [
    'device' => [
        'title' => '장비',
        'viewAll' => ['label' => '모든 장비 보기', 'description' => '모든 장비 보기'],
        'view' => ['label' => '장비 상세 보기', 'description' => '사용자가 접근할 수 있는 장비 보기'],
        'create' => ['label' => '장비 추가', 'description' => 'LibreNMS에 새 장비 추가'],
        'update' => ['label' => '장비 편집', 'description' => '장비 설정 수정'],
        'delete' => ['label' => '장비 삭제', 'description' => 'LibreNMS에서 장비 제거'],
        'debug' => ['label' => '장비 디버그', 'description' => '장비에서 snmpwalk 및 기타 디버그 명령 실행'],
        'showConfig' => ['label' => '장비 설정 보기', 'description' => '장비 구성 보기'],
        'updateNotes' => ['label' => '장비 메모 업데이트', 'description' => '장비 메모 업데이트'],
    ],

    'alert' => [
        'title' => '경보',
        'viewAll' => ['label' => '모든 경보 보기', 'description' => '모든 경보 보기'],
        'view' => ['label' => '경보 상세 보기', 'description' => '사용자가 접근할 수 있는 장비의 경보 보기'],
        'detail' => ['label' => '경보 상세 보기', 'description' => '상세 경보 정보 보기'],
        'update' => ['label' => '경보 편집', 'description' => '경보 확인 또는 수정'],
        'delete' => ['label' => '경보 삭제', 'description' => '경보 이력 삭제'],
    ],

    'alert-rule' => [
        'title' => '경보 규칙',
        'viewAll' => ['label' => '모든 경보 규칙 보기', 'description' => '모든 경보 규칙 보기'],
        'view' => ['label' => '경보 규칙 보기', 'description' => '사용자가 접근할 수 있는 장비의 경보 규칙 상세 보기'],
        'create' => ['label' => '경보 규칙 생성', 'description' => '새 경보 규칙 생성'],
        'update' => ['label' => '경보 규칙 편집', 'description' => '기존 경보 규칙 수정'],
        'delete' => ['label' => '경보 규칙 삭제', 'description' => '경보 규칙 삭제'],
    ],

    'alert-schedule' => [
        'title' => '경보 일정',
        'view' => ['label' => '경보 일정 보기', 'description' => '경보 일정 상세 보기'],
        'create' => ['label' => '경보 일정 생성', 'description' => '새 경보 일정 생성'],
        'update' => ['label' => '경보 일정 편집', 'description' => '기존 경보 일정 수정'],
        'delete' => ['label' => '경보 일정 삭제', 'description' => '경보 일정 삭제'],
    ],

    'alert-template' => [
        'title' => '경보 템플릿',
        'view' => ['label' => '경보 템플릿 보기', 'description' => '경보 템플릿 보기'],
        'create' => ['label' => '경보 템플릿 생성', 'description' => '새 경보 템플릿 생성'],
        'update' => ['label' => '경보 템플릿 편집', 'description' => '기존 경보 템플릿 수정'],
        'delete' => ['label' => '경보 템플릿 삭제', 'description' => '경보 템플릿 삭제'],
    ],

    'alert-transport' => [
        'title' => '경보 전송',
        'view' => ['label' => '경보 전송 보기', 'description' => '경보 전송 보기'],
        'create' => ['label' => '경보 전송 생성', 'description' => '새 경보 전송 생성'],
        'update' => ['label' => '경보 전송 편집', 'description' => '기존 경보 전송 수정'],
        'delete' => ['label' => '경보 전송 삭제', 'description' => '경보 전송 삭제'],
    ],

    'api' => [
        'title' => 'API 접근',
        'access' => ['label' => 'API 접근', 'description' => 'LibreNMS REST API 접근'],
    ],

    'application' => [
        'title' => '애플리케이션',
        'update' => ['label' => '애플리케이션 업데이트', 'description' => '애플리케이션 데이터 업데이트'],
    ],

    'auth-log' => [
        'title' => '인증 로그',
        'view' => ['label' => '인증 로그 보기', 'description' => '인증 로그 보기'],
    ],

    'bill' => [
        'title' => '과금',
        'viewAll' => ['label' => '모든 과금 보기', 'description' => '모든 과금 기록 보기'],
        'view' => ['label' => '과금 상세 보기', 'description' => '사용자가 접근할 수 있는 과금 상세 및 그래프 보기'],
        'create' => ['label' => '과금 생성', 'description' => '새 과금 기록 생성'],
        'update' => ['label' => '과금 편집', 'description' => '과금 설정 수정'],
        'delete' => ['label' => '과금 삭제', 'description' => '과금 기록 제거'],
    ],

    'component' => [
        'title' => '컴포넌트',
        'update' => ['label' => '컴포넌트 업데이트', 'description' => '컴포넌트 데이터 업데이트'],
    ],

    'custom-map' => [
        'title' => '맵',
        'viewAll' => ['label' => '모든 맵 보기', 'description' => '모든 네트워크 맵 보기'],
        'view' => ['label' => '맵 보기', 'description' => '사용자가 접근할 수 있는 장비가 포함된 네트워크 맵 보기'],
        'create' => ['label' => '맵 생성', 'description' => '새 네트워크 맵 생성'],
        'update' => ['label' => '맵 편집', 'description' => '기존 네트워크 맵 수정'],
        'delete' => ['label' => '맵 삭제', 'description' => '네트워크 맵 삭제'],
    ],

    'dashboard' => [
        'title' => '대시보드',
        'copy' => ['label' => '대시보드 복사', 'description' => '다른 사용자의 대시보드 복사'],
    ],

    'device-group' => [
        'title' => '장비 그룹',
        'viewAll' => ['label' => '모든 장비 그룹 보기', 'description' => '모든 장비 그룹 보기'],
        'view' => ['label' => '장비 그룹 보기', 'description' => '사용자가 접근할 수 있는 장비가 포함된 장비 그룹 보기'],
        'create' => ['label' => '장비 그룹 생성', 'description' => '새 장비 그룹 생성'],
        'update' => ['label' => '장비 그룹 편집', 'description' => '기존 장비 그룹 수정'],
        'delete' => ['label' => '장비 그룹 삭제', 'description' => '장비 그룹 삭제'],
    ],

    'link' => [
        'title' => '링크',
        'viewAll' => ['label' => '모든 링크 보기', 'description' => '네트워크 링크 정보 보기'],
    ],

    'location' => [
        'title' => '위치',
        'viewAll' => ['label' => '모든 위치 보기', 'description' => '모든 위치 보기'],
        'view' => ['label' => '위치 보기', 'description' => '사용자가 접근할 수 있는 장비와 관련된 위치 보기'],
        'create' => ['label' => '위치 생성', 'description' => '새 위치 생성'],
        'update' => ['label' => '위치 편집', 'description' => '기존 위치 수정'],
        'delete' => ['label' => '위치 삭제', 'description' => '위치 삭제'],
    ],

    'mempool' => [
        'title' => '메모리 풀',
        'update' => ['label' => '메모리 풀 업데이트', 'description' => '메모리 풀 데이터 업데이트'],
    ],

    'notification' => [
        'title' => '알림',
        'create' => ['label' => '알림 생성', 'description' => '새 알림 생성'],
        'update' => ['label' => '알림 편집', 'description' => '기존 알림 수정'],
    ],

    'oxidized' => [
        'title' => 'Oxidized',
        'view' => ['label' => 'Oxidized 보기', 'description' => '장비 구성 백업 보기'],
        'refresh' => ['label' => 'Oxidized 새로고침', 'description' => '장비의 구성 재수집 트리거'],
        'search' => ['label' => 'Oxidized 검색', 'description' => 'Oxidized 구성 백업 검색'],
    ],

    'peering-db' => [
        'title' => 'PeeringDB',
        'view' => ['label' => 'PeeringDB 보기', 'description' => 'PeeringDB 정보 보기'],
    ],

    'plugin' => [
        'title' => '플러그인',
        'admin' => ['label' => '플러그인 관리', 'description' => '플러그인 설정 및 상태 관리'],
    ],

    'poller' => [
        'title' => '폴러',
        'view' => ['label' => '폴러 보기', 'description' => '폴러 정보 및 상태 보기'],
        'update' => ['label' => '폴러 편집', 'description' => '폴러 설정 수정'],
        'delete' => ['label' => '폴러 삭제', 'description' => 'LibreNMS에서 폴러 제거'],
    ],

    'poller-group' => [
        'title' => '폴러 그룹',
        'create' => ['label' => '폴러 그룹 생성', 'description' => '새 폴러 그룹 생성'],
        'update' => ['label' => '폴러 그룹 편집', 'description' => '기존 폴러 그룹 수정'],
        'delete' => ['label' => '폴러 그룹 삭제', 'description' => '폴러 그룹 삭제'],
    ],

    'port' => [
        'title' => '포트',
        'viewAll' => ['label' => '모든 포트 보기', 'description' => '모든 포트 보기'],
        'view' => ['label' => '포트 상세 보기', 'description' => '사용자가 접근할 수 있는 장비의 포트 또는 포트 보기'],
        'update' => ['label' => '포트 편집', 'description' => '포트 설명 및 설정 수정'],
        'delete' => ['label' => '포트 삭제', 'description' => '포트 및 데이터 영구 삭제'],
    ],

    'port-group' => [
        'title' => '포트 그룹',
        'viewAll' => ['label' => '모든 포트 그룹 보기', 'description' => '모든 포트 그룹 보기'],
        'view' => ['label' => '포트 그룹 보기', 'description' => '사용자가 접근할 수 있는 포트가 포함된 포트 그룹 보기'],
        'create' => ['label' => '포트 그룹 생성', 'description' => '새 포트 그룹 생성'],
        'update' => ['label' => '포트 그룹 편집', 'description' => '기존 포트 그룹 수정'],
        'delete' => ['label' => '포트 그룹 삭제', 'description' => '포트 그룹 삭제'],
    ],

    'processor' => [
        'title' => '프로세서',
        'viewAll' => ['label' => '모든 프로세서 보기', 'description' => '모든 프로세서 보기'],
        'view' => ['label' => '프로세서 보기', 'description' => '사용자가 접근할 수 있는 장비의 프로세서 보기'],
        'update' => ['label' => '프로세서 업데이트', 'description' => '프로세서 데이터 업데이트'],
    ],

    'reporting' => [
        'title' => '보고',
        'update' => ['label' => '보고 업데이트', 'description' => '보고 설정 업데이트'],
    ],

    'role' => [
        'title' => '역할',
        'update' => ['label' => '역할 편집', 'description' => '역할 권한 및 설정 수정'],
    ],

    'routing' => [
        'title' => '라우팅',
        'viewAll' => ['label' => '모든 라우팅 보기', 'description' => '모든 라우팅 정보 보기'],
        'view' => ['label' => '라우팅 보기', 'description' => '특정 라우팅 상세 보기'],
        'update' => ['label' => '라우팅 업데이트', 'description' => '라우팅 데이터 업데이트'],
    ],

    'service' => [
        'title' => '서비스',
        'viewAll' => ['label' => '모든 서비스 보기', 'description' => '모든 서비스 보기'],
        'view' => ['label' => '서비스 보기', 'description' => '사용자가 접근할 수 있는 장비의 서비스 보기'],
        'create' => ['label' => '서비스 추가', 'description' => '장비에 새 서비스 추가'],
        'update' => ['label' => '서비스 편집', 'description' => '서비스 점검 설정 수정'],
        'delete' => ['label' => '서비스 삭제', 'description' => '장비에서 서비스 제거'],
    ],

    'service-template' => [
        'title' => '서비스 템플릿',
        'view' => ['label' => '서비스 템플릿 보기', 'description' => '서비스 템플릿 보기'],
        'create' => ['label' => '서비스 템플릿 생성', 'description' => '새 서비스 템플릿 생성'],
        'update' => ['label' => '서비스 템플릿 편집', 'description' => '기존 서비스 템플릿 수정'],
        'delete' => ['label' => '서비스 템플릿 삭제', 'description' => '서비스 템플릿 삭제'],
    ],

    'settings' => [
        'title' => '설정',
        'view' => ['label' => '설정 보기', 'description' => 'LibreNMS 전역 설정 보기'],
        'update' => ['label' => '설정 편집', 'description' => 'LibreNMS 전역 설정 수정'],
    ],

    'syslog' => [
        'title' => 'Syslog',
        'delete' => ['label' => 'Syslog 삭제', 'description' => 'Syslog 이력 삭제'],
    ],

    'user' => [
        'title' => '사용자',
        'view' => ['label' => '사용자 보기', 'description' => '사용자 계정 상세 보기'],
        'create' => ['label' => '사용자 생성', 'description' => '새 사용자 계정 생성'],
        'update' => ['label' => '사용자 편집', 'description' => '사용자 계정, 역할 및 권한 수정'],
        'delete' => ['label' => '사용자 삭제', 'description' => '사용자 계정 삭제'],
        'manage' => ['label' => '권한 관리', 'description' => '사용자 권한 관리'],
        'updatePassword' => ['label' => '비밀번호 업데이트', 'description' => '사용자 비밀번호 업데이트'],
    ],

    'vlan' => [
        'title' => 'VLAN',
        'viewAll' => ['label' => '모든 VLAN 보기', 'description' => '모든 VLAN 정보 보기'],
    ],

    'vminfo' => [
        'title' => '가상 머신',
        'viewAll' => ['label' => '모든 가상 머신 보기', 'description' => '모든 가상 머신 정보 보기'],
        'view' => ['label' => '가상 머신 보기', 'description' => '사용자가 접근할 수 있는 장비의 가상 머신 상세 보기'],
        'update' => ['label' => '가상 머신 업데이트', 'description' => '가상 머신 데이터 업데이트'],
    ],

    'wireless-sensor' => [
        'title' => '무선 센서',
        'update' => ['label' => '무선 센서 업데이트', 'description' => '무선 센서 데이터 업데이트'],
        'delete' => ['label' => '무선 센서 삭제', 'description' => '무선 센서 데이터 삭제'],
    ],

    'customoid' => [
        'title' => '사용자 정의 OID',
        'view' => ['label' => '사용자 정의 OID 보기', 'description' => '사용자 정의 OID 데이터 보기'],
        'create' => ['label' => '사용자 정의 OID 생성', 'description' => '새 사용자 정의 OID 생성'],
        'update' => ['label' => '사용자 정의 OID 편집', 'description' => '기존 사용자 정의 OID 수정'],
        'delete' => ['label' => '사용자 정의 OID 삭제', 'description' => '사용자 정의 OID 삭제'],
    ],

    'rbac' => [
        'title' => '역할 및 권한',
        'beta_warning_title' => '베타 기능',
        'beta_warning_message' => '이 기능은 베타 버전입니다. 권한이 아직 올바르게 적용되지 않을 수 있습니다. 발견하신 문제를 신고해 주세요.',
        'manage_users' => '사용자 관리',
        'manage_roles' => '역할 관리',
        'add_role' => '역할 추가',
        'create_role' => '역할 생성',
        'create_new_role' => '새 역할 생성',
        'edit_role' => '역할 편집',
        'delete_role' => '역할 삭제',
        'role_name' => '역할 이름',
        'permissions' => '권한',
        'actions' => '작업',
        'all_permissions' => '모든 권한',
        'view_all_permissions' => '모든 권한 보기',
        'view_permissions' => '권한 보기',
        'no_permissions' => '할당된 권한 없음',
        'confirm_delete' => '이 역할을 삭제하시겠습니까?',
        'role_name_placeholder' => '예: network-engineer',
        'search_permissions' => '권한 검색...',
        'select_all' => '모두 선택',
        'clear_all' => '모두 지우기',
        'save_role' => '역할 저장',
        'update_role' => '역할 업데이트',
        'created' => '역할 :name이(가) 성공적으로 생성되었습니다',
        'updated' => '역할 :name이(가) 성공적으로 업데이트되었습니다',
        'deleted' => '역할 :name이(가) 성공적으로 삭제되었습니다',
        'role_name_regex' => '역할 이름은 소문자와 하이픈(-)만 사용할 수 있습니다.',
    ],
    'permissions' => [
        'user_permissons' => ':name 권한',
        'bill_access' => '과금 접근 (:count)',
        'device_access' => '장비 접근 (:count)',
        'device_group_access' => '장비 그룹 접근 (:count)',
        'port_access' => '포트 접근 (:count)',
        'bill_all' => '모든 과금',
        'device_all' => '모든 장비',
        'device_group_all' => '모든 장비 그룹',
        'port_all' => '모든 포트',
        'none_configured' => '설정된 항목 없음',
    ],
];
