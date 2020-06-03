<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Installateur de Laravel',
    'next' => 'Suivant',
    'back' => 'Précedent',
    'finish' => 'Installer',
    'forms' => [
        'errorTitle' => 'Les erreurs suivantes sont survenues:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'title'   => 'Bienvenue dans l’installateur...',
        'message' => 'Assistant d\'installation et de configuration facile.',
        'next'    => 'Vérifier les prérequis',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Étape 1 | Prérequis du serveur',
        'title' => 'Prérequis du serveur',
        'next'    => 'Vérifier les Permissions',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Étape 2 | Permissions',
        'title' => 'Permissions',
        'next' => 'Configurer l\'Environment',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement',
            'title' => 'Paramètres d\'environnement',
            'desc' => 'Veuillez sélectionner comment vous souhaitez configurer les applications <code>.env</code> file.',
            'wizard-button' => 'Configuration de l\'assistant de formulaire',
            'classic-button' => 'Éditeur de texte classique',
        ],
        'wizard' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement | Assistant guidé',
            'title' => 'Assitant <code>.env</code> Guidé',
            'tabs' => [
                'environment' => 'Environnement',
                'database' => 'Base de donnée',
                'application' => 'Application',
            ],
            'form' => [
                'name_required' => 'Un nom d\'environnement est requis.',
                'app_name_label' => 'App Name',
                'app_name_placeholder' => 'App Name',
                'app_environment_label' => 'App Environment',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Development',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Production',
                'app_environment_label_other' => 'Other',
                'app_environment_placeholder_other' => 'Entrez votre environnement...',
                'app_debug_label' => 'App Debug',
                'app_debug_label_true' => 'True',
                'app_debug_label_false' => 'False',
                'app_log_level_label' => 'App Log Level',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'App Url',
                'app_url_placeholder' => 'App Url',
                'db_connection_label' => 'Database Connection',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Database Host',
                'db_host_placeholder' => 'Database Host',
                'db_port_label' => 'Database Port',
                'db_port_placeholder' => 'Database Port',
                'db_name_label' => 'Database Name',
                'db_name_placeholder' => 'Database Name',
                'db_username_label' => 'Database User Name',
                'db_username_placeholder' => 'Database User Name',
                'db_password_label' => 'Database Password',
                'db_password_placeholder' => 'Database Password',

                'app_tabs' => [
                    'more_info' => 'Plus d\'informations',
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
                    'setup_database' => 'Configuration de la base de donnée',
                    'setup_application' => 'Configuration de l\'application',
                    'install' => 'Installer',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement | Editeur Classique',
            'title' => 'Éditeur de texte classique',
            'save' => 'Enregistrer .env',
            'back' => 'Utiliser le formulaire',
            'install' => 'Enregistrer et installer',
        ],
        'success' => 'Vos paramètres de fichier .env ont été enregistrés.',
        'errors' => 'Impossible de sauvegarder le fichier .env, veuillez le créer manuellement.',
    ],

    'install' => 'Installer',

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Terminé',
        'templateTitle' => 'Installation Terminé',
        'finished' => 'L’application a été installée avec succès.',
        'migration' => 'Migration &amp; Seed Console Output:',
        'console' => 'Application Console Output:',
        'log' => 'Installation Log Entry:',
        'env' => 'Final .env File:',
        'exit' => 'Cliquez ici pour quitter',
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
        'title' => 'Mise à jour de Laravel',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Bienvenue dans l\'updateur...',
            'message' => 'Bienvenue dans le programme de mise à jour.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Aperçu',
            'message' => 'Il y a 1 mise à jour.|Il y a :number mises à jour.',
            'install_updates' => 'Installer la mise à jour',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Terminé',
            'finished' => 'L’application a été mise à jour avec succès.',
            'exit' => 'Cliquez ici pour quitter',
        ],

        'log' => [
            'success_message' => 'L\'installateur Laravel a été mis à jour avec succès le ',
        ],
    ],
];
