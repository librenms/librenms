#!/usr/bin/php
<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Util\Debug;

$options = getopt('u:rldvh');
if (isset($options['h']) || (! isset($options['l']) && ! isset($options['u']))) {
    echo ' -u <username>  (Required) username to test
] -d             Enable debug output
 -v             Enable verbose debug output
 -h             Display this help message
';
    exit;
}

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (isset($options['d'])) {
    Debug::set();
}

if (isset($options['v'])) {
    // Enable debug mode for auth methods that have it
    LibrenmsConfig::set('auth_ad_debug', 1);
    LibrenmsConfig::set('auth_ldap_debug', 1);
}

echo 'Authentication Method: ' . LibrenmsConfig::get('auth_mechanism') . PHP_EOL;

// if ldap like, check selinux
if (LibrenmsConfig::get('auth_mechanism') == 'ldap' || LibrenmsConfig::get('auth_mechanism') == 'active_directory') {
    $enforce = shell_exec('getenforce 2>/dev/null');
    if (Str::contains($enforce, 'Enforcing')) {
        // has selinux
        $output = shell_exec('getsebool httpd_can_connect_ldap');
        if ($output != "httpd_can_connect_ldap --> on\n") {
            print_error('You need to run: setsebool -P httpd_can_connect_ldap=1');
            exit;
        }
    }
}
if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
    $username = $options['u'] ?? '';
    $question = new \Symfony\Component\Console\Question\Question('Password: ')->setHidden(true);
    $password = new \Symfony\Component\Console\Helper\QuestionHelper()->ask(
        new \Symfony\Component\Console\Input\ArgvInput(),
        new \Symfony\Component\Console\Output\ConsoleOutput(),
        $question
    );
    $credentials = app(\App\Ldap\ActiveDirectoryConfig::class)->credentials(compact('username', 'password'));
    $success = \Illuminate\Support\Facades\Auth::once($credentials);
    echo $success ? "AUTH SUCCESS\n" : "AUTH FAILURE\n";
    exit($success ? 0 : 1);
}

try {
    $authorizer = LegacyAuth::get();

    // ldap based auth we should bind before using, otherwise searches may fail due to anonymous bind
    if (method_exists($authorizer, 'bind')) {
        $authorizer->bind([]);
    }

    $test_username = $options['u'];
    $auth = false;

    echo 'Password: ';
    shell_exec('stty -echo');
    $test_password = trim(fgets(STDIN));
    shell_exec('stty echo');
    echo PHP_EOL;

    echo "Authenticate user $test_username: \n";
    $auth = $authorizer->authenticate(['username' => $test_username, 'password' => $test_password]);
    unset($test_password);

    if ($auth) {
        print_message("AUTH SUCCESS\n");
    } else {
        if (isset($ldap_connection)) {
            echo ldap_error($ldap_connection) . PHP_EOL;
        }
        print_error('AUTH FAILURE');
    }

    if ($auth) {
        $user_id = $authorizer->getUserid($test_username);

        echo "User ($user_id):\n";
        if (method_exists($authorizer, 'getUser')) {
            $user = $authorizer->getUser($user_id);

            unset($user['password']);
            unset($user['remember_token']);
            foreach ($user as $property => $value) {
                echo "  $property => $value\n";
            }
        }

        if (method_exists($authorizer, 'getGroupList')) {
            echo 'Groups: ' . implode('; ', $authorizer->getGroupList()) . PHP_EOL;
        }
        if (method_exists($authorizer, 'getRoles')) {
            echo 'Roles: ' . implode('; ', $authorizer->getRoles($test_username)) . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e::class . " thrown!\n";
    echo $e->getMessage() . PHP_EOL;
}
