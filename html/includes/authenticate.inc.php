<?php

use Phpass\PasswordHash;

@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_httponly', 1);
@ini_set('session.use_strict_mode', 1); // php >= 5.5.2
@ini_set('session.use_trans_sid', 0);   // insecure feature, be sure it is disabled

// Preflight checks
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

if ($vars['page'] == 'logout' && $_SESSION['authenticated']) {
    dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged Out'), 'authlog');
    dbDelete('session', '`session_username` =  ? AND session_value = ?', array($_SESSION['username'], $_COOKIE['sess_id']));
    unset($_SESSION['authenticated']);
    unset($_COOKIE);
    setcookie('sess_id', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    setcookie('token', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    setcookie('auth', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    session_destroy();
    $auth_message = 'Logged Out';
    header('Location: ' . $config['base_url']);
    exit;
}

if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
    $username = clean($_REQUEST['username']);
    $password = $_REQUEST['password'];
} elseif (isset($_SERVER['REMOTE_USER'])) {
    $username = $_SERVER['REMOTE_USER'];
} elseif (isset($_SERVER['PHP_AUTH_USER']) && $config['auth_mechanism'] === 'http-auth') {
    $username = $_SERVER['PHP_AUTH_USER'];
}

if ((isset($username)) || (isset($_COOKIE['sess_id'], $_COOKIE['token']))) {
    if ((isset($_SESSION['authenticated']) && $_SESSION['authenticated']) ||
        (isset($_COOKIE['sess_id'], $_COOKIE['token']) && reauthenticate($_COOKIE['sess_id'], $_COOKIE['token'])) ||
        (isset($username, $password) && authenticate($username, $password))
    ) {
        session_regenerate_id(true); // prevent session fixation
        if (isset($username)) {
            $_SESSION['username'] = $username;
        }
        unset($username, $password);

        $_SESSION['userlevel'] = get_userlevel($_SESSION['username']);
        $_SESSION['user_id']   = get_userid($_SESSION['username']);
        if (!$_SESSION['authenticated']) {
            if ($config['twofactor'] === true && !isset($_SESSION['twofactor'])) {
                include_once $config['install_dir'].'/html/includes/authentication/twofactor.lib.php';
                twofactor_auth();
            }

            if (!$config['twofactor'] || $_SESSION['twofactor']) {
                $_SESSION['authenticated'] = true;
                dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged In'), 'authlog');
            }
        }

        if (isset($_POST['remember'])) {
            $sess_id  = session_id();
            $token    = strgen();
            $auth     = strgen();
            $hasher   = new PasswordHash(8, false);
            $token_id = $_SESSION['username'].'|'.$hasher->HashPassword($_SESSION['username'].$token);
            // If we have been asked to remember the user then set the relevant cookies and create a session in the DB.
            setcookie('sess_id', $sess_id, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('token', $token_id, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('auth', $auth, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            dbInsert(array('session_username' => $_SESSION['username'], 'session_value' => $sess_id, 'session_token' => $token, 'session_auth' => $auth, 'session_expiry' => time() + 60 * 60 * 24 * $config['auth_remember']), 'session');
        }

        if (isset($_COOKIE['sess_id'], $_COOKIE['token'], $_COOKIE['auth'])) {
            // If we have the remember me cookies set then update session expiry times to keep us logged in.
            $sess_id = session_id();
            dbUpdate(array('session_value' => $sess_id, 'session_expiry' => time() + 60 * 60 * 24 * $config['auth_remember']), 'session', 'session_auth=?', array($_COOKIE['auth']));
            setcookie('sess_id', $sess_id, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('token', $_COOKIE['token'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('auth', $_COOKIE['auth'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
        }

        $permissions = permissions_cache($_SESSION['user_id']);
        if (isset($_POST['username'])) {
            // Trim the trailing slash off of base_url and concatenate the (relative) REQUEST_URI
            header('Location: '.rtrim($config['base_url'], '/').$_SERVER['REQUEST_URI'], true, 303);
            exit;
        }
    } elseif (isset($_SESSION['username'])) {
        global $auth_error;
        if (isset($auth_error)) {
            $auth_message = $auth_error;
        } else {
            $auth_message = 'Authentication Failed';
        }
        unset($_SESSION['authenticated']);
        dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Authentication Failure'), 'authlog');
    }
}

session_write_close();
