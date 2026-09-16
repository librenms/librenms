<?php

return [
    'title' => '설정',
    'readonly' => 'config.php에 설정되어 있습니다. 활성화하려면 config.php에서 제거하십시오.',
    'groups' => [
        'alerting' => '경보',
        'api' => 'API',
        'apps' => '애플리케이션',
        'auth' => '인증',
        'authorization' => '권한 부여',
        'external' => '외부',
        'global' => '전역',
        'os' => 'OS',
        'discovery' => '탐색',
        'graphing' => '그래프',
        'poller' => '폴러',
        'system' => '시스템',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => ['name' => '일반 경보 설정'],
            'email' => ['name' => '이메일 옵션'],
            'rules' => ['name' => '경보 규칙 기본 설정'],
            'scheduled-maintenance' => ['name' => '예약된 유지보수'],
        ],
        'api' => [
            'cors' => ['name' => 'CORS'],
        ],
        'apps' => [
            'powerdns-recursor' => ['name' => 'PowerDNS Recursor'],
            'oslv_monitor' => ['name' => 'OSLV Monitor'],
            'sneck' => ['name' => 'Sneck'],
            'ssl-certificates' => ['name' => 'SSL 인증서'],
        ],
        'auth' => [
            'general' => ['name' => '일반 인증 설정'],
            'ad' => ['name' => 'Active Directory 설정'],
            'ldap' => ['name' => 'LDAP 설정'],
            'radius' => ['name' => 'Radius 설정'],
            'socialite' => ['name' => 'Socialite 설정'],
            'http' => ['name' => 'HTTP 인증 설정'],
            'sso' => ['name' => '싱글 사인온'],
        ],
        'authorization' => [
            'device-group' => ['name' => '장비 그룹 설정'],
        ],
        'discovery' => [
            'general' => ['name' => '일반 탐색 설정'],
            'route' => ['name' => '경로 탐색 모듈'],
            'discovery_modules' => ['name' => '탐색 모듈'],
            'autodiscovery' => ['name' => '네트워크 자동 탐색'],
            'ports' => ['name' => '포트 모듈'],
            'storage' => ['name' => '스토리지 모듈'],
            'processor' => ['name' => '프로세서 모듈'],
            'ipmi' => ['name' => 'IPMI 모듈'],
            'sensors' => ['name' => '센서 모듈'],
            'virtualization' => ['name' => '가상화 모듈'],
        ],
        'external' => [
            'binaries' => ['name' => '바이너리 위치'],
            'location' => ['name' => '위치 설정'],
            'graylog' => ['name' => 'Graylog 연동'],
            'oxidized' => ['name' => 'Oxidized 연동'],
            'mac_oui' => ['name' => 'Mac OUI 조회 연동'],
            'peeringdb' => ['name' => 'PeeringDB 연동'],
            'nfsen' => ['name' => 'NfSen 연동'],
            'unix-agent' => ['name' => 'Unix-Agent 연동'],
            'smokeping' => ['name' => 'Smokeping 연동'],
            'snmptrapd' => ['name' => 'SNMP 트랩 연동'],
            'rancid' => ['name' => 'RANCID 연동'],
            'collectd' => ['name' => 'Collectd 연동'],
            'unimus' => ['name' => 'Unimus 연동'],
        ],
        'poller' => [
            'availability' => ['name' => '장비 가용성'],
            'distributed' => ['name' => '분산 폴러'],
            'graphite' => ['name' => '데이터스토어: Graphite'],
            'influxdb' => ['name' => '데이터스토어: InfluxDB'],
            'influxdbv2' => ['name' => '데이터스토어: InfluxDBv2'],
            'kafka' => ['name' => '데이터스토어: Kafka'],
            'mtu' => ['name' => 'MTU 점검'],
            'opentsdb' => ['name' => '데이터스토어: OpenTSDB'],
            'ping' => ['name' => 'Ping'],
            'prometheus' => ['name' => '데이터스토어: Prometheus'],
            'rrdtool' => ['name' => '데이터스토어: RRDTool'],
            'snmp' => ['name' => 'SNMP'],
            'dispatcherservice' => ['name' => '디스패처 서비스'],
            'poller_modules' => ['name' => '폴러 모듈'],
            'ports' => ['name' => '포트 폴러 모듈'],
        ],
        'system' => [
            'billing' => ['name' => '과금'],
            'cleanup' => ['name' => '정리'],
            'proxy' => ['name' => '프록시'],
            'updates' => ['name' => '업데이트'],
            'scheduledtasks' => ['name' => '예약 작업'],
            'server' => ['name' => '서버'],
            'reporting' => ['name' => '보고'],
        ],
        'webui' => [
            'availability-map' => ['name' => '가용성 맵 설정'],
            'custom-map' => ['name' => '커스텀 맵 설정'],
            'graph' => ['name' => '그래프 설정'],
            'dashboard' => ['name' => '대시보드 설정'],
            'port-descr' => ['name' => '인터페이스 설명 파싱'],
            'search' => ['name' => '검색 설정'],
            'style' => ['name' => '스타일'],
            'device' => ['name' => '장비 설정'],
            'worldmap' => ['name' => '세계 지도 설정'],
            'general' => ['name' => '일반 Web UI 설정'],
            'front-page' => ['name' => '초기 페이지 설정'],
            'menu' => ['name' => '메뉴 설정'],
            'scheduled-maintenance' => ['name' => '예약된 유지보수'],
            'alert-map' => ['name' => '경보 맵 설정'],
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => '비활성 사용자 유지 기간',
                'help' => '이 기간(일) 동안 로그인하지 않은 사용자는 LibreNMS에서 삭제됩니다. 0은 삭제하지 않음을 의미하며, 해당 사용자가 다시 로그인하면 재생성됩니다.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => '장비 추가 시 중복 IP 확인',
            'help' => 'IP 주소로 호스트를 추가하면 해당 IP가 이미 존재하는지 확인합니다. IP가 이미 존재하면 호스트가 추가되지 않습니다. 호스트명으로 추가하면 이 확인이 수행되지 않습니다. 이 설정이 true이면 호스트명을 확인하고 IP 중복 확인도 수행합니다. 이를 통해 우발적인 중복 호스트를 방지할 수 있습니다.',
        ],
        'alert_rule' => [
            'acknowledged_alerts' => [
                'description' => '확인된 경보',
                'help' => '경보가 확인(acknowledge)되었을 때 알림을 보냅니다',
            ],
            'severity' => [
                'description' => '심각도',
                'help' => '경보의 심각도',
            ],
            'default_operation_steps_to' => [
                'description' => '기본 작업: 종료 단계',
                'help' => '생성된 작업 행의 기본 에스컬레이션 종료 단계 (-1은 제한 없음)',
            ],
            'default_operation_start_in' => [
                'description' => '기본 작업: 시작 지연',
                'help' => '작업 알림이 전송되기 전 기본 지연 시간',
            ],
            'default_operation_step_duration' => [
                'description' => '기본 작업: 단계 지속 시간',
                'help' => '기본 작업 단계 지속 시간 (분)',
            ],
            'default_operation_notifications_suppressed' => [
                'description' => '기본 작업: 알림 억제',
                'help' => '생성된 작업 행에 대해 기본적으로 알림을 억제합니다',
            ],
            'invert_rule_match' => [
                'description' => '규칙 매칭 반전',
                'help' => '규칙이 일치하지 않을 때만 경보를 발생시킵니다',
            ],
            'recovery_alerts' => [
                'description' => '복구 경보',
                'help' => '경보가 복구되면 알림을 보냅니다',
            ],
            'acknowledgement_alerts' => [
                'description' => '확인 경보',
                'help' => '경보가 확인(acknowledge)되면 알림을 보냅니다',
            ],
            'invert_map' => [
                'description' => '목록 외 모든 장비',
                'help' => '목록에 없는 장비에 대해서만 경보를 발생시킵니다',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => '경보 해제까지 확인 유지 기본값',
                'help' => '경보가 해제될 때까지 확인 상태를 유지하는 기본 옵션',
            ],
            'admins' => [
                'description' => '관리자에게 경보 발송 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'default_copy' => [
                'description' => '모든 이메일 경보를 기본 연락처에 복사 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'default_if_none' => [
                'description' => 'webui에서 설정 불가 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'default_mail' => [
                'description' => '기본 연락처 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'default_only' => [
                'description' => '기본 연락처에만 경보 전송 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'disable' => [
                'description' => '경보 비활성화',
                'help' => '경보 생성을 중지합니다',
            ],
            'acknowledged' => [
                'description' => '확인된 경보 발송',
                'help' => '경보가 확인(acknowledge)된 경우 알림을 보냅니다',
            ],
            'fixed-contacts' => [
                'description' => '활성 경보의 연락처 변경 비활성화',
                'help' => 'TRUE이면 경보가 활성화된 동안 sysContact 또는 사용자 이메일 변경이 적용되지 않습니다',
            ],
            'globals' => [
                'description' => '읽기 전용 사용자에게 경보 발송 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'scheduled_maintenance_default_behavior' => [
                'description' => '예약된 유지보수 기본 동작',
                'help' => '예약된 유지보수의 기본 동작',
                'options' => [
                    '1' => '경보 건너뜀',
                    '2' => '경보 음소거',
                    '3' => '경보 실행',
                ],
            ],
            'syscontact' => [
                'description' => 'sysContact에 경보 발송 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
            'transports' => [
                'mail' => [
                    'description' => '이메일 경보 활성화',
                    'help' => '메일 경보 전송 수단',
                ],
            ],
            'tolerance_window' => [
                'description' => 'cron 허용 오차 창',
                'help' => '초 단위 허용 오차 창',
            ],
            'users' => [
                'description' => '일반 사용자에게 경보 발송 (더 이상 사용되지 않음)',
                'help' => '더 이상 사용되지 않습니다. 대신 메일 경보 전송 수단을 사용하십시오.',
            ],
        ],
        'alert_log_purge' => [
            'description' => '이 기간보다 오래된 경보 로그 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'discovery_on_reboot' => [
            'description' => '재부팅 시 탐색',
            'help' => '재부팅된 장비에 대해 탐색을 수행합니다',
        ],
        'allow_duplicate_sysName' => [
            'description' => '중복 sysName 허용',
            'help' => '기본적으로 중복 sysName은 여러 인터페이스가 있는 장비가 여러 번 추가되는 것을 방지하기 위해 비활성화되어 있습니다',
        ],
        'allow_unauth_graphs' => [
            'description' => '비인증 그래프 접근 허용',
            'help' => '로그인 없이 누구나 그래프에 접근할 수 있도록 허용합니다',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => '특정 네트워크에 그래프 접근 허용',
            'help' => '특정 네트워크에 비인증 그래프 접근을 허용합니다 (비인증 그래프가 활성화된 경우에는 적용되지 않음)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => '허용 헤더',
                    'help' => 'Access-Control-Allow-Headers 응답 헤더를 설정합니다',
                ],
                'allowcredentials' => [
                    'description' => '자격 증명 허용',
                    'help' => 'Access-Control-Allow-Credentials 헤더를 설정합니다',
                ],
                'allowmethods' => [
                    'description' => '허용된 메서드',
                    'help' => '요청 메서드와 일치시킵니다',
                ],
                'enabled' => [
                    'description' => 'API용 CORS 지원 활성화',
                    'help' => '웹 클라이언트에서 API 리소스를 불러올 수 있게 합니다',
                ],
                'exposeheaders' => [
                    'description' => '노출 헤더',
                    'help' => 'Access-Control-Expose-Headers 응답 헤더를 설정합니다',
                ],
                'maxage' => [
                    'description' => '최대 유효 기간',
                    'help' => 'Access-Control-Max-Age 응답 헤더를 설정합니다',
                ],
                'origin' => [
                    'description' => '허용 요청 출처',
                    'help' => '요청 출처와 일치시킵니다. 와일드카드 사용 가능 (예: *.mydomain.com)',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'PowerDNS Recursor API 키',
                    'help' => '직접 연결 시 PowerDNS Recursor 앱에 사용할 API 키',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor HTTPS 사용 여부',
                    'help' => '직접 연결 시 PowerDNS Recursor 앱에 HTTP 대신 HTTPS를 사용합니다',
                ],
                'port' => [
                    'description' => 'PowerDNS Recursor 포트',
                    'help' => '직접 연결 시 PowerDNS Recursor 앱에 사용할 TCP 포트',
                ],
            ],
            'oslv_monitor' => [
                'seen_age' => [
                    'description' => '확인 경과 시간 임계값',
                    'help' => '항목이 오래된 것으로 간주되는 경과 시간(초)',
                ],
                'linux_pg_memory_stats' => [
                    'description' => 'Linux 페이지 메모리 통계',
                    'help' => 'Linux 페이지 메모리 통계 수집을 활성화합니다',
                ],
                'misc_linux_memory_stats' => [
                    'description' => '기타 Linux 메모리 통계',
                    'help' => '기타 Linux 메모리 통계 수집을 활성화합니다',
                ],
                'zswap_size' => [
                    'description' => 'ZSwap 크기 통계',
                    'help' => 'ZSwap 크기 통계 수집을 활성화합니다',
                ],
                'zswap_activity' => [
                    'description' => 'ZSwap 활동 통계',
                    'help' => 'ZSwap 활동 통계 수집을 활성화합니다',
                ],
                'workingset_stats' => [
                    'description' => '워킹 셋 통계',
                    'help' => '워킹 셋 통계 수집을 활성화합니다',
                ],
                'thp_activity' => [
                    'description' => 'THP 활동 통계',
                    'help' => '투명 대형 페이지(THP) 활동 통계 수집을 활성화합니다',
                ],
            ],
            'sneck' => [
                'polling_time_diff' => [
                    'description' => '폴링 시간 차이',
                    'help' => 'Sneck의 폴링 시간 차이 추적을 활성화합니다',
                ],
            ],
        ],
        'astext' => [
            'description' => '자율 시스템 설명 캐시 키',
        ],
        'auth' => [
            'allow_get_login' => [
                'description' => 'GET 로그인 허용 (보안 취약)',
                'help' => 'URL GET 요청에 사용자 이름과 비밀번호를 포함하여 로그인할 수 있게 합니다. 디스플레이 시스템 등 대화형 로그인이 불가능한 경우에 유용하지만, 비밀번호가 로그에 노출되고 브루트포스 공격에 취약합니다.',
            ],
            'socialite' => [
                'redirect' => [
                    'description' => '로그인 페이지 리디렉션',
                    'help' => '로그인 페이지에서 첫 번째 정의된 제공자로 즉시 리디렉션합니다.<br><br>팁: URL에 ?redirect=0을 추가하면 리디렉션을 방지할 수 있습니다',
                ],
                'register' => [
                    'description' => '제공자를 통한 등록 허용',
                ],
                'configs' => [
                    'description' => '제공자 설정',
                ],
                'scopes' => [
                    'description' => '인증 요청에 포함할 스코프',
                    'help' => 'https://laravel.com/docs/10.x/socialite#access-scopes 참조',
                ],
                'default_role' => [
                    'description' => '기본 역할',
                ],
                'claims' => [
                    'description' => '클레임',
                    'help' => '그룹을 역할에 매핑합니다',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => '기본 DN',
            'help' => '그룹 및 사용자는 이 DN 아래에 있어야 합니다. 예: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => '인증서 확인',
            'help' => '인증서의 유효성을 확인합니다. 일부 서버는 자체 서명된 인증서를 사용하므로, 이를 비활성화하면 해당 서버를 허용할 수 있습니다.',
        ],
        'auth_ad_debug' => [
            'description' => '디버그',
            'help' => '자세한 오류 메시지를 표시합니다. 데이터가 노출될 수 있으므로 활성화 상태로 두지 마십시오.',
        ],
        'auth_ad_domain' => [
            'description' => 'Active Directory 도메인',
            'help' => 'Active Directory 도메인 예: example.com',
        ],
        'auth_ad_global_read' => [
            'description' => '전역 읽기',
            'help' => '모든 사용자에게 전역 읽기 접근을 허용합니다',
        ],
        'auth_ad_group' => [
            'description' => '접근 그룹 DN',
            'help' => '일반 수준 접근 권한을 부여할 그룹의 고유 이름입니다. 예: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ad_group_filter' => [
            'description' => '그룹 LDAP 필터',
            'help' => '그룹 선택을 위한 Active Directory LDAP 필터',
        ],
        'auth_ad_groups' => [
            'description' => '그룹 접근',
            'help' => '접근 권한과 수준을 가진 그룹을 정의합니다',
        ],
        'auth_ad_require_groupmembership' => [
            'description' => '그룹 멤버십 필요',
            'help' => '정의된 그룹의 구성원인 경우에만 로그인을 허용합니다',
        ],
        'auth_ad_timeout' => [
            'description' => '연결 시간 초과',
            'help' => '하나 이상의 서버가 응답하지 않으면 시간 초과 값이 높을수록 로그인이 느려집니다. 너무 낮으면 일부 경우 연결에 실패할 수 있습니다',
        ],
        'auth_ad_user_filter' => [
            'description' => '사용자 LDAP 필터',
            'help' => '사용자 선택을 위한 Active Directory LDAP 필터',
        ],
        'auth_ad_url' => [
            'description' => 'Active Directory 서버',
            'help' => '서버를 공백으로 구분하여 설정합니다. SSL을 사용하려면 ldaps://를 접두사로 붙이십시오. 예: ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => '사용자 이름 확인에 사용할 속성',
                'help' => '사용자 이름으로 사용자를 식별하는 데 사용되는 속성',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => '바인드 DN (바인드 사용자 이름 재정의)',
            'help' => '바인드 사용자의 전체 DN',
        ],
        'auth_ldap_bindpassword' => [
            'description' => '바인드 비밀번호',
            'help' => '바인드 사용자의 비밀번호',
        ],
        'auth_ldap_binduser' => [
            'description' => '바인드 사용자 이름',
            'help' => '로그인한 사용자가 없을 때 LDAP 서버를 쿼리하는 데 사용됩니다 (경보, API 등)',
        ],
        'auth_ad_binddn' => [
            'description' => '바인드 DN (바인드 사용자 이름 재정의)',
            'help' => '바인드 사용자의 전체 DN',
        ],
        'auth_ad_bindpassword' => [
            'description' => '바인드 비밀번호',
            'help' => '바인드 사용자의 비밀번호',
        ],
        'auth_ad_binduser' => [
            'description' => '바인드 사용자 이름',
            'help' => '로그인한 사용자가 없을 때 AD 서버를 쿼리하는 데 사용됩니다 (경보, API 등)',
        ],
        'auth_ad_starttls' => [
            'description' => 'STARTTLS 사용',
            'help' => 'STARTTLS를 사용하여 연결을 보호합니다. LDAPS의 대안입니다.',
            'options' => [
                'disabled' => '비활성화',
                'optional' => '선택적',
                'required' => '필수',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'LDAP 캐시 만료 시간',
            'help' => 'LDAP 쿼리 결과를 일시적으로 저장합니다. 속도가 향상되지만 데이터가 오래될 수 있습니다.',
        ],
        'auth_ldap_debug' => [
            'description' => '디버그 표시',
            'help' => '디버그 정보를 표시합니다. 개인 정보가 노출될 수 있으므로 활성화 상태로 두지 마십시오.',
        ],
        'auth_ldap_cacertfile' => [
            'description' => '시스템 TLS CA 인증서 재정의',
            'help' => 'LDAPS에 제공된 CA 인증서를 사용합니다.',
        ],
        'auth_ldap_ignorecert' => [
            'description' => '유효한 인증서 요구 안 함',
            'help' => 'LDAPS에 유효한 TLS 인증서를 요구하지 않습니다.',
        ],
        'auth_ldap_emailattr' => [
            'description' => '메일 속성',
        ],
        'auth_ldap_group' => [
            'description' => '접근 그룹 DN',
            'help' => '일반 수준 접근 권한을 부여할 그룹의 고유 이름입니다. 예: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => '그룹 기본 DN',
            'help' => '그룹 검색을 위한 고유 이름입니다. 예: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => '그룹 멤버 속성',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => '그룹 멤버 검색 방법',
            'options' => [
                'username' => '사용자 이름',
                'fulldn' => '전체 DN (접두사 및 접미사 사용)',
                'puredn' => 'DN 검색 (uid 속성으로 검색)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => '그룹 접근',
            'help' => '접근 권한과 수준을 가진 그룹을 정의합니다',
        ],
        'auth_ldap_require_groupmembership' => [
            'description' => 'LDAP 그룹 멤버십 확인',
            'help' => '제공자가 허용하는 경우 ldap_compare를 수행합니다 (또는 Compare 작업을 허용하지 않는 경우 건너뜁니다).',
        ],
        'auth_ldap_port' => [
            'description' => 'LDAP 포트',
            'help' => '서버 연결에 사용할 포트입니다. LDAP는 389, LDAPS는 636이어야 합니다',
        ],
        'auth_ldap_prefix' => [
            'description' => '사용자 접두사',
            'help' => '사용자 이름을 고유 이름으로 변환하는 데 사용됩니다',
        ],
        'auth_ldap_server' => [
            'description' => 'LDAP 서버',
            'help' => '서버를 공백으로 구분하여 설정합니다. SSL을 사용하려면 ldaps://를 접두사로 붙이십시오',
        ],
        'auth_ldap_starttls' => [
            'description' => 'STARTTLS 사용',
            'help' => 'STARTTLS를 사용하여 연결을 보호합니다. LDAPS의 대안입니다.',
            'options' => [
                'disabled' => '비활성화',
                'optional' => '선택적',
                'required' => '필수',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => '사용자 접미사',
            'help' => '사용자 이름을 고유 이름으로 변환하는 데 사용됩니다',
        ],
        'auth_ldap_timeout' => [
            'description' => '연결 시간 초과',
            'help' => '하나 이상의 서버가 응답하지 않으면 시간 초과 값이 높을수록 접근이 느려집니다. 너무 낮으면 일부 경우 연결에 실패할 수 있습니다',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => '고유 ID 속성',
            'help' => '사용자를 식별하는 데 사용할 LDAP 속성, 숫자여야 합니다',
        ],
        'auth_ldap_userdn' => [
            'description' => '전체 사용자 DN 사용',
            'help' => '그룹의 멤버 속성 값으로 접두사/접미사를 사용한 member: username 대신 사용자의 전체 DN을 사용합니다.',
        ],
        'auth_ldap_userlist_filter' => [
            'description' => '사용자 정의 LDAP 사용자 필터',
            'help' => '사용자가 수천 명인 LDAP 디렉터리에서 응답 수를 제한하기 위한 사용자 정의 LDAP 필터',
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => '와일드카드 사용자 OU',
            'help' => '사용자 접미사에 설정된 OU와 상관없이 사용자 이름으로 사용자를 검색합니다. 사용자가 다른 OU에 있는 경우 유용합니다.',
        ],
        'auth_ldap_version' => [
            'description' => 'LDAP 버전',
            'help' => '서버와 통신할 때 사용할 LDAP 버전입니다. 일반적으로 v3을 사용해야 합니다',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => '인증 방법 (주의!)',
            'help' => '인증 방법입니다. 주의: 로그인 능력을 잃을 수 있습니다. config.php에서 $config[\'auth_mechanism\'] = \'mysql\';을 설정하여 MySQL로 되돌릴 수 있습니다',
            'options' => [
                'mysql' => 'MySQL (기본값)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP 인증',
                'ad-authorization' => '외부 인증 AD',
                'ldap-authorization' => '외부 인증 LDAP',
                'sso' => '싱글 사인온',
            ],
        ],
        'auth_remember' => [
            'description' => '로그인 유지 기간',
            'help' => '로그인 시 "로그인 유지" 체크박스를 선택할 경우 사용자가 로그인 상태를 유지할 일수',
        ],
        'authlog_purge' => [
            'description' => '이 기간보다 오래된 인증 로그 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'availablity' => [
            'threshold_ok' => [
                'description' => '가용성 정상 임계값',
                'help' => '녹색으로 표시되는 임계값',
            ],
            'threshold_warning' => [
                'description' => '가용성 경고 임계값',
                'help' => '주황색으로 표시되는 임계값',
            ],
        ],
        'bad_entity_sensor_regex' => [
            'description' => '잘못된 엔티티 센서 정규식',
            'help' => '잘못된 엔티티 센서를 매칭하는 정규식입니다. 이에 해당하는 센서는 웹 인터페이스에 표시되지 않습니다.',
        ],
        'billing' => [
            '95th_default_agg' => [
                'description' => '기본 95번째 퍼센타일 집계',
                'help' => '95번째 퍼센타일 계산의 기본 집계 옵션을 설정합니다.',
            ],
        ],
        'enable_billing' => [
            'description' => '과금 활성화',
            'help' => '과금 모듈을 활성화합니다. 포트 사용량을 모니터링할 수 있습니다.',
        ],
        'peering_descr' => [
            'description' => '피어링 포트 유형',
            'help' => '나열된 설명 유형의 포트가 피어링 포트 메뉴에 표시됩니다. 자세한 내용은 인터페이스 설명 파싱 문서를 참조하십시오.',
        ],
        'transit_descr' => [
            'description' => '트랜짓 포트 유형',
            'help' => '나열된 설명 유형의 포트가 트랜짓 포트 메뉴에 표시됩니다. 자세한 내용은 인터페이스 설명 파싱 문서를 참조하십시오.',
        ],
        'collectd_dir' => [
            'description' => 'Collectd 디렉터리',
            'help' => 'Collectd가 RRD 파일을 저장하는 디렉터리입니다. LibreNMS에서 collectd 데이터를 표시하는 데 사용됩니다.',
        ],
        'collectd_sock' => [
            'description' => 'Collectd 소켓',
            'help' => 'Collectd가 수신 대기하는 소켓입니다. LibreNMS에서 collectd 데이터를 표시하는 데 사용됩니다.',
        ],
        'core_descr' => [
            'description' => '코어 포트 유형',
            'help' => '나열된 설명 유형의 포트가 코어 포트 메뉴에 표시됩니다. 자세한 내용은 인터페이스 설명 파싱 문서를 참조하십시오.',
        ],
        'custom_descr' => [
            'description' => '사용자 정의 포트 유형',
            'help' => '나열된 설명 유형의 포트가 사용자 정의 포트 메뉴에 표시됩니다. 자세한 내용은 인터페이스 설명 파싱 문서를 참조하십시오.',
        ],
        'custom_map' => [
            'background_type' => [
                'description' => '배경 유형',
                'help' => '새 맵의 기본 배경 유형입니다. 배경 데이터가 설정되어 있어야 합니다.',
            ],
            'background_data' => [
                'color' => [
                    'description' => '배경 색상',
                    'help' => '맵 배경의 초기 색상',
                ],
                'lat' => [
                    'description' => '배경 맵 위도',
                    'help' => '배경 지도의 초기 위도',
                ],
                'lng' => [
                    'description' => '배경 맵 경도',
                    'help' => '배경 지도의 초기 경도',
                ],
                'layer' => [
                    'description' => '배경 맵 레이어',
                    'help' => '배경 지도의 초기 레이어',
                ],
                'zoom' => [
                    'description' => '배경 맵 확대/축소',
                    'help' => '배경 지도의 초기 확대/축소 수준',
                ],
            ],
            'edge_font_color' => [
                'description' => '엣지 텍스트 색상',
                'help' => '엣지 레이블의 기본 글꼴 색상',
            ],
            'edge_font_face' => [
                'description' => '엣지 글꼴',
                'help' => '엣지 레이블의 기본 글꼴',
            ],
            'edge_font_size' => [
                'description' => '엣지 텍스트 크기',
                'help' => '엣지 레이블의 기본 글꼴 크기',
            ],
            'edge_seperation' => [
                'description' => '엣지 간격',
                'help' => '새 맵의 기본 엣지 간격',
            ],
            'height' => [
                'description' => '맵 높이',
                'help' => '새 맵의 기본 높이',
            ],
            'node_align' => [
                'description' => '노드 정렬',
                'help' => '새 맵의 기본 노드 정렬',
            ],
            'node_background' => [
                'description' => '노드 배경',
                'help' => '노드 레이블의 기본 배경 색상',
            ],
            'node_border' => [
                'description' => '노드 테두리',
                'help' => '노드 레이블의 기본 테두리 색상',
            ],
            'node_font_color' => [
                'description' => '노드 텍스트 색상',
                'help' => '노드 레이블의 기본 글꼴 색상',
            ],
            'node_font_face' => [
                'description' => '노드 글꼴',
                'help' => '노드 레이블의 기본 글꼴',
            ],
            'node_font_size' => [
                'description' => '노드 텍스트 크기',
                'help' => '노드 레이블의 기본 글꼴 크기',
            ],
            'node_size' => [
                'description' => '노드 크기',
                'help' => '노드의 기본 크기',
            ],
            'node_type' => [
                'description' => '노드 표시 유형',
                'help' => '노드의 기본 표시 유형',
            ],
            'reverse_arrows' => [
                'description' => '엣지 화살표 반전',
                'help' => '기본 화살표 방향. 중심을 향하거나 (기본값) 끝을 향합니다',
            ],
            'width' => [
                'description' => '맵 너비',
                'help' => '새 맵의 기본 너비',
            ],
        ],
        'customers_descr' => [
            'description' => '고객 포트 유형',
            'help' => '나열된 설명 유형의 포트가 고객 포트 메뉴에 표시됩니다. 자세한 내용은 인터페이스 설명 파싱 문서를 참조하십시오.',
        ],
        'base_url' => [
            'description' => '기본 URL',
            'help' => '특정 호스트명/포트를 강제로 사용하려는 경우에만 설정하십시오. 다른 호스트명에서는 웹 인터페이스를 사용할 수 없게 됩니다',
        ],
        'disabled_sensors' => [
            'description' => '비활성화된 센서',
            'help' => '폴링하거나 웹 인터페이스에 표시되지 않을 센서들',
        ],
        'disabled_sensors_regex' => [
            'description' => '비활성화된 센서 정규식',
            'help' => '이 정규식과 일치하는 센서는 폴링되거나 웹 인터페이스에 표시되지 않습니다.',
        ],
        'discovery_modules' => [
            'arp-table' => ['description' => 'ARP 테이블'],
            'applications' => ['description' => '애플리케이션'],
            'bgp-peers' => ['description' => 'BGP 피어'],
            'cisco-cef' => ['description' => 'Cisco CEF'],
            'mac-accounting' => ['description' => 'MAC 어카운팅'],
            'cisco-otv' => ['description' => 'Cisco OTV'],
            'cisco-qfp' => ['description' => 'Cisco QFP'],
            'slas' => ['description' => '서비스 수준 협약 추적'],
            'cisco-pw' => ['description' => 'Cisco PW'],
            'cisco-vrf-lite' => ['description' => 'Cisco VRF Lite'],
            'discovery-arp' => ['description' => '탐색 ARP'],
            'discovery-protocols' => ['description' => '탐색 프로토콜'],
            'entity-physical' => ['description' => '엔티티 물리'],
            'entity-state' => ['description' => '엔티티 상태'],
            'fdb-table' => ['description' => 'FDB 테이블'],
            'hr-device' => ['description' => 'HR 장치'],
            'ipv4-addresses' => ['description' => 'IPv4 주소'],
            'ipv6-addresses' => ['description' => 'IPv6 주소'],
            'isis' => ['description' => 'ISIS'],
            'junose-atm-vp' => ['description' => 'Junose ATM VP'],
            'loadbalancers' => ['description' => '로드밸런서'],
            'mef' => ['description' => 'MEF'],
            'mempools' => ['description' => '메모리 풀'],
            'mpls' => ['description' => 'MPLS'],
            'ntp' => ['description' => 'NTP'],
            'os' => ['description' => 'OS'],
            'ports' => ['description' => '포트'],
            'ports-stack' => ['description' => '포트 스택'],
            'processors' => ['description' => '프로세서'],
            'qos' => ['description' => 'QoS'],
            'route' => ['description' => '경로'],
            'sensors' => ['description' => '센서'],
            'services' => ['description' => '서비스'],
            'storage' => ['description' => '스토리지'],
            'stp' => ['description' => 'STP'],
            'ucd-diskio' => ['description' => 'UCD DiskIO'],
            'vlans' => ['description' => 'VLAN'],
            'vminfo' => ['description' => '하이퍼바이저 VM 정보'],
            'vrf' => ['description' => 'VRF'],
            'wireless' => ['description' => '무선'],
            'xdsl' => ['description' => 'xDSL'],
            'printer-supplies' => ['description' => '프린터 소모품'],
        ],
        'distributed_poller' => [
            'description' => '분산 폴링 활성화 (추가 설정 필요)',
            'help' => '시스템 전체에서 분산 폴링을 활성화합니다. 부하 분산을 위한 것으로 원격 폴링이 아닙니다. 활성화 단계는 문서를 참조하십시오.',
        ],
        'default_poller_group' => [
            'description' => '기본 폴러 그룹',
            'help' => 'config.php에 설정된 값이 없을 때 모든 폴러가 폴링할 기본 폴러 그룹',
        ],
        'device_traffic_iftype' => [
            'description' => '장비 트래픽 인터페이스 유형',
            'help' => '장비 그래프에서 제외할 인터페이스 유형',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Memcached 호스트',
            'help' => 'Memcached 서버의 호스트명 또는 IP입니다. poller_wrapper.py 및 daily.sh 잠금에 필요합니다.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Memcached 포트',
            'help' => 'Memcached 서버의 포트입니다. 기본값은 11211입니다',
        ],
        'enable_ports_etherlike' => [
            'description' => '포트에 이더라이크 그래프 활성화',
        ],
        'email_auto_tls' => [
            'description' => '자동 TLS 지원',
            'help' => '암호화되지 않은 연결로 대체하기 전에 TLS를 먼저 시도합니다',
        ],
        'email_smtp_verifypeer' => [
            'description' => '피어 인증서 확인',
            'help' => 'TLS를 통해 SMTP 서버에 연결할 때 피어 인증서를 확인하지 않습니다',
        ],
        'email_smtp_allowselfsigned' => [
            'description' => '자체 서명 인증서 허용',
            'help' => 'TLS를 통해 SMTP 서버에 연결할 때 자체 서명 인증서를 허용합니다',
        ],
        'email_attach_graphs' => [
            'description' => '그래프 이미지 첨부',
            'help' => '경보 발생 시 그래프를 생성하여 이메일에 첨부하고 삽입합니다.',
        ],
        'email_backend' => [
            'description' => '메일 전송 방법',
            'help' => '이메일 전송에 사용할 백엔드입니다. mail, sendmail 또는 SMTP 중에서 선택합니다',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => '발신 이메일 주소',
            'help' => '이메일 전송에 사용되는 이메일 주소 (발신)',
        ],
        'email_html' => [
            'description' => 'HTML 이메일 사용',
            'help' => 'HTML 이메일을 발송합니다',
        ],
        'email_sendmail_path' => [
            'description' => 'sendmail 바이너리 경로',
        ],
        'email_smtp_auth' => [
            'description' => 'SMTP 인증',
            'help' => 'SMTP 서버에 인증이 필요한 경우 활성화하십시오',
        ],
        'email_smtp_host' => [
            'description' => 'SMTP 서버',
            'help' => '메일을 전달할 SMTP 서버의 IP 또는 DNS 이름',
        ],
        'email_smtp_password' => [
            'description' => 'SMTP 인증 비밀번호',
        ],
        'email_smtp_port' => [
            'description' => 'SMTP 포트 설정',
        ],
        'email_smtp_secure' => [
            'description' => '암호화',
            'options' => [
                '' => '비활성화',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'SMTP 시간 초과 설정',
        ],
        'email_smtp_username' => [
            'description' => 'SMTP 인증 사용자 이름',
        ],
        'email_user' => [
            'description' => '발신자 이름',
            'help' => '발신 주소의 일부로 사용되는 이름',
        ],
        'enable_clear_discovery' => [
            'description' => '탐색 초기화 활성화',
            'help' => '장비의 탐색 날짜 및 시간을 초기화하는 기능을 활성화합니다. 장비를 강제로 재탐색합니다.',
        ],
        'enable_inventory' => [
            'description' => '인벤토리 활성화',
            'help' => '장비의 하드웨어 인벤토리를 보여주는 인벤토리 페이지를 활성화합니다.',
        ],
        'enable_lazy_load' => [
            'description' => '지연 로딩 활성화',
            'help' => '필요한 데이터만 로딩하여 페이지 로딩 속도를 높입니다. 문제가 있는 경우 비활성화할 수 있습니다.',
        ],
        'enable_libvirt' => [
            'description' => 'Libvirt 활성화',
            'help' => '장비의 가상 머신을 보여주는 libvirt 페이지를 활성화합니다.',
        ],
        'enable_proxmox' => [
            'description' => 'Proxmox 활성화',
            'help' => '장비의 가상 머신을 보여주는 Proxmox 페이지를 활성화합니다.',
        ],
        'enable_pseudowires' => [
            'description' => '수도 회선 활성화',
            'help' => '장비의 수도 회선을 보여주는 페이지를 활성화합니다.',
        ],
        'enable_syslog' => [
            'description' => 'Syslog 활성화',
            'help' => 'Web UI에서 syslog 가시성을 활성화합니다.',
        ],
        'eventlog_purge' => [
            'description' => '이 기간보다 오래된 이벤트 로그 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'favicon' => [
            'description' => '파비콘',
            'help' => '기본 파비콘을 재정의합니다.',
        ],
        'front_page' => [
            'description' => '첫 페이지',
            'help' => '사용자 정의 첫 페이지를 설정합니다. 로그인 후 처음 표시되는 페이지입니다. 예: `resources/views/overview/custom/foobar.blade.php`를 생성한 경우, `front_page`를 `foobar`로 설정하십시오',
        ],
        'front_page_down_box_limit' => [
            'description' => '다운 장비 표시 한도',
            'help' => '첫 페이지의 다운 박스에 표시할 장비 수',
        ],
        'front_page_settings' => [
            'top_devices' => [
                'description' => '상위 장비',
                'help' => '첫 페이지에 표시할 상위 장비 수',
            ],
            'top_ports' => [
                'description' => '상위 포트',
                'help' => '첫 페이지에 표시할 상위 포트 수',
            ],
        ],
        'fping' => [
            'description' => 'fping 경로',
        ],
        'fping6' => [
            'description' => 'fping6 경로',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'fping 횟수',
                'help' => 'ICMP로 호스트 상태 확인 시 전송할 핑 횟수',
            ],
            'interval' => [
                'description' => 'fping 간격',
                'help' => '핑 사이의 대기 시간 (밀리초)',
            ],
            'timeout' => [
                'description' => 'fping 시간 초과',
                'help' => '에코 응답 대기 시간 (밀리초)',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => '지도 엔진 API 키',
                'help' => '지오코딩 API 키 (필수)',
            ],
            'dns' => [
                'description' => 'DNS 위치 레코드 사용',
                'help' => 'DNS 서버의 LOC 레코드를 사용하여 호스트명의 지리적 좌표를 가져옵니다',
            ],
            'engine' => [
                'description' => '지도 엔진',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                    'esri' => 'ESRI ArcGIS',
                ],
            ],
            'latlng' => [
                'description' => '위치 지오코딩 시도',
                'help' => '폴링 중 지오코딩 API를 통해 위도와 경도를 조회합니다',
            ],
            'layer' => [
                'description' => '초기 지도 레이어',
                'help' => '표시할 초기 지도 레이어입니다. *모든 레이어가 모든 지도 엔진에서 사용 가능한 것은 아닙니다.',
                'options' => [
                    'Streets' => '도로',
                    'Sattelite' => '위성',
                    'Topography' => '지형',
                ],
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => '활성화',
                'help' => 'Graphite로 메트릭을 내보냅니다',
            ],
            'host' => [
                'description' => '서버',
                'help' => '데이터를 전송할 Graphite 서버의 IP 또는 호스트명',
            ],
            'port' => [
                'description' => '포트',
                'help' => 'Graphite 서버 연결에 사용할 포트',
            ],
            'prefix' => [
                'description' => '접두사 (선택사항)',
                'help' => '모든 메트릭 앞에 접두사를 추가합니다. 점으로 구분된 영숫자여야 합니다',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => '기간',
                'help' => '나열된 기간 동안 장비 가용성을 계산합니다. (기간은 초 단위로 정의됩니다)',
            ],
            'availability_consider_maintenance' => [
                'description' => '예약된 유지보수는 가용성에 영향을 주지 않음',
                'help' => '유지보수 모드에 있는 장비에 대해 장애 생성 및 가용성 감소를 비활성화합니다.',
            ],
        ],
        'graphs' => [
            'port_speed_zoom' => [
                'description' => '포트 그래프를 포트 속도에 맞게 확대',
                'help' => '포트 그래프를 최대값이 항상 포트 속도가 되도록 확대합니다. 비활성화하면 트래픽에 맞게 확대됩니다',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => '기본 URI',
                'help' => 'Graylog 기본값을 변경한 경우 기본 URI를 재정의합니다.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => '장비 개요 로그 수준',
                    'help' => '장비 개요 페이지에 표시할 최대 로그 수준을 설정합니다.',
                ],
                'rowCount' => [
                    'description' => '장비 개요 행 수',
                    'help' => '장비 개요 페이지에 표시할 행 수를 설정합니다.',
                ],
            ],
            'password' => [
                'description' => '비밀번호',
                'help' => 'Graylog API 접근을 위한 비밀번호입니다.',
            ],
            'port' => [
                'description' => '포트',
                'help' => 'Graylog API 접근에 사용할 포트입니다. 지정하지 않으면 http는 80, https는 443입니다.',
            ],
            'server' => [
                'description' => '서버',
                'help' => 'Graylog 서버 API 엔드포인트의 IP 또는 호스트명입니다.',
            ],
            'timezone' => [
                'description' => '표시 시간대',
                'help' => 'Graylog 시간은 GMT로 저장됩니다. 이 설정으로 표시 시간대를 변경합니다. 유효한 PHP 시간대여야 합니다.',
            ],
            'username' => [
                'description' => '사용자 이름',
                'help' => 'Graylog API 접근을 위한 사용자 이름입니다.',
            ],
            'version' => [
                'description' => '버전',
                'help' => 'Graylog API의 base_uri를 자동으로 생성하는 데 사용됩니다. API URI를 기본값에서 변경했다면 other로 설정하고 base_uri를 직접 지정하십시오.',
            ],
            'query' => [
                'field' => [
                    'description' => '쿼리 API 필드',
                    'help' => 'Graylog API를 쿼리할 기본 필드를 변경합니다.',
                ],
            ],
            'match-any-address' => [
                'description' => '모든 주소 일치',
                'help' => '장비의 모든 주소를 graylog 로그 메시지 출처와 일치시킵니다. 기본적으로는 기본 주소만 사용됩니다',
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => '기본 드롭다운 링크',
                    'help' => '장비 드롭다운 메뉴의 기본 링크를 설정합니다',
                ],
            ],
        ],
        'http_auth_header' => [
            'description' => '사용자 이름을 포함하는 필드 이름',
            'help' => 'REMOTE_USER, PHP_AUTH_USER 또는 사용자 정의 변형과 같은 ENV 또는 HTTP 헤더 필드일 수 있습니다',
        ],
        'http_auth_guest' => [
            'description' => 'HTTP 인증 게스트 사용자',
            'help' => '설정되면 모든 HTTP 사용자가 인증할 수 있으며 알 수 없는 사용자에게는 지정된 로컬 사용자 이름이 부여됩니다',
        ],
        'http_proxy' => [
            'description' => 'HTTP 프록시',
            'help' => 'http_proxy 환경 변수를 사용할 수 없는 경우 대체로 설정합니다.',
        ],
        'https_proxy' => [
            'description' => 'HTTPS 프록시',
            'help' => 'https_proxy 환경 변수를 사용할 수 없는 경우 대체로 설정합니다.',
        ],
        'icmp_check' => [
            'description' => 'ICMP 점검',
            'help' => '모든 장비에 대해 전역으로 ICMP 점검을 활성화합니다. 장비에 핑을 보내 상태를 확인합니다. 비활성화하면 폴링이 제때 완료되지 않을 수 있습니다.',
        ],
        'ignore_mount' => [
            'description' => '무시할 마운트 포인트',
            'help' => '이 마운트 포인트의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'ignore_mount_network' => [
            'description' => '네트워크 마운트 포인트 무시',
            'help' => '네트워크 마운트 포인트의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'ignore_mount_optical' => [
            'description' => '광학 드라이브 무시',
            'help' => '광학 드라이브의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'ignore_mount_removable' => [
            'description' => '이동식 드라이브 무시',
            'help' => '이동식 장치의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'ignore_mount_regexp' => [
            'description' => '정규식으로 일치하는 마운트 포인트 무시',
            'help' => '정규식 중 하나와 일치하는 마운트 포인트의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'ignore_mount_string' => [
            'description' => '특정 문자열을 포함하는 마운트 포인트 무시',
            'help' => '이 문자열 중 하나를 포함하는 마운트 포인트의 디스크 사용량을 모니터링하지 않습니다',
        ],
        'influxdb' => [
            'db' => [
                'description' => '데이터베이스',
                'help' => '메트릭을 저장할 InfluxDB 데이터베이스 이름',
            ],
            'enable' => [
                'description' => '활성화',
                'help' => 'InfluxDB로 메트릭을 내보냅니다',
            ],
            'host' => [
                'description' => '서버',
                'help' => '데이터를 전송할 InfluxDB 서버의 IP 또는 호스트명',
            ],
            'password' => [
                'description' => '비밀번호',
                'help' => 'InfluxDB 연결에 필요한 비밀번호 (필요한 경우)',
            ],
            'port' => [
                'description' => '포트',
                'help' => 'InfluxDB 서버 연결에 사용할 포트',
            ],
            'timeout' => [
                'description' => '시간 초과',
                'help' => 'InfluxDB 서버 대기 시간, 0은 기본 시간 초과를 의미합니다',
            ],
            'transport' => [
                'description' => '전송 방식',
                'help' => 'InfluxDB 서버 연결에 사용할 포트',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDP',
                ],
            ],
            'username' => [
                'description' => '사용자 이름',
                'help' => 'InfluxDB 연결에 필요한 사용자 이름 (필요한 경우)',
            ],
            'batch_size' => [
                'description' => '배치 크기',
                'help' => '한 번에 전송할 메트릭 수, 0은 배치 없음을 의미합니다',
            ],
            'measurements' => [
                'description' => '측정값',
                'help' => 'InfluxDB로 전송할 측정값 목록, 비워두면 모두 전송합니다',
            ],
            'verifySSL' => [
                'description' => 'SSL 확인',
                'help' => 'SSL 인증서가 유효하고 신뢰할 수 있는지 확인합니다',
            ],
            'debug' => [
                'description' => '디버그',
                'help' => 'CLI 상세 출력을 활성화 또는 비활성화합니다',
            ],
        ],
        'influxdbv2' => [
            'bucket' => [
                'description' => '버킷',
                'help' => '메트릭을 저장할 InfluxDB 버킷 이름',
            ],
            'enable' => [
                'description' => '활성화',
                'help' => 'InfluxDBv2 API를 사용하여 InfluxDB로 메트릭을 내보냅니다',
            ],
            'host' => [
                'description' => '서버',
                'help' => '데이터를 전송할 InfluxDB 서버의 IP 또는 호스트명',
            ],
            'token' => [
                'description' => '토큰',
                'help' => 'InfluxDB 연결에 필요한 토큰 (필요한 경우)',
            ],
            'port' => [
                'description' => '포트',
                'help' => 'InfluxDB 서버 연결에 사용할 포트',
            ],
            'transport' => [
                'description' => '전송 방식',
                'help' => 'InfluxDB 서버 연결에 사용할 전송 방식',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                ],
            ],
            'organization' => [
                'description' => '조직',
                'help' => 'InfluxDB 서버에서 버킷을 포함하는 조직',
            ],
            'allow_redirects' => [
                'description' => '리디렉션 허용',
                'help' => 'InfluxDB 서버의 리디렉션을 허용합니다',
            ],
            'debug' => [
                'description' => '디버그',
                'help' => 'CLI 상세 출력을 활성화 또는 비활성화합니다',
            ],
            'log_file' => [
                'description' => '로그 파일',
                'help' => '디버그용 다른 로그 파일을 지정합니다',
            ],
            'groups-exclude' => [
                'description' => '제외된 장비 그룹',
                'help' => 'InfluxDBv2로 데이터를 보내지 않을 장비 그룹',
            ],
            'timeout' => [
                'description' => '시간 초과',
                'help' => '시간 초과 (초 단위)',
            ],
            'verify' => [
                'description' => '인증서 확인',
                'help' => '인증서를 확인합니다',
            ],
            'batch_size' => [
                'description' => '배치 크기',
                'help' => '전송 전에 묶을 메트릭 수',
            ],
            'max_retry' => [
                'description' => '최대 재시도',
                'help' => '재시도 횟수',
            ],
        ],
        'kafka' => [
            'enable' => [
                'description' => '활성화',
                'help' => 'idealo/php-rdkafka-ffi를 사용하여 Kafka로 메트릭을 내보냅니다',
            ],
            'groups-exclude' => [
                'description' => '제외된 장비 그룹 ID',
                'help' => 'Kafka로 데이터를 보내지 않을 장비 그룹 ID',
            ],
            'measurement-exclude' => [
                'description' => '제외된 측정값',
                'help' => 'Kafka로 전송하지 않을 탐색 모듈',
            ],
            'debug' => [
                'description' => '디버그',
                'help' => 'Kafka 내부 저장 과정에 대한 상세 로그를 활성화합니다',
            ],
            'security' => [
                'debug' => [
                    'description' => '보안 디버그',
                    'help' => 'Kafka 브로커와의 보안 통신에 대한 상세 정보를 표시합니다',
                ],
            ],
            'broker' => [
                'list' => [
                    'description' => 'Kafka 브로커 서버 목록 (host:port 형식)',
                    'help' => 'host:port 형식의 Kafka 브로커 목록입니다.',
                ],
            ],
            'idempotence' => [
                'description' => '멱등성',
                'help' => 'true로 설정하면 프로듀서가 메시지를 정확히 한 번, 원래 순서대로 전송합니다',
            ],
            'topic' => [
                'description' => '토픽',
                'help' => '메시지를 구성하는 데 사용되는 카테고리',
            ],
            'ssl' => [
                'enable' => [
                    'description' => 'SSL 활성화',
                    'help' => 'Kafka에서 SSL 지원을 활성화합니다',
                ],
                'protocol' => [
                    'description' => 'SSL 프로토콜',
                    'help' => '브로커와 통신하는 데 사용되는 프로토콜',
                ],
                'ca' => [
                    'location' => [
                        'description' => 'SSL 인증 기관 위치',
                        'help' => '브로커의 키를 확인하기 위한 CA 인증서 파일 또는 디렉터리 경로',
                    ],
                ],
                'certificate' => [
                    'location' => [
                        'description' => 'SSL 인증서 위치',
                        'help' => '인증에 사용되는 클라이언트 공개 키(PEM) 경로',
                    ],
                ],
                'key' => [
                    'location' => [
                        'description' => 'SSL 인증서 키 위치',
                        'help' => '인증에 사용되는 클라이언트 개인 키(PEM) 경로',
                    ],
                    'password' => [
                        'description' => 'SSL 인증서 키 비밀번호',
                        'help' => '개인 키 암호 (kafka.ssl.key.location과 함께 사용)',
                    ],
                ],
                'keystore' => [
                    'location' => [
                        'description' => 'SSL 키스토어 인증서 위치',
                        'help' => '인증에 사용되는 클라이언트 키스토어(PKCS#12) 경로',
                    ],
                    'password' => [
                        'description' => 'SSL 키스토어 키 비밀번호',
                        'help' => '클라이언트 키스토어(PKCS#12) 비밀번호',
                    ],
                ],
            ],
            'flush' => [
                'timeout' => [
                    'description' => 'Kafka 플러시 시간 초과',
                    'help' => '대기열의 메시지를 플러시하기 위해 Kafka가 대기하는 시간 초과',
                ],
            ],
            'buffer' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka 버퍼 최대 메시지 수',
                        'help' => '폴러 메모리에 보관할 Kafka 버퍼 최대 허용 메시지 수',
                    ],
                ],
            ],
            'batch' => [
                'max' => [
                    'message' => [
                        'description' => 'Kafka 배치 최대 메시지 수',
                        'help' => 'Kafka 서버에 한 번 호출할 때 전송할 최대 메시지 수',
                    ],
                ],
            ],
            'linger' => [
                'ms' => [
                    'description' => 'Kafka 배치 전송 전 대기 시간 (ms)',
                    'help' => '배치 전송 전 폴러 메모리에서 메시지를 누적하기 위해 Kafka가 대기하는 시간 (ms)',
                ],
            ],
            'request' => [
                'required' => [
                    'acks' => [
                        'description' => 'Kafka 요청 필수 acks',
                        'help' => 'Kafka 요청 필수 acks',
                    ],
                ],
            ],
        ],
        'int_core' => [
            'description' => '코어 포트 메뉴 활성화',
            'help' => '웹 인터페이스에서 코어 포트 메뉴를 활성화합니다',
        ],
        'int_customers' => [
            'description' => '고객 포트 메뉴 활성화',
            'help' => '웹 인터페이스에서 고객 포트 메뉴를 활성화합니다',
        ],
        'int_peering' => [
            'description' => '피어링 포트 메뉴 활성화',
            'help' => '웹 인터페이스에서 피어링 포트 메뉴를 활성화합니다',
        ],
        'int_transit' => [
            'description' => '트랜짓 포트 메뉴 활성화',
            'help' => '웹 인터페이스에서 트랜짓 포트 메뉴를 활성화합니다',
        ],
        'int_l2tp' => [
            'description' => 'L2TP 포트 메뉴 활성화',
            'help' => '웹 인터페이스에서 L2TP 포트 메뉴를 활성화합니다',
        ],
        'ipmitool' => [
            'description' => 'ipmitool 경로',
        ],
        'ipmi.type' => [
            'description' => 'IPMI 유형',
            'help' => '사용할 IPMI 유형입니다. `lan`, `lanplus`, `open`, `sol`, `raw` 또는 `shell`일 수 있습니다',
        ],
        'ipmi_unit' => [
            'description' => 'IPMI 단위',
            'help' => '탐색 가능한 IPMI 단위 유형',
        ],
        'libvirt_protocols' => [
            'description' => 'Libvirt 프로토콜',
            'help' => 'Libvirt 연결에 사용할 프로토콜',
        ],
        'libvirt_username' => [
            'description' => 'Libvirt 사용자 이름',
            'help' => 'Libvirt 연결에 사용할 사용자 이름',
        ],
        'location_map' => [
            'description' => '특정 위치 매핑',
            'help' => 'sysLocation 값을 다른 값으로 매핑합니다.',
        ],
        'location_map_regex' => [
            'description' => '정규식을 사용한 위치 매핑',
            'help' => '정규식을 사용하여 sysLocation 값을 다른 값으로 매핑합니다.',
        ],
        'location_map_regex_sub' => [
            'description' => '정규식 치환을 사용한 위치 매핑',
            'help' => '정규식 치환을 사용하여 sysLocation 값을 변환합니다.',
        ],
        'login_message' => [
            'description' => '로그인 메시지',
            'help' => '로그인 페이지에 표시됩니다',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => 'MAC OUI 조회 활성화',
                'help' => 'MAC 주소 벤더(OUI) 조회를 활성화합니다 (데이터는 daily.sh로 다운로드됩니다)',
            ],
        ],
        'mono_font' => [
            'description' => '고정폭 글꼴',
        ],
        'mtr' => [
            'description' => 'mtr 경로',
        ],
        'mtu_options' => [
            'bytes' => [
                'description' => 'MTU 테스트 패킷 크기',
                'help' => 'MTU 테스트의 패킷 크기 (바이트, 비워두면 MTU 테스트 비활성화)',
            ],
        ],
        'mydomain' => [
            'description' => '기본 도메인',
            'help' => '이 도메인은 네트워크 자동 탐색 및 기타 프로세스에 사용됩니다. LibreNMS는 비정규화된 호스트명에 이 도메인을 추가하려고 시도합니다.',
        ],
        'network_map_show_on_worldmap' => [
            'description' => '지도에 네트워크 링크 표시',
            'help' => '세계 지도에서 다른 위치 간의 네트워크 링크를 표시합니다',
        ],
        'network_map_worldmap_show_disabled_alerts' => [
            'description' => '경보 비활성화 장비 표시',
            'help' => '경보가 비활성화된 장비를 네트워크 맵에 표시합니다',
        ],
        'network_map_worldmap_link_type' => [
            'description' => '네트워크 맵 소스',
            'help' => '네트워크 맵 링크의 데이터 소스를 선택합니다',
        ],
        'nfsen_enable' => [
            'description' => 'NfSen 활성화',
            'help' => 'NfSen과의 연동을 활성화합니다',
        ],
        'nfsen_rrds' => [
            'description' => 'NfSen RRD 디렉터리',
            'help' => 'NFSen RRD 파일이 있는 위치를 지정합니다.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'NfSen 하위 디렉터리 레이아웃 설정',
            'help' => 'NfSen에 설정한 하위 디렉터리 레이아웃과 일치해야 합니다. 기본값은 1입니다.',
        ],
        'nfsen_last_max' => [
            'description' => '마지막 최댓값',
        ],
        'nfsen_top_max' => [
            'description' => '상위 최댓값',
            'help' => '통계의 최대 topN 값',
        ],
        'nfsen_top_N' => [
            'description' => '상위 N',
        ],
        'nfsen_top_default' => [
            'description' => '기본 상위 N',
        ],
        'nfsen_stats_default' => [
            'description' => '기본 통계',
        ],
        'nfsen_order_default' => [
            'description' => '기본 정렬',
        ],
        'nfsen_last_default' => [
            'description' => '기본 마지막',
        ],
        'nfsen_lasts' => [
            'description' => '기본 마지막 옵션',
        ],
        'nfsen_base' => [
            'description' => 'NFSen 기본 디렉터리',
            'help' => '장비별 그래프를 찾는 데 사용됩니다',
        ],
        'nfsen_split_char' => [
            'description' => '구분 문자',
            'help' => '장비 호스트명의 점(.)을 대체할 문자입니다. 일반적으로: `_`',
        ],
        'nfsen_suffix' => [
            'description' => '파일 이름 접미사',
            'help' => 'NfSen의 장비 이름은 21자로 제한됩니다. 긴 도메인 이름을 처리하기 위해 이 부분이 제거됩니다.',
        ],
        'no_proxy' => [
            'description' => '프록시 예외',
            'help' => 'no_proxy 환경 변수를 사용할 수 없는 경우 대체로 설정합니다. 무시할 IP, 호스트 또는 도메인을 쉼표로 구분합니다.',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => '활성화',
                'help' => 'OpenTSDB로 메트릭을 내보냅니다',
            ],
            'host' => [
                'description' => '서버',
                'help' => '데이터를 전송할 OpenTSDB 서버의 IP 또는 호스트명',
            ],
            'port' => [
                'description' => '포트',
                'help' => 'OpenTSDB 서버 연결에 사용할 포트',
            ],
        ],
        'overview_show_sysDescr' => [
            'description' => '장비 개요에 sysDescr 표시',
            'help' => '장비 개요 페이지에 sysDescr을 표시합니다',
        ],
        'own_hostname' => [
            'description' => 'LibreNMS 호스트명',
            'help' => 'LibreNMS 서버가 추가된 호스트명/IP로 설정해야 합니다',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => '반환할 기본 그룹 설정',
            ],
            'ignore_groups' => [
                'description' => '백업하지 않을 Oxidized 그룹',
                'help' => 'Oxidized로 전송에서 제외할 그룹 (변수 매핑으로 설정됨)',
            ],
            'enabled' => [
                'description' => 'Oxidized 지원 활성화',
            ],
            'features' => [
                'versioning' => [
                    'description' => '설정 버전 관리 접근 활성화',
                    'help' => 'Oxidized 설정 버전 관리를 활성화합니다 (git 백엔드 필요)',
                ],
            ],
            'group_support' => [
                'description' => 'Oxidized에 그룹 반환 활성화',
            ],
            'ignore_os' => [
                'description' => '백업하지 않을 OS',
                'help' => 'Oxidized로 나열된 OS를 백업하지 않습니다. OS는 LibreNMS OS 이름과 일치해야 합니다.',
            ],
            'ignore_types' => [
                'description' => '백업하지 않을 장비 유형',
                'help' => 'Oxidized로 나열된 장비 유형을 백업하지 않습니다.',
            ],
            'reload_nodes' => [
                'description' => '장비 추가 시마다 Oxidized 노드 목록 새로 고침',
            ],
            'maps' => [
                'description' => '변수 매핑',
                'help' => '그룹이나 다른 변수를 설정하거나 다른 OS 이름을 매핑하는 데 사용됩니다.',
            ],
            'url' => [
                'description' => 'Oxidized API URL',
                'help' => 'Oxidized API URL (예: http://127.0.0.1:8888)',
            ],
        ],
        'page_refresh' => [
            'description' => '페이지 새로 고침',
            'help' => '페이지를 새로 고치는 주기 (초). 0으로 설정하면 비활성화됩니다.',
        ],
        'password' => [
            'min_length' => [
                'description' => '최소 비밀번호 길이',
                'help' => '지정한 길이보다 짧은 비밀번호는 거부됩니다',
            ],
            'uncompromised' => [
                'description' => '유출되지 않은 비밀번호 요구',
                'help' => 'k-익명성을 사용하여 HaveIBeenPwned 데이터베이스에서 비밀번호를 확인합니다',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'PeeringDB 조회 활성화',
                'help' => 'PeeringDB 조회를 활성화합니다 (데이터는 daily.sh로 다운로드됩니다)',
            ],
        ],
        'percentile_value' => [
            'description' => '퍼센타일 값',
            'help' => '트래픽 그래프에 사용할 퍼센타일 값. 0은 비활성화를 의미합니다.',
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => '동적 장비 그룹을 통한 사용자 접근 활성화',
                ],
            ],
        ],
        'bad_if' => [
            'description' => '잘못된 인터페이스 ifDescr',
            'help' => '무시해야 할 네트워크 인터페이스 IF-MIB::ifDescr',
        ],
        'bad_if_regexp' => [
            'description' => '잘못된 인터페이스 ifDescr 정규식',
            'help' => '정규식을 사용하여 무시해야 할 네트워크 인터페이스 IF-MIB::ifDescr',
        ],
        'bad_ifalias_regexp' => [
            'description' => '잘못된 인터페이스 ifAlias 정규식',
            'help' => '정규식을 사용하여 무시해야 할 네트워크 인터페이스 IF-MIB::ifAlias',
        ],
        'bad_ifname_regexp' => [
            'description' => '잘못된 인터페이스 ifName 정규식',
            'help' => '정규식을 사용하여 무시해야 할 네트워크 인터페이스 IF-MIB::ifName',
        ],
        'bad_ifoperstatus' => [
            'description' => '잘못된 인터페이스 ifOperStatus',
            'help' => '무시해야 할 네트워크 인터페이스 IF-MIB::ifOperStatus',
        ],
        'bad_iftype' => [
            'description' => '잘못된 인터페이스 ifType',
            'help' => '무시해야 할 네트워크 인터페이스 IF-MIB::ifType',
        ],
        'ping' => [
            'description' => 'ping 경로',
        ],
        'poller_modules' => [
            'unix-agent' => ['description' => 'Unix 에이전트'],
            'os' => ['description' => 'OS'],
            'ipmi' => ['description' => 'IPMI'],
            'qos' => ['description' => 'QoS'],
            'sensors' => ['description' => '센서'],
            'processors' => ['description' => '프로세서'],
            'mempools' => ['description' => '메모리 풀'],
            'storage' => ['description' => '스토리지'],
            'netstats' => ['description' => '네트워크 통계'],
            'hr-mib' => ['description' => 'HR MIB'],
            'ucd-mib' => ['description' => 'UCD MIB'],
            'ipSystemStats' => ['description' => 'IP 시스템 통계'],
            'ports' => ['description' => '포트'],
            'ports-stack' => ['description' => '포트 스택'],
            'bgp-peers' => ['description' => 'BGP 피어'],
            'vlans' => ['description' => 'VLAN'],
            'junose-atm-vp' => ['description' => 'JunOS ATM VP'],
            'ucd-diskio' => ['description' => 'UCD DiskIO'],
            'wireless' => ['description' => '무선'],
            'ospf' => ['description' => 'OSPF'],
            'ospfv3' => ['description' => 'OSPFv3'],
            'isis' => ['description' => 'ISIS'],
            'cisco-ipsec-flow-monitor' => ['description' => 'Cisco IPSec 플로우 모니터'],
            'cisco-remote-access-monitor' => ['description' => 'Cisco 원격 접근 모니터'],
            'cisco-cef' => ['description' => 'Cisco CEF'],
            'slas' => ['description' => '서비스 수준 협약 추적'],
            'mac-accounting' => ['description' => 'Cisco MAC 어카운팅'],
            'cipsec-tunnels' => ['description' => 'Cipsec 터널'],
            'cisco-ace-loadbalancer' => ['description' => 'Cisco ACE 로드밸런서'],
            'cisco-ace-serverfarms' => ['description' => 'Cisco ACE 서버 팜'],
            'cisco-otv' => ['description' => 'Cisco OTV'],
            'cisco-qfp' => ['description' => 'Cisco QFP'],
            'cisco-vpdn' => ['description' => 'Cisco VPDN'],
            'nac' => ['description' => 'NAC'],
            'netscaler-vsvr' => ['description' => 'Netscaler VSVR'],
            'aruba-controller' => ['description' => 'Aruba 컨트롤러'],
            'availability' => ['description' => '가용성'],
            'entity-physical' => ['description' => '엔티티 물리'],
            'entity-state' => ['description' => '엔티티 상태'],
            'applications' => ['description' => '애플리케이션'],
            'stp' => ['description' => 'STP'],
            'vminfo' => ['description' => '하이퍼바이저 VM 정보'],
            'ntp' => ['description' => 'NTP'],
            'loadbalancers' => ['description' => '로드밸런서'],
            'mef' => ['description' => 'MEF'],
            'mpls' => ['description' => 'MPLS'],
            'xdsl' => ['description' => 'xDSL'],
            'printer-supplies' => ['description' => '프린터 소모품'],
            'port-security' => ['description' => '포트 보안'],
        ],
        'polling.selected_ports' => [
            'description' => '선택적 포트 폴링',
            'help' => '활성화되고 활성화된 포트만 폴링하는 선택적 포트 폴링을 활성화합니다',
        ],
        'ports_fdb_purge' => [
            'description' => '이 기간보다 오래된 포트 FDB 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'ports_ipv4_neighbours' => [
            'description' => '포트 IPv4 이웃 조회 방법',
            'help' => '포트 세부 정보 볼 때 IPv4 이웃을 조회하는 방법입니다. ARP는 ARP 테이블을 사용하여 일치하는 IP 및 MAC 주소를 가진 장비를 찾습니다. 서브넷은 동일 서브넷의 IP 주소를 가진 장비를 찾습니다.',
        ],
        'ports_nac_purge' => [
            'description' => '이 기간보다 오래된 포트 NAC 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'ports_page_default' => [
            'description' => '기본 포트 탭',
            'help' => '장비 페이지에서 포트를 볼 때 기본으로 열릴 탭',
        ],
        'ports_purge' => [
            'description' => '삭제된 포트 제거',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'processor.default_perc_warn' => [
            'description' => '기본 프로세서 사용률 경고 임계값',
            'help' => '경고가 발생하기 전 프로세서 사용률의 기본 임계값',
        ],
        'prometheus' => [
            'enable' => [
                'description' => '활성화',
                'help' => 'Prometheus Push Gateway로 메트릭을 내보냅니다',
            ],
            'url' => [
                'description' => 'URL',
                'help' => '데이터를 전송할 Prometheus Push Gateway의 URL',
            ],
            'Job' => [
                'description' => '작업',
                'help' => '내보낸 메트릭의 작업 레이블',
            ],
            'attach_sysname' => [
                'description' => '장비 sysName 첨부',
                'help' => 'Prometheus에 sysName 정보를 추가합니다.',
            ],
            'prefix' => [
                'description' => '접두사',
                'help' => '내보낸 메트릭 이름 앞에 추가할 선택적 텍스트',
            ],
        ],
        'public_status' => [
            'description' => '상태 공개 표시',
            'help' => '인증 없이 로그인 페이지에서 일부 장비의 상태를 표시합니다.',
        ],
        'routes_max_number' => [
            'description' => '탐색을 허용하는 최대 경로 수',
            'help' => '라우팅 테이블이 이 수보다 크면 경로가 탐색되지 않습니다',
        ],
        'default_port_group' => [
            'description' => '기본 포트 그룹',
            'help' => '새로 탐색된 포트는 이 포트 그룹에 할당됩니다.',
        ],
        'nets' => [
            'description' => '자동 탐색 네트워크',
            'help' => '장비가 자동으로 탐색될 네트워크입니다.',
        ],
        'autodiscovery' => [
            'bgp' => [
                'description' => 'BGP 이웃 탐색 활성화',
                'help' => 'BGP 피어를 기반으로 링크와 이웃을 추가합니다',
            ],
            'cdp_exclude' => [
                'platform_regexp' => [
                    'description' => 'CDP 제외 플랫폼 정규식',
                    'help' => 'sysName이 정규식과 일치하면 CDP로 장비가 추가되지 않도록 합니다',
                ],
            ],
            'nets-exclude' => [
                'description' => '무시할 네트워크/IP',
                'help' => '자동으로 탐색되지 않을 네트워크/IP입니다. 자동 탐색 네트워크에서도 IP를 제외합니다',
            ],
            'ospf' => [
                'description' => 'OSPF 이웃 탐색 활성화',
                'help' => 'OSPF 피어를 기반으로 링크와 이웃을 추가합니다',
            ],
            'ospfv3' => [
                'description' => 'OSPFv3 이웃 탐색 활성화',
                'help' => 'OSPFv3 피어를 기반으로 링크와 이웃을 추가합니다',
            ],
            'xdp' => [
                'description' => 'xDP 탐색 프로토콜 활성화',
                'help' => 'LLDP, CDP 등 프로토콜을 사용하여 네트워크 토폴로지와 이웃을 탐색하고 LibreNMS에 추가합니다',
            ],
            'xdp_exclude' => [
                'sysname_regexp' => [
                    'description' => 'xDP 제외 sysName 정규식',
                    'help' => 'sysName이 정규식과 일치하면 장비가 추가되지 않도록 합니다',
                ],
                'sysdesc_regexp' => [
                    'description' => 'xDP 제외 sysDescr 정규식',
                    'help' => 'sysDescr이 정규식과 일치하면 장비가 추가되지 않도록 합니다',
                ],
            ],
        ],
        'radius' => [
            'default_roles' => [
                'description' => '기본 사용자 역할',
                'help' => 'Radius가 역할을 지정하는 속성을 보내지 않는 경우 사용자에게 할당될 역할을 설정합니다',
            ],
            'enforce_roles' => [
                'description' => '로그인 시 역할 강제 적용',
                'help' => '활성화되면 로그인 시 Filter-ID 속성이나 radius.default_roles에 지정된 역할로 역할이 설정됩니다. 그렇지 않으면 사용자 생성 시에만 설정되고 이후에는 변경되지 않습니다.',
            ],
        ],
        'rancid_configs' => [
            'description' => 'RANCID 설정',
            'help' => 'RANCID 설정 디렉터리입니다. 장비 페이지에서 설정 차이를 표시하는 데 사용됩니다',
        ],
        'rancid_repo_type' => [
            'description' => 'RANCID 저장소 유형',
            'help' => 'RANCID에서 사용하는 저장소 유형입니다. 장비 페이지에서 설정 차이를 표시하는 데 사용됩니다',
        ],
        'rancid_repo_url' => [
            'description' => 'RANCID 저장소 URL',
            'help' => 'RANCID 저장소 URL입니다. 베어 Git 저장소를 시각화하는 GitWeb을 가리키는 데 사용됩니다',
        ],
        'rancid_ignorecomments' => [
            'description' => 'RANCID 주석 무시',
            'help' => 'RANCID 설정 비교 시 주석을 무시합니다. 장비 페이지에서 설정 차이를 표시하는 데 사용됩니다',
        ],
        'reporting' => [
            'error' => [
                'description' => '오류 보고서 전송',
                'help' => '분석 및 수정을 위해 LibreNMS에 일부 오류를 전송합니다',
            ],
            'usage' => [
                'description' => '사용량 보고서 전송',
                'help' => 'LibreNMS에 사용량 및 버전을 보고합니다. 익명 통계를 삭제하려면 정보 페이지를 방문하십시오.',
            ],
            'dump_errors' => [
                'description' => '디버그 오류 덤프 (설치가 손상될 수 있음)',
                'help' => '일반적으로 숨겨진 오류를 출력하여 개발자가 문제를 찾고 수정할 수 있게 합니다.',
            ],
            'throttle' => [
                'description' => '오류 보고 제한',
                'help' => '지정한 초 간격으로만 보고서가 전송됩니다. 0으로 설정하면 제한을 비활성화합니다.',
            ],
        ],
        'rewrite_if' => [
            'description' => 'ifDescr 다시 쓰기',
            'help' => 'ifDescr에서 인터페이스 유형 및 번호를 제거합니다. 예: GigabitEthernet0/1은 GigabitEthernet이 됩니다',
        ],
        'route_purge' => [
            'description' => '이 기간보다 오래된 경로 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'RRD heartbeat 값 변경 (기본값 600)',
            ],
            'step' => [
                'description' => 'RRD step 값 변경 (기본값 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'RRD 위치',
            'help' => 'RRD 파일의 위치입니다. 기본값은 LibreNMS 디렉터리 내의 rrd입니다. 이 설정을 변경해도 RRD 파일이 이동되지 않습니다.',
        ],
        'rrd_purge' => [
            'description' => '이 기간보다 오래된 RRD 파일 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'rrd_rra' => [
            'description' => 'RRD 형식 설정',
            'help' => '기존 RRD 파일을 삭제하지 않으면 변경할 수 없습니다.',
        ],
        'rrdcached' => [
            'description' => 'rrdcached 활성화 (소켓)',
            'help' => 'rrdcached 소켓 위치를 설정하여 활성화합니다. Unix 또는 네트워크 소켓 가능 (unix:/run/rrdcached.sock 또는 localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'rrdtool 경로',
        ],
        'rrdtool_tune' => [
            'description' => '모든 RRD 포트 파일을 최대값으로 조정',
            'help' => 'RRD 포트 파일의 최대값을 자동으로 조정합니다',
        ],
        'rrdtool_version' => [
            'description' => '서버의 rrdtool 버전 설정',
            'help' => '1.5.5 이상은 LibreNMS가 사용하는 모든 기능을 지원합니다. 설치된 버전보다 높게 설정하지 마십시오',
        ],
        'schedule_type' => [
            'alerting' => [
                'description' => '경보',
                'help' => '경보 작업 스케줄링 방법입니다. 레거시는 crontab 항목이 있으면 cron을 사용하고, service_billing_enabled가 true이면 디스패처 서비스를 사용합니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'cron' => 'Cron (alerts.php)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
            'billing' => [
                'description' => '과금',
                'help' => '과금 작업 스케줄링 방법입니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'cron' => 'Cron (poll-billing.php 및 billing-calculate.php)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
            'discovery' => [
                'description' => '탐색',
                'help' => '탐색 작업 스케줄링 방법입니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'cron' => 'Cron (lnms device:discover)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
            'ping' => [
                'description' => '빠른 핑',
                'help' => '빠른 핑 작업 스케줄링 방법입니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'disabled' => '비활성화 (폴링 중에만 핑)',
                    'cron' => 'Cron (ping.php)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
            'poller' => [
                'description' => '폴러',
                'help' => '폴러 작업 스케줄링 방법입니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'cron' => 'Cron (poller.php)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
            'services' => [
                'description' => '서비스',
                'help' => '서비스 작업 스케줄링 방법입니다.',
                'options' => [
                    'legacy' => '레거시 (제한 없음)',
                    'cron' => 'Cron (check-services.php)',
                    'dispatcher' => '디스패처 서비스',
                ],
            ],
        ],
        'sensors' => [
            'guess_limits' => [
                'description' => '센서 한도 추정',
                'help' => '활성화되면 LibreNMS가 센서 유형과 값을 기반으로 센서 한도를 추정하려고 시도합니다. 항상 정확하지 않을 수 있습니다.',
            ],
        ],
        'service_master_timeout' => [
            'description' => '마스터 디스패처 시간 초과',
            'help' => '마스터 잠금이 만료되기까지의 시간입니다. 마스터가 사라지면 이 시간만큼 기다린 후 다른 노드가 인계받습니다.',
        ],
        'service_ping_frequency' => [
            'description' => '핑 주기',
            'help' => '모든 장비에 빠른 핑을 실행하는 주기',
        ],
        'service_poller_workers' => [
            'description' => '폴러 워커 수',
            'help' => '생성할 폴러 워커의 수입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_poller_frequency' => [
            'description' => '폴링 주기 (주의!)',
            'help' => '장비를 폴링하는 주기입니다. 모든 노드의 기본값을 설정합니다. 주의! RRD 파일을 수정하지 않고 변경하면 그래프가 깨집니다.',
        ],
        'service_poller_down_retry' => [
            'description' => '장비 다운 시 재시도',
            'help' => '폴링 시도 시 장비가 다운 상태이면 재시도 전 대기 시간입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_discovery_workers' => [
            'description' => '탐색 워커 수',
            'help' => '실행할 탐색 워커의 수입니다. 너무 높게 설정하면 과부하가 발생할 수 있습니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_discovery_frequency' => [
            'description' => '탐색 주기',
            'help' => '장비 탐색을 실행하는 주기입니다. 모든 노드의 기본값을 설정합니다. 기본값은 하루 4회입니다.',
        ],
        'service_services_workers' => [
            'description' => '서비스 워커 수',
            'help' => '서비스 워커 수입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_services_frequency' => [
            'description' => '서비스 주기',
            'help' => '서비스를 실행하는 주기입니다. 폴링 주기와 일치해야 합니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_billing_frequency' => [
            'description' => '과금 주기',
            'help' => '과금 데이터를 수집하는 주기입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_billing_calculate_frequency' => [
            'description' => '과금 계산 주기',
            'help' => '과금 사용량을 계산하는 주기입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_alerting_frequency' => [
            'description' => '경보 점검 주기',
            'help' => '경보 규칙을 점검하는 주기입니다. 데이터는 폴링 주기에 따라서만 갱신됩니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_update_enabled' => [
            'description' => '일일 유지보수 활성화',
            'help' => 'daily.sh 유지보수 스크립트를 실행하고 이후 디스패처 서비스를 재시작합니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_update_frequency' => [
            'description' => '유지보수 주기',
            'help' => '일일 유지보수를 실행하는 주기입니다. 기본값은 1일이며 변경하지 않는 것을 강력히 권장합니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_loglevel' => [
            'description' => '로그 레벨',
            'help' => '디스패치 서비스의 로그 레벨입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_watchdog_enabled' => [
            'description' => '워치독 활성화',
            'help' => '워치독은 로그 파일을 모니터링하고 갱신되지 않으면 서비스를 재시작합니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_watchdog_log' => [
            'description' => '감시할 로그 파일',
            'help' => '기본값은 LibreNMS 로그 파일입니다. 모든 노드의 기본값을 설정합니다.',
        ],
        'service_health_file' => [
            'description' => '서비스 상태 파일',
            'help' => '디스패처 서비스가 실행 중인지 확인하기 위한 상태 파일 경로',
        ],
        'shorthost_target_length' => [
            'description' => '축약된 호스트명 최대 길이',
            'help' => '호스트명을 최대 길이로 축약합니다. 항상 완전한 서브도메인 부분을 유지합니다',
        ],
        'show_locations' => [
            'description' => '탐색 바에 위치 표시',
            'help' => '탐색 바에 위치를 표시합니다',
        ],
        'show_locations_dropdown' => [
            'description' => '드롭다운에 위치 표시',
            'help' => '드롭다운 메뉴에 위치를 표시합니다',
        ],
        'show_services' => [
            'description' => '탐색 바에 서비스 표시',
            'help' => '탐색 바에 서비스를 표시합니다',
        ],
        'site_style' => [
            'description' => '기본 테마',
            'options' => [
                'device' => '장비',
                'blue' => '파란색',
                'dark' => '다크',
                'light' => '라이트',
                'mono' => '단색',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => '전송 방식 (우선순위)',
                'help' => '활성화할 전송 방식을 선택하고 시도 순서대로 정렬합니다.',
            ],
            'version' => [
                'description' => '버전 (우선순위)',
                'help' => '활성화할 버전을 선택하고 시도 순서대로 정렬합니다.',
            ],
            'community' => [
                'description' => '커뮤니티 (우선순위)',
                'help' => 'v1 및 v2c용 커뮤니티 문자열을 입력하고 시도 순서대로 정렬합니다',
            ],
            'max_oid' => [
                'description' => '최대 OID 수',
                'help' => '쿼리당 최대 OID 수입니다. OS 및 장비 수준에서 재정의할 수 있습니다.',
            ],
            'max_repeaters' => [
                'description' => '최대 반복자',
                'help' => 'SNMP 대량 요청에 사용할 반복자를 설정합니다',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => 'OID에 대한 SNMP 대량 비활성화',
                    'help' => '특정 OID에 대해 SNMP 대량 작업을 비활성화합니다. 일반적으로 OS에서 설정해야 합니다. 형식: MIB::OID',
                ],
                'unordered' => [
                    'description' => 'OID에 대한 비순서 SNMP 응답 허용',
                    'help' => '특정 OID에 대한 SNMP 응답에서 비순서 OID를 무시합니다. 일반적으로 OS에서 설정해야 합니다. 형식: MIB::OID',
                ],
            ],
            'port' => [
                'description' => '포트',
                'help' => 'SNMP에 사용할 TCP/UDP 포트를 설정합니다',
            ],
            'timeout' => [
                'description' => '시간 초과',
                'help' => 'SNMP 시간 초과 (초 단위)',
            ],
            'retries' => [
                'description' => '재시도 횟수',
                'help' => '쿼리 재시도 횟수',
            ],
            'v3' => [
                'description' => 'SNMP v3 인증 (우선순위)',
                'help' => 'v3 인증 변수를 설정하고 시도 순서대로 정렬합니다',
                'auth' => '인증',
                'crypto' => '암호화',
                'fields' => [
                    'authalgo' => '알고리즘',
                    'authlevel' => '수준',
                    'authname' => '사용자 이름',
                    'authpass' => '비밀번호',
                    'cryptoalgo' => '알고리즘',
                    'cryptopass' => '비밀번호',
                ],
                'level' => [
                    'noAuthNoPriv' => '인증 없음, 개인 정보 보호 없음',
                    'authNoPriv' => '인증, 개인 정보 보호 없음',
                    'authPriv' => '인증 및 개인 정보 보호',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'snmpbulkwalk 경로',
        ],
        'snmpget' => [
            'description' => 'snmpget 경로',
        ],
        'snmpgetnext' => [
            'description' => 'snmpgetnext 경로',
        ],
        'snmptranslate' => [
            'description' => 'snmptranslate 경로',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'SNMP 트랩 이벤트 로그 생성',
                'help' => '트랩에 매핑된 작업과 독립적으로',
            ],
            'eventlog_detailed' => [
                'description' => '상세 로그 활성화',
                'help' => '트랩과 함께 수신된 모든 OID를 이벤트 로그에 추가합니다',
            ],
        ],
        'snmpwalk' => [
            'description' => 'snmpwalk 경로',
        ],
        'ssl_certificates' => [
            'auto_discover' => [
                'description' => 'SSL 인증서 자동 탐색',
                'help' => 'SSL 인증서를 자동으로 탐색합니다',
            ],
            'skip_hosts' => [
                'description' => '제외할 호스트',
                'help' => 'SSL 인증서 탐색에서 제외할 호스트',
            ],
            'days_until_expiry_warning' => [
                'description' => '경고 (일)',
                'help' => '인증서 만료까지의 일수가 이 값 이하이면 경고를 발생시킵니다',
            ],
            'days_until_expiry_danger' => [
                'description' => '위험 (일)',
                'help' => '인증서 만료까지의 일수가 이 값 이하이면 위험 경보를 발생시킵니다',
            ],
        ],
        'sso' => [
            'create_users' => [
                'description' => '사용자 생성',
                'help' => '로그인 시 새 사용자를 생성할지 여부',
            ],
            'descr_attr' => [
                'description' => '사용자 설명 속성',
                'help' => '사용자 설명을 포함하는 속성',
            ],
            'email_attr' => [
                'description' => '이메일 속성',
                'help' => '사용자의 이메일 주소를 포함하는 속성',
            ],
            'group_attr' => [
                'description' => '그룹 속성',
                'help' => '매핑을 사용하는 경우 그룹 정보를 포함하는 속성',
            ],
            'group_delimiter' => [
                'description' => '그룹 구분자',
                'help' => '매핑 그룹 전략을 사용하는 경우 그룹 정보에 사용할 구분자',
            ],
            'group_filter' => [
                'description' => '그룹 필터 정규식',
                'help' => '매핑 그룹 전략을 사용하는 경우 그룹 정보를 필터링하는 데 사용됩니다',
            ],
            'group_level_map' => [
                'description' => '그룹 수준 맵',
                'help' => '그룹을 역할에 매핑합니다.',
            ],
            'group_strategy' => [
                'description' => '그룹 전략',
                'help' => '그룹 매핑 방법',
            ],
            'level_attr' => [
                'description' => '수준 속성',
                'help' => '속성 그룹 전략을 사용하는 경우 사용할 속성',
            ],
            'mode' => [
                'description' => '모드',
                'help' => '환경 변수 또는 HTTP 헤더를 사용할지 여부',
            ],
            'realname_attr' => [
                'description' => '실제 이름 속성',
                'help' => '사용자의 실제 이름을 포함하는 속성',
            ],
            'static_level' => [
                'description' => '정적 수준',
                'help' => '정적 모드 사용 시 접근 권한이 있는 모든 사용자에게 적용할 역할 수준',
            ],
            'trusted_proxies' => [
                'description' => '신뢰할 수 있는 프록시',
                'help' => '신뢰할 수 있는 프록시 목록',
            ],
            'update_users' => [
                'description' => '사용자 업데이트',
                'help' => '로그인 시 사용자를 업데이트할지 여부',
            ],
            'user_attr' => [
                'description' => '사용자 속성',
                'help' => '사용자 이름을 포함하는 속성',
            ],
        ],
        'storage_perc_warn' => [
            'description' => '기본 스토리지 사용률 경고 임계값',
            'help' => '경고가 발생하기 전 스토리지 사용률의 기본 임계값. 0은 경고 비활성화를 의미합니다.',
        ],
        'syslog_filter' => [
            'description' => '다음을 포함하는 syslog 메시지 필터',
        ],
        'syslog_purge' => [
            'description' => '이 기간보다 오래된 Syslog 항목 삭제',
            'help' => 'daily.sh에 의해 정리됩니다',
        ],
        'title_image' => [
            'description' => '제목 이미지',
            'help' => '기본 제목 이미지를 재정의합니다. 같은 서버의 SVG는 포함되어 현재 테마에 동적으로 맞게 currentColor를 사용할 수 있습니다.',
        ],
        'traceroute' => [
            'description' => 'traceroute 경로',
        ],
        'twofactor' => [
            'description' => '이중 인증',
            'help' => '사용자가 시간 기반(TOTP) 또는 횟수 기반(HOTP) 일회용 비밀번호(OTP)를 활성화하고 사용할 수 있게 합니다',
        ],
        'twofactor_lock' => [
            'description' => '이중 인증 제한 시간 (초)',
            'help' => '이중 인증이 3회 연속 실패하면 추가 시도를 허용하기 전에 대기할 잠금 시간(초)입니다. 0으로 설정하면 영구 계정 잠금이 발생합니다',
        ],
        'unimus' => [
            'api_version' => [
                'description' => 'Unimus API 버전',
            ],
            'enabled' => [
                'description' => 'Unimus 지원 활성화',
                'help' => '장비 설정 탭에서 Unimus의 장비 설정 백업을 표시합니다',
            ],
            'token' => [
                'description' => 'Unimus API 토큰',
                'help' => 'Unimus에서 생성된 API 토큰 (기본/읽기 전용 접근으로 충분합니다)',
            ],
            'url' => [
                'description' => 'Unimus URL',
                'help' => 'Unimus 서버의 기본 URL, 예: http://unimus.example.com:8085',
            ],
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Unix 에이전트 연결 시간 초과',
            ],
            'port' => [
                'description' => '기본 Unix 에이전트 포트',
                'help' => 'Unix 에이전트 (check_mk)의 기본 포트',
            ],
            'read-timeout' => [
                'description' => 'Unix 에이전트 읽기 시간 초과',
            ],
        ],
        'update' => [
            'description' => './daily.sh에서 업데이트 활성화',
        ],
        'update_channel' => [
            'description' => '업데이트 채널',
            'options' => [
                'master' => '일별',
                'release' => '월별',
            ],
        ],
        'update_on_days' => [
            'description' => '지정된 요일에만 업데이트 실행',
            'help' => '설정되면 daily.sh는 오늘이 이 값 중 하나와 일치할 때만 코드를 업데이트합니다: monday-sunday 또는 mon-sun. 매일 업데이트하려면 비워두십시오.',
        ],
        'uptime_warning' => [
            'description' => '업타임이 이 값(초) 미만이면 경고 표시',
            'help' => '업타임이 이 값 미만이면 장비를 경고로 표시합니다. 0은 경고 비활성화를 의미합니다. 기본값 24시간',
        ],
        'virsh' => [
            'description' => 'virsh 경로',
        ],
        'web_mouseover' => [
            'description' => '마우스오버 활성화',
            'help' => '웹 인터페이스에서 마우스오버 그래프를 활성화합니다',
        ],
        'webui' => [
            'scheduled_maintenance_default_behavior' => [
                'description' => '기본 동작',
                'help' => '예약된 유지보수 관리 시 동작 옵션의 기본값입니다.',
            ],
            'alert_map_compact' => [
                'description' => '경보 맵 간결 보기',
                'help' => '작은 표시기로 경보 맵을 표시합니다',
            ],
            'alert_map_sort_status' => [
                'description' => '상태로 정렬',
                'help' => '상태별로 경보를 정렬합니다',
            ],
            'alert_map_use_device_groups' => [
                'description' => '장비 그룹 필터 사용',
                'help' => '장비 그룹 필터 사용을 활성화합니다',
            ],
            'alert_map_box_size' => [
                'description' => '경보 박스 너비',
                'help' => '전체 보기에서 박스 크기의 타일 너비(픽셀)를 입력합니다',
            ],
            'availability_map_box_size' => [
                'description' => '가용성 박스 너비',
                'help' => '전체 보기에서 박스 크기의 타일 너비(픽셀)를 입력합니다',
            ],
            'availability_map_compact' => [
                'description' => '가용성 맵 간결 보기',
                'help' => '작은 표시기로 가용성 맵을 표시합니다',
            ],
            'availability_map_sort_status' => [
                'description' => '상태로 정렬',
                'help' => '상태별로 장비 및 서비스를 정렬합니다',
            ],
            'availability_map_use_device_groups' => [
                'description' => '장비 그룹 필터 사용',
                'help' => '장비 그룹 필터 사용을 활성화합니다',
            ],
            'custom_css' => [
                'description' => '사용자 정의 CSS',
                'help' => '웹 인터페이스에 사용자 정의 CSS를 추가합니다',
            ],
            'default_dashboard_id' => [
                'description' => '기본 대시보드',
                'help' => '자체 기본값이 설정되지 않은 모든 사용자를 위한 전역 기본 dashboard_id',
            ],
            'dynamic_graphs' => [
                'description' => '동적 그래프 활성화',
                'help' => '그래프에서 확대/축소 및 이동을 가능하게 하는 동적 그래프를 활성화합니다',
            ],
            'global_search_result_limit' => [
                'description' => '최대 검색 결과 수 설정',
                'help' => '전역 검색 결과 한도',
            ],
            'graph_stacked' => [
                'description' => '누적 그래프 사용',
                'help' => '반전 그래프 대신 누적 그래프를 표시합니다',
            ],
            'graph_type' => [
                'description' => '그래프 유형 설정',
                'help' => '기본 그래프 유형을 설정합니다',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => '최소 그래프 높이 설정',
                'help' => '최소 그래프 높이 (기본값: 300)',
            ],
            'graph_stat_percentile_disable' => [
                'description' => '통계 그래프에서 퍼센타일 전역 비활성화',
                'help' => '퍼센타일 값과 선을 표시하는 그래프에서 표시를 비활성화합니다',
            ],
        ],
        'device_display_default' => [
            'description' => '기본 장비 표시 이름 템플릿',
            'help' => '모든 장비의 기본 표시 이름을 설정합니다 (장비별로 재정의 가능). 호스트명/IP: 장비가 추가된 호스트명 또는 IP만 표시. sysName: SNMP의 sysName만 표시. 호스트명 또는 sysName: 호스트명을 표시하되 IP인 경우 sysName을 표시.',
            'options' => [
                'hostname' => '호스트명 / IP',
                'sysName_fallback' => '호스트명, IP인 경우 sysName으로 대체',
                'sysName' => 'sysName',
                'ip' => 'IP (호스트명 IP 또는 확인된 IP)',
            ],
        ],
        'device_location_map_open' => [
            'description' => '위치 맵 열기',
            'help' => '기본적으로 위치 맵이 표시됩니다',
        ],
        'device_location_map_show_devices' => [
            'description' => '위치 맵에 장비 표시',
            'help' => '위치 맵이 표시될 때 모든 장비를 위치 맵에 표시합니다',
        ],
        'device_location_map_show_device_dependencies' => [
            'description' => '위치 맵에 장비 종속성 표시',
            'help' => '부모 종속성을 기반으로 위치 맵에 장비 간 링크를 표시합니다',
        ],
        'device_stats_avg_factor' => [
            'description' => '평균 인수',
            'help' => '지수 가중 이동 평균 함수를 사용하여 이동 평균을 계산합니다. 현재 값이 평균에 영향을 주는 정도를 제어하는 인수입니다. 1에 가까울수록 평균이 더 빠르게 변합니다.',
        ],
        'smokeping.integration' => [
            'description' => '활성화',
            'help' => 'Smokeping 연동을 활성화합니다',
        ],
        'smokeping.dir' => [
            'description' => 'RRD 경로',
            'help' => 'Smokeping RRD의 전체 경로',
        ],
        'smokeping.pings' => [
            'description' => '핑 수',
            'help' => 'Smokeping에 설정된 핑 수',
        ],
        'smokeping.url' => [
            'description' => 'Smokeping URL',
            'help' => 'Smokeping GUI의 전체 URL',
        ],
    ],
    'twofactor' => [
        'description' => '이중 인증 활성화',
        'help' => '내장된 이중 인증을 활성화합니다. 각 계정에서 직접 설정해야 활성화됩니다.',
    ],
    'units' => [
        'days' => '일',
        'ms' => 'ms',
        'seconds' => '초',
        'percent' => '%',
    ],
    'validate' => [
        'boolean' => ':value은(는) 유효한 불리언 값이 아닙니다',
        'color' => ':value은(는) 유효한 16진수 색상 코드가 아닙니다',
        'email' => ':value은(는) 유효한 이메일이 아닙니다',
        'float' => ':value은(는) 부동소수점 수가 아닙니다',
        'integer' => ':value은(는) 정수가 아닙니다',
        'password' => '비밀번호가 올바르지 않습니다',
        'select' => ':value은(는) 허용된 값이 아닙니다',
        'text' => ':value은(는) 허용되지 않습니다',
        'array' => '형식이 올바르지 않습니다',
        'password-array' => '형식이 올바르지 않습니다',
        'executable' => ':value은(는) 유효한 실행 파일이 아닙니다',
        'directory' => ':value은(는) 유효한 디렉터리가 아닙니다',
    ],
];
