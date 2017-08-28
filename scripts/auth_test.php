#!/usr/bin/php
<?php

use Phpass\PasswordHash;

$options = getopt('u:rdvh');
if (isset($options['h']) || !isset($options['u'])) {
    echo ' -u <username>  (Required) username to test
 -r             Reauthenticate user, (requires previous web login with "Remember me" enabled)
 -d             Enable debug output
 -v             Enable verbose debug output
 -h             Display this help message
';
    exit;
}

$test_username = $options['u'];

if (isset($options['d'])) {
    $debug = true;
}

if (isset($options['v'])) {
    // might need more options for other auth methods
    $config['auth_ad_debug'] = 1; // active_directory
}

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

echo "Authentication Method: {$config['auth_mechanism']}\n";

// if ldap like, check selinux
if ($config['auth_mechanism'] = 'ldap' || $config['auth_mechanism'] = "active_directory") {
    $enforce = shell_exec('getenforce 2>/dev/null');
    if (str_contains($enforce, 'Enforcing')) {
        // has selinux
        $output = shell_exec('getsebool httpd_can_connect_ldap');
        if ($output != "httpd_can_connect_ldap --> on\n") {
            print_error("You need to run: setsebool -P httpd_can_connect_ldap=1");
            exit;
        }
    }
}

if (function_exists('ad_bind')) {
    if (isset($config['auth_ad_binduser']) && isset($config['auth_ad_bindpassword'])) {
        if (!ad_bind($ldap_connection, false)) {
            $ldap_error = ldap_error($ldap_connection);
            echo $ldap_error . PHP_EOL;
            if ($ldap_error == 'Invalid credentials') {
                print_error('AD bind failed for user ' . $config['auth_ad_binduser'] . '@' . $config['auth_ad_domain'] .
                    '. Check $config[\'auth_ad_binduser\'] and $config[\'auth_ad_bindpassword\'] in your config.php');
            }
        } else {
            print_message('AD bind success');
        }
    } else {
        if (!ad_bind($ldap_connection)) {
            echo ldap_error($ldap_connection) . PHP_EOL;
            print_warn("Could not anonymous bind to AD");
        } else {
            print_message('AD bind anonymous successful');
        }
    }
}

$auth = false;
if (isset($options['r'])) {
    echo "Reauthenticate Test\n";

    $session = dbFetchRow('SELECT * FROM `session` WHERE `session_username`=? ORDER BY `session_id` DESC LIMIT 1', array($test_username));
    d_echo($session);
    if (empty($session)) {
        print_error('Requires previous login with \'Remember me\' box checked on the webui');
        exit;
    }

    $hasher   = new PasswordHash(8, false);
    $token = $session['session_username'] . '|' . $hasher->HashPassword($session['session_username'] . $session['session_token']);

    $auth = reauthenticate($session['session_value'], $token);
    if ($auth) {
        print_message("Reauthentication successful.\n");
    } else {
        print_error('Reauthentication failed or is unsupported');
    }
} else {
    echo 'Password: ';
    `stty -echo`;
    $test_password = trim(fgets(STDIN));
    `stty echo`;
    echo PHP_EOL;

    echo "Authenticate user $test_username: \n";
    $auth = authenticate($test_username, $test_password);
    unset($test_password);

    if ($auth) {
        print_message("AUTH SUCCESS\n");
    } else {
        if (isset($ldap_connection)) {
            echo ldap_error($ldap_connection) . PHP_EOL;
        }
        print_error('AUTH FAILURE');
    }
}

if ($auth) {
    $user_id = get_userid($test_username);

    echo "User:\n";
    if (function_exists('get_user')) {
        $user = get_user($user_id);

        unset($user['password']);
        unset($user['remember_token']);
        foreach ($user as $property => $value) {
            echo "  $property => $value\n";
        }
    }

    if (function_exists('get_group_list')) {
        echo 'Groups: ' . implode('; ', get_group_list()) . PHP_EOL;
    }
}
