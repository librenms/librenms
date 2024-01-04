<?php

return [
    'title' => 'Налаштування',
    'readonly' => 'Зазначено у config.php, для увімкнення видаліть з config.php.',
    'groups' => [
        'alerting' => 'Сповіщення',
        'api' => 'API',
        'auth' => 'Автентифікація',
        'authorization' => 'Авторизація',
        'external' => 'Зовнішні',
        'global' => 'Глобальні',
        'os' => 'ОС',
        'discovery' => 'Віднайдення',
        'graphing' => 'Графіки',
        'poller' => 'Опитувач',
        'system' => 'Системні',
        'webui' => 'Веб-інтерфейс',
    ],
    'sections' => [
        'alerting' => [
            'general' => ['name' => 'Загальні налаштування сповіщень'],
            'email' => ['name' => 'Налаштування Email'],
            'rules' => ['name' => 'Налаштування правил сповіщень за замовчуванням'],
        ],
        'api' => [
            'cors' => ['name' => 'CORS'],
        ],
        'auth' => [
            'general' => ['name' => 'Загальні налаштування автентифікації'],
            'ad' => ['name' => 'Налаштування Active Directory'],
            'ldap' => ['name' => 'Налаштування LDAP'],
            'socialite' => ['name' => 'Налаштування Socialite'],
        ],
        'authorization' => [
            'device-group' => ['name' => 'Налаштування груп пристроїв'],
        ],
        'discovery' => [
            'general' => ['name' => 'Загальні налаштування віднайдення'],
            'route' => ['name' => 'Модуль віднайдення маршрутів'],
            'discovery_modules' => ['name' => 'Модулі віднайдення'],
            'storage' => ['name' => 'Модуль збереження даних'],
            'networks' => ['name' => 'Мережі'],
        ],
        'external' => [
            'binaries' => ['name' => 'Розміщення виконуваних файлів'],
            'location' => ['name' => 'Налаштування розміщення'],
            'graylog' => ['name' => 'Інтеграція з Graylog'],
            'oxidized' => ['name' => 'Інтеграція з Oxidized'],
            'mac_oui' => ['name' => 'Інтеграція з Mac OUI Lookup'],
            'peeringdb' => ['name' => 'Інтеграція з PeeringDB'],
            'nfsen' => ['name' => 'Інтеграція з NfSen'],
            'unix-agent' => ['name' => 'Інтеграція з Unix-Agent'],
            'smokeping' => ['name' => 'Інтеграція з Smokeping'],
            'snmptrapd' => ['name' => 'Інтеграція з SNMP трапами'],
        ],
        'poller' => [
            'availability' => ['name' => 'Доступність пристроїв'],
            'distributed' => ['name' => 'Розподілений опитувач'],
            'graphite' => ['name' => 'Сховище даних: Graphite'],
            'influxdb' => ['name' => 'Сховище даних: InfluxDB'],
            'opentsdb' => ['name' => 'Сховище даних: OpenTSDB'],
            'ping' => ['name' => 'Ping'],
            'prometheus' => ['name' => 'Сховище даних: Prometheus'],
            'rrdtool' => ['name' => 'Сховище даних: RRDTool'],
            'snmp' => ['name' => 'SNMP'],
            'poller_modules' => ['name' => 'Модулі опитувача'],
        ],
        'system' => [
            'cleanup' => ['name' => 'Очистка'],
            'proxy' => ['name' => 'Проксі'],
            'updates' => ['name' => 'Оновлення'],
            'server' => ['name' => 'Сервер'],
        ],
        'webui' => [
            'availability-map' => ['name' => 'Налаштування мапи доступності'],
            'graph' => ['name' => 'Налаштування графіків'],
            'dashboard' => ['name' => 'Налаштування дашбордів'],
            'port-descr' => ['name' => 'Обробка описів інтерфейсів'],
            'search' => ['name' => 'Налаштування пошуку'],
            'style' => ['name' => 'Стиль'],
            'device' => ['name' => 'Налаштування пристроїв'],
            'worldmap' => ['name' => 'Налаштування мапи світу'],
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Зберігати неактивних користувачів',
                'help' => 'Користувачі будуть видалені з LibreNMS після визначеної кількості днів без входу. 0 означає ніколи. Користувачі будуть створені повторно при вході.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Перевіряти на повтори IP адрес при додаванні пристроїв',
            'help' => 'Якщо хост додано по IP адресі, виконується перевірка на те що такий пристрій вже додано. Якщо такий IP існує в базі, хост не додається. Якщо хост додається по імені, ця перевірка не застосовується. З відповідним налаштуванням отримуються адреси імен хостів і перевірка виконується. Це допомагає запобігти випадковим дублікатам хостів.',
        ],
        'alert_rule' => [
            'severity' => [
                'description' => 'Тяжкість',
                'help' => 'Тяжкість сповіщення',
            ],
            'max_alerts' => [
                'description' => 'Максимум сповіщень',
                'help' => 'Кількість сповіщень які будуть надіслані',
            ],
            'delay' => [
                'description' => 'Затримка',
                'help' => 'Затримка перед надсиланням сповіщення',
            ],
            'interval' => [
                'description' => 'Інтервал',
                'help' => 'Інтервал для перевірки для цього сповіщення',
            ],
            'mute_alerts' => [
                'description' => 'Заглушити сповіщення',
                'help' => 'Сповіщення будуть показані лише у веб-інтерфейсі',
            ],
            'invert_rule_match' => [
                'description' => 'Інвертувати правило',
                'help' => 'Сповіщувати лише якщо правило не спрацьовує',
            ],
            'recovery_alerts' => [
                'description' => 'Сповіщення про відновлення',
                'help' => 'Сповістити при відновленні правила',
            ],
            'invert_map' => [
                'description' => 'Всі пристрої окрім вказаних у списку',
                'help' => 'Сповіщення лише для пристроїв що не у списку',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Підтвердження до очищення сповіщення',
                'help' => 'Підтвердження до очищення сповіщення за замовчуванням',
            ],
            'admins' => [
                'description' => 'Надсилати сповіщення адміністраторам',
                'help' => 'Сповіщати адміністраторів',
            ],
            'default_copy' => [
                'description' => 'Копіювати всі сповіщення по електронній пошті контакту за замовчуванням',
                'help' => 'Копіювати всі сповіщення по електронній пошті контакту за замовчуванням',
            ],
            'default_if_none' => [
                'description' => 'неможливо призначити у веб-інтерфейсі?',
                'help' => 'Надіслати листа контакту за замовчуванням якщо не було знайдено інших контактів',
            ],
            'default_mail' => [
                'description' => 'Контакт за замовчуванням',
                'help' => 'Контакт електронної пошти за замовчуванням',
            ],
            'default_only' => [
                'description' => 'Надвилати сповіщення лише контакту за замовчуванням',
                'help' => 'Сповіщувати лише контакт за замовчуванням по пошті',
            ],
            'disable' => [
                'description' => 'Вимкнути сповіщення',
                'help' => 'Зупинити генерацію сповіщень',
            ],
            'fixed-contacts' => [
                'description' => 'Оновлення контактних адрес електронної пошти не враховуються',
                'help' => 'При значенні TRUE будь-які зміни до sysContact або поштових адрес користувачів не будуть враховані допоки сповіщення активне',
            ],
            'globals' => [
                'description' => 'Надсилати сповіщення до користувачів з правами лише на читання',
                'help' => 'Сповістити користувачів з правами лише на читання',
            ],
            'syscontact' => [
                'description' => 'Надсилати сповіщення до sysContact',
                'help' => 'Надсилати сповіщення на поштову адресу у SNMP sysContact',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Увімкнути сповіщення по електронній пошті',
                    'help' => 'Транспорт сповіщення по електронній пошті',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Вікно відхилення для cron',
                'help' => 'Вікно відхилення у секундах',
            ],
            'users' => [
                'description' => 'Надсилати сповіщення звичайним користувачам',
                'help' => 'Сповіщувати звичайних користувачів',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Лог сповіщень старших за',
            'help' => 'Очистка виконується daily.sh',
        ],
        'discovery_on_reboot' => [
            'description' => 'Віднайдення при перезавантаженні',
            'help' => 'Виконувати віднайдення на перезавантаженому пристрої',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Дозволити повтори sysName',
            'help' => 'За замовчуванням повтори sysName не дозволені для уникнення багаторазового додання пристроїв з декількома інтерфейсами',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Дозволити неавтентифікований доступ до графіків',
            'help' => 'Дозволити будь-кому переглядати графіки без входу',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Дозволити доступ до графіків для обраних підмереж',
            'help' => 'Дозволити доступ до графіків без автентифікації з обраних підмереж (не застосовується при увімкненні неавтентифікованого доступу до графіків)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => 'Дозволити заголовки',
                    'help' => 'Виставляє заголовок відповіді Access-Control-Allow-Headers',
                ],
                'allowcredentials' => [
                    'description' => 'Дозволити автентифікаційні дані',
                    'help' => 'Виставляє заголовок Access-Control-Allow-Credentials',
                ],
                'allowmethods' => [
                    'description' => 'Дозволені методи',
                    'help' => 'Співпадає з методами запиту.',
                ],
                'enabled' => [
                    'description' => 'Увімкнути підтримку CORS для API',
                    'help' => 'Дозволяє завантажувати ресурси API з веб-клієнта',
                ],
                'exposeheaders' => [
                    'description' => 'Показувати заголовки',
                    'help' => 'Виставляє заголовок відповіді Access-Control-Expose-Headers',
                ],
                'maxage' => [
                    'description' => 'Максимальний вік',
                    'help' => 'Виставляє заголовок відповіді Access-Control-Max-Age',
                ],
                'origin' => [
                    'description' => 'Дозволити джерело запиту',
                    'help' => 'Співпадає з джерелом запиту. Можуть бути використані вільні символи, наприклад *.mydomain.com',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'API ключ для PowerDNS Recursor',
                    'help' => 'API ключ для застосунку PowerDNS Recursor при прямому підключенні',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor використовує HTTPS?',
                    'help' => 'Використати HTTPS замість HTTP для застосунку PowerDNS Recursor при прямому підключенні',
                ],
                'port' => [
                    'description' => 'Порт PowerDNS Recursor',
                    'help' => 'TCP порт для прямого з\'єднання до застосунку PowerDNS Recursor',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Ключ для кешу описів автономних систем',
        ],
        'auth' => [
            'socialite' => [
                'redirect' => [
                    'description' => 'Перенаправити сторінку входу',
                    'help' => 'Сторінка входу має одразу перенаправляти до першого визначеного провайдера.<br><br>ПІДКАЗКА: Можливо уникнути вставкою ?redirect=0 у URL',
                ],
                'register' => [
                    'description' => 'Дозволити реєстрацію через провайдера',
                ],
                'configs' => [
                    'description' => 'Налаштування провайдерів',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'Групи та користувачі мають входити до цього DN. Наприклад: dc=example,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Перевіряти сертифікати',
            'help' => 'Перевіряти сертифікати на дійсність. Деякі сервери використовують самопідписані сертифікати, вимкнення цього налаштування дозволяє їх використовувати.',
        ],
        'auth_ad_group_filter' => [
            'description' => 'Фільтр груп LDAP',
            'help' => 'Фільтр Active Directory LDAP для вибору груп',
        ],
        'auth_ad_groups' => [
            'description' => 'Доступ груп',
            'help' => 'Визначте групи що мають доступ та його рівень',
        ],
        'auth_ad_user_filter' => [
            'description' => 'Фільтр користувачів LDAP',
            'help' => 'Фільтр Active Directory LDAP для вибору користувачів',
        ],
        'auth_ad_url' => [
            'description' => 'Сервер(и) Active Directory',
            'help' => 'Вкажіть сервер(и), розділені пробілами. Вкажіть префікс ldaps:// для SSL. Приклад: ldaps://dc1.example.com ldaps://dc2.example.com',
        ],
        'auth_ad_domain' => [
            'description' => 'Домен Active Directory',
            'help' => 'Приклад домену Active Directory: example.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Атрибут для перевірки імен користувачів',
                'help' => 'Атрибут що використовується для ідентифікаії користувачів по імені',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (має вищий пріоритет за ім\'я користувача для bind)',
            'help' => 'Повний DN користувача для bind',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Пароль bind',
            'help' => 'Пароль користувача для bind',
        ],
        'auth_ldap_binduser' => [
            'description' => 'Ім\'я користувача для bind',
            'help' => 'Використовується для запитів до сервера LDAP коли користувач не увійшов (сповіщення, API, ін)',
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (має вищий пріоритет за ім\'я користувача для bind)',
            'help' => 'Повний DN користувача для bind',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Пароль bind',
            'help' => 'Пароль користувача для bind',
        ],
        'auth_ad_binduser' => [
            'description' => 'Bind username',
            'help' => 'Використовується для запитів до сервера AD коли користувач не увійшов (сповіщення, API, ін)',
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'Час існування кешу LDAP',
            'help' => 'Тимчасово зберігає результати запитів LDAP. Пришвидшує роботу, але дані можуть бути простроченими.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Показати debug',
            'help' => 'Показує інформацію для відладки. Може показувати чутливу інформацію не залишати увімкненим.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Атрибут пошти',
        ],
        'auth_ldap_group' => [
            'description' => 'DN групи для доступу',
            'help' => 'DN групи користувачів якій буде надано звичайний рівень доступу. Приклад: cn=groupname,ou=groups,dc=example,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'DN для груп',
            'help' => 'DN для пошуку груп. Приклад: ou=group,dc=example,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Атрибут участі в групі',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Шукати учасників групи по',
            'options' => [
                'username' => 'Імені користувача',
                'fulldn' => 'Повному DN (використовуючи префікс та суфікс)',
                'puredn' => 'DN (пошук використовуючи атрибут uid)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Доступ груп',
            'help' => 'Визначити групи що мають доступ та його рівень',
        ],
        'auth_ldap_port' => [
            'description' => 'Порт LDAP',
            'help' => 'Порт на сервері до якого буде здійснено підключення. Для LDAP повинно бути  389, для LDAPS 636',
        ],
        'auth_ldap_prefix' => [
            'description' => 'Префікс користувача',
            'help' => 'Використовується для перетворення імені користувача у DN',
        ],
        'auth_ldap_server' => [
            'description' => 'Сервер(и) LDAP',
            'help' => 'Визначити сервер(и), розділені пробілами. Префікс ldaps:// означає SSL',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Використовувати STARTTLS',
            'help' => 'Використовувати STARTTLS для безпеки з\'єднання. Альтернатива для LDAPS.',
            'options' => [
                'disabled' => 'Вимкнено',
                'optional' => 'Опціонально',
                'required' => 'Обов\'язково',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'Суфікс користувача',
            'help' => 'Використовується для перетворення імені користувача у DN',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Таймаут з\'єднання',
            'help' => 'Якщо один або більше серверів не відповідають, великі значення спричинять повільну роботу. Низькі значення в окремих випадках можуть призвести до скиду з\'єднань',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Атрибут унікального ID',
            'help' => 'Атрибут LDAP для ідентифікації користувачів, має бути числовим',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Використовувати повний DN користувача',
            'help' => 'Використовує повний DN користувача як значення атрибута member у групі замість member: username використовуючи префікс та суфікс. (member: uid=username,ou=groups,dc=domain,dc=com)',
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => 'Вайлдкард OU користувача',
            'help' => 'Шукати користувача що відповідає імені користувача незалежно від OU вказаному у суфіксі користувача. Корисно якщо ваші користувачі у різних OU. Ім\'я користувача для bind все ще використовує суфікс користувача',
        ],
        'auth_ldap_version' => [
            'description' => 'Версія LDAP',
            'help' => 'Версія LDAP для використання. Зазвичай 3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Метод авторизації (Увага!)',
            'help' => "Метод авторизації.  Увага, ви можете втратити змогу здійснити вхід. Ви можете перезаписати це налаштування назад до mysql зазначивши \$config['auth_mechanism'] = 'mysql'; у config.php",
            'options' => [
                'mysql' => 'MySQL (за замовчуванням)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'HTTP Автентифікація',
                'ad-authorization' => 'Зовнішньо автентифікований AD',
                'ldap-authorization' => 'Зовнішньо автентифікований LDAP',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Тривалість пам\'яті для користувача',
            'help' => 'Кількість днів протягом яких користувач буде зберігати сесію при використанні запам\'ятати мене.',
        ],
        'authlog_purge' => [
            'description' => 'Лог автентифікації старший за',
            'help' => 'Очистка проводиться daily.sh',
        ],
        'peering_descr' => [
            'description' => 'Типи портів Peering',
            'help' => 'Порти з вказаними типами описів будуть показані у пункті меню Peering.  Для додаткової інформації перегляньте документацію про Interface Description Parsing.',
        ],
        'transit_descr' => [
            'description' => 'Типи портів Transit',
            'help' => 'Порти з вказаними типами описів будуть показані у пункті меню Transit.  Для додаткової інформації перегляньте документацію про Interface Description Parsing.',
        ],
        'core_descr' => [
            'description' => 'Типи портів Core',
            'help' => 'Порти з вказаними типами описів будуть показані у пункті меню Core.  Для додаткової інформації перегляньте документацію про Interface Description Parsing.',
        ],
        'customers_descr' => [
            'description' => 'Типи портів Customer',
            'help' => 'Порти з вказаними типами описів будуть показані у пункті меню Customers.  Для додаткової інформації перегляньте документацію про Interface Description Parsing.',
        ],
        'base_url' => [
            'description' => 'Чітко вказаний URL',
            'help' => 'Це налаштування має бути вказане *лише* якщо необхідно *примусити* до використання певного імені хоста та порта. У цьому разі веб інтерфейс буде недоступний з будь-якого іншого імені',
        ],
        'device_perf_purge' => [
            'description' => 'Дані про поведінку пристроїв старші за',
            'help' => 'Очистка що виконується daily.sh',
        ],
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'Таблиця ARP',
            ],
            'applications' => [
                'description' => 'Застосунки',
            ],
            'bgp-peers' => [
                'description' => 'BGP Peers',
            ],
            'cisco-cbqos' => [
                'description' => 'Cisco CBQOS',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'slas' => [
                'description' => 'Service Level Agreement Tracking',
            ],
            'cisco-pw' => [
                'description' => 'Cisco PW',
            ],
            'cisco-vrf-lite' => [
                'description' => 'Cisco VRF Lite',
            ],
            'discovery-arp' => [
                'description' => 'Discovery ARP',
            ],
            'discovery-protocols' => [
                'description' => 'Протоколи дискаверінга',
            ],
            'entity-physical' => [
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'fdb-table' => [
                'description' => 'Таблиця FDB',
            ],
            'hr-device' => [
                'description' => 'Пристрій HR',
            ],
            'ipv4-addresses' => [
                'description' => 'Адреси IPv4',
            ],
            'ipv6-addresses' => [
                'description' => 'Адреси IPv6',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'loadbalancers' => [
                'description' => 'Loadbalancers',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mempools' => [
                'description' => 'Mempools',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'os' => [
                'description' => 'ОС',
            ],
            'ports' => [
                'description' => 'Порти',
            ],
            'ports-stack' => [
                'description' => 'Ports Stack',
            ],
            'processors' => [
                'description' => 'Процесори',
            ],

            'route' => [
                'description' => 'Маршрут',
            ],

            'sensors' => [
                'description' => 'Сенсори',
            ],

            'services' => [
                'description' => 'Сервіси',
            ],
            'storage' => [
                'description' => 'Пам\'ять',
            ],

            'stp' => [
                'description' => 'STP',
            ],
            'toner' => [
                'description' => 'Toner',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'vlans' => [
                'description' => 'VLans',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM Info',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => 'Бездротові',
            ],
        ],
        'distributed_poller' => [
            'description' => 'Увімкнути розподілене опитування (вимагає додаткових дій )',
            'help' => 'Увімкнути  розподілене опитування для всієї системи. Призначено для розподілу навантаження, не віддаленого опитування. Необхідно ознайомитися з документацією для налаштування: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => 'Група опитування за замовчуванням',
            'help' => 'Група опитування за замовчуванням яку мають опитувати всі опитувачі якщо не вказано у config.php',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Хост з memcached',
            'help' => 'Ім\'я хоста або IP адреса хоста сервера memcached. Необхідно для блокування poller_wrapper.py та daily.sh.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Порт memcached',
            'help' => 'Порт сервера memcached. За замовчуванням 11211',
        ],
        'email_auto_tls' => [
            'description' => 'Автоматична підтримка TLS',
            'help' => 'Виконується спроба використати TLS перед поверненням до нешифрованого з\'єднання',
        ],
        'email_backend' => [
            'description' => 'Як доставляти пошту',
            'help' => 'Бекенд доставки повідомлень електронної пошти, може бути mail, sendmail або SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'З адреси',
            'help' => 'Адреса електронної пошти для відправки листів (from)',
        ],
        'email_html' => [
            'description' => 'Використовувати HTML листи',
            'help' => 'Надсилати листи з HTML',
        ],
        'email_sendmail_path' => [
            'description' => 'Шлях до виконуваного файлу sendmail',
        ],
        'email_smtp_auth' => [
            'description' => 'Автентифікація SMTP',
            'help' => 'Увімкніть якщо ваш сервер SMTP потребує автентифікації',
        ],
        'email_smtp_host' => [
            'description' => 'Сервер SMTP',
            'help' => 'IP або ім\'я DNS для сервера SMTP на який надсилати пошту',
        ],
        'email_smtp_password' => [
            'description' => 'Пароль автентифікації SMTP',
        ],
        'email_smtp_port' => [
            'description' => 'Порт SMTP',
        ],
        'email_smtp_secure' => [
            'description' => 'Шифрування',
            'options' => [
                '' => 'Вимкнено',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'Таймаут SMTP',
        ],
        'email_smtp_username' => [
            'description' => 'Ім\'я користувача SMTP',
        ],
        'email_user' => [
            'description' => 'Ім\'я для поля from',
            'help' => 'Ім\'я що буде використане в якості частини адреси from',
        ],
        'eventlog_purge' => [
            'description' => 'Повідомлення логу подій старші за',
            'help' => 'Очистка проводиться daily.sh',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Перезаписує favicon за замовчуванням.',
        ],
        'fping' => [
            'description' => 'Шлях до fping',
        ],
        'fping6' => [
            'description' => 'Шлях до fping6',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'Кількість fping',
                'help' => 'Кількість запитів що будуть надіслані при перевірці по протоколу icmp',
            ],
            'interval' => [
                'description' => 'Інтервал fping',
                'help' => 'Кількість мілісекунд між запитами',
            ],
            'timeout' => [
                'description' => 'Таймаут fping',
                'help' => 'Кількість мілісекунд після яких припиняється очікування відповіді на запит',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Ключ до Mapping Engine API',
                'help' => 'Ключ до Geocoding API (Необхідний для функціонування)',
            ],
            'dns' => [
                'description' => 'Використати запис місцезнаходження DNS',
                'help' => 'Використати LOC записи DNS сервера для отримання географічних координат імені хоста',
            ],
            'engine' => [
                'description' => 'Провайдер мапи',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                ],
            ],
            'latlng' => [
                'description' => 'Спробувати геокодувати місцезнаходження',
                'help' => 'Спробувати отримати широту та довготу через API Geocode під час опитування',
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => 'Увімкнути',
                'help' => 'Експортувати метрики до Graphite',
            ],
            'host' => [
                'description' => 'Сервер',
                'help' => 'IP адреса або ім\'я сервера Graphite до якого будуть надіслані дані',
            ],
            'port' => [
                'description' => 'Порт',
                'help' => 'Порт для з\'єднання з сервером Graphite',
            ],
            'prefix' => [
                'description' => 'Префікс (опціонально)',
                'help' => 'Додасть префікс на початку всіх метрик. Має бути буквенно-чисельним та розділеним крапками',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => 'Тривалість',
                'help' => 'Обчислювати доступність пристроїв для вказаних тривалостей. (Визначаються в секундах)',
            ],
            'availability_consider_maintenance' => [
                'description' => 'Простій за розкладом не впливає на доступність',
                'help' => 'Вимикає створення втрати досяжності та зменшення доступності пристроїв у режимі простою.',
            ],
        ],
        'graphs' => [
            'port_speed_zoom' => [
                'description' => 'Наближати графіки портів до значень їх швидкості',
                'help' => 'Наближати графіки портів так щоб їх максимумом завжди була швидкість порта, графіки вимкнених портів наближаються до значень трафіку',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'Base URI',
                'help' => 'Перезаписати Base URI у разі якщо його було змінено з URI Graylog за замовчуванням.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Рівень логів на сторінці огляду пристрою',
                    'help' => 'Вказує максимальний рівень логів показаних на сторінці огляду пристрою.',
                ],
                'rowCount' => [
                    'description' => 'Кількість рядків на сторінці огляду пристрою',
                    'help' => 'Задає кількість рядків показаних на сторінці огляду пристрою.',
                ],
            ],
            'password' => [
                'description' => 'Пароль',
                'help' => 'Пароль для доступу до Graylog API.',
            ],
            'port' => [
                'description' => 'Порт',
                'help' => 'Порт доступу до Graylog API. Якщо не зазначено, використовуються 80 для HTTP та 443 для HTTPS.',
            ],
            'server' => [
                'description' => 'Сервер',
                'help' => 'IP адреса або ім\'я хоста API Graylog.',
            ],
            'timezone' => [
                'description' => 'Відображувана часова зона',
                'help' => 'Час у Graylog зберігається у GMT, це налаштування змінить відображувану часову зону. Значення має бути валідною часовою зоною PHP.',
            ],
            'username' => [
                'description' => 'Користувач',
                'help' => 'Користувач для доступу до Graylog API.',
            ],
            'version' => [
                'description' => 'Версія',
                'help' => 'Використовується для автоматичного сторення base_uri для Graylog API. Якщо ви змінили API URI, зазначте other та вкажіть свій base_uri.',
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => 'Головне випадаюче посилання',
                    'help' => 'Визначає головне посилання у випадаючому меню пристрою',
                ],
            ],
        ],
        'http_proxy' => [
            'description' => 'HTTP(S) Proxy',
            'help' => 'Зазначте як запасний варіант якщо змінні оточення http_proxy або https_proxy не доступні.',
        ],
        'ignore_mount' => [
            'description' => 'Проігноровані точки монтування',
            'help' => 'Не моніторити використання дискового простору даних точок монтування',
        ],
        'ignore_mount_network' => [
            'description' => 'Ігнорувати мережеві точки монтування',
            'help' => 'Не моніторити використання дискового простору мережевих точок монтування',
        ],
        'ignore_mount_optical' => [
            'description' => 'Ігнорувати оптичні приводи',
            'help' => 'Не моніторити використання дискового простору оптичних приводів',
        ],
        'ignore_mount_removable' => [
            'description' => 'Ігнорувати з\'ємні диски',
            'help' => 'Не моніторити використання дискового простору з\'ємних дисків',
        ],
        'ignore_mount_regexp' => [
            'description' => 'Ігнорувати точки монтування по регулярному виразу',
            'help' => 'Не моніторити використання дискового простору точок монтування або дискових пристроїв що відповідають хоча б одному зі вказаних регулярних виразів',
        ],
        'ignore_mount_string' => [
            'description' => 'Ігнорувати точки монтування з заданою стрічкою',
            'help' => 'Не моніторити використання дискового простору точок монтування або дискових пристроїв що містять хоча б одну зі вказаних стрічок',
        ],
        'influxdb' => [
            'db' => [
                'description' => 'База даних',
                'help' => 'Ім\'я бази даних InfluxDB для збереження метрик',
            ],
            'enable' => [
                'description' => 'Увімкнути',
                'help' => 'Експортувати метрики до InfluxDB',
            ],
            'host' => [
                'description' => 'Сервер',
                'help' => 'IP адреса або ім\'я сервера InfluxDB до якого будуть надіслані дані',
            ],
            'password' => [
                'description' => 'Пароль',
                'help' => 'Якщо необхідно, пароль для з\'єднання з InfluxDB',
            ],
            'port' => [
                'description' => 'Порт',
                'help' => 'Порт для з\'єднання з сервером InfluxDB',
            ],
            'timeout' => [
                'description' => 'Таймаут',
                'help' => 'Як довго чекати сервера InfluxDB, 0 означає таймаут за замовчуванням',
            ],
            'transport' => [
                'description' => 'Транспорт',
                'help' => 'Протокол для з\'єднання з сервером InfluxDB',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDP',
                ],
            ],
            'username' => [
                'description' => 'Користувач',
                'help' => 'КОристувач для з\'єднання з сервером InfluxDB',
            ],
            'verifySSL' => [
                'description' => 'Перевіряти SSL',
                'help' => 'Перевіряти чи сертифікат SSL валідний та довірений',
            ],
        ],
        'ipmitool' => [
            'description' => 'Шлях до ipmitool',
        ],
        'login_message' => [
            'description' => 'Повідомлення при вході',
            'help' => 'Відображається на сторінці входу',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => 'Увімкнути співставлення MAC OUI',
                'help' => 'Увімкнути співставлення вендорів MAC адрес (дані завантажуються daily.sh)',
            ],
        ],
        'mono_font' => [
            'description' => 'Моноширинний шрифт',
        ],
        'mtr' => [
            'description' => 'Шлях до mtr',
        ],
        'mydomain' => [
            'description' => 'Головний домен',
            'help' => 'Цей домен буде використаний для автовіднайдення пристроїв у мережі та інших процесів. LibreNMS спробує додавати його до некваліфікованих імен хостів.',
        ],
        'network_map_show_on_worldmap' => [
            'description' => 'Показати мережеві з\'єднання на мапі',
            'help' => 'Показати мережеві з\'єднання між різними місцями на мапі (на кшталт weathermap)',
        ],
        'nfsen_enable' => [
            'description' => 'Увімкнути NfSen',
            'help' => 'Увімкнути інтеграцію з NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'Директорії для RRD для NFSen',
            'help' => 'Це значення вказує на місце збереження файлів RRD для NFSen.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Задати схему піддиректорій NfSen',
            'help' => 'Має відповідати схемі піддиректорій визначеній у NfSen. 1 за замовчуванням.',
        ],
        'nfsen_last_max' => [
            'description' => 'Last Max',
        ],
        'nfsen_top_max' => [
            'description' => 'Top Max',
            'help' => 'Max topN value for stats',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => 'Default Top N',
        ],
        'nfsen_stat_default' => [
            'description' => 'Default Stat',
        ],
        'nfsen_order_default' => [
            'description' => 'Default Order',
        ],
        'nfsen_last_default' => [
            'description' => 'Default Last',
        ],
        'nfsen_lasts' => [
            'description' => 'Default Last Options',
        ],
        'nfsen_split_char' => [
            'description' => 'Символ-роздільник',
            'help' => 'Це значення контролює чим заміняти `.` у іменах пристроїв. Зазвичай: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'Суфікс імені файлу',
            'help' => 'Важливе налаштування та як імена пристроїв в NfSen обмежені 21 символом. Це значить що повні доменні імена пристроїв можуть не вміщатися, тому дане налаштування зазвичай не використовується.',
        ],
        'nmap' => [
            'description' => 'Шлях до nmap',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => 'Увімкнути',
                'help' => 'Експортувати метрики до OpenTSDB',
            ],
            'host' => [
                'description' => 'Сервер',
                'help' => 'IP адреса або ім\'я хоста сервера OpenTSDB',
            ],
            'port' => [
                'description' => 'Port',
                'help' => 'Порт для з\'єднання з сервером OpenTSDB',
            ],
        ],
        'own_hostname' => [
            'description' => 'Ім\'я хоста сервера LibreNMS',
            'help' => 'Має бути іменем хоста/IP адресою сервера LibreNMS',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Група яку Oxidized повертає за замовчуванням',
            ],
            'ignore_groups' => [
                'description' => 'Не зберігати резервну копію даних груп Oxidized',
                'help' => 'Групи (задані через відношення змінних) що не будуть надіслані до Oxidized',
            ],
            'enabled' => [
                'description' => 'Увімкнути підтримку Oxidized',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Увімкнути версіонування конфігурації',
                    'help' => 'Увімкнути збереження різних версій конфігурації (потребує git бекенду)',
                ],
            ],
            'group_support' => [
                'description' => 'Увімкнути підримку відправки груп до Oxidized',
            ],
            'ignore_os' => [
                'description' => 'Не створювати резервних копій для наступних OS',
                'help' => 'Ну здійснювати резервних копій вказаних OS за допомогою Oxidized. OS мусить співпадати з назвою OS у LibreNMS (у нижньому регістрі без пробілів).  Лише OS з існуючими визначеннями.',
            ],
            'ignore_types' => [
                'description' => 'Не створювати резервних копій для наступних типів пристроїв',
                'help' => 'Не створювати резервних копій для наступних типів пристроїв за допомогою Oxidized. Лише типи з існуючими визначеннями.',
            ],
            'reload_nodes' => [
                'description' => 'Перезавантажувати список пристроїв Oxidized кожного разу коли додається новий пристрій',
            ],
            'maps' => [
                'description' => 'Відношення змінних',
                'help' => 'Використовується для призначення груп або інших змінних.',
            ],
            'url' => [
                'description' => 'URL вашого Oxidized API',
                'help' => 'URL Oxidized API (Наприклад: http://127.0.0.1:8888)',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => 'Мінімальна довжина пароля',
                'help' => 'Паролі коротші за це значення будуть відкинуті',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Увімкнути співставлення з PeeringDB',
                'help' => 'Увімкнути співставлення з PeeringDB (дані завантажуються за допомогою  daily.sh)',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => 'Увімкнути доступ користувачів по динамічним групам пристроїв',
                ],
            ],
        ],
        'bad_iftype' => [
            'description' => 'Погані інтерфейси',
            'help' => 'Типи мережевих інтерфейсів щр мають бути проігноровані',
        ],
        'ping' => [
            'description' => 'Шлях до ping',
        ],
        'ping_rrd_step' => [
            'description' => 'Частота Ping',
            'help' => 'Частота перевірок. Є значенням за замовчуванням для всіх пристроїв. Увага! При зміні цього значення необхідно ввести додаткоі зміни.  Зверніться до документації Fast Ping.',
        ],
        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Unix Agent',
            ],
            'os' => [
                'description' => 'OS',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'sensors' => [
                'description' => 'Сенсори',
            ],
            'processors' => [
                'description' => 'Процесори',
            ],
            'mempools' => [
                'description' => 'Оперативна пам\'ять',
            ],
            'storage' => [
                'description' => 'Пристрої збереження даних',
            ],
            'netstats' => [
                'description' => 'Netstats',
            ],
            'hr-mib' => [
                'description' => 'HR Mib',
            ],
            'ucd-mib' => [
                'description' => 'Ucd Mib',
            ],
            'ipSystemStats' => [
                'description' => 'ipSystemStats',
            ],
            'ports' => [
                'description' => 'Порти',
            ],
            'bgp-peers' => [
                'description' => 'BGP Peers',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'toner' => [
                'description' => 'Toner',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wireless' => [
                'description' => 'Бездротові',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => 'Cisco IPSec flow Monitor',
            ],
            'cisco-remote-access-monitor' => [
                'description' => 'Cisco remote access Monitor',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'slas' => [
                'description' => 'Service Level Agreement Tracking',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cipsec-tunnels' => [
                'description' => 'Тунелі Cipsec',
            ],
            'cisco-ace-loadbalancer' => [
                'description' => 'Cisco ACE Loadbalancer',
            ],
            'cisco-ace-serverfarms' => [
                'description' => 'Cisco ACE Serverfarms',
            ],
            'cisco-asa-firewall' => [
                'description' => 'Cisco ASA Firewall',
            ],
            'cisco-voice' => [
                'description' => 'Cisco Voice',
            ],
            'cisco-cbqos' => [
                'description' => 'Cisco CBQOS',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'cisco-vpdn' => [
                'description' => 'Cisco VPDN',
            ],
            'nac' => [
                'description' => 'NAC',
            ],
            'netscaler-vsvr' => [
                'description' => 'Netscaler VSVR',
            ],
            'aruba-controller' => [
                'description' => 'Aruba Controller',
            ],
            'availability' => [
                'description' => 'Доступність',
            ],
            'entity-physical' => [
                'description' => 'Entity Physical',
            ],
            'entity-state' => [
                'description' => 'Entity State',
            ],
            'applications' => [
                'description' => 'Застосунки',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'vminfo' => [
                'description' => 'Hypervisor VM Info',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'loadbalancers' => [
                'description' => 'Loadbalancers',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
        ],
        'ports_fdb_purge' => [
            'description' => 'Записи FDB старші за',
            'help' => 'Очистка виконується daily.sh',
        ],
        'ports_purge' => [
            'description' => 'Порти очищення видалено',
            'help' => 'Очистка виконується daily.sh',
        ],
        'prometheus' => [
            'enable' => [
                'description' => 'Увімкнути',
                'help' => 'Експортувати метрики до Prometheus Push Gateway',
            ],
            'url' => [
                'description' => 'URL',
                'help' => 'URL Prometheus Push Gateway до якого надсилати дані',
            ],
            'Job' => [
                'description' => 'Job',
                'help' => 'Мітка Job для експортованих метрик',
            ],
            'attach_sysname' => [
                'description' => 'Додавати sysName пристрою',
                'help' => 'Додавати інформацію про sysName до Prometheus.',
            ],
            'prefix' => [
                'description' => 'Префікс',
                'help' => 'Опціональний префікс для додання до імен експортованих метрик',
            ],
        ],
        'public_status' => [
            'description' => 'Показувати статус публічно',
            'help' => 'Показувати статус деяких пристроїв на сторінці входу без автентифікації.',
        ],
        'routes_max_number' => [
            'description' => 'Максимальна кількість маршрутів дозволена для віднайдення',
            'help' => 'Маршрути не будуть віднайдені якщо розмір таблиці маршрутів перевищує це значення',
        ],
        'default_port_group' => [
            'description' => 'Група портів за замовчуванням',
            'help' => 'Нововіднайдені порти будуть призначені до цієї групи портів.',
        ],
        'nets' => [
            'description' => 'Підмережі для автовіднайдення',
            'help' => 'Підмережі пристрої з яких будуть віднайдені автоматично.',
        ],
        'autodiscovery' => [
            'nets-exclude' => [
                'description' => 'Підмережі/IP адреси які будуть проігноровані',
                'help' => 'Підмережі/IP адреси які не будуть віднайдені автоматично. Також виключає адреси з підмереж для автовіднайдення',
            ],
        ],
        'route_purge' => [
            'description' => 'Записи маршрутів старші за',
            'help' => 'Очистка виконується daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Змінити значення rrd heartbeat (за замовчуванням 600)',
            ],
            'step' => [
                'description' => 'Змінити значення rrd step (за замовчуванням 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'Шлях до файлів RRD',
            'help' => 'Місцезнаходження файлів RRD. За замовчуванням вони знаходяться усередині директорії LibreNMS. Зміна цього налаштування не переміщує файли.',
        ],
        'rrd_purge' => [
            'description' => 'RRD файли старші за',
            'help' => 'Очистка виконується daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'Налаштування формату RRD',
            'help' => 'Ці налаштування не можуть бути змінені без видалення існуючих файлів RRD. Однак ви можете зменшити або збільшити об\'єм кожного RRA.',
        ],
        'rrdcached' => [
            'description' => 'Увімкнути rrdcached (socket)',
            'help' => 'Вмикає rrdcached та визначає адресу сокета. Може бути мережевим або UNIX сокетом (unix:/run/rrdcached.sock або localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Шлях до rrdtool',
        ],
        'rrdtool_tune' => [
            'description' => 'Підлаштувати всі RRD файли портів на використання максимальних значень',
            'help' => 'Автоматично підлаштувати максимальні значення для RRD файлів портів',
        ],
        'rrdtool_version' => [
            'description' => 'Визначає версію rrdtool на вашому сервері',
            'help' => 'Версії вищі за 1.5.5 підтримують всі функції що використовує LibreNMS, не встановлюйте значення вищі за наявну версію',
        ],
        'service_poller_enabled' => [
            'description' => 'Увімкнути опитування',
            'help' => 'Вмикає процеси опитування. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_poller_workers' => [
            'description' => 'Процеси опитування',
            'help' => 'Кількість процесів опитування які буде створено. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_poller_frequency' => [
            'description' => 'Частота опитування (Увага!)',
            'help' => 'Як часто виконувати опитування пристроїв. Визначає значення за замовчуванням для всіх вузлів. Увага! Зміна цього налаштування без змін до файлів RRD поламає графіки. За додатковою інформацією зверніться до документації.',
        ],
        'service_poller_down_retry' => [
            'description' => 'Повторна спроба для недоступних пристроїв',
            'help' => 'Якщо пристрій недоступний при опитуванні. Визначає час який необхідно зачекати перед повторною спробою. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_discovery_enabled' => [
            'description' => 'Увімкнути віднайдення',
            'help' => 'Увімкнути процеси віднайдення. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_discovery_workers' => [
            'description' => 'Кількість процесів віднайдення',
            'help' => 'Кількість запущених процесів віднайдення. Занадто велике значення може викликати перевантаження. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_discovery_frequency' => [
            'description' => 'Частота віднайдення',
            'help' => 'Як часто виконувати віднайдення пристроїв. Визначає значення за замовчуванням для всіх вузлів. За замовчуванням 4 рази в день.',
        ],
        'service_services_enabled' => [
            'description' => 'Увімкнути перевірки сервісів',
            'help' => 'Увімкнути процеси перевірки сервісів. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_services_workers' => [
            'description' => 'Кількість процесів перевірки сервісів',
            'help' => 'Кількість процесів перевірки сервісів. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_services_frequency' => [
            'description' => 'Частота перевірки сервісів',
            'help' => 'Як часто запускати перевірки сервісів. Має відповідати частоті опитування. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_billing_enabled' => [
            'description' => 'Білінг увімкнено',
            'help' => 'Увімкнути процеси білінгу. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_billing_frequency' => [
            'description' => 'Частота білінгу',
            'help' => 'Як часто необхідно збирати інформацію білінгу. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_billing_calculate_frequency' => [
            'description' => 'Частота обчислень білінгу',
            'help' => 'Як часто обчислювати використання ресурсів. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_alerting_enabled' => [
            'description' => 'Сповіщення увімкнено',
            'help' => 'Увімкнути процес сповіщення. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_alerting_frequency' => [
            'description' => 'Частота перевірки сповіщень',
            'help' => 'Як часто перевіряються правила сповіщень. Дані оновлюються відповідно до частоти опитування. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_ping_enabled' => [
            'description' => 'Fast Ping Увімкнено',
            'help' => 'Fast Ping просто перевіряє пристрої на доступність. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_update_enabled' => [
            'description' => 'Щоденне обслуговування увімкнено',
            'help' => 'Запускає скрипт обслуговування daily.sh та перезапускає сервіс диспетчер. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_update_frequency' => [
            'description' => 'Частота обслуговування',
            'help' => 'Як часто запускати щоденне обслуговування. За замовчуванням раз в день. Бажано не змінювати дане значення. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_loglevel' => [
            'description' => 'Рівень логів',
            'help' => 'Рівень логів сервісу диспетчера. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_watchdog_enabled' => [
            'description' => 'Watchdog увімкнено',
            'help' => 'Watchdog спостерігає за лог файлом та перезапускає сервіс у разі тривалої відсутності оновлень. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'service_watchdog_log' => [
            'description' => 'Спостережуваний лог файл',
            'help' => 'За замовчуванням лог файл LibreNMS. Визначає значення за замовчуванням для всіх вузлів.',
        ],
        'sfdp' => [
            'description' => 'Шлях до sfdp',
        ],
        'shorthost_target_length' => [
            'description' => 'Максимальний розмір скороченого імені хоста',
            'help' => 'Скорочує ім\'я хоста до вказаної довжини, при цьому зберігаючи піддомен',
        ],
        'site_style' => [
            'description' => 'Визначити CSS стиль сайта',
            'options' => [
                'blue' => 'Blue',
                'dark' => 'Dark',
                'light' => 'Light',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Транспорт (пріоритет)',
                'help' => 'Оберіть увімкнені транспорти у порядку в якому необхідно їх використовувати.',
            ],
            'version' => [
                'description' => 'Версія (пріоритет)',
                'help' => 'Оберіть увімкнені версії у порядку в якому необхідно їх використовувати.',
            ],
            'community' => [
                'description' => 'Community (пріоритет)',
                'help' => 'Оберіть community для v1 та v2c у порядку в якому необхідно їх використовувати',
            ],
            'max_repeaters' => [
                'description' => 'Максимальна кількість repeater',
                'help' => 'Зазначити кількість repeater для використання у запитах SNMP bulk',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => 'Вимкнути SNMP bulk для окремих OID',
                    'help' => 'Вимкнути SNMP bulk для окремих OID. Зазвичай, має бути задано у визначенні OS. Має бути у форматі MIB::OID',
                ],
            ],
            'port' => [
                'description' => 'Порт',
                'help' => 'Визначити порт TCP/UDP для використання з SNMP',
            ],
            'timeout' => [
                'description' => 'Таймаут',
                'help' => 'Таймаут SNMP у секундах',
            ],
            'retries' => [
                'description' => 'Повторні спроби',
                'help' => 'Кількість повторних спроб на запит',
            ],
            'v3' => [
                'description' => 'Автентифікація SNMP v3 (пріоритет)',
                'help' => 'Налаштувати змінні автентифікації SNMPv3 у порядку в якому необхідно їх використовувати.',
                'auth' => 'Автентифікація',
                'crypto' => 'Крипто',
                'fields' => [
                    'authalgo' => 'Алгоритм',
                    'authlevel' => 'Рівень',
                    'authname' => 'Ім\'я користувача',
                    'authpass' => 'Пароль',
                    'cryptoalgo' => 'Алгоритм',
                    'cryptopass' => 'Пароль',
                ],
                'level' => [
                    'noAuthNoPriv' => 'Без автентифікації, без шифрування',
                    'authNoPriv' => 'Автентифікація, без шифрування',
                    'authPriv' => 'Автентифікація, шифрування',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'Шлях до snmpbulkwalk',
        ],
        'snmpget' => [
            'description' => 'Шлях до snmpget',
        ],
        'snmpgetnext' => [
            'description' => 'Шлях до snmpgetnext',
        ],
        'snmptranslate' => [
            'description' => 'Шлях до snmptranslate',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'Створювати запис у логу подій для трапів SNMP',
                'help' => 'Незалежно від дії що може бути призначена до трапа',
            ],
            'eventlog_detailed' => [
                'description' => 'Увімкнути детальне логування',
                'help' => 'Додавати всі отримані OID з трапами до логу подій',
            ],
        ],
        'snmpwalk' => [
            'description' => 'Шлях до snmpwalk',
        ],
        'syslog_filter' => [
            'description' => 'Фільтрувати повідомлення системного журналу що містять',
        ],
        'syslog_purge' => [
            'description' => 'Повідомлення системного журналу старші за',
            'help' => 'Очистка виконується daily.sh',
        ],
        'title_image' => [
            'description' => 'Титульне зображення',
            'help' => 'Перезаписує титульне зображення за замовчуванням.',
        ],
        'traceroute' => [
            'description' => 'Шлях до traceroute',
        ],
        'twofactor' => [
            'description' => 'Двохфакторна автентифікація',
            'help' => 'Дозволяє користувачам активувати та використовувати Timebased (TOTP) або Counterbased (HOTP) одноразові паролі (OTP)',
        ],
        'twofactor_lock' => [
            'description' => 'Час затримки між спробами (секунди)',
            'help' => 'Час затримки перед тим як дозволити наступні спроби якщо автентифікацію не вдалося виконати три рази. Зазначте 0 для вимкнення, що призведе до втрати доступу до облікового запису та відображення повідомлення з проханням звернутися до адміністратора',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Таймаут з\'єднання з Unix-agent',
            ],
            'port' => [
                'description' => 'Порт unix-agent за замовчуванням',
                'help' => 'Порт unix-agent за замовчуванням (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Таймаут читання Unix-agent',
            ],
        ],
        'update' => [
            'description' => 'Увімкнути оновлення у ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Визначити канал оновлень',
            'options' => [
                'master' => 'Daily',
                'release' => 'Monthly',
            ],
        ],
        'uptime_warning' => [
            'description' => 'Показувати warning якщо час роботи (uptime) менше ніж (у секундах)',
            'help' => 'Показувати warning на пристрої якщо його час роботи (uptime) менше ніж це значення. За замовчуванням 24h',
        ],
        'virsh' => [
            'description' => 'Шлях до virsh',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Ширина елементу доступності',
                'help' => 'Бажана ширина елементу у пікселях у повноекранному вигляді',
            ],
            'availability_map_compact' => [
                'description' => 'Компактний вигляд мапи доступності',
                'help' => 'Перегляд мапи доступності з малими індикаторами',
            ],
            'availability_map_sort_status' => [
                'description' => 'Сортувати по статусу',
                'help' => 'Сортувати пристрої та сервіси по статусу',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Використовувати фільтр по групі пристроїв',
                'help' => 'Увімкнути використання фільтру по групам пристроїв',
            ],
            'default_dashboard_id' => [
                'description' => 'Дашборд за замовчуванням',
                'help' => 'Глобальний dashboard_id за замовчуванням для всіх користувачів що не мають власного заданого дашборда',
            ],
            'dynamic_graphs' => [
                'description' => 'Увімкнути динамічні графіки',
                'help' => 'Увімкнути динамічні графіки, вмикає наближення та віддалення на графіках',
            ],
            'global_search_result_limit' => [
                'description' => 'Задає ліміт результатів пошуку',
                'help' => 'Глобальний ліміт результатів пошуку',
            ],
            'graph_stacked' => [
                'description' => 'Використовувати складені (stacked) графіки',
                'help' => 'Використовувати stacked графіки замість інвертованих',
            ],
            'graph_type' => [
                'description' => 'Визначити тип графіків',
                'help' => 'Визначити тип графіків за замовчуванням',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Визначити мінімальну висоту графіків',
                'help' => 'Мінімальна висота графіка (за замовчуванням: 300)',
            ],
        ],
        'device_display_default' => [
            'description' => 'Шаблон відображуваного імені пристрою за замовчуванням',
            'help' => 'Визначає відображуване ім\'я для всіх пристроїв (може бути перезаписано для окремих пристроїв).  Ім\'я хоста / IP: Показувати ім\'я хоста або IP адресу з якими пристрій було додано. sysName: Показувати sysName з протоколу SNMP. Ім\'я хоста, якщо нема то sysName: Показувати ім\'я хоста, але якщо це IP адреса показувати sysName.',
            'options' => [
                'hostname' => 'Ім\'я хоста / IP',
                'sysName_fallback' => 'Ім\'я хоста, якщо нема то sysName',
                'sysName' => 'sysName',
                'ip' => 'IP (hostname IP або по запиту DNS)',
            ],
        ],
        'device_location_map_open' => [
            'description' => 'Відкрита мапа місцезнаходження',
            'help' => 'Мапа місцезнаходження показана за замовчуванням',
        ],
        'whois' => [
            'description' => 'Шлях до whois',
        ],
        'smokeping.integration' => [
            'description' => 'Увімкнути',
            'help' => 'Увімкнути інтеграцію зі smokeping',
        ],
        'smokeping.dir' => [
            'description' => 'Шлях до файлів RRD',
            'help' => 'Повний шлях до файлів Smokeping RRD',
        ],
        'smokeping.pings' => [
            'description' => 'Кількість ping',
            'help' => 'Кількість запитів ping налаштована у Smokeping',
        ],
        'smokeping.url' => [
            'description' => 'URL до smokeping',
            'help' => 'Повний URL до GUI smokeping',
        ],
    ],
    'twofactor' => [
        'description' => 'Увімкнути двофакторну автентифікацію',
        'help' => 'Вмикає вбудовану двофакторну автентифікацію. ВИ маєте налаштуванти кожний акаунт для активації.',
    ],
    'units' => [
        'days' => 'дні',
        'ms' => 'мс',
        'seconds' => 'секунди',
    ],
    'validate' => [
        'boolean' => ':value не є валідним булевим значенням',
        'color' => ':value не є валідним шістнадцятковим кольоровим кодом',
        'email' => ':value не є валідною адресою електронної пошти',
        'integer' => ':value не є типом integer',
        'password' => 'Пароль невірний',
        'select' => ':value не є дозволеним значенням',
        'text' => ':value не є дозволеним',
        'array' => 'Невірний формат',
        'executable' => ':value не є валідним виконуваним файлом',
        'directory' => ':value не є валідною директорією',
    ],
];
