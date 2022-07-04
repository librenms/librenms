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

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'alpha_space' => 'The :attribute may only contain letters, numbers, underscores and spaces.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ip_or_hostname' => 'The :attribute must a valid IP address/network or hostname.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'is_regex' => 'The :attribute is not a valid regular expression',
    'json' => 'The :attribute must be a valid JSON string.',
    'keys_in' => 'The :attribute contains invalid keys: :extra. Valid keys: :values',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

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
                'ok' => 'MySQl and PHP time match',
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

            ],
        ],
    ],
];
