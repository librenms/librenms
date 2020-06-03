<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Kurulum',
    'next' => 'Sonraki Adım',
    'back' => 'Önceki Adım',
    'finish' => 'Kur',
    'forms' => [
        'errorTitle' => 'Hatalar tespit edildi :',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Kurulum\'a Hoşgeldiniz',
        'title'   => 'Kurulum',
        'message' => 'Kolay Kurulum Sihirbazı.',
        'next'    => 'Gereksinimleri Denetle',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Adım 1 | Sunucu Gereksinimleri',
        'title' => 'Sunucu Gereksinimleri',
        'next'    => 'İzinleri Kontrol Et',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Adım 2 | İzinler',
        'title' => 'İzinler',
        'next' => 'Ortam ayarlarına geç',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Adım 3 | Ortam Ayarları',
            'title' => 'Ortam Ayarları',
            'desc' => 'Lütfen uygulamanın <code> .env </code> dosyasını nasıl yapılandıracağınızı seçin.',
            'wizard-button' => 'Form Sihirbazı Kurulumu ',
            'classic-button' => 'Klasik Metin Editörü',
        ],
        'wizard' => [
            'templateTitle' => 'Adım 3 | Ortam Ayarları | Form sihirbazı',
            'title' => 'Guided <code>.env</code> Wizard',
            'tabs' => [
                'environment' => 'Ortam',
                'database' => 'Veritabanı',
                'application' => 'Uygulama',
            ],
            'form' => [
                'name_required' => 'Bir ortam adı gerekiyor.',
                'app_name_label' => 'Uygulama Adı',
                'app_name_placeholder' => 'Uygulama Adı',
                'app_environment_label' => 'Uygulama Ortamı',
                'app_environment_label_local' => 'Yerel',
                'app_environment_label_developement' => 'Geliştirme',
                'app_environment_label_qa' => 'qa',
                'app_environment_label_production' => 'Üretim',
                'app_environment_label_other' => 'Diğer',
                'app_environment_placeholder_other' => 'Çevrenizi girin ...',
                'app_debug_label' => 'Uygulama Hataları Gösterme',
                'app_debug_label_true' => 'Aktif',
                'app_debug_label_false' => 'Pasif',
                'app_log_level_label' => 'Uygulama Günlüğü Düzeyi',
                'app_log_level_label_debug' => 'hata ayıklama',
                'app_log_level_label_info' => 'bilgi',
                'app_log_level_label_notice' => 'haber',
                'app_log_level_label_warning' => 'uyarı',
                'app_log_level_label_error' => 'hata',
                'app_log_level_label_critical' => 'kritik',
                'app_log_level_label_alert' => 'uyarı',
                'app_log_level_label_emergency' => 'acil durum',
                'app_url_label' => 'Uygulama URL\'si',
                'app_url_placeholder' => 'Uygulama URL\'si',
                'db_connection_label' => 'Veritabanı Bağlantısı',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Veritabanı Sunucusu',
                'db_host_placeholder' => 'Veritabanı Sunucusu',
                'db_port_label' => 'Veritabanı Bağlantı Noktası',
                'db_port_placeholder' => 'Veritabanı Bağlantı Noktası',
                'db_name_label' => 'Veritabanı Adı',
                'db_name_placeholder' => 'Veritabanı Adı',
                'db_username_label' => 'Veritabanı Kullanıcı Adı',
                'db_username_placeholder' => 'Veritabanı Kullanıcı Adı',
                'db_password_label' => 'Veritabanı Şifresi',
                'db_password_placeholder' => 'Veritabanı Şifresi',
                'app_tabs' => [
                    'more_info' => 'Daha Fazla Bilgi',
                    'broadcasting_title' => 'Yayıncılık, Önbellekleme, Oturum &amp; Kuyruk',
                    'broadcasting_label' => 'Yayıncı Sürücüsü',
                    'broadcasting_placeholder' => 'Yayıncı Sürücüsü',
                    'cache_label' => 'Önbellek Sürücüsü',
                    'cache_placeholder' => 'Önbellek Sürücüsü',
                    'session_label' => 'Oturum Sürücüsü',
                    'session_placeholder' => 'Oturum Sürücüsü',
                    'queue_label' => 'Kuyruk Sürücüsü',
                    'queue_placeholder' => 'Kuyruk Sürücüsü',
                    'redis_label' => 'Redis Sürücüsü',
                    'redis_host' => 'Redis Host',
                    'redis_password' => 'Redis Şifre',
                    'redis_port' => 'Redis Port',

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Posta Sürücüsü',
                    'mail_driver_placeholder' => 'Posta Sürücüsü',
                    'mail_host_label' => 'Posta Sunucusu',
                    'mail_host_placeholder' => 'Posta Sunucusu',
                    'mail_port_label' => 'Posta Bağlantı Noktası',
                    'mail_port_placeholder' => 'Posta Bağlantı Noktası',
                    'mail_username_label' => 'Posta Kullanıcı Adı',
                    'mail_username_placeholder' => 'Posta Kullanıcı Adı',
                    'mail_password_label' => 'Posta Parolası',
                    'mail_password_placeholder' => 'Posta Parolası',
                    'mail_encryption_label' => 'Posta Güvenlik Türü',
                    'mail_encryption_placeholder' => 'Posta Güvenlik Türü',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'İtici Uygulama Kimliği',
                    'pusher_app_id_palceholder' => 'İtici Uygulama Kimliği',
                    'pusher_app_key_label' => 'İtici Uygulama Anahtarı',
                    'pusher_app_key_palceholder' => 'İtici Uygulama Anahtarı',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Veritabanı Ayarları',
                    'setup_application' => 'Uygulama Ayarları',
                    'install' => 'Yükle',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => '3. Adım | Ortam Ayarları | Klasik Editör ',
            'title' => 'Klasik Metin Editörü',
            'save' => 'Kaydet (.env)',
            'back' => 'Form Sihirbazını Kullan',
            'install' => 'Yükle',
        ],
        'success' => '.env dosyası ayarları kaydedildi.',
        'errors' => '.env dosyasını kaydedemiyoruz, lütfen el ile oluşturun.',
    ],

    'install' => 'Kurulum',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Uygulama başarıyla KURULDU ',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Kurulum Bitti',
        'templateTitle' => 'Kurulum Bitti',
        'finished' => 'Uygulama başarıyla kuruldu.',
        'migration' => 'Veritabanı  Konsolu Çıktısı: ',
        'console' => 'Uygulama Konsolu Çıktısı:',
        'log' => 'Kurulum Günlüğü Girişi:',
        'env' => 'Son .env Dosyası:',
        'exit' => 'Çıkmak için burayı tıklayın',
    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        /*
         *
         * Shared translations.
         *
         */
        'title' => 'Güncelleyici',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title' => 'Güncelleyiciye Hoş Geldiniz',
            'message' => 'Güncelleme sihirbazına hoş geldiniz.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Genel bakış',
            'message' => '1 güncelleme var.| :number güncellemeleri var.',
            'install_updates' => 'Güncellemeyi yükle',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Tamamlandı',
            'finished' => 'Uygulamanın veritabanını başarıyla güncelleştirildi.',
            'exit' => 'Çıkmak ve uygulamayı başlatmak için buraya tıklayın',
        ],

        'log' => [
            'success_message' => 'Uygulama GÜNCELLENDİ  ',
        ],
    ],
];
