<?php

return [
    'config:get' => [
        'description' => 'Get configuration value',
        'arguments' => [
            'setting' => 'setting to get value of in dot notation (example: snmp.community.0)',
        ],
        'options' => [
            'json' => 'Output setting or entire config as json',
        ],
    ],
    'config:set' => [
        'description' => 'Set configuration value (or unset)',
        'arguments' => [
            'setting' => 'setting to set in dot notation (example: snmp.community.0)',
            'value' => 'value to set, unset setting if this is omitted',
        ],
        'options' => [
            'ignore-checks' => 'Ignore all safety checks'
        ],
        'confirm' => 'Reset :setting to the default?',
        'errors' => [
            'failed' => 'Failed to set :setting',
            'invalid' => 'This is not a valid setting. Please check your spelling',
            'nodb' => 'Database is not connected',
            'no-validation' => 'Cannot set :setting, it is missing validation definition.',
        ]
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
        ]
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
    'translation:generate' => [
        'description' => 'Generate updated json language files for use in the web frontend',
    ]
];
