<?php

return [
    'config:get' => [
        'description' => 'Get configuration value',
        'arguments' => [
            'setting' => 'setting to get value of in dot notation (example: snmp.community.0)',
        ],
        'options' => [
            'dump' => 'Output the entire config as json',
        ],
    ],
    'config:set' => [
        'description' => 'Set configuration value (or unset)',
        'arguments' => [
            'setting' => 'setting to set in dot notation (example: snmp.community.0) To append to an array suffix with .+',
            'value' => 'value to set, unset setting if this is omitted',
        ],
        'options' => [
            'ignore-checks' => 'Ignore all safety checks',
        ],
        'confirm' => 'Reset :setting to the default?',
        'forget_from' => 'Forget :path from :parent?',
        'errors' => [
            'append' => 'Cannot append to non-array setting',
            'failed' => 'Failed to set :setting',
            'invalid' => 'This is not a valid setting. Please check your input',
            'invalid_os' => 'Specified OS (:os) does not exist',
            'nodb' => 'Database is not connected',
            'no-validation' => 'Cannot set :setting, it is missing validation definition.',
        ],
    ],
    'db:seed' => [
        'existing_config' => 'Database contains existing settings. Continue?',
    ],
    'dev:check' => [
        'description' => 'LibreNMS code checks. Running with no options runs all checks',
        'arguments' => [
            'check' => 'Run the specified check :checks',
        ],
        'options' => [
            'commands' => 'Print commands that would be run only, no checks',
            'db' => 'Run unit tests that require a database connection',
            'fail-fast' => 'Stop checks when any failure is encountered',
            'full' => 'Run full checks ignoring changed file filtering',
            'module' => 'Specific Module to run tests on. Implies unit, --db, --snmpsim',
            'os' => 'Specific OS to run tests on. Implies unit, --db, --snmpsim',
            'quiet' => 'Hide output unless there is an error',
            'snmpsim' => 'Use snmpsim for unit tests',
        ],
    ],
    'dev:simulate' => [
        'description' => 'Simulate devices using test data',
        'arguments' => [
            'file' => 'The file name (only base name) of the snmprec file to update or add to LibreNMS. If file not specified, no device will be added or updated.',
        ],
        'options' => [
            'multiple' => 'Use community name for hostname instead of snmpsim',
            'remove' => 'Remove the device after stopping',
        ],
        'added' => 'Device :hostname (:id) added',
        'exit' => 'Ctrl-C to stop',
        'removed' => 'Device :id removed',
        'updated' => 'Device :hostname (:id) updated',
    ],
    'device:ping' => [
        'description' => 'Ping device and record data for response',
        'arguments' => [
            'device spec' => 'Device to ping one of: <Device ID>, <Hostname/IP>, all',
        ],
    ],
    'device:poll' => [
        'description' => 'Poll data from device(s) as defined by discovery',
        'arguments' => [
            'device spec' => 'Device spec to poll: device_id, hostname, wildcard, odd, even, all',
        ],
        'options' => [
            'modules' => 'Specify single module to be run. Comma separate modules, submodules may be added with /',
            'no-data' => 'Do not update datastores (RRD, InfluxDB, etc)',
        ],
        'errors' => [
            'db_connect' => 'Failed to connect to database. Verify database service is running and connection settings.',
            'db_auth' => 'Failed to connect to database. Verify credentials: :error',
        ],
    ],
    'key:rotate' => [
        'description' => 'Rotate APP_KEY, this decrypts all encrypted data with the given old key and stores it with the new key in APP_KEY.',
        'arguments' => [
            'old_key' => 'The old APP_KEY which is valid for encrypted data',
        ],
        'cleared-cache' => 'Config was cached, cleared cache to make sure APP_KEY is correct. Please re-run lnms key:rotate',
        'backup_keys' => 'Document BOTH keys! In case something goes wrong set the new key in .env and use the old key as an argument to this command',
        'backups' => 'This command could cause irreversible loss of data and will invalidate all browser sessions. Make sure you have backups.',
        'confirm' => 'I have backups and want to continue',
        'decrypt-failed' => 'Failed to decrypt :item, skipping',
        'failed' => 'Failed to decrypt item(s).  Set new key as APP_KEY and run this again with the old key as an argument.',
        'new_key' => 'New key: :key',
        'old_key' => 'Old key: :key',
        'save_key' => 'Save new key to .env?',
        'success' => 'Successfully rotated keys!',
        'validation-errors' => [
            'not_in' => ':attribute must not match current APP_KEY',
            'required' => 'Either old key or --generate-new-key is required.',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => 'Use one of --probes and --targets',
        'config-insufficient' => 'In order to generate a smokeping configuration, you must have set "smokeping.probes", "fping", and "fping6" set in your configuration',
        'dns-fail' => 'was not resolvable and was omitted from the configuration',
        'description' => 'Generate a configuration suitable for use with smokeping',
        'header-first' => 'This file was automatically generated by "lnms smokeping:generate',
        'header-second' => 'Local changes may be overwritten without notice or backups being taken',
        'header-third' => 'For more information see https://docs.librenms.org/Extensions/Smokeping/"',
        'no-devices' => 'No eligible devices found - devices must not be disabled.',
        'no-probes' => 'At least one probe is required.',
        'options' => [
            'probes' => 'Generate probe list - used for splitting the smokeping configuration into multiple files. Conflicts with "--targets"',
            'targets' => 'Generate the target list - used for splitting the smokeping configuration into multiple files. Conflicts with "--probes"',
            'no-header' => 'Don\'t add the boilerplate comment to the start of the generated file',
            'no-dns' => 'Skip DNS lookups',
            'single-process' => 'Only use a single process for smokeping',
            'compat' => '[deprecated] Mimic the behaviour of gen_smokeping.php',
        ],
    ],
    'snmp:fetch' => [
        'description' => 'Run snmp query against a device',
        'arguments' => [
            'device spec' => 'Device to query: device_id, hostname/ip, hostname regex, or all',
            'oid' => 'SNMP OID to fetch.  Should be either MIB::oid or a numeric oid',
        ],
        'failed' => 'SNMP command failed!',
        'oid' => 'OID',
        'options' => [
            'type' => 'The type of snmp query to perform :types',
            'output' => 'Specify the output format :formats',
            'numeric' => 'Numeric OIDs',
        ],
        'not_found' => 'Device not found',
        'value' => 'Value',
    ],
    'translation:generate' => [
        'description' => 'Generate updated json language files for use in the web frontend',
    ],
    'user:add' => [
        'description' => 'Add a local user, you can only log in with this user if auth is set to mysql',
        'arguments' => [
            'username' => 'The username the user will log in with',
        ],
        'options' => [
            'descr' => 'User description',
            'email' => 'Email to use for the user',
            'password' => 'Password for the user, if not given, you will be prompted',
            'full-name' => 'Full name for the user',
            'role' => 'Set the user to the desired role :roles',
        ],
        'password-request' => "Please enter the user's password",
        'success' => 'Successfully added user: :username',
        'wrong-auth' => 'Warning! You will not be able to log in with this user because you are not using MySQL auth',
    ],
];
