<?php

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

function init_auth()
{
    // deferred
}


function authenticate($username, $password)
{
    $connection = get_ldap_connection(true);

    if ($username) {
        if ($password && ldap_bind($connection, get_full_dn($username), $password)) {
            if (!Config::has('auth_ldap_group')) {
                return true;
            } else {
                $ldap_groups = get_group_list();
                foreach ($ldap_groups as $ldap_group) {
                    $ldap_comparison = ldap_compare(
                        $connection,
                        $ldap_group,
                        Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                        get_membername($username)
                    );
                    if ($ldap_comparison === true) {
                        return true;
                    }
                }
            }
        }

        if (!isset($password) || $password == '') {
            throw new AuthenticationException('A password is required');
        }

        throw new AuthenticationException(ldap_error($connection));
    }

    throw new AuthenticationException();
}


function reauthenticate($sess_id, $token)
{
    $sess_id = clean($sess_id);
    $token = clean($token);

    list($username, $hash) = explode('|', $token);

    if (!user_exists($username, true)) {
        throw new AuthenticationException();
    }

    return check_remember_me($sess_id, $token);
}


function passwordscanchange($username = '')
{
    return 0;
}


function changepassword($username, $newpassword)
{
    // Not supported (for now)
}


function auth_usermanagement()
{
    return 0;
}


function adduser($username, $password, $level, $email = '', $realname = '', $can_modify_passwd = '1')
{
    // Not supported
    return 0;
}


function user_exists($username, $throw_exception = false)
{
    try {
        $connection = get_ldap_connection();

        $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
        $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($connection, $search);
        if ($entries['count']) {
            return 1;
        }
    } catch (AuthenticationException $e) {
        if ($throw_exception) {
            throw $e;
        } else {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    return 0;
}


function get_userlevel($username)
{
    $userlevel = 0;

    try {
        $connection = get_ldap_connection();
        $groups = Config::get('auth_ldap_groups');

        // Find all defined groups $username is in
        $filter = '(&(|(cn=' . join(')(cn=', array_keys($groups)) . '))(' . Config::get('auth_ldap_groupmemberattr', 'memberUid') . '=' . get_membername($username) . '))';
        $search = ldap_search($connection, Config::get('auth_ldap_groupbase'), $filter);
        $entries = ldap_get_entries($connection, $search);

        // Loop the list and find the highest level
        foreach ($entries as $entry) {
            $groupname = $entry['cn'][0];
            if ($groups[$groupname]['level'] > $userlevel) {
                $userlevel = $groups[$groupname]['level'];
            }
        }
    } catch (AuthenticationException $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    return $userlevel;
}


function get_userid($username)
{
    try {
        $connection = get_ldap_connection();

        $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
        $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($connection, $search);

        if ($entries['count']) {
            $uid_attr = strtolower(Config::get('auth_ldap_uid_attribute', 'uidnumber'));
            return $entries[0][$uid_attr][0];
        }
    } catch (AuthenticationException $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    return -1;
}


function deluser($username)
{
    // Not supported
    return 0;
}


function get_userlist()
{
    $userlist = array();

    try {
        $connection = get_ldap_connection();

        $filter = '(' . Config::get('auth_ldap_prefix') . '*)';
        $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($connection, $search);

        if ($entries['count']) {
            foreach ($entries as $entry) {
                $username = $entry['uid'][0];
                $realname = $entry['cn'][0];
                $uid_attr = strtolower(Config::get('auth_ldap_uid_attribute', 'uidnumber'));
                $user_id = $entry[$uid_attr][0];
                $email = $entry[Config::get('auth_ldap_emailattr', 'mail')][0];
                $ldap_groups = get_group_list();
                foreach ($ldap_groups as $ldap_group) {
                    $ldap_comparison = ldap_compare(
                        $connection,
                        $ldap_group,
                        Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                        get_membername($username)
                    );
                    if (!Config::has('auth_ldap_group') || $ldap_comparison === true) {
                        $userlist[$username] = array(
                            'username' => $username,
                            'realname' => $realname,
                            'user_id' => $user_id,
                            'email' => $email,
                        );
                    }
                }
            }
        }
    } catch (AuthenticationException $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    return $userlist;
}


function can_update_users()
{
    // not supported so return 0
    return 0;
}


function get_user($user_id)
{
    foreach (get_userlist() as $user) {
        if ($user['user_id'] === $user_id) {
            return $user;
        }
    }
    return 0;
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email)
{
    // not supported
    return 0;
}


function get_membername($username)
{
    $type = Config::get('auth_ldap_groupmembertype');

    if ($type == 'fulldn') {
        return get_full_dn($username);
    }

    if ($type == 'puredn') {
        try {
            $connection = get_ldap_connection();
            $filter = '(' . Config::get('auth_ldap_attr.uid') . '=' . $username . ')';
            $search = ldap_search($connection, Config::get('auth_ldap_groupbase'), $filter);
            $entries = ldap_get_entries($connection, $search);
            return $entries[0]['dn'];
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    return $username;
}


function get_group_list()
{
    $ldap_groups = array();

    $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';  // in the documentation
    if (Config::get('auth_ldap_group', $default_group) !== $default_group) {
        $ldap_groups[] = Config::get('auth_ldap_group');
    }

    foreach (Config::get('auth_ldap_groups') as $key => $value) {
        $ldap_groups[] = "cn=$key,".Config::get('auth_ldap_groupbase');
    }

    return $ldap_groups;
}

/**
 * Get the full dn with auth_ldap_prefix and auth_ldap_suffix
 * @internal
 *
 * @return string
 */
function get_full_dn($username)
{
    return Config::get('auth_ldap_prefix', '') . $username . Config::get('auth_ldap_suffix', '');
}

/**
 * Get the ldap connection. If it hasn't been established yet, connect and try to bind.
 * @internal
 *
 * @param bool $skip_bind do not attempt to bind on connection
 * @return false|resource
 * @throws AuthenticationException
 */
function get_ldap_connection($skip_bind = false)
{
    global $ldap_connection;

    if ($ldap_connection) {
        return $ldap_connection; // bind already attempted
    }

    $ldap_connection = @ldap_connect(Config::get('auth_ldap_server'), Config::get('auth_ldap_port', 389));

    if (!$ldap_connection) {
        throw new AuthenticationException('Unable to connect to ldap server');
    }

    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, Config::get('auth_ldap_version', 2));

    $use_tls = Config::get('auth_ldap_starttls');
    if ($use_tls == 'optional'||$use_tls == 'require') {
        $tls_success = ldap_start_tls($ldap_connection);
        if ($use_tls == 'require' && $tls_success === false) {
            $error = ldap_error($ldap_connection);
            throw new AuthenticationException("Fatal error: LDAP TLS required but not successfully negotiated: $error");
        }
    }

    if ($skip_bind) {
        return $ldap_connection;
    }

    // set timeout
    ldap_set_option($ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ldap_timeout', 5));

    // With specified bind user
    if ((Config::has('auth_ldap_binduser') || Config::has('auth_ldap_binddn'))
        && Config::has('auth_ldap_bindpassword')
    ) {
        if (Config::has('auth_ldap_binddn')) {
            $bind_dn = Config::get('auth_ldap_binddn');
        } else {
            $bind_dn = get_full_dn(Config::get('auth_ldap_binduser'));
        }

        if (ldap_bind(
            $ldap_connection,
            $bind_dn,
            Config::get('auth_ldap_bindpassword')
        )) {
            ldap_set_option($ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
            return $ldap_connection;
        }
    }

    // Anonymous
    ldap_bind($ldap_connection);

    ldap_set_option($ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
    return $ldap_connection;
}
