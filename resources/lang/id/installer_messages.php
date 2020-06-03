<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Laravel Installer',
    'next' => 'Selanjutnya',
    'back' => 'Kembali',
    'finish' => 'Pasang',
    'forms' => [
        'errorTitle' => 'Terjadi galat sebagai berikut:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Selamat Datang',
        'title'   => 'Laravel Installer',
        'message' => 'Instalasi Mudah dan Persiapan Aplikasi',
        'next'    => 'Cek Kebutuhan',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Langkah 1 | Kebutuhan Server',
        'title' => 'Kebutuhan Server',
        'next'    => 'Cek Hak Akses',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Langkah 2 | Hak Akses',
        'title' => 'Hak Akses',
        'next' => 'Konfigurasi Lingkungan',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Langkah 3 | Penyetelan Lingkungan',
            'title' => 'Penyetelan Lingkungan',
            'desc' => 'Silahkan pilih bagaimana Anda akan mengkofigurasi berkas <code>.env</code> aplikasi.',
            'wizard-button' => 'Form Penyetelan Wizard',
            'classic-button' => 'Classic Text Editor',
        ],
        'wizard' => [
            'templateTitle' => 'Langkah 3 | Penyetelan Lingkungan | Wizard Terpandu',
            'title' => 'Wizard <code>.env</code> Terpandu',
            'tabs' => [
                'environment' => 'Lingkungan',
                'database' => 'Basis Data',
                'application' => 'Aplikasi',
            ],
            'form' => [
                'name_required' => 'Lingkungan aplikasi harus ditetapkan',
                'app_name_label' => 'Nama Aplikasi',
                'app_name_placeholder' => 'Nama Aplikasi',
                'app_environment_label' => 'Lingkungan Aplikasi',
                'app_environment_label_local' => 'Lokal',
                'app_environment_label_developement' => 'Pengembangan',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Produksi',
                'app_environment_label_other' => 'Lainnya',
                'app_environment_placeholder_other' => 'Masukan lingkungan...',
                'app_debug_label' => 'Debug Aplikasi',
                'app_debug_label_true' => 'Iya',
                'app_debug_label_false' => 'Tidak',
                'app_log_level_label' => 'Level Log Aplikasi',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL Aplikasi',
                'app_url_placeholder' => 'URL Aplikasi',
                'db_connection_label' => 'Koneksi Basis Data',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host Basis Data',
                'db_host_placeholder' => 'Host Basis Data',
                'db_port_label' => 'Port Basis Data',
                'db_port_placeholder' => 'Port Basis Data',
                'db_name_label' => 'Nama Basis Data',
                'db_name_placeholder' => 'Nama Basis Data',
                'db_username_label' => 'Pengguna Basis Data',
                'db_username_placeholder' => 'Pengguna Basis Data',
                'db_password_label' => 'Kata Sandi Basis Data',
                'db_password_placeholder' => 'Kata Sandi Basis Data',

                'app_tabs' => [
                    'more_info' => 'Informasi Lainnya',
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
                    'setup_database' => 'Setel Basis Data',
                    'setup_application' => 'Setel Aplikasi',
                    'install' => 'Pasang',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Langkah 3 | Penyetelan Lingkungan | Classic Editor',
            'title' => 'Classic Environment Editor',
            'save' => 'Simpan .env',
            'back' => 'Gunakan Form Wizard',
            'install' => 'Simpan dan Pasang',
        ],
        'success' => 'Berkas penyetelan .env Anda telah disimpan.',
        'errors' => 'Tidak bisa menyimpan berkas .env. Silahkan buat secara manual.',
    ],

    'install' => 'Pasang',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Laravel Installer berhasil DIPASANG pada ',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Instalasi Selesai',
        'templateTitle' => 'Instalasi Selesai',
        'finished' => 'Aplikasi telah berhasil dipasang.',
        'migration' => 'Keluaran Migration &amp; Seed Console:',
        'console' => 'Keluaran Application Console:',
        'log' => 'Entri Log Aplikasi:',
        'env' => 'Hasil akhir berkas .env:',
        'exit' => 'Klik disini untuk keluar',
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
        'title' => 'Laravel Updater',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Selamat Datang di App Updater',
            'message' => 'Selamat Datang di update wizard.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Tinjauan',
            'message' => 'Ada 1 pembaruan.|Ada :number pembaruan.',
            'install_updates' => 'Pasang Pembaruan',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Selesai',
            'finished' => 'Basis Data Aplikasi telah berhasil diperbarui.',
            'exit' => 'Klik disini untuk keluar',
        ],

        'log' => [
            'success_message' => 'Laravel Installer berhasil DIPERBARUI pada ',
        ],
    ],
];
