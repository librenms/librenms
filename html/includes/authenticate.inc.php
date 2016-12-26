<?php

require_once 'includes/authentication_functions.inc.php';

@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_httponly', 1);
@ini_set('session.use_strict_mode', 1); // php >= 5.5.2
@ini_set('session.use_trans_sid', 0);   // insecure feature, be sure it is disabled

/* make sure our PHP sessions can survive for the desired amount of time */
@ini_set('session.gc_maxlifetime', $config['auth_remember'] * 60 * 60 * 24);


auth_check_session();

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
    auth_end_session();
    $auth_message = 'Logged Out';
    header('Location: ' . $config['base_url']);
    exit;
}

unset($form_password);
unset($form_username);

// We are only interested in login details passed via POST.
if (isset($_POST['username']) && isset($_POST['password'])) {
    $form_username = clean($_POST['username']);
    $form_password = $_POST['password'];
} elseif (isset($_GET['username']) && isset($_GET['password'])) {
    /* FIXME: allowing GET for authentication reduces security */
    $form_username = clean($_GET['username']);
    $form_password = $_GET['password'];
} elseif (isset($_SERVER['REMOTE_USER'])) {
    $form_username = clean($_SERVER['REMOTE_USER']);
}

if (!isset($config['auth_mechanism'])) {
    $config['auth_mechanism'] = 'mysql';
}

if (!$_SESSION['authenticated']) {
    $auth_mechanism_successful = false;

    if (isset($form_username)) {
        /* try password auth */
        $_SESSION['username'] = $form_username;
        $auth_mechanism_successful = authenticate($form_username, $form_password);
        if ($auth_mechanism_successful !== 1) {
            $auth_mechanism_successful = false;
        }
        if (isset($_SESSION['username']) && $_SESSION['username'] !== $form_username) {
            /* Must be using one of the auth methods that trusts $_SERVER['REMOTE_USER'] */
            $form_username = $_SESSION['username'];
        }
    }

    if ($auth_mechanism_successful === 1) {
        if ($config['twofactor'] === true && !isset($_SESSION['twofactor'])) {
            include_once $config['install_dir'].'/html/includes/authentication/twofactor.lib.php';
            twofactor_auth();
        }

        if (!$config['twofactor'] || $_SESSION['twofactor']) {
            /* Congratulations, you are authenticated! */

            /* do this before changing the session_id so the session cookie has
             * the right lifetime; a long expiration time on an unauthenticated
             * session should not be dangerous */
            if (isset($_POST['remember'])) {
                // keep session cookie for auth_remember days
                session_set_cookie_params($config['auth_remember'] * 60 * 60 * 24);
                $_SESSION['expires'] = time() + $config['auth_remember'] * 60 * 60 * 24;
            } else {
                // keep session cookie for the duration of the browser session
                session_set_cookie_params(0);

                // valid for auth_no_remember minutes, regardless of activity
                $_SESSION['expires'] = time() + $config['auth_no_remember'] * 60;
            }

            /* prevent session fixation vulnerabilities */
            auth_update_session_id();

            $_SESSION['authenticated'] = true;
            dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Logged In'), 'authlog');
        }
    }

    if ($_SESSION['authenticated']) {
        if (!$_SESSION['username']) {
            unset($_SESSION['authenticated']);
            print_error("ERROR: auth_mechanism did not set session username");
            exit();
        }
        $_SESSION['userlevel'] = get_userlevel($_SESSION['username']);
        $_SESSION['user_id']   = get_userid($_SESSION['username']);

        $permissions = permissions_cache($_SESSION['user_id']);
        if ($auth_mechanism_successful === 1) {
            header('Location: '.$_SERVER['REQUEST_URI'], true, 303);
            exit;
        }
    } elseif (isset($form_username) || isset($_SESSION['username'])) {
        global $auth_error;
        if (isset($auth_error)) {
            $auth_message = $auth_error;
        } else {
            $auth_message = 'Authentication Failed';
        }
        auth_end_session();
        dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Authentication Failure'), 'authlog');
    }
} else {
    /* we have a valid, previously-authenticated session */
}
