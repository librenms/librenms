<?php

use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Exceptions\AuthenticationException;

ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1); // php >= 5.5.2
ini_set('session.use_trans_sid', 0);   // insecure feature, be sure it is disabled

// Pre-flight checks
if (!is_dir($config['rrd_dir'])) {
    echo "<div class='errorbox'>RRD Log Directory is missing ({$config['rrd_dir']}).  Graphing may fail.</div>";
}

if (!is_dir($config['temp_dir'])) {
    echo "<div class='errorbox'>Temp Directory is missing ({$config['temp_dir']}).  Graphing may fail.</div>";
}

if (!is_writable($config['temp_dir'])) {
    echo "<div class='errorbox'>Temp Directory is not writable ({$config['tmp_dir']}).  Graphing may fail.</div>";
}

// Clear up any old sessions
dbDelete('session', '`session_expiry` <  ?', array(time()));

session_start();

if ($vars['page'] == 'logout' && session_authenticated()) {
    log_out_user();
    header('Location: ' . $config['base_url']);
    exit;
}

try {
    if (session_authenticated()) {
        // session authenticated already
        log_in_user();
    } else {
        // try authentication methods

        if (isset($_POST['twofactor']) && TwoFactor::authenticate($_POST['twofactor'])) {
            // process two-factor auth tokens
            log_in_user();
        } elseif (isset($_COOKIE['sess_id'], $_COOKIE['token']) &&
            reauthenticate(clean($_COOKIE['sess_id']), clean($_COOKIE['token']))
        ) {
            $_SESSION['remember'] = true;
            $_SESSION['twofactor'] = true; // trust cookie
            // cookie authentication
            log_in_user();
        } else {
            // collect username and password
            $password = null;
            if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
                $username = clean($_REQUEST['username']);
                $password = $_REQUEST['password'];
            } elseif (isset($_SERVER['REMOTE_USER'])) {
                $username = clean($_SERVER['REMOTE_USER']);
            } elseif (isset($_SERVER['PHP_AUTH_USER']) && $config['auth_mechanism'] === 'http-auth') {
                $username = clean($_SERVER['PHP_AUTH_USER']);
            }

            // form authentication
            if (isset($username) && authenticate($username, $password)) {
                $_SESSION['username'] = $username;

                if (isset($_POST['remember'])) {
                    $_SESSION['remember'] = $_POST['remember'];
                }

                if (log_in_user()) {
                    // redirect to original uri or home page.
                    header('Location: '.rtrim($config['base_url'], '/').$_SERVER['REQUEST_URI'], true, 303);
                }
            }
        }
    }
} catch (AuthenticationException $ae) {
    $auth_message = $ae->getMessage();
    if ($debug) {
        $auth_message .= '<br /> ' . $ae->getFile() . ': ' . $ae->getLine();
    }

    dbInsert(
        array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => $auth_message),
        'authlog'
    );
    log_out_user($auth_message);
}

session_write_close();

// populate the permissions cache
if (isset($_SESSION['user_id'])) {
    $permissions = permissions_cache($_SESSION['user_id']);
}

unset($username, $password);
