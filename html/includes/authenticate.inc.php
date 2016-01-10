<?php

@ini_set('session.gc_maxlifetime', '0');
@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_httponly', 1);
require 'includes/PasswordHash.php';

session_start();

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

if ($vars['page'] == 'logout' && $_SESSION['authenticated']) {
    dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged Out'), 'authlog');
    dbDelete('session', '`session_username` =  ? AND session_value = ?', array($_SESSION['username'], $_COOKIE['sess_id']));
    unset($_SESSION);
    unset($_COOKIE);
    setcookie('sess_id', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    setcookie('token', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    setcookie('auth', '', (time() - 60 * 60 * 24 * $config['auth_remember']), '/');
    session_destroy();
    $auth_message = 'Logged Out';
    header('Location: ' . $config['base_url']);
    exit;
}

// We are only interested in login details passed via POST.
if (isset($_POST['username']) && isset($_POST['password'])) {
    $_SESSION['username'] = mres($_POST['username']);
    $_SESSION['password'] = $_POST['password'];
}
else if (isset($_GET['username']) && isset($_GET['password'])) {
    $_SESSION['username'] = mres($_GET['username']);
    $_SESSION['password'] = $_GET['password'];
}

if (!isset($config['auth_mechanism'])) {
    $config['auth_mechanism'] = 'mysql';
}

if (file_exists('includes/authentication/'.$config['auth_mechanism'].'.inc.php')) {
    include_once 'includes/authentication/'.$config['auth_mechanism'].'.inc.php';
}
else {
    print_error('ERROR: no valid auth_mechanism defined!');
    exit();
}

$auth_success = 0;

if ((isset($_SESSION['username'])) || (isset($_COOKIE['sess_id'],$_COOKIE['token']))) {
    if (reauthenticate($_COOKIE['sess_id'], $_COOKIE['token']) || authenticate($_SESSION['username'], $_SESSION['password'])) {
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
            $hasher   = new PasswordHash(8, false);
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

        if (isset($_COOKIE['sess_id'],$_COOKIE['token'],$_COOKIE['auth'])) {
            // If we have the remember me cookies set then update session expiry times to keep us logged in.
            $sess_id = session_id();
            dbUpdate(array('session_value' => $sess_id, 'session_expiry' => time() + 60 * 60 * 24 * $config['auth_remember']), 'session', 'session_auth=?', array($_COOKIE['auth']));
            setcookie('sess_id', $sess_id, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('token', $_COOKIE['token'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
            setcookie('auth', $_COOKIE['auth'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
        }

        $permissions = permissions_cache($_SESSION['user_id']);
        if (isset($_POST['username'])) {
            header('Location: '.$_SERVER['REQUEST_URI'], true, 303);
            exit;
        }
    }
    else if (isset($_SESSION['username'])) {
        $auth_message = 'Authentication Failed';
        unset($_SESSION['authenticated']);
        dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Authentication Failure'), 'authlog');
    }
}
