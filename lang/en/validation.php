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

    'accepted' => 'The :attribute field must be accepted.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'The :attribute field must be a date after :date.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute field must be a date before :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute field must have between :min and :max items.',
        'file' => 'The :attribute field must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute field must be between :min and :max.',
        'string' => 'The :attribute field must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute field must be a valid date.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field must exist in :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

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
            'rule-name' => 'custom-message',
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
                'bad_driver' => 'Using :driver for locking, you should set CACHE_DRIVER=redis',
                'ok' => 'Redis is functional',
                'unavailable' => 'Redis is unavailable',
            ],
        ],
    ],
];
