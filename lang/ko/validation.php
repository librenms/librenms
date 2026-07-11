<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    // Librenms specific
    'alpha_space' => ':attribute 필드는 영문자, 숫자, 밑줄, 공백만 포함할 수 있습니다.',
    'ip_or_hostname' => ':attribute은(는) 유효한 IP 주소/네트워크 또는 호스트명이어야 합니다.',
    'is_regex' => ':attribute은(는) 유효한 정규 표현식이 아닙니다',
    'array_keys_not_empty' => ':attribute에 빈 배열 키가 포함되어 있습니다.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'results' => [
        'autofix' => '자동 수정 시도',
        'fix' => '수정',
        'fixed' => '수정이 완료되었습니다. 새로 고침하여 검증을 다시 실행하십시오.',
        'fetch_failed' => '검증 결과를 가져오는 데 실패했습니다',
        'backend_failed' => '백엔드에서 데이터를 불러오지 못했습니다. 콘솔에서 ./validate.php를 실행하여 확인하십시오.',
        'invalid_fixer' => '유효하지 않은 수정기',
        'show_all' => '모두 표시',
        'show_less' => '간략히 표시',
        'validate' => '검증',
        'validating' => '검증 중',
        'skipped' => '건너뜀',
        'run' => '실행',
    ],
    'validations' => [
        // Display names for validation groups
        'groups' => [
            'configuration' => '설정',
            'database' => '데이터베이스',
            'dependencies' => '의존성',
            'disk' => '디스크',
            'distributedpoller' => '분산 폴러',
            'mail' => '메일',
            'php' => 'PHP',
            'poller' => '폴러',
            'programs' => '프로그램',
            'python' => 'Python',
            'rrd' => 'RRD',
            'rrdcheck' => 'RRD 점검',
            'scheduler' => '스케줄러',
            'system' => '시스템',
            'updates' => '업데이트',
            'user' => '사용자',
            'webserver' => '웹 서버',
        ],
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'rrdtool 버전 :installed_version은 너무 오래되었습니다. LibreNMS는 최소 1.5.5 버전이 필요합니다',
                'fail_config' => '지정한 rrdtool_version :config_version이 너무 오래되었습니다. LibreNMS는 최소 1.5.5 버전이 필요합니다',
                'fix' => 'config.php에서 $config[\'rrdtool_version\'] = \':version\';을 주석 처리하거나 삭제하십시오',
                'ok' => 'rrdtool 버전이 적합합니다',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => ':socket이 존재하지 않는 것 같습니다. rrdcached 연결 테스트에 실패했습니다',
                'fail_port' => ':port 포트의 rrdcached 서버 :server에 연결할 수 없습니다',
                'ok' => 'rrdcached에 연결되었습니다',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'RRD 디렉터리가 root 소유입니다. 비-root 사용자로 변경하는 것을 권장합니다',
                'fail_mode' => 'RRD 디렉터리가 0775로 설정되어 있지 않습니다',
                'ok' => 'rrd_dir에 쓸 수 있습니다',
            ],
            'CheckRrdStep' => [
                'fail' => '일부 RRD 파일의 스텝이 올바르지 않습니다. :bad/:total',
                'fail_bad_files' => 'RRD 파일 읽기 오류. :bad/:total',
                'list_bad_step_title' => '잘못된 스텝을 가진 RRD 파일',
                'list_bad_files_title' => 'rrdinfo 실행 오류 파일',
                'list_bad_step_item' => ':file: 스텝 :step, 올바른 값은 :target',
                'ok' => '전체 :total개의 RRD 파일이 올바른 스텝을 가지고 있습니다.',
                'timeout' => 'RRD 파일 점검 시간이 초과되었습니다. :command를 실행하여 모든 RRD 파일을 점검하고 수정할 수 있습니다.',
            ],
        ],
        'database' => [
            'CheckDatabaseConnected' => [
                'fail' => '데이터베이스에 연결할 수 없습니다',
                'fail_connect' => '데이터베이스에 연결할 수 없습니다. 데이터베이스 서버가 실행 중인지, 연결 정보가 올바른지 확인하십시오. 환경 변수 또는 :env_file에서 DB_HOST, DB_PORT, DB_NAME을 확인하십시오',
                'fail_access' => '데이터베이스에 연결되었지만 사용자에게 데이터베이스 접근 권한이 없습니다. SQL 쿼리를 실행하여 권한을 부여하십시오 (데이터베이스가 원격인 경우 localhost를 로컬 호스트명으로 변경하십시오)',
                'fail_auth' => '데이터베이스 자격 증명이 올바르지 않습니다. 환경 변수 또는 :env_file에서 DB_USERNAME과 DB_PASSWORD를 다시 확인하십시오',
                'ok' => '데이터베이스가 연결되었습니다',
            ],
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'MySQL 설정에서 lower_case_table_names가 1 또는 true로 설정되어 있습니다.',
                'fix' => 'MySQL 설정 파일의 [mysqld] 섹션에 lower_case_table_names=0을 설정하십시오.',
                'ok' => 'lower_case_table_names가 활성화되어 있습니다',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => ':date 기준으로 :server 버전 :min이 지원되는 최소 버전입니다.',
                'fix' => ':server를 지원되는 버전으로 업데이트하십시오. :suggested 버전을 권장합니다.',
                'ok' => 'SQL 서버가 최소 요구 사항을 충족합니다',
            ],
            'CheckMysqlEngine' => [
                'fail' => '일부 테이블이 권장 InnoDB 엔진을 사용하지 않습니다. 문제가 발생할 수 있습니다.',
                'tables' => '테이블',
                'ok' => 'MySQL 엔진이 최적입니다',
            ],
            'CheckSqlServerTime' => [
                'fail' => "이 서버와 MySQL 데이터베이스 간의 시간이 다릅니다\n MySQL 시간: :mysql_time\n PHP 시간: :php_time",
                'ok' => 'MySQL과 PHP 시간이 일치합니다',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => '데이터베이스가 최신 버전이 아닙니다!',
                'fail_legacy_outdated' => '데이터베이스 스키마(:current)가 최신(:latest)보다 오래되었습니다.',
                'fix_legacy_outdated' => './daily.sh를 수동으로 실행하고 오류를 확인하십시오.',
                'warn_extra_migrations' => '데이터베이스 스키마에 추가 마이그레이션(:migrations)이 있습니다. 일일 릴리스에서 안정 릴리스로 전환한 경우, 데이터베이스가 릴리스 사이에 있으며 다음 릴리스에서 해결됩니다.',
                'warn_legacy_newer' => '데이터베이스 스키마(:current)가 예상(:latest)보다 최신입니다. 일일 릴리스에서 안정 릴리스로 전환한 경우, 데이터베이스가 릴리스 사이에 있으며 다음 릴리스에서 해결됩니다.',
                'ok' => '데이터베이스 스키마가 최신입니다',
            ],
            'CheckSchemaCollation' => [
                'ok' => '데이터베이스 및 컬럼 콜레이션이 올바릅니다',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => '분산 폴링 설정이 전역으로 활성화되어 있습니다',
                'not_enabled' => 'distributed_poller가 활성화되지 않았습니다',
                'not_enabled_globally' => 'distributed_poller가 전역으로 활성화되지 않았습니다',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'distributed_poller_memcached_host가 설정되지 않았습니다',
                'not_configured_port' => 'distributed_poller_memcached_port가 설정되지 않았습니다',
                'could_not_connect' => 'memcached 서버에 연결할 수 없습니다',
                'ok' => 'memcached 연결이 정상입니다',
            ],
            'CheckRrdcached' => [
                'fail' => 'rrdcached가 활성화되지 않았습니다',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => '폴러가 실행 중이 아닙니다. 최근 :interval초 이내에 폴링이 실행되지 않았습니다.',
                'both_fail' => '디스패처 서비스와 Python 래퍼가 최근에 모두 활성 상태였습니다. 이중 폴링이 발생할 수 있습니다',
                'ok' => '활성 폴러가 있습니다',
            ],
            'CheckDispatcherService' => [
                'fail' => '활성 디스패처 노드가 없습니다',
                'ok' => '디스패처 서비스가 활성화되어 있습니다',
                'nodes_down' => '일부 디스패처 노드가 최근에 체크인하지 않았습니다',
                'not_detected' => '디스패처 서비스가 감지되지 않았습니다',
                'warn' => '디스패처 서비스가 사용된 적 있지만 최근에는 사용되지 않았습니다',
            ],
            'CheckLocking' => [
                'fail' => '캐싱 서버 문제: :message',
                'ok' => '잠금이 정상적으로 작동합니다',
            ],
            'CheckPythonWrapper' => [
                'fail' => '활성 Python 래퍼 폴러가 없습니다',
                'no_pollers' => 'Python 래퍼 폴러가 없습니다',
                'cron_unread' => 'cron 파일을 읽을 수 없습니다',
                'ok' => 'Python 폴러 래퍼가 폴링 중입니다',
                'nodes_down' => '일부 폴러 노드가 최근에 체크인하지 않았습니다',
                'not_detected' => 'Python 래퍼 cron 항목이 없습니다',
            ],
            'CheckRedis' => [
                'bad_driver' => '잠금에 :driver를 사용 중입니다. CACHE_STORE=redis로 설정하십시오',
                'ok' => 'Redis가 정상적으로 작동합니다',
                'unavailable' => 'Redis를 사용할 수 없습니다',
            ],
        ],
    ],
];
