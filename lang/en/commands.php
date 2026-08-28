<?php

return [
    'errors' => [
        'db_connect' => 'Failed to connect to the database. Check that the database service runs and that the connection settings are correct.',
        'db_auth' => 'Failed to connect to the database. Check the credentials: :error',
        'no_devices' => 'No devices match the given device specification',
        'no_new_devices' => 'No new devices',
    ],
    'api:token-create' => [
        'description' => 'Create a new API token for a user',
        'arguments' => [
            'username' => 'User to create the token for',
        ],
        'options' => [
            'name' => 'Name for the token',
        ],
        'created' => 'Token created successfully.',
        'save-warning' => 'Save this token. It is not shown again.',
        'user-not-found' => 'User \':username\' not found.',
    ],
    'api:token-list' => [
        'description' => 'List API tokens for a user',
        'arguments' => [
            'username' => 'User to list tokens for',
        ],
        'no-tokens' => 'No tokens found for user \':username\'.',
        'user-not-found' => 'User \':username\' not found.',
    ],
    'api:token-revoke' => [
        'description' => 'Revoke an API token for a user',
        'arguments' => [
            'username' => 'User the token belongs to',
            'token-id' => 'ID of the token to revoke (see api:token-list)',
        ],
        'revoked' => 'Token \':name\' (ID: :id) revoked.',
        'token-not-found' => 'Token ID :id not found for user \':username\'.',
        'user-not-found' => 'User \':username\' not found.',
    ],
    'config:clear' => [
        'description' => 'Clear the config cache. The current config then includes all changes since the last full config load.',
    ],
    'config:get' => [
        'description' => 'Get configuration value',
        'arguments' => [
            'setting' => 'Setting to get the value of, in dot notation (example: snmp.community.0)',
        ],
        'options' => [
            'dump' => 'Output the entire config as json',
        ],
    ],
    'config:list' => [
        'description' => 'List and search configuration settings',
        'arguments' => [
            'search' => 'Search for a setting, matching config name or description',
        ],
        'not_found' => 'No settings found matching \':search\'',
    ],
    'config:set' => [
        'description' => 'Set configuration value (or unset)',
        'arguments' => [
            'setting' => 'Setting to set, in dot notation (example: snmp.community.0). To append to an array, add the suffix .+',
            'value' => 'Value to set. If you omit this, the setting is unset.',
        ],
        'options' => [
            'ignore-checks' => 'Ignore all safety checks',
        ],
        'confirm' => 'Reset :setting to the default?',
        'forget_from' => 'Forget :path from :parent?',
        'errors' => [
            'append' => 'Cannot append to non-array setting',
            'failed' => 'Failed to set :setting',
            'invalid' => 'This is not a valid setting. Check your input.',
            'invalid_os' => 'Specified OS (:os) does not exist',
            'nodb' => 'Database is not connected',
            'no-validation' => 'Cannot set :setting, it is missing validation definition.',
        ],
    ],
    'db:seed' => [
        'existing_config' => 'Database contains existing settings. Continue?',
    ],
    'dev:check' => [
        'description' => 'LibreNMS code checks. With no options, this command runs all checks',
        'arguments' => [
            'check' => 'Run the specified check :checks',
        ],
        'options' => [
            'commands' => 'Print the commands only, do not run the checks',
            'db' => 'Run unit tests that require a database connection',
            'fail-fast' => 'Stop the checks at the first failure',
            'full' => 'Run full checks and ignore the changed file filter',
            'module' => 'Specific Module to run tests on. Implies unit, --db, --snmpsim',
            'os' => 'Specific OS to run tests on. Can be a regular expression or a comma separated list. Implies unit, --db, --snmpsim',
            'os-modules-only' => 'Skip the OS detection test when you specify a specific OS. This speeds up tests for non-detection changes.',
            'quiet' => 'Hide output unless there is an error',
            'snmpsim' => 'Use snmpsim for unit tests',
        ],
    ],
    'dev:collect-snmprec' => [
        'description' => 'Collect SNMP data from a device for snmpsim test files',
        'help' => "Collect the OIDs used by discovery and polling into an snmprec fixture.\n\n" .
            "Example:\n  lnms dev:collect-snmprec 123 --variant=crs317 --modules=ports,sensors\n\n" .
            'Use -v to show captured OIDs, -vv for LibreNMS debug output, or -vvv for full verbose debug and SNMP output.',
        'arguments' => [
            'device' => 'ID, IP, or hostname of the device to collect data from',
        ],
        'options' => [
            'variant' => 'Required fixture variant, usually the device model; use an empty value to explicitly select the base fixture',
            'modules' => 'Comma-delimited discovery/poller modules to collect data for',
            'prefer-collected' => 'Use newly collected values when an OID already exists (other existing OIDs are preserved)',
            'os' => 'Name of the OS to save test data for (only used if device is generic)',
            'output' => 'Write to this snmprec file instead of the standard fixture path',
            'full' => 'Walk the whole device instead of running discovery and polling modules',
        ],
        'device_not_found' => 'Device \':device\' not found.',
        'variant_required' => 'The --variant (-r) option is required to avoid accidentally updating the base fixture; use --variant= to select it explicitly.',
        'variant_underscore' => 'Variant name cannot contain an underscore (_).',
        'variant_single' => 'Only one variant can be collected at a time.',
        'os_required' => 'OS (-o, --os) is required because device is generic.',
        'capturing_data' => 'Capturing SNMP data...',
        'saved_snmprec' => 'Saved snmprec data :file',
        'no_data' => 'No data for :file',
        'verify_private_data' => 'Before you share these files, check that they contain no private data.',
    ],
    'dev:generate-test-data' => [
        'description' => 'Generate JSON test data from snmpsim recordings',
        'help' => "Regenerate existing JSON fixtures, or explicitly recreate fixtures with --variant.\n\n" .
            "Examples:\n  lnms dev:generate-test-data routeros\n  lnms dev:generate-test-data all\n  lnms dev:generate-test-data routeros --variant=crs317,wifi --modules=ports,sensors\n\n" .
            'Use -v to show discovery and poller output, -vv for LibreNMS debug output, or -vvv for full verbose debug and SNMP output.',
        'arguments' => [
            'os' => 'Process existing JSON fixtures for this OS, including their variants, or specify "all" for all OS fixtures',
        ],
        'options' => [
            'variant' => 'Comma-delimited OS variants to process or recreate (requires an OS; use an empty value for the base fixture)',
            'modules' => 'Comma-delimited modules to regenerate (default: existing fixture modules, or configured defaults with --variant)',
            'output' => 'Write one fixture to this file, or use - for standard output',
        ],
        'scope_required' => 'Specify an OS (or all).',
        'variant_requires_os' => '--variant requires an OS.',
        'invalid_module' => 'Invalid module name: :module',
        'no_fixtures' => 'No matching JSON test fixtures found.',
        'no_fixtures_for_os' => 'No matching JSON test fixtures found for OS ":os".',
        'fixture_selection_note' => 'OS-wide selection is based on existing tests/data/*.json files so detection-only snmprec files are not included.',
        'recreate_hint' => 'To recreate a deleted fixture, specify its variant explicitly with --variant (use --variant= for the base OS fixture).',
        'output_single' => '--output can only be used with one OS/variant combination.',
        'combinations_found' => 'Multiple combinations (:count) found.',
        'labels' => [
            'os' => 'OS: :os',
            'variant' => 'Variant: :variant',
            'base' => '(base)',
            'modules' => 'Modules: :modules',
            'configured_defaults' => 'configured defaults',
        ],
        'progress' => [
            'generating' => 'Generating test data',
            'generated' => 'Generated test data',
            'fixtures' => '{1} :count fixture|[2,*] :count fixtures',
            'discovering_module' => ':fixture: discovering :module',
            'discovered_module' => ':fixture: discovered :module',
            'polling_module' => ':fixture: polling :module',
            'polled_module' => ':fixture: polled :module',
            'discovery_complete' => ':fixture: discovery complete',
            'polling_complete' => ':fixture: polling complete',
        ],
        'saved_to' => 'Saved to :file',
        'generated_count' => '{1} Generated :count fixture.|[2,*] Generated :count fixtures.',
        'ready' => 'Ready for testing!',
        'waiting_for_snmpsim' => 'Waiting for snmpsim to initialize...',
        'snmpsim_failed' => "Failed to start snmpsim. Make sure it is installed and working, and that the snmprec files are valid.\n:error",
    ],
    'dev:simulate' => [
        'description' => 'Simulate devices using test data',
        'arguments' => [
            'file' => 'The file name (base name only) of the snmprec file to update or add to LibreNMS. If you do not specify a file, no device is added or updated.',
        ],
        'options' => [
            'multiple' => 'Use community name for hostname instead of snmpsim',
            'remove' => 'Remove the device after stopping',
        ],
        'added' => 'Device :hostname (:id) added',
        'exit' => 'Ctrl-C to stop',
        'removed' => 'Device :id removed',
        'updated' => 'Device :hostname (:id) updated',
        'setup' => 'Setting up snmpsim venv in :dir',
    ],
    'device:add' => [
        'description' => 'Add a new device',
        'arguments' => [
            'device spec' => 'Hostname or IP to add',
        ],
        'options' => [
            'v1' => 'Use SNMP v1',
            'v2c' => 'Use SNMP v2c',
            'v3' => 'Use SNMP v3',
            'display-name' => "A string to display as the name of this device. The default is the hostname.\nThis can be a simple template with these replacements: {{ \$hostname }}, {{ \$sysName }}, {{ \$sysName_fallback }}, {{ \$ip }}",
            'force' => 'Add the device and do not make any safety checks',
            'group' => 'Poller group (for distributed polling)',
            'ping-fallback' => 'Add the device as ping only if it does not respond to SNMP',
            'port-association-mode' => 'Sets how ports are mapped. ifName is suggested for Linux/Unix',
            'community' => 'SNMP v1 or v2 community',
            'transport' => 'Transport to connect to the device',
            'port' => 'SNMP transport port',
            'security-name' => 'SNMPv3 security username',
            'auth-password' => 'SNMPv3 authentication password',
            'auth-protocol' => 'SNMPv3 authentication protocol',
            'privacy-protocol' => 'SNMPv3 privacy protocol',
            'privacy-password' => 'SNMPv3 privacy password',
            'ping-only' => 'Add a ping only device',
            'os' => 'Ping only: specify OS',
            'hardware' => 'Ping only: specify hardware',
            'sysName' => 'Ping only: specify sysName',
        ],
        'validation-errors' => [
            'port.between' => 'Port must be 1-65535',
            'poller-group.in' => 'The given poller-group does not exist',
        ],
        'messages' => [
            'save_failed' => 'Failed to save device :hostname',
            'try_force' => 'Use the --force option to skip the safety checks',
            'added' => 'Added device :hostname (:device_id)',
        ],
    ],
    'device:discover' => [
        'description' => 'Discover information about existing devices. This defines what is polled.',
        'arguments' => [
            'device spec' => 'Device spec to discover: device_id, hostname, wildcard (*), odd, even, all',
        ],
        'options' => [
            'modules' => 'Specify the module(s) to run. To add a submodule, use /. Multiple values are allowed.',
            'os' => 'Discover devices only with specified operating system',
            'type' => 'Discover devices only with specified type',
        ],
        'errors' => [
            'none_up' => 'Device was down, unable to discover.|All devices were down, unable to discover.',
            'none_actioned' => 'No devices were discovered.',
        ],
        'actioned' => 'Discovered :count devices in :time',
        'starting' => 'Starting discovery:',
    ],
    'device:ping' => [
        'description' => 'Ping device and record data for response',
        'arguments' => [
            'device spec' => 'Device to ping one of: <Device ID>, <Hostname/IP>, all, fast ("fast" will ping all devices and update graphs and status)',
        ],
        'options' => [
            'groups' => 'Group ID(s) to ping. Specify multiple times for multiple groups. (only valid with fast)',
        ],
        'errors' => [
            'groups_without_fast' => 'The --groups (-g) option is only supported with "fast" device spec.',
        ],
    ],
    'device:poll' => [
        'description' => 'Poll data from device(s) as defined by discovery',
        'arguments' => [
            'device spec' => 'Device spec to poll: device_id, hostname, wildcard (*), odd, even, all',
        ],
        'options' => [
            'modules' => 'Specify a single module to run. Separate modules with a comma. To add a submodule, use /',
            'no-data' => 'Do not update datastores (RRD, InfluxDB, etc)',
            'os' => 'Poll devices only with specified operating system',
            'type' => 'Poll devices only with specified type',
        ],
        'errors' => [
            'none_up' => 'Device was down, unable to poll.|All devices were down, unable to poll.',
            'none_actioned' => 'No devices were polled.',
        ],
        'actioned' => 'Polled :count devices in :time',
        'starting' => 'Starting polling run:',
    ],
    'device:remove' => [
        'doesnt_exists' => 'No such device: :device',
    ],
    'key:rotate' => [
        'description' => 'Rotate APP_KEY. This command decrypts all encrypted data with the old key. It then stores the data with the new key in APP_KEY.',
        'arguments' => [
            'old_key' => 'The old APP_KEY which is valid for encrypted data',
        ],
        'options' => [
            'generate-new-key' => 'If the new key is not in .env, use the APP_KEY from .env to decrypt the data. The command then generates a new key and sets it in .env.',
            'forgot-key' => 'If you do not have the old key, you must delete all encrypted data. Otherwise, you cannot use some LibreNMS features.',
        ],
        'destroy' => 'Destroy all encrypted configuration data?',
        'destroy_confirm' => 'Destroy all encrypted data only if you cannot find the old APP_KEY.',
        'cleared-cache' => 'The config was cached. The cache was cleared to make sure that APP_KEY is correct. Run lnms key:rotate again.',
        'backup_keys' => 'Document BOTH keys. If something goes wrong, set the new key in .env. Then use the old key as an argument to this command.',
        'backup_key' => 'Document this key! This key is required to access encrypted data',
        'backups' => 'This command can cause irreversible loss of data. It also invalidates all browser sessions. Make sure that you have backups.',
        'confirm' => 'I have backups and want to continue',
        'decrypt-failed' => 'Failed to decrypt :item. Skipped it.',
        'failed' => 'Failed to decrypt item(s).  Set new key as APP_KEY and run this again with the old key as an argument.',
        'current_key' => 'Current APP_KEY: :key',
        'new_key' => 'New APP_KEY: :key',
        'old_key' => 'Old APP_KEY: :key',
        'save_key' => 'Save new key to .env?',
        'success' => 'Successfully rotated keys!',
        'validation-errors' => [
            'not_in' => ':attribute must not match current APP_KEY',
            'required' => 'Either old key or --generate-new-key is required.',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => 'Selected :option is invalid. It must be one of: :values',
        ],
    ],
    'maintenance:cleanup-database' => [
        'description' => 'Database cleanup of orphaned items.',
    ],
    'maintenance:cleanup-networks' => [
        'delete' => 'Deleting :count unused networks',
    ],
    'maintenance:fetch-ouis' => [
        'description' => 'Fetch MAC OUIs and cache them to display vendor names for MAC addresses',
        'options' => [
            'force' => 'Ignore any settings or locks that prevent the command from being run',
            'wait' => 'Wait a random amount of time. The scheduler uses this to prevent server strain.',
        ],
        'disabled' => 'Mac OUI integration disabled (:setting)',
        'enable_question' => 'Enable Mac OUI integration and scheduled fetching?',
        'recently_fetched' => 'The MAC OUI database was fetched recently. Skipped the update.',
        'waiting' => 'The MAC OUI update starts in :minutes minute|The MAC OUI update starts in :minutes minutes',
        'starting' => 'Storing Mac OUI in the database',
        'downloading' => 'Downloading',
        'processing' => 'Processing CSV',
        'saving' => 'Saving results',
        'success' => 'Successfully updated OUI/Vendor mappings. :count modified OUI|Successfully updated. :count modified OUIs',
        'error' => 'Error processing Mac OUI:',
        'vendor_update' => 'Adding OUI :oui for :vendor',
    ],
    'maintenance:rrd-step' => [
        'description' => 'Convert RRD files to match configured step and heartbeat',
        'arguments' => [
            'device' => 'Hostname, device id, or all',
        ],
        'options' => [
            'confirm' => 'Confirm that you backed up your rrd files.',
        ],
        'errors' => [
            'invalid' => 'Invalid hostname or device id specified',
        ],
        'confirm_backup' => 'Before you continue, confirm that you backed up your rrd files.',
        'mismatched_heartbeat' => ':file: Mismatched heartbeat. :ds != :hb',
        'skipping' => 'Skipped :file. The step is already :step.',
        'converting' => 'Converting :file:',
        'summary' => 'Converted: :converted  Failed: :failed  Skipped: :skipped',
    ],
    'maintenance:cleanup-syslog' => [
        'description' => 'Cleanup syslog entries older than a specified number of days',
        'arguments' => [
            'days' => 'Number of days to keep syslog entries (default: syslog_purge configured value)',
        ],
        'bad_days_input' => 'Days must be numeric',
        'bad_days_setting' => 'Syslog cleanup is disabled because the syslog_purge setting is invalid',
        'delete' => 'Cleared syslog entries older than :days days (:count rows)',
        'disabled' => 'Syslog cleanup is disabled, because days <= 0',
    ],
    'maintenance:discover-ssl-certificates' => [
        'description' => 'Discover SSL certificates on devices (HTTPS port 443)',
        'options' => [
            'device' => 'Device spec to discover: device_id, hostname, or all',
        ],
        'no_devices' => 'No devices found',
        'summary' => 'Created: :created, Updated: :updated, Failed: :failed',
    ],
    'maintenance:refresh-ssl-certificates' => [
        'description' => 'Refresh certificate data for stored SSL certificates',
        'options' => [
            'id' => 'Certificate ID to refresh (omit to refresh all enabled)',
        ],
        'none' => 'No enabled certificates to refresh',
        'summary' => 'Refreshed: :refreshed, Failed: :failed',
    ],
    'plugin:disable' => [
        'description' => 'Disable all plugins with the given name',
        'arguments' => [
            'plugin' => 'The name of the plugin to disable, or "all" to disable all plugins',
        ],
        'already_disabled' => 'Plugin already disabled',
        'disabled' => ':count plugin disabled|:count plugins disabled',
        'failed' => 'Failed to disable plugin(s)',
    ],
    'plugin:enable' => [
        'description' => 'Enable the newest plugin with the given name',
        'arguments' => [
            'plugin' => 'The name of the plugin to enable, or "all" to enable all plugins',
        ],
        'already_enabled' => 'Plugin already enabled',
        'enabled' => ':count plugin enabled|:count plugins enabled',
        'failed' => 'Failed to enable plugin(s)',
    ],
    'port:tune' => [
        'description' => 'Tune port rrd files to limit the max transfer rate based on ifSpeed',
        'arguments' => [
            'device spec' => 'Device spec to tune: device_id, hostname, wildcard (*), odd, even, all',
            'ifname' => 'Port ifName to match. Use all or * for a wildcard',
        ],
        'device' => 'Device :device:',
        'port' => 'Tuning port :port',
    ],
    'report:devices' => [
        'description' => 'Print out data from devices',
        'columns' => 'Database columns:',
        'synthetic' => 'Additional fields:',
        'counts' => 'Relationship counts:',
        'arguments' => [
            'device spec' => 'Device spec to poll: device_id, hostname, wildcard (*), odd, even, all',
        ],
        'options' => [
            'list-fields' => 'Print out a list of valid fields',
            'fields' => 'A comma separated list of fields to display. Valid options: device column names from the database, relationship counts (ports_count), and displayName. Not used for json output.',
            'output' => 'Output format to display the data :types',
            'no-header' => 'Do not add the header',
            'relationships' => 'A comma separated list of relationships to include. Only used for json output.',
            'list-relationships' => 'Print out a list/description of relationships',
            'all-relationships' => 'Include all relationships. -r, --relationships takes precedence.',
            'devices-as-array' => 'Return the output as a JSON array instead of a JSON entry per device per line',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => 'Use one of --probes and --targets',
        'config-insufficient' => 'To generate a smokeping configuration, you must set "smokeping.probes", "fping", and "fping6" in your configuration',
        'dns-fail' => 'did not resolve and was omitted from the configuration',
        'description' => 'Generate a configuration suitable for use with smokeping',
        'header-first' => 'This file was automatically generated by "lnms smokeping:generate',
        'header-second' => 'Local changes can be overwritten without notice and without a backup',
        'header-third' => 'For more information see https://docs.librenms.org/Extensions/Smokeping/"',
        'no-devices' => 'No eligible devices found. Devices must not be disabled.',
        'no-probes' => 'At least one probe is required.',
        'options' => [
            'probes' => 'Generate probe list - used for splitting the smokeping configuration into multiple files. Conflicts with "--targets"',
            'targets' => 'Generate the target list - used for splitting the smokeping configuration into multiple files. Conflicts with "--probes"',
            'no-header' => 'Do not add the boilerplate comment to the start of the generated file',
            'no-dns' => 'Skip DNS lookups',
            'single-process' => 'Only use a single process for smokeping',
            'compat' => '[deprecated] Mimic the behavior of gen_smokeping.php',
        ],
    ],
    'snmp:fetch' => [
        'description' => 'Run snmp query against a device',
        'arguments' => [
            'device spec' => 'Device spec to poll: device_id, hostname, wildcard (*), odd, even, all',
            'oid(s)' => 'One or more SNMP OID to fetch. Each OID must be MIB::oid or a numeric OID',
        ],
        'failed' => 'SNMP command failed!',
        'numeric' => 'Numeric',
        'oid' => 'OID',
        'options' => [
            'output' => 'Specify the output format :formats',
            'numeric' => 'Numeric OIDs',
            'depth' => 'Depth to group the snmp table at. Usually the same number as the items in the index of the table',
        ],
        'not_found' => 'Device not found',
        'textual' => 'Textual',
        'value' => 'Value',
    ],
    'translation:generate' => [
        'description' => 'Generate updated json language files for use in the web frontend',
    ],
    'user:add' => [
        'description' => 'Add a local user. You can log in with this user only if auth is set to mysql.',
        'arguments' => [
            'username' => 'The username the user will log in with',
        ],
        'options' => [
            'descr' => 'User description',
            'email' => 'Email to use for the user',
            'password' => 'Password for the user. If you do not give it, the command prompts you.',
            'full-name' => 'Full name for the user',
            'role' => 'Set the user to the desired role :roles',
        ],
        'form' => [
            'username' => 'Username',
            'password' => 'Password',
            'roles' => 'Select user role(s)',
            'email' => 'Email (optional)',
            'full-name' => 'Full name (optional)',
            'descr' => 'Description (optional)',
        ],
        'success' => 'Successfully added user: :username',
        'wrong-auth' => 'Warning! You cannot log in with this user, because auth is not set to MySQL.',
    ],
];
