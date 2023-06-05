<?php

return [
    'database_connect' => [
        'title' => 'Ошибка подключения к базе данных',
    ],
    'database_inconsistent' => [
        'title' => 'Несогласованность базы данных',
        'header' => 'Во время ошибки базы данных обнаружены несоответствия в базе данных. Пожалуйста, исправьте их, чтобы продолжить.',
    ],
    'dusk_unsafe' => [
        'title' => 'Запуск Dusk в производственной среде небезопасен',
        'message' => 'Выполните команду ":command", чтобы удалить Dusk, или, если вы разработчик, установите соответствующую переменную APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'Ошибка: не удалось записать в файл',
        'message' => 'Не удалось записать в файл (:file). Пожалуйста, проверьте права доступа и SELinux/AppArmor, если применимо.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Устройство :hostname уже существует',
        'ip_exists' => 'Невозможно добавить :hostname, так как устройство :existing уже имеет этот IP-адрес :ip',
        'sysname_exists' => 'Уже есть устройство :hostname из-за дублирующегося sysName: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'Не удалось выполнить ping для :hostname (:ip)',
        'unsnmpable' => 'Не удалось подключиться к :hostname. Пожалуйста, проверьте данные SNMP и доступность SNMP.',
        'unresolvable' => 'Имя хоста не удалось преобразовать в IP-адрес',
        'no_reply_community' => 'SNMP :version: Нет ответа с community :credentials',
        'no_reply_credentials' => 'SNMP :version: Нет ответа с учетными данными :credentials',
    ],
    'ldap_missing' => [
        'title' => 'Отсутствует поддержка PHP LDAP',
        'message' => 'PHP не поддерживает LDAP. Пожалуйста, установите или включите расширение PHP LDAP',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Превышено максимальное время выполнения :seconds секунду|Превышено максимальное время выполнения :seconds секунды|Превышено максимальное время выполнения :seconds секунд',
        'message' => 'Время загрузки страницы превысило максимальное время выполнения, заданное в PHP. Увеличьте значение max_execution_time в php.ini или улучшите аппаратное обеспечение сервера.',
    ],
    'unserializable_route_cache' => [
        'title' => 'Ошибка, вызванная несовпадением версий PHP',
        'message' => 'Версия PHP, которую использует ваш веб-сервер (:web_version), не совпадает с версией CLI (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'Неподдерживаемая версия SNMP ":snmpver". Должна быть v1, v2c или v3',
    ],
];
