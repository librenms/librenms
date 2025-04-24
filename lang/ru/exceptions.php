<?php

return [
    'database_connect' => [
        'title' => 'Ошибка подключения к базе данных',
    ],
    'database_inconsistent' => [
        'title' => 'Несоответствие базы данных',
        'header' => 'Обнаружены несоответствия базы данных во время ошибки базы данных, пожалуйста, исправьте, чтобы продолжить.',
    ],
    'dusk_unsafe' => [
        'title' => 'Запуск Dusk в производственной среде небезопасен',
        'message' => 'Запустите ":command", чтобы удалить Dusk, или, если вы разработчик, установите соответствующую APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'Ошибка: Не удалось записать в файл',
        'message' => 'Не удалось записать в файл (:file). Пожалуйста, проверьте права доступа и SELinux/AppArmor, если это применимо.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Устройство :hostname уже существует',
        'ip_exists' => 'Невозможно добавить :hostname, уже есть устройство :existing с этим IP :ip',
        'sysname_exists' => 'Уже есть устройство :hostname из-за дублирующегося sysName: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'Не удалось пропинговать :hostname (:ip)',
        'unsnmpable' => 'Не удалось подключиться к :hostname, пожалуйста, проверьте детали snmp и доступность snmp',
        'unresolvable' => 'Имя хоста не разрешилось в IP',
        'no_reply_community' => 'SNMP :version: Нет ответа с сообществом :credentials',
        'no_reply_credentials' => 'SNMP :version: Нет ответа с учетными данными :credentials',
    ],
    'ldap_missing' => [
        'title' => 'Отсутствует поддержка PHP LDAP',
        'message' => 'PHP не поддерживает LDAP, пожалуйста, установите или активируйте расширение PHP LDAP',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Превышено максимальное время выполнения :seconds секунда|Превышено максимальное время выполнения :seconds секунд',
        'message' => 'Загрузка страницы превысила ваше максимальное время выполнения, настроенное в PHP. Увеличьте max_execution_time в вашем php.ini или улучшите аппаратное обеспечение сервера',
    ],
    'unserializable_route_cache' => [
        'title' => 'Ошибка, вызванная несовпадением версий PHP',
        'message' => 'Версия PHP, которую использует ваш веб-сервер (:web_version), не совпадает с версией CLI (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'Неподдерживаемая версия SNMP ":snmpver", должна быть v1, v2c или v3',
    ],
];
