<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'โปรแกรมติดตั้ง Laravel',
    'next' => 'ขั้นตอนต่อไป',
    'back' => 'ย้อนกลับ',
    'finish' => 'ติดตั้ง',
    'forms' => [
        'errorTitle' => 'ข้อผิดพลาดต่อไปนี้เกิดขึ้น:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'ยินดีต้อนรับ',
        'title'   => 'โปรแกรมติดตั้ง Laravel',
        'message' => 'วิซาร์ดการติดตั้งและติดตั้งง่าย',
        'next'    => 'ตรวจสอบข้อกำหนด',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'ขั้นตอนที่ 1 | ข้อกำหนดของเซิร์ฟเวอร์',
        'title' => 'ข้อกำหนดของเซิร์ฟเวอร์',
        'next'    => 'ตรวจสอบการอนุญาต',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'ขั้นตอนที่ 2 | สิทธิ์',
        'title' => 'สิทธิ์',
        'next' => 'กำหนดค่าสภาพแวดล้อม',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'ขั้นตอนที่ 3 | การตั้งค่าสภาพแวดล้อม',
            'title' => 'การตั้งค่าสภาพแวดล้อม',
            'desc' => 'โปรดเลือกวิธีที่คุณต้องการกำหนดค่าไฟล์แอป <code> .env </code>',
            'wizard-button' => 'การตั้งค่าตัวช่วยสร้างฟอร์ม',
            'classic-button' => 'แก้ไขข้อความคลาสสิก',
        ],
        'wizard' => [
            'templateTitle' => 'ขั้นตอนที่ 3 | การตั้งค่าสภาพแวดล้อม | ตัวช่วยสร้างการแนะนำ',
            'title' => 'วิซาร์ด <code> .env </code> ที่แนะนำ',
            'tabs' => [
                'environment' => 'สิ่งแวดล้อม',
                'database' => 'ฐานข้อมูล',
                'application' => 'แอพพลิเคชั่น',
            ],
            'form' => [
                'name_required' => 'ต้องระบุชื่อสภาพแวดล้อม',
                'app_name_label' => 'ชื่อแอป',
                'app_name_placeholder' => 'ชื่อแอป',
                'app_environment_label' => 'สภาพแวดล้อมของแอป',
                'app_environment_label_local' => 'ในประเทศ',
                'app_environment_label_developement' => 'พัฒนาการ',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'การผลิต',
                'app_environment_label_other' => 'อื่น ๆ',
                'app_environment_placeholder_other' => 'เข้าสู่สภาพแวดล้อมของคุณ ...',
                'app_debug_label' => 'Debug แอป',
                'app_debug_label_true' => 'จริง',
                'app_debug_label_false' => 'เท็จ',
                'app_log_level_label' => 'ระดับการบันทึกแอป',
                'app_log_level_label_debug' => 'การแก้ปัญหา',
                'app_log_level_label_info' => 'ข้อมูล',
                'app_log_level_label_notice' => 'แจ้งให้ทราบ',
                'app_log_level_label_warning' => 'การเตือน',
                'app_log_level_label_error' => 'ความผิดพลาด',
                'app_log_level_label_critical' => 'วิกฤติ',
                'app_log_level_label_alert' => 'เตือนภัย',
                'app_log_level_label_emergency' => 'กรณีฉุกเฉิน',
                'app_url_label' => 'แอป URL',
                'app_url_placeholder' => 'แอป URL',
                'db_connection_label' => 'การเชื่อมต่อฐานข้อมูล',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'โฮสต์ฐานข้อมูล',
                'db_host_placeholder' => 'โฮสต์ฐานข้อมูล',
                'db_port_label' => 'พอร์ตฐานข้อมูล',
                'db_port_placeholder' => 'พอร์ตฐานข้อมูล',
                'db_name_label' => 'ชื่อฐานข้อมูล',
                'db_name_placeholder' => 'ชื่อฐานข้อมูล',
                'db_username_label' => 'ชื่อผู้ใช้ฐานข้อมูล',
                'db_username_placeholder' => 'ชื่อผู้ใช้ฐานข้อมูล',
                'db_password_label' => 'รหัสผ่านฐานข้อมูล',
                'db_password_placeholder' => 'รหัสผ่านฐานข้อมูล',

                'app_tabs' => [
                    'more_info' => 'ข้อมูลเพิ่มเติม',
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
                    'setup_database' => 'ตั้งค่าฐานข้อมูล',
                    'setup_application' => 'แอปพลิเคชันติดตั้ง',
                    'install' => 'ติดตั้ง',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'ขั้นตอนที่ 3 | การตั้งค่าสภาพแวดล้อม | ตัวแก้ไขแบบคลาสสิก',
            'title' => 'ตัวแก้ไขสภาพแวดล้อมแบบคลาสสิค',
            'save' => 'บันทึก .env',
            'back' => 'ใช้ตัวช่วยสร้างแบบฟอร์ม',
            'install' => 'บันทึกและติดตั้ง',
        ],
        'success' => 'ของคุณ .env บันทึกการตั้งค่าไฟล์แล้ว',
        'errors' => 'ไม่สามารถบันทึก .env ไฟล์, โปรดสร้างด้วยตนเอง',
    ],

    'install' => 'ติดตั้ง',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'ติดตั้ง Laravel สำเร็จติดตั้งแล้ว',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'การติดตั้งเสร็จสิ้น',
        'templateTitle' => 'การติดตั้งเสร็จสิ้น',
        'finished' => 'ติดตั้งแอปพลิเคชันสำเร็จแล้ว',
        'migration' => 'การย้าย &amp; Seed Console Output:',
        'console' => 'แอพพลิเคชันคอนโซลเอาท์พุท:',
        'log' => 'บันทึกการติดตั้ง:',
        'env' => 'ไฟล์. env สุดท้าย:',
        'exit' => 'คลิกที่นี่เพื่อออก',
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
            'title'   => 'ยินดีต้อนรับสู่ The Updater',
            'message' => 'ยินดีต้อนรับสู่ตัวช่วยการอัพเดต',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'ภาพรวม',
            'message' => 'มีการอัปเดต 1 รายการ | มี: อัปเดตตัวเลข',
            'install_updates' => 'ติดตั้งการปรับปรุง',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'เสร็จ',
            'finished' => 'แอพพลิเคชั่น อัปเดตฐานข้อมูลสำเร็จแล้ว',
            'exit' => 'คลิกที่นี่เพื่อออก',
        ],

        'log' => [
            'success_message' => 'ติดตั้ง Laravel สำเร็จแล้วอัปเดตเมื่อ',
        ],
    ],
];
