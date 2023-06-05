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

    'accepted' => 'Поле :attribute должно быть принято.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute должно быть действительным URL.',
    'after' => 'Поле :attribute должно быть датой после :date.',
    'after_or_equal' => 'Поле :attribute должно быть датой после или равной :date.',
    'alpha' => 'Поле :attribute должно содержать только буквы.',
    'alpha_dash' => 'Поле :attribute должно содержать только буквы, цифры, дефисы и подчеркивания.',
    'alpha_num' => 'Поле :attribute должно содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'ascii' => 'Поле :attribute должно содержать только однобайтовые буквенно-цифровые символы и символы.',
    'before' => 'Поле :attribute должно быть датой перед :date.',
    'before_or_equal' => 'Поле :attribute должно быть датой перед или равной :date.',
    'between' => [
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
        'file' => 'Поле :attribute должно быть между :min и :max килобайтами.',
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'string' => 'Поле :attribute должно быть между :min и :max символами.',
    ],
    'boolean' => 'Поле :attribute должно быть true или false.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute должно быть действительной датой.',
    'date_equals' => 'Поле :attribute должно быть датой, равной :date.',
    'date_format' => 'Поле :attribute должно соответствовать формату :format.',
    'decimal' => 'Поле :attribute должно иметь :decimal десятичных знаков.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поле :attribute и :other должны быть разными.',
    'digits' => 'Поле :attribute должно быть :digits цифрами.',
    'digits_between' => 'Поле :attribute должно быть между :min и :max цифрами.',
    'dimensions' => 'Поле :attribute имеет недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute имеет повторяющееся значение.',
    'doesnt_end_with' => 'Поле :attribute не должно заканчиваться на одно из следующих значений: :values.',
    'doesnt_start_with' => 'Поле :attribute не должно начинаться с одного из следующих значений: :values.',
    'email' => 'Поле :attribute должно быть действительным адресом электронной почты.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values.',
    'enum' => 'Выбранное значение :attribute недопустимо.',
    'exists' => 'Выбранное значение :attribute недопустимо.',
    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле :attribute должно иметь значение.',
    'gt' => [
        'array' => 'Поле :attribute должно содержать более :value элементов.',
        'file' => 'Поле :attribute должно быть больше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'string' => 'Поле :attribute должно быть больше :value символов.',
    ],
    'gte' => [
        'array' => 'Поле :attribute должно содержать :value элементов или более.',
        'file' => 'Поле :attribute должно быть больше или равно :value килобайтам.',
        'numeric' => 'Поле :attribute должно быть больше или равно :value.',
        'string' => 'Поле :attribute должно быть больше или равно :value символам.',
    ],
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение :attribute недопустимо.',
    'in_array' => 'Поле :attribute должно существовать в :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно быть действительным IP-адресом.',
    'ipv4' => 'Поле :attribute должно быть действительным IPv4-адресом.',
    'ipv6' => 'Поле :attribute должно быть действительным IPv6-адресом.',
    'json' => 'Поле :attribute должно быть действительной JSON-строкой.',
    'lowercase' => 'Поле :attribute должно быть в нижнем регистре.',
    'lt' => [
        'array' => 'Поле :attribute должно содержать меньше :value элементов.',
        'file' => 'Поле :attribute должно быть меньше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'string' => 'Поле :attribute должно быть меньше :value символов.',
    ],
    'lte' => [
        'array' => 'Поле :attribute не должно содержать более :value элементов.',
        'file' => 'Поле :attribute должно быть меньше или равно :value килобайтам.',
        'numeric' => 'Поле :attribute должно быть меньше или равно :value.',
        'string' => 'Поле :attribute должно быть меньше или равно :value символам.',
    ],
    'mac_address' => 'Поле :attribute должно быть действительным MAC-адресом.',
    'max' => [
        'array' => 'Поле :attribute не должно содержать более :max элементов.',
        'file' => 'Поле :attribute не должно быть больше :max килобайт.',
        'numeric' => 'Поле :attribute не должно быть больше :max.',
        'string' => 'Поле :attribute не должно быть больше :max символов.',
    ],
    'max_digits' => 'Поле :attribute не должно содержать более :max цифр.',
    'mimes' => 'Поле :attribute должно быть файлом одного из следующих типов: :values.',
    'mimetypes' => 'Поле :attribute должно быть файлом одного из следующих типов: :values.',
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Поле :attribute должно быть не менее :min килобайт.',
        'numeric' => 'Поле :attribute должно быть не менее :min.',
        'string' => 'Поле :attribute должно быть не менее :min символов.',
    ],
    'min_digits' => 'Поле :attribute должно содержать не менее :min цифр.',
    'missing' => 'Поле :attribute должно отсутствовать.',
    'missing_if' => 'Поле :attribute должно отсутствовать, когда :other равно :value.',
    'missing_unless' => 'Поле :attribute должно отсутствовать, если :other не равно :value.',
    'missing_with' => 'Поле :attribute должно отсутствовать, когда присутствует :values.',
    'missing_with_all' => 'Поле :attribute должно отсутствовать, когда присутствуют :values.',
    'multiple_of' => 'Поле :attribute должно быть кратным :value.',
    'not_in' => 'Выбранное значение :attribute недопустимо.',
    'not_regex' => 'Формат поля :attribute недопустим.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать хотя бы одну заглавную букву и одну строчную букву.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одну цифру.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Указанное значение :attribute появилось в утечке данных. Пожалуйста, выберите другое значение для :attribute.',
    ],
    'present' => 'Поле :attribute должно присутствовать.',
    'prohibited' => 'Поле :attribute запрещено.',
    'prohibited_if' => 'Поле :attribute запрещено, когда :other равно :value.',
    'prohibited_unless' => 'Поле :attribute запрещено, если :other не принадлежит к значению :values.',
    'prohibits' => 'Поле :attribute запрещает присутствие :other.',
    'regex' => 'Формат поля :attribute недопустим.',
    'required' => 'Поле :attribute является обязательным.',
    'required_array_keys' => 'Поле :attribute должно содержать записи для: :values.',
    'required_if' => 'Поле :attribute является обязательным, когда :other равно :value.',
    'required_if_accepted' => 'Поле :attribute является обязательным, когда :other принимается.',
    'required_unless' => 'Поле :attribute является обязательным, если :other не принадлежит к значению :values.',
    'required_with' => 'Поле :attribute является обязательным, когда присутствует :values.',
    'required_with_all' => 'Поле :attribute является обязательным, когда присутствуют :values.',
    'required_without' => 'Поле :attribute является обязательным, когда отсутствует :values.',
    'required_without_all' => 'Поле :attribute является обязательным, когда отсутствуют все :values.',
    'same' => 'Поле :attribute должно совпадать с :other.',
    'size' => [
        'array' => 'Поле :attribute должно содержать :size элементов.',
        'file' => 'Поле :attribute должно быть :size килобайт.',
        'numeric' => 'Поле :attribute должно быть равным :size.',
        'string' => 'Поле :attribute должно содержать :size символов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с одного из следующих значений: :values.',
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно быть допустимым часовым поясом.',
    'unique' => 'Поле :attribute уже занято.',
    'uploaded' => 'Не удалось загрузить файл :attribute.',
    'uppercase' => 'Поле :attribute должно быть в верхнем регистре.',
    'url' => 'Поле :attribute должно быть действительным URL.',
    'ulid' => 'Поле :attribute должно быть действительным ULID.',
    'uuid' => 'Поле :attribute должно быть действительным UUID.',

    // Специфичные для Librenms
    'alpha_space' => 'Поле :attribute может содержать только буквы, цифры, подчеркивания и пробелы.',
    'ip_or_hostname' => 'Поле :attribute должно быть действительным IP-адресом/сетью или именем хоста.',
    'is_regex' => 'Поле :attribute не является действительным регулярным выражением.',
    'keys_in' => 'Поле :attribute содержит недопустимые ключи: :extra. Допустимые ключи: :values.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Здесь можно указать пользовательские сообщения об ошибках валидации для атрибутов,
    | используя соглашение "attribute.rule" для именования строк. Это позволяет быстро
    | указать конкретное пользовательское сообщение для заданного правила атрибута.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Следующие строки языка используются для замены плейсхолдера атрибута
    | на более понятное для пользователя значение, например, "E-Mail Address"
    | вместо "email". Это помогает сделать сообщение более выразительным.
    |
    */

    'attributes' => [],

    'results' => [
        'autofix' => 'Попытка автоматического исправления',
        'fix' => 'Исправить',
        'fixed' => 'Исправление выполнено, обновите страницу для повторного запуска проверки.',
        'fetch_failed' => 'Не удалось получить результаты проверки.',
        'backend_failed' => 'Не удалось загрузить данные с сервера. Проверьте консоль на наличие ошибок.',
        'invalid_fixer' => 'Недопустимый исправитель',
        'show_all' => 'Показать все',
        'show_less' => 'Показать меньше',
        'validate' => 'Проверить',
        'validating' => 'Выполняется проверка',
    ],

    'validations' => [
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'Указанная вами версия rrdtool новее, чем установленная. Конфигурация: :config_version Установленная версия: :installed_version',
                'fix' => 'Закомментируйте или удалите строку $config[\'rrdtool_version\'] = \':version\'; из файла config.php',
                'ok' => 'Версия rrdtool соответствует требованиям',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => 'Файл :socket не существует, не удалось подключиться к rrdcached',
                'fail_port' => 'Не удалось подключиться к серверу rrdcached на порту :port',
                'ok' => 'Подключение к rrdcached выполнено успешно',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'Каталог RRD принадлежит пользователю root, рекомендуется изменить на другого пользователя, не являющегося root',
                'fail_mode' => 'Режим доступа к каталогу RRD не установлен на 0775',
                'ok' => 'rrd_dir доступен для записи',
            ],
        ],
        'database' => [
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'В настройках MySQL установлен параметр lower_case_table_names со значением 1 или true.',
                'fix' => 'Установите значение lower_case_table_names=0 в файле конфигурации MySQL в разделе [mysqld].',
                'ok' => 'Включена поддержка lower_case_table_names',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => 'Версия :server :min - это минимальная поддерживаемая версия, начиная с :date.',
                'fix' => 'Обновите :server до поддерживаемой версии. Предлагается использовать :suggested.',
                'ok' => 'Версия SQL Server соответствует минимальным требованиям',
            ],
            'CheckMysqlEngine' => [
                'fail' => 'Некоторые таблицы не используют рекомендуемый движок InnoDB, это может вызвать проблемы.',
                'tables' => 'Таблицы',
                'ok' => 'Используется оптимальный движок MySQL',
            ],
            'CheckSqlServerTime' => [
                'fail' => 'Время между этим сервером и базой данных MySQL расходится\n Время MySQL: :mysql_time\n Время PHP: :php_time',
                'ok' => 'Время MySQL и PHP совпадают',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => 'Версия вашей базы данных устарела!',
                'fail_legacy_outdated' => 'Схема вашей базы данных (:current) устарела по сравнению с последней (:latest).',
                'fix_legacy_outdated' => 'Запустите вручную команду ./daily.sh и проверьте наличие ошибок.',
                'warn_extra_migrations' => 'В схеме вашей базы данных есть дополнительные миграции (:migrations). Если вы только что переключились на стабильную версию с ежедневной, то ваша база данных находится между версиями и это будет исправлено в следующем выпуске.',
                'warn_legacy_newer' => 'Схема вашей базы данных (:current) новее ожидаемой (:latest). Если вы только что переключились на стабильную версию с ежедневной, то ваша база данных находится между версиями и это будет исправлено в следующем выпуске.',
                'ok' => 'Схема базы данных актуальна',
            ],
            'CheckSchemaCollation' => [
                'ok' => 'Кодировка базы данных и столбцов указана корректно',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => 'Настройка распределенного опроса включена глобально',
                'not_enabled' => 'Вы не включили распределенный опрос',
                'not_enabled_globally' => 'Вы не включили распределенный опрос глобально',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'Вы не настроили distributed_poller_memcached_host',
                'not_configured_port' => 'Вы не настроили distributed_poller_memcached_port',
                'could_not_connect' => 'Не удалось подключиться к серверу memcached',
                'ok' => 'Подключение к memcached выполнено успешно',
            ],
            'CheckRrdcached' => [
                'fail' => 'Вы не включили rrdcached',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => 'Не обнаружено активных методов опроса',
                'both_fail' => 'Недавно были активны и Dispatcher Service, и Python Wrapper. Это может привести к двойному опросу.',
                'ok' => 'Обнаружены активные опрашиватели',
            ],
            'CheckDispatcherService' => [
                'fail' => 'Не обнаружено активных узлов диспетчера',
                'ok' => 'Диспетчерская служба включена',
                'nodes_down' => 'Некоторые узлы диспетчера не подключались в последнее время',
                'not_detected' => 'Диспетчерская служба не обнаружена',
                'warn' => 'Диспетчерская служба использовалась, но не недавно',
            ],
            'CheckLocking' => [
                'fail' => 'Проблема с сервером блокировки: :message',
                'ok' => 'Блокировки работают корректно',
            ],
            'CheckPythonWrapper' => [
                'fail' => 'Не обнаружены активные опрашиватели Python Wrapper',
                'no_pollers' => 'Не обнаружено опрашивателей Python Wrapper',
                'cron_unread' => 'Не удалось прочитать файлы cron',
                'ok' => 'Python опрашиватель работает',
                'nodes_down' => 'Некоторые узлы опрашивателей не подключались в последнее время',
                'not_detected' => 'Конфигурационная запись для Python Wrapper не найдена',
            ],
            'CheckRedis' => [
                'bad_driver' => 'Используется драйвер :driver для блокировки, рекомендуется установить CACHE_DRIVER=redis',
                'ok' => 'Redis работает корректно',
                'unavailable' => 'Redis недоступен',
            ],
        ],
    ],

];
