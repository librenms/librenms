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

    // Librenms specific
    'alpha_space' => 'The :attribute may only contain letters, numbers, underscores and spaces.',
    'ip_or_hostname' => 'The :attribute must a valid IP address/network or hostname.',
    'is_regex' => 'The :attribute is not a valid regular expression',
    'keys_in' => 'The :attribute contains invalid keys: :extra. Valid keys: :values',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'results' => [
        'autofix' => 'Attempt to automatically fix',
        'fix' => 'Fix',
        'fixed' => 'Fix has completed, refresh to re-run validations.',
        'fetch_failed' => 'Failed to fetch validation results',
        'backend_failed' => 'Failed to load data from backend, check console for error.',
        'invalid_fixer' => 'Invalid Fixer',
        'show_all' => 'Show all',
        'show_less' => 'Show less',
        'validate' => 'Validate',
        'validating' => 'Validating',
    ],
    'validations' => [
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'The rrdtool version you have specified is newer than what is installed. Config: :config_version Installed :installed_version',
                'fix' => 'Either comment out or delete $config[\'rrdtool_version\'] = \':version\'; from your config.php file',
                'ok' => 'rrdtool version ok',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => ':socket does not appear to exist, rrdcached connectivity test failed',
                'fail_port' => 'Cannot connect to rrdcached server on port :port',
                'ok' => 'Connected to rrdcached',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'Your RRD directory is owned by root, please consider changing over to user a non-root user',
                'fail_mode' => 'Your RRD directory is not set to 0775',
                'ok' => 'rrd_dir is writable',
            ],
        ],
        'database' => [
            'CheckDatabaseConnected' => [
                'fail' => 'Unable to connect to database',
                'fail_connect' => 'Unable to connect to database. Confirm database server is running and connection info is correct.  Check DB_HOST, DB_PORT, and DB_NAME in environment or in :env_file',
                'fail_access' => 'Database connected, but user does not have permission to access database. Run SQL query to grant permissions (change localhost to local hostname if datababase is remote)',
                'fail_auth' => 'Database credentials incorrect. Double check credentials in DB_USERNAME and DB_PASSWORD either in environment or in :env_file',
                'ok' => 'Database Connected',
            ],
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'You have lower_case_table_names set to 1 or true in mysql config.',
                'fix' => 'Set lower_case_table_names=0 in your mysql config file in the [mysqld] section.',
                'ok' => 'lower_case_table_names is enabled',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => ':server version :min is the minimum supported version as of :date.',
                'fix' => 'Update :server to a supported version, :suggested suggested.',
                'ok' => 'SQL Server meets minimum requirements',
            ],
            'CheckMysqlEngine' => [
                'fail' => 'Some tables are not using the recommended InnoDB engine, this may cause you issues.',
                'tables' => 'Tables',
                'ok' => 'MySQL engine is optimal',
            ],
            'CheckSqlServerTime' => [
                'fail' => "Time between this server and the mysql database is off\n Mysql time :mysql_time\n PHP time :php_time",
                'ok' => 'MySQL and PHP time match',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => 'Your database is out of date!',
                'fail_legacy_outdated' => 'Your database schema (:current) is older than the latest (:latest).',
                'fix_legacy_outdated' => 'Manually run ./daily.sh, and check for any errors.',
                'warn_extra_migrations' => 'Your database schema has extra migrations (:migrations). If you just switched to the stable release from the daily release, your database is in between releases and this will be resolved with the next release.',
                'warn_legacy_newer' => 'Your database schema (:current) is newer than expected (:latest). If you just switched to the stable release from the daily release, your database is in between releases and this will be resolved with the next release.',
                'ok' => 'Database Schema is current',
            ],
            'CheckSchemaCollation' => [
                'ok' => 'Database and column collations are correct',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => 'Distributed Polling setting is enabled globally',
                'not_enabled' => 'You have not enabled distributed_poller',
                'not_enabled_globally' => 'You have not enabled distributed_poller globally',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'You have not configured distributed_poller_memcached_host',
                'not_configured_port' => 'You have not configured distributed_poller_memcached_port',
                'could_not_connect' => 'Could not connect to memcached server',
                'ok' => 'Connection to memcached is ok',
            ],
            'CheckRrdcached' => [
                'fail' => 'You have not enabled rrdcached',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => 'Poller is not running.  No poller has run within the last :interval seconds.',
                'both_fail' => 'Both Dispatcher Service and Python Wrapper were active recently, this could cause double polling',
                'ok' => 'Active pollers found',
            ],
            'CheckDispatcherService' => [
                'fail' => 'No active dispatcher nodes found',
                'ok' => 'Dispatcher Service is enabled',
                'nodes_down' => 'Some dispatcher nodes have not checked in recently',
                'not_detected' => 'Dispatcher Service not detected',
                'warn' => 'Dispatcher Service has been used, but not recently',
            ],
            'CheckLocking' => [
                'fail' => 'Locking server issue: :message',
                'ok' => 'Locks are functional',
            ],
            'CheckPythonWrapper' => [
                'fail' => 'No active python wrapper pollers found',
                'no_pollers' => 'No python wrapper pollers found',
                'cron_unread' => 'Could not read cron files',
                'ok' => 'Python poller wrapper is polling',
                'nodes_down' => 'Some poller nodes have not checked in recently',
                'not_detected' => 'Python wrapper cron entry is not present',
            ],
            'CheckRedis' => [
                'bad_driver' => 'Using :driver for locking, you should set CACHE_STORE=redis',
                'ok' => 'Redis is functional',
                'unavailable' => 'Redis is unavailable',
            ],
        ],
    ],
];
