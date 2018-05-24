<?php

use LibreNMS\Authentication\Auth;
use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1); // php >= 5.5.2
ini_set('session.use_trans_sid', 0);   // insecure feature, be sure it is disabled

// Clear up any old sessions
dbDelete('session', '`session_expiry` <  ?', array(time()));

session_start();

$authorizer =  Auth::get();
if ($vars['page'] == 'logout' && $authorizer->sessionAuthenticated()) {
    $authorizer->logOutUser();
    header('Location: ' . Config::get('post_logout_action', Config::get('base_url')));
    exit;
}

try {
    if ($authorizer->sessionAuthenticated()) {
        // session authenticated already
        $authorizer->logInUser();
    } else {
        // try authentication methods

        if (isset($_POST['twofactor']) && TwoFactor::authenticate($_POST['twofactor'])) {
            // process two-factor auth tokens
            $authorizer->logInUser();
        } elseif (isset($_COOKIE['sess_id'], $_COOKIE['token']) &&
            $authorizer->reauthenticate(clean($_COOKIE['sess_id']), clean($_COOKIE['token']))
        ) {
            $_SESSION['remember'] = true;
            $_SESSION['twofactor'] = true; // trust cookie
            // cookie authentication
            $authorizer->logInUser();
        } else {
            // collect username and password
            $password = null;
            if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
                $username = clean($_REQUEST['username']);
                $password = $_REQUEST['password'];
            } elseif ($authorizer->authIsExternal()) {
                $username = $authorizer->getExternalUsername();
            }

            // form authentication
            if (isset($username) && $authorizer->authenticate($username, $password)) {
                $_SESSION['username'] = $username;

                if (isset($_POST['remember'])) {
                    $_SESSION['remember'] = $_POST['remember'];
                }

                if ($authorizer->logInUser()) {
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
    $authorizer->logOutUser($auth_message);
}

session_write_close();

// populate the permissions cache
if (isset($_SESSION['user_id'])) {
    $permissions = permissions_cache($_SESSION['user_id']);
}

unset($username, $password);
