<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title'        => 'Установка Laravel',
    'next'         => 'Следующий шаг',

    /*
     *
     * Home page translations.
     *
     */
    'welcome'      => [
        'title'   => 'Установка Laravel',
        'message' => 'Добро пожаловать в первоначальную настройку фреймворка Laravel.',
        'next'    => 'Следующий шаг',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'title' => 'Необходимые модули',
        'next'  => 'Следующий шаг',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions'  => [
        'title' => 'Проверка прав на папках',
        'next'  => 'Следующий шаг',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment'  => [
        'menu' => [
            'templateTitle' => 'Шаг 3 | Настройки среды',
            'title' => 'Настройки среды',
            'desc' => 'Выберите, как вы хотите настроить файл <code> .env </code>.',
            'wizard-button' => 'Мастера форм',
            'classic-button' => 'Редактор текста',
        ],
        'wizard' => [
            'templateTitle' => 'Шаг 3 | Настройки среды | Управляемый мастер',
            'title' => 'Управляемый <code> .env </code> Мастер',
            'tabs' => [
                'environment' => 'Окружение',
                'database' => 'База данных',
                'application' => 'Приложение',
            ],
            'form' => [
                'name_required' => 'Требуется имя среды.',
                'app_name_label' => 'Имя приложения',
                'app_name_placeholder' => 'Имя приложения',
                'app_environment_label' => 'Окружение приложения',
                'app_environment_label_local' => 'Локальное',
                'app_environment_label_developement' => 'Разработочное',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Продакшн',
                'app_environment_label_other' => 'Другое',
                'app_environment_placeholder_other' => 'Введите свое окружение ...',
                'app_debug_label' => 'Дебаг приложения',
                'app_debug_label_true' => 'Да',
                'app_debug_label_false' => 'Нет',
                'app_log_level_label' => 'Уровень журнала логирования',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL приложения',
                'app_url_placeholder' => 'URL приложения',
                'db_connection_label' => 'Подключение к базе данных',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Хост базы данных',
                'db_host_placeholder' => 'Хост базы данных',
                'db_port_label' => 'Порт базы данных',
                'db_port_placeholder' => 'Порт базы данных',
                'db_name_label' => 'Название базы данных',
                'db_name_placeholder' => 'Название базы данных',
                'db_username_label' => 'Имя пользователя базы данных',
                'db_username_placeholder' => 'Имя пользователя базы данных',
                'db_password_label' => 'Пароль базы данных',
                'db_password_placeholder' => 'Пароль базы данных',

                'app_tabs' => [
                    'more_info' => 'Больше информации',
                    'broadcasting_title' => 'Broadcasting, Caching, Session, &amp; Queue',
                    'broadcasting_label' => 'Broadcast Driver',
                    'broadcasting_placeholder' => 'Broadcast Driver',
                    'cache_label' => 'Cache Driver',
                    'cache_placeholder' => 'Cache Driver',
                    'session_label' => 'Session Driver',
                    'session_placeholder' => 'Session Driver',
                    'queue_label' => 'Queue Driver',
                    'queue_placeholder' => 'Queue Driver',
                    'redis_label' => 'Redis Driver',
                    'redis_host' => 'Redis Host',
                    'redis_password' => 'Redis Password',
                    'redis_port' => 'Redis Port',

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Mail Driver',
                    'mail_driver_placeholder' => 'Mail Driver',
                    'mail_host_label' => 'Mail Host',
                    'mail_host_placeholder' => 'Mail Host',
                    'mail_port_label' => 'Mail Port',
                    'mail_port_placeholder' => 'Mail Port',
                    'mail_username_label' => 'Mail Username',
                    'mail_username_placeholder' => 'Mail Username',
                    'mail_password_label' => 'Mail Password',
                    'mail_password_placeholder' => 'Mail Password',
                    'mail_encryption_label' => 'Mail Encryption',
                    'mail_encryption_placeholder' => 'Mail Encryption',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App Id',
                    'pusher_app_id_palceholder' => 'Pusher App Id',
                    'pusher_app_key_label' => 'Pusher App Key',
                    'pusher_app_key_palceholder' => 'Pusher App Key',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Настройка базы данных',
                    'setup_application' => 'Настройка приложения',
                    'install' => 'Установить',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Шаг 3 | Настройки среды | Классический редактор',
            'title' => 'Классический редактор среды',
            'save' => 'Сохранить .env',
            'back' => 'Использовать мастер форм',
            'install' => 'Сохранить и установить',
        ],
        'title'   => 'Настройки окружения',
        'save'    => 'Сохранить .env',
        'success' => 'Настройки успешно сохранены в файле .env',
        'errors'  => 'Произошла ошибка при сохранении файла .env, пожалуйста, сохраните его вручную',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final'        => [
        'title'    => 'Готово',
        'finished' => 'Приложение успешно настроено.',
        'exit'     => 'Нажмите для выхода',
    ],
];
