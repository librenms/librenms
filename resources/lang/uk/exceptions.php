<?php

return [
    'database_connect' => [
        'title' => 'Помилка з\'єднання з базою даних',
    ],
    'database_inconsistent' => [
        'title' => 'Неконсистентна база даних',
        'header' => 'Під час обробки помилки бази даних було знайдено розходження у базі даних, виправте для продовження роботи.',
    ],
    'dusk_unsafe' => [
        'title' => 'Небезпечно запускати Dusk у робочому середовищі',
        'message' => 'Запустіть ":command" для видалення Dusk або зазначте відповідний APP_ENV якщо ви розробник',
    ],
    'file_write_failed' => [
        'title' => 'Помилка: Неможливий запис до файла',
        'message' => 'Не вдалося записати до файла (:file).  Перевірте дозволи та SELinux/AppArmor.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Пристрій :hostname вже існує',
        'ip_exists' => 'Неможливо додати :hostname, вже існує пристрій :existing з адресою :ip',
        'sysname_exists' => 'Вже є пристрій :hostname з однаковим sysName: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'Не вдалося отримати відповідь на ping від :hostname (:ip)',
        'unsnmpable' => 'Не вдалося з\'єднатися з :hostname, перевірте налаштування та доступність по протоколу SNMP',
        'unresolvable' => 'Ім\'я не вдалося співставити з IP адресою',
        'no_reply_community' => 'SNMP :version: Немає відповіді з community :credentials',
        'no_reply_credentials' => 'SNMP :version: Немає відповіді з реквізитами :credentials',
    ],
    'ldap_missing' => [
        'title' => 'Відсутня підтримка PHP LDAP',
        'message' => 'PHP не підтримує LDAP, встановіть або увімкніть розширення PHP LDAP',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Максимальний час виконання (:seconds секунда) перевищено|Максимальний час виконання (:seconds секунд) перевищено',
        'message' => 'Завантаження сторінки перевищило максимальний час виконання вказаний у налаштуваннях PHP.  Збільшіть max_execution_time у php.ini або покращіть серверне апаратне забезпечення',
    ],
    'unserializable_route_cache' => [
        'title' => 'Помилка спричинена неспівпадінням версії PHP',
        'message' => 'Версія PHP на веб-сервері (:web_version) не відповідає версії CLI (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'Не підтримувана версія SNMP ":snmpver", має бути v1, v2c, або v3',
    ],
];
