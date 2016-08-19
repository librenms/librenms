<?php

@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_httponly', 1);

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

unset($form_password);
unset($form_username);

// We are only interested in login details passed via POST.
if (isset($_POST['username']) && isset($_POST['password'])) {
    $form_username = mres($_POST['username']);
    $form_password = $_POST['password'];
} elseif (isset($_GET['username']) && isset($_GET['password'])) {
    $form_username = mres($_GET['username']);
    $form_password = $_GET['password'];
}

if (!isset($config['auth_mechanism'])) {
    $config['auth_mechanism'] = 'mysql';
}

if (file_exists('includes/authentication/'.$config['auth_mechanism'].'.inc.php')) {
    include_once 'includes/authentication/'.$config['auth_mechanism'].'.inc.php';
} else {
    print_error('ERROR: no valid auth_mechanism defined!');
    exit();
}

if (!$_SESSION['authenticated']) {
    $authenticated = false;
    $pw_success = false;
    $cookie_success = false;

    if (isset($form_username)) {
        /* try password auth */
        $_SESSION['username'] = $form_username;
        $pw_success = authenticate($form_username, $form_password);
    } elseif (isset($_COOKIE['sess_id'],$_COOKIE['token'])) {
        /* trying cookie auth */
        $cookie_success = reauthenticate($_COOKIE['sess_id'], $_COOKIE['token']);
    }

    if ($pw_success || $cookie_success) {
        if ($config['twofactor'] === true && !isset($_SESSION['twofactor'])) {
            include_once $config['install_dir'].'/html/includes/authentication/twofactor.lib.php';
            twofactor_auth();
        }

        if (!$config['twofactor'] || $_SESSION['twofactor']) {
            $authenticated = true;
            $_SESSION['authenticated'] = true;
            dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged In'), 'authlog');
        }
    }

    if ($authenticated) {
        if (!$_SESSION['username']) {
            unset($_SESSION['authenticated']);
            print_error("ERROR: auth_mechanism did not set session username: pw: $pw_success, cookie: $cookie_success");
            exit();
        }
        $_SESSION['userlevel'] = get_userlevel($_SESSION['username']);
        $_SESSION['user_id']   = get_userid($_SESSION['username']);

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

        $permissions = permissions_cache($_SESSION['user_id']);
        if ($pw_success) {
            header('Location: '.$_SERVER['REQUEST_URI'], true, 303);
            exit;
        }
    } elseif (isset($form_username)) {
        global $auth_error;
        if (isset($auth_error)) {
            $auth_message = $auth_error;
        } else {
            $auth_message = 'Authentication Failed';
        }
        unset($_SESSION['authenticated']);
        dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Authentication Failure'), 'authlog');
    }
} else {
    /* we have a valid, authenticated session */
    if (isset($_COOKIE['sess_id'],$_COOKIE['token'],$_COOKIE['auth'])) {
        // If we have the remember me cookies set then update session expiry times to keep us logged in.
        $sess_id = session_id();
        dbUpdate(array('session_value' => $sess_id, 'session_expiry' => time() + 60 * 60 * 24 * $config['auth_remember']), 'session', 'session_auth=?', array($_COOKIE['auth']));
        setcookie('sess_id', $sess_id, (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
        setcookie('token', $_COOKIE['token'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
        setcookie('auth', $_COOKIE['auth'], (time() + 60 * 60 * 24 * $config['auth_remember']), '/', null, false, true);
    }
}
