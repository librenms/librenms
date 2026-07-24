<?php

return [
    'errors' => [
        'db_connect' => '데이터베이스 연결에 실패했습니다. 데이터베이스 서비스가 실행 중인지, 연결 설정을 확인하세요.',
        'db_auth' => '데이터베이스 연결에 실패했습니다. 자격 증명을 확인하세요: :error',
        'no_devices' => '지정한 장비 사양에 해당하는 장비를 찾을 수 없습니다.',
        'no_new_devices' => '새로운 장비가 없습니다.',
    ],
    'config:clear' => [
        'description' => '설정 캐시를 초기화합니다. 마지막 전체 설정 로드 이후 변경된 내용이 현재 설정에 반영됩니다.',
    ],
    'config:get' => [
        'description' => '설정 값을 가져옵니다.',
        'arguments' => [
            'setting' => '점 표기법으로 값을 가져올 설정 (예: snmp.community.0)',
        ],
        'options' => [
            'dump' => '전체 설정을 JSON 형식으로 출력합니다.',
        ],
    ],
    'config:list' => [
        'description' => '설정 항목을 목록으로 보거나 검색합니다.',
        'arguments' => [
            'search' => '설정 이름 또는 설명으로 설정 항목을 검색합니다.',
        ],
        'not_found' => '\':search\'에 해당하는 설정을 찾을 수 없습니다.',
    ],
    'config:set' => [
        'description' => '설정 값을 지정하거나 해제합니다.',
        'arguments' => [
            'setting' => '점 표기법으로 설정할 항목 (예: snmp.community.0). 배열에 추가하려면 .+를 접미사로 붙이세요.',
            'value' => '설정할 값. 생략하면 해당 설정이 해제됩니다.',
        ],
        'options' => [
            'ignore-checks' => '모든 안전 검사를 무시합니다.',
        ],
        'confirm' => ':setting을 기본값으로 초기화하시겠습니까?',
        'forget_from' => ':parent에서 :path를 제거하시겠습니까?',
        'errors' => [
            'append' => '배열이 아닌 설정에는 값을 추가할 수 없습니다.',
            'failed' => ':setting 설정에 실패했습니다.',
            'invalid' => '유효하지 않은 설정입니다. 입력값을 확인하세요.',
            'invalid_os' => '지정한 OS(:os)가 존재하지 않습니다.',
            'nodb' => '데이터베이스가 연결되지 않았습니다.',
            'no-validation' => ':setting을 설정할 수 없습니다. 유효성 검사 정의가 없습니다.',
        ],
    ],
    'db:seed' => [
        'existing_config' => '데이터베이스에 기존 설정이 있습니다. 계속하시겠습니까?',
    ],
    'dev:check' => [
        'description' => 'LibreNMS 코드 검사. 옵션 없이 실행하면 모든 검사를 수행합니다.',
        'arguments' => [
            'check' => '지정한 검사를 실행합니다: :checks',
        ],
        'options' => [
            'commands' => '실행될 명령어만 출력하고, 실제 검사는 수행하지 않습니다.',
            'db' => '데이터베이스 연결이 필요한 단위 테스트를 실행합니다.',
            'fail-fast' => '첫 번째 실패 시 검사를 중단합니다.',
            'full' => '변경된 파일 필터링을 무시하고 전체 검사를 실행합니다.',
            'module' => '테스트할 특정 모듈을 지정합니다. unit, --db, --snmpsim을 포함합니다.',
            'os' => '테스트할 특정 OS를 지정합니다. 정규식 또는 쉼표로 구분된 목록을 사용할 수 있습니다. unit, --db, --snmpsim을 포함합니다.',
            'os-modules-only' => '특정 OS 지정 시 OS 탐색 테스트를 건너뜁니다. 탐색 외 변경사항 검사 시 테스트 시간을 단축합니다.',
            'quiet' => '오류가 없으면 출력을 숨깁니다.',
            'snmpsim' => '단위 테스트에 snmpsim을 사용합니다.',
        ],
    ],
    'dev:simulate' => [
        'description' => '테스트 데이터를 사용하여 장비를 시뮬레이션합니다.',
        'arguments' => [
            'file' => 'LibreNMS에 추가하거나 업데이트할 snmprec 파일의 이름(기본 이름만). 파일을 지정하지 않으면 장비가 추가되거나 업데이트되지 않습니다.',
        ],
        'options' => [
            'multiple' => 'snmpsim 대신 커뮤니티 이름을 호스트명으로 사용합니다.',
            'remove' => '중단 후 장비를 삭제합니다.',
        ],
        'added' => '장비 :hostname (:id)이 추가되었습니다.',
        'exit' => '중단하려면 Ctrl-C를 누르세요.',
        'removed' => '장비 :id가 삭제되었습니다.',
        'updated' => '장비 :hostname (:id)이 업데이트되었습니다.',
        'setup' => ':dir에 snmpsim venv를 설정하는 중',
    ],
    'device:add' => [
        'description' => '새 장비를 추가합니다.',
        'arguments' => [
            'device spec' => '추가할 호스트명 또는 IP 주소',
        ],
        'options' => [
            'v1' => 'SNMP v1 사용',
            'v2c' => 'SNMP v2c 사용',
            'v3' => 'SNMP v3 사용',
            'display-name' => "장비 이름으로 표시할 문자열. 기본값은 호스트명입니다.\n대체자를 사용하는 간단한 템플릿을 사용할 수 있습니다: {{ \$hostname }}, {{ \$sysName }}, {{ \$sysName_fallback }}, {{ \$ip }}",
            'force' => '안전 검사 없이 장비를 추가합니다.',
            'group' => '폴러 그룹 (분산 폴링용)',
            'ping-fallback' => 'SNMP에 응답하지 않을 경우 ping 전용 장비로 추가합니다.',
            'port-association-mode' => '포트 매핑 방식을 설정합니다. Linux/Unix에는 ifName이 권장됩니다.',
            'community' => 'SNMP v1 또는 v2 커뮤니티',
            'transport' => '장비 연결에 사용할 전송 방식',
            'port' => 'SNMP 전송 포트',
            'security-name' => 'SNMPv3 보안 사용자 이름',
            'auth-password' => 'SNMPv3 인증 비밀번호',
            'auth-protocol' => 'SNMPv3 인증 프로토콜',
            'privacy-protocol' => 'SNMPv3 프라이버시 프로토콜',
            'privacy-password' => 'SNMPv3 프라이버시 비밀번호',
            'ping-only' => 'ping 전용 장비로 추가합니다.',
            'os' => 'ping 전용: OS 지정',
            'hardware' => 'ping 전용: 하드웨어 지정',
            'sysName' => 'ping 전용: sysName 지정',
        ],
        'validation-errors' => [
            'port.between' => '포트는 1~65535 사이의 값이어야 합니다.',
            'poller-group.in' => '지정한 폴러 그룹이 존재하지 않습니다.',
        ],
        'messages' => [
            'save_failed' => '장비 :hostname 저장에 실패했습니다.',
            'try_force' => '--force 옵션을 사용하여 안전 검사를 건너뛸 수 있습니다.',
            'added' => '장비 :hostname (:device_id)이 추가되었습니다.',
        ],
    ],
    'device:discover' => [
        'description' => '기존 장비에 대한 정보를 탐색합니다. 폴링할 항목을 정의합니다.',
        'arguments' => [
            'device spec' => '탐색할 장비 사양: device_id, 호스트명, 와일드카드 (*), odd, even, all',
        ],
        'options' => [
            'modules' => '실행할 모듈을 지정합니다. 서브모듈은 /로 추가할 수 있습니다. 여러 값을 허용합니다.',
            'os' => '지정한 운영체제의 장비만 탐색합니다.',
            'type' => '지정한 유형의 장비만 탐색합니다.',
        ],
        'errors' => [
            'none_up' => '장비가 다운되어 탐색할 수 없습니다.|모든 장비가 다운되어 탐색할 수 없습니다.',
            'none_actioned' => '탐색된 장비가 없습니다.',
        ],
        'actioned' => ':time 내에 :count개 장비를 탐색했습니다.',
        'starting' => '탐색을 시작합니다:',
    ],
    'device:ping' => [
        'description' => '장비에 ping을 보내고 응답 데이터를 기록합니다.',
        'arguments' => [
            'device spec' => 'ping할 장비: <Device ID>, <호스트명/IP>, all, fast ("fast"는 모든 장비에 ping하고 그래프와 상태를 업데이트합니다)',
        ],
        'options' => [
            'groups' => 'ping할 그룹 ID. 여러 그룹을 지정하려면 여러 번 사용하세요. (fast 옵션과 함께 사용)',
        ],
        'errors' => [
            'groups_without_fast' => '--groups (-g) 옵션은 "fast" 장비 사양과 함께만 사용할 수 있습니다.',
        ],
    ],
    'device:poll' => [
        'description' => '탐색에 정의된 대로 장비에서 데이터를 폴링합니다.',
        'arguments' => [
            'device spec' => '폴링할 장비 사양: device_id, 호스트명, 와일드카드 (*), odd, even, all',
        ],
        'options' => [
            'modules' => '실행할 단일 모듈을 지정합니다. 쉼표로 모듈을 구분하며, 서브모듈은 /로 추가할 수 있습니다.',
            'no-data' => '데이터 저장소(RRD, InfluxDB 등)를 업데이트하지 않습니다.',
            'os' => '지정한 운영체제의 장비만 폴링합니다.',
            'type' => '지정한 유형의 장비만 폴링합니다.',
        ],
        'errors' => [
            'none_up' => '장비가 다운되어 폴링할 수 없습니다.|모든 장비가 다운되어 폴링할 수 없습니다.',
            'none_actioned' => '폴링된 장비가 없습니다.',
        ],
        'actioned' => ':time 내에 :count개 장비를 폴링했습니다.',
        'starting' => '폴링을 시작합니다:',
    ],
    'device:remove' => [
        'doesnt_exists' => '해당 장비가 없습니다: :device',
    ],
    'key:rotate' => [
        'description' => 'APP_KEY를 교체합니다. 이전 키로 암호화된 데이터를 복호화하고 APP_KEY의 새 키로 저장합니다.',
        'arguments' => [
            'old_key' => '암호화된 데이터에 유효한 이전 APP_KEY',
        ],
        'options' => [
            'generate-new-key' => '.env에 새 키가 설정되지 않은 경우, .env의 APP_KEY로 데이터를 복호화하고 새 키를 생성하여 .env에 저장합니다.',
            'forgot-key' => '이전 키를 분실한 경우, 일부 LibreNMS 기능을 계속 사용하려면 암호화된 데이터를 모두 삭제해야 합니다.',
        ],
        'destroy' => '암호화된 모든 설정 데이터를 삭제하시겠습니까?',
        'destroy_confirm' => '이전 APP_KEY를 찾을 수 없는 경우에만 암호화된 데이터를 모두 삭제하세요!',
        'cleared-cache' => '설정이 캐시되어 있어, APP_KEY가 올바른지 확인하기 위해 캐시를 초기화했습니다. lnms key:rotate를 다시 실행하세요.',
        'backup_keys' => '두 키 모두 기록해 두세요! 문제가 발생할 경우 .env에 새 키를 설정하고 이전 키를 이 명령의 인수로 사용하세요.',
        'backup_key' => '이 키를 기록해 두세요! 암호화된 데이터에 접근하려면 이 키가 필요합니다.',
        'backups' => '이 명령은 데이터의 되돌릴 수 없는 손실을 초래할 수 있으며 모든 브라우저 세션을 무효화합니다. 백업이 있는지 확인하세요.',
        'confirm' => '백업이 있으며 계속 진행하겠습니다.',
        'decrypt-failed' => ':item 복호화에 실패했습니다. 건너뜁니다.',
        'failed' => '항목 복호화에 실패했습니다. 새 키를 APP_KEY로 설정하고 이전 키를 인수로 하여 다시 실행하세요.',
        'current_key' => '현재 APP_KEY: :key',
        'new_key' => '새 APP_KEY: :key',
        'old_key' => '이전 APP_KEY: :key',
        'save_key' => '새 키를 .env에 저장하시겠습니까?',
        'success' => '키 교체에 성공했습니다!',
        'validation-errors' => [
            'not_in' => ':attribute는 현재 APP_KEY와 달라야 합니다.',
            'required' => '이전 키 또는 --generate-new-key 옵션이 필요합니다.',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => '선택한 :option이 유효하지 않습니다. 다음 중 하나여야 합니다: :values',
        ],
    ],
    'maintenance:cleanup-database' => [
        'description' => '고아 항목에 대한 데이터베이스 정리를 수행합니다.',
    ],
    'maintenance:cleanup-networks' => [
        'delete' => '사용하지 않는 네트워크 :count개를 삭제하는 중',
    ],
    'maintenance:fetch-ouis' => [
        'description' => 'MAC OUI를 가져와 캐시하여 MAC 주소의 벤더 이름을 표시합니다.',
        'options' => [
            'force' => '명령 실행을 막는 설정이나 잠금을 무시합니다.',
            'wait' => '서버 부하를 방지하기 위해 스케줄러가 사용하는 임의의 대기 시간',
        ],
        'disabled' => 'Mac OUI 통합이 비활성화되어 있습니다 (:setting)',
        'enable_question' => 'Mac OUI 통합 및 예약 가져오기를 활성화하시겠습니까?',
        'recently_fetched' => 'MAC OUI 데이터베이스를 최근에 가져왔습니다. 업데이트를 건너뜁니다.',
        'waiting' => 'MAC OUI 업데이트 시도 전 :minutes분 대기 중|MAC OUI 업데이트 시도 전 :minutes분 대기 중',
        'starting' => '데이터베이스에 Mac OUI를 저장하는 중',
        'downloading' => '다운로드 중',
        'processing' => 'CSV 처리 중',
        'saving' => '결과 저장 중',
        'success' => 'OUI/벤더 매핑이 성공적으로 업데이트되었습니다. :count개 OUI가 수정되었습니다.|성공적으로 업데이트되었습니다. :count개 OUI가 수정되었습니다.',
        'error' => 'Mac OUI 처리 중 오류 발생:',
        'vendor_update' => ':vendor에 대해 OUI :oui를 추가하는 중',
    ],
    'maintenance:rrd-step' => [
        'description' => '설정된 step 및 heartbeat에 맞게 RRD 파일을 변환합니다.',
        'arguments' => [
            'device' => '호스트명, 장비 ID, 또는 all',
        ],
        'options' => [
            'confirm' => 'RRD 파일을 백업했음을 확인합니다.',
        ],
        'errors' => [
            'invalid' => '유효하지 않은 호스트명 또는 장비 ID가 지정되었습니다.',
        ],
        'confirm_backup' => '계속하기 전에 RRD 파일을 백업했는지 확인하세요.',
        'mismatched_heartbeat' => ':file: heartbeat가 일치하지 않습니다. :ds != :hb',
        'skipping' => ':file 건너뜁니다. step이 이미 :step입니다.',
        'converting' => ':file 변환 중:',
        'summary' => '변환됨: :converted  실패: :failed  건너뜀: :skipped',
    ],
    'maintenance:cleanup-syslog' => [
        'description' => '지정한 일수보다 오래된 syslog 항목을 정리합니다.',
        'arguments' => [
            'days' => 'syslog 항목을 보존할 일수 (기본값: syslog_purge 설정 값)',
        ],
        'bad_days_input' => '일수는 숫자여야 합니다.',
        'bad_days_setting' => '유효하지 않은 syslog_purge 설정으로 인해 syslog 정리가 비활성화되었습니다.',
        'delete' => ':days일보다 오래된 syslog 항목을 삭제했습니다 (:count개 행)',
        'disabled' => 'syslog 정리가 비활성화되었습니다. 일수 <= 0',
    ],
    'maintenance:discover-ssl-certificates' => [
        'description' => '장비에서 SSL 인증서를 탐색합니다 (HTTPS 포트 443)',
        'options' => [
            'device' => '탐색할 장비 사양: device_id, 호스트명, 또는 all',
        ],
        'no_devices' => '장비를 찾을 수 없습니다.',
        'summary' => '생성됨: :created, 업데이트됨: :updated, 실패: :failed',
    ],
    'maintenance:refresh-ssl-certificates' => [
        'description' => '저장된 SSL 인증서의 인증서 데이터를 갱신합니다.',
        'options' => [
            'id' => '갱신할 인증서 ID (생략하면 활성화된 모든 인증서를 갱신합니다)',
        ],
        'none' => '갱신할 활성화된 인증서가 없습니다.',
        'summary' => '갱신됨: :refreshed, 실패: :failed',
    ],
    'plugin:disable' => [
        'description' => '지정한 이름의 모든 플러그인을 비활성화합니다.',
        'arguments' => [
            'plugin' => '비활성화할 플러그인 이름 또는 모든 플러그인을 비활성화하려면 "all"',
        ],
        'already_disabled' => '플러그인이 이미 비활성화되어 있습니다.',
        'disabled' => ':count개 플러그인이 비활성화되었습니다.|:count개 플러그인이 비활성화되었습니다.',
        'failed' => '플러그인 비활성화에 실패했습니다.',
    ],
    'plugin:enable' => [
        'description' => '지정한 이름의 최신 플러그인을 활성화합니다.',
        'arguments' => [
            'plugin' => '활성화할 플러그인 이름 또는 모든 플러그인을 비활성화하려면 "all"',
        ],
        'already_enabled' => '플러그인이 이미 활성화되어 있습니다.',
        'enabled' => ':count개 플러그인이 활성화되었습니다.|:count개 플러그인이 활성화되었습니다.',
        'failed' => '플러그인 활성화에 실패했습니다.',
    ],
    'port:tune' => [
        'description' => '포트 RRD 파일을 조정하여 ifSpeed에 기반한 최대 전송 속도를 제한합니다.',
        'arguments' => [
            'device spec' => '조정할 장비 사양: device_id, 호스트명, 와일드카드 (*), odd, even, all',
            'ifname' => '일치시킬 포트 ifName. all 또는 *를 와일드카드로 사용할 수 있습니다.',
        ],
        'device' => '장비 :device:',
        'port' => '포트 :port 조정 중',
    ],
    'report:devices' => [
        'description' => '장비의 데이터를 출력합니다.',
        'columns' => '데이터베이스 컬럼:',
        'synthetic' => '추가 필드:',
        'counts' => '관계 수:',
        'arguments' => [
            'device spec' => '폴링할 장비 사양: device_id, 호스트명, 와일드카드 (*), odd, even, all',
        ],
        'options' => [
            'list-fields' => '유효한 필드 목록을 출력합니다.',
            'fields' => '표시할 필드의 쉼표로 구분된 목록. 유효한 옵션: 데이터베이스의 장비 컬럼 이름, 관계 수(ports_count), displayName. JSON 출력에는 사용되지 않습니다.',
            'output' => '데이터를 표시할 출력 형식: :types',
            'no-header' => '헤더를 추가하지 않습니다.',
            'relationships' => '포함할 관계의 쉼표로 구분된 목록. JSON 출력에만 사용됩니다.',
            'list-relationships' => '관계의 목록/설명을 출력합니다.',
            'all-relationships' => '모든 관계를 포함합니다. -r, --relationships가 우선합니다.',
            'devices-as-array' => '출력을 장비별 JSON 항목 대신 JSON 배열로 반환합니다.',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => '--probes 또는 --targets 중 하나를 사용하세요.',
        'config-insufficient' => 'smokeping 설정을 생성하려면 설정에서 "smokeping.probes", "fping", "fping6"이 설정되어야 합니다.',
        'dns-fail' => '확인할 수 없어 설정에서 제외되었습니다.',
        'description' => 'smokeping에서 사용할 수 있는 설정을 생성합니다.',
        'header-first' => '이 파일은 "lnms smokeping:generate"에 의해 자동으로 생성되었습니다.',
        'header-second' => '로컬 변경 사항은 백업 없이 덮어쓰일 수 있습니다.',
        'header-third' => '자세한 내용은 https://docs.librenms.org/Extensions/Smokeping/ 를 참조하세요.',
        'no-devices' => '적합한 장비를 찾을 수 없습니다. 장비가 비활성화되지 않아야 합니다.',
        'no-probes' => '최소 하나 이상의 프로브가 필요합니다.',
        'options' => [
            'probes' => '프로브 목록 생성 - smokeping 설정을 여러 파일로 분할할 때 사용합니다. "--targets"와 충돌합니다.',
            'targets' => '대상 목록 생성 - smokeping 설정을 여러 파일로 분할할 때 사용합니다. "--probes"와 충돌합니다.',
            'no-header' => '생성된 파일의 시작 부분에 상용구 주석을 추가하지 않습니다.',
            'no-dns' => 'DNS 조회를 건너뜁니다.',
            'single-process' => 'smokeping에 단일 프로세스만 사용합니다.',
            'compat' => '[사용 중단] gen_smokeping.php의 동작을 모방합니다.',
        ],
    ],
    'snmp:fetch' => [
        'description' => '장비에 대해 SNMP 쿼리를 실행합니다.',
        'arguments' => [
            'device spec' => '폴링할 장비 사양: device_id, 호스트명, 와일드카드 (*), odd, even, all',
            'oid(s)' => '가져올 하나 이상의 SNMP OID. MIB::oid 또는 숫자형 OID 형식이어야 합니다.',
        ],
        'failed' => 'SNMP 명령이 실패했습니다!',
        'numeric' => '숫자형',
        'oid' => 'OID',
        'options' => [
            'output' => '출력 형식을 지정합니다: :formats',
            'numeric' => '숫자형 OID',
            'depth' => 'SNMP 테이블을 그룹화할 깊이. 일반적으로 테이블 인덱스의 항목 수와 같습니다.',
        ],
        'not_found' => '장비를 찾을 수 없습니다.',
        'textual' => '텍스트형',
        'value' => '값',
    ],
    'translation:generate' => [
        'description' => '웹 프론트엔드에서 사용할 업데이트된 JSON 언어 파일을 생성합니다.',
    ],
    'user:add' => [
        'description' => '로컬 사용자를 추가합니다. 인증이 mysql로 설정된 경우에만 이 사용자로 로그인할 수 있습니다.',
        'arguments' => [
            'username' => '사용자가 로그인할 때 사용할 사용자 이름',
        ],
        'options' => [
            'descr' => '사용자 설명',
            'email' => '사용자의 이메일 주소',
            'password' => '사용자 비밀번호. 지정하지 않으면 입력을 요청합니다.',
            'full-name' => '사용자의 전체 이름',
            'role' => '사용자에게 원하는 역할을 설정합니다: :roles',
        ],
        'form' => [
            'username' => '사용자 이름',
            'password' => '비밀번호',
            'roles' => '사용자 역할 선택',
            'email' => '이메일 (선택사항)',
            'full-name' => '전체 이름 (선택사항)',
            'descr' => '설명 (선택사항)',
        ],
        'success' => '사용자가 성공적으로 추가되었습니다: :username',
        'wrong-auth' => '경고! MySQL 인증을 사용하지 않으므로 이 사용자로 로그인할 수 없습니다.',
    ],
];
