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
    'alpha_space' => ':attribute შეიძლება, მხოლოდ, ლათინურ ასოებს, ციფრებს, ქვედატირეებს და ჰარეებს შეიცავდეს.',
    'ip_or_hostname' => ':attribute სწორი IP მისამართი/ქსელი, ან ჰოსტის სახელი უნდა იყოს.',
    'is_regex' => ':attribute სწორი რეგულარული გამოსახულება არაა',
    'array_keys_not_empty' => ':attribute ცარიელ მასივის გასაღებებს შეიცავს.',

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
        'autofix' => 'ავტომატური გასწორების მცდელობა',
        'fix' => 'გასწორება',
        'fixed' => 'გასწორება დასრულდა. განაახლეთ გვერდი ვალიდაციების თავიდან გასაშვებად.',
        'fetch_failed' => 'ვალიდაციის შედეგების გამოთხოვა ჩავარდა',
        'backend_failed' => 'უკანაბოლოდან მონაცემების ჩატვირთვა ჩავარდა. შეცდომებისთვის იხილეთ კონსოლი.',
        'invalid_fixer' => 'არასწორი გამსწორებელი',
        'show_all' => 'ყველას ჩვენება',
        'show_less' => 'ნაკლების ჩვენება',
        'validate' => 'ვალიდაცია',
        'validating' => 'მიმდინარეობს ვალიდაცია',
    ],
    'validations' => [
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'rrdtool-ის ვერსია, რომელიც მიუთითეთ, უფრო ახალია იმაზე, რაც დაყენებული გაქვთ. კონფიგურაცია: :config_version დაყენებული :installed_version',
                'fix' => 'ან დააკომენტარეთ, ან წაშალეთ $config[\'rrdtool_version\'] = \':version\'; თქვენი ფაილიდან config.php',
                'ok' => 'rrdtool-ის ვერსია კარგია',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => 'როგორც ჩანს :socket არ არსებობს. rrdcached-სთან დაკავშირების შემოწმება ჩავარდა',
                'fail_port' => 'ვერ დავუკავშირდი rrdcached-ის სერვერს პორტზე :port',
                'ok' => 'rrdcached დაკავშირებულია',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'თქვენი RRD-ის საქაღალდის მფლობელია root. სჯობს, არა-root მომხმარებელზე შეცვლაზე იფიქროთ',
                'fail_mode' => 'თქვენი RRD-ის საქაღალდის წვდომები არაა 0775',
                'ok' => 'rrd_dir ჩაწერადია',
            ],
        ],
        'database' => [
            'CheckDatabaseConnected' => [
                'fail' => 'მონაცემთა ბაზასთან დაკავშირების შეცდომა',
                'fail_connect' => 'მონაცემთა ბაზასთან დაკავშირების შეცდომა. დაადასტურეთ, რომ მონაცემთა ბაზის სერვერი გაშვებულია და დაკავშირების ინფორმაცია სწორია.  შეამოწმეთ DB_HOST, DB_PORT და DB_NAME გარემოში, ან :env_file-ში',
                'fail_access' => 'მონაცემთა ბაზა დაკავშირებულია, მაგრამ მომხმარებელს მონაცემთა ბაზასთან წვდომა არ აქვს. გაუშვიტ SQL ბრძანება წვდომების მისანიჭნებლად (შეცვალეთ localhost ლოკალურ ჰოსტის სახელზე, თუ მონაცემთა ბაზა ამავე მანქანაზე არაა)',
                'fail_auth' => 'მონაცემთა ბაზის ავტორიზაციის დეტალები არასწორია. გადაამოწმეთ DB_USERNAME და DB_PASSWORD გარემოში, ან თქვენს :env_file ფაილში',
                'ok' => 'მონაცემთა ბაზა დაკავშირებულია',
            ],
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'თქვენ გაქვთ lower_case_table_names დაყენებული მნიშვნელობაზე 1 ან true mysql-ის კონფიგურაციაში.',
                'fix' => 'დააყენთ lower_case_table_names=0 თქვენი mysql-ის კონფიგურაციის ფაილის [mysqld] სექციაში.',
                'ok' => 'lower_case_table_names ჩართულია',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => ':server ვერსია :min :date-ზე მინიმალური მხარდაჭერილი ვერსიაა.',
                'fix' => 'განაახლეთ :server მხარდაჭერილ ვერსიამდ. გირჩევთ: :suggested.',
                'ok' => 'SQL სერვერი აკმაყოფილებს მინიმალურ მოთხოვნებს',
            ],
            'CheckMysqlEngine' => [
                'fail' => 'ზოგიერთი ცხრილი რეკომენდებულ InnoDB ძრავას არ იყენებს. ამან, შეიძლება, პრობლემები გამოიწვიოს.',
                'tables' => 'ცხრილები',
                'ok' => 'MySQL-ს ძრავი ოპტიმალურია',
            ],
            'CheckSqlServerTime' => [
                'fail' => "დრო ამ სერვერსა და mysql-ს შორის განსხვავდება\n Mysql-ის დროა :mysql_time\n PHP-ის დროა :php_time",
                'ok' => 'MySQL-ის და PHP-ის დრო ემთხვევა',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => 'თქვენი მონაცემთა ბაზა მოძველებულია!!',
                'fail_legacy_outdated' => 'თქვენი მონაცემთა ბაზის სქემა (:current) უფრო ძველია, ვიდრე უახლესი (:latest).',
                'fix_legacy_outdated' => 'ხელით გაუშვით ./daily.sh და ნახეთ, რა შეცდომებს ყრის.',
                'warn_extra_migrations' => 'თქვენი მონაცემთა ბაზის სქემას აქვს დამატებითი მიგრაციები (:migrations). თუ თქვენ ახლა გადაერთეთ სტაბილურ ვერსიაზე დღიური გამოცემებიდანმ თქვენი მონაცემთა ბაზა რელიზებს შორისაა და ეს შემდეგ რელიზში გასწორდება.',
                'warn_legacy_newer' => 'თქვენი მონაცემთა ბაზის სქემა (:current) უფრო ახალია, ვიდრე მოსალოდნელი იყო (:latest). თუ თქვენ ახლა გადაერთეთ სტაბილურ ვერსიაზე ყოველდღიური რელიზებიდან, თქვენი მონაცემთა ბაზა რელიზებს შორისაა და შემდეგ რელიზში გასწორდება.',
                'ok' => 'მონაცემთა ბაზის სქემა მიმდინარეა',
            ],
            'CheckSchemaCollation' => [
                'ok' => 'მონაცემთა ბაზა და სვეტის კოლაციები სწორია',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => 'განაწილებული მოთხოვნის პარამეტრი ჩართულია გლობალურად',
                'not_enabled' => 'განაწილებული მომთხოვნი არ ჩაგირთავთ',
                'not_enabled_globally' => 'განაწილებული მომთხოვნი გლობალურად არ ჩაგირთავთ',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'distributed_poller_memcached_host მორგებული არაა',
                'not_configured_port' => 'distributed_poller_memcached_port მორგებული არაა',
                'could_not_connect' => 'memcached-ის სერვერთან მიერთების შეცდომა',
                'ok' => 'კავშირი memcached-თან მუშაობს',
            ],
            'CheckRrdcached' => [
                'fail' => 'rrdcached არ ჩაგირთავთ',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => 'მომთხოვნი გაშვებული არაა. ბოლო: interval წამში მომთხოვნი არ გაშვებულა.',
                'both_fail' => 'ახლახან აქტიური იყო დისპეჩერის სერვისიც და Python-ის გადამყვანიც. ამან, შეიძლება, ორმაგი მოთხოვნა გამოიწვიოს',
                'ok' => 'აღმოჩენილია აქტიური მომთხოვნები',
            ],
            'CheckDispatcherService' => [
                'fail' => 'აქტიური დისპეჩერი კვანძების გარეშე',
                'ok' => 'დისპეჩერის სერვისი ჩართულია',
                'nodes_down' => 'ზოგიერთი დისპეჩერის სერვისი კარგა ხანია, ხმას არ იღებს',
                'not_detected' => 'დისპეჩერის სერვისი აღმოჩენილი არაა',
                'warn' => 'დისპეჩერის სერვისი გამოიყენება, მაგრამ არა ახლახან',
            ],
            'CheckLocking' => [
                'fail' => 'დაბლოკვის სერვერის პრობლემა: :message',
                'ok' => 'დაბლოკვა მუშაობს',
            ],
            'CheckPythonWrapper' => [
                'fail' => 'აქტიური python-ის გადამყვანი მოთხოვნები აღმოჩენილი არაა',
                'no_pollers' => 'python-ის გადამყვანი მოთხოვნები აღმოჩენილი არაა',
                'cron_unread' => 'cron-ის ფაილების წაკითხვის შეცდომა',
                'ok' => 'Python-ის გადამყვანი მომთხოვნი მუშაობს',
                'nodes_down' => 'ზოგიერთი მომთხოვნი კვანძი ინფორმაციას არ გვაწვდის',
                'not_detected' => 'Python-ის გადამყვანი cron-ის ჩანაწერი არ არსებობს',
            ],
            'CheckRedis' => [
                'bad_driver' => 'იყენებთ დრაივერს :driver დასაბლოკად. გამოიყენეთ CACHE_STORE=redis',
                'ok' => 'Redis მუშაობს',
                'unavailable' => 'Redis ხელმისაწვდომი არაა',
            ],
        ],
    ],
];
