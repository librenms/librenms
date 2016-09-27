<?php
/* Authentication-related functions */

function auth_end_session()
{
    global $config;

    /* delete the important things just in case, then blow this session away */
    unset($_SESSION['authenticated']);
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['userlevel']);

    /* out with the old */
    session_unset();
    session_destroy();

    /* in with the new */
    session_start();
    auth_update_session_id();

    $_SESSION['last_activity'] = time();
    $_SESSION['expires'] = time() + $config['auth_unauthenticated_session_timeout']*60;
}

function auth_check_session()
{
    global $config;

    session_start();

    if (!isset($_SESSION['expires']) || !isset($_SESSION['last_activity'])) {
        auth_end_session();
        return;
    }

    // Check to see if the session has exceeded its maximum lifetime
    if ($_SESSION['expires'] < time()) {
        if ($_SESSION['authenticated']) {
            dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Session exceeded maximum lifetime'), 'authlog');
        }
        auth_end_session();
        return;
    }

    if ($config['auth_idle_session_timeout'] > 0
        && $_SESSION['last_activity'] < time() + 60 * $config['auth_idle_session_timeout']) {
        if ($_SESSION['authenticated']) {
            dbInsert(array('user' => $_SESSION['username'], 'address' => get_client_ip(), 'result' => 'Session idle timeout'), 'authlog');
        }
        auth_end_session();
        return;
    }
}

function auth_update_session_id()
{
    /* Change our session ID to help prevent session fixation attacs.
     */

    /* The docs for session_regenerate_id claim that it can lose a session with
     * an unreliable network connection, but this is only called during the
     * login process, and the old session is useless anyway -- so we just
     * blindly delete the old session and hope for the best
     */

    session_regenerate_id(true);
}
